<?php
session_start();
// 1. Hubungkan ke Database SIMAVIS
include '../koneksi.php';

// Helper Format Tanggal Bahasa Indonesia
function formatTanggalIndo($tanggal) {
    if (!$tanggal) return '-';
    // Gunakan nama bulan lengkap agar seragam dengan laporan bulanan
    $bulan = [
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    $split = explode('-', date('Y-m-d', strtotime($tanggal)));
    return $split[2] . ' ' . $bulan[(int)$split[1]] . ' ' . $split[0];
}

// Ambil nama dari session (default: 'Rifki Maulana')
$nama_user  = $_SESSION['nama_staf'] ?? $_SESSION['nama_admin'] ?? 'Rifki Maulana';
$role_user  = $_SESSION['role'] ?? 'Admin Utama';

// Ambil huruf pertama dan ubah ke huruf kapital (misal: "Rifki" -> "R", "nakka" -> "N")
$inisial = strtoupper(substr(trim($nama_user), 0, 1));

// 2. TENTUKAN TANGGAL ACUAN PERIODE MINGGUAN (Default: Hari Ini)
$tgl_ref = isset($_GET['tgl']) ? $_GET['tgl'] : date('Y-m-d');

// Cari Tanggal Senin dan Minggu dari minggu berjalan
$timestamp_ref = strtotime($tgl_ref);
$day_of_week   = date('N', $timestamp_ref); // 1 = Senin, 7 = Minggu

$tgl_senin  = date('Y-m-d', strtotime('-' . ($day_of_week - 1) . ' days', $timestamp_ref));
$tgl_minggu = date('Y-m-d', strtotime('+' . (7 - $day_of_week) . ' days', $timestamp_ref));

// 3. AMBIL DATA PENDAPATAN HARIAN (SENIN - MINGGU)
$nama_hari_indo = [
    1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 
    5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu'
];

$data_harian = [];
$total_pendapatan_minggu = 0;

for ($i = 1; $i <= 7; $i++) {
    $tgl_loop = date('Y-m-d', strtotime('+' . ($i - 1) . ' days', strtotime($tgl_senin)));
    
    // Query total pendapatan per hari dari booking
    $q_harian = mysqli_query($conn, "
        SELECT SUM(total_harga) AS total 
        FROM booking 
        WHERE DATE(tanggal_sesi) = '$tgl_loop' 
          AND (status_pembayaran = 'Lunas' OR status_pembayaran = 'Berhasil')
    ");
    $r_harian = mysqli_fetch_assoc($q_harian);
    $nominal = $r_harian['total'] ?? 0;

    $total_pendapatan_minggu += $nominal;

    $is_weekend = ($i == 6 || $i == 7);
    $ket = $is_weekend ? 'Akhir pekan' : 'Hari kerja';

    $data_harian[$i] = [
        'hari'       => $nama_hari_indo[$i],
        'kode_hari'  => substr($nama_hari_indo[$i], 0, 3),
        'tanggal'    => $tgl_loop,
        'nominal'    => $nominal,
        'keterangan' => $ket,
        'display_mb' => ($nominal >= 1000000) ? number_format($nominal/1000000, 1, ',', '.') . ' jt' : number_format($nominal/1000, 0, ',', '.') . ' rb'
    ];
}

// 4. KALKULASI STATISTIK RINGKASAN
$rata_rata_per_hari = $total_pendapatan_minggu / 7;

// Cari Pendapatan Tertinggi
$nominal_tertinggi = 0;
$hari_tertinggi = 'Sabtu';
foreach ($data_harian as $i => $d) {
    if ($d['nominal'] >= $nominal_tertinggi) {
        $nominal_tertinggi = $d['nominal'];
        $hari_tertinggi = $d['hari'];
    }
}
$persen_tertinggi = ($total_pendapatan_minggu > 0) ? ($nominal_tertinggi / $total_pendapatan_minggu) * 100 : 0;

// Cari nilai max untuk tinggi grafik bar (skala max 200px)
$max_val_chart = max(array_column($data_harian, 'nominal'));
if ($max_val_chart <= 0) $max_val_chart = 1;

// Total order untuk badge sidebar
$q_total_order = mysqli_query($conn, "SELECT COUNT(*) AS total FROM booking");
$r_total_order = mysqli_fetch_assoc($q_total_order);
$total_order = $r_total_order['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SIMAVIS - Laporan Pendapatan Mingguan</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <!-- Tailwind CSS CDN -->
  <script src="https://cdn.tailwindcss.com"></script>
  <!-- Google Font: Instrument Sans -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>
    body { font-family: 'Instrument Sans', sans-serif; }
    .custom-scrollbar::-webkit-scrollbar { width: 6px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: #f1f1f1; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
    @media print {
      aside, button, form, .no-print { display: none !important; }
      main { padding: 0 !important; overflow: visible !important; }
      body { background: white !important; }
    }
  </style>
</head>
<body class="bg-[#F8FAFC] flex h-screen w-screen overflow-hidden">

  <!-- ================= SIDEBAR ================= -->
  <aside class="w-[260px] h-full flex-shrink-0 p-5 pt-8 pb-8 flex flex-col justify-between items-start border-r border-[#164E63]"
         style="background: linear-gradient(180deg, #0F597A 0%, #0A4061 60%, #082E4D 100%);">
    
    <div class="w-full flex flex-col justify-start items-start gap-8">
      <!-- Logo Brand -->
      <div class="flex justify-start items-center gap-3">
        <img src="../user/asset/logo.jpeg" alt="Logo SIMAVIS" class="w10 h-10 rounded-lg object-cover shadow-xs" />
        <div class="flex flex-col justify-start items-start gap-0.5">
          <div class="text-white text-base font-bold">SIMAVIS</div>
          <div class="text-[#22D3EE]/80 text-[11px] font-medium uppercase tracking-wider">Vibes Studio Foto</div>
        </div>
      </div>

      <!-- Navigation Menu -->
      <nav class="w-full flex flex-col gap-2">
            <!-- Dashboard -->
            <a href="dashboard.php" class="w-full px-4 py-3 rounded-xl flex justify-start items-center gap-3 text-white/85 hover:bg-white/10 transition-colors font-medium text-sm">
                <i class="fa-solid fa-house w-5 text-center"></i>
                <span class="flex-1">Dashboard</span>
            </a>

            <!-- Pesanan -->
            <a href="pesanan.php" class="w-full px-4 py-3 rounded-xl flex justify-start items-center gap-3 text-white/85 hover:bg-white/10 transition-colors font-medium text-sm">
                <i class="fa-solid fa-cart-shopping w-5 text-center"></i>
                <span class="flex-1">Pesanan</span>
            </a>

            <!-- Galeri & QR -->
            <a href="galeri.php" class="w-full px-4 py-3 rounded-xl flex justify-start items-center gap-3 text-white/85 hover:bg-white/10 transition-colors font-medium text-sm">
                <i class="fa-solid fa-images w-5 text-center"></i>
                <span class="flex-1">Galeri & QR</span>
            </a>

            <!-- Paket & Tema Foto -->
            <a href="paket.php" class="w-full px-4 py-3 rounded-xl flex justify-start items-center gap-3 text-white/85 hover:bg-white/10 transition-colors font-medium text-sm">
            <i class="fa-solid fa-boxes-packing w-5 text-center"></i>
            <span class="flex-1">Paket & Tema</span>
            </a>
            
            <!-- Laporan -->
            <a href="laporan.php" class="w-full px-4 py-3 bg-[#0891B2] rounded-xl flex justify-start items-center gap-3 text-white font-semibold text-sm shadow-md">
                <i class="fa-solid fa-chart-pie w-5 text-center"></i>
                <span class="flex-1">Laporan</span>
            </a>
        </nav>
    </div>

    <!-- Bottom Profile Card & Logout -->
    <div class="w-full p-3 bg-white rounded-2xl outline outline-1 outline-[#E5E7EB] -outline-offset-1 flex justify-start items-center gap-3 shadow-sm">
      <img class="w-9 h-9 rounded-xl flex-shrink-0" src="https://placehold.co/36x36" alt="User Profile" />
      <div class="flex-1 flex flex-col justify-start items-start gap-0.5 min-w-0">
        <div class="text-[#2B2544] text-xs font-semibold truncate w-full"><?= htmlspecialchars($_SESSION['nama_staf'] ?? 'Dimas Prayoga'); ?></div>
        <div class="text-[#6C6287] text-[11px] font-normal truncate w-full">Admin Utama</div>
      </div>
      <a href="login.php" title="Keluar" class="p-1 text-gray-400 hover:text-red-500 transition-colors">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
        </svg>
      </a>
    </div>
  </aside>

  <!-- ================= MAIN CONTENT ================= -->
  <main class="flex-1 h-full flex flex-col min-w-0 overflow-hidden bg-white">
    
    <!-- Area Data dengan Scroll Bar -->
    <div class="flex-1 overflow-y-auto p-8 lg:p-10 flex flex-col gap-6 custom-scrollbar">
      
      <!-- HEADER PAGE -->
      <div class="flex justify-between items-center w-full pb-2">
        <div class="flex flex-col gap-1">
          <h1 class="text-[#2B2544] text-2xl lg:text-[30px] font-bold">Laporan Pendapatan Mingguan</h1>
          <p class="text-[#6C6287] text-sm font-normal">Analisis pendapatan per hari dalam satu minggu</p>
        </div>
        <div class="flex items-center gap-3">
          <div class="px-4 py-2 bg-white shadow-sm rounded-xl outline outline-1 outline-[#EAE6FA] -outline-offset-1 flex items-center gap-2">
            <svg class="w-5 h-5 text-[#2B2544] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            <span class="text-[#2B2544] text-xs font-semibold">Hari Ini: <?= formatTanggalIndo(date('Y-m-d')); ?></span>
          </div>
        </div>
      </div>

      <!-- DATE & PRINT BANNER -->
      <form method="GET" action="" class="p-4 bg-white shadow-[0px_10px_24px_-8px_rgba(15,23,42,0.08)] rounded-2xl outline outline-1 outline-[#E5E7EB] -outline-offset-1 flex justify-between items-center w-full">
        <div class="flex items-center gap-2.5 whitespace-nowrap text-[#2B2544]">
          <svg class="w-5 h-5 text-[#0891B2] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
          </svg>
          <span class="text-xs font-semibold text-[#6C6287]">Laporan Mingguan:</span>
          
          <!-- Date Picker Interaktif Mingguan -->
          <input type="date" name="tgl" value="<?= $tgl_ref; ?>" onchange="this.form.submit()" class="text-xs font-bold text-[#2B2544] bg-transparent focus:outline-none cursor-pointer border border-gray-200 rounded-lg px-2 py-1" />
          
          <span class="text-xs font-bold text-[#2B2544]">
            (<?= formatTanggalIndo($tgl_senin); ?> - <?= formatTanggalIndo($tgl_minggu); ?>)
          </span>
        </div>
        
        <button type="button" onclick="window.print()" class="px-4 py-2 bg-[#0891B2]/13 hover:bg-[#0891B2]/20 transition-colors rounded-xl text-[#0891B2] font-bold text-xs flex items-center gap-2 shrink-0 whitespace-nowrap">
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
          </svg>
          <span>Cetak Rekap Mingguan</span>
        </button>
      </form>

      <!-- FILTER TABS & 4 STAT CARDS -->
      <div class="flex flex-col gap-3">
        <!-- FILTER TABS -->
        <div class="flex items-center gap-3">
          <a href="Laporan.php" class="px-4 py-2 bg-white text-[#6C6287] font-semibold text-xs rounded-xl outline outline-1 outline-[#E5E7EB] -outline-offset-1 hover:bg-gray-50 transition-colors">Harian</a>
          <a href="LaporanMingguan.php" class="px-4 py-2 bg-[#0891B2] text-white font-bold text-xs rounded-xl shadow-sm transition-colors">Mingguan</a>
          <a href="LaporanBulanan.php" class="px-4 py-2 bg-white text-[#6C6287] font-semibold text-xs rounded-xl outline outline-1 outline-[#E5E7EB] -outline-offset-1 hover:bg-gray-50 transition-colors">Bulanan</a>
        </div>

        <!-- 4 STAT CARDS GRID -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          
          <div class="p-4 bg-white shadow-[0px_10px_24px_-8px_rgba(15,23,42,0.08)] rounded-2xl outline outline-1 outline-[#EAE6FA] -outline-offset-1 flex flex-col gap-2">
            <span class="text-[#6C6287] text-xs font-semibold">Total Pendapatan</span>
            <div class="flex items-center gap-2.5">
              <svg class="w-4 h-4 text-[#0891B2]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
              <span class="text-[#2B2544] text-lg font-bold">Rp <?= number_format($total_pendapatan_minggu, 0, ',', '.'); ?></span>
            </div>
            <span class="text-[#6C6287] text-[11px]">Akumulasi pendapatan 7 hari</span>
          </div>

          <div class="p-4 bg-white shadow-[0px_10px_24px_-8px_rgba(15,23,42,0.08)] rounded-2xl outline outline-1 outline-[#EAE6FA] -outline-offset-1 flex flex-col gap-2">
            <span class="text-[#6C6287] text-xs font-semibold">Rata-rata per Hari</span>
            <div class="flex items-center gap-2.5">
              <svg class="w-4 h-4 text-[#0891B2]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
              <span class="text-[#2B2544] text-lg font-bold">Rp <?= number_format($rata_rata_per_hari, 0, ',', '.'); ?></span>
            </div>
            <span class="text-[#6C6287] text-[11px]">Total pendapatan ÷ 7 hari</span>
          </div>

          <div class="p-4 bg-white shadow-[0px_10px_24px_-8px_rgba(15,23,42,0.08)] rounded-2xl outline outline-1 outline-[#EAE6FA] -outline-offset-1 flex flex-col gap-2">
            <span class="text-[#6C6287] text-xs font-semibold">Pendapatan Tertinggi</span>
            <div class="flex items-center gap-2.5">
              <svg class="w-4 h-4 text-[#0891B2]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
              <span class="text-[#2B2544] text-lg font-bold">Rp <?= number_format($nominal_tertinggi, 0, ',', '.'); ?></span>
            </div>
            <span class="text-[#6C6287] text-[11px]"><?= $hari_tertinggi; ?> • <?= number_format($persen_tertinggi, 2, ',', '.'); ?>% dari total</span>
          </div>

          <div class="p-4 bg-white shadow-[0px_10px_24px_-8px_rgba(15,23,42,0.08)] rounded-2xl outline outline-1 outline-[#EAE6FA] -outline-offset-1 flex flex-col gap-2">
            <span class="text-[#6C6287] text-xs font-semibold">Cakupan Laporan</span>
            <div class="flex items-center gap-2.5">
              <svg class="w-4 h-4 text-[#0891B2]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
              <span class="text-[#2B2544] text-lg font-bold">7 Hari</span>
            </div>
            <span class="text-[#6C6287] text-[11px]">Senin sampai Minggu</span>
          </div>

        </div>
      </div>

      <!-- BAR CHART: WEEKLY TREND -->
      <div class="p-5 bg-white shadow-[0px_10px_24px_-8px_rgba(15,23,42,0.08)] rounded-2xl outline outline-1 outline-[#E5E7EB] -outline-offset-1 flex flex-col gap-3">
        <div class="flex justify-between items-center">
          <h3 class="text-[#2B2544] text-sm font-bold">Tren Pendapatan Mingguan</h3>
          <span class="text-[#6C6287] text-xs font-semibold">Total: Rp <?= number_format($total_pendapatan_minggu, 0, ',', '.'); ?></span>
        </div>
        <span class="text-[#6C6287] text-xs">Pendapatan harian dalam juta rupiah</span>

        <!-- BAR CHART GRAPHIC CONTAINER -->
        <div class="h-[240px] flex items-end justify-between gap-3 pt-4 pb-2">
          
          <?php 
          $warna_bar = ['bg-[#6366F1]', 'bg-[#22D3EE]', 'bg-[#6366F1]', 'bg-[#8B5CF6]', 'bg-[#6366F1]', 'bg-[#22D3EE]', 'bg-[#6366F1]'];
          foreach ($data_harian as $i => $d) : 
              $height_px = max(12, round(($d['nominal'] / $max_val_chart) * 200));
          ?>
          <!-- <?= $d['hari']; ?> -->
          <div class="flex-1 flex flex-col items-center gap-1.5 h-full justify-end">
            <span class="text-[#6C6287] text-[11px] font-semibold"><?= $d['display_mb']; ?></span>
            <div class="w-16 <?= $warna_bar[$i-1]; ?> rounded-t-md transition-all duration-300" style="height: <?= $height_px; ?>px;"></div>
            <span class="text-[#2B2544] text-xs font-semibold"><?= $d['kode_hari']; ?></span>
          </div>
          <?php endforeach; ?>

        </div>

        <p class="text-[#6C6287] text-xs leading-relaxed">
          Pendapatan tertinggi pada <?= $hari_tertinggi; ?>: Rp <?= number_format($nominal_tertinggi, 0, ',', '.'); ?>. Data diambil langsung dari tabel booking.
        </p>
      </div>

      <!-- DAILY RECAP TABLE CONTAINER -->
      <div class="p-5 bg-white shadow-[0px_10px_24px_-8px_rgba(15,23,42,0.08)] rounded-2xl outline outline-1 outline-[#E5E7EB] -outline-offset-1 flex flex-col gap-3">
        <h3 class="text-[#2B2544] text-sm font-bold">Rekapitulasi Pendapatan per Hari</h3>

        <div class="rounded-xl border border-[#EAE6FA] overflow-hidden flex flex-col text-xs">
          <!-- Table Header -->
          <div class="px-3 py-2 bg-[#F3F0FA] flex items-center font-semibold text-[#6C6287]">
            <div class="w-[170px]">Hari</div>
            <div class="flex-1">Keterangan</div>
            <div class="w-[200px] text-right">Pendapatan</div>
            <div class="w-[160px] text-right">Kontribusi</div>
          </div>

          <!-- Rows Dinamis DB -->
          <?php foreach ($data_harian as $i => $d) : 
              $kontribusi = ($total_pendapatan_minggu > 0) ? ($d['nominal'] / $total_pendapatan_minggu) * 100 : 0;
              $ket_row = $d['keterangan'];
              if ($d['hari'] === $hari_tertinggi && $d['nominal'] > 0) {
                  $ket_row .= ' • Pendapatan tertinggi';
              }
          ?>
          <div class="px-3 py-2 bg-[#F8FBFF] border-b border-[#EAE6FA] flex items-center">
            <div class="w-[170px] font-bold text-[#0891B2]"><?= $d['hari']; ?> (<?= date('d/m', strtotime($d['tanggal'])); ?>)</div>
            <div class="flex-1 text-[#6C6287]"><?= $ket_row; ?></div>
            <div class="w-[200px] text-right font-semibold text-[#2B2544]">Rp <?= number_format($d['nominal'], 0, ',', '.'); ?></div>
            <div class="w-[160px] text-right text-[#6C6287]"><?= number_format($kontribusi, 2, ',', '.'); ?>%</div>
          </div>
          <?php endforeach; ?>

          <!-- Total Footer Row -->
          <div class="px-3 py-2 bg-[#F3F0FA] flex items-center font-bold">
            <div class="flex-1 text-[#2B2544]">Total Pendapatan</div>
            <div class="w-[200px] text-right text-[#0891B2]">Rp <?= number_format($total_pendapatan_minggu, 0, ',', '.'); ?></div>
            <div class="w-[160px] text-right text-[#2B2544]">100%</div>
          </div>
        </div>

        <p class="text-[#6C6287] text-xs leading-relaxed">
          Rekapitulasi otomatis mengikuti data transaksi pesanan yang telah lunas pada database SIMAVIS.
        </p>
      </div>

    </div>
  </main>

</body>
</html>
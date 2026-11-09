<?php
session_start();
// 1. Hubungkan ke Database SIMAVIS
include '../koneksi.php';

// Helper Format Tanggal Bahasa Indonesia
function formatTanggalIndo($tanggal) {
    if (!$tanggal) return '-';
    $bulan = [
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    $split = explode('-', date('Y-m-d', strtotime($tanggal)));
    return $split[2] . ' ' . $bulan[(int)$split[1]] . ' ' . $split[0];
}

// 2. TENTUKAN BULAN & TAHUN LAPORAN (Default: Bulan & Tahun Saat Ini)
$bulan_pilihan = isset($_GET['bulan']) ? intval($_GET['bulan']) : date('m');
$tahun_pilihan = isset($_GET['tahun']) ? intval($_GET['tahun']) : date('Y');

$nama_bulan = [
    1 => 'Oktober', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
    'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
];
$bulan_teks = [
    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
];

$jumlah_hari_bulan = cal_days_in_month(CAL_GREGORIAN, $bulan_pilihan, $tahun_pilihan);

// 3. HITUNG REKAPITULASI MINGGUAN DINAMIS (5 PERIODE MINGGU)
$rentang_minggu = [
    1 => ['mulai' => 1, 'selesai' => 7],
    2 => ['mulai' => 8, 'selesai' => 14],
    3 => ['mulai' => 15, 'selesai' => 21],
    4 => ['mulai' => 22, 'selesai' => 28],
    5 => ['mulai' => 29, 'selesai' => $jumlah_hari_bulan]
];

$rekap_mingguan = [];
$total_pendapatan_bulan = 0;

foreach ($rentang_minggu as $m => $r) {
    $tgl_mulai = sprintf('%04d-%02d-%02d', $tahun_pilihan, $bulan_pilihan, $r['mulai']);
    $tgl_selesai = sprintf('%04d-%02d-%02d', $tahun_pilihan, $bulan_pilihan, $r['selesai']);
    
    // Query total pendapatan per rentang minggu
    $q_m = mysqli_query($conn, "
        SELECT SUM(total_harga) AS total 
        FROM booking 
        WHERE DATE(tanggal_sesi) BETWEEN '$tgl_mulai' AND '$tgl_selesai'
          AND (status_pembayaran = 'Lunas' OR status_pembayaran = 'Berhasil')
    ");
    $r_m = mysqli_fetch_assoc($q_m);
    $nominal = $r_m['total'] ?? 0;

    $total_pendapatan_bulan += $nominal;

    $jumlah_hari_periode = ($r['selesai'] - $r['mulai']) + 1;
    $label_sisa = ($m == 5) ? " ($jumlah_hari_periode hari)" : "";

    $rekap_mingguan[$m] = [
        'minggu'      => "Minggu $m",
        'rentang'     => $r['mulai'] . " – " . $r['selesai'] . " " . $bulan_teks[$bulan_pilihan] . " " . $tahun_pilihan . $label_sisa,
        'nominal'     => $nominal,
        'display_mb'  => ($nominal >= 1000000) ? number_format($nominal/1000000, 1, ',', '.') . ' jt' : number_format($nominal/1000, 0, ',', '.') . ' rb'
    ];
}

// 4. KALKULASI STATISTIK RINGKASAN
$rata_rata_per_minggu = $total_pendapatan_bulan / 5;

// Cari Pendapatan Tertinggi
$nominal_tertinggi = 0;
$minggu_tertinggi = 'Minggu 1';
foreach ($rekap_mingguan as $m => $data) {
    if ($data['nominal'] >= $nominal_tertinggi) {
        $nominal_tertinggi = $data['nominal'];
        $minggu_tertinggi = $data['minggu'];
    }
}

// Cari nilai max untuk tinggi grafik bar (skala max 204px)
$max_val_chart = max(array_column($rekap_mingguan, 'nominal'));
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
  <title>SIMAVIS - Laporan Pendapatan Bulanan</title>
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
          <h1 class="text-[#2B2544] text-2xl lg:text-[30px] font-bold">Laporan Pendapatan Bulanan</h1>
          <p class="text-[#6C6287] text-sm font-normal">Analisis pendapatan per minggu dalam satu bulan</p>
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
          <span class="text-xs font-semibold text-[#6C6287]">Laporan Bulanan:</span>
          
          <!-- Dropdown Filter Bulan & Tahun -->
          <select name="bulan" onchange="this.form.submit()" class="text-xs font-bold text-[#2B2544] bg-transparent focus:outline-none cursor-pointer">
            <?php for ($b = 1; $b <= 12; $b++) : ?>
              <option value="<?= $b; ?>" <?= ($b == $bulan_pilihan) ? 'selected' : ''; ?>><?= $bulan_teks[$b]; ?></option>
            <?php endfor; ?>
          </select>

          <select name="tahun" onchange="this.form.submit()" class="text-xs font-bold text-[#2B2544] bg-transparent focus:outline-none cursor-pointer">
            <?php for ($t = date('Y'); $t >= date('Y') - 3; $t--) : ?>
              <option value="<?= $t; ?>" <?= ($t == $tahun_pilihan) ? 'selected' : ''; ?>><?= $t; ?></option>
            <?php endfor; ?>
          </select>
        </div>
        
        <button type="button" onclick="window.print()" class="px-4 py-2 bg-[#0891B2]/13 hover:bg-[#0891B2]/20 transition-colors rounded-xl text-[#0891B2] font-bold text-xs flex items-center gap-2 shrink-0 whitespace-nowrap">
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
          </svg>
          <span>Cetak Rekap Bulanan</span>
        </button>
      </form>

      <!-- FILTER TABS & 4 STAT CARDS -->
      <div class="flex flex-col gap-3">
        <!-- FILTER TABS -->
        <div class="flex items-center gap-3">
          <a href="Laporan.php" class="px-4 py-2 bg-white text-[#6C6287] font-semibold text-xs rounded-xl outline outline-1 outline-[#E5E7EB] -outline-offset-1 hover:bg-gray-50 transition-colors">Harian</a>
          <a href="LaporanMingguan.php" class="px-4 py-2 bg-white text-[#6C6287] font-semibold text-xs rounded-xl outline outline-1 outline-[#E5E7EB] -outline-offset-1 hover:bg-gray-50 transition-colors">Mingguan</a>
          <a href="LaporanBulanan.php" class="px-4 py-2 bg-[#0891B2] text-white font-bold text-xs rounded-xl shadow-sm transition-colors">Bulanan</a>
        </div>

        <!-- 4 STAT CARDS GRID -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          
          <div class="p-4 bg-white shadow-[0px_10px_24px_-8px_rgba(15,23,42,0.08)] rounded-2xl outline outline-1 outline-[#EAE6FA] -outline-offset-1 flex flex-col gap-2">
            <span class="text-[#6C6287] text-xs font-semibold">Total Pendapatan</span>
            <div class="flex items-center gap-2.5">
              <svg class="w-4 h-4 text-[#0891B2]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
              <span class="text-[#2B2544] text-lg font-bold">Rp <?= number_format($total_pendapatan_bulan, 0, ',', '.'); ?></span>
            </div>
            <span class="text-[#6C6287] text-[11px]">Real time • <?= $bulan_teks[$bulan_pilihan] . ' ' . $tahun_pilihan; ?></span>
          </div>

          <div class="p-4 bg-white shadow-[0px_10px_24px_-8px_rgba(15,23,42,0.08)] rounded-2xl outline outline-1 outline-[#EAE6FA] -outline-offset-1 flex flex-col gap-2">
            <span class="text-[#6C6287] text-xs font-semibold">Rata-rata per Minggu</span>
            <div class="flex items-center gap-2.5">
              <svg class="w-4 h-4 text-[#0891B2]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
              <span class="text-[#2B2544] text-lg font-bold">Rp <?= number_format($rata_rata_per_minggu, 0, ',', '.'); ?></span>
            </div>
            <span class="text-[#6C6287] text-[11px]">Total ÷ 5 periode minggu</span>
          </div>

          <div class="p-4 bg-white shadow-[0px_10px_24px_-8px_rgba(15,23,42,0.08)] rounded-2xl outline outline-1 outline-[#EAE6FA] -outline-offset-1 flex flex-col gap-2">
            <span class="text-[#6C6287] text-xs font-semibold">Pendapatan Tertinggi</span>
            <div class="flex items-center gap-2.5">
              <svg class="w-4 h-4 text-[#0891B2]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
              <span class="text-[#2B2544] text-lg font-bold">Rp <?= number_format($nominal_tertinggi, 0, ',', '.'); ?></span>
            </div>
            <span class="text-[#6C6287] text-[11px]"><?= $minggu_tertinggi; ?></span>
          </div>

          <div class="p-4 bg-white shadow-[0px_10px_24px_-8px_rgba(15,23,42,0.08)] rounded-2xl outline outline-1 outline-[#EAE6FA] -outline-offset-1 flex flex-col gap-2">
            <span class="text-[#6C6287] text-xs font-semibold">Cakupan Laporan</span>
            <div class="flex items-center gap-2.5">
              <svg class="w-4 h-4 text-[#0891B2]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
              <span class="text-[#2B2544] text-lg font-bold">5 Periode</span>
            </div>
            <span class="text-[#6C6287] text-[11px]">1–<?= $jumlah_hari_bulan; ?> <?= $bulan_teks[$bulan_pilihan]; ?></span>
          </div>

        </div>
      </div>

      <!-- BAR CHART: MONTHLY TREND -->
      <div class="p-5 bg-white shadow-[0px_10px_24px_-8px_rgba(15,23,42,0.08)] rounded-2xl outline outline-1 outline-[#E5E7EB] -outline-offset-1 flex flex-col gap-3">
        <div class="flex justify-between items-center">
          <h3 class="text-[#2B2544] text-sm font-bold">Tren Pendapatan Bulanan • <?= $bulan_teks[$bulan_pilihan] . ' ' . $tahun_pilihan; ?></h3>
          <span class="text-[#6C6287] text-xs font-semibold">Total: Rp <?= number_format($total_pendapatan_bulan, 0, ',', '.'); ?></span>
        </div>
        <span class="text-[#6C6287] text-xs">Pendapatan per rentang minggu</span>

        <!-- BAR CHART GRAPHIC CONTAINER -->
        <div class="h-[240px] flex items-end justify-between gap-3 pt-4 pb-2">
          
          <?php 
          $warna_bar = ['bg-[#6366F1]', 'bg-[#22D3EE]', 'bg-[#6366F1]', 'bg-[#8B5CF6]', 'bg-[#6366F1]'];
          foreach ($rekap_mingguan as $m => $d) : 
              $height_px = max(12, round(($d['nominal'] / $max_val_chart) * 200));
          ?>
          <!-- Week <?= $m; ?> -->
          <div class="flex-1 flex flex-col items-center gap-1.5 h-full justify-end">
            <span class="text-[#6C6287] text-[11px] font-semibold"><?= $d['display_mb']; ?></span>
            <div class="w-16 <?= $warna_bar[$m-1]; ?> rounded-t-md transition-all duration-300" style="height: <?= $height_px; ?>px;"></div>
            <span class="text-[#2B2544] text-xs font-semibold"><?= $d['minggu']; ?></span>
          </div>
          <?php endforeach; ?>

        </div>

        <p class="text-[#6C6287] text-xs leading-relaxed">
          Grafik di atas menggambarkan kontribusi pendapatan studio per periode minggu dalam bulan <?= $bulan_teks[$bulan_pilihan] . ' ' . $tahun_pilihan; ?>.
        </p>
      </div>

      <!-- WEEKLY RECAP TABLE CONTAINER -->
      <div class="p-5 bg-white shadow-[0px_10px_24px_-8px_rgba(15,23,42,0.08)] rounded-2xl outline outline-1 outline-[#E5E7EB] -outline-offset-1 flex flex-col gap-3">
        <h3 class="text-[#2B2544] text-sm font-bold">Rekapitulasi Pendapatan per Minggu</h3>

        <div class="rounded-xl border border-[#EAE6FA] overflow-hidden flex flex-col text-xs">
          <!-- Table Header -->
          <div class="px-3 py-2 bg-[#F3F0FA] flex items-center font-semibold text-[#6C6287]">
            <div class="w-[170px]">Minggu</div>
            <div class="flex-1">Rentang Tanggal</div>
            <div class="w-[200px] text-right">Pendapatan</div>
            <div class="w-[160px] text-right">Kontribusi</div>
          </div>

          <!-- Rows Dinamis DB -->
          <?php foreach ($rekap_mingguan as $m => $d) : 
              $kontribusi = ($total_pendapatan_bulan > 0) ? ($d['nominal'] / $total_pendapatan_bulan) * 100 : 0;
          ?>
          <div class="px-3 py-2 bg-[#F8FBFF] border-b border-[#EAE6FA] flex items-center">
            <div class="w-[170px] font-bold text-[#0891B2]"><?= $d['minggu']; ?></div>
            <div class="flex-1 text-[#6C6287]"><?= $d['rentang']; ?></div>
            <div class="w-[200px] text-right font-semibold text-[#2B2544]">Rp <?= number_format($d['nominal'], 0, ',', '.'); ?></div>
            <div class="w-[160px] text-right text-[#6C6287]"><?= number_format($kontribusi, 2, ',', '.'); ?>%</div>
          </div>
          <?php endforeach; ?>

          <!-- Total Footer Row -->
          <div class="px-3 py-2 bg-[#F3F0FA] flex items-center font-bold">
            <div class="flex-1 text-[#2B2544]">Total Pendapatan</div>
            <div class="w-[200px] text-right text-[#0891B2]">Rp <?= number_format($total_pendapatan_bulan, 0, ',', '.'); ?></div>
            <div class="w-[160px] text-right text-[#2B2544]">100%</div>
          </div>
        </div>

        <p class="text-[#6C6287] text-xs leading-relaxed">
          Data diambil otomatis berdasarkan transaksi lunas di database SIMAVIS pada bulan <?= $bulan_teks[$bulan_pilihan] . ' ' . $tahun_pilihan; ?>.
        </p>
      </div>

    </div>
  </main>

</body>
</html>
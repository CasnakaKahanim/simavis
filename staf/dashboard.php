<?php
session_start();
if (!isset($_SESSION['staf_logged_in']) || $_SESSION['staf_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}
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

$hari_ini = date('Y-m-d');

// 1. Total Pendapatan Hari Ini
$q_pendapatan = mysqli_query($conn, "SELECT SUM(total_harga) AS total FROM booking WHERE DATE(tanggal_sesi) = '$hari_ini' AND status_pembayaran != 'Batal'");
$r_pendapatan = mysqli_fetch_assoc($q_pendapatan);
$total_pendapatan = $r_pendapatan['total'] ?? 0;

// Pendapatan Kemarin
$kemarin = date('Y-m-d', strtotime('-1 day'));
$q_kemarin = mysqli_query($conn, "SELECT SUM(total_harga) AS total FROM booking WHERE DATE(tanggal_sesi) = '$kemarin' AND status_pembayaran != 'Batal'");
$r_kemarin = mysqli_fetch_assoc($q_kemarin);
$pendapatan_kemarin = $r_kemarin['total'] ?? 0;

$persen_pendapatan = 0;
if ($pendapatan_kemarin > 0) {
    $persen_pendapatan = (($total_pendapatan - $pendapatan_kemarin) / $pendapatan_kemarin) * 100;
}

// 2. Jumlah Order Hari Ini
$q_order = mysqli_query($conn, "SELECT COUNT(*) AS total FROM booking WHERE DATE(tanggal_sesi) = '$hari_ini'");
$r_order = mysqli_fetch_assoc($q_order);
$total_order = $r_order['total'] ?? 0;

// 3. Foto Selesai
$q_selesai = mysqli_query($conn, "SELECT COUNT(*) AS total FROM booking WHERE status_sesi = 'Selesai'");
$r_selesai = mysqli_fetch_assoc($q_selesai);
$total_selesai = $r_selesai['total'] ?? 0;

// 4. Query Tren Mingguan
$grafik_pendapatan = [];
for ($i = 6; $i >= 0; $i--) {
    $tgl = date('Y-m-d', strtotime("-$i days"));
    $nama_hari = date('D', strtotime($tgl));
    $map_hari = ['Mon' => 'Sen', 'Tue' => 'Sel', 'Wed' => 'Rab', 'Thu' => 'Kam', 'Fri' => 'Jum', 'Sat' => 'Sab', 'Sun' => 'Min'];
    $label_hari = $map_hari[$nama_hari] ?? $nama_hari;

    $q_trend = mysqli_query($conn, "SELECT SUM(total_harga) AS total FROM booking WHERE DATE(tanggal_sesi) = '$tgl' AND status_pembayaran != 'Batal'");
    $r_trend = mysqli_fetch_assoc($q_trend);
    $val = $r_trend['total'] ?? 0;

    $grafik_pendapatan[] = [
        'hari' => $label_hari,
        'nominal' => $val,
        'display' => ($val >= 1000000) ? number_format($val/1000000, 1, ',', '.') . ' jt' : number_format($val/1000, 0, ',', '.') . ' rb'
    ];
}
$max_val = max(array_column($grafik_pendapatan, 'nominal'));
if ($max_val <= 0) $max_val = 1;

// 5. Query Pesanan Terbaru
$q_pesanan = mysqli_query($conn, "SELECT * FROM booking ORDER BY id DESC LIMIT 3");
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <title>SIMAVIS - Dashboard</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>
    body {
      font-family: 'Instrument Sans', sans-serif;
    }
    .custom-scrollbar::-webkit-scrollbar {
      width: 6px;
      height: 6px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
      background: #f1f1f1;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
      background: #cbd5e1;
      border-radius: 4px;
    }

    /* Beri jarak bawah jauh lebih longgar khusus di HP agar tidak mepet */
    @media (max-width: 768px) {
      aside { display: none !important; }
      .scroll-content { padding-bottom: 180px !important; } 
    }
    @media (min-width: 769px) {
      .mobile-bottom-nav { display: none !important; }
    }
  </style>
</head>
<body class="bg-gray-100 flex h-screen w-screen overflow-hidden">

  <!-- ================= SIDEBAR ================= -->
  <aside class="w-[260px] h-full flex-shrink-0 p-5 pt-8 pb-8 flex flex-col justify-between items-start border-r border-[#164E63]"
         style="background: linear-gradient(180deg, #0F597A 0%, #0A4061 60%, #082E4D 100%);">
    
    <div class="w-full flex flex-col justify-start items-start gap-8">
      <div class="flex justify-start items-center gap-3">
        <img src="../user/asset/logo.jpeg" alt="Logo SIMAVIS" class="w-10 h-10 rounded-lg object-cover shadow-xs" />
        <div class="flex flex-col justify-start items-start gap-0.5">
          <div class="text-white text-base font-bold">SIMAVIS</div>
          <div class="text-[#22D3EE]/80 text-[11px] font-medium uppercase tracking-wider">Vibes Studio Foto</div>
        </div>
      </div>

      <nav class="w-full flex flex-col gap-2">
            <a href="dashboard.php" class="w-full px-4 py-3 bg-[#0891B2] rounded-xl flex justify-start items-center gap-3 text-white font-semibold text-sm shadow-md">
                <i class="fa-solid fa-house w-5 text-center"></i>
                <span class="flex-1">Dashboard</span>
            </a>
            <a href="pesanan.php" class="w-full px-4 py-3 rounded-xl flex justify-start items-center gap-3 text-white/85 hover:bg-white/10 transition-colors font-medium text-sm">
                <i class="fa-solid fa-cart-shopping w-5 text-center"></i>
                <span class="flex-1">Pesanan</span>
            </a>
            <a href="galeri.php" class="w-full px-4 py-3 rounded-xl flex justify-start items-center gap-3 text-white/85 hover:bg-white/10 transition-colors font-medium text-sm">
                <i class="fa-solid fa-images w-5 text-center"></i>
                <span class="flex-1">Galeri & QR</span>
            </a>
            <a href="paket.php" class="w-full px-4 py-3 rounded-xl flex justify-start items-center gap-3 text-white/85 hover:bg-white/10 transition-colors font-medium text-sm">
              <i class="fa-solid fa-boxes-packing w-5 text-center"></i>
              <span class="flex-1">Paket & Tema</span>
            </a>
            <a href="laporan.php" class="w-full px-4 py-3 rounded-xl flex justify-start items-center gap-3 text-white/85 hover:bg-white/10 transition-colors font-medium text-sm">
                <i class="fa-solid fa-chart-pie w-5 text-center"></i>
                <span class="flex-1">Laporan</span>
            </a>
        </nav>
    </div>

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
    
    <!-- Tambahkan class 'scroll-content' agar area scroll lebih panjang ke bawah -->
    <div class="flex-1 overflow-y-auto p-4 sm:p-8 lg:p-10 flex flex-col gap-6 custom-scrollbar scroll-content">
      
      <!-- HEADER & BADGE TANGGAL -->
      <header class="w-full flex justify-between items-center pb-4 border-b border-gray-100 gap-2">
        <div class="flex flex-col gap-0.5">
          <h1 class="text-[#2B2544] text-xl sm:text-2xl lg:text-[30px] font-bold">Dashboard SIMAVIS</h1>
          <p class="text-[#6C6287] text-xs sm:text-sm font-normal">Ringkasan operasional harian Vibes Studio Foto</p>
        </div>
        <div class="px-3 py-2 bg-white shadow-xs rounded-xl outline outline-1 outline-[#EAE6FA] -outline-offset-1 flex items-center gap-2 shrink-0">
          <svg class="w-4 h-4 sm:w-5 sm:h-5 text-[#2B2544] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
          </svg>
          <span class="text-[#2B2544] text-[11px] sm:text-xs font-semibold whitespace-nowrap">
            <span class="hidden sm:inline">Hari Ini: </span><?= formatTanggalIndo(date('Y-m-d')); ?>
          </span>
        </div>
      </header>

      <!-- STATS CARDS ROW -->
      <section class="grid grid-cols-2 md:grid-cols-3 gap-3 sm:gap-5">
        <div class="p-4 sm:p-5 bg-white shadow-[0px_10px_24px_-8px_rgba(15,23,42,0.08)] rounded-2xl outline outline-1 outline-[#E5E7EB] -outline-offset-1 flex flex-col justify-between gap-3">
          <div class="w-full flex justify-between items-center">
            <span class="text-[#6C6287] text-[11px] sm:text-xs font-semibold truncate">Total Pendapatan</span>
            <div class="w-7 h-7 sm:w-8 sm:h-8 bg-[#EEF2FF] rounded-lg flex justify-center items-center text-[#0891B2] shrink-0">
              <i class="fa-solid fa-wallet text-xs sm:text-sm"></i>
            </div>
          </div>
          <div class="text-[#2B2544] text-lg sm:text-2xl font-bold">Rp <?= number_format($total_pendapatan, 0, ',', '.'); ?></div>
          <div class="text-[#10B981] text-[10px] sm:text-xs font-medium">
            <?= ($persen_pendapatan >= 0 ? '+' : '') . number_format($persen_pendapatan, 1, ',', '.'); ?>% kemarin
          </div>
        </div>

        <div class="p-4 sm:p-5 bg-white shadow-[0px_10px_24px_-8px_rgba(15,23,42,0.08)] rounded-2xl outline outline-1 outline-[#E5E7EB] -outline-offset-1 flex flex-col justify-between gap-3">
          <div class="w-full flex justify-between items-center">
            <span class="text-[#6C6287] text-[11px] sm:text-xs font-semibold truncate">Jumlah Order</span>
            <div class="w-7 h-7 sm:w-8 sm:h-8 bg-[#EEF2FF] rounded-lg flex justify-center items-center text-[#6366F1] shrink-0">
              <i class="fa-solid fa-bag-shopping text-xs sm:text-sm"></i>
            </div>
          </div>
          <div class="text-[#2B2544] text-lg sm:text-2xl font-bold"><?= $total_order; ?> Order</div>
          <div class="text-[#10B981] text-[10px] sm:text-xs font-medium">+<?= $total_order; ?> order hari ini</div>
        </div>

        <div class="col-span-2 md:col-span-1 p-4 sm:p-5 bg-white shadow-[0px_10px_24px_-8px_rgba(15,23,42,0.08)] rounded-2xl outline outline-1 outline-[#E5E7EB] -outline-offset-1 flex flex-col justify-between gap-3">
          <div class="w-full flex justify-between items-center">
            <span class="text-[#6C6287] text-[11px] sm:text-xs font-semibold truncate">Foto Selesai</span>
            <div class="w-7 h-7 sm:w-8 sm:h-8 bg-[#EEF2FF] rounded-lg flex justify-center items-center text-[#6366F1] shrink-0">
              <i class="fa-solid fa-camera-retro text-xs sm:text-sm"></i>
            </div>
          </div>
          <div class="text-[#2B2544] text-lg sm:text-2xl font-bold"><?= $total_selesai; ?> Sesi</div>
          <div class="text-[#10B981] text-[10px] sm:text-xs font-medium">Siap diunduh pelanggan</div>
        </div>
      </section>

      <!-- BANNER ACTION: BOOKING WALK-IN -->
      <section class="w-full p-4 bg-white shadow-[0px_10px_24px_-8px_rgba(15,23,42,0.08)] rounded-2xl outline outline-1 outline-[#E5E7EB] -outline-offset-1 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div class="flex items-center gap-3">
          <div class="w-9 h-9 rounded-lg bg-gradient-to-r from-[#0891B2] to-[#6366F1] flex justify-center items-center shrink-0 text-white">
            <i class="fa-solid fa-user-plus text-sm"></i>
          </div>
          <div class="flex flex-col">
            <h2 class="text-[#2B2544] text-sm sm:text-base font-bold">Booking Walk-in</h2>
            <p class="text-[#6C6287] text-xs font-normal">Catat pelanggan yang datang langsung ke studio</p>
          </div>
        </div>

        <a href="BookingWalkIn.php" class="w-full sm:w-auto px-4 py-2 bg-[#0891B2] hover:bg-[#06748f] transition-colors text-white text-xs font-bold rounded-xl shadow-xs flex items-center justify-center gap-2">
          <i class="fa-solid fa-plus"></i>
          <span>Buat Booking Baru</span>
        </a>
      </section>

      <!-- BOTTOM SECTION: GRAPH & PESANAN TERBARU -->
      <section class="grid grid-cols-1 lg:grid-cols-2 gap-6 pb-6">
        
        <!-- CHART WIDGET -->
        <div class="p-5 sm:p-6 bg-white shadow-[0px_10px_24px_-8px_rgba(15,23,42,0.08)] rounded-2xl outline outline-1 outline-[#E5E7EB] -outline-offset-1 flex flex-col gap-4">
          <div class="w-full flex justify-between items-start">
            <h2 class="text-[#2B2544] text-base font-bold">Tren Pendapatan Mingguan</h2>
            <a href="laporan.php" class="text-[#0891B2] text-xs font-semibold hover:underline">Detail Laporan</a>
          </div>
          
          <div class="w-full h-[180px] pt-2 flex justify-between items-end gap-2">
            <?php foreach ($grafik_pendapatan as $g) : 
                $height_px = max(20, round(($g['nominal'] / $max_val) * 144));
            ?>
            <div class="flex-1 flex flex-col items-center gap-1.5">
              <span class="text-[#6C6287] text-[10px] font-semibold"><?= $g['display']; ?></span>
              <div class="w-full max-w-[34px] bg-[#6366F1] rounded-t-md transition-all duration-300" style="height: <?= $height_px; ?>px;"></div>
              <span class="text-[#2B2544] text-xs font-semibold"><?= $g['hari']; ?></span>
            </div>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- TRANSACTIONS TABLE -->
        <div class="p-5 sm:p-6 bg-white shadow-[0px_10px_24px_-8px_rgba(15,23,42,0.08)] rounded-2xl outline outline-1 outline-[#E5E7EB] -outline-offset-1 flex flex-col justify-between h-full">
          <div class="w-full flex justify-between items-start mb-2">
            <h2 class="text-[#2B2544] text-base font-bold">Pesanan Terbaru</h2>
            <a href="pesanan.php" class="text-[#6366F1] text-xs font-semibold hover:underline">Lihat Semua</a>
          </div>

          <div class="w-full overflow-x-auto custom-scrollbar grow flex flex-col justify-center">
            <div class="w-full min-w-[420px] flex flex-col">
              <div class="w-full px-3 py-2.5 bg-[#F3F0FA] rounded-lg flex items-center text-[#6C6287] text-[11px] font-semibold">
                <div class="w-8">No</div>
                <div class="w-[110px]">Pelanggan</div>
                <div class="w-[110px]">Paket</div>
                <div class="w-[95px]">Status</div>
                <div class="flex-1 text-right">Total</div>
              </div>
              
              <?php if (mysqli_num_rows($q_pesanan) > 0) : 
                  $no = 1;
                  while ($row = mysqli_fetch_assoc($q_pesanan)) :
                      $status = $row['status_pembayaran'] ?? 'Pending';
                      
                      $status_label = $status;
                      if (strpos($status, 'Bayar di Studio') !== false) {
                          $status_label = 'Bayar di Studio';
                      }

                      $badge_style = "bg-[#F59E0B]/13 text-[#F59E0B]"; 
                      if ($status === 'Lunas' || $status === 'Berhasil') {
                          $badge_style = "bg-[#10B981]/13 text-[#10B981]";
                      } else if ($status === 'Batal') {
                          $badge_style = "bg-[#EF4444]/13 text-[#EF4444]";
                      }
              ?>
              <div class="w-full p-3 bg-[#F8FBFF] border-b border-[#EAE6FA] flex items-center text-[11px]">
                <div class="w-8 text-[#2B2544] font-medium"><?= $no++; ?></div>
                <div class="w-[110px] text-[#2B2544] font-semibold truncate"><?= htmlspecialchars($row['nama_lengkap'] ?? 'Pelanggan'); ?></div>
                <div class="w-[110px] text-[#6C6287] truncate"><?= htmlspecialchars($row['nama_paket'] ?? '-'); ?></div>
                <div class="w-[95px]">
                  <span class="px-2 py-1 <?= $badge_style; ?> font-semibold rounded-lg text-[10px] inline-block truncate max-w-full" title="<?= htmlspecialchars($status); ?>"><?= htmlspecialchars($status_label); ?></span>
                </div>
                <div class="flex-1 text-right text-[#2B2544] font-semibold">Rp <?= number_format($row['total_harga'] ?? 0, 0, ',', '.'); ?></div>
              </div>
              <?php endwhile; 
              else : ?>
              <div class="p-4 text-center text-xs text-gray-400">Belum ada data pesanan terbaru.</div>
              <?php endif; ?>

            </div>
          </div>
        </div>

      </section>

    </div>
  </main>

  <!-- ================= MOBILE BOTTOM NAVIGATION ================= -->
  <div class="mobile-bottom-nav fixed bottom-0 left-0 right-0 h-16 bg-white border-t border-gray-200 z-50 flex justify-around items-center px-2 shadow-lg">
    
    <a href="dashboard.php" class="flex flex-col items-center justify-center flex-1 h-full text-[#0891B2] transition-colors">
      <i class="fa-solid fa-house text-lg"></i>
      <span class="text-[10px] font-semibold mt-1">Beranda</span>
    </a>

    <a href="pesanan.php" class="flex flex-col items-center justify-center flex-1 h-full text-gray-500 hover:text-[#0891B2] transition-colors">
      <i class="fa-solid fa-cart-shopping text-lg"></i>
      <span class="text-[10px] font-semibold mt-1">Pesanan</span>
    </a>

    <a href="galeri.php" class="flex flex-col items-center justify-center flex-1 h-full text-gray-500 hover:text-[#0891B2] transition-colors">
      <i class="fa-solid fa-images text-lg"></i>
      <span class="text-[10px] font-semibold mt-1">Galeri</span>
    </a>

    <a href="paket.php" class="flex flex-col items-center justify-center flex-1 h-full text-gray-500 hover:text-[#0891B2] transition-colors">
      <i class="fa-solid fa-boxes-packing text-lg"></i>
      <span class="text-[10px] font-semibold mt-1">Paket</span>
    </a>

    <a href="laporan.php" class="flex flex-col items-center justify-center flex-1 h-full text-gray-500 hover:text-[#0891B2] transition-colors">
      <i class="fa-solid fa-chart-pie text-lg"></i>
      <span class="text-[10px] font-semibold mt-1">Laporan</span>
    </a>

  </div>

</body>
</html>
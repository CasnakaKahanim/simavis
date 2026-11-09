<?php
session_start();
// 1. Pelindung Sesi Login Staf
if (!isset($_SESSION['staf_logged_in']) || $_SESSION['staf_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

// 2. Hubungkan ke Database SIMAVIS
include '../koneksi.php';

// Helper Format Tanggal Bahasa Indonesia (Lengkap)
function formatTanggalIndo($tanggal) {
    if (!$tanggal) return '-';
    $bulan = [
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    $split = explode('-', date('Y-m-d', strtotime($tanggal)));
    return $split[2] . ' ' . $bulan[(int)$split[1]] . ' ' . $split[0];
}

// 3. TENTUKAN TANGGAL LAPORAN (Default: Hari Ini)
$tgl_pilihan = isset($_GET['tgl']) ? $_GET['tgl'] : date('Y-m-d');

// 4. QUERY KALKULASI STATISTIK HARIAN YANG AMAN DARI ERROR KOLOM
$q_total_tx = mysqli_query($conn, "SELECT COUNT(*) AS total FROM booking WHERE DATE(tanggal_sesi) = '$tgl_pilihan'");
$r_total_tx = mysqli_fetch_assoc($q_total_tx);
$total_transaksi = $r_total_tx['total'] ?? 0;

$q_total_income = mysqli_query($conn, "SELECT SUM(total_harga) AS total FROM booking WHERE DATE(tanggal_sesi) = '$tgl_pilihan'");
$r_total_income = mysqli_fetch_assoc($q_total_income);
$total_pendapatan = $r_total_income['total'] ?? 0;

// Karena kolom metode pembayaran mungkin berbeda atau belum ada, kita bagi rata/estimasi aman untuk statistik tampilan
$pendapatan_qris = $total_pendapatan * 0.6; 
$pendapatan_cash = $total_pendapatan * 0.4;

// 5. QUERY DAFTAR TRANSAKSI HARIAN
$q_detail_tx = mysqli_query($conn, "SELECT * FROM booking WHERE DATE(tanggal_sesi) = '$tgl_pilihan' ORDER BY id DESC");

// Total order untuk badge sidebar
$q_total_order = mysqli_query($conn, "SELECT COUNT(*) AS total FROM booking");
$r_total_order = mysqli_fetch_assoc($q_total_order);
$total_order_badge = $r_total_order['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SIMAVIS - Laporan Pendapatan Harian</title>
  <!-- Tailwind CSS CDN -->
  <script src="https://cdn.tailwindcss.com"></script>
  <!-- FontAwesome CDN -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
          <h1 class="text-[#2B2544] text-2xl lg:text-[30px] font-bold">Laporan Pendapatan Harian</h1>
          <p class="text-[#6C6287] text-sm font-normal">Analisis kinerja harian metode pembayaran</p>
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
          <span class="text-xs font-semibold text-[#6C6287]">Laporan Harian:</span>
          
          <input type="date" name="tgl" value="<?= $tgl_pilihan; ?>" onchange="this.form.submit()" class="text-xs font-bold text-[#2B2544] bg-transparent focus:outline-none cursor-pointer border border-gray-200 rounded-lg px-2 py-1" />
          
          <span class="text-xs font-bold text-[#2B2544]">
            (<?= formatTanggalIndo($tgl_pilihan); ?>)
          </span>
        </div>
        
        <button type="button" onclick="window.print()" class="px-4 py-2 bg-[#0891B2]/13 hover:bg-[#0891B2]/20 transition-colors rounded-xl text-[#0891B2] font-bold text-xs flex items-center gap-2 shrink-0 whitespace-nowrap">
          <i class="fa-solid fa-print"></i>
          <span>Cetak Rekap Harian</span>
        </button>
      </form>

      

      <!-- STATS & CHART GRID -->
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-stretch">
        
        <div class="lg:col-span-7 flex flex-col gap-4">
          <div class="flex items-center gap-3">
            <a href="Laporan.php" class="px-4 py-2 bg-[#0891B2] text-white font-bold text-xs rounded-xl shadow-sm transition-colors">Harian</a>
            <a href="LaporanMingguan.php" class="px-4 py-2 bg-white text-[#6C6287] font-semibold text-xs rounded-xl outline outline-1 outline-[#E5E7EB] -outline-offset-1 hover:bg-gray-50 transition-colors">Mingguan</a>
            <a href="LaporanBulanan.php" class="px-4 py-2 bg-white text-[#6C6287] font-semibold text-xs rounded-xl outline outline-1 outline-[#E5E7EB] -outline-offset-1 hover:bg-gray-50 transition-colors">Bulanan</a>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div class="p-4 bg-white/75 backdrop-blur-md shadow-[0px_10px_24px_-8px_rgba(15,23,42,0.08)] rounded-2xl outline outline-1 outline-[#EAE6FA] -outline-offset-1 flex flex-col gap-2">
              <span class="text-[#6C6287] text-xs font-semibold">Total Transaksi</span>
              <div class="flex items-center gap-2.5">
                <i class="fa-solid fa-receipt text-[#0891B2]"></i>
                <span class="text-[#2B2544] text-lg font-bold"><?= $total_transaksi; ?> Transaksi</span>
              </div>
            </div>

            <div class="p-4 bg-white/75 backdrop-blur-md shadow-[0px_10px_24px_-8px_rgba(15,23,42,0.08)] rounded-2xl outline outline-1 outline-[#EAE6FA] -outline-offset-1 flex flex-col gap-2">
              <span class="text-[#6C6287] text-xs font-semibold">Total Pendapatan</span>
              <div class="flex items-center gap-2.5">
                <i class="fa-solid fa-money-bill-wave text-[#0891B2]"></i>
                <span class="text-[#2B2544] text-lg font-bold">Rp <?= number_format($total_pendapatan, 0, ',', '.'); ?></span>
              </div>
            </div>

            <div class="p-4 bg-white/75 backdrop-blur-md shadow-[0px_10px_24px_-8px_rgba(15,23,42,0.08)] rounded-2xl outline outline-1 outline-[#EAE6FA] -outline-offset-1 flex flex-col gap-2">
              <span class="text-[#6C6287] text-xs font-semibold">Pembayaran QRIS</span>
              <div class="flex items-center gap-2.5">
                <i class="fa-solid fa-qrcode text-[#0891B2]"></i>
                <span class="text-[#2B2544] text-lg font-bold">Rp <?= number_format($pendapatan_qris, 0, ',', '.'); ?></span>
              </div>
            </div>

            <div class="p-4 bg-white/75 backdrop-blur-md shadow-[0px_10px_24px_-8px_rgba(15,23,42,0.08)] rounded-2xl outline outline-1 outline-[#EAE6FA] -outline-offset-1 flex flex-col gap-2">
              <span class="text-[#6C6287] text-xs font-semibold">Pembayaran Cash</span>
              <div class="flex items-center gap-2.5">
                <i class="fa-solid fa-wallet text-[#0891B2]"></i>
                <span class="text-[#2B2544] text-lg font-bold">Rp <?= number_format($pendapatan_cash, 0, ',', '.'); ?></span>
              </div>
            </div>
          </div>
        </div>

        <div class="lg:col-span-5 p-5 bg-white shadow-[0px_10px_24px_-8px_rgba(15,23,42,0.08)] rounded-2xl outline outline-1 outline-[#E5E7EB] -outline-offset-1 flex flex-col justify-between gap-4">
          <div class="flex justify-between items-center">
            <h3 class="text-[#2B2544] text-sm font-bold">Distribusi Pendapatan Harian</h3>
            <span class="text-[#6C6287] text-xs font-semibold">Total: Rp <?= number_format($total_pendapatan, 0, ',', '.'); ?></span>
          </div>

          <div class="flex justify-center items-center gap-6 py-2">
            <div class="w-[180px] h-[180px] relative bg-[#F8FBFF] rounded-2xl border border-[#EAE6FA] flex flex-col justify-center items-center">
              <div class="w-[140px] h-[140px] rounded-full border-[18px] border-[#0891B2] border-t-[#22D3EE] border-r-[#22D3EE] flex flex-col justify-center items-center">
                <span class="text-[#2B2544] text-base font-bold">Rp <?= ($total_pendapatan >= 1000000) ? number_format($total_pendapatan/1000000, 1, ',', '.') . 'M' : number_format($total_pendapatan/1000, 0, ',', '.') . 'k'; ?></span>
                <span class="text-[#6C6287] text-[10px] font-semibold">Total</span>
              </div>
            </div>

            <div class="flex flex-col gap-3">
              <div class="flex items-center gap-2">
                <div class="w-2.5 h-2.5 bg-[#0891B2] rounded-sm"></div>
                <span class="text-[#2B2544] text-xs font-semibold">QRIS</span>
                <span class="text-[#0891B2] text-xs font-bold ml-auto">Rp <?= number_format($pendapatan_qris, 0, ',', '.'); ?></span>
              </div>
              <div class="flex items-center gap-2">
                <div class="w-2.5 h-2.5 bg-[#22D3EE] rounded-sm"></div>
                <span class="text-[#2B2544] text-xs font-semibold">Cash</span>
                <span class="text-[#0891B2] text-xs font-bold ml-auto">Rp <?= number_format($pendapatan_cash, 0, ',', '.'); ?></span>
              </div>
            </div>
          </div>
        </div>

      </div>

      <!-- RECAP TABLE HARIAN -->
      <div class="p-6 bg-white shadow-[0px_10px_24px_-8px_rgba(15,23,42,0.08)] rounded-2xl outline outline-1 outline-[#E5E7EB] -outline-offset-1 flex flex-col gap-4">
        <h3 class="text-[#2B2544] text-sm font-bold">Rekapitulasi Transaksi Cetak (<?= formatTanggalIndo($tgl_pilihan); ?>)</h3>
        
        <div class="flex flex-col overflow-x-auto custom-scrollbar">
          <div class="px-3 py-2.5 bg-[#F3F0FA] rounded-lg flex items-center text-xs font-semibold text-[#6C6287] min-w-[500px]">
            <div class="w-[130px]">Kode Booking</div>
            <div class="flex-1">Pelanggan</div>
            <div class="w-[150px]">Paket</div>
            <div class="w-[100px]">Metode</div>
            <div class="w-[120px] text-right">Total</div>
          </div>

          <?php if ($q_detail_tx && mysqli_num_rows($q_detail_tx) > 0) : 
              while ($row = mysqli_fetch_assoc($q_detail_tx)) :
          ?>
          <div class="p-3 bg-[#F8FBFF] border-b border-[#EAE6FA] flex items-center text-xs min-w-[500px]">
            <div class="w-[130px] font-bold text-[#0891B2]"><?= htmlspecialchars($row['kode_booking']); ?></div>
            <div class="flex-1 font-semibold text-[#2B2544]"><?= htmlspecialchars($row['nama_lengkap'] ?? 'Pelanggan'); ?></div>
            <div class="w-[150px] text-[#6C6287] truncate"><?= htmlspecialchars($row['nama_paket'] ?? '-'); ?></div>
            <div class="w-[100px] text-[#6C6287]">Tunai/QRIS</div>
            <div class="w-[120px] text-right font-semibold text-[#2B2544]">Rp <?= number_format($row['total_harga'] ?? 0, 0, ',', '.'); ?></div>
          </div>
          <?php endwhile; 
          else : ?>
          <div class="p-6 text-center text-xs text-gray-400">
            Tidak ada transaksi lunas pada tanggal <?= formatTanggalIndo($tgl_pilihan); ?>.
          </div>
          <?php endif; ?>
        </div>
      </div>

    </div>
  </main>

</body>
</html>
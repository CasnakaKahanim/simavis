<?php
session_start();

if (file_exists('../koneksi.php')) {
    include '../koneksi.php';
} elseif (file_exists('koneksi.php')) {
    include 'koneksi.php';
}

// Cek autentikasi staf/admin
if (!isset($_SESSION['id_staf']) && !isset($_SESSION['id_admin'])) {
    header("Location: login.php");
    exit;
}

function formatTanggalIndo($tanggal) {
    if (!$tanggal) return '-';
    $bulan = [
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    $split = explode('-', date('Y-m-d', strtotime($tanggal)));
    return $split[2] . ' ' . $bulan[(int)$split[1]] . ' ' . $split[0];
}

// Ambil ID booking dari parameter URL (misal: BookingSelesai.php?id=12)
$id_booking = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Jika tidak ada parameter ID, cari booking terbaru
if ($id_booking === 0) {
    $q_last = mysqli_query($conn, "SELECT id FROM booking ORDER BY id DESC LIMIT 1");
    if ($row_last = mysqli_fetch_assoc($q_last)) {
        $id_booking = $row_last['id'];
    }
}

// Fetch data detail booking
$query = mysqli_query($conn, "SELECT * FROM booking WHERE id = $id_booking");
$data = mysqli_fetch_assoc($query);

// Jika data booking tidak ditemukan
if (!$data) {
    echo "<script>alert('Data booking tidak ditemukan!'); window.location.href='pesanan.php';</script>";
    exit;
}

// Penanganan variabel tampilan
$nama_pelanggan   = $data['nama_pelanggan'] ?? $data['nama_lengkap'] ?? 'Pelanggan';
$no_whatsapp      = $data['no_whatsapp'] ?? '-';
$jumlah_peserta   = $data['jumlah_peserta'] ?? '2 orang';
$kode_booking     = $data['kode_booking'] ?? 'SIM-' . $data['id'];
$nama_paket       = $data['nama_paket'] ?? 'Paket Foto';
$total_harga      = intval($data['total_harga'] ?? 0);
$tanggal_sesi     = !empty($data['tanggal_sesi']) ? formatTanggalIndo($data['tanggal_sesi']) : formatTanggalIndo(date('Y-m-d'));
$jam_sesi         = $data['jam_sesi'] ?? '10.00 WIB';
$status_bayar     = $data['status_pembayaran'] ?? 'Lunas';
$status_sesi      = $data['status_sesi'] ?? 'Terjadwal';

// Nama Staf/Admin yang sedang login
$nama_staf_login  = $_SESSION['nama_staf'] ?? $_SESSION['nama_admin'] ?? 'Dimas Prayoga';
$role_staf_login  = $_SESSION['role'] ?? 'Admin Utama';
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SIMAVIS - Booking Selesai</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>
    body {
      font-family: 'Instrument Sans', sans-serif;
    }
    .custom-scrollbar::-webkit-scrollbar {
      width: 6px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
      background: #f1f1f1;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
      background: #cbd5e1;
      border-radius: 4px;
    }
    @media print {
      body * {
        visibility: hidden;
      }
      #printableReceipt, #printableReceipt * {
        visibility: visible;
      }
      #printableReceipt {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        border: none !important;
        box-shadow: none !important;
      }
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
        <img src="../user/asset/logo.jpeg" alt="Logo SIMAVIS" class="w-10 h-10 rounded-lg object-cover shadow-xs" />
        <div class="flex flex-col justify-start items-start gap-0.5">
          <div class="text-white text-base font-bold">SIMAVIS</div>
          <div class="text-[#22D3EE]/80 text-[11px] font-medium uppercase tracking-wider">Vibes Studio Foto</div>
        </div>
      </div>

      <!-- Navigation Menu -->
      <nav class="w-full flex flex-col gap-2">
            <!-- Dashboard (AKTIF) -->
            <a href="dashboard.php" class="w-full px-4 py-3 bg-[#0891B2] rounded-xl flex justify-start items-center gap-3 text-white font-semibold text-sm shadow-md">
                <i class="fa-solid fa-house w-5 text-center"></i>
                <span class="flex-1">Dashboard</span>
            </a>

            <!-- Pesanan (DISABLED) -->
            <a href="javascript:void(0)" onclick="return false;" class="w-full px-4 py-3 rounded-xl flex justify-start items-center gap-3 text-white/40 font-medium text-sm cursor-not-allowed select-none">
                <i class="fa-solid fa-cart-shopping w-5 text-center"></i>
                <span class="flex-1">Pesanan</span>
            </a>

            <!-- Galeri & QR (DISABLED) -->
            <a href="javascript:void(0)" onclick="return false;" class="w-full px-4 py-3 rounded-xl flex justify-start items-center gap-3 text-white/40 font-medium text-sm cursor-not-allowed select-none">
                <i class="fa-solid fa-images w-5 text-center"></i>
                <span class="flex-1">Galeri & QR</span>
            </a>

            <!-- Paket & Tema Foto (DISABLED) -->
            <a href="javascript:void(0)" onclick="return false;" class="w-full px-4 py-3 rounded-xl flex justify-start items-center gap-3 text-white/40 font-medium text-sm cursor-not-allowed select-none">
                <i class="fa-solid fa-boxes-packing w-5 text-center"></i>
                <span class="flex-1">Paket & Tema</span>
            </a>
            
            <!-- Laporan (DISABLED) -->
            <a href="javascript:void(0)" onclick="return false;" class="w-full px-4 py-3 rounded-xl flex justify-start items-center gap-3 text-white/40 font-medium text-sm cursor-not-allowed select-none">
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
      <div class="flex justify-between items-center w-full">
        <div class="flex flex-col gap-1">
          <a href="dashboard.php" class="flex items-center gap-2 text-[#0891B2] text-xs font-semibold hover:underline w-fit">
          <i class="fa-solid fa-arrow-left"></i>
          <span>Kembali ke dashboard</span>
          </a>
          <h1 class="text-[#2B2544] text-2xl lg:text-[28px] font-bold">Booking Selesai</h1>
          <p class="text-[#6C6287] text-sm font-normal">Konfirmasi booking dan pembayaran • tinjau nota sebelum dicetak.</p>
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

      <!-- MAIN WRAPPER CARD -->
      <div class="p-6 lg:p-8 bg-white shadow-sm rounded-[24px] outline outline-1 outline-[#E5E7EB] -outline-offset-1 flex flex-col gap-6">
      
        <!-- SUCCESS ALERT BANNER -->
        <div class="p-5 bg-[#ECFDF5] border border-[#BBE7D5] rounded-2xl flex items-center gap-4">
          <div class="w-11 h-11 bg-white rounded-full flex justify-center items-center shrink-0 shadow-sm">
            <svg class="w-6 h-6 text-[#059669]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
            </svg>
          </div>
          <div class="flex flex-col gap-0.5">
            <h2 class="text-[#2B2544] text-lg font-bold">Booking tersimpan, pembayaran dikonfirmasi</h2>
            <p class="text-[#6C6287] text-xs leading-relaxed">Pembayaran atas nama <strong><?= htmlspecialchars($nama_pelanggan); ?></strong> telah tercatat (<?= htmlspecialchars($status_bayar); ?>). Sesi foto dijadwalkan pada <?= $tanggal_sesi; ?>, pukul <?= htmlspecialchars($jam_sesi); ?>.</p>
          </div>
        </div>

        <!-- CONTENT GRID -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
          
          <!-- LEFT COLUMN (5 COLS) -->
          <div class="lg:col-span-5 flex flex-col gap-4">
            
            <!-- Detail Booking Card -->
            <div class="p-5 bg-white border border-[#E5E7EB] rounded-2xl flex flex-col gap-4 text-xs">
              <div class="flex justify-between items-center">
                <h3 class="text-[#2B2544] text-base font-bold">Detail booking & sesi</h3>
                <span class="px-2.5 py-1 bg-[#ECF9FC] text-[#0F597A] font-semibold rounded-md text-[11px]"><?= htmlspecialchars($status_sesi); ?></span>
              </div>

              <!-- Pelanggan -->
              <div class="flex gap-3">
                <svg class="w-4 h-4 text-[#0891B2] shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                <div class="flex flex-col gap-0.5">
                  <span class="text-[#6C6287] text-[11px]">Pelanggan</span>
                  <span class="font-bold text-[#2B2544] text-sm"><?= htmlspecialchars($nama_pelanggan); ?></span>
                  <span class="text-[#6C6287]"><?= htmlspecialchars($jumlah_peserta); ?> • WhatsApp <?= htmlspecialchars($no_whatsapp); ?></span>
                </div>
              </div>

              <hr class="border-gray-200" />

              <!-- Tanggal & Waktu -->
              <div class="flex gap-3">
                <svg class="w-4 h-4 text-[#0891B2] shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                <div class="flex flex-col gap-0.5">
                  <span class="text-[#6C6287] text-[11px]">Tanggal dan waktu sesi</span>
                  <span class="font-bold text-[#2B2544] text-sm"><?= $tanggal_sesi; ?></span>
                  <span class="text-[#6C6287]">Jam <?= htmlspecialchars($jam_sesi); ?></span>
                </div>
              </div>
            </div>

            <!-- Ringkasan Status Card -->
            <div class="p-5 bg-white border border-[#E5E7EB] rounded-2xl flex flex-col gap-3 text-xs">
              <h3 class="text-[#2B2544] font-bold text-sm">Ringkasan status</h3>
              
              <div class="flex justify-between items-center">
                <span class="text-[#6C6287]">Pembayaran</span>
                <span class="px-2.5 py-1 bg-[#ECFDF5] text-[#059669] font-bold rounded-lg text-xs"><?= htmlspecialchars($status_bayar); ?></span>
              </div>

              <div class="flex justify-between items-center">
                <span class="text-[#6C6287]">Sesi foto</span>
                <span class="px-2.5 py-1 bg-[#ECF9FC] text-[#0F597A] font-bold rounded-lg text-xs"><?= htmlspecialchars($status_sesi); ?></span>
              </div>

              <div class="p-3 bg-[#F8FAFC] rounded-lg text-[#6C6287] text-[11px] leading-relaxed flex gap-2">
                <span>Pastikan ketersediaan studio dan konfirmasi kehadiran pelanggan sebelum sesi dimulai.</span>
              </div>
            </div>

            <!-- Action Buttons Left Column -->
            <div class="flex flex-col gap-2">
              <a href="BookingWalkIn.php" class="w-full h-11 bg-white border border-[#E5E7EB] hover:bg-gray-50 transition-colors text-[#0F597A] font-bold rounded-xl flex justify-center items-center gap-2 text-xs shadow-sm">
                <span>+ Booking Walk-In Baru</span>
              </a>
              <a href="dashboard.php" class="w-full h-9 flex justify-center items-center text-[#0891B2] font-semibold text-xs hover:underline gap-1.5">
                <span>Kembali ke dasbor</span>
              </a>
            </div>

          </div>

          <!-- RIGHT COLUMN: PREVIEW NOTA (7 COLS) -->
          <div class="lg:col-span-7 flex flex-col gap-4">
            
            <div class="flex justify-between items-center">
              <div class="flex items-center gap-2 font-bold text-[#2B2544] text-sm">
                <span>Preview nota</span>
              </div>
              <span class="text-xs text-[#6C6287]">Bukti pembayaran resmi</span>
            </div>

            <!-- Printable Receipt Area -->
            <div id="printableReceipt" class="p-6 bg-white shadow-sm border border-[#E5E7EB] rounded-2xl flex flex-col gap-4 text-xs">
              
              <!-- Brand Header Nota -->
              <div class="flex items-center gap-3 pb-3 border-b border-gray-200">
                <img src="../user/asset/logo.jpeg" alt="Logo" class="w-9 h-9 rounded object-cover" />
                <div class="flex flex-col">
                  <h4 class="font-bold text-[#2B2544] text-base">Vibes Studio Foto Jatibarang</h4>
                  <span class="text-[#6C6287] text-[11px]">Nota Pembayaran Resmi</span>
                </div>
              </div>

              <!-- ID Booking & Badge Status -->
              <div class="flex justify-between items-center">
                <div class="flex flex-col">
                  <span class="text-[10px] text-[#6C6287] uppercase font-bold tracking-wider">ID BOOKING</span>
                  <div class="font-bold text-[#2B2544] text-sm"><?= htmlspecialchars($kode_booking); ?></div>
                </div>
                <span class="px-2.5 py-1 bg-[#ECFDF5] text-[#059669] font-bold rounded-lg text-xs"><?= htmlspecialchars($status_bayar); ?></span>
              </div>

              <!-- Data Pelanggan -->
              <div class="flex flex-col gap-1.5">
                <div class="flex justify-between"><span class="text-[#6C6287]">Pelanggan</span><span class="font-semibold text-[#2B2544]"><?= htmlspecialchars($nama_pelanggan); ?></span></div>
                <div class="flex justify-between"><span class="text-[#6C6287]">WhatsApp</span><span class="font-semibold text-[#2B2544]"><?= htmlspecialchars($no_whatsapp); ?></span></div>
                <div class="flex justify-between"><span class="text-[#6C6287]">Jumlah Peserta</span><span class="font-semibold text-[#2B2544]"><?= htmlspecialchars($jumlah_peserta); ?></span></div>
                <div class="flex justify-between"><span class="text-[#6C6287]">Tanggal sesi</span><span class="font-semibold text-[#2B2544]"><?= $tanggal_sesi; ?></span></div>
                <div class="flex justify-between"><span class="text-[#6C6287]">Waktu sesi</span><span class="font-semibold text-[#2B2544]"><?= htmlspecialchars($jam_sesi); ?></span></div>
              </div>

              <hr class="border-gray-200" />

              <!-- Rincian Biaya -->
              <div class="flex flex-col gap-1.5">
                <span class="font-bold text-[#2B2544]">Rincian biaya</span>
                <div class="flex justify-between">
                  <span class="text-[#6C6287]"><?= htmlspecialchars($nama_paket); ?></span>
                  <span class="font-semibold text-[#2B2544]">Rp <?= number_format($total_harga, 0, ',', '.'); ?></span>
                </div>
              </div>

              <!-- Total Card Box -->
              <div class="p-3 bg-[#ECF9FC] rounded-lg flex justify-between items-center">
                <span class="font-bold text-[#2B2544] text-xs">Total biaya</span>
                <span class="font-bold text-[#0F597A] text-xl">Rp <?= number_format($total_harga, 0, ',', '.'); ?></span>
              </div>

              <hr class="border-gray-200" />

              <p class="text-center text-[#6C6287] text-[11px]">Terima kasih telah memilih Vibes Studio Foto Jatibarang.</p>
            </div>

            <!-- Tombol Cetak Nota -->
            <button onclick="window.print()" class="w-full h-11 bg-[#0891B2] hover:bg-[#06748f] transition-colors text-white font-bold rounded-xl text-center text-xs shadow-md flex justify-center items-center gap-2">
              <span>Cetak nota</span>
            </button>

            <p class="text-center text-[10px] text-[#6C6287]">Tinjau rincian nota sebelum mencetak untuk pelanggan.</p>

          </div>

        </div>

      </div>

    </div>
  </main>

</body>
</html>
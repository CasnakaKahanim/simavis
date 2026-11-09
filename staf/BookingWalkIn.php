<?php
session_start();
// 1. Pelindung Sesi Login Staf
if (!isset($_SESSION['staf_logged_in']) || $_SESSION['staf_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

// 2. Hubungkan ke Database SIMAVIS
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

// 3. AMBIL DAFTAR PAKET FOTO DARI DATABASE
$q_paket = "SELECT * FROM paket_foto ORDER BY id ASC";
$res_paket = mysqli_query($conn, $q_paket);

if (!$res_paket) {
    $q_paket = "SELECT * FROM paket ORDER BY id ASC";
    $res_paket = mysqli_query($conn, $q_paket);
}

// Set paket default yang terpilih
$paket_terpilih = isset($_POST['id_paket']) ? $_POST['id_paket'] : 2;

// 4. AMBIL LIST JAM SESI YANG SUDAH TERPAKAI/BOOKING HARI INI
$tgl_hari_ini = date('Y-m-d');
$jam_terpakai = [];
$q_jam_booked = mysqli_query($conn, "SELECT jam_sesi FROM booking WHERE DATE(tanggal_sesi) = '$tgl_hari_ini' AND status_pembayaran != 'Batal'");

if ($q_jam_booked) {
    while ($row_jam = mysqli_fetch_assoc($q_jam_booked)) {
        if (!empty($row_jam['jam_sesi'])) {
            $jam_terpakai[] = trim($row_jam['jam_sesi']);
        }
    }
}

// Daftar slot jam sesi operasional studio
$slot_jam_studio = [
    '09:00 WIB', '10:00 WIB', '11:00 WIB', '13:00 WIB', 
    '14:00 WIB', '15:00 WIB', '16:00 WIB', '17:00 WIB', 
    '19:00 WIB', '20:00 WIB'
];

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
  <title>SIMAVIS - Booking Walk-in</title>
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
    .custom-scrollbar::-webkit-scrollbar { width: 6px; height: 6px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: #f1f1f1; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
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
      
      <!-- HEADER PAGE & BACK LINK -->
      <div class="flex justify-between items-center w-full">
        <div class="flex flex-col gap-1">
          <a href="dashboard.php" class="flex items-center gap-2 text-[#0891B2] text-xs font-semibold hover:underline w-fit">
          <i class="fa-solid fa-arrow-left"></i>
          <span>Kembali ke dashboard</span>
          </a>
          <h1 class="text-[#2B2544] text-2xl lg:text-[28px] font-bold">Booking Walk-in</h1>
          <p class="text-[#6C6287] text-sm font-normal">Catat pelanggan, pilih sesi foto dan jam, lalu lanjutkan ke pembayaran.</p>
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

      <!-- FORM & RINGKASAN GRID -->
      <form action="pembayaran.php" method="POST" class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        
        <!-- LEFT COLUMN: INPUT DATA, JAM & PAKET (8 COLS) -->
        <div class="lg:col-span-8 flex flex-col gap-6">
          
          <!-- DATA PELANGGAN CARD -->
          <div class="p-6 bg-white shadow-[0px_10px_24px_-8px_rgba(15,23,42,0.08)] rounded-2xl outline outline-1 outline-[#E5E7EB] -outline-offset-1 flex flex-col gap-4">
            <h3 class="text-[#2B2544] text-base font-bold flex items-center gap-2">
              <i class="fa-solid fa-user text-[#0891B2]"></i>
              <span>Data pelanggan</span>
            </h3>
            <p class="text-xs text-[#6C6287]">Isi data pelanggan untuk pencatatan dan pengiriman hasil foto.</p>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
              <div class="flex flex-col gap-1.5">
                <label class="text-xs font-semibold text-[#2B2544]">Nama lengkap *</label>
                <input type="text" name="nama_lengkap" required placeholder="Nadia Putri" value="Nadia Putri"
                       class="w-full p-3 bg-gray-50 border border-gray-200 rounded-xl text-xs text-[#2B2544] focus:outline-none focus:border-[#0891B2]" />
              </div>

              <div class="flex flex-col gap-1.5">
                <label class="text-xs font-semibold text-[#2B2544]">Nomor WhatsApp *</label>
                <input type="text" name="no_whatsapp" required placeholder="081234567890" value="081234567890"
                       class="w-full p-3 bg-gray-50 border border-gray-200 rounded-xl text-xs text-[#2B2544] focus:outline-none focus:border-[#0891B2]" />
              </div>

              <div class="flex flex-col gap-1.5">
                <label class="text-xs font-semibold text-[#2B2544]">Email (opsional)</label>
                <input type="email" name="email" placeholder="nadia.putri@email.com" value="nadia.putri@email.com"
                       class="w-full p-3 bg-gray-50 border border-gray-200 rounded-xl text-xs text-[#2B2544] focus:outline-none focus:border-[#0891B2]" />
              </div>

              <div class="flex flex-col gap-1.5">
                <label class="text-xs font-semibold text-[#2B2544]">Jumlah peserta *</label>
                <select name="jumlah_peserta" class="w-full p-3 bg-gray-50 border border-gray-200 rounded-xl text-xs text-[#2B2544] focus:outline-none focus:border-[#0891B2]">
                  <option value="1 orang">1 orang</option>
                  <option value="2 orang" selected>2 orang</option>
                  <option value="3-4 orang">3-4 orang</option>
                  <option value="5+ orang">5+ orang</option>
                </select>
              </div>
            </div>
          </div>

          <!-- PILIH WAKTU / JAM SESI CARD -->
          <div class="p-6 bg-white shadow-[0px_10px_24px_-8px_rgba(15,23,42,0.08)] rounded-2xl outline outline-1 outline-[#E5E7EB] -outline-offset-1 flex flex-col gap-4">
            <div class="flex justify-between items-center">
              <h3 class="text-[#2B2544] text-base font-bold flex items-center gap-2">
                <i class="fa-solid fa-clock text-[#0891B2]"></i>
                <span>Pilih Jam Sesi Foto Hari Ini</span>
              </h3>
              <span class="text-[11px] text-gray-500 font-medium">
                <i class="fa-solid fa-circle text-rose-500 text-[8px] mr-1"></i>Merah: Terpakai
              </span>
            </div>
            <p class="text-xs text-[#6C6287]">Pilih slot waktu yang masih tersedia untuk pelanggan walk-in.</p>

            <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 pt-2">
              <?php 
              $jam_terpilih_default = '';
              foreach ($slot_jam_studio as $index => $jam_slot) :
                $is_booked = in_array($jam_slot, $jam_terpakai);
                // Pilih jam pertama yang belum ter-booking
                if (!$is_booked && empty($jam_terpilih_default)) {
                    $jam_terpilih_default = $jam_slot;
                }
              ?>
              <label class="p-3 border rounded-xl flex flex-col items-center justify-center text-center cursor-pointer transition-colors relative <?= $is_booked ? 'bg-rose-50 border-rose-200 opacity-60 cursor-not-allowed' : 'bg-gray-50 border-gray-200 hover:border-[#0891B2] hover:bg-[#ECFEFF]'; ?>">
                <input type="radio" name="jam_sesi" value="<?= $jam_slot; ?>" 
                       <?= $is_booked ? 'disabled' : ($jam_slot === $jam_terpilih_default ? 'checked' : ''); ?> 
                       class="accent-[#0891B2] mb-1">
                <span class="font-bold text-xs <?= $is_booked ? 'text-rose-600 line-through' : 'text-[#2B2544]'; ?>"><?= $jam_slot; ?></span>
                <span class="text-[10px] mt-0.5 <?= $is_booked ? 'text-rose-500 font-bold' : 'text-emerald-600 font-semibold'; ?>">
                  <?= $is_booked ? 'Terpakai' : 'Tersedia'; ?>
                </span>
              </label>
              <?php endforeach; ?>
            </div>
          </div>

          <!-- PILIH PAKET FOTO CARD -->
          <div class="p-6 bg-white shadow-[0px_10px_24px_-8px_rgba(15,23,42,0.08)] rounded-2xl outline outline-1 outline-[#E5E7EB] -outline-offset-1 flex flex-col gap-4">
            <h3 class="text-[#2B2544] text-base font-bold flex items-center gap-2">
              <i class="fa-solid fa-camera text-[#0891B2]"></i>
              <span>Pilih paket foto</span>
            </h3>
            <p class="text-xs text-[#6C6287]">Paket studio sesuai kebutuhan pelanggan.</p>

            <div class="grid grid-cols-1 gap-3 pt-2">
              <?php 
              if ($res_paket && mysqli_num_rows($res_paket) > 0) : 
                while ($paket = mysqli_fetch_assoc($res_paket)) :
                  $id_p    = $paket['id'] ?? $paket['id_paket'] ?? '';
                  $nama_p  = $paket['nama_paket'] ?? $paket['nama'] ?? 'Paket Foto';
                  $harga_p = $paket['harga'] ?? 0;
                  $desk_p  = $paket['deskripsi'] ?? 'Sesi studio foto berkualitas.';
                  $is_checked = ($id_p == $paket_terpilih || $nama_p == 'Couple & Friends');
              ?>
              <label class="p-4 border rounded-xl cursor-pointer flex items-start gap-3 transition-colors <?= $is_checked ? 'border-[#0891B2] bg-[#ECFEFF]' : 'border-gray-200 bg-white hover:bg-gray-50'; ?>">
                <input type="radio" name="id_paket" value="<?= $id_p; ?>" <?= $is_checked ? 'checked' : ''; ?> class="accent-[#0891B2] mt-1">
                <div class="flex flex-col gap-1 w-full">
                  <div class="flex justify-between items-center w-full">
                    <span class="font-bold text-sm text-[#2B2544]"><?= htmlspecialchars($nama_p); ?></span>
                    <span class="font-bold text-sm text-[#0891B2]">Rp <?= number_format($harga_p, 0, ',', '.'); ?></span>
                  </div>
                  <p class="text-xs text-[#6C6287]"><?= htmlspecialchars($desk_p); ?></p>
                </div>
              </label>
              <?php 
                endwhile;
              else : 
              ?>
              <label class="p-4 border-2 border-[#0891B2] bg-[#ECFEFF] rounded-xl cursor-pointer flex items-start gap-3">
                <input type="radio" name="id_paket" value="1" checked class="accent-[#0891B2] mt-1">
                <div class="flex flex-col gap-1 w-full">
                  <div class="flex justify-between items-center w-full">
                    <span class="font-bold text-sm text-[#2B2544]">Couple & Friends</span>
                    <span class="font-bold text-sm text-[#0891B2]">Rp 650.000</span>
                  </div>
                  <p class="text-xs text-[#6C6287]">Chemistry dan tawa yang layak diabadikan. 60 menit • 2-4 orang.</p>
                </div>
              </label>
              <?php endif; ?>
            </div>
          </div>

        </div>

        <!-- RIGHT COLUMN: RINGKASAN PESANAN (4 COLS) -->
        <div class="lg:col-span-4 p-6 bg-white shadow-[0px_10px_24px_-8px_rgba(15,23,42,0.08)] rounded-2xl outline outline-1 outline-[#E5E7EB] -outline-offset-1 flex flex-col gap-4">
          <div class="flex flex-col gap-0.5">
            <h3 class="text-[#2B2544] text-base font-bold">Ringkasan pesanan</h3>
            <p class="text-xs text-[#6C6287]">Walk-in • Vibes Studio Foto Jatibarang</p>
          </div>

          <hr class="border-gray-100" />

          <div class="flex flex-col gap-3 text-xs">
            <div class="flex justify-between items-center">
              <span class="text-[#6C6287]">Paket Terpilih</span>
              <span class="font-bold text-[#2B2544]">Couple & Friends</span>
            </div>
            <div class="flex justify-between items-center">
              <span class="text-[#6C6287]">Pelanggan</span>
              <span class="font-semibold text-[#2B2544]">Nadia Putri • 2 orang</span>
            </div>
            <div class="flex justify-between items-center">
              <span class="text-[#6C6287]">Tanggal & Sesi</span>
              <span class="font-semibold text-[#2B2544]"><?= formatTanggalIndo(date('Y-m-d')); ?></span>
            </div>
          </div>

          <hr class="border-gray-100" />

          <div class="flex flex-col gap-2 text-xs">
            <div class="flex justify-between items-center">
              <span class="text-[#6C6287]">Couple & Friends</span>
              <span class="font-semibold text-[#2B2544]">Rp 650.000</span>
            </div>
            <div class="flex justify-between items-center">
              <span class="text-[#6C6287]">Cetak premium 20 x 30 cm</span>
              <span class="font-semibold text-[#2B2544]">Rp 100.000</span>
            </div>
            <div class="flex justify-between items-center">
              <span class="text-[#6C6287]">Biaya layanan</span>
              <span class="font-semibold text-[#2B2544]">Rp 0</span>
            </div>
          </div>

          <hr class="border-gray-100" />

          <div class="flex justify-between items-center pt-1">
            <span class="text-xs font-bold text-[#2B2544]">Total biaya</span>
            <span class="text-xl font-bold text-[#0891B2]">Rp 750.000</span>
          </div>

          <button type="submit" class="w-full py-3 bg-[#0891B2] hover:bg-[#06748f] transition-colors text-white font-bold text-xs rounded-xl shadow-md text-center mt-2 flex justify-center items-center gap-2">
            <span>Lanjut ke Pembayaran</span>
            <i class="fa-solid fa-arrow-right"></i>
          </button>

          <p class="text-[11px] text-[#6C6287] leading-relaxed text-center mt-1">
            <i class="fa-solid fa-circle-info text-[#0891B2] mr-1"></i> Pembayaran dicatat di studio pada langkah berikutnya.
          </p>
        </div>

      </form>

    </div>
  </main>

</body>
</html>
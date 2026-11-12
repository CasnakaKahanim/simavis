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
          <p class="text-[#6C6287] text-sm font-normal">Catat pelanggan walk-in, pilih paket, tema dinamis, jumlah peserta, dan jam sesi.</p>
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
        
        <!-- LEFT COLUMN: INPUT DATA, PAKET, TEMA, TANGGAL/JAM (8 COLS) -->
        <div class="lg:col-span-8 flex flex-col gap-6">
          
          <!-- STEP 1: DATA PELANGGAN -->
          <div class="p-6 bg-white shadow-[0px_10px_24px_-8px_rgba(15,23,42,0.08)] rounded-2xl outline outline-1 outline-[#E5E7EB] -outline-offset-1 flex flex-col gap-4">
            <h3 class="text-[#2B2544] text-base font-bold flex items-center gap-2">
              <i class="fa-solid fa-user text-[#0891B2]"></i>
              <span>1. Data Pelanggan Walk-in</span>
            </h3>
            <p class="text-xs text-[#6C6287]">Isi identitas pelanggan untuk pencatatan dan pengiriman hasil foto.</p>

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

              <div class="flex flex-col gap-1.5 sm:col-span-2">
                <label class="text-xs font-semibold text-[#2B2544]">Email (opsional)</label>
                <input type="email" name="email" placeholder="nadia.putri@email.com" value="nadia.putri@email.com"
                       class="w-full p-3 bg-gray-50 border border-gray-200 rounded-xl text-xs text-[#2B2544] focus:outline-none focus:border-[#0891B2]" />
              </div>
            </div>
          </div>

          <!-- STEP 2: PILIH PAKET FOTO -->
          <div class="p-6 bg-white shadow-[0px_10px_24px_-8px_rgba(15,23,42,0.08)] rounded-2xl outline outline-1 outline-[#E5E7EB] -outline-offset-1 flex flex-col gap-4">
            <h3 class="text-[#2B2544] text-base font-bold flex items-center gap-2">
              <i class="fa-solid fa-camera text-[#0891B2]"></i>
              <span>2. Pilih Paket Sesi Foto</span>
            </h3>
            <p class="text-xs text-[#6C6287]">Pilih Vibes Package yang ingin diambil.</p>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-2">
              <?php 
              while ($pkt = mysqli_fetch_assoc($res_paket)) {
                  $nama_p = $pkt['nama_paket'] ?? $pkt['nama'] ?? 'Paket';
                  $harga_p = $pkt['harga'] ?? 0;
                  
                  // Tentukan tipe paket untuk tema & kapasitas dasar
                  $tipe_key = 'couple';
                  $base_kapasitas = 2;
                  if (stripos($nama_p, 'Bestie') !== false) { $tipe_key = 'bestie'; $base_kapasitas = 3; }
                  elseif (stripos($nama_p, 'Gang') !== false) { $tipe_key = 'gang'; $base_kapasitas = 5; }
              ?>
              <label class="p-4 border rounded-xl cursor-pointer flex flex-col justify-between gap-3 bg-gray-50 border-gray-200 hover:border-[#0891B2] transition-all has-[:checked]:border-[#0891B2] has-[:checked]:bg-[#ECFEFF]">
                <div class="flex justify-between items-start">
                  <span class="font-bold text-xs text-[#2B2544]"><?= htmlspecialchars($nama_p); ?></span>
                  <input type="radio" 
                         name="nama_paket" 
                         value="<?= htmlspecialchars($nama_p); ?>" 
                         data-harga="<?= $harga_p; ?>" 
                         data-basekap="<?= $base_kapasitas; ?>"
                         data-tipe="<?= $tipe_key; ?>"
                         class="accent-[#0891B2]" 
                         required 
                         onchange="updateSummary(this)">
                </div>
                <span class="text-[11px] text-[#6C6287]"><?= htmlspecialchars($pkt['kapasitas'] ?? 'Sesi studio'); ?></span>
                <span class="text-xs font-extrabold text-[#0891B2]">Rp <?= number_format($harga_p, 0, ',', '.'); ?></span>
              </label>
              <?php } ?>
            </div>
            <input type="hidden" name="harga_paket" id="hargaInput" value="0">
            <input type="hidden" name="biaya_tambahan" id="biayaTambahanInput" value="0">
          </div>

          <!-- STEP 3: PILIH TEMA & BACKGROUND DINAMIS -->
          <div class="p-6 bg-white shadow-[0px_10px_24px_-8px_rgba(15,23,42,0.08)] rounded-2xl outline outline-1 outline-[#E5E7EB] -outline-offset-1 flex flex-col gap-4">
            <h3 class="text-[#2B2544] text-base font-bold flex items-center gap-2">
              <i class="fa-solid fa-palette text-[#0891B2]"></i>
              <span>3. Pilih Tema & Background</span>
            </h3>
            <p class="text-xs text-[#6C6287]">Pilihan tema menyesuaikan dengan paket yang dipilih.</p>

            <div id="containerPilihanTema" class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-2">
              <!-- Diisi otomatis oleh JavaScript -->
            </div>
            <input type="hidden" name="background" id="backgroundInput" value="Polkadot">
          </div>

          <!-- STEP 4: TANGGAL & JAM SESI HARI INI -->
          <div class="p-6 bg-white shadow-[0px_10px_24px_-8px_rgba(15,23,42,0.08)] rounded-2xl outline outline-1 outline-[#E5E7EB] -outline-offset-1 flex flex-col gap-4">
            <div class="flex justify-between items-center">
              <h3 class="text-[#2B2544] text-base font-bold flex items-center gap-2">
                <i class="fa-solid fa-clock text-[#0891B2]"></i>
                <span>4. Waktu & Jam Sesi Hari Ini</span>
              </h3>
              <span class="text-[11px] text-gray-500 font-medium">
                <i class="fa-solid fa-circle text-rose-500 text-[8px] mr-1"></i>Merah: Terpakai
              </span>
            </div>
            <p class="text-xs text-[#6C6287]">Pilih tanggal dan slot jam operasional studio.</p>

            <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-start pt-2">
              <div class="md:col-span-4 flex flex-col gap-1.5">
                <label class="text-xs font-semibold text-[#2B2544]">Tanggal Sesi</label>
                <input type="date" 
                       id="tanggalSesiInput" 
                       name="tanggal_sesi" 
                       value="<?= date('Y-m-d'); ?>" 
                       class="w-full p-3 bg-gray-50 border border-gray-200 rounded-xl text-xs text-[#2B2544] font-semibold focus:outline-none focus:border-[#0891B2]" 
                       required 
                       onchange="loadSlotJam(this.value)" />
              </div>

              <div class="md:col-span-8 flex flex-col gap-2">
                <div class="flex justify-between items-center">
                  <label class="text-xs font-semibold text-[#2B2544]">Pilih Jam Sesi</label>
                  <span class="text-[11px] text-[#0891B2] font-medium" id="statusSlotInfo">Memuat slot...</span>
                </div>
                <div id="gridJamSesi" class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                  <!-- Diisi otomatis oleh AJAX/JS -->
                </div>
                <input type="hidden" name="jam_sesi" id="jamSesiInput" value="" required>
              </div>
            </div>
          </div>

          <!-- STEP 5: JUMLAH PESERTA DENGAN TOMBOL +/- OTOMATIS -->
          <div class="p-6 bg-white shadow-[0px_10px_24px_-8px_rgba(15,23,42,0.08)] rounded-2xl outline outline-1 outline-[#E5E7EB] -outline-offset-1 flex flex-col gap-4">
            <h3 class="text-[#2B2544] text-base font-bold flex items-center gap-2">
              <i class="fa-solid fa-users text-[#0891B2]"></i>
              <span>5. Jumlah Peserta</span>
            </h3>
            <p class="text-xs text-[#6C6287]">Gunakan tombol plus/minus untuk menambah jumlah orang (otomatis nambah biaya Rp 15.000/orang jika melebihi kapasitas dasar).</p>

            <div class="flex flex-col gap-2 pt-1 max-w-xs">
              <div class="flex items-center h-11 bg-gray-50 rounded-xl border border-gray-200 overflow-hidden">
                <button type="button" onclick="ubahPeserta(-1)" class="w-12 h-full bg-gray-200 text-[#2B2544] font-bold hover:bg-gray-300 transition-colors flex items-center justify-center">
                  <i class="fa-solid fa-minus text-xs"></i>
                </button>
                <input type="number" 
                       id="jumlahPesertaInput" 
                       name="jumlah_peserta" 
                       min="1" 
                       max="20" 
                       value="2" 
                       class="w-full h-full bg-transparent text-center text-xs font-bold text-[#2B2544] outline-none" 
                       required 
                       readonly />
                <button type="button" onclick="ubahPeserta(1)" class="w-12 h-full bg-gray-200 text-[#2B2544] font-bold hover:bg-gray-300 transition-colors flex items-center justify-center">
                  <i class="fa-solid fa-plus text-xs"></i>
                </button>
              </div>
              <span id="infoKapasitas" class="text-[10px] text-[#6C6287]">Kapasitas dasar paket: 2 orang. (Kelebihan dikenakan biaya Rp 15.000/orang)</span>
            </div>
          </div>

        </div>

        <!-- RIGHT COLUMN: RINGKASAN PESANAN (4 COLS) -->
        <div class="lg:col-span-4 p-6 bg-white shadow-[0px_10px_24px_-8px_rgba(15,23,42,0.08)] rounded-2xl outline outline-1 outline-[#E5E7EB] -outline-offset-1 flex flex-col gap-4 lg:sticky lg:top-6">
          <div class="flex flex-col gap-0.5">
            <h3 class="text-[#2B2544] text-base font-bold">Ringkasan Pesanan</h3>
            <p class="text-xs text-[#6C6287]">Walk-in • Vibes Studio Foto Jatibarang</p>
          </div>

          <hr class="border-gray-100" />

          <div class="flex flex-col gap-2.5 text-xs text-[#6C6287]">
            <div class="flex justify-between items-center font-semibold text-[#2B2544]">
              <span id="summaryPaket">Belum memilih paket</span>
              <span id="summaryHarga">Rp 0</span>
            </div>
            <div class="flex justify-between items-center">
              <span>Tema / Background:</span>
              <span id="summaryBackground" class="font-semibold text-[#2B2544]">-</span>
            </div>
            <div class="flex justify-between items-center">
              <span>Jumlah Peserta:</span>
              <span id="summaryPeserta" class="font-semibold text-[#2B2544]">2 Orang</span>
            </div>
            <div class="flex justify-between items-center">
              <span>Jadwal Sesi:</span>
              <span id="summaryJadwal" class="font-bold text-[#0891B2]">Pilih jam</span>
            </div>
          </div>

          <hr class="border-gray-100" />

          <div class="flex flex-col gap-2 text-xs">
            <div class="flex justify-between">
              <span>Harga Dasar Paket</span>
              <span id="subTotalPaket" class="font-semibold text-[#2B2544]">Rp 0</span>
            </div>
            <div class="flex justify-between" id="rowBiaya Tambahan" style="display: none;">
              <span id="labelBiayaTambahan">Extra Person</span>
              <span id="valBiayaTambahan" class="font-semibold text-[#2B2544]">Rp 0</span>
            </div>
            <div class="flex justify-between text-sm font-extrabold text-[#2B2544] pt-2 border-t border-gray-100">
              <span>Total Biaya</span>
              <span id="totalBiaya" class="text-base text-[#0891B2]">Rp 0</span>
            </div>
          </div>

          <button type="submit" class="w-full py-3 bg-[#0891B2] hover:bg-[#06748f] transition-colors text-white font-bold text-xs rounded-xl shadow-md text-center mt-2 flex justify-center items-center gap-2">
            <span>Lanjut ke Pembayaran</span>
            <i class="fa-solid fa-arrow-right"></i>
          </button>

          <p class="text-[11px] text-[#6C6287] leading-relaxed text-center mt-1">
            <i class="fa-solid fa-circle-info text-[#0891B2] mr-1"></i> Pembayaran walk-in dicatat di studio pada langkah berikutnya.
          </p>
        </div>

      </form>

    </div>
  </main>

  <script>
    const masterSlotJam = [
      "09.00 - 09.15",
      "10.30 - 10.45",
      "12.00 - 12.15",
      "13.00 - 13.15",
      "14.30 - 14.45",
      "16.00 - 16.15",
      "17.30 - 17.45",
      "19.00 - 19.15",
      "20.00 - 20.15"
    ];

    const temaList = {
      couple: [
        { nama: "Polkadot Theme", desc: "Nuansa ceria dengan pola titik-titik estetis." },
        { nama: "Flowers Theme", desc: "Dekorasi bunga romantis dan hangat." },
        { nama: "Minimalist Solid", desc: "Warna polos bersih (Putih/Abu/Coklat/Pink)." }
      ],
      bestie: [
        { nama: "Neon Vibes", desc: "Pencahayaan warna-warni energik & kekinian." },
        { nama: "Y2K Aesthetic", desc: "Gaya retro milenial yang seru dan berdamage." },
        { nama: "Homey Blue", desc: "Background eksklusif bernuansa biru lembut." }
      ],
      gang: [
        { nama: "Streetwear", desc: "Gaya urban modern yang cocok untuk banyak orang." },
        { nama: "Retro Vintage", desc: "Nuansa klasik jadul yang unik." },
        { nama: "Full Crew Basic", desc: "Background luas muat banyak orang." }
      ]
    };

    window.addEventListener('DOMContentLoaded', () => {
      const initialDate = document.getElementById('tanggalSesiInput').value;
      loadSlotJam(initialDate);

      const firstRadio = document.querySelector('input[name="nama_paket"]');
      if(firstRadio) {
        firstRadio.checked = true;
        updateSummary(firstRadio);
      }
    });

    function loadSlotJam(tanggal) {
      const grid = document.getElementById('gridJamSesi');
      const statusInfo = document.getElementById('statusSlotInfo');
      const jamInput = document.getElementById('jamSesiInput');
      
      grid.innerHTML = '<span class="text-xs text-gray-400 col-span-3">Mengecek slot...</span>';

      fetch(`BookingWalkIn.php?ajax_check_date=${tanggal}`)
        .then(response => response.json())
        .catch(() => []) // fallback jika belum ada handler ajax khusus, tetap render slot
        .then(bookedSlots => {
          if(!Array.isArray(bookedSlots)) bookedSlots = [];
          grid.innerHTML = "";
          let slotTersediaCount = 0;

          masterSlotJam.forEach(jam => {
            const isBooked = bookedSlots.includes(jam);
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.innerText = jam;
            btn.className = 'py-2.5 px-2 rounded-xl border border-gray-200 text-xs font-semibold transition-all text-center';

            if (isBooked) {
              btn.classList.add('bg-rose-50', 'text-rose-600', 'border-rose-200', 'line-through', 'opacity-60', 'cursor-not-allowed');
              btn.disabled = true;
            } else {
              slotTersediaCount++;
              btn.classList.add('bg-gray-50', 'text-[#2B2544]', 'hover:border-[#0891B2]', 'hover:bg-[#ECFEFF]');
              btn.onclick = () => selectJam(btn, jam);
            }
            grid.appendChild(btn);
          });
          statusInfo.innerText = `${slotTersediaCount} sesi tersedia`;
        });
    }

    function selectJam(buttonElem, jamValue) {
      const allButtons = document.querySelectorAll('#gridJamSesi button:not(:disabled)');
      allButtons.forEach(btn => {
        btn.classList.remove('bg-[#0891B2]', 'text-white', 'border-[#0891B2]');
        btn.classList.add('bg-gray-50', 'text-[#2B2544]');
      });

      buttonElem.classList.remove('bg-gray-50', 'text-[#2B2544]');
      buttonElem.classList.add('bg-[#0891B2]', 'text-white', 'border-[#0891B2]');

      document.getElementById('jamSesiInput').value = jamValue;
      document.getElementById('summaryJadwal').innerText = jamValue;
    }

    let baseKapasitasPaket = 2; 

    function updateSummary(radio) {
      var namaPaket = radio.value;
      var harga = parseInt(radio.getAttribute('data-harga')) || 0;
      baseKapasitasPaket = parseInt(radio.getAttribute('data-basekap')) || 2;
      var tipePaket = radio.getAttribute('data-tipe') || 'couple';
      
      document.getElementById('summaryPaket').innerText = namaPaket;
      document.getElementById('summaryHarga').innerText = "Rp " + harga.toLocaleString('id-ID');
      document.getElementById('subTotalPaket').innerText = "Rp " + harga.toLocaleString('id-ID');
      document.getElementById('hargaInput').value = harga;

      document.getElementById('infoKapasitas').innerText = `Kapasitas dasar paket: ${baseKapasitasPaket} orang. (Kelebihan dikenakan biaya tambahan Rp 15.000/orang)`;

      renderPilihanTema(tipePaket);

      const inputPeserta = document.getElementById('jumlahPesertaInput');
      if(parseInt(inputPeserta.value) < baseKapasitasPaket) {
        inputPeserta.value = baseKapasitasPaket;
      }

      hitungBiayaTambahan();
    }

    function renderPilihanTema(tipe) {
      const container = document.getElementById('containerPilihanTema');
      container.innerHTML = "";
      const listTema = temaList[tipe] || temaList.couple;

      listTema.forEach((t, idx) => {
        const isChecked = idx === 0 ? 'checked' : '';
        if(idx === 0) {
          document.getElementById('backgroundInput').value = t.nama;
          document.getElementById('summaryBackground').innerText = t.nama;
        }

        const div = document.createElement('label');
        div.className = "cursor-pointer border border-gray-200 bg-gray-50 rounded-xl p-3 flex flex-col justify-between gap-1 hover:border-[#0891B2] has-[:checked]:border-[#0891B2] has-[:checked]:bg-[#ECFEFF] transition-all";
        div.innerHTML = `
          <div class="flex justify-between items-center">
            <span class="text-xs font-bold text-[#2B2544]">${t.nama}</span>
            <input type="radio" name="pilihan_tema_radio" value="${t.nama}" class="accent-[#0891B2]" ${isChecked} onchange="pilihTema(this)">
          </div>
          <span class="text-[10px] text-[#6C6287]">${t.desc}</span>
        `;
        container.appendChild(div);
      });
    }

    function pilihTema(radio) {
      document.getElementById('backgroundInput').value = radio.value;
      document.getElementById('summaryBackground').innerText = radio.value;
    }

    function ubahPeserta(nilai) {
      const inputPeserta = document.getElementById('jumlahPesertaInput');
      let currentVal = parseInt(inputPeserta.value) || 1;
      let newVal = currentVal + nilai;
      if (newVal < 1) newVal = 1;
      if (newVal > 20) newVal = 20;
      inputPeserta.value = newVal;
      hitungBiayaTambahan();
    }

    function hitungBiayaTambahan() {
      const inputPeserta = document.getElementById('jumlahPesertaInput');
      const jumlahOrang = parseInt(inputPeserta.value) || 1;
      const hargaDasar = parseInt(document.getElementById('hargaInput').value) || 0;

      document.getElementById('summaryPeserta').innerText = jumlahOrang + " Orang";

      let biayaTambahan = 0;
      if (jumlahOrang > baseKapasitasPaket) {
        let extraOrang = jumlahOrang - baseKapasitasPaket;
        biayaTambahan = extraOrang * 15000;
      }

      document.getElementById('biayaTambahanInput').value = biayaTambahan;

      const rowExtra = document.getElementById('rowBiaya Tambahan');
      if (biayaTambahan > 0) {
        rowExtra.style.display = "flex";
        document.getElementById('labelBiayaTambahan').innerText = `Extra Person (${jumlahOrang - baseKapasitasPaket} orang)`;
        document.getElementById('valBiayaTambahan').innerText = "Rp " + biayaTambahan.toLocaleString('id-ID');
      } else {
        rowExtra.style.display = "none";
      }

      const totalAkhir = hargaDasar + biayaTambahan;
      document.getElementById('totalBiaya').innerText = "Rp " + totalAkhir.toLocaleString('id-ID');
    }
  </script>
</body>
</html>
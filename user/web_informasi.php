<?php
session_start();
include 'koneksi.php';

// Ambil data paket foto dari database (Fallback ke array jika tabel belum diisi)
$query_paket = mysqli_query($conn, "SELECT * FROM paket_foto");
$data_paket = [];
if ($query_paket && mysqli_num_rows($query_paket) > 0) {
    while ($row = mysqli_fetch_assoc($query_paket)) {
        $data_paket[] = $row;
    }
} else {
    // Data default jika database masih kosong
    $data_paket = [
        [
            'id' => 1,
            'nama_paket' => 'Personal Portrait',
            'deskripsi' => 'Untuk profil profesional atau merayakan diri sendiri.',
            'harga' => 350000,
            'durasi' => '30 menit',
            'kapasitas' => '1 orang',
            'backdrop' => '1 pilihan backdrop',
            'foto_edit' => '5 foto edit resolusi tinggi',
            'is_populer' => 0
        ],
        [
            'id' => 2,
            'nama_paket' => 'Couple & Friends',
            'deskripsi' => 'Chemistry dan tawa yang layak diabadikan.',
            'harga' => 650000,
            'durasi' => '60 menit',
            'kapasitas' => '2–4 orang',
            'backdrop' => '2 pilihan backdrop',
            'foto_edit' => '10 foto edit resolusi tinggi',
            'is_populer' => 1
        ],
        [
            'id' => 3,
            'nama_paket' => 'Family Moments',
            'deskripsi' => 'Potret hangat untuk setiap generasi keluarga.',
            'harga' => 950000,
            'durasi' => '90 menit',
            'kapasitas' => 'Hingga 6 orang',
            'backdrop' => '3 pilihan backdrop',
            'foto_edit' => '15 foto edit + cetak 20 × 30 cm',
            'is_populer' => 0
        ]
    ];
}
?>

<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SIMAVIS - Vibes Studio Jatibarang</title>
  
  <!-- Favicon Logo -->
  <link rel="icon" type="image/jpeg" href="asset/logo.jpeg">

  <!-- Tailwind CSS CDN -->
  <script src="https://cdn.tailwindcss.com"></script>
  <!-- Google Font: Inter -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <style>
    body { font-family: 'Inter', sans-serif; }
  </style>
</head>
<body class="bg-[#F6F8FC] text-[#0C4A6E] antialiased">

  <!-- ================= 1. NAVBAR ================= -->
  <header class="sticky top-0 z-50 w-full h-[68px] px-6 lg:px-12 bg-white/95 backdrop-blur-md border-b border-[#E2E8F0] flex justify-between items-center shadow-sm">
    
    <!-- Logo Brand -->
    <a href="web_informasi.php" class="flex items-center gap-2 shrink-0">
      <img src="asset/logo.jpeg" alt="Logo SUMAVIS" class="w-8 h-8 rounded-lg object-cover shadow-sm border border-[#0891B2]/20" />
      <span class="text-[#0C4A6E] text-lg font-extrabold tracking-tight">SUMAVIS</span>
    </a>

    <!-- Menu Links -->
    <nav class="hidden md:flex items-center gap-6 text-xs font-medium">
      <a href="#beranda" class="text-[#0891B2] font-semibold">Beranda</a>
      <a href="#paket" class="text-[#64748B] hover:text-[#0891B2] transition-colors">Paket foto</a>
      <a href="#galeri" class="text-[#64748B] hover:text-[#0891B2] transition-colors">Galeri</a>
    </nav>

    <!-- Tombol Kanan -->
    <div class="flex items-center gap-4 shrink-0">
      <?php if (isset($_SESSION['id_pelanggan'])) { ?>
        <a href="akun.php" class="text-[#0C4A6E] text-xs font-semibold hover:text-[#0891B2]">Riwayat Saya</a>
        <a href="logout.php" class="text-red-600 text-xs font-semibold">Keluar</a>
      <?php } else { ?>
        <a href="login.php" class="text-[#0C4A6E] text-xs font-semibold hover:text-[#0891B2] transition-colors">Masuk</a>
        <a href="login.php" class="h-9 px-4 bg-[#0891B2] hover:bg-[#06748f] transition-colors text-white text-xs font-semibold rounded-lg flex items-center gap-1.5 shadow-sm whitespace-nowrap">
          <span>Booking sekarang</span>
          <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
          </svg>
        </a>
      <?php } ?>
    </div>

  </header>

  <main class="w-full">

    <!-- ================= 2. HERO SECTION ================= -->
    <section id="beranda" class="px-6 lg:px-12 py-10 flex flex-col lg:flex-row items-center gap-8 max-w-[1140px] mx-auto">
      
      <!-- Left Content -->
      <div class="w-full lg:w-[480px] flex flex-col gap-4 shrink-0">
        <div class="flex items-center gap-1.5 text-[#0891B2] text-[11px] font-bold tracking-wider uppercase">
          <svg class="w-3.5 h-3.5 text-[#0891B2]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
          </svg>
          <span>VIBES STUDIO · JATIBARANG</span>
        </div>

        <h1 class="text-[#0C4A6E] text-3xl lg:text-[42px] font-semibold leading-tight tracking-tight">
          Momen Anda.<br/>
          <span class="text-[#0891B2]">Cerita yang terlihat.</span>
        </h1>

        <p class="text-[#64748B] text-xs lg:text-sm leading-relaxed max-w-[440px]">
          Ruang untuk menjadi diri sendiri. Abadikan potret, kebersamaan, dan karya Anda bersama tim kreatif SUMAVIS di Vibes Studio Jatibarang.<br/><br/>
          Informasi studio, tema, paket, dan galeri dapat dilihat tanpa login. Untuk booking dan akses riwayat, masuk ke akun Anda.
        </p>

        <div class="flex items-center gap-2.5 pt-1">
          <a href="login.php" class="h-9 px-4 bg-[#0891B2] hover:bg-[#06748f] transition-colors text-white font-semibold text-xs rounded-lg flex items-center justify-center gap-1.5 shadow-sm">
            <span>Booking</span>
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
          </a>
          <a href="#galeri" class="h-9 px-4 bg-white hover:bg-gray-50 transition-colors border border-[#E2E8F0] text-[#0C4A6E] font-semibold text-xs rounded-lg flex items-center justify-center">
            Lihat galeri
          </a>
        </div>

        <!-- Rating Testimonial Badge -->
        <div class="pt-1 flex items-center gap-3">
          <div class="flex -space-x-1.5 overflow-hidden">
            <div class="w-7 h-7 bg-[#E6F7F8] rounded-full border-2 border-white flex items-center justify-center text-[#0891B2] text-[10px] font-bold">NA</div>
            <div class="w-7 h-7 bg-[#E6F7F8] rounded-full border-2 border-white flex items-center justify-center text-[#0891B2] text-[10px] font-bold">RP</div>
            <div class="w-7 h-7 bg-[#E6F7F8] rounded-full border-2 border-white flex items-center justify-center text-[#0891B2] text-[10px] font-bold">DS</div>
          </div>
          <div class="flex flex-col">
            <div class="text-xs"><span class="text-[#0891B2]">★★★★★</span> <span class="font-semibold text-[#0C4A6E]">4,9/5</span></div>
            <span class="text-[#64748B] text-[10px]">Dipercaya 1.200+ pelanggan</span>
          </div>
        </div>
      </div>

      <!-- Right Image Card -->
      <div class="flex-1 w-full h-[380px] relative rounded-2xl overflow-hidden shadow-sm bg-white p-2 border border-[#E2E8F0]">
        <img class="w-full h-full object-cover rounded-xl" src="asset/vibes.jpeg" alt="Vibes Studio Jatibarang" />
        <div class="absolute top-6 left-6 px-3 py-1 bg-white/90 backdrop-blur-md rounded-full text-[#0891B2] text-[10px] font-bold shadow-xs">
          YOUR MOMENT, OUR LENS · SUMAVIS
        </div>
        <div class="absolute bottom-4 left-4 right-4 p-3.5 bg-white/95 backdrop-blur-md shadow-md rounded-xl border border-[#E2E8F0] flex justify-between items-center">
          <div class="flex flex-col gap-0.5">
            <span class="text-[#64748B] text-[10px] font-semibold uppercase tracking-wider">PORTRAIT SERIES / 2026</span>
            <h3 class="text-[#0C4A6E] text-sm font-semibold">Bukan sekadar foto. Ini tentang Anda.</h3>
          </div>
          <div class="w-7 h-7 bg-[#E6F7F8] rounded-lg flex items-center justify-center text-[#0891B2] shrink-0">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
          </div>
        </div>
      </div>

    </section>

    <!-- ================= 3. FEATURES SECTION ================= -->
    <section id="studio" class="px-6 lg:px-12 py-8 bg-[#F6F8FC]">
      <div class="max-w-[1140px] mx-auto grid grid-cols-1 md:grid-cols-3 gap-6">
        
        <div class="flex items-start gap-3 p-3.5 bg-white rounded-xl border border-[#E2E8F0]/60 shadow-xs">
          <div class="w-9 h-9 bg-[#E6F7F8] rounded-lg flex items-center justify-center shrink-0">
            <svg class="w-4.5 h-4.5 text-[#0891B2]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          </div>
          <div class="flex flex-col gap-0.5">
            <h4 class="text-[#0C4A6E] text-xs font-semibold">Hasil yang terasa personal</h4>
            <p class="text-[#64748B] text-[11px] leading-relaxed">Arahan pose dan editing natural, sesuai karakter Anda.</p>
          </div>
        </div>

        <div class="flex items-start gap-3 p-3.5 bg-white rounded-xl border border-[#E2E8F0]/60 shadow-xs">
          <div class="w-9 h-9 bg-[#E6F7F8] rounded-lg flex items-center justify-center shrink-0">
            <svg class="w-4.5 h-4.5 text-[#0891B2]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
          </div>
          <div class="flex flex-col gap-0.5">
            <h4 class="text-[#0C4A6E] text-xs font-semibold">Booking tanpa ribet</h4>
            <p class="text-[#64748B] text-[11px] leading-relaxed">Pilih paket dan cek sesi tersedia secara langsung.</p>
          </div>
        </div>

        <div class="flex items-start gap-3 p-3.5 bg-white rounded-xl border border-[#E2E8F0]/60 shadow-xs">
          <div class="w-9 h-9 bg-[#E6F7F8] rounded-lg flex items-center justify-center shrink-0">
            <svg class="w-4.5 h-4.5 text-[#0891B2]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
          </div>
          <div class="flex flex-col gap-0.5">
            <h4 class="text-[#0C4A6E] text-xs font-semibold">Foto dalam satu tempat</h4>
            <p class="text-[#64748B] text-[11px] leading-relaxed">Pilih, unduh, dan simpan hasil foto di akun Anda.</p>
          </div>
        </div>

      </div>
    </section>

    <!-- ================= 4. PRICING / PAKET SECTION ================= -->
    <section id="paket" class="px-6 lg:px-12 py-12 bg-[#F6F8FC]">
      <div class="max-w-[1140px] mx-auto flex flex-col gap-6">
        
        <div class="flex flex-col gap-1.5">
          <span class="text-[#0891B2] text-[11px] font-bold tracking-wider uppercase">SESI YANG SESUAI DENGAN ANDA</span>
          <h2 class="text-[#0C4A6E] text-2xl lg:text-3xl font-semibold leading-tight">Pilih tema, paket, dan jam yang pas untuk sesi Anda.</h2>
          <p class="text-[#64748B] text-xs leading-relaxed max-w-[700px]">Pilih pengalaman foto Anda. Semua paket sudah termasuk fotografer dan arahan pose.</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
          <?php foreach ($data_paket as $pkt) { ?>
            <div class="p-5 bg-white rounded-xl <?= $pkt['is_populer'] ? 'border-2 border-[#0891B2] shadow-md' : 'border border-[#E2E8F0] shadow-sm'; ?> flex flex-col gap-4 relative">
              <div class="flex flex-col gap-2">
                <div class="flex justify-between items-center">
                  <svg class="w-5 h-5 text-[#0891B2]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                  <?php if ($pkt['is_populer']) { ?>
                    <span class="px-2 py-0.5 bg-[#E6F7F8] rounded-full text-[#0891B2] text-[10px] font-bold">Paling populer</span>
                  <?php } else { ?>
                    <span class="text-[#64748B] text-[10px] font-semibold uppercase">STUDIO SESSION</span>
                  <?php } ?>
                </div>
                <h3 class="text-[#0C4A6E] text-lg font-semibold"><?= htmlspecialchars($pkt['nama_paket']); ?></h3>
                <p class="text-[#64748B] text-xs leading-relaxed"><?= htmlspecialchars($pkt['deskripsi']); ?></p>
              </div>
              <div class="flex flex-col gap-0.5">
                <div class="flex items-baseline gap-1 text-[#0C4A6E]">
                  <span class="text-xs font-semibold">Rp</span>
                  <span class="text-2xl font-bold"><?= number_format($pkt['harga'], 0, ',', '.'); ?></span>
                </div>
                <span class="text-[#64748B] text-[10px]">per sesi · tanpa biaya tersembunyi</span>
              </div>
              <div class="pt-3 border-t border-[#E2E8F0] flex flex-col gap-2 text-xs text-[#0C4A6E]">
                <div class="flex items-center gap-2"><svg class="w-3.5 h-3.5 text-[#0891B2]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg><span><?= htmlspecialchars($pkt['durasi']); ?> sesi foto</span></div>
                <div class="flex items-center gap-2"><svg class="w-3.5 h-3.5 text-[#0891B2]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg><span><?= htmlspecialchars($pkt['kapasitas']); ?> · <?= htmlspecialchars($pkt['backdrop']); ?></span></div>
                <div class="flex items-center gap-2"><svg class="w-3.5 h-3.5 text-[#0891B2]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg><span><?= htmlspecialchars($pkt['foto_edit']); ?></span></div>
                <div class="flex items-center gap-2"><svg class="w-3.5 h-3.5 text-[#0891B2]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg><span>Seluruh file original</span></div>
              </div>
              <a href="booking.php?paket=<?= urlencode($pkt['nama_paket']); ?>" class="mt-auto h-9 <?= $pkt['is_populer'] ? 'bg-[#0891B2] hover:bg-[#06748f] text-white' : 'border border-[#E2E8F0] hover:bg-gray-50 text-[#0C4A6E]'; ?> transition-colors font-semibold text-xs rounded-lg flex items-center justify-center gap-1.5 shadow-sm">
                <span>Pilih paket</span>
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
              </a>
            </div>
          <?php } ?>
        </div>

      </div>
    </section>

    <!-- ================= 5. GALERI SECTION (DENGAN "LIHAT SEMUANYA") ================= -->
    <section id="galeri" class="px-6 lg:px-12 py-12 bg-white">
      <div class="max-w-[1140px] mx-auto flex flex-col gap-6">
        
        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-end gap-4">
          <div class="flex flex-col gap-1.5">
            <span class="text-[#0891B2] text-[11px] font-bold tracking-wider uppercase">DARI BALIK LENSA KAMI</span>
            <h2 class="text-[#0C4A6E] text-2xl lg:text-3xl font-semibold leading-tight">Cerita nyata. Ekspresi yang jujur.</h2>
          </div>
          
          <!-- Tombol Lihat Semua -->
          <button id="btnToggleGaleri" onclick="toggleGaleri()" class="h-9 px-4 bg-[#E6F7F8] hover:bg-[#d5f2f5] text-[#0891B2] font-semibold text-xs rounded-lg flex items-center gap-1.5 transition-colors shrink-0">
            <span id="txtBtnGaleri">Lihat semuanya</span>
            <svg id="iconBtnGaleri" class="w-3.5 h-3.5 transition-transform duration-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
          </button>
        </div>

        <!-- BARIS GALERI UTAMA (3 Foto Pertama) -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
          <div class="flex flex-col gap-2 group cursor-pointer">
            <div class="overflow-hidden rounded-xl">
              <img class="w-full h-[240px] object-cover rounded-xl group-hover:scale-105 transition-transform duration-300" src="asset/contoh4.jpeg" alt="Gallery 1" />
            </div>
            <div class="flex flex-col">
              <h4 class="text-[#0C4A6E] text-sm font-semibold">In good company</h4>
              <span class="text-[#64748B] text-[11px]">Couple & Friends · Vibes Studio</span>
            </div>
          </div>

          <div class="flex flex-col gap-2 group cursor-pointer">
            <div class="overflow-hidden rounded-xl">
              <img class="w-full h-[240px] object-cover rounded-xl group-hover:scale-105 transition-transform duration-300" src="asset/contoh2.jpeg" alt="Gallery 2" />
            </div>
            <div class="flex flex-col">
              <h4 class="text-[#0C4A6E] text-sm font-semibold">Little moments, big love</h4>
              <span class="text-[#64748B] text-[11px]">Family Moments · Vibes Studio</span>
            </div>
          </div>

          <div class="flex flex-col gap-2 group cursor-pointer">
            <div class="overflow-hidden rounded-xl">
              <img class="w-full h-[240px] object-cover rounded-xl group-hover:scale-105 transition-transform duration-300" src="asset/contoh3.jpeg" alt="Gallery 3" />
            </div>
            <div class="flex flex-col">
              <h4 class="text-[#0C4A6E] text-sm font-semibold">Made to stand out</h4>
              <span class="text-[#64748B] text-[11px]">Brand & Product · Vibes Studio</span>
            </div>
          </div>
        </div>

        <!-- BARIS GALERI TAMBAHAN (Sembunyi secara default, terbuka saat diklik) -->
        <div id="galeriTambahan" class="hidden grid-cols-1 md:grid-cols-3 gap-5 pt-2">
          
          <div class="flex flex-col gap-2 group cursor-pointer">
            <div class="overflow-hidden rounded-xl">
              <img class="w-full h-[240px] object-cover rounded-xl group-hover:scale-105 transition-transform duration-300" src="asset/contoh1.jpeg" alt="Gallery 4" />
            </div>
            <div class="flex flex-col">
              <h4 class="text-[#0C4A6E] text-sm font-semibold">Studio Atmosphere</h4>
              <span class="text-[#64748B] text-[11px]">Vibes Studio Jatibarang</span>
            </div>
          </div>

          <div class="flex flex-col gap-2 group cursor-pointer">
            <div class="overflow-hidden rounded-xl">
              <img class="w-full h-[240px] object-cover rounded-xl group-hover:scale-105 transition-transform duration-300" src="asset/contoh5.jpeg" alt="Gallery 5" />
            </div>
            <div class="flex flex-col">
              <h4 class="text-[#0C4A6E] text-sm font-semibold">Warmth & Joy</h4>
              <span class="text-[#64748B] text-[11px]">Personal Portrait · Vibes Studio</span>
            </div>
          </div>

          <div class="flex flex-col gap-2 group cursor-pointer">
            <div class="overflow-hidden rounded-xl">
              <img class="w-full h-[240px] object-cover rounded-xl group-hover:scale-105 transition-transform duration-300" src="asset/contoh6.jpeg" alt="Gallery 6" />
            </div>
            <div class="flex flex-col">
              <h4 class="text-[#0C4A6E] text-sm font-semibold">Sweet Moments</h4>
              <span class="text-[#64748B] text-[11px]">Group Session · Vibes Studio</span>
            </div>
          </div>

        </div>

      </div>
    </section>

    <!-- ================= 6. 3 LANGKAH BOOKING ================= -->
    <section class="px-6 lg:px-12 py-10 bg-[#E6F7F8]">
      <div class="max-w-[1140px] mx-auto flex flex-col gap-6">
        
        <div class="flex flex-col gap-1">
          <span class="text-[#0891B2] text-[11px] font-bold tracking-wider uppercase">BOOKING DALAM 3 LANGKAH</span>
          <h2 class="text-[#0C4A6E] text-2xl lg:text-3xl font-semibold leading-tight">Lebih sedikit repot. Lebih banyak momen.</h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
          <div class="flex items-start gap-3">
            <span class="text-[#0891B2] text-2xl font-bold shrink-0">01</span>
            <div class="flex flex-col gap-1">
              <h4 class="text-[#0C4A6E] text-sm font-semibold">Masuk ke Akun Anda</h4>
              <p class="text-[#64748B] text-xs leading-relaxed">Masuk ke akun Anda untuk melacak booking dan riwayat.</p>
            </div>
          </div>
          <div class="flex items-start gap-3">
            <span class="text-[#0891B2] text-2xl font-bold shrink-0">02</span>
            <div class="flex flex-col gap-1">
              <h4 class="text-[#0C4A6E] text-sm font-semibold">Pilih paket dan jam</h4>
              <p class="text-[#64748B] text-xs leading-relaxed">Pilih paket dan jam sesi yang sesuai untuk booking Anda.</p>
            </div>
          </div>
          <div class="flex items-start gap-3">
            <span class="text-[#0891B2] text-2xl font-bold shrink-0">03</span>
            <div class="flex flex-col gap-1">
              <h4 class="text-[#0C4A6E] text-sm font-semibold">Lihat riwayat booking</h4>
              <p class="text-[#64748B] text-xs leading-relaxed">Cek riwayat booking, jadwal, dan hasil foto setelah login.</p>
            </div>
          </div>
        </div>

      </div>
    </section>

    <!-- ================= 7. KATA MEREKA / TESTIMONIAL ================= -->
    <section class="px-6 lg:px-12 py-12 bg-[#F6F8FC]">
      <div class="max-w-[1140px] mx-auto flex flex-col gap-6">
        
        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-end gap-4">
          <div class="flex flex-col gap-1.5">
            <span class="text-[#0891B2] text-[11px] font-bold tracking-wider uppercase">KATA MEREKA</span>
            <h2 class="text-[#0C4A6E] text-2xl lg:text-3xl font-semibold leading-tight">Pengalaman baik, dari awal sampai akhir.</h2>
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
          <div class="p-5 bg-white rounded-xl border border-[#E2E8F0] flex flex-col gap-3">
            <span class="text-[#0891B2] text-sm">★★★★★</span>
            <p class="text-[#0C4A6E] text-xs leading-relaxed">“Awalnya takut kaku di depan kamera, tapi sesi jadi santai banget. Hasilnya benar-benar terasa seperti kami!”</p>
            <div class="flex items-center gap-2.5 mt-auto pt-2">
              <div class="w-8 h-8 bg-[#E6F7F8] rounded-full flex items-center justify-center text-[#0891B2] text-[10px] font-bold shrink-0">NA</div>
              <div class="flex flex-col">
                <span class="text-[#0C4A6E] text-xs font-semibold">Nadia & Arif</span>
                <span class="text-[#64748B] text-[10px]">Couple & Friends</span>
              </div>
            </div>
          </div>
          <div class="p-5 bg-white rounded-xl border border-[#E2E8F0] flex flex-col gap-3">
            <span class="text-[#0891B2] text-sm">★★★★★</span>
            <p class="text-[#0C4A6E] text-xs leading-relaxed">“Studio nyaman, anak-anak betah, dan fotografernya sabar. Foto keluarga kami sekarang jadi pajangan favorit di rumah.”</p>
            <div class="flex items-center gap-2.5 mt-auto pt-2">
              <div class="w-8 h-8 bg-[#E6F7F8] rounded-full flex items-center justify-center text-[#0891B2] text-[10px] font-bold shrink-0">DS</div>
              <div class="flex flex-col">
                <span class="text-[#0C4A6E] text-xs font-semibold">Dewi Santoso</span>
                <span class="text-[#64748B] text-[10px]">Family Moments</span>
              </div>
            </div>
          </div>
          <div class="p-5 bg-white rounded-xl border border-[#E2E8F0] flex flex-col gap-3">
            <span class="text-[#0891B2] text-sm">★★★★★</span>
            <p class="text-[#0C4A6E] text-xs leading-relaxed">“Dari booking sampai download foto semuanya jelas. Portrait untuk LinkedIn saya terlihat profesional tanpa terasa berlebihan.”</p>
            <div class="flex items-center gap-2.5 mt-auto pt-2">
              <div class="w-8 h-8 bg-[#E6F7F8] rounded-full flex items-center justify-center text-[#0891B2] text-[10px] font-bold shrink-0">RP</div>
              <div class="flex flex-col">
                <span class="text-[#0C4A6E] text-xs font-semibold">Rizky Pratama</span>
                <span class="text-[#64748B] text-[10px]">Personal Portrait</span>
              </div>
            </div>
          </div>
        </div>

      </div>
    </section>

    <!-- ================= 8. CTA BANNER SECTION ================= -->
    <section id="booking" class="px-6 lg:px-12 pb-10">
      <div class="max-w-[1140px] mx-auto p-8 bg-[#0891B2] rounded-2xl flex flex-col lg:flex-row justify-between items-center gap-6 shadow-md">
        <div class="max-w-[680px] flex flex-col gap-2 text-white">
          <span class="text-[10px] font-semibold tracking-wider opacity-75 uppercase">RUANG UNTUK CERITA ANDA</span>
          <h2 class="text-xl lg:text-2xl font-semibold leading-tight">Momen berikutnya dimulai di sini.</h2>
          <p class="text-xs opacity-85 leading-relaxed">Pilih tanggal favorit Anda. Booking dan akses riwayat tersedia setelah masuk ke akun.</p>
        </div>
        <a href="login.php" class="h-9 px-5 bg-white hover:bg-gray-100 transition-colors text-[#0891B2] font-semibold text-xs rounded-lg flex items-center justify-center gap-1.5 shrink-0 whitespace-nowrap">
          <span>Booking</span>
          <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
        </a>
      </div>
    </section>

  </main>

  <!-- ================= 9. FOOTER ================= -->
  <footer class="px-6 lg:px-12 pt-10 pb-5 bg-white border-t border-[#E2E8F0]">
    <div class="max-w-[1140px] mx-auto flex flex-col gap-8">
      
      <div class="grid grid-cols-1 md:grid-cols-12 gap-8">
        <div class="md:col-span-4 flex flex-col gap-3">
          <div class="flex items-center gap-2">
            <img src="asset/logo.jpeg" alt="Logo" class="w-8 h-8 rounded-lg object-cover" />
            <span class="text-[#0C4A6E] text-lg font-extrabold tracking-tight">SUMAVIS</span>
          </div>
          <p class="text-[#64748B] text-xs leading-relaxed">Sistem Manajemen Informasi Studio Foto untuk Vibes Studio Jatibarang. Dari informasi studio, tema, paket, galeri, booking, hingga riwayat, semuanya dalam satu tempat.</p>
        </div>

        <div class="md:col-span-2 flex flex-col gap-2 text-xs">
          <span class="text-[#0C4A6E] font-semibold">Jelajahi studio</span>
          <a href="#paket" class="text-[#64748B] hover:text-[#0891B2]">Paket & harga</a>
          <a href="#galeri" class="text-[#64748B] hover:text-[#0891B2]">Portofolio</a>
        </div>

        <div class="md:col-span-3 flex flex-col gap-2 text-xs">
          <span class="text-[#0C4A6E] font-semibold">Temui kami</span>
          <p class="text-[#64748B] leading-relaxed">Vibes Studio<br/>Jatibarang</p>
        </div>

        <div class="md:col-span-3 flex flex-col gap-2 text-xs">
          <span class="text-[#0C4A6E] font-semibold">Jam operasional</span>
          <p class="text-[#64748B] leading-relaxed">09.00 – 21.00 WIB</p>
        </div>
      </div>

      <div class="pt-4 border-t border-[#E2E8F0] flex flex-col md:flex-row justify-between items-center gap-2 text-[11px] text-[#64748B]">
        <span>© 2026 SUMAVIS. Setiap momen punya cerita.</span>
      </div>

    </div>
  </footer>

  <!-- JAVASCRIPT UNTUK TOGGLE GALERI TAMBAHAN -->
  <script>
    function toggleGaleri() {
      const galeriTambahan = document.getElementById('galeriTambahan');
      const txtBtn = document.getElementById('txtBtnGaleri');
      const iconBtn = document.getElementById('iconBtnGaleri');

      if (galeriTambahan.classList.contains('hidden')) {
        galeriTambahan.classList.remove('hidden');
        galeriTambahan.classList.add('grid');
        txtBtn.innerText = 'Sembunyikan';
        iconBtn.classList.add('rotate-180');
      } else {
        galeriTambahan.classList.add('hidden');
        galeriTambahan.classList.remove('grid');
        txtBtn.innerText = 'Lihat semuanya';
        iconBtn.classList.remove('rotate-180');
      }
    }
  </script>

</body>
</html>
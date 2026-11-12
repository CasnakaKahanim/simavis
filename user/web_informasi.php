<?php
session_start();
include '../koneksi.php';

$query_paket = mysqli_query($conn, "SELECT * FROM paket_foto");
$data_paket = [];
if ($query_paket && mysqli_num_rows($query_paket) > 0) {
    while ($row = mysqli_fetch_assoc($query_paket)) {
        // Memetakan gambar spesifik jika dari database berdasarkan nama paket
        if (stripos($row['nama_paket'], 'Couple') !== false) {
            $row['gambar'] = 'asset/contoh6.jpeg';
        } elseif (stripos($row['nama_paket'], 'Bestie') !== false) {
            $row['gambar'] = 'asset/contoh4.jpeg';
        } elseif (stripos($row['nama_paket'], 'Gang') !== false) {
            $row['gambar'] = 'asset/contoh1.jpeg';
        }
        $data_paket[] = $row;
    }
} else {
    // Data fallback dengan gambar contoh yang dipastikan berbeda tiap paket
    $data_paket = [
        [
            'id' => 1,
            'nama_paket' => 'COUPLE VibesPackage',
            'deskripsi' => 'Sesi foto intim dan berkesan bersama pasangan tercinta.',
            'harga' => 35000,
            'durasi' => '10 menit',
            'kapasitas' => '1 - 2 Orang',
            'backdrop' => 'Background polos (putih, abu, coklat, pink, biru)',
            'foto_edit' => '10 foto',
            'gambar' => 'asset/contoh6.jpeg',
            'is_populer' => 0
        ],
        [
            'id' => 2,
            'nama_paket' => 'BESTIE VibesPackage',
            'deskripsi' => 'Rayakan pertemanan bersama Vibes Studio.',
            'harga' => 50000,
            'durasi' => '10 menit',
            'kapasitas' => '3 - 4 Orang',
            'backdrop' => 'Background polos (putih, abu, coklat, pink, biru)',
            'foto_edit' => '10 foto edit resolusi tinggi',
            'gambar' => 'asset/contoh4.jpeg',
            'is_populer' => 1
        ],
        [
            'id' => 3,
            'nama_paket' => 'GANG VibesPackage',
            'deskripsi' => 'Dokumentasikan setiap moment bersama Vibes Studio.',
            'harga' => 85000,
            'durasi' => '10 menit',
            'kapasitas' => '5 - 8 Orang',
            'backdrop' => 'Background polos (putih, abu, coklat, pink, biru)',
            'foto_edit' => '15 foto edit + cetak 20 x 30 cm',
            'gambar' => 'asset/contoh1.jpeg',
            'is_populer' => 0
        ]
    ];
}
?>

<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <title>SIMAVIS - Vibes Studio Jatibarang</title>
  
  <link rel="icon" type="image/jpeg" href="asset/logo.jpeg">
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      darkMode: 'class',
    }
  </script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Caveat:wght@700&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style> 
    body { font-family: 'Inter', sans-serif; }
    .font-handwriting { font-family: 'Caveat', cursive; }

    @keyframes marquee {
      0% { transform: translateX(0); }
      100% { transform: translateX(calc(-50% - 12px)); }
    }
    .animate-marquee {
      display: flex;
      width: max-content;
      animation: marquee 25s linear infinite;
    }
    .animate-marquee:hover {
      animation-play-state: paused;
    }
    .no-scrollbar::-webkit-scrollbar {
      display: none;
    }

    .reveal {
      opacity: 0;
      transform: translateY(30px);
      transition: all 0.8s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .reveal.active {
      opacity: 1;
      transform: translateY(0);
    }
  </style>
</head>
<body class="bg-[#FDFBF7] dark:bg-[#1A1614] text-[#3E2723] dark:text-[#F5EBE6] antialiased transition-colors duration-300">

  <!-- ================= 0. TOP ANNOUNCEMENT BAR ================= -->
  <div class="w-full bg-[#3E2723] dark:bg-[#2A1B18] text-[#EFEBE9] text-[10px] sm:text-[11px] py-1.5 px-4 text-center font-medium tracking-wide flex justify-center items-center gap-2 transition-colors">
    <i class="fa-solid fa-camera-retro text-[#D7CCC8]"></i>
    <span>VIBES STUDIO JATIBARANG · ABADIKAN MOMEN TERBAIK ANDA BERSAMA KAMI</span>
  </div>

  <!-- ================= 1. NAVBAR ================= -->
  <header class="sticky top-0 z-50 w-full h-[60px] sm:h-[70px] px-4 lg:px-12 bg-white/90 dark:bg-[#231E1B]/90 backdrop-blur-md border-b border-[#D7CCC8]/40 dark:border-[#4E342E]/40 flex justify-between items-center shadow-xs transition-colors">
    <a href="web_informasi.php" class="flex items-center gap-2 sm:gap-2.5 shrink-0">
      <img src="asset/logo.jpeg" alt="Logo SIMAVIS" class="w-7 h-7 sm:w-8 sm:h-8 rounded-lg object-cover border border-[#3E2723]/20 shadow-xs" />
      <span class="text-[#3E2723] dark:text-white text-base sm:text-lg font-extrabold tracking-tight">SIMAVIS</span>
    </a>

    <!-- Navigasi dengan Indikator Warna Cokelat Aktif Otomatis sesuai Section -->
    <nav class="hidden md:flex items-center gap-2 bg-[#F5EBE6] dark:bg-[#2A1B18] p-1.5 rounded-full border border-[#D7CCC8]/60 dark:border-[#4E342E] text-xs font-semibold transition-colors">
      <a href="#beranda" id="nav-beranda" class="nav-link px-4 py-1.5 text-[#5D4037] dark:text-gray-300 rounded-full transition-all flex items-center gap-1.5">
        <i class="fa-solid fa-house"></i>
        <span>Home</span>
      </a>
      <a href="#paket" id="nav-paket" class="nav-link px-4 py-1.5 text-[#5D4037] dark:text-gray-300 rounded-full transition-all flex items-center gap-1.5">
        <i class="fa-solid fa-box-open"></i>
        <span>Paket</span>
      </a>
      <a href="#galeri" id="nav-galeri" class="nav-link px-4 py-1.5 text-[#5D4037] dark:text-gray-300 rounded-full transition-all flex items-center gap-1.5">
        <i class="fa-solid fa-images"></i>
        <span>Galeri</span>
      </a>
      <a href="#lokasi" id="nav-lokasi" class="nav-link px-4 py-1.5 text-[#5D4037] dark:text-gray-300 rounded-full transition-all flex items-center gap-1.5">
        <i class="fa-solid fa-map-location-dot"></i>
        <span>Lokasi</span>
      </a>
      <a href="#kontak" id="nav-kontak" class="nav-link px-4 py-1.5 text-[#5D4037] dark:text-gray-300 rounded-full transition-all flex items-center gap-1.5">
        <i class="fa-solid fa-phone"></i>
        <span>Kontak</span>
      </a>
    </nav>

    <div class="flex items-center gap-2 sm:gap-3 shrink-0">
      <button onclick="toggleDarkMode()" id="themeToggleBtn" class="w-9 h-9 rounded-full bg-[#F5EBE6] dark:bg-[#2A1B18] border border-[#D7CCC8]/60 dark:border-[#4E342E] text-[#3E2723] dark:text-amber-400 flex items-center justify-center text-xs transition-transform active:scale-95" title="Ganti Mode Tampilan">
        <i id="themeIcon" class="fa-solid fa-moon"></i>
      </button>

      <?php if (isset($_SESSION['id_pelanggan'])) { ?>
        <a href="akun.php" class="text-[#3E2723] dark:text-gray-200 text-xs font-semibold hover:text-[#5D4037]">Riwayat Saya</a>
        <a href="logout.php" class="text-red-600 dark:text-red-400 text-xs font-semibold">Keluar</a>
      <?php } else { ?>
        <a href="login.php" class="text-[#3E2723] dark:text-gray-200 text-xs font-semibold hover:text-[#5D4037] mr-1">Masuk</a>
        <a href="login.php" class="h-8 sm:h-10 px-3.5 sm:px-5 bg-[#6D4C41] hover:bg-[#5D4037] transition-colors text-white text-[11px] sm:text-xs font-bold rounded-full flex items-center gap-1.5 shadow-md">
          <span>Booking</span>
          <i class="fa-solid fa-chevron-right text-[9px]"></i>
        </a>
      <?php } ?>
    </div>
  </header>

  <main class="w-full">

    <!-- ================= 2. HERO BANNER ================= -->
    <section id="beranda" class="w-full bg-[#F5EBE6] dark:bg-[#231E1B]/50 border-b border-[#D7CCC8]/40 dark:border-[#4E342E] relative overflow-hidden py-8 sm:py-12 lg:py-16 px-4 lg:px-12 transition-colors">
      <div class="max-w-[1140px] mx-auto grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-8 items-center">
        <div class="lg:col-span-5 flex flex-col gap-3 sm:gap-4 text-center lg:text-left reveal">
          <span class="text-[#5D4037] dark:text-[#D7CCC8] text-[10px] sm:text-xs font-bold tracking-widest uppercase bg-white/90 dark:bg-[#2A1B18] backdrop-blur-md px-3 py-1 rounded-full w-fit mx-auto lg:mx-0 border border-[#3E2723]/20 shadow-xs">
            Vibes Studio Jatibarang
          </span>
          <h1 class="text-3xl sm:text-4xl lg:text-5xl text-[#3E2723] dark:text-white font-handwriting leading-tight">
            Explore your imagination
          </h1>
          <p class="text-[#6D4C41] dark:text-gray-300 text-xs lg:text-sm leading-relaxed max-w-[420px] mx-auto lg:mx-0">
            Abadikan momen indah bersama keluarga, pasangan, dan teman terbaik Anda di studio foto modern bersuasana hangat.
          </p>
          <div class="flex items-center justify-center lg:justify-start gap-2.5 sm:gap-3 pt-1">
            <a href="login.php" class="h-9 sm:h-10 px-4 sm:px-6 bg-[#3E2723] hover:bg-[#5D4037] text-white text-xs font-bold rounded-xl flex items-center gap-2 shadow-md transition-all">
              <span>Pilih Sesi Foto</span>
              <i class="fa-solid fa-arrow-right text-[10px]"></i>
            </a>
            <a href="#galeri" class="h-9 sm:h-10 px-4 sm:px-5 bg-white dark:bg-[#2A1B18] text-[#3E2723] dark:text-white hover:bg-gray-50 dark:hover:bg-[#3E2723] border border-[#D7CCC8] dark:border-[#4E342E] text-xs font-semibold rounded-xl flex items-center transition-all">
              Lihat Galeri
            </a>
          </div>
        </div>

        <div class="lg:col-span-7 relative flex justify-center items-center py-2 sm:py-6 reveal">
          <div class="grid grid-cols-3 gap-2 sm:gap-3 w-full max-w-[560px]">
            <div class="bg-white dark:bg-[#2A1B18] p-1.5 sm:p-2 pb-3 sm:pb-5 rounded-lg shadow-md border border-[#D7CCC8]/60 dark:border-[#4E342E] transform -rotate-3 hover:rotate-0 transition-transform duration-300">
              <img src="asset/contoh6.jpeg" alt="Couple" class="w-full h-[120px] sm:h-[160px] lg:h-[200px] object-cover rounded-md">
              <span class="text-[9px] sm:text-[10px] text-center block mt-1.5 font-handwriting text-[#6D4C41] dark:text-gray-300 sm:text-base">Couple Session</span>
            </div>
            <div class="bg-white dark:bg-[#2A1B18] p-1.5 sm:p-2 pb-3 sm:pb-5 rounded-lg shadow-xl border border-[#3E2723]/30 transform rotate-2 scale-105 z-10 hover:scale-110 transition-transform duration-300">
              <img src="asset/contoh4.jpeg" alt="Bestie" class="w-full h-[130px] sm:h-[170px] lg:h-[210px] object-cover rounded-md">
              <span class="text-[9px] sm:text-[10px] text-center block mt-1.5 font-handwriting text-[#5D4037] dark:text-[#D7CCC8] font-bold sm:text-lg">Self Photo</span>
            </div>
            <div class="bg-white dark:bg-[#2A1B18] p-1.5 sm:p-2 pb-3 sm:pb-5 rounded-lg shadow-md border border-[#D7CCC8]/60 dark:border-[#4E342E] transform -rotate-2 hover:rotate-0 transition-transform duration-300">
              <img src="asset/contoh1.jpeg" alt="Gang" class="w-full h-[120px] sm:h-[160px] lg:h-[200px] object-cover rounded-md">
              <span class="text-[9px] sm:text-[10px] text-center block mt-1.5 font-handwriting text-[#6D4C41] dark:text-gray-300 sm:text-base">Gang Vibes</span>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ================= 3. FEATURES SECTION ================= -->
    <section id="studio" class="px-4 lg:px-12 py-6 sm:py-8 bg-white dark:bg-[#231E1B] border-b border-[#D7CCC8]/40 dark:border-[#4E342E] transition-colors">
      <div class="max-w-[1140px] mx-auto grid grid-cols-1 md:grid-cols-3 gap-3 sm:gap-6 reveal">
        <div class="flex items-start gap-3 p-3.5 sm:p-4 bg-[#FDFBF7] dark:bg-[#1A1614] rounded-xl border border-[#D7CCC8]/60 dark:border-[#4E342E]">
          <div class="w-9 h-9 sm:w-10 sm:h-10 bg-[#F5EBE6] dark:bg-[#3E2723]/30 rounded-xl flex items-center justify-center shrink-0">
            <i class="fa-solid fa-face-smile text-[#5D4037] dark:text-[#D7CCC8] text-base sm:text-lg"></i>
          </div>
          <div class="flex flex-col gap-0.5">
            <h4 class="text-[#3E2723] dark:text-white text-xs font-bold">Hasil yang Terasa Personal</h4>
            <p class="text-[#6D4C41] dark:text-gray-300 text-[11px] leading-relaxed">Arahan pose dan editing natural, sesuai karakter Anda.</p>
          </div>
        </div>

        <div class="flex items-start gap-3 p-3.5 sm:p-4 bg-[#FDFBF7] dark:bg-[#1A1614] rounded-xl border border-[#D7CCC8]/60 dark:border-[#4E342E]">
          <div class="w-9 h-9 sm:w-10 sm:h-10 bg-[#F5EBE6] dark:bg-[#3E2723]/30 rounded-xl flex items-center justify-center shrink-0">
            <i class="fa-solid fa-calendar-check text-[#5D4037] dark:text-[#D7CCC8] text-base sm:text-lg"></i>
          </div>
          <div class="flex flex-col gap-0.5">
            <h4 class="text-[#3E2723] dark:text-white text-xs font-bold">Booking Praktis & Cepat</h4>
            <p class="text-[#6D4C41] dark:text-gray-300 text-[11px] leading-relaxed">Pilih paket dan cek sesi yang tersedia secara langsung.</p>
          </div>
        </div>

        <div class="flex items-start gap-3 p-3.5 sm:p-4 bg-[#FDFBF7] dark:bg-[#1A1614] rounded-xl border border-[#D7CCC8]/60 dark:border-[#4E342E]">
          <div class="w-9 h-9 sm:w-10 sm:h-10 bg-[#F5EBE6] dark:bg-[#3E2723]/30 rounded-xl flex items-center justify-center shrink-0">
            <i class="fa-solid fa-cloud-arrow-down text-[#5D4037] dark:text-[#D7CCC8] text-base sm:text-lg"></i>
          </div>
          <div class="flex flex-col gap-0.5">
            <h4 class="text-[#3E2723] dark:text-white text-xs font-bold">Akses Album Digital</h4>
            <p class="text-[#6D4C41] dark:text-gray-300 text-[11px] leading-relaxed">Pilih, unduh, dan simpan hasil foto di akun Anda.</p>
          </div>
        </div>
      </div>
    </section>

    <!-- ================= 4. PRICING / PAKET SECTION (SELF PHOTO PRICE LIST) ================= -->
    <section id="paket" class="px-4 lg:px-12 py-10 sm:py-14 bg-[#FDFBF7] dark:bg-[#1A1614] transition-colors">
      <div class="max-w-[900px] mx-auto flex flex-col gap-8 reveal">
        
        <!-- Header Price List ala Pamflet -->
        <div class="text-center flex flex-col items-center gap-3">
          <h2 class="text-3xl sm:text-4xl font-serif font-bold text-[#3E2723] dark:text-white tracking-wide">Price List</h2>
          <div class="inline-block px-6 py-2 bg-[#5D4037] text-white rounded-full text-xs sm:text-sm font-bold tracking-widest uppercase shadow-md">
            SELF PHOTO
          </div>
        </div>

        <!-- Kartu Daftar Paket Utama (Couple, Bestie, Gang) - Tanpa Garis/Shadow Pilih -->
        <div class="flex flex-col gap-5">
          <?php foreach ($data_paket as $pkt) { 
              $is_pop = isset($pkt['is_populer']) && $pkt['is_populer'] == 1;
              $img_paket = !empty($pkt['gambar']) ? $pkt['gambar'] : 'asset/contoh1.jpeg';
          ?>
            <div class="bg-white dark:bg-[#231E1B] rounded-2xl p-5 sm:p-6 border border-[#D7CCC8]/80 dark:border-[#4E342E] shadow-sm relative overflow-hidden transition-all group">
              
              <div class="grid grid-cols-1 md:grid-cols-12 gap-5 items-center">
                <!-- Thumbnail Contoh Foto yang Berbeda Tiap Paket -->
                <div class="md:col-span-4 relative h-36 sm:h-40 rounded-xl overflow-hidden bg-[#F5EBE6] dark:bg-[#2A1B18] border border-[#D7CCC8]/50">
                  <img src="<?= htmlspecialchars($img_paket); ?>" alt="<?= htmlspecialchars($pkt['nama_paket']); ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" />
                  <div class="absolute inset-0 bg-gradient-to-t from-black/40 via-transparent to-transparent"></div>
                  <span class="absolute bottom-2 left-2 px-2 py-0.5 bg-black/50 backdrop-blur-md text-white rounded text-[10px] font-medium">
                    <i class="fa-solid fa-camera mr-1"></i> Contoh Sesi
                  </span>
                  <?php if($is_pop) { ?>
                    <span class="absolute top-2 right-2 px-2 py-0.5 bg-[#3E2723] text-amber-300 rounded text-[9px] font-bold">
                      <i class="fa-solid fa-star"></i> Paling Laris
                    </span>
                  <?php } ?>
                </div>

                <!-- Detail & Fitur Paket -->
                <div class="md:col-span-8 flex flex-col justify-between gap-4">
                  <div class="flex justify-between items-start gap-4">
                    <div>
                      <h3 class="text-lg sm:text-xl font-bold text-[#3E2723] dark:text-white tracking-tight"><?= htmlspecialchars($pkt['nama_paket']); ?></h3>
                      <p class="text-[#6D4C41] dark:text-gray-300 text-xs mt-0.5"><?= htmlspecialchars($pkt['deskripsi']); ?></p>
                    </div>
                    <div class="text-right shrink-0">
                      <span class="text-2xl sm:text-3xl font-extrabold text-[#3E2723] dark:text-white">
                        <?= $pkt['harga'] >= 1000 ? (number_format($pkt['harga']/1000, 0, '', '') . 'K') : number_format($pkt['harga'], 0, ',', '.'); ?>
                      </span>
                    </div>
                  </div>

                  <!-- List Fitur -->
                  <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs text-[#3E2723] dark:text-gray-300 pt-2 border-t border-[#D7CCC8]/40 dark:border-[#4E342E]">
                    <div class="flex items-center gap-2"><i class="fa-solid fa-users text-[#5D4037] dark:text-[#D7CCC8]"></i><span>Kapasitas: <strong><?= htmlspecialchars($pkt['kapasitas']); ?></strong></span></div>
                    <div class="flex items-center gap-2"><i class="fa-solid fa-clock text-[#5D4037] dark:text-[#D7CCC8]"></i><span>Durasi Sesi: <strong><?= htmlspecialchars($pkt['durasi']); ?></strong></span></div>
                    <div class="flex items-center gap-2 sm:col-span-2"><i class="fa-solid fa-cloud-arrow-down text-[#5D4037] dark:text-[#D7CCC8]"></i><span><?= htmlspecialchars($pkt['foto_edit']); ?></span></div>
                  </div>

                  <a href="login.php?paket=<?= urlencode($pkt['nama_paket']); ?>" class="w-full h-9 bg-[#3E2723] hover:bg-[#5D4037] text-white text-xs font-bold rounded-xl flex items-center justify-center gap-2 transition-colors shadow-xs">
                    <span>Pilih <?= htmlspecialchars($pkt['nama_paket']); ?></span>
                    <i class="fa-solid fa-arrow-right text-[10px]"></i>
                  </a>
                </div>
              </div>

            </div>
          <?php } ?>
        </div>

        <!-- Kotak Add On & Concept -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mt-2">
          
          <!-- Add On Box -->
          <div class="bg-[#5D4037] text-[#FDFBF7] p-5 sm:p-6 rounded-2xl shadow-md flex flex-col justify-between">
            <h4 class="text-base font-bold uppercase tracking-wider mb-3 border-b border-white/20 pb-2 flex items-center gap-2">
              <i class="fa-solid fa-circle-plus text-amber-300"></i> Add On (Tambahan)
            </h4>
            <ul class="space-y-2 text-xs">
              <li class="flex justify-between items-center py-1 border-b border-white/10">
                <span>Extra time (5 menit)</span>
                <span class="font-bold bg-white/10 px-2 py-0.5 rounded">15K</span>
              </li>
              <li class="flex justify-between items-center py-1 border-b border-white/10">
                <span>Extra person / orang</span>
                <span class="font-bold bg-white/10 px-2 py-0.5 rounded">15K</span>
              </li>
              <li class="flex justify-between items-center py-1 border-b border-white/10">
                <span>Cetak foto 4R</span>
                <span class="font-bold bg-white/10 px-2 py-0.5 rounded">5K</span>
              </li>
              <li class="flex justify-between items-center py-1">
                <span>Keychain (Gantungan kunci)</span>
                <span class="font-bold bg-white/10 px-2 py-0.5 rounded">10K</span>
              </li>
            </ul>
          </div>

          <!-- Concept Box -->
          <div class="bg-white dark:bg-[#231E1B] p-5 sm:p-6 rounded-2xl border border-[#D7CCC8]/80 dark:border-[#4E342E] shadow-sm flex flex-col justify-between">
            <h4 class="text-base font-bold uppercase tracking-wider mb-3 border-b border-[#D7CCC8]/40 dark:border-[#4E342E] pb-2 text-[#3E2723] dark:text-white flex items-center gap-2">
              <i class="fa-solid fa-palette text-[#5D4037]"></i> Concept / Background
            </h4>
            <div class="space-y-3 text-xs text-[#3E2723] dark:text-gray-300">
              <div class="flex items-start gap-2">
                <span class="font-bold bg-[#F5EBE6] dark:bg-[#3E2723] px-2 py-0.5 rounded text-[#5D4037] dark:text-white shrink-0">Basic</span>
                <p>Background polos; tersedia pilihan warna <strong>putih, abu, coklat, pink</strong>.</p>
              </div>
              <div class="flex items-start gap-2">
                <span class="font-bold bg-[#F5EBE6] dark:bg-[#3E2723] px-2 py-0.5 rounded text-[#5D4037] dark:text-white shrink-0">Homey</span>
                <p>Background eksklusif bernuansa <strong>biru</strong> yang hangat dan estetis.</p>
              </div>
            </div>
            <div class="mt-4 pt-3 border-t border-[#D7CCC8]/40 dark:border-[#4E342E] text-[11px] text-[#6D4C41] dark:text-gray-400 text-center">
              📍 Vibes Studio I Self Photo Studio<br/>Jl. Banjarsari, Bulak, Jatibarang Indramayu · <strong>@vibesstudio.im</strong>
            </div>
          </div>

        </div>

      </div>
    </section>

    <!-- ================= 5. GALERI SECTION ================= -->
    <section id="galeri" class="px-4 lg:px-12 py-8 sm:py-12 bg-white dark:bg-[#231E1B] overflow-hidden transition-colors">
      <div class="max-w-[1140px] mx-auto flex flex-col gap-5 sm:gap-6">
        <div class="flex flex-row justify-between items-end gap-4 reveal">
          <div class="flex flex-col gap-1">
            <span class="text-[#5D4037] dark:text-[#D7CCC8] text-[10px] sm:text-[11px] font-bold tracking-wider uppercase">PORTOFOLIO KAMI</span>
            <h2 class="text-[#3E2723] dark:text-white text-xl sm:text-2xl lg:text-3xl font-bold leading-tight">Cerita nyata. Ekspresi yang jujur.</h2>
          </div>
          <span class="text-[#6D4C41] dark:text-gray-400 text-[11px] sm:text-xs font-medium">
            <i class="fa-solid fa-arrows-left-right text-[#5D4037] dark:text-[#D7CCC8] mr-1"></i> Auto-scroll
          </span>
        </div>

        <div class="relative w-full overflow-x-auto no-scrollbar group reveal">
          <div id="autoScrollTrack" class="flex gap-4 sm:gap-6 w-max py-2 animate-marquee">
            <?php 
            $galeri_items = [
                ['img' => 'asset/contoh6.jpeg', 'tag' => 'Couple', 'title' => 'Couple VibesPackage', 'desc' => 'Vibes Studio Jatibarang'],
                ['img' => 'asset/contoh4.jpeg', 'tag' => 'Bestie', 'title' => 'Bestie VibesPackage', 'desc' => 'Vibes Studio Jatibarang'],
                ['img' => 'asset/contoh1.jpeg', 'tag' => 'Gang', 'title' => 'Gang VibesPackage', 'desc' => 'Vibes Studio Jatibarang'],
                ['img' => 'asset/contoh3.jpeg', 'tag' => 'Studio', 'title' => 'Basic & Homey Concept', 'desc' => 'Vibes Studio Jatibarang'],
                ['img' => 'asset/contoh2.jpeg', 'tag' => 'Portrait', 'title' => 'Self Photo Session', 'desc' => 'Vibes Studio Jatibarang'],
                ['img' => 'asset/contoh5.jpeg', 'tag' => 'Family', 'title' => 'Warmth & Joy', 'desc' => 'Vibes Studio Jatibarang']
            ];

            foreach($galeri_items as $item) { 
            ?>
              <div class="w-[240px] sm:w-[280px] lg:w-[340px] bg-white dark:bg-[#1A1614] rounded-2xl overflow-hidden border border-[#D7CCC8]/60 dark:border-[#4E342E] shadow-sm hover:shadow-xl transition-all duration-300 flex flex-col shrink-0 group/card cursor-pointer">
                <div class="relative overflow-hidden h-[180px] sm:h-[220px] lg:h-[250px]">
                  <img class="w-full h-full object-cover group-hover/card:scale-110 transition-transform duration-500 ease-out" src="<?= $item['img']; ?>" alt="<?= $item['title']; ?>" />
                  <span class="absolute top-2.5 left-2.5 px-2.5 py-0.5 bg-black/50 backdrop-blur-md text-[#EFEBE9] text-[9px] sm:text-[10px] font-semibold rounded-lg">
                    <?= $item['tag']; ?>
                  </span>
                </div>
                <div class="p-3.5 sm:p-4 flex flex-col gap-0.5 bg-white dark:bg-[#1A1614] transition-colors">
                  <h4 class="text-[#3E2723] dark:text-white text-xs sm:text-sm font-bold group-hover/card:text-[#5D4037] transition-colors"><?= $item['title']; ?></h4>
                  <span class="text-[#6D4C41] dark:text-gray-400 text-[10px] sm:text-[11px]"><?= $item['desc']; ?></span>
                </div>
              </div>
            <?php } ?>
          </div>
        </div>
      </div>
    </section>

    <!-- ================= 6. 3 LANGKAH BOOKING ================= -->
    <section class="px-4 lg:px-12 py-8 sm:py-12 bg-[#FDFBF7] dark:bg-[#1A1614] transition-colors">
      <div class="max-w-[1140px] mx-auto flex flex-col gap-5 sm:gap-6">
        <div class="flex flex-col gap-1 reveal">
          <span class="text-[#5D4037] dark:text-[#D7CCC8] text-[10px] sm:text-[11px] font-bold tracking-wider uppercase">CARA PEMESANAN</span>
          <h2 class="text-[#3E2723] dark:text-white text-xl sm:text-2xl lg:text-3xl font-bold leading-tight">Booking cepat dalam 3 langkah mudah.</h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 sm:gap-5 reveal">
          <div class="p-4 sm:p-5 bg-white dark:bg-[#231E1B] rounded-2xl border border-[#D7CCC8]/60 dark:border-[#4E342E] shadow-sm flex flex-col gap-2.5 sm:gap-3 transition-colors">
            <div class="w-8 h-8 sm:w-10 sm:h-10 bg-[#F5EBE6] dark:bg-[#3E2723]/30 rounded-xl flex items-center justify-center text-[#5D4037] dark:text-[#D7CCC8] font-extrabold text-sm sm:text-base">01</div>
            <h4 class="text-[#3E2723] dark:text-white text-xs sm:text-sm font-bold">Masuk ke Akun Anda</h4>
            <p class="text-[#6D4C41] dark:text-gray-300 text-[11px] sm:text-xs leading-relaxed">Login untuk mengamankan slot sesi foto dan melacak status pemesanan Anda.</p>
          </div>

          <div class="p-4 sm:p-5 bg-white dark:bg-[#231E1B] rounded-2xl border border-[#D7CCC8]/60 dark:border-[#4E342E] shadow-sm flex flex-col gap-2.5 sm:gap-3 transition-colors">
            <div class="w-8 h-8 sm:w-10 sm:h-10 bg-[#F5EBE6] dark:bg-[#3E2723]/30 rounded-xl flex items-center justify-center text-[#5D4037] dark:text-[#D7CCC8] font-extrabold text-sm sm:text-base">02</div>
            <h4 class="text-[#3E2723] dark:text-white text-xs sm:text-sm font-bold">Pilih Paket & Jam Sesi</h4>
            <p class="text-[#6D4C41] dark:text-gray-300 text-[11px] sm:text-xs leading-relaxed">Tentukan pilihan Vibes Package serta konsep background yang kamu inginkan.</p>
          </div>

          <div class="p-4 sm:p-5 bg-white dark:bg-[#231E1B] rounded-2xl border border-[#D7CCC8]/60 dark:border-[#4E342E] shadow-sm flex flex-col gap-2.5 sm:gap-3 transition-colors">
            <div class="w-8 h-8 sm:w-10 sm:h-10 bg-[#F5EBE6] dark:bg-[#3E2723]/30 rounded-xl flex items-center justify-center text-[#5D4037] dark:text-[#D7CCC8] font-extrabold text-sm sm:text-base">03</div>
            <h4 class="text-[#3E2723] dark:text-white text-xs sm:text-sm font-bold">Konfirmasi & Nikmati Sesi</h4>
            <p class="text-[#6D4C41] dark:text-gray-300 text-[11px] sm:text-xs leading-relaxed">Lakukan konfirmasi pembayaran dan datang langsung ke studio kami!</p>
          </div>
        </div>
      </div>
    </section>

    <!-- ================= 7. BANNER CTA ================= -->
    <section id="booking" class="px-4 lg:px-12 py-4 sm:py-6 reveal">
      <div class="max-w-[1140px] mx-auto p-5 sm:p-8 bg-[#3E2723] border border-[#5D4037] rounded-2xl flex flex-col sm:flex-row justify-between items-center gap-4 sm:gap-6 shadow-md text-white text-center sm:text-left">
        <div class="flex flex-col gap-1 sm:gap-1.5">
          <span class="text-[9px] sm:text-[10px] font-bold tracking-widest text-[#D7CCC8] uppercase">SAVE YOUR MEMORIES HERE</span>
          <h2 class="text-xl sm:text-2xl font-bold leading-tight">Momen berikutnya dimulai di sini.</h2>
          <p class="text-[11px] sm:text-xs text-[#EFEBE9]/80">Amankan slot sesi self photo favorit Anda sekarang di Vibes Studio Jatibarang.</p>
        </div>
        <a href="login.php" class="h-9 sm:h-10 px-5 sm:px-6 bg-[#F5EBE6] hover:bg-white transition-colors text-[#3E2723] font-bold text-xs rounded-xl flex items-center justify-center gap-2 shrink-0 shadow-sm w-full sm:w-auto">
          <span>Booking Sekarang</span>
          <i class="fa-solid fa-arrow-right"></i>
        </a>
      </div>
    </section>

    <!-- ================= 8. SECTION LOKASI GOOGLE MAPS ================= -->
    <section id="lokasi" class="px-4 lg:px-12 py-8 sm:py-12 bg-white dark:bg-[#231E1B] border-t border-b border-[#D7CCC8]/40 dark:border-[#4E342E] transition-colors">
      <div class="max-w-[1140px] mx-auto flex flex-col gap-5 sm:gap-6">
        <div class="flex flex-col gap-1 reveal">
          <span class="text-[#5D4037] dark:text-[#D7CCC8] text-[10px] sm:text-[11px] font-bold tracking-wider uppercase">KUNJUNGI STUDIO KAMI</span>
          <h2 class="text-[#3E2723] dark:text-white text-xl sm:text-2xl lg:text-3xl font-bold leading-tight">Lokasi & Petunjuk Arah</h2>
          <p class="text-[#6D4C41] dark:text-gray-300 text-xs leading-relaxed max-w-[600px]">Jl. Banjarsari, Bulak, Jatibarang Indramayu. Samping jalan strategis, mudah diakses kendaraan roda 2 maupun 4.</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 sm:gap-6 items-center reveal">
          <div class="lg:col-span-5 p-4 sm:p-6 bg-[#FDFBF7] dark:bg-[#1A1614] rounded-2xl border border-[#D7CCC8]/60 dark:border-[#4E342E] flex flex-col gap-4 sm:gap-5 shadow-sm transition-colors">
            <div class="flex items-start gap-3">
              <div class="w-9 h-9 sm:w-10 sm:h-10 bg-[#F5EBE6] dark:bg-[#3E2723]/30 rounded-xl flex items-center justify-center text-[#5D4037] dark:text-[#D7CCC8] shrink-0 text-base sm:text-lg">
                <i class="fa-solid fa-location-dot"></i>
              </div>
              <div class="flex flex-col gap-0.5 sm:gap-1">
                <h4 class="text-[#3E2723] dark:text-white text-xs sm:text-sm font-bold">Vibes Studio Jatibarang</h4>
                <p class="text-[#6D4C41] dark:text-gray-300 text-[11px] sm:text-xs leading-relaxed">Jl. Banjarsari, Bulak, Jatibarang Indramayu</p>
              </div>
            </div>

            <div class="flex items-start gap-3">
              <div class="w-9 h-9 sm:w-10 sm:h-10 bg-[#F5EBE6] dark:bg-[#3E2723]/30 rounded-xl flex items-center justify-center text-[#5D4037] dark:text-[#D7CCC8] shrink-0 text-base sm:text-lg">
                <i class="fa-solid fa-clock"></i>
              </div>
              <div class="flex flex-col gap-0.5 sm:gap-1">
                <h4 class="text-[#3E2723] dark:text-white text-xs sm:text-sm font-bold">Jam Operasional Studio</h4>
                <p class="text-[#6D4C41] dark:text-gray-300 text-[11px] sm:text-xs leading-relaxed">Setiap Hari: 09.00 – 20.00 WIB<br/><span class="text-[#5D4037] dark:text-[#D7CCC8] font-semibold">Buka setiap hari termasuk akhir pekan!</span></p>
              </div>
            </div>

            <a href="https://maps.app.goo.gl/99af2a98df422090" target="_blank" class="mt-1 h-9 sm:h-10 bg-[#3E2723] hover:bg-[#5D4037] text-white font-bold text-xs rounded-xl flex items-center justify-center gap-2 shadow-sm transition-colors">
              <i class="fa-solid fa-map-location-dot"></i>
              <span>Buka Petunjuk Arah (Google Maps)</span>
            </a>
          </div>

          <div class="lg:col-span-7 w-full h-[260px] sm:h-[320px] lg:h-[360px] rounded-2xl overflow-hidden border border-[#D7CCC8]/60 dark:border-[#4E342E] shadow-sm bg-white dark:bg-[#1A1614] p-1.5 transition-colors">
            <iframe 
              src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3768.209608612605!2d108.31032197475332!3d-6.475624393516204!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e6ec7007b9fe7a1%3A0x99af2a98df422090!2sVIBES%20STUDIO!5e1!3m2!1sid!2sid!4v1794287022355!5m2!1sid!2sid" 
              width="100%" 
              height="100%" 
              style="border:0;" 
              allowfullscreen="" 
              loading="lazy" 
              referrerpolicy="strict-origin-when-cross-origin"
              class="rounded-xl">
            </iframe>
          </div>
        </div>
      </div>
    </section>

    <!-- ================= 9. KATA MEREKA / TESTIMONIAL ================= -->
    <section id="kontak" class="px-4 lg:px-12 py-8 sm:py-12 bg-[#FDFBF7] dark:bg-[#1A1614] transition-colors">
      <div class="max-w-[1140px] mx-auto flex flex-col gap-5 sm:gap-6">
        <div class="flex flex-col gap-1 reveal">
          <span class="text-[#5D4037] dark:text-[#D7CCC8] text-[10px] sm:text-[11px] font-bold tracking-wider uppercase">ULASAN PELANGGAN</span>
          <h2 class="text-[#3E2723] dark:text-white text-xl sm:text-2xl lg:text-3xl font-bold leading-tight">Pengalaman berkesan, dari awal hingga akhir.</h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 sm:gap-5 reveal">
          <div class="p-4 sm:p-5 bg-white dark:bg-[#231E1B] rounded-2xl border border-[#D7CCC8]/60 dark:border-[#4E342E] shadow-sm flex flex-col gap-3 justify-between transition-colors">
            <div class="flex flex-col gap-1.5 sm:gap-2">
              <div class="text-[#8D6E63] text-xs">★★★★★</div>
              <p class="text-[#3E2723] dark:text-gray-200 text-xs leading-relaxed">“Seru banget self photo di Vibes Studio! Hasilnya langsung dapet semua via G-Drive plus dapat cetakan 4R.”</p>
            </div>
            <div class="flex items-center gap-2.5 pt-2.5 sm:pt-3 border-t border-[#D7CCC8]/40 dark:border-[#4E342E]">
              <div class="w-7 h-7 sm:w-8 sm:h-8 bg-[#F5EBE6] dark:bg-[#3E2723]/30 rounded-full flex items-center justify-center text-[#5D4037] dark:text-[#D7CCC8] text-[10px] font-bold shrink-0">NA</div>
              <div class="flex flex-col">
                <span class="text-[#3E2723] dark:text-white text-xs font-bold">Nadia & Arif</span>
                <span class="text-[#6D4C41] dark:text-gray-400 text-[10px]">Couple VibesPackage</span>
              </div>
            </div>
          </div>

          <div class="p-4 sm:p-5 bg-white dark:bg-[#231E1B] rounded-2xl border border-[#D7CCC8]/60 dark:border-[#4E342E] shadow-sm flex flex-col gap-3 justify-between transition-colors">
            <div class="flex flex-col gap-1.5 sm:gap-2">
              <div class="text-[#8D6E63] text-xs">★★★★★</div>
              <p class="text-[#3E2723] dark:text-gray-200 text-xs leading-relaxed">“Bestie package worth it banget! Background birunya homey dan estetik banget buat foto bareng sahabat.”</p>
            </div>
            <div class="flex items-center gap-2.5 pt-2.5 sm:pt-3 border-t border-[#D7CCC8]/40 dark:border-[#4E342E]">
              <div class="w-7 h-7 sm:w-8 sm:h-8 bg-[#F5EBE6] dark:bg-[#3E2723]/30 rounded-full flex items-center justify-center text-[#5D4037] dark:text-[#D7CCC8] text-[10px] font-bold shrink-0">DS</div>
              <div class="flex flex-col">
                <span class="text-[#3E2723] dark:text-white text-xs font-bold">Dewi Santoso</span>
                <span class="text-[#6D4C41] dark:text-gray-400 text-[10px]">Bestie VibesPackage</span>
              </div>
            </div>
          </div>

          <div class="p-4 sm:p-5 bg-white dark:bg-[#231E1B] rounded-2xl border border-[#D7CCC8]/60 dark:border-[#4E342E] shadow-sm flex flex-col gap-3 justify-between transition-colors">
            <div class="flex flex-col gap-1.5 sm:gap-2">
              <div class="text-[#8D6E63] text-xs">★★★★★</div>
              <p class="text-[#3E2723] dark:text-gray-200 text-xs leading-relaxed">“Datang bareng geng sekolah, muat banyak dan harganya terjangkau banget. Recommended!”</p>
            </div>
            <div class="flex items-center gap-2.5 pt-2.5 sm:pt-3 border-t border-[#D7CCC8]/40 dark:border-[#4E342E]">
              <div class="w-7 h-7 sm:w-8 sm:h-8 bg-[#F5EBE6] dark:bg-[#3E2723]/30 rounded-full flex items-center justify-center text-[#5D4037] dark:text-[#D7CCC8] text-[10px] font-bold shrink-0">RP</div>
              <div class="flex flex-col">
                <span class="text-[#3E2723] dark:text-white text-xs font-bold">Rizky Pratama</span>
                <span class="text-[#6D4C41] dark:text-gray-400 text-[10px]">Gang VibesPackage</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

  </main>

  <!-- ================= 10. FOOTER ================= -->
  <footer class="px-4 lg:px-12 pt-8 sm:pt-10 pb-6 bg-white dark:bg-[#231E1B] border-t border-[#D7CCC8]/40 dark:border-[#4E342E] transition-colors">
    <div class="max-w-[1140px] mx-auto flex flex-col gap-6 sm:gap-8">
      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-12 gap-6 sm:gap-8">
        <div class="md:col-span-4 flex flex-col gap-2.5 sm:gap-3">
          <div class="flex items-center gap-2">
            <img src="asset/logo.jpeg" alt="Logo SIMAVIS" class="w-7 h-7 sm:w-8 sm:h-8 rounded-lg object-cover" onerror="this.src='https://placehold.co/32x32'" />
            <span class="text-[#3E2723] dark:text-white text-base sm:text-lg font-extrabold tracking-tight">SIMAVIS</span>
          </div>
          <p class="text-[#6D4C41] dark:text-gray-300 text-xs leading-relaxed">Sistem Manajemen Informasi Studio Foto untuk Vibes Studio Jatibarang. Dari pemesanan paket foto, jadwal sesi, hingga akses hasil foto digital.</p>
        </div>

        <div class="md:col-span-2 flex flex-col gap-2 text-xs">
          <span class="text-[#3E2723] dark:text-white font-bold">Navigasi</span>
          <a href="#beranda" class="text-[#6D4C41] dark:text-gray-300 hover:text-[#5D4037] transition-colors">Beranda</a>
          <a href="#paket" class="text-[#6D4C41] dark:text-gray-300 hover:text-[#5D4037] transition-colors">Paket & Harga</a>
          <a href="#galeri" class="text-[#6D4C41] dark:text-gray-300 hover:text-[#5D4037] transition-colors">Portofolio</a>
          <a href="#lokasi" class="text-[#6D4C41] dark:text-gray-300 hover:text-[#5D4037] transition-colors">Lokasi</a>
        </div>

        <div class="md:col-span-3 flex flex-col gap-2.5 text-xs">
          <span class="text-[#3E2723] dark:text-white font-bold">Hubungi Kami</span>
          <div class="flex flex-col gap-2 text-[#6D4C41] dark:text-gray-300">
            <a href="https://instagram.com/vibesstudio.im" target="_blank" class="flex items-center gap-2.5 hover:text-[#5D4037] transition-colors">
              <i class="fa-brands fa-instagram text-sm text-[#5D4037] dark:text-[#D7CCC8] w-4 text-center"></i>
              <span>@vibesstudio.im</span>
            </a>
            <a href="https://wa.me/6282111174879" target="_blank" class="flex items-center gap-2.5 hover:text-[#5D4037] transition-colors">
              <i class="fa-brands fa-whatsapp text-sm text-[#5D4037] dark:text-[#D7CCC8] w-4 text-center"></i>
              <span>+62 821-1117-4879</span>
            </a>
            <a href="https://tiktok.com/@vibesstudio.im" target="_blank" class="flex items-center gap-2.5 hover:text-[#5D4037] transition-colors">
              <i class="fa-brands fa-tiktok text-sm text-[#5D4037] dark:text-[#D7CCC8] w-4 text-center"></i>
              <span>vibesstudio.im</span>
            </a>
          </div>
        </div>

        <div class="md:col-span-3 flex flex-col gap-2 text-xs">
          <span class="text-[#3E2723] dark:text-white font-bold">Jam Operasional</span>
          <p class="text-[#6D4C41] dark:text-gray-300 leading-relaxed">
            <i class="fa-solid fa-clock text-[#5D4037] dark:text-[#D7CCC8] mr-1"></i> Setiap Hari: 09.00 – 20.00 WIB<br/>
            <i class="fa-solid fa-location-dot text-[#5D4037] dark:text-[#D7CCC8] mr-1 mt-1"></i> Jatibarang, Indramayu
          </p>
        </div>
      </div>

      <div class="pt-4 border-t border-[#D7CCC8]/40 dark:border-[#4E342E] flex flex-col sm:flex-row justify-between items-center gap-2 text-[10px] sm:text-[11px] text-[#6D4C41] dark:text-gray-400 text-center sm:text-left">
        <span>© 2026 SIMAVIS. Setiap momen punya cerita.</span>
      </div>
    </div>
  </footer>

  <!-- ================= FLOATING CHATBOT WIDGET ================= -->
  <div class="fixed bottom-5 right-5 z-50">
    <button onclick="toggleChatbot()" id="chatBtn" class="w-14 h-14 bg-[#3E2723] hover:bg-[#5D4037] border border-[#5D4037] text-[#EFEBE9] rounded-full shadow-2xl flex items-center justify-center text-xl transition-transform hover:scale-105 active:scale-95">
      <i class="fa-solid fa-comment-dots"></i>
    </button>

    <div id="chatWindow" class="hidden absolute bottom-16 right-0 w-[320px] sm:w-[360px] bg-white dark:bg-[#231E1B] rounded-2xl shadow-2xl border border-[#D7CCC8]/60 dark:border-[#4E342E] flex flex-col overflow-hidden transition-all duration-300">
      
      <div class="bg-[#3E2723] dark:bg-[#1A1614] text-white p-3.5 flex justify-between items-center border-b border-[#5D4037]/50">
        <div class="flex items-center gap-2.5">
          <div class="w-8 h-8 rounded-full bg-[#8D6E63] text-white flex items-center justify-center font-bold text-xs">AI</div>
          <div class="flex flex-col">
            <span class="text-xs font-bold">SIMAVIS Assistant</span>
            <span class="text-[10px] text-emerald-400 flex items-center gap-1">● Online</span>
          </div>
        </div>
        <button onclick="toggleChatbot()" class="text-white/80 hover:text-white text-sm px-2">✕</button>
      </div>

      <div id="chatBody" class="p-3.5 h-[280px] overflow-y-auto flex flex-col gap-2.5 bg-[#FDFBF7] dark:bg-[#1A1614] text-xs transition-colors">
        <div class="flex items-start gap-2">
          <div class="w-6 h-6 rounded-full bg-[#3E2723] text-white flex items-center justify-center text-[10px] shrink-0 font-bold">AI</div>
          <div class="p-2.5 bg-white dark:bg-[#231E1B] rounded-xl rounded-tl-none border border-[#D7CCC8]/60 dark:border-[#4E342E] text-[#3E2723] dark:text-white shadow-2xs max-w-[80%] leading-relaxed">
            Halo! Saya asisten virtual Vibes Studio Jatibarang. Ada yang bisa dibantu seputar paket Self Photo (Couple, Bestie, Gang) atau Add On?
          </div>
        </div>
      </div>

      <div class="p-2 bg-white dark:bg-[#231E1B] border-t border-[#D7CCC8]/40 dark:border-[#4E342E] flex gap-1.5 overflow-x-auto no-scrollbar text-[10px] transition-colors">
        <button onclick="kirimPesanCepat('Berapa harga paket self photo?')" class="px-2.5 py-1 bg-[#F5EBE6] dark:bg-[#3E2723]/40 text-[#5D4037] dark:text-[#D7CCC8] font-semibold rounded-full hover:bg-[#3E2723] hover:text-white transition-colors shrink-0">💰 Harga Paket</button>
        <button onclick="kirimPesanCepat('Apa saja pilihan background?')" class="px-2.5 py-1 bg-[#F5EBE6] dark:bg-[#3E2723]/40 text-[#5D4037] dark:text-[#D7CCC8] font-semibold rounded-full hover:bg-[#3E2723] hover:text-white transition-colors shrink-0">🎨 Background</button>
        <button onclick="kirimPesanCepat('Dimana lokasi studio?')" class="px-2.5 py-1 bg-[#F5EBE6] dark:bg-[#3E2723]/40 text-[#5D4037] dark:text-[#D7CCC8] font-semibold rounded-full hover:bg-[#3E2723] hover:text-white transition-colors shrink-0">📍 Lokasi</button>
        <button onclick="kirimPesanCepat('Bagaimana cara booking?')" class="px-2.5 py-1 bg-[#F5EBE6] dark:bg-[#3E2723]/40 text-[#5D4037] dark:text-[#D7CCC8] font-semibold rounded-full hover:bg-[#3E2723] hover:text-white transition-colors shrink-0">📅 Cara Booking</button>
      </div>

      <div class="p-2.5 bg-white dark:bg-[#231E1B] border-t border-[#D7CCC8]/40 dark:border-[#4E342E] flex gap-2 transition-colors">
        <input type="text" id="chatInput" onkeypress="handleKey(event)" placeholder="Ketik pertanyaan Anda..." class="w-full px-3 py-2 bg-[#FDFBF7] dark:bg-[#1A1614] border border-[#D7CCC8]/60 dark:border-[#4E342E] rounded-xl text-xs text-[#3E2723] dark:text-white focus:outline-none focus:border-[#5D4037]" />
        <button onclick="kirimPesanUser()" class="px-3 bg-[#3E2723] hover:bg-[#5D4037] text-white rounded-xl text-xs flex items-center justify-center transition-colors">
          <i class="fa-solid fa-paper-plane"></i>
        </button>
      </div>

    </div>
  </div>

  <!-- JAVASCRIPT -->
  <script>
    const databasePaket = <?php echo json_encode($data_paket); ?>;

    document.addEventListener("DOMContentLoaded", function() {
      if (localStorage.getItem('theme') === 'dark') {
        document.documentElement.classList.add('dark');
        document.getElementById('themeIcon').className = 'fa-solid fa-sun';
      }

      const track = document.getElementById('autoScrollTrack');
      if(track) {
        track.innerHTML += track.innerHTML;
      }

      // Observer untuk Navbar Active State (Warna Cokelat Aktif sesuai Section)
      const sections = document.querySelectorAll("section[id]");
      const navLinks = document.querySelectorAll(".nav-link");

      const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) {
            const id = entry.target.getAttribute("id");
            navLinks.forEach((link) => {
              link.classList.remove("bg-[#3E2723]", "text-white", "shadow-xs");
              link.classList.add("text-[#5D4037]", "dark:text-gray-300");
              if (link.getAttribute("href") === `#${id}`) {
                link.classList.remove("text-[#5D4037]", "dark:text-gray-300");
                link.classList.add("bg-[#3E2723]", "text-white", "shadow-xs");
              }
            });
          }
        });
      }, { threshold: 0.3 });

      sections.forEach((section) => observer.observe(section));

      const reveals = document.querySelectorAll(".reveal");
      function revealOnScroll() {
        for (let i = 0; i < reveals.length; i++) {
          let windowHeight = window.innerHeight;
          let elementTop = reveals[i].getBoundingClientRect().top;
          let elementVisible = 100;
          if (elementTop < windowHeight - elementVisible) {
            reveals[i].classList.add("active");
          }
        }
      }
      window.addEventListener("scroll", revealOnScroll);
      revealOnScroll();
    });

    function toggleDarkMode() {
      const html = document.documentElement;
      const icon = document.getElementById('themeIcon');
      if (html.classList.contains('dark')) {
        html.classList.remove('dark');
        localStorage.setItem('theme', 'light');
        icon.className = 'fa-solid fa-moon';
      } else {
        html.classList.add('dark');
        localStorage.setItem('theme', 'dark');
        icon.className = 'fa-solid fa-sun';
      }
    }

    function toggleChatbot() {
      const windowChat = document.getElementById('chatWindow');
      windowChat.classList.toggle('hidden');
    }

    function handleKey(e) {
      if (e.key === 'Enter') { kirimPesanUser(); }
    }

    function kirimPesanCepat(teks) {
      tambahPesanUser(teks);
      responBot(teks);
    }

    function kirimPesanUser() {
      const input = document.getElementById('chatInput');
      const teks = input.value.trim();
      if (teks === "") return;
      tambahPesanUser(teks);
      input.value = "";
      responBot(teks);
    }

    function tambahPesanUser(teks) {
      const body = document.getElementById('chatBody');
      const div = document.createElement('div');
      div.className = 'flex items-start gap-2 justify-end';
      div.innerHTML = `
        <div class="p-2.5 bg-[#3E2723] text-white rounded-xl rounded-tr-none shadow-2xs max-w-[80%] leading-relaxed">
          ${escapeHtml(teks)}
        </div>
      `;
      body.appendChild(div);
      body.scrollTop = body.scrollHeight;
    }

    function responBot(pertanyaan) {
      const body = document.getElementById('chatBody');
      const q = pertanyaan.toLowerCase();

      let jawaban = "Maaf, saya kurang paham. Anda bisa bertanya seputar harga paket self photo, background, atau lokasi studio!";

      if (q.includes('harga') || q.includes('paket') || q.includes('biaya') || q.includes('tarif')) {
        jawaban = "Berikut daftar Self Photo Vibes Package:<br>";
        databasePaket.forEach((p, index) => {
          let hargaFormat = 'Rp ' + Number(p.harga).toLocaleString('id-ID');
          jawaban += `${index + 1}. **${p.nama_paket}** - ${hargaFormat} (${p.kapasitas}, ${p.durasi})<br>`;
        });
        jawaban += "<br>Tersedia juga Add On (Extra time 15k, Extra person 15k, Cetak 4R 5k, Keychain 10k).";
      } else if (q.includes('background') || q.includes('konsep') || q.includes('concept')) {
        jawaban = "Pilihan konsep background:<br>- **Basic**: Polos (putih, abu, coklat, pink).<br>- **Homey**: Background eksklusif berwarna biru.";
      } else if (q.includes('lokasi') || q.includes('tempat') || q.includes('alamat') || q.includes('studio') || q.includes('dimana')) {
        jawaban = "Vibes Studio berlokasi di **Jl. Banjarsari, Bulak, Jatibarang Indramayu**. Anda dapat melihat petunjuk arah pada bagian **Lokasi** di halaman ini.";
      } else if (q.includes('booking') || q.includes('pesan') || q.includes('daftar')) {
        jawaban = "Cara booking sesi foto:<br>1. Klik tombol **Booking** di kanan atas.<br>2. Masukkan data diri.<br>3. Pilih paket Vibes Package yang diinginkan.";
      } else if (q.includes('halo') || q.includes('hai')) {
        jawaban = "Halo! Selamat datang di SIMAVIS Vibes Studio Jatibarang. Silakan pilih paket Self Photo favoritmu!";
      }

      setTimeout(() => {
        const div = document.createElement('div');
        div.className = 'flex items-start gap-2';
        div.innerHTML = `
          <div class="w-6 h-6 rounded-full bg-[#3E2723] text-white flex items-center justify-center text-[10px] shrink-0 font-bold">AI</div>
          <div class="p-2.5 bg-white dark:bg-[#231E1B] rounded-xl rounded-tl-none border border-[#D7CCC8]/60 dark:border-[#4E342E] text-[#3E2723] dark:text-white shadow-2xs max-w-[80%] leading-relaxed">
            ${jawaban}
          </div>
        `;
        body.appendChild(div);
        body.scrollTop = body.scrollHeight;
      }, 400);
    }

    function escapeHtml(text) {
      const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
      return text.replace(/[&<>"']/g, function(m) { return map[m]; });
    }
  </script>
</body>
</html>
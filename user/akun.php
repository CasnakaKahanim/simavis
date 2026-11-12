<?php
session_start();

if (file_exists('koneksi.php')) {
    include 'koneksi.php';
} else {
    include '../koneksi.php';
}

if (!isset($_SESSION['id_pelanggan'])) {
    header("Location: login.php");
    exit;
}

$id_pelanggan = $_SESSION['id_pelanggan'];
$sukses = "";
$error = "";

// Proses Simpan Rating & Ulasan jika form rating dikirim
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_rating'])) {
    $id_booking = intval($_POST['id_booking']);
    $rating     = intval($_POST['rating']);
    $ulasan     = mysqli_real_escape_string($conn, $_POST['ulasan']);

    if ($id_booking > 0 && $rating > 0) {
        $update_rating = mysqli_query($conn, "UPDATE booking SET rating = $rating, ulasan = '$ulasan' WHERE id = $id_booking AND id_pelanggan = $id_pelanggan");
        if ($update_rating) {
            $sukses = "Terima kasih! Rating dan ulasan berhasil disimpan. Album foto kini dapat diunduh.";
        } else {
            $error = "Gagal menyimpan rating. Silakan coba lagi.";
        }
    } else {
        $error = "Mohon pilih minimal 1 bintang rating.";
    }
}

// 1. Ambil Data Pelanggan
$query_pelanggan = mysqli_query($conn, "SELECT * FROM pelanggan WHERE id = $id_pelanggan");
$pelanggan = mysqli_fetch_assoc($query_pelanggan);

// 2. Ambil Statistik Booking
$query_total = mysqli_query($conn, "SELECT COUNT(*) as total FROM booking WHERE id_pelanggan = $id_pelanggan");
$total_booking = mysqli_fetch_assoc($query_total)['total'];

$query_mendatang = mysqli_query($conn, "SELECT COUNT(*) as total FROM booking WHERE id_pelanggan = $id_pelanggan AND status_sesi = 'Mendatang'");
$jml_mendatang = mysqli_fetch_assoc($query_mendatang)['total'];

$query_selesai = mysqli_query($conn, "SELECT COUNT(*) as total FROM booking WHERE id_pelanggan = $id_pelanggan AND status_sesi = 'Selesai'");
$jml_selesai = mysqli_fetch_assoc($query_selesai)['total'];

// 3. Ambil Sesi Berikutnya
$query_next = mysqli_query($conn, "SELECT * FROM booking WHERE id_pelanggan = $id_pelanggan AND status_sesi = 'Mendatang' ORDER BY tanggal_sesi ASC LIMIT 1");
$sesi_berikutnya = mysqli_fetch_assoc($query_next);

// 4. Ambil Semua Riwayat Booking
$query_riwayat = mysqli_query($conn, "SELECT * FROM booking WHERE id_pelanggan = $id_pelanggan ORDER BY tanggal_sesi DESC");

// 5. Ambil DOKUMEN ALBUM PER SESI BESERTA STATUS RATING BERDASARKAN NAMA ALBUM / PAKET
$query_group_album = mysqli_query($conn, "
    SELECT a.nama_album, a.tanggal_foto, a.status_album, COUNT(a.id) as total_foto, MIN(a.gambar_url) as cover_url,
           b.id as id_booking, COALESCE(b.rating, 0) as rating, COALESCE(b.ulasan, '') as ulasan
    FROM album_foto a
    LEFT JOIN booking b ON a.id_pelanggan = b.id_pelanggan AND (a.nama_album = b.nama_paket OR b.id = (SELECT MAX(id) FROM booking WHERE id_pelanggan = a.id_pelanggan))
    WHERE a.id_pelanggan = $id_pelanggan 
    GROUP BY a.nama_album, a.tanggal_foto, a.status_album, b.id
    ORDER BY a.id DESC
");

$query_total_foto_semua = mysqli_query($conn, "SELECT COUNT(*) as total FROM album_foto WHERE id_pelanggan = $id_pelanggan");
$total_semua_foto = mysqli_fetch_assoc($query_total_foto_semua)['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <title>SIMAVIS - Akun & Riwayat Klien</title>
  
  <link rel="icon" type="image/jpeg" href="asset/logo.jpeg">
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = { darkMode: 'class' }
  </script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Caveat:wght@700&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <style>
    body { font-family: 'Inter', sans-serif; }
    .font-handwriting { font-family: 'Caveat', cursive; }
  </style>
</head>
<body class="bg-[#FDFBF7] dark:bg-[#1A1614] text-[#3E2723] dark:text-[#F5EBE6] antialiased flex flex-col min-h-screen transition-colors duration-300">

  <!-- ================= TOP BAR ================= -->
  <div class="w-full bg-[#3E2723] dark:bg-[#2A1B18] text-[#EFEBE9] text-[10px] sm:text-[11px] py-1.5 px-4 text-center font-medium tracking-wide flex justify-center items-center gap-2">
    <i class="fa-solid fa-camera-retro text-[#D7CCC8]"></i>
    <span>VIBES STUDIO FOTO JATIBARANG · PORTAL AKUN & RIWAYAT KLIEN</span>
  </div>

  <!-- ================= NAVBAR ================= -->
  <header class="sticky top-0 z-40 w-full h-[68px] px-4 sm:px-6 lg:px-12 bg-white/95 dark:bg-[#231E1B]/95 backdrop-blur-md border-b border-[#D7CCC8]/60 dark:border-[#4E342E] flex justify-between items-center shadow-2xs transition-colors">
    <a href="web_informasi.php" class="flex items-center gap-2.5 shrink-0">
      <img src="asset/logo.jpeg" alt="Logo" class="w-8 h-8 rounded-lg object-cover border border-[#D7CCC8]/50">
      <span class="font-handwriting text-2xl text-[#3E2723] dark:text-white">Vibes Studio</span>
    </a>

    <div class="flex items-center gap-3 shrink-0">
      <button onclick="toggleDarkMode()" id="themeToggleBtn" class="w-9 h-9 rounded-full bg-[#FDFBF7] dark:bg-[#1A1614] border border-[#D7CCC8]/60 dark:border-[#4E342E] text-[#3E2723] dark:text-amber-400 flex items-center justify-center text-xs transition-transform active:scale-95" title="Ganti Mode Tampilan">
        <i id="themeIcon" class="fa-solid fa-moon"></i>
      </button>

      <div class="hidden sm:flex items-center gap-2 cursor-pointer">
        <div class="w-8 h-8 bg-[#F5EBE6] dark:bg-[#3E2723] rounded-full flex items-center justify-center text-[#5D4037] dark:text-[#D7CCC8] text-xs font-bold border border-[#D7CCC8]/50">
          <?= htmlspecialchars($pelanggan['inisial'] ?? substr($pelanggan['nama'], 0, 1)); ?>
        </div>
        <span class="text-[#3E2723] dark:text-gray-200 text-xs font-semibold"><?= htmlspecialchars($pelanggan['nama']); ?></span>
      </div>

      <a href="booking.php" class="h-9 px-4 bg-[#3E2723] hover:bg-[#5D4037] transition-colors text-white text-xs font-semibold rounded-xl flex items-center gap-1.5 shadow-2xs whitespace-nowrap">
        <span>Booking</span>
      </a>
    </div>
  </header>

  <!-- ================= MAIN CONTAINER ================= -->
  <main class="w-full max-w-[1140px] mx-auto px-4 sm:px-6 lg:px-12 py-8 flex flex-col gap-6 flex-grow">

    <?php if (!empty($sukses)) { ?>
      <div class="p-4 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-300 text-emerald-800 dark:text-emerald-300 text-xs sm:text-sm rounded-xl font-medium"><?= $sukses; ?></div>
    <?php } ?>
    <?php if (!empty($error)) { ?>
      <div class="p-4 bg-red-50 dark:bg-red-950/40 border border-red-300 text-red-700 dark:text-red-300 text-xs sm:text-sm rounded-xl font-medium"><?= $error; ?></div>
    <?php } ?>

    <!-- TITLE & 4 SUMMARY CARDS -->
    <div class="flex flex-col gap-6">
      <div class="flex flex-col gap-1">
        <h1 class="text-[#3E2723] dark:text-white text-xl sm:text-2xl lg:text-3xl font-bold">Halo, <?= htmlspecialchars(explode(' ', $pelanggan['nama'])[0]); ?>! Selamat Datang Kembali</h1>
        <p class="text-[#6D4C41] dark:text-gray-400 text-xs">Kelola riwayat pesanan dan unduh album digital foto studio Anda di sini.</p>
      </div>

      <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        <div class="p-4 bg-white dark:bg-[#231E1B] rounded-2xl border border-[#D7CCC8]/60 dark:border-[#4E342E] shadow-2xs flex flex-col gap-1.5 transition-colors">
          <span class="text-xs text-[#6D4C41] dark:text-gray-400">Total booking</span>
          <span class="text-[#3E2723] dark:text-white text-xl sm:text-2xl font-bold"><?= $total_booking; ?> pesanan</span>
          <span class="text-[#8D6E63] dark:text-gray-500 text-[10px] sm:text-[11px]">Seluruh riwayat booking</span>
        </div>

        <div class="p-4 bg-white dark:bg-[#231E1B] rounded-2xl border border-[#D7CCC8]/60 dark:border-[#4E342E] shadow-2xs flex flex-col gap-1.5 transition-colors">
          <span class="text-xs text-[#6D4C41] dark:text-gray-400">Mendatang</span>
          <span class="text-[#3E2723] dark:text-white text-xl sm:text-2xl font-bold"><?= $jml_mendatang; ?> sesi</span>
          <span class="text-[#8D6E63] dark:text-gray-500 text-[10px] sm:text-[11px]"><?= $sesi_berikutnya ? date('d M Y', strtotime($sesi_berikutnya['tanggal_sesi'])) : 'Tidak ada jadwal'; ?></span>
        </div>

        <div class="p-4 bg-white dark:bg-[#231E1B] rounded-2xl border border-[#D7CCC8]/60 dark:border-[#4E342E] shadow-2xs flex flex-col gap-1.5 transition-colors">
          <span class="text-xs text-[#6D4C41] dark:text-gray-400">Selesai</span>
          <span class="text-[#3E2723] dark:text-white text-xl sm:text-2xl font-bold"><?= $jml_selesai; ?> sesi</span>
          <span class="text-[#8D6E63] dark:text-gray-500 text-[10px] sm:text-[11px]">Momen terabadikan</span>
        </div>

        <div class="p-4 bg-white dark:bg-[#231E1B] rounded-2xl border border-[#D7CCC8]/60 dark:border-[#4E342E] shadow-2xs flex flex-col gap-1.5 transition-colors">
          <span class="text-xs text-[#6D4C41] dark:text-gray-400">Foto siap</span>
          <span class="text-[#3E2723] dark:text-white text-xl sm:text-2xl font-bold"><?= $total_semua_foto; ?> foto</span>
          <span class="text-[#8D6E63] dark:text-gray-500 text-[10px] sm:text-[11px]">Album digital aktif</span>
        </div>
      </div>
    </div>

    <!-- SESI BERIKUTNYA (Jika Ada) -->
    <?php if ($sesi_berikutnya) { ?>
    <div class="p-5 sm:p-6 bg-white dark:bg-[#231E1B] rounded-2xl border border-[#D7CCC8]/60 dark:border-[#4E342E] shadow-2xs flex flex-col gap-4 transition-colors">
      <div class="flex justify-between items-center">
        <h3 class="text-[#3E2723] dark:text-white text-base font-semibold">Sesi berikutnya</h3>
        <span class="px-3 py-1 bg-[#F5EBE6] dark:bg-[#3E2723] text-[#5D4037] dark:text-[#D7CCC8] text-[11px] font-semibold rounded-full">Mendatang · <?= htmlspecialchars($sesi_berikutnya['status_pembayaran']); ?></span>
      </div>
      <div class="flex flex-col sm:flex-row items-start gap-5">
        <div class="w-full sm:w-28 h-24 sm:h-32 bg-[#F5EBE6] dark:bg-[#3E2723]/50 rounded-xl border border-[#D7CCC8]/40 dark:border-[#4E342E] flex flex-col justify-center items-center gap-0.5 shrink-0 text-[#5D4037] dark:text-[#D7CCC8]">
          <span class="text-[10px] font-semibold tracking-wider"><?= strtoupper(date('F', strtotime($sesi_berikutnya['tanggal_sesi']))); ?></span>
          <span class="text-3xl sm:text-4xl font-bold leading-none"><?= date('d', strtotime($sesi_berikutnya['tanggal_sesi'])); ?></span>
          <span class="text-[11px]"><?= date('Y', strtotime($sesi_berikutnya['tanggal_sesi'])); ?></span>
        </div>
        <div class="flex flex-col gap-1.5 text-xs text-[#6D4C41] dark:text-gray-300">
          <span class="text-[11px] font-semibold text-[#5D4037] dark:text-[#D7CCC8]"><?= htmlspecialchars($sesi_berikutnya['kode_booking']); ?></span>
          <h4 class="text-[#3E2723] dark:text-white text-lg sm:text-xl font-semibold"><?= htmlspecialchars($sesi_berikutnya['nama_paket']); ?></h4>
          <span>Jadwal: <?= htmlspecialchars($sesi_berikutnya['jam_sesi']); ?> WIB</span>
          <span>Peserta: <?= htmlspecialchars($sesi_berikutnya['jumlah_peserta']); ?></span>
          <span class="text-[#3E2723] dark:text-white font-bold text-sm sm:text-base pt-1">Total: Rp <?= number_format($sesi_berikutnya['total_harga'], 0, ',', '.'); ?></span>
        </div>
      </div>
    </div>
    <?php } ?>

    <!-- TABEL RIWAYAT BOOKING -->
    <div class="p-5 sm:p-6 bg-white dark:bg-[#231E1B] rounded-2xl border border-[#D7CCC8]/60 dark:border-[#4E342E] shadow-2xs flex flex-col gap-4 transition-colors">
      <div class="flex justify-between items-center">
        <h3 class="text-[#3E2723] dark:text-white text-base font-semibold">Riwayat booking</h3>
        <span class="px-3 py-1 bg-[#FDFBF7] dark:bg-[#1A1614] border border-[#D7CCC8]/40 dark:border-[#4E342E] text-[#6D4C41] dark:text-gray-400 text-[11px] font-semibold rounded-full"><?= $total_booking; ?> pesanan</span>
      </div>

      <div class="w-full overflow-x-auto">
        <table class="w-full text-left text-xs whitespace-nowrap sm:whitespace-normal">
          <thead>
            <tr class="bg-[#FDFBF7] dark:bg-[#1A1614] text-[#6D4C41] dark:text-gray-400 font-medium border-b border-[#D7CCC8]/40 dark:border-[#4E342E]">
              <th class="p-3 rounded-l-lg">Paket / ID booking</th>
              <th class="p-3">Tanggal sesi</th>
              <th class="p-3">Total / pembayaran</th>
              <th class="p-3">Status sesi</th>
              <th class="p-3">Hasil foto</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-[#D7CCC8]/40 dark:divide-[#4E342E]">
            <?php 
            if (mysqli_num_rows($query_riwayat) > 0) {
                $rows_riwayat = [];
                while ($r = mysqli_fetch_assoc($query_riwayat)) {
                    $rows_riwayat[] = $r;
                }
                $total_riwayat = count($rows_riwayat);

                foreach ($rows_riwayat as $index => $row) { 
                    $isHidden = $index >= 2 ? 'hidden riwayat-tambahan' : '';
            ?>
            <tr class="riwayat-row <?= $isHidden; ?>">
              <td class="p-3">
                <div class="flex flex-col">
                  <span class="text-[#3E2723] dark:text-white font-semibold"><?= htmlspecialchars($row['nama_paket']); ?></span>
                  <span class="text-[#6D4C41] dark:text-gray-400 text-[11px]"><?= htmlspecialchars($row['kode_booking']); ?></span>
                </div>
              </td>
              <td class="p-3">
                <div class="flex flex-col">
                  <span class="text-[#3E2723] dark:text-gray-200"><?= date('d M Y', strtotime($row['tanggal_sesi'])); ?></span>
                  <span class="text-[#6D4C41] dark:text-gray-400 text-[11px]"><?= htmlspecialchars($row['jam_sesi']); ?></span>
                </div>
              </td>
              <td class="p-3">
                <div class="flex flex-col">
                  <span class="text-[#3E2723] dark:text-gray-200 font-semibold">Rp <?= number_format($row['total_harga'], 0, ',', '.'); ?></span>
                  <span class="text-emerald-700 dark:text-emerald-400 text-[11px]"><?= htmlspecialchars($row['status_pembayaran']); ?></span>
                </div>
              </td>
              <td class="p-3">
                <span class="px-2.5 py-1 <?= ($row['status_sesi'] == 'Mendatang') ? 'bg-[#F5EBE6] text-[#5D4037] dark:bg-[#3E2723] dark:text-[#D7CCC8]' : 'bg-emerald-50 text-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-400'; ?> text-[11px] font-semibold rounded-full inline-block">
                  <?= htmlspecialchars($row['status_sesi']); ?>
                </span>
              </td>
              <td class="p-3 text-[#6D4C41] dark:text-gray-400">
                <?= ($row['status_sesi'] == 'Selesai') ? 'Tersedia' : 'Setelah sesi'; ?>
              </td>
            </tr>
            <?php } 
            } else { ?>
            <tr>
              <td colspan="5" class="p-4 text-center text-[#6D4C41] dark:text-gray-400">Belum ada riwayat booking.</td>
            </tr>
            <?php } ?>
          </tbody>
        </table>
      </div>

      <?php if (isset($total_riwayat) && $total_riwayat > 2) { ?>
        <button id="btnLihatLainnya" onclick="toggleLihatLainnya()" class="w-full py-2.5 bg-[#FDFBF7] dark:bg-[#1A1614] hover:bg-[#F5EBE6] dark:hover:bg-[#3E2723]/50 border border-[#D7CCC8]/60 dark:border-[#4E342E] text-[#5D4037] dark:text-[#D7CCC8] font-semibold text-xs rounded-xl transition-colors text-center mt-2">
          Lihat Lainnya (<?= $total_riwayat - 2; ?> riwayat lainnya)
        </button>
      <?php } ?>
    </div>

    <!-- HASIL FOTO & DATA TERSIMPAN -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
      
      <!-- Hasil Foto / Album Digital -->
      <div class="lg:col-span-7 p-5 sm:p-6 bg-white dark:bg-[#231E1B] rounded-2xl border border-[#D7CCC8]/60 dark:border-[#4E342E] shadow-2xs flex flex-col gap-4 transition-colors">
        
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center border-b border-[#D7CCC8]/40 dark:border-[#4E342E] pb-3 gap-2">
          <div>
            <h3 class="text-[#3E2723] dark:text-white text-base font-semibold">Hasil foto Anda</h3>
            <p class="text-[11px] text-[#6D4C41] dark:text-gray-400">Setiap sesi foto ditampilkan dalam 1 album ringkas.</p>
          </div>
          <span class="text-xs bg-[#F5EBE6] dark:bg-[#3E2723] text-[#5D4037] dark:text-[#D7CCC8] font-semibold px-3 py-1 rounded-full">
            <i class="fa-solid fa-camera mr-1"></i> SIMAVIS Gallery
          </span>
        </div>

        <div class="relative w-full">
          <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-[#8D6E63]">
            <i class="fa-solid fa-search text-xs"></i>
          </span>
          <input type="text" id="searchAlbum" onkeyup="filterAlbum()" placeholder="Cari nama album atau paket foto..." class="w-full pl-9 pr-4 py-2.5 bg-[#FDFBF7] dark:bg-[#1A1614] border border-[#D7CCC8]/60 dark:border-[#4E342E] rounded-xl text-xs text-[#3E2723] dark:text-white placeholder-[#8D6E63] focus:outline-none focus:border-[#5D4037] transition-all" />
        </div>

        <div id="containerAlbum" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
          <?php if ($query_group_album && mysqli_num_rows($query_group_album) > 0) {
              while ($alb_group = mysqli_fetch_assoc($query_group_album)) { 
                  $nama_alb     = $alb_group['nama_album'];
                  $tgl_alb      = $alb_group['tanggal_foto'];
                  $id_booking   = $alb_group['id_booking'] ?? 0;
                  $sudah_rating = isset($alb_group['rating']) && $alb_group['rating'] > 0;

                  $q_detail_foto = mysqli_query($conn, "SELECT * FROM album_foto WHERE id_pelanggan = $id_pelanggan AND nama_album = '" . mysqli_real_escape_string($conn,$nama_alb) . "' AND tanggal_foto = '" . mysqli_real_escape_string($conn,$tgl_alb) . "' ORDER BY id ASC");
                  
                  $list_foto_sesi = [];
                  while ($f = mysqli_fetch_assoc($q_detail_foto)) {
                      $list_foto_sesi[] = $f['gambar_url'];
                  }

                  $cover_url       = $alb_group['cover_url'];
                  $total_foto_sesi = $alb_group['total_foto'];
          ?>
          
          <div class="album-item flex flex-col gap-2.5 p-3 bg-[#FDFBF7] dark:bg-[#1A1614] rounded-xl border border-[#D7CCC8]/60 dark:border-[#4E342E] hover:border-[#3E2723] transition-all" data-title="<?= strtolower(htmlspecialchars($nama_alb)); ?>">
            
            <div class="relative group overflow-hidden rounded-lg aspect-video bg-gray-100 dark:bg-gray-800 cursor-pointer" onclick='aksesAlbum(<?= $id_booking; ?>, "<?= htmlspecialchars($nama_alb); ?>", "<?= htmlspecialchars($tgl_alb); ?>", <?= json_encode($list_foto_sesi); ?>, <?= $sudah_rating ? "true" : "false"; ?>)'>
              
              <img class="w-full h-full object-cover rounded-lg <?= !$sudah_rating ? 'blur-xs brightness-75' : 'group-hover:scale-105'; ?> transition-all duration-300" src="<?= htmlspecialchars($cover_url); ?>" alt="Album Cover" />
              
              <?php if (!$sudah_rating) { ?>
                <div class="absolute inset-0 flex flex-col items-center justify-center text-white bg-black/40 gap-1 p-1 text-center">
                  <i class="fa-solid fa-lock text-sm text-amber-400"></i>
                  <span class="text-[9px] font-bold px-2 py-0.5 bg-amber-500/90 rounded-full shadow">Beri Rating</span>
                </div>
              <?php } else { ?>
                <span class="absolute top-1.5 right-1.5 bg-black/70 backdrop-blur-xs text-white text-[9px] font-bold px-1.5 py-0.5 rounded">
                  <i class="fa-solid fa-images mr-0.5"></i><?= $total_foto_sesi; ?>
                </span>

                <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                  <span class="px-2.5 py-1 bg-white text-[#3E2723] text-[11px] font-bold rounded-lg shadow-md flex items-center gap-1">
                    <i class="fa-solid fa-expand"></i> Lihat
                  </span>
                </div>
              <?php } ?>
            </div>

            <div class="flex flex-col gap-0.5">
              <h4 class="text-[#3E2723] dark:text-white text-xs font-bold truncate"><?= htmlspecialchars($nama_alb); ?></h4>
              <span class="text-[#6D4C41] dark:text-gray-400 text-[10px]"><?= date('d M Y', strtotime($tgl_alb)); ?></span>
            </div>

            <div class="flex justify-between items-center pt-2 border-t border-[#D7CCC8]/40 dark:border-[#4E342E]">
              <?php if ($sudah_rating) { ?>
                <span class="px-2 py-0.5 bg-emerald-50 text-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-400 text-[10px] font-semibold rounded-full">Siap</span>
                <button type="button" onclick='openAlbumModal("<?= htmlspecialchars($nama_alb); ?>", "<?= htmlspecialchars($tgl_alb); ?>", <?= json_encode($list_foto_sesi); ?>)' class="text-[11px] text-[#5D4037] dark:text-[#D7CCC8] font-bold hover:underline">
                  Buka →
                </button>
              <?php } else { ?>
                <span class="px-2 py-0.5 bg-amber-50 text-amber-800 dark:bg-amber-950/40 dark:text-amber-400 text-[10px] font-semibold rounded-full">Terkunci</span>
                <button type="button" onclick='openRatingModal(<?= $id_booking; ?>, "<?= htmlspecialchars($nama_alb); ?>")' class="text-[11px] text-amber-700 dark:text-amber-400 font-bold hover:underline">
                  Nilai →
                </button>
              <?php } ?>
            </div>
          </div>

          <?php } 
          } else { ?>
            <div class="col-span-3 p-6 text-center bg-[#FDFBF7] dark:bg-[#1A1614] rounded-xl border border-dashed border-[#D7CCC8]/60 dark:border-[#4E342E]">
              <i class="fa-regular fa-images text-2xl mb-1 text-[#8D6E63] dark:text-gray-600 block"></i>
              <p class="text-xs text-[#6D4C41] dark:text-gray-400">Belum ada album foto yang tersedia.</p>
            </div>
          <?php } ?>
        </div>
      </div>

      <!-- Data Tersimpan -->
      <div class="lg:col-span-5 p-5 sm:p-6 bg-white dark:bg-[#231E1B] rounded-2xl border border-[#D7CCC8]/60 dark:border-[#4E342E] shadow-2xs flex flex-col gap-4 h-fit transition-colors">
        <div class="flex justify-between items-center">
          <h3 class="text-[#3E2723] dark:text-white text-base font-semibold">Data tersimpan</h3>
          <span class="px-3 py-0.5 bg-[#FDFBF7] dark:bg-[#1A1614] border border-[#D7CCC8]/40 dark:border-[#4E342E] text-[#6D4C41] dark:text-gray-400 text-[11px] font-semibold rounded-full">1 profil</span>
        </div>

        <div class="flex items-center gap-3">
          <div class="w-10 h-10 bg-[#F5EBE6] dark:bg-[#3E2723] rounded-full flex items-center justify-center text-[#5D4037] dark:text-[#D7CCC8] text-xs font-bold shrink-0 border border-[#D7CCC8]/50">
            <?= htmlspecialchars($pelanggan['inisial'] ?? substr($pelanggan['nama'], 0, 1)); ?>
          </div>
          <div class="flex flex-col truncate">
            <span class="text-[#3E2723] dark:text-white text-sm font-semibold truncate"><?= htmlspecialchars($pelanggan['nama']); ?></span>
            <span class="text-[#6D4C41] dark:text-gray-400 text-[11px]">Akun Terdaftar SIMAVIS</span>
          </div>
        </div>

        <div class="flex flex-col gap-2 text-xs text-[#3E2723] dark:text-gray-300">
          <div class="flex items-center gap-2 truncate">
            <span class="flex items-center gap-1.5 truncate"><i class="fa-solid fa-envelope text-[#5D4037] dark:text-[#D7CCC8]"></i> <?= htmlspecialchars($pelanggan['email']); ?></span>
          </div>
          <div class="flex items-center gap-2">
            <span class="flex items-center gap-1.5"><i class="fa-solid fa-phone text-[#5D4037] dark:text-[#D7CCC8]"></i> <?= htmlspecialchars($pelanggan['no_hp']); ?></span>
          </div>
        </div>

        <a href="login.php" onclick="return confirm('Apakah Anda yakin ingin keluar?');" class="h-10 w-full bg-red-600 hover:bg-red-700 transition-colors text-white font-semibold text-xs rounded-xl flex items-center justify-center gap-1.5 shadow-2xs mt-2">
          <span>Keluar (Logout)</span>
        </a>
      </div>

    </div>

  </main>

  <!-- ================= MODAL RATING BINTANG & ULASAN ================= -->
  <div id="ratingModal" class="fixed inset-0 bg-black/75 z-50 hidden flex justify-center items-center p-4 backdrop-blur-xs">
    <div class="bg-white dark:bg-[#231E1B] text-[#3E2723] dark:text-white rounded-2xl w-full max-w-md overflow-hidden shadow-2xl flex flex-col border border-[#D7CCC8]/60 dark:border-[#4E342E] transition-colors">
      <div class="p-4 sm:p-5 border-b border-[#D7CCC8]/40 dark:border-[#4E342E] flex justify-between items-center bg-[#FDFBF7] dark:bg-[#1A1614]">
        <h3 class="text-sm sm:text-base font-bold">Beri Rating & Ulasan Sesi</h3>
        <button type="button" onclick="closeRatingModal()" class="w-8 h-8 rounded-full bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 flex items-center justify-center text-gray-600 dark:text-gray-300 text-sm">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>

      <form action="" method="POST" class="p-5 flex flex-col gap-4">
        <input type="hidden" name="id_booking" id="rateIdBooking">
        <input type="hidden" name="rating" id="ratingValue" value="5">

        <div class="text-center flex flex-col gap-1">
          <p class="text-xs text-[#6D4C41] dark:text-gray-400">Bagaimana kepuasan Anda untuk paket:</p>
          <span id="rateAlbumLabel" class="text-sm font-bold">-</span>
        </div>

        <div class="flex justify-center gap-2 text-2xl py-2">
          <i class="fa-solid fa-star text-amber-400 cursor-pointer transition-colors" onclick="setRating(1)"></i>
          <i class="fa-solid fa-star text-amber-400 cursor-pointer transition-colors" onclick="setRating(2)"></i>
          <i class="fa-solid fa-star text-amber-400 cursor-pointer transition-colors" onclick="setRating(3)"></i>
          <i class="fa-solid fa-star text-amber-400 cursor-pointer transition-colors" onclick="setRating(4)"></i>
          <i class="fa-solid fa-star text-amber-400 cursor-pointer transition-colors" onclick="setRating(5)"></i>
        </div>

        <div class="flex flex-col gap-1.5">
          <label class="text-xs font-semibold">Ulasan / Pesan untuk Studio (opsional)</label>
          <textarea name="ulasan" rows="3" placeholder="Contoh: Hasil fotonya keren banget, fotografernya ramah!" class="w-full p-3 bg-[#FDFBF7] dark:bg-[#1A1614] rounded-xl border border-[#D7CCC8]/60 dark:border-[#4E342E] text-xs outline-none focus:border-[#5D4037] text-[#3E2723] dark:text-white"></textarea>
        </div>

        <button type="submit" name="submit_rating" class="w-full h-11 bg-[#3E2723] hover:bg-[#5D4037] text-white text-xs font-bold rounded-xl shadow-md transition-colors flex items-center justify-center gap-2">
          <span>Kirim Rating & Buka Album</span>
        </button>
      </form>
    </div>
  </div>

  <!-- ================= MODAL FULL GALERI ALBUM SESI ================= -->
  <div id="albumModal" class="fixed inset-0 bg-black/75 z-50 hidden flex justify-center items-center p-4 backdrop-blur-xs">
      <div class="bg-white dark:bg-[#231E1B] text-[#3E2723] dark:text-white rounded-2xl w-full max-w-4xl max-h-[90vh] overflow-hidden shadow-2xl flex flex-col border border-[#D7CCC8]/60 dark:border-[#4E342E] transition-colors">
          <div class="p-4 sm:p-5 border-b border-[#D7CCC8]/40 dark:border-[#4E342E] flex justify-between items-center bg-[#FDFBF7] dark:bg-[#1A1614]">
              <div>
                  <h3 class="text-sm sm:text-base font-bold" id="modalAlbumTitle">Galeri Foto Sesi</h3>
                  <p class="text-xs text-[#6D4C41] dark:text-gray-400" id="modalAlbumSubtitle">-</p>
              </div>
              <button type="button" onclick="closeAlbumModal()" class="w-8 h-8 rounded-full bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 flex items-center justify-center text-gray-600 dark:text-gray-300 text-sm">
                  <i class="fa-solid fa-xmark"></i>
              </button>
          </div>

          <div class="p-4 sm:p-6 overflow-y-auto grow">
              <div id="modalPhotoGrid" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
                  <!-- Diisi otomatis oleh JS -->
              </div>
          </div>

          <div class="p-4 border-t border-[#D7CCC8]/40 dark:border-[#4E342E] bg-[#FDFBF7] dark:bg-[#1A1614] flex justify-between items-center text-xs">
              <span class="text-[#6D4C41] dark:text-gray-400" id="modalAlbumCount">Total: 0 Foto</span>
              <button type="button" onclick="closeAlbumModal()" class="px-4 py-2 bg-white dark:bg-[#231E1B] border border-[#D7CCC8]/60 dark:border-[#4E342E] hover:bg-[#F5EBE6] dark:hover:bg-[#3E2723] text-[#3E2723] dark:text-white font-bold rounded-xl transition-colors">
                  Tutup
              </button>
          </div>
      </div>
  </div>

  <!-- ================= FOOTER ================= -->
  <footer class="w-full max-w-[1140px] mx-auto px-4 sm:px-6 lg:px-12 py-5 flex flex-col sm:flex-row justify-between items-center gap-2 text-xs text-[#8D6E63] dark:text-gray-500">
    <span>© 2026 SIMAVIS · Vibes Studio Jatibarang</span>
  </footer>

<script>
  document.addEventListener("DOMContentLoaded", function() {
    if (localStorage.getItem('theme') === 'dark') {
      document.documentElement.classList.add('dark');
      const icon = document.getElementById('themeIcon');
      if (icon) icon.className = 'fa-solid fa-sun';
    }
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

  function toggleLihatLainnya() {
      const hiddenRows = document.querySelectorAll('.riwayat-tambahan');
      const btn = document.getElementById('btnLihatLainnya');
      
      hiddenRows.forEach(row => {
          row.classList.toggle('hidden');
      });

      if (btn.innerText.includes('Lihat Lainnya')) {
          btn.innerText = 'Sembunyikan Sebagian';
      } else {
          let totalHidden = hiddenRows.length;
          btn.innerText = 'Lihat Lainnya (' + totalHidden + ' riwayat lainnya)';
      }
  }

  function filterAlbum() {
      let input = document.getElementById('searchAlbum').value.toLowerCase().trim();
      let cards = document.querySelectorAll('.album-item');

      cards.forEach(card => {
          let title = card.getAttribute('data-title');
          if (title.includes(input)) {
              card.style.display = "flex";
          } else {
              card.style.display = "none";
          }
      });
  }

  function aksesAlbum(idBooking, nama, tanggal, fotoArray, sudahRating) {
      if (!sudahRating) {
          openRatingModal(idBooking, nama);
      } else {
          openAlbumModal(nama, tanggal, fotoArray);
      }
  }

  function openRatingModal(idBooking, namaAlbum) {
      document.getElementById('rateIdBooking').value = idBooking;
      document.getElementById('rateAlbumLabel').innerText = '"' + namaAlbum + '"';
      document.getElementById('ratingModal').classList.remove('hidden');
      document.body.style.overflow = 'hidden';
  }

  function closeRatingModal() {
      document.getElementById('ratingModal').classList.add('hidden');
      document.body.style.overflow = 'auto';
  }

  function setRating(score) {
      document.getElementById('ratingValue').value = score;
      let stars = document.querySelectorAll('#ratingModal .fa-star');
      stars.forEach((star, index) => {
          if (index < score) {
              star.classList.remove('text-gray-300');
              star.classList.add('text-amber-400');
          } else {
              star.classList.remove('text-amber-400');
              star.classList.add('text-gray-300');
          }
      });
  }

  function openAlbumModal(namaAlbum, tanggal, fotoArray) {
      document.getElementById('modalAlbumTitle').innerText = namaAlbum;
      document.getElementById('modalAlbumSubtitle').innerText = 'Tanggal Sesi: ' + tanggal;
      document.getElementById('modalAlbumCount').innerText = 'Total: ' + fotoArray.length + ' Foto';

      const gridContainer = document.getElementById('modalPhotoGrid');
      gridContainer.innerHTML = '';

      fotoArray.forEach((imgUrl, index) => {
          const photoCard = document.createElement('div');
          photoCard.className = 'group relative rounded-xl overflow-hidden border border-[#D7CCC8]/60 dark:border-[#4E342E] bg-gray-100 dark:bg-gray-800 flex flex-col';
          photoCard.innerHTML = `
              <div class="aspect-square overflow-hidden bg-gray-200 dark:bg-gray-900 relative">
                  <img src="${imgUrl}" alt="Foto ${index + 1}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
              </div>
              <div class="p-2 bg-white dark:bg-[#231E1B] flex justify-between items-center border-t border-[#D7CCC8]/40 dark:border-[#4E342E]">
                  <span class="text-[10px] text-[#6D4C41] dark:text-gray-400 font-semibold">Foto #${index + 1}</span>
                  <a href="${imgUrl}" download class="px-2.5 py-1 bg-[#3E2723] hover:bg-[#5D4037] text-white rounded-lg text-[11px] font-semibold flex items-center gap-1 transition-colors">
                      <i class="fa-solid fa-download"></i>
                      <span>Unduh</span>
                  </a>
              </div>
          `;
          gridContainer.appendChild(photoCard);
      });

      document.getElementById('albumModal').classList.remove('hidden');
      document.body.style.overflow = 'hidden';
  }

  function closeAlbumModal() {
      document.getElementById('albumModal').classList.add('hidden');
      document.body.style.overflow = 'auto';
  }
</script>

</body>
</html>
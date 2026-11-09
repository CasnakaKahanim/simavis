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

$id_pelanggan =$_SESSION['id_pelanggan'];

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

// 5. Ambil DOKUMEN ALBUM PER SESI (Grouping berdasarkan nama_album & tanggal_foto)
// Ambil DOKUMEN ALBUM PER SESI BESERTA STATUS RATING BERDASARKAN ID BOOKING
$query_group_album = mysqli_query($conn, "
    SELECT a.nama_album, a.tanggal_foto, a.status_album, COUNT(a.id) as total_foto, MIN(a.gambar_url) as cover_url,
           b.id as id_booking, COALESCE(b.rating, 0) as rating, COALESCE(b.ulasan, '') as ulasan
    FROM album_foto a
    LEFT JOIN booking b ON a.id_pelanggan = b.id_pelanggan AND a.nama_album = b.nama_paket
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
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SIMAVIS - Akun & Riwayat Klien</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style> body { font-family: 'Inter', sans-serif; } </style>
</head>
<body class="bg-[#F6F8FC] text-[#0C4A6E] antialiased flex flex-col min-h-screen">

  <!-- ================= NAVBAR ================= -->
  <header class="sticky top-0 z-40 w-full h-[68px] px-6 lg:px-12 bg-white/95 backdrop-blur-md border-b border-[#E2E8F0] flex justify-between items-center shadow-2xs">
    <a href="../index.php" class="flex items-center gap-2 shrink-0">
      <img src="asset/logo.jpeg" alt="Logo SIMAVIS" class="w-8 h-8 rounded-lg object-cover shadow-xs" />
      <span class="text-[#0C4A6E] text-lg font-extrabold tracking-tight">SIMAVIS</span>
    </a>

    <div class="flex items-center gap-4 shrink-0">
      <div class="flex items-center gap-2 cursor-pointer">
        <div class="w-8 h-8 bg-[#E6F7F8] rounded-full flex items-center justify-center text-[#0891B2] text-xs font-bold">
          <?= htmlspecialchars($pelanggan['inisial'] ?? substr($pelanggan['nama'], 0, 1)); ?>
        </div>
        <span class="text-[#0C4A6E] text-xs font-semibold"><?= htmlspecialchars($pelanggan['nama']); ?></span>
      </div>

      <a href="booking.php" class="h-9 px-4 bg-[#0891B2] hover:bg-[#06748f] transition-colors text-white text-xs font-semibold rounded-lg flex items-center gap-1.5 shadow-2xs whitespace-nowrap">
        <span>Booking lagi</span>
        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
      </a>
    </div>
  </header>

  <!-- ================= MAIN CONTAINER ================= -->
  <main class="w-full max-w-[1140px] mx-auto px-6 lg:px-12 py-8 flex flex-col gap-6 flex-grow">

    <!-- TITLE & 4 SUMMARY CARDS -->
    <div class="flex flex-col gap-6">
      <div class="flex flex-col gap-1">
        <h1 class="text-[#0C4A6E] text-2xl lg:text-3xl font-semibold">Halooo <?= htmlspecialchars(explode(' ', $pelanggan['nama'])[0]); ?>, Selamat Datang kembali</h1>
        <p class="text-[#64748B] text-xs">Halo, <?= htmlspecialchars(explode(' ', $pelanggan['nama'])[0]); ?>. Ini riwayat pribadi akun Anda. Informasi studio tetap bisa dilihat tanpa login.</p>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="p-4 bg-white rounded-xl border border-[#E2E8F0] shadow-2xs flex flex-col gap-2">
          <span class="text-xs text-[#64748B]">Total booking</span>
          <span class="text-[#0C4A6E] text-2xl font-bold"><?= $total_booking; ?> pesanan</span>
          <span class="text-[#64748B] text-[11px]">Seluruh riwayat booking Anda</span>
        </div>

        <div class="p-4 bg-white rounded-xl border border-[#E2E8F0] shadow-2xs flex flex-col gap-2">
          <span class="text-xs text-[#64748B]">Mendatang</span>
          <span class="text-[#0C4A6E] text-2xl font-bold"><?= $jml_mendatang; ?> sesi</span>
          <span class="text-[#64748B] text-[11px]"><?= $sesi_berikutnya ? date('d F Y', strtotime($sesi_berikutnya['tanggal_sesi'])) : 'Tidak ada jadwal'; ?></span>
        </div>

        <div class="p-4 bg-white rounded-xl border border-[#E2E8F0] shadow-2xs flex flex-col gap-2">
          <span class="text-xs text-[#64748B]">Selesai</span>
          <span class="text-[#0C4A6E] text-2xl font-bold"><?= $jml_selesai; ?> sesi</span>
          <span class="text-[#64748B] text-[11px]">Momen yang sudah diabadikan</span>
        </div>

        <div class="p-4 bg-white rounded-xl border border-[#E2E8F0] shadow-2xs flex flex-col gap-2">
          <span class="text-xs text-[#64748B]">Foto siap</span>
          <span class="text-[#0C4A6E] text-2xl font-bold"><?= $total_semua_foto; ?> foto</span>
          <span class="text-[#64748B] text-[11px]">Tersedia dalam album digital</span>
        </div>
      </div>
    </div>

    <!-- SESI BERIKUTNYA (Jika Ada) -->
    <?php if ($sesi_berikutnya) { ?>
    <div class="p-5 bg-white rounded-xl border border-[#E2E8F0] shadow-2xs flex flex-col gap-4">
      <div class="flex justify-between items-center">
        <h3 class="text-[#0C4A6E] text-base font-semibold">Sesi berikutnya</h3>
        <span class="px-2.5 py-1 bg-[#E6F7F8] text-[#0891B2] text-[11px] font-semibold rounded-full">Mendatang · <?= htmlspecialchars($sesi_berikutnya['status_pembayaran']); ?></span>
      </div>
      <div class="flex flex-col sm:flex-row items-start gap-5">
        <div class="w-24 sm:w-28 h-32 bg-[#E6F7F8] rounded-xl flex flex-col justify-center items-center gap-0.5 shrink-0 text-[#0891B2]">
          <span class="text-[10px] font-semibold tracking-wider"><?= strtoupper(date('F', strtotime($sesi_berikutnya['tanggal_sesi']))); ?></span>
          <span class="text-4xl font-bold leading-none"><?= date('d', strtotime($sesi_berikutnya['tanggal_sesi'])); ?></span>
          <span class="text-[11px]"><?= date('Y', strtotime($sesi_berikutnya['tanggal_sesi'])); ?></span>
        </div>
        <div class="flex flex-col gap-2 text-xs text-[#64748B]">
          <span class="text-[11px] text-[#64748B]"><?= htmlspecialchars($sesi_berikutnya['kode_booking']); ?></span>
          <h4 class="text-[#0C4A6E] text-xl font-semibold"><?= htmlspecialchars($sesi_berikutnya['nama_paket']); ?></h4>
          <span>Jadwal: <?= htmlspecialchars($sesi_berikutnya['jam_sesi']); ?></span>
          <span>Peserta: <?= htmlspecialchars($sesi_berikutnya['jumlah_peserta']); ?></span>
          <span class="text-[#0C4A6E] font-bold text-base">Total: Rp <?= number_format($sesi_berikutnya['total_harga'], 0, ',', '.'); ?></span>
        </div>
      </div>
    </div>
    <?php } ?>

    <!-- TABEL RIWAYAT BOOKING -->
    <div class="p-5 bg-white rounded-xl border border-[#E2E8F0] shadow-2xs flex flex-col gap-4">
      <div class="flex justify-between items-center">
        <h3 class="text-[#0C4A6E] text-base font-semibold">Riwayat booking</h3>
        <span class="px-2.5 py-0.5 bg-[#F6F8FC] text-[#64748B] text-[11px] font-semibold rounded-full"><?= $total_booking; ?> pesanan</span>
      </div>

      <div class="w-full overflow-x-auto">
        <table class="w-full text-left text-xs">
          <thead>
            <tr class="bg-[#F6F8FC] text-[#64748B] font-medium">
              <th class="p-3 rounded-l-lg">Paket / ID booking</th>
              <th class="p-3">Tanggal sesi</th>
              <th class="p-3">Total / pembayaran</th>
              <th class="p-3">Status sesi</th>
              <th class="p-3">Hasil foto</th>
              <th class="p-3 text-right rounded-r-lg">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-[#E2E8F0]">
            <?php if (mysqli_num_rows($query_riwayat) > 0) {
                while ($row = mysqli_fetch_assoc($query_riwayat)) { ?>
            <tr>
              <td class="p-3">
                <div class="flex flex-col">
                  <span class="text-[#0C4A6E] font-semibold"><?= htmlspecialchars($row['nama_paket']); ?></span>
                  <span class="text-[#64748B] text-[11px]"><?= htmlspecialchars($row['kode_booking']); ?></span>
                </div>
              </td>
              <td class="p-3">
                <div class="flex flex-col">
                  <span class="text-[#0C4A6E]"><?= date('d M Y', strtotime($row['tanggal_sesi'])); ?></span>
                  <span class="text-[#64748B] text-[11px]"><?= htmlspecialchars($row['jam_sesi']); ?></span>
                </div>
              </td>
              <td class="p-3">
                <div class="flex flex-col">
                  <span class="text-[#0C4A6E] font-semibold">Rp <?= number_format($row['total_harga'], 0, ',', '.'); ?></span>
                  <span class="text-[#16795E] text-[11px]"><?= htmlspecialchars($row['status_pembayaran']); ?></span>
                </div>
              </td>
              <td class="p-3">
                <span class="px-2.5 py-1 <?= ($row['status_sesi'] == 'Mendatang') ? 'bg-[#E6F7F8] text-[#0891B2]' : 'bg-[#EAF7F1] text-[#16795E]'; ?> text-[11px] font-semibold rounded-full inline-block">
                  <?= htmlspecialchars($row['status_sesi']); ?>
                </span>
              </td>
              <td class="p-3 text-[#64748B]">
                <?= ($row['status_sesi'] == 'Selesai') ? 'Tersedia' : 'Setelah sesi'; ?>
              </td>
              <td class="p-3 text-right">
                <span class="text-[#0891B2] font-semibold cursor-pointer">Detail →</span>
              </td>
            </tr>
            <?php } 
            } else { ?>
            <tr>
              <td colspan="6" class="p-4 text-center text-[#64748B]">Belum ada riwayat booking.</td>
            </tr>
            <?php } ?>
          </tbody>
        </table>
      </div>
    </div>

<!-- HASIL FOTO & DATA TERSIMPAN -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
      
      <!-- Hasil Foto / Album Digital (Kolom Kiri) -->
      <div class="lg:col-span-7 xl:col-span-8 p-5 bg-white rounded-xl border border-[#E2E8F0] shadow-2xs flex flex-col gap-4">
        
        <div class="flex justify-between items-center border-b border-[#E2E8F0] pb-3">
          <div>
            <h3 class="text-[#0C4A6E] text-base font-semibold">Hasil foto Anda</h3>
            <p class="text-[11px] text-[#64748B]">Setiap sesi foto ditampilkan dalam 1 album ringkas.</p>
          </div>
          <span class="text-xs bg-[#E6F7F8] text-[#0891B2] font-semibold px-3 py-1 rounded-full">
            <i class="fa-solid fa-camera mr-1"></i> SIMAVIS Gallery
          </span>
        </div>

        <!-- SEARCH BAR PENCARIAN ALBUM -->
        <div class="relative w-full">
          <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-[#64748B]">
            <i class="fa-solid fa-search text-xs"></i>
          </span>
          <input type="text" id="searchAlbum" onkeyup="filterAlbum()" placeholder="Cari nama album atau paket foto..." class="w-full pl-9 pr-4 py-2 bg-[#F8FAFC] border border-[#E2E8F0] rounded-xl text-xs text-[#0C4A6E] placeholder-[#94A3B8] focus:outline-none focus:border-[#0891B2] focus:bg-white transition-all" />
        </div>

        <!-- GRID ALBUM KARTU TUNGGAL PER SESI -->
        <div id="containerAlbum" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <?php if ($query_group_album && mysqli_num_rows($query_group_album) > 0) {
              while ($alb_group = mysqli_fetch_assoc($query_group_album)) { 
                  $nama_alb     = $alb_group['nama_album'];
                  $tgl_alb      = $alb_group['tanggal_foto'];
                  $id_booking   = $alb_group['id_booking'] ?? 0;
                  $sudah_rating = isset($alb_group['rating']) && $alb_group['rating'] > 0;

                  // Ambil seluruh foto milik album/sesi ini
                  $q_detail_foto = mysqli_query($conn, "SELECT * FROM album_foto WHERE id_pelanggan = $id_pelanggan AND nama_album = '" . mysqli_real_escape_string($conn,$nama_alb) . "' AND tanggal_foto = '" . mysqli_real_escape_string($conn,$tgl_alb) . "' ORDER BY id ASC");
                  
                  $list_foto_sesi = [];
                  while ($f = mysqli_fetch_assoc($q_detail_foto)) {
                      $list_foto_sesi[] = $f['gambar_url'];
                  }

                  $cover_url       = $alb_group['cover_url'];
                  $total_foto_sesi = $alb_group['total_foto'];
          ?>
          
          <!-- CARD KARTU SESI (Tambahkan class 'album-item' dan atribut 'data-title') -->
          <div class="album-item flex flex-col gap-3 p-3.5 bg-[#F8FAFC] rounded-xl border border-[#E2E8F0] hover:border-[#0891B2] transition-all" data-title="<?= strtolower(htmlspecialchars($nama_alb)); ?>">
            
            <!-- Cover Gambar Sampul -->
            <div class="relative group overflow-hidden rounded-lg aspect-video bg-gray-100 cursor-pointer" onclick='aksesAlbum(<?= $id_booking; ?>, "<?= htmlspecialchars($nama_alb); ?>", "<?= htmlspecialchars($tgl_alb); ?>", <?= json_encode($list_foto_sesi); ?>, <?= $sudah_rating ? "true" : "false"; ?>)'>
              
              <img class="w-full h-full object-cover rounded-lg <?= !$sudah_rating ? 'blur-xs brightness-75' : 'group-hover:scale-105'; ?> transition-all duration-300" src="<?= htmlspecialchars($cover_url); ?>" alt="Album Cover" />
              
              <?php if (!$sudah_rating) { ?>
                <div class="absolute inset-0 flex flex-col items-center justify-center text-white bg-black/40 gap-1">
                  <i class="fa-solid fa-lock text-base text-amber-400"></i>
                  <span class="text-[10px] font-bold px-2.5 py-1 bg-amber-500/90 rounded-full shadow">Beri Rating Dulu</span>
                </div>
              <?php } else { ?>
                <span class="absolute top-2 right-2 bg-black/70 backdrop-blur-xs text-white text-[10px] font-bold px-2 py-0.5 rounded-md">
                  <i class="fa-solid fa-images mr-1"></i><?= $total_foto_sesi; ?> Foto
                </span>

                <!-- Hover Overlay -->
                <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                  <span class="px-3 py-1.5 bg-white text-[#0C4A6E] text-xs font-bold rounded-lg shadow-md flex items-center gap-1">
                    <i class="fa-solid fa-expand"></i> Lihat Semua (<?= $total_foto_sesi; ?>)
                  </span>
                </div>
              <?php } ?>
            </div>

            <div class="flex flex-col gap-0.5">
              <h4 class="text-[#0C4A6E] text-sm font-bold truncate"><?= htmlspecialchars($nama_alb); ?></h4>
              <span class="text-[#64748B] text-[11px]">
                <?= date('d M Y', strtotime($tgl_alb)); ?> · <?= $total_foto_sesi; ?> foto edit
              </span>
            </div>

            <div class="flex justify-between items-center pt-2 border-t border-[#E2E8F0]">
              <?php if ($sudah_rating) { ?>
                <span class="px-2.5 py-0.5 bg-[#EAF7F1] text-[#16795E] text-[11px] font-semibold rounded-full">Siap diunduh</span>
                <button type="button" onclick='openAlbumModal("<?= htmlspecialchars($nama_alb); ?>", "<?= htmlspecialchars($tgl_alb); ?>", <?= json_encode($list_foto_sesi); ?>)' class="text-xs text-[#0891B2] font-bold hover:underline flex items-center gap-1">
                  Lihat Semua →
                </button>
              <?php } else { ?>
                <span class="px-2.5 py-0.5 bg-amber-50 text-amber-600 text-[11px] font-semibold rounded-full">Belum Dinilai</span>
                <button type="button" onclick='openRatingModal(<?= $id_booking; ?>, "<?= htmlspecialchars($nama_alb); ?>")' class="text-xs text-amber-600 font-bold hover:underline flex items-center gap-1">
                  Beri Rating Dulu →
                </button>
              <?php } ?>
            </div>
          </div>

          <?php } 
          } else { ?>
            <div class="col-span-2 p-8 text-center bg-gray-50 rounded-xl border border-dashed border-gray-200">
              <i class="fa-regular fa-images text-3xl mb-2 text-gray-300 block"></i>
              <p class="text-xs text-[#64748B]">Belum ada album foto yang tersedia untuk diunduh.</p>
            </div>
          <?php } ?>
        </div>
      </div>

      <!-- Data Tersimpan (Kolom Kanan) -->
      <div class="lg:col-span-5 xl:col-span-4 p-5 bg-white rounded-xl border border-[#E2E8F0] shadow-2xs flex flex-col gap-4">
        <div class="flex justify-between items-center">
          <h3 class="text-[#0C4A6E] text-base font-semibold">Data tersimpan</h3>
          <span class="px-2.5 py-0.5 bg-[#F6F8FC] text-[#64748B] text-[11px] font-semibold rounded-full">1 profil</span>
        </div>

        <div class="flex items-center gap-3">
          <div class="w-10 h-10 bg-[#E6F7F8] rounded-full flex items-center justify-center text-[#0891B2] text-xs font-bold shrink-0">
            <?= htmlspecialchars($pelanggan['inisial'] ?? substr($pelanggan['nama'], 0, 1)); ?>
          </div>
          <div class="flex flex-col">
            <span class="text-[#0C4A6E] text-sm font-semibold"><?= htmlspecialchars($pelanggan['nama']); ?></span>
            <span class="text-[#64748B] text-[11px]">Akun Terdaftar SIMAVIS</span>
          </div>
        </div>

        <div class="flex flex-col gap-2 text-xs text-[#0C4A6E]">
          <!-- Mengganti emoji surat dengan ikon email Font Awesome -->
          <div class="flex items-center gap-2">
            <span class="flex items-center gap-1.5"><i class="fa-solid fa-envelope text-[#0891B2]"></i> <?= htmlspecialchars($pelanggan['email']); ?></span>
          </div>
          
          <!-- Mengganti emoji telepon dengan ikon telepon Font Awesome -->
          <div class="flex items-center gap-2">
            <span class="flex items-center gap-1.5"><i class="fa-solid fa-phone text-[#0891B2]"></i> <?= htmlspecialchars($pelanggan['no_hp']); ?></span>
          </div>
        </div>

        <a href="login.php" class="h-9 w-full bg-red-600 hover:bg-red-700 transition-colors text-white font-semibold text-xs rounded-lg flex items-center justify-center gap-1.5 shadow-2xs mt-2">
          <span>Keluar (Logout)</span>
        </a>
      </div>

    </div>

  </main>

  <!-- ================= MODAL FULL GALERI ALBUM SESI ================= -->
  <div id="albumModal" class="fixed inset-0 bg-black/75 z-50 hidden flex justify-center items-center p-4 backdrop-blur-xs">
      <div class="bg-white rounded-2xl w-full max-w-4xl max-h-[90vh] overflow-hidden shadow-2xl flex flex-col">
          
          <!-- Header Modal -->
          <div class="p-4 sm:p-5 border-b border-[#E2E8F0] flex justify-between items-center bg-[#F8FAFC]">
              <div>
                  <h3 class="text-sm sm:text-base font-bold text-[#0C4A6E]" id="modalAlbumTitle">Galeri Foto Sesi</h3>
                  <p class="text-xs text-[#64748B]" id="modalAlbumSubtitle">-</p>
              </div>
              <button type="button" onclick="closeAlbumModal()" class="w-8 h-8 rounded-full bg-gray-200 hover:bg-gray-300 flex items-center justify-center text-gray-600 text-sm">
                  <i class="fa-solid fa-xmark"></i>
              </button>
          </div>

          <!-- Body / Grid Seluruh Foto Sesi -->
          <div class="p-4 sm:p-6 overflow-y-auto grow">
              <div id="modalPhotoGrid" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
                  <!-- Diisi otomatis oleh JS -->
              </div>
          </div>

          <!-- Footer Modal -->
          <div class="p-4 border-t border-[#E2E8F0] bg-[#F8FAFC] flex justify-between items-center text-xs">
              <span class="text-[#64748B]" id="modalAlbumCount">Total: 0 Foto</span>
              <button type="button" onclick="closeAlbumModal()" class="px-4 py-2 bg-[#F1F5F9] hover:bg-[#E2E8F0] text-[#0C4A6E] font-bold rounded-xl transition-colors">
                  Tutup
              </button>
          </div>
      </div>
  </div>

  <!-- ================= FOOTER ================= -->
  <footer class="w-full max-w-[1140px] mx-auto px-6 lg:px-12 py-5 flex flex-col sm:flex-row justify-between items-center gap-2 text-xs text-[#64748B]">
    <span>© 2026 SIMAVIS · Vibes Studio Jatibarang</span>
  </footer>

<script>
  // Pencarian fleksibel (cukup ketik sebagian kata saja)
  function filterAlbum() {
      let input = document.getElementById('searchAlbum').value.toLowerCase().trim();
      let cards = document.querySelectorAll('.album-item');

      cards.forEach(card => {
          let title = card.getAttribute('data-title');
          // Menggunakan .includes() agar pencocokan parsial berfungsi otomatis
          if (title.includes(input)) {
              card.style.display = "flex";
          } else {
              card.style.display = "none";
          }
      });
  }

  // Validasi Akses Album & Pemicu Modal Rating
  function aksesAlbum(idBooking, nama, tanggal, fotoArray, sudahRating) {
      if (!sudahRating) {
          openRatingModal(idBooking, nama);
      } else {
          openAlbumModal(nama, tanggal, fotoArray);
      }
  }

  // Pastikan elemen modal terpanggil dengan benar
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

  // Interaktif Rating Bintang
  function setRating(score) {
      document.getElementById('ratingValue').value = score;
      // Memberikan efek visual warna pada bintang yang dipilih
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

  // Modal Galeri Foto
  function openAlbumModal(namaAlbum, tanggal, fotoArray) {
      document.getElementById('modalAlbumTitle').innerText = namaAlbum;
      document.getElementById('modalAlbumSubtitle').innerText = 'Tanggal Sesi: ' + tanggal;
      document.getElementById('modalAlbumCount').innerText = 'Total: ' + fotoArray.length + ' Foto';

      const gridContainer = document.getElementById('modalPhotoGrid');
      gridContainer.innerHTML = '';

      fotoArray.forEach((imgUrl, index) => {
          const photoCard = document.createElement('div');
          photoCard.className = 'group relative rounded-xl overflow-hidden border border-[#E2E8F0] bg-gray-100 flex flex-col';
          photoCard.innerHTML = `
              <div class="aspect-square overflow-hidden bg-gray-200 relative">
                  <img src="${imgUrl}" alt="Foto ${index + 1}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
              </div>
              <div class="p-2 bg-white flex justify-between items-center border-t border-[#E2E8F0]">
                  <span class="text-[10px] text-[#64748B] font-semibold">Foto #${index + 1}</span>
                  <a href="${imgUrl}" download class="px-2.5 py-1 bg-[#0891B2] hover:bg-[#06748f] text-white rounded-lg text-[11px] font-semibold flex items-center gap-1 transition-colors">
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
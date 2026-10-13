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

// 5. Ambil Hasil Foto / Album
$query_album = mysqli_query($conn, "SELECT * FROM album_foto WHERE id_pelanggan = $id_pelanggan");
?>

<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SIMAVIS - Akun & Riwayat Klien</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <style> body { font-family: 'Inter', sans-serif; } </style>
</head>
<body class="bg-[#F6F8FC] text-[#0C4A6E] antialiased flex flex-col min-h-screen">

  <!-- ================= NAVBAR ================= -->
  <header class="sticky top-0 z-50 w-full h-[68px] px-6 lg:px-12 bg-white/95 backdrop-blur-md border-b border-[#E2E8F0] flex justify-between items-center shadow-2xs">
        <a href="../index.php" class="flex items-center gap-2 shrink-0">
        <img src="asset/logo.jpeg" alt="Logo SIMAVIS" class="w-8 h-8 rounded-lg object-cover shadow-xs" />
        <span class="text-[#0C4A6E] text-lg font-extrabold tracking-tight">SIMAVIS</span></a>
        <div class="w-8 h-8 bg-[#0891B2] rounded-lg flex justify-center items-center shadow-xs">
        <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h0.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
        </svg>
      </div>
      <span class="text-[#0C4A6E] text-lg font-extrabold tracking-tight">SIMAVIS</span>
    </a>

    <div class="flex items-center gap-4 shrink-0">
      <div class="flex items-center gap-2 cursor-pointer">
        <div class="w-8 h-8 bg-[#E6F7F8] rounded-full flex items-center justify-center text-[#0891B2] text-xs font-bold">
          <?= htmlspecialchars($pelanggan['inisial']); ?>
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
          <span class="text-[#0C4A6E] text-2xl font-bold"><?= mysqli_num_rows($query_album) * 10; ?> foto</span>
          <span class="text-[#64748B] text-[11px]">Tersedia dalam album</span>
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
      
      <!-- Hasil Foto (Kolom Kiri) -->
      <div class="lg:col-span-7 xl:col-span-8 p-5 bg-white rounded-xl border border-[#E2E8F0] shadow-2xs flex flex-col gap-4">
        <div class="flex justify-between items-center">
          <h3 class="text-[#0C4A6E] text-base font-semibold">Hasil foto Anda</h3>
          <span class="text-[#64748B] text-xs">Album foto digital</span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <?php if (mysqli_num_rows($query_album) > 0) {
              while ($alb = mysqli_fetch_assoc($query_album)) { ?>
          <div class="flex flex-col gap-3">
            <img class="w-full h-36 object-cover rounded-lg" src="<?= htmlspecialchars($alb['gambar_url']); ?>" alt="Album" />
            <div class="flex flex-col gap-0.5">
              <h4 class="text-[#0C4A6E] text-sm font-semibold"><?= htmlspecialchars($alb['nama_album']); ?></h4>
              <span class="text-[#64748B] text-[11px]"><?= htmlspecialchars($alb['tanggal_foto']); ?> · <?= htmlspecialchars($alb['jumlah_foto']); ?></span>
            </div>
            <span class="px-2.5 py-0.5 bg-[#EAF7F1] text-[#16795E] text-[11px] font-semibold rounded-full w-fit"><?= htmlspecialchars($alb['status_album']); ?></span>
          </div>
          <?php } 
          } else { ?>
          <p class="text-xs text-[#64748B]">Belum ada album foto yang tersedia.</p>
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
          <div class="w-10 h-10 bg-[#E6F7F8] rounded-full flex items-center justify-center text-[#0891B2] text-xs font-bold shrink-0"><?= htmlspecialchars($pelanggan['inisial']); ?></div>
          <div class="flex flex-col">
            <span class="text-[#0C4A6E] text-sm font-semibold"><?= htmlspecialchars($pelanggan['nama']); ?></span>
            <span class="text-[#64748B] text-[11px]">Akun Terdaftar SIMAVIS</span>
          </div>
        </div>

        <div class="flex flex-col gap-2 text-xs text-[#0C4A6E]">
          <div class="flex items-center gap-2"><span>📧 <?= htmlspecialchars($pelanggan['email']); ?></span></div>
          <div class="flex items-center gap-2"><span>📞 <?= htmlspecialchars($pelanggan['no_hp']); ?></span></div>
        </div>

        <a href="logout.php" class="h-9 w-full bg-red-600 hover:bg-red-700 transition-colors text-white font-semibold text-xs rounded-lg flex items-center justify-center gap-1.5 shadow-2xs mt-2">
          <span>Keluar (Logout)</span>
        </a>
      </div>

    </div>

  </main>

  <!-- ================= FOOTER ================= -->
  <footer class="w-full max-w-[1140px] mx-auto px-6 lg:px-12 py-5 flex flex-col sm:flex-row justify-between items-center gap-2 text-xs text-[#64748B]">
    <span>© 2026 SIMAVIS · Vibes Studio Jatibarang</span>
  </footer>

</body>
</html>
<?php
session_start();
// 1. Hubungkan ke Database SIMAVIS
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

// 2. AMBIL ID BOOKING / PESANAN DARI URL
$id_booking = isset($_GET['id_booking']) ? intval($_GET['id_booking']) : (isset($_GET['id']) ? intval($_GET['id']) : 0);

// Query Detail Pesanan
$query_booking = "SELECT * FROM booking WHERE id = '$id_booking' OR kode_booking = '$id_booking' LIMIT 1";
$res_booking   = mysqli_query($conn, $query_booking);

if ($res_booking && mysqli_num_rows($res_booking) > 0) {
    $data_booking    = mysqli_fetch_assoc($res_booking);
    $id_booking_db   = $data_booking['id'];
    $id_pelanggan_db = $data_booking['id_pelanggan'];

    // Ambil Data Pelanggan Terpisah dari Tabel Pelanggan
    $res_pel = mysqli_query($conn, "SELECT * FROM pelanggan WHERE id = '$id_pelanggan_db' LIMIT 1");
    $data_pel = mysqli_fetch_assoc($res_pel);

    $nama_pelanggan = $data_booking['nama_lengkap'] ?? $data_pel['nama'] ?? 'Pelanggan';
    $no_whatsapp    = $data_booking['no_whatsapp'] ?? $data_pel['no_whatsapp'] ?? '';
} else {
    // Fallback jika tidak ditemukan
    header("Location: galeri.php");
    exit;
}

// 3. PROSES HAPUS FOTO JIKA ADA ACTION DELETE
if (isset($_GET['action']) && $_GET['action'] === 'hapus' && isset($_GET['id_foto'])) {
    $id_foto_hapus = intval($_GET['id_foto']);
    
    // Ambil data file sebelum dihapus dari DB
    $q_file = mysqli_query($conn, "SELECT * FROM album_foto WHERE id = '$id_foto_hapus' LIMIT 1");
    if ($q_file && mysqli_num_rows($q_file) > 0) {
        $data_foto = mysqli_fetch_assoc($q_file);
        $file_path = $data_foto['gambar_url'];

        // Hapus berkas fisik di folder uploads jika file lokal
        if (file_exists($file_path) && !filter_var($file_path, FILTER_VALIDATE_URL)) {
            unlink($file_path);
        }

        // Hapus record dari database
        mysqli_query($conn, "DELETE FROM album_foto WHERE id = '$id_foto_hapus'");
    }
    
    // Refresh halaman
    header("Location: DetailFolder.php?id_booking=" . $id_booking_db);
    exit;
}

// 4. AMBIL DAFTAR HASIL FOTO DARI TABEL ALBUM_FOTO
$query_foto = "SELECT * FROM album_foto WHERE id_pelanggan = '$id_pelanggan_db' ORDER BY id DESC";
$res_foto   = mysqli_query($conn, $query_foto);
$total_foto = mysqli_num_rows($res_foto);

// Total order untuk badge sidebar
$q_total_order = mysqli_query($conn, "SELECT COUNT(*) AS total FROM booking");
$r_total_order = mysqli_fetch_assoc($q_total_order);
$total_order   = $r_total_order['total'] ?? 0;

// URL Link Unduh Pelanggan
$link_unduh_user = "user/akun.php?kode=" . $data_booking['kode_booking'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SIMAVIS - Detail Folder Pesanan</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <!-- Tailwind CSS CDN -->
  <script src="https://cdn.tailwindcss.com"></script>
  <!-- Google Font: Instrument Sans -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>
    body { font-family: 'Instrument Sans', sans-serif; }
    .custom-scrollbar::-webkit-scrollbar { width: 6px; }
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
            <a href="galeri.php" class="w-full px-4 py-3 bg-[#0891B2] rounded-xl flex justify-start items-center gap-3 text-white font-semibold text-sm shadow-md">
                <i class="fa-solid fa-images w-5 text-center"></i>
                <span class="flex-1">Galeri & QR</span>
            </a>

            <!-- Paket & Tema Foto -->
            <a href="paket.php" class="w-full px-4 py-3 rounded-xl flex justify-start items-center gap-3 text-white/85 hover:bg-white/10 transition-colors font-medium text-sm">
            <i class="fa-solid fa-boxes-packing w-5 text-center"></i>
            <span class="flex-1">Paket & Tema</span>
            </a>
            
            <!-- Laporan -->
            <a href="laporan.php" class="w-full px-4 py-3 rounded-xl flex justify-start items-center gap-3 text-white/85 hover:bg-white/10 transition-colors font-medium text-sm">
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
  <main class="flex-1 h-full flex flex-col min-w-0 overflow-y-auto custom-scrollbar p-8">
    <div class="flex flex-col gap-6 max-w-[1106px] mx-auto w-full">
      
      <!-- HEADER PAGE -->
      <div class="flex justify-between items-center w-full pb-2">
        <div class="flex flex-col gap-1">
          <h1 class="text-[#2B2544] text-2xl lg:text-[30px] font-bold">Detail Folder Pesanan</h1>
          <p class="text-[#6C6287] text-sm font-normal">Kelola hasil foto dan akses unduh untuk pelanggan</p>
        </div>
        <div class="flex items-center gap-3">
          <div class="px-4 py-2 bg-white shadow-[0px_4px_16px_rgba(49,40,82,0.03)] rounded-xl outline outline-1 outline-[#EAE6FA] -outline-offset-1 flex items-center gap-2">
            <div class="w-4 h-4 relative overflow-hidden">
              <div class="w-3 h-[13.33px] absolute left-[2px] top-[1.33px] outline outline-2 outline-[#6366F1] -outline-offset-1"></div>
            </div>
            <span class="text-[#2B2544] text-xs font-semibold">Hari Ini: <?= formatTanggalIndo(date('Y-m-d')); ?></span>
          </div>
        </div>
      </div>

      <!-- BREADCRUMB & BACK LINK -->
      <div class="flex flex-col gap-1">
        <a href="galeri.php" class="flex items-center gap-2 text-[#0891B2] text-xs font-semibold hover:underline w-fit">
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
          <span>Kembali ke Galeri</span>
        </a>
        <div class="text-[#6C6287] text-xs">
          Galeri / <span class="font-medium text-[#2B2544]"><?= htmlspecialchars($nama_pelanggan); ?> — <?= htmlspecialchars($data_booking['kode_booking']); ?></span>
        </div>
      </div>

      <!-- CUSTOMER INFO SUMMARY CARD -->
      <div class="p-6 bg-white shadow-[0px_10px_24px_-8px_rgba(15,23,42,0.08)] rounded-2xl outline outline-1 outline-[#E5E7EB] -outline-offset-1 flex flex-col gap-5">
        <div class="flex justify-between items-center w-full flex-wrap gap-3">
          <div class="flex items-center gap-3">
            <div class="w-11 h-11 bg-[#ECFEFF] rounded-xl flex justify-center items-center shrink-0">
              <svg class="w-6 h-6 text-[#0891B2]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
            </div>
            <div class="flex flex-col gap-0.5">
              <h2 class="text-[#2B2544] text-xl font-bold"><?= htmlspecialchars($nama_pelanggan); ?></h2>
              <span class="text-[#0891B2] text-xs font-semibold"><?= htmlspecialchars($data_booking['kode_booking']); ?></span>
            </div>
          </div>
          
          <div class="px-3 py-1.5 bg-[#ECFEFF] rounded-lg text-[#0891B2] text-xs font-semibold flex items-center gap-1.5">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            <span><?= ($total_foto > 0) ? 'Foto siap' : 'Belum ada foto'; ?></span>
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 text-xs pt-2 border-t border-gray-100">
          <div class="flex flex-col gap-1">
            <span class="text-[#6C6287]">Paket</span>
            <span class="font-semibold text-[#2B2544] text-sm"><?= htmlspecialchars($data_booking['nama_paket'] ?? 'Studio Portrait'); ?></span>
          </div>
          <div class="flex flex-col gap-1">
            <span class="text-[#6C6287]">Tanggal pesanan</span>
            <span class="font-semibold text-[#2B2544] text-sm"><?= formatTanggalIndo($data_booking['tanggal_sesi']); ?></span>
          </div>
          <div class="flex flex-col gap-1">
            <span class="text-[#6C6287]">Status pembayaran</span>
            <div><span class="px-2.5 py-1 bg-[#E6FBF2] text-[#10B981] font-semibold rounded-lg"><?= htmlspecialchars($data_booking['status_pembayaran'] ?? 'Lunas'); ?></span></div>
          </div>
          <div class="text-right md:text-right text-[#6C6287] text-[11px] leading-relaxed self-center">
            Data pelanggan otomatis<br/>dari pesanan
          </div>
        </div>
      </div>

      <!-- MAIN CONTENT GRID -->
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        
        <!-- LEFT COLUMN: PHOTO GALLERY LIST (8 COLS) -->
        <div class="lg:col-span-8 p-6 bg-white shadow-[0px_10px_24px_-8px_rgba(15,23,42,0.08)] rounded-2xl outline outline-1 outline-[#E5E7EB] -outline-offset-1 flex flex-col gap-5">
          
          <div class="flex justify-between items-center w-full">
            <div class="flex flex-col gap-0.5">
              <h3 class="text-[#2B2544] text-lg font-bold">Hasil foto</h3>
              <span class="text-[#6C6287] text-xs"><?= $total_foto; ?> foto dalam folder ini</span>
            </div>

            <a href="UnggahFoto.php?id_booking=<?= $id_booking_db; ?>" class="px-4 py-2 bg-[#0891B2] hover:bg-[#06748f] transition-colors text-white font-semibold text-xs rounded-xl shadow-sm flex items-center gap-2">
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
              <span>Tambah foto</span>
            </a>
          </div>

          <!-- PHOTO CARD CONTAINER DINAMIS -->
          <?php if ($res_foto && $total_foto > 0) : 
              while ($foto = mysqli_fetch_assoc($res_foto)) :
                  $src_gambar = htmlspecialchars($foto['gambar_url']);
                  $nama_album = htmlspecialchars($foto['nama_album'] ?? 'Album Foto');
          ?>
          <div class="rounded-xl border border-[#E5E7EB] overflow-hidden flex flex-col mb-2">
            <img src="<?= $src_gambar; ?>" alt="Preview Foto" class="w-full h-[336px] object-cover" />
            
            <div class="p-4 flex flex-col gap-4 bg-white">
              <div class="flex justify-between items-center">
                <div class="flex flex-col gap-0.5 min-w-0">
                  <span class="font-semibold text-[#2B2544] text-sm truncate"><?= $nama_album; ?></span>
                  <span class="text-[#6C6287] text-xs">Diunggah: <?= htmlspecialchars($foto['tanggal_foto']); ?> • <?= htmlspecialchars($nama_pelanggan); ?></span>
                </div>
                <div class="flex items-center gap-1.5 text-[#0891B2] text-xs font-semibold shrink-0">
                  <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                  <span><?= htmlspecialchars($foto['status_album'] ?? 'Siap diunduh'); ?></span>
                </div>
              </div>

              <div class="flex items-center gap-2 flex-wrap">
                <a href="<?= $src_gambar; ?>" target="_blank" class="h-10 px-4 bg-white border border-[#EAE6FA] text-[#0891B2] hover:bg-[#0891B2]/5 transition-colors font-semibold text-xs rounded-xl flex items-center gap-2 shadow-sm">
                  <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                  <span>Lihat foto</span>
                </a>

                <a href="<?= $src_gambar; ?>" download class="h-10 px-4 bg-white border border-[#EAE6FA] text-[#0891B2] hover:bg-[#0891B2]/5 transition-colors font-semibold text-xs rounded-xl flex items-center gap-2 shadow-sm">
                  <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                  <span>Unduh foto</span>
                </a>

                <a href="DetailFolder.php?id_booking=<?= $id_booking_db; ?>&action=hapus&id_foto=<?= $foto['id']; ?>" onclick="return confirm('Apakah Anda yakin ingin menghapus foto ini?')" class="ml-auto flex items-center gap-1.5 text-[#6C6287] hover:text-red-500 transition-colors font-semibold text-xs">
                  <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                  <span>Hapus</span>
                </a>
              </div>
            </div>
          </div>
          <?php endwhile; 
          else : ?>
          <div class="p-8 text-center bg-gray-50 rounded-xl border border-dashed border-gray-200 text-xs text-[#6C6287]">
            Belum ada hasil foto yang diunggah untuk pesanan ini.
          </div>
          <?php endif; ?>

          <p class="text-[#6C6287] text-xs leading-relaxed">
            Hasil foto ini terhubung ke pesanan <?= htmlspecialchars($data_booking['kode_booking']); ?>.<br/>Kelola dan bagikan akses unduh dari folder yang sama.
          </p>

        </div>

        <!-- RIGHT COLUMN: READY LINK & QR UNDUH CARD (4 COLS) -->
        <div class="lg:col-span-4 p-6 bg-white shadow-[0px_10px_24px_-8px_rgba(15,23,42,0.08)] rounded-2xl outline outline-1 outline-[#E5E7EB] -outline-offset-1 flex flex-col gap-4.5">
          
          <div class="flex flex-col gap-1">
            <h3 class="text-[#2B2544] text-lg font-bold">Link & QR unduh</h3>
            <p class="text-[#6C6287] text-xs leading-relaxed">Bagikan akses hasil foto <?= htmlspecialchars($nama_pelanggan); ?> kepada pelanggan.</p>
          </div>

          <!-- QR CODE BOX DINAMIS -->
          <div class="flex flex-col items-center gap-3">
            <div class="p-5 bg-[#ECFEFF] rounded-2xl flex justify-center items-center">
              <img src="https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=<?= urlencode("SIMAVIS-" . $data_booking['kode_booking']); ?>" alt="QR Code Unduh" class="w-[180px] h-[180px] rounded-xl" />
            </div>
            <span class="text-[#2B2544] text-xs font-semibold text-center">Scan untuk mengunduh foto</span>
            <span class="text-[#6C6287] text-[11px] text-center">Tersedia di akun pengguna pelanggan</span>
          </div>

          <!-- QR NOTICE BANNER -->
          <div class="p-2.5 bg-[#ECFEFF] rounded-lg flex items-center gap-2 text-[#0891B2] text-xs font-medium">
            <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>QR unduh foto — bukan QRIS pembayaran</span>
          </div>

          <hr class="border-[#EAE6FA]" />

          <!-- LINK & ACTION BUTTONS -->
          <div class="flex flex-col gap-2">
            <label class="text-[#6C6287] text-xs font-semibold">Link unduh foto</label>
            <div id="linkText" class="p-3 bg-[#F3F0FA] rounded-xl text-[#0891B2] text-xs font-mono truncate">
              <?= $link_unduh_user; ?>
            </div>

            <div class="grid grid-cols-2 gap-2 mt-1">
              <button type="button" onclick="salinLink()" class="h-10 bg-white border border-[#EAE6FA] text-[#0891B2] hover:bg-[#0891B2]/5 transition-colors font-semibold text-xs rounded-xl flex justify-center items-center gap-2 shadow-sm">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                <span>Salin link</span>
              </button>

              <a href="https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=<?= urlencode("SIMAVIS-" . $data_booking['kode_booking']); ?>" download="QR_<?= $data_booking['kode_booking']; ?>.png" target="_blank" class="h-10 bg-white border border-[#EAE6FA] text-[#0891B2] hover:bg-[#0891B2]/5 transition-colors font-semibold text-xs rounded-xl flex justify-center items-center gap-2 shadow-sm">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                <span>Unduh QR</span>
              </a>
            </div>

            <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $no_whatsapp); ?>?text=Halo%20<?= urlencode($nama_pelanggan); ?>,%20foto%20sesi%20studio%20Anda%20sudah%20selesai%20diunggah.%20Silakan%20cek%20di%20akun%20Anda." target="_blank" class="w-full h-10 bg-[#0891B2] hover:bg-[#06748f] transition-colors text-white font-semibold text-xs rounded-xl flex justify-center items-center gap-2 shadow-sm mt-1">
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/></svg>
              <span>Bagikan ke pelanggan</span>
            </a>
          </div>

        </div>

      </div>

    </div>
  </main>

  <script>
    function salinLink() {
      const link = document.getElementById('linkText').innerText.trim();
      navigator.clipboard.writeText(link);
      alert('Link unduh berhasil disalin!');
    }
  </script>

</body>
</html>
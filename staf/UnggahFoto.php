<?php
session_start();
include '../koneksi.php';

// 1. Ambil ID atau Kode Booking dari Parameter URL
$id_booking = isset($_GET['id_booking']) ? $_GET['id_booking'] : 0;

// Query Ambil Detail Booking (Menggunakan kolom 'id' sesuai struktur tabel database Anda)
$query_info = "SELECT * FROM booking WHERE id = '$id_booking' OR kode_booking = '$id_booking' LIMIT 1";
$res_info = mysqli_query($conn, $query_info);

if ($res_info && mysqli_num_rows($res_info) > 0) {
    $data_booking = mysqli_fetch_assoc($res_info);
    $id_booking_db = $data_booking['id']; // ID utama dari tabel booking
    $id_pelanggan_db = $data_booking['id_pelanggan'];

    // Ambil data pelanggan dari tabel pelanggan berdasarkan id_pelanggan
    $res_pel = mysqli_query($conn, "SELECT * FROM pelanggan WHERE id = '$id_pelanggan_db' LIMIT 1");
    $data_pel = mysqli_fetch_assoc($res_pel);
    
    // Ambil nama lengkap dari tabel booking atau tabel pelanggan
    $data_booking['nama_pelanggan'] = $data_booking['nama_lengkap'] ?? $data_pel['nama'] ?? $data_pel['nama_lengkap'] ?? 'Pelanggan';
    $data_booking['no_whatsapp'] = $data_booking['no_whatsapp'] ?? $data_pel['no_whatsapp'] ?? '-';
} else {
    // Fallback Dummy jika dibuka langsung tanpa parameter ID
    $id_booking_db = 1;
    $id_pelanggan_db = 1;
    $data_booking = [
        'id'                => 1,
        'kode_booking'      => 'SMV-261013-239',
        'nama_pelanggan'    => 'Nadia Putri',
        'nama_paket'        => 'Couple & Friends',
        'tanggal_sesi'      => '2026-10-17',
        'status_pembayaran' => 'Lunas',
        'status_sesi'       => 'Mendatang',
        'no_whatsapp'       => '081234567890'
    ];
}

// 2. PROSES UPLOAD FOTO DARI FORM
$pesan_error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['foto_hasil'])) {
    $id_target_booking = intval($_POST['id_booking']);
    
    $upload_dir = "../uploads/";
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    $file_name = $_FILES['foto_hasil']['name'];
    $file_tmp  = $_FILES['foto_hasil']['tmp_name'];
    $file_ext  = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    
    $new_file_name = "SIMAVIS_" . time() . "_" . rand(100, 999) . "." . $file_ext;
    $target_file   = $upload_dir . $new_file_name;

    $allowed_extensions = array("jpg", "jpeg", "png", "webp");

    if (in_array($file_ext, $allowed_extensions)) {
        if (move_uploaded_file($file_tmp, $target_file)) {
            $gambar_url   = "../uploads/" . $new_file_name;
            $nama_album   = "Album " . ($data_booking['nama_paket'] ?? 'Sesi Foto');
            $tanggal_foto = date('d M Y');
            $jumlah_foto  = "1 foto edit";
            $status_album = "Siap diunduh";

            // Simpan ke tabel album_foto sesuai struktur database Anda
            $query_album = "INSERT INTO album_foto (id_pelanggan, nama_album, tanggal_foto, jumlah_foto, gambar_url, status_album) 
                            VALUES ('$id_pelanggan_db', '$nama_album', '$tanggal_foto', '$jumlah_foto', '$gambar_url', '$status_album')";
            mysqli_query($conn, $query_album);

            // Update status sesi booking menjadi Selesai menggunakan kolom 'id'
            $query_update = "UPDATE booking SET status_sesi = 'Selesai' WHERE id = '$id_target_booking'";
            mysqli_query($conn, $query_update);

            header("Location: UnggahSelesai.php?id_booking=" . $id_target_booking);
            exit;
        } else {
            $pesan_error = "Gagal mengunggah berkas foto ke direktori server.";
        }
    } else {
        $pesan_error = "Format file tidak didukung! Harap unggah foto dengan ekstensi JPG, JPEG, PNG, atau WEBP.";
    }
}

// Helper Format Tanggal Indonesia
function formatTanggalIndo($tanggal) {
    if (!$tanggal) return '-';
    $bulan = [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    $split = explode('-', date('Y-m-d', strtotime($tanggal)));
    return $split[2] . ' ' . $bulan[(int)$split[1]] . ' ' . $split[0];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SIMAVIS - Unggah Foto Pesanan</title>
  <script src="https://cdn.tailwindcss.com"></script>
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

  <!-- SIDEBAR -->
  <aside class="w-[260px] h-full flex-shrink-0 p-5 pt-8 pb-8 flex flex-col justify-between items-start border-r border-[#164E63]"
         style="background: linear-gradient(180deg, #0F597A 0%, #0A4061 60%, #082E4D 100%);">
    <div class="w-full flex flex-col justify-start items-start gap-8">
      <div class="flex justify-start items-center gap-3">
        <img class="w-10 h-10 rounded-lg" src="https://placehold.co/40x40" alt="Logo" />
        <div class="flex flex-col justify-start items-start gap-0.5">
          <div class="text-white text-base font-bold">SIMAVIS</div>
          <div class="text-[#22D3EE]/80 text-[11px] font-medium uppercase tracking-wider">Vibes Studio Foto</div>
        </div>
      </div>
      <nav class="w-full flex flex-col gap-2">
        <a href="dashboard.php" class="w-full px-4 py-3 rounded-xl flex justify-start items-center gap-3 text-white/85 hover:bg-white/10 transition-colors font-medium text-sm">
          <span class="flex-1">Dashboard SIMAVIS</span>
        </a>
        <a href="pesanan.php" class="w-full px-4 py-3 rounded-xl flex justify-start items-center gap-3 text-white/85 hover:bg-white/10 transition-colors font-medium text-sm">
          <span class="flex-1">Pesanan</span>
          <span class="px-1.5 py-0.5 bg-[#F0EDFF] rounded-md text-[#8A72F1] text-[11px] font-bold">12</span>
        </a>
        <a href="galeri.php" class="w-full px-4 py-3 bg-[#0891B2] rounded-xl flex justify-start items-center gap-3 text-white font-semibold text-sm shadow-md">
          <span class="flex-1">Galeri & QR</span>
        </a>
        <a href="Laporan.php" class="w-full px-4 py-3 rounded-xl flex justify-start items-center gap-3 text-white/85 hover:bg-white/10 transition-colors font-medium text-sm">
          <span class="flex-1">Laporan</span>
        </a>
      </nav>
    </div>
    <div class="w-full p-3 bg-white rounded-2xl outline outline-1 outline-[#E5E7EB] -outline-offset-1 flex justify-start items-center gap-3 shadow-sm">
      <img class="w-9 h-9 rounded-xl flex-shrink-0" src="https://placehold.co/36x36" alt="User Profile" />
      <div class="flex-1 flex flex-col justify-start items-start gap-0.5 min-w-0">
        <div class="text-[#2B2544] text-xs font-semibold truncate w-full">Dimas Prayoga</div>
        <div class="text-[#6C6287] text-[11px] font-normal truncate w-full">Admin Utama</div>
      </div>
    </div>
  </aside>

  <!-- MAIN CONTENT -->
  <main class="flex-1 h-full flex flex-col min-w-0 overflow-y-auto custom-scrollbar p-8">
    <div class="flex flex-col gap-6 max-w-[1106px] mx-auto w-full">
      
      <!-- HEADER PAGE -->
      <div class="flex justify-between items-center w-full pb-2">
        <div class="flex flex-col gap-1">
          <h1 class="text-[#2B2544] text-2xl lg:text-[30px] font-bold">Unggah Foto Pesanan</h1>
          <p class="text-[#6C6287] text-sm font-normal">Masukkan hasil foto ke folder pesanan pelanggan</p>
        </div>
        <div class="px-4 py-2 bg-white shadow-sm rounded-xl outline outline-1 outline-[#EAE6FA] text-[#2B2544] text-xs font-semibold">
          Hari Ini: <?= formatTanggalIndo(date('Y-m-d')); ?>
        </div>
      </div>

      <!-- BREADCRUMB -->
      <div class="flex flex-col gap-1">
        <a href="galeri.php" class="flex items-center gap-2 text-[#0891B2] text-xs font-semibold hover:underline w-fit">
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
          <span>Kembali ke Galeri</span>
        </a>
        <div class="text-[#6C6287] text-xs">
          Galeri / <span class="font-medium text-[#2B2544]"><?= htmlspecialchars($data_booking['nama_pelanggan']); ?> — <?= htmlspecialchars($data_booking['kode_booking']); ?></span>
        </div>
      </div>

      <!-- NOTIFIKASI ERROR JIKA ADA -->
      <?php if (!empty($pesan_error)): ?>
        <div class="p-4 bg-red-50 border border-red-200 text-red-700 text-xs rounded-xl font-medium">
          ⚠️ <?= $pesan_error; ?>
        </div>
      <?php endif; ?>

      <!-- SUMMARY CARD -->
      <div class="p-6 bg-white shadow-sm rounded-2xl border border-[#E5E7EB] flex flex-col gap-5">
        <div class="flex justify-between items-center w-full flex-wrap gap-3">
          <div class="flex items-center gap-3">
            <div class="w-11 h-11 bg-[#ECFEFF] rounded-xl flex justify-center items-center shrink-0">
              <svg class="w-6 h-6 text-[#0891B2]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
            </div>
            <div class="flex flex-col gap-0.5">
              <h2 class="text-[#2B2544] text-xl font-bold"><?= htmlspecialchars($data_booking['nama_pelanggan']); ?></h2>
              <span class="text-[#0891B2] text-xs font-semibold"><?= htmlspecialchars($data_booking['kode_booking']); ?></span>
            </div>
          </div>
          
          <div class="px-3 py-1.5 bg-[#ECFEFF] rounded-lg text-[#0891B2] text-xs font-semibold flex items-center gap-1.5">
            <span>Siap Unggah</span>
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 text-xs pt-2 border-t border-gray-100">
          <div class="flex flex-col gap-1"><span class="text-[#6C6287]">Paket</span><span class="font-semibold text-[#2B2544] text-sm"><?= htmlspecialchars($data_booking['nama_paket'] ?? 'Studio Portrait'); ?></span></div>
          <div class="flex flex-col gap-1"><span class="text-[#6C6287]">Tanggal pesanan</span><span class="font-semibold text-[#2B2544] text-sm"><?= formatTanggalIndo($data_booking['tanggal_sesi']); ?></span></div>
          <div class="flex flex-col gap-1"><span class="text-[#6C6287]">Status pembayaran</span><div><span class="px-2.5 py-1 bg-[#E6FBF2] text-[#10B981] font-semibold rounded-lg">Lunas</span></div></div>
          <div class="text-right text-[#6C6287] text-[11px] leading-relaxed self-center">Data pelanggan otomatis dari pesanan</div>
        </div>
      </div>

      <!-- MAIN GRID -->
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        
        <!-- LEFT: UPLOAD BOX & FORM -->
        <div class="lg:col-span-8 p-6 bg-white shadow-sm rounded-2xl border border-[#E5E7EB] flex flex-col gap-6">
          <div class="flex flex-col gap-1">
            <h3 class="text-[#2B2544] text-lg font-bold">Unggah hasil foto</h3>
            <p class="text-[#6C6287] text-xs">Foto akan tersimpan di folder <?= htmlspecialchars($data_booking['nama_pelanggan']); ?> — <?= htmlspecialchars($data_booking['kode_booking']); ?>.</p>
          </div>

          <!-- FORM PENGIRIMAN FOTO REAL DB -->
          <form id="uploadForm" action="" method="POST" enctype="multipart/form-data" class="flex flex-col gap-4">
            <input type="hidden" name="id_booking" value="<?= $id_booking_db; ?>">

            <!-- DRAG BOX INPUT -->
            <div class="p-6 bg-[#F7FCFD] rounded-xl border border-[#0891B2]/40 flex flex-col items-center gap-3 text-center cursor-pointer relative" onclick="document.getElementById('foto_hasil').click()">
              <input type="file" name="foto_hasil" id="foto_hasil" required class="hidden" onchange="previewFile(this)">
              
              <div class="w-12 h-12 bg-[#ECFEFF] rounded-xl flex justify-center items-center">
                <svg class="w-6 h-6 text-[#0891B2]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
              </div>
              <span id="labelPilihFile" class="text-[#2B2544] font-semibold text-sm">Tarik dan lepas foto di sini</span>
              <span id="labelNamaFile" class="text-[#6C6287] text-xs">atau klik di sini untuk memilih dari perangkat Anda</span>

              <button type="button" class="px-4 py-2 bg-[#0891B2] hover:bg-[#06748f] transition-colors text-white text-xs font-semibold rounded-xl flex items-center gap-2 shadow-sm pointer-events-none mt-1">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Pilih Foto Hasil</span>
              </button>
            </div>

            <!-- PROGRESS AREA -->
            <div class="flex flex-col gap-3">
              <div class="flex justify-between items-center text-xs">
                <span class="font-semibold text-[#2B2544]">Progres unggahan</span>
                <span id="fileCountText" class="text-[#6C6287]">Siap diunggah</span>
              </div>

              <div class="p-4 bg-[#F8FBFF] rounded-xl border border-[#E5E7EB] flex flex-col gap-3.5">
                <div class="flex justify-between items-center gap-3">
                  <div class="w-10 h-10 bg-white rounded-lg border border-gray-200 flex justify-center items-center shrink-0">
                    <svg class="w-5 h-5 text-[#0891B2]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                  </div>
                  <div class="flex-1 flex flex-col gap-0.5 min-w-0">
                    <span id="fileNameText" class="font-semibold text-[#2B2544] text-xs truncate">Pilih berkas foto...</span>
                    <span id="uploadDetailText" class="text-[#6C6287] text-[11px]">Siap menyimpan ke database pelanggan</span>
                  </div>
                  <span id="progressPercent" class="text-[#0891B2] font-bold text-base">0%</span>
                </div>

                <div class="w-full h-2 bg-[#DFEDF1] rounded-full overflow-hidden">
                  <div id="progressBar" class="h-full bg-[#0891B2] rounded-full w-[0%] transition-all duration-300"></div>
                </div>
              </div>
            </div>

            <!-- SUBMIT BUTTON REAL -->
            <button type="submit" id="submitBtn" class="w-full py-3 bg-[#10B981] hover:bg-[#059669] transition-colors text-white font-bold text-xs rounded-xl flex justify-center items-center gap-2 shadow-md">
              <span>Simpan Foto & Selesaikan →</span>
            </button>
          </form>

        </div>

        <!-- RIGHT: PANEL LINK & QR UNDUH -->
        <div class="lg:col-span-4 p-6 bg-white shadow-sm rounded-2xl border border-[#E5E7EB] flex flex-col gap-4.5">
          <div class="flex flex-col gap-1">
            <h3 class="text-[#2B2544] text-lg font-bold">Link & QR unduh</h3>
            <p class="text-[#6C6287] text-xs">Akses foto pelanggan berada di dalam folder pesanan ini.</p>
          </div>

          <!-- QR BOX -->
          <div class="p-5 bg-[#ECFEFF] rounded-xl flex flex-col items-center gap-3 text-center">
            <img src="https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=<?= urlencode("SIMAVIS-" . $data_booking['kode_booking']); ?>" class="w-[180px] h-[180px] rounded-xl" alt="QR Code Link Foto" />
            <span class="text-[#2B2544] font-semibold text-xs">Scan untuk mengunduh foto</span>
            <span class="text-[#6C6287] text-[11px]">Foto otomatis tampil di akun pengguna pelanggan</span>
          </div>

          <div class="flex flex-col gap-2">
            <label class="text-[#6C6287] text-xs font-semibold">Link unduh foto</label>
            <div id="linkText" class="p-3 bg-[#F3F0FA] rounded-xl text-[#0891B2] text-xs truncate font-mono">
              user/akun.php?kode=<?= htmlspecialchars($data_booking['kode_booking']); ?>
            </div>

            <div class="grid grid-cols-2 gap-2 mt-1">
              <button type="button" onclick="salinLink()" class="h-10 bg-white border border-[#EAE6FA] text-[#0891B2] font-semibold text-xs rounded-xl hover:bg-[#0891B2]/5 transition-colors">
                Salin link
              </button>
              <a href="https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=<?= urlencode("SIMAVIS-" . $data_booking['kode_booking']); ?>" download="QR_<?= $data_booking['kode_booking']; ?>.png" target="_blank" class="h-10 bg-white border border-[#EAE6FA] text-[#0891B2] font-semibold text-xs rounded-xl hover:bg-[#0891B2]/5 transition-colors flex justify-center items-center">
                Unduh QR
              </a>
            </div>

            <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $data_booking['no_whatsapp'] ?? ''); ?>?text=Halo%20<?= urlencode($data_booking['nama_pelanggan']); ?>,%20foto%20sesi%20studio%20Anda%20sudah%20selesai%20diunggah.%20Silakan%20cek%20di%20akun%20Anda." target="_blank" class="w-full h-10 bg-[#0891B2] text-white font-semibold text-xs rounded-xl hover:bg-[#06748f] transition-colors mt-1 flex justify-center items-center gap-2">
              <span>Bagikan ke WhatsApp Pelanggan</span>
            </a>
          </div>
        </div>

      </div>

    </div>
  </main>

  <script>
    function previewFile(input) {
      if (input.files && input.files[0]) {
        const file = input.files[0];
        document.getElementById('fileNameText').innerText = file.name;
        document.getElementById('labelPilihFile').innerText = file.name;
        document.getElementById('labelNamaFile').innerText = (file.size / 1024 / 1024).toFixed(2) + ' MB';
        
        document.getElementById('progressBar').style.width = '100%';
        document.getElementById('progressPercent').innerText = '100%';
        document.getElementById('uploadDetailText').innerText = 'Berkas terverifikasi, siap disimpan.';
        document.getElementById('fileCountText').innerText = '1 berkas terpilih';
      }
    }

    function salinLink() {
      const link = document.getElementById('linkText').innerText.trim();
      navigator.clipboard.writeText(link);
      alert('Link unduh berhasil disalin!');
    }
  </script>

</body>
</html>
<?php
session_start();
// Pelindung Sesi Login Staf
if (!isset($_SESSION['staf_logged_in']) || $_SESSION['staf_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

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

$pesan_sukses = '';
$pesan_error = '';

// --- PROSES SIMPAN / TAMBAH PAKET BARU ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aksi']) && $_POST['aksi'] === 'tambah') {
    $nama_paket = trim($_POST['nama_paket'] ?? '');
    $harga      = intval($_POST['harga'] ?? 0);
    $durasi     = trim($_POST['durasi'] ?? '');
    $kapasitas  = trim($_POST['kapasitas'] ?? '');
    $backdrop   = trim($_POST['backdrop'] ?? '');
    $foto_edit  = trim($_POST['foto_edit'] ?? '');
    $deskripsi  = trim($_POST['deskripsi'] ?? '');

    if (!empty($nama_paket) && $harga > 0) {
        $stmt = mysqli_prepare($conn, "INSERT INTO paket_foto (nama_paket, deskripsi, harga, durasi, kapasitas, backdrop, foto_edit) VALUES (?, ?, ?, ?, ?, ?, ?)");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "ssissss", $nama_paket, $deskripsi, $harga, $durasi, $kapasitas, $backdrop, $foto_edit);
            if (mysqli_stmt_execute($stmt)) {
                $pesan_sukses = "Paket/Tema foto baru berhasil ditambahkan!";
            } else {
                $pesan_error = "Gagal menyimpan data paket!";
            }
        }
    } else {
        $pesan_error = "Nama paket dan harga wajib diisi!";
    }
}

// --- PROSES EDIT PAKET ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aksi']) && $_POST['aksi'] === 'edit') {
    $id_paket   = intval($_POST['id_paket'] ?? 0);
    $nama_paket = trim($_POST['nama_paket'] ?? '');
    $harga      = intval($_POST['harga'] ?? 0);
    $durasi     = trim($_POST['durasi'] ?? '');
    $kapasitas  = trim($_POST['kapasitas'] ?? '');
    $backdrop   = trim($_POST['backdrop'] ?? '');
    $foto_edit  = trim($_POST['foto_edit'] ?? '');
    $deskripsi  = trim($_POST['deskripsi'] ?? '');

    if ($id_paket > 0 && !empty($nama_paket)) {
        $stmt = mysqli_prepare($conn, "UPDATE paket_foto SET nama_paket = ?, deskripsi = ?, harga = ?, durasi = ?, kapasitas = ?, backdrop = ?, foto_edit = ? WHERE id = ?");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "ssissssi", $nama_paket, $deskripsi, $harga, $durasi, $kapasitas, $backdrop, $foto_edit, $id_paket);
            if (mysqli_stmt_execute($stmt)) {
                $pesan_sukses = "Data paket berhasil diperbarui!";
            } else {
                $pesan_error = "Gagal memperbarui data paket!";
            }
        }
    }
}

// --- PROSES HAPUS PAKET ---
if (isset($_GET['action']) && $_GET['action'] === 'hapus' && isset($_GET['id'])) {
    $id_hapus = intval($_GET['id']);
    mysqli_query($conn, "DELETE FROM paket_foto WHERE id = '$id_hapus'");
    header("Location: paket.php");
    exit;
}

// AMBIL SEMUA DATA PAKET FOTO DARI TABEL 'paket_foto'
$res_paket = mysqli_query($conn, "SELECT * FROM paket_foto ORDER BY id ASC");

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
  <title>SIMAVIS - Kelola Paket & Tema Foto</title>
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
        <img src="../user/asset/logo.jpeg" alt="Logo SIMAVIS" class="w10 h-10 rounded-lg object-cover shadow-xs" />
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
            <a href="galeri.php" class="w-full px-4 py-3 rounded-xl flex justify-start items-center gap-3 text-white/85 hover:bg-white/10 transition-colors font-medium text-sm">
                <i class="fa-solid fa-images w-5 text-center"></i>
                <span class="flex-1">Galeri & QR</span>
            </a>

            <!-- Paket & Tema Foto -->
            <a href="paket.php" class="w-full px-4 py-3 bg-[#0891B2] rounded-xl flex justify-start items-center gap-3 text-white font-semibold text-sm shadow-md">
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
  <main class="flex-1 h-full flex flex-col min-w-0 overflow-hidden bg-white">
    
    <!-- Area Data dengan Scroll Bar -->
    <div class="flex-1 overflow-y-auto p-8 lg:p-10 flex flex-col gap-6 custom-scrollbar">
      
      <!-- HEADER PAGE -->
      <div class="flex justify-between items-center w-full pb-2">
        <div class="flex flex-col gap-1">
          <h1 class="text-[#2B2544] text-2xl lg:text-[30px] font-bold">Kelola Paket & Tema Foto</h1>
          <p class="text-[#6C6287] text-sm font-normal">Atur variasi paket studio, durasi, kapasitas, dan fasilitas foto</p>
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

      <!-- NOTIFIKASI SUKSES / ERROR -->
      <?php if (!empty($pesan_sukses)) : ?>
        <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs font-semibold rounded-2xl flex items-center gap-2">
          <i class="fa-solid fa-circle-check text-emerald-600 text-base"></i>
          <span><?= htmlspecialchars($pesan_sukses); ?></span>
        </div>
      <?php endif; ?>

      <?php if (!empty($pesan_error)) : ?>
        <div class="p-4 bg-rose-50 border border-rose-200 text-rose-700 text-xs font-semibold rounded-2xl flex items-center gap-2">
          <i class="fa-solid fa-circle-exclamation text-rose-600 text-base"></i>
          <span><?= htmlspecialchars($pesan_error); ?></span>
        </div>
      <?php endif; ?>

      <!-- GRID FORM & DAFTAR PAKET -->
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        
        <!-- FORM INPUT PAKET (4 COLS) -->
        <div class="lg:col-span-4 p-6 bg-white shadow-[0px_10px_24px_-8px_rgba(15,23,42,0.08)] rounded-2xl border border-gray-200 flex flex-col gap-4">
          <h3 id="formTitle" class="text-[#2B2544] text-base font-bold flex items-center gap-2">
            <i class="fa-solid fa-plus text-[#0891B2]"></i>
            <span>Tambah Paket Foto</span>
          </h3>

          <form action="" method="POST" id="paketForm" class="flex flex-col gap-3 text-xs">
            <input type="hidden" name="aksi" id="formAksi" value="tambah">
            <input type="hidden" name="id_paket" id="formIdPaket" value="">

            <div class="flex flex-col gap-1">
              <label class="font-semibold text-gray-700">Nama Paket / Tema *</label>
              <input type="text" name="nama_paket" id="inputNama" required placeholder="Contoh: Graduation Package" class="p-3 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:border-[#0891B2]" />
            </div>

            <div class="flex flex-col gap-1">
              <label class="font-semibold text-gray-700">Harga (Rp) *</label>
              <input type="number" name="harga" id="inputHarga" required placeholder="350000" class="p-3 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:border-[#0891B2]" />
            </div>

            <div class="grid grid-cols-2 gap-2">
              <div class="flex flex-col gap-1">
                <label class="font-semibold text-gray-700">Durasi Sesi</label>
                <input type="text" name="durasi" id="inputDurasi" placeholder="30 menit" class="p-3 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:border-[#0891B2]" />
              </div>

              <div class="flex flex-col gap-1">
                <label class="font-semibold text-gray-700">Kapasitas Orang</label>
                <input type="text" name="kapasitas" id="inputKapasitas" placeholder="1 orang" class="p-3 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:border-[#0891B2]" />
              </div>
            </div>

            <div class="grid grid-cols-2 gap-2">
              <div class="flex flex-col gap-1">
                <label class="font-semibold text-gray-700">Pilihan Backdrop</label>
                <input type="text" name="backdrop" id="inputBackdrop" placeholder="1 pilihan backdrop" class="p-3 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:border-[#0891B2]" />
              </div>

              <div class="flex flex-col gap-1">
                <label class="font-semibold text-gray-700">Ket. Foto Edit</label>
                <input type="text" name="foto_edit" id="inputFotoEdit" placeholder="5 foto edit..." class="p-3 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:border-[#0891B2]" />
              </div>
            </div>

            <div class="flex flex-col gap-1">
              <label class="font-semibold text-gray-700">Deskripsi Singkat</label>
              <textarea name="deskripsi" id="inputDeskripsi" rows="3" placeholder="Deskripsikan keunggulan paket foto ini..." class="p-3 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:border-[#0891B2] resize-none"></textarea>
            </div>

            <div class="flex items-center gap-2 mt-2">
              <button type="submit" id="btnSubmit" class="flex-1 py-3 bg-[#0891B2] hover:bg-[#06748f] text-white font-bold rounded-xl transition-colors shadow-md">
                Simpan Paket
              </button>
              <button type="button" onclick="resetForm()" id="btnReset" class="hidden px-3 py-3 bg-gray-200 text-gray-700 font-semibold rounded-xl hover:bg-gray-300">
                Batal
              </button>
            </div>
          </form>
        </div>

        <!-- DAFTAR PAKET AKTIF (8 COLS) -->
        <div class="lg:col-span-8 p-6 bg-white shadow-[0px_10px_24px_-8px_rgba(15,23,42,0.08)] rounded-2xl border border-gray-200 flex flex-col gap-4">
          <div class="flex justify-between items-center">
            <h3 class="text-[#2B2544] text-base font-bold">Daftar Paket Aktif</h3>
            <span class="text-xs text-[#6C6287]">Klik 'Edit' untuk mengubah data</span>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <?php 
            if ($res_paket && mysqli_num_rows($res_paket) > 0) :
              while ($p = mysqli_fetch_assoc($res_paket)) :
                $id_p        = $p['id'];
                $nama_p      = $p['nama_paket'];
                $harga_p     = $p['harga'];
                $durasi_p    = $p['durasi'];
                $kapasitas_p = $p['kapasitas'];
                $backdrop_p  = $p['backdrop'];
                $foto_edit_p = $p['foto_edit'];
                $desk_p      = $p['deskripsi'];
            ?>
            <div class="p-5 border border-gray-200 rounded-2xl bg-gray-50/70 flex flex-col justify-between gap-3 hover:border-[#0891B2] transition-colors shadow-sm">
              <div class="flex flex-col gap-2">
                <div class="flex justify-between items-start gap-2">
                  <span class="font-bold text-sm text-[#2B2544]"><?= htmlspecialchars($nama_p); ?></span>
                  <span class="font-bold text-xs text-[#0891B2] bg-[#ECFEFF] px-2.5 py-1 rounded-lg shrink-0">
                    Rp <?= number_format($harga_p, 0, ',', '.'); ?>
                  </span>
                </div>

                <p class="text-xs text-[#6C6287] leading-relaxed line-clamp-2"><?= htmlspecialchars($desk_p); ?></p>

                <!-- Informasi Spesifikasi Paket -->
                <div class="flex flex-wrap items-center gap-x-3 gap-y-1.5 text-[11px] text-gray-600 font-medium pt-1">
                  <?php if (!empty($durasi_p)) : ?>
                    <span><i class="fa-regular fa-clock text-[#0891B2] mr-1"></i><?= htmlspecialchars($durasi_p); ?></span>
                  <?php endif; ?>
                  <?php if (!empty($kapasitas_p)) : ?>
                    <span><i class="fa-solid fa-users text-[#0891B2] mr-1"></i><?= htmlspecialchars($kapasitas_p); ?></span>
                  <?php endif; ?>
                  <?php if (!empty($backdrop_p)) : ?>
                    <span><i class="fa-solid fa-image text-[#0891B2] mr-1"></i><?= htmlspecialchars($backdrop_p); ?></span>
                  <?php endif; ?>
                  <?php if (!empty($foto_edit_p)) : ?>
                    <span><i class="fa-solid fa-wand-magic-sparkles text-[#0891B2] mr-1"></i><?= htmlspecialchars($foto_edit_p); ?></span>
                  <?php endif; ?>
                </div>
              </div>

              <!-- Tombol Aksi Edit & Hapus -->
              <div class="flex items-center gap-2 pt-3 border-t border-gray-200 text-xs">
                <button type="button" 
                        onclick="editPaket(<?= $id_p; ?>, '<?= addslashes(htmlspecialchars($nama_p)); ?>', <?= $harga_p; ?>, '<?= addslashes(htmlspecialchars($durasi_p)); ?>', '<?= addslashes(htmlspecialchars($kapasitas_p)); ?>', '<?= addslashes(htmlspecialchars($backdrop_p)); ?>', '<?= addslashes(htmlspecialchars($foto_edit_p)); ?>', '<?= addslashes(htmlspecialchars($desk_p)); ?>')" 
                        class="flex-1 py-2 bg-white border border-gray-200 text-[#0891B2] font-semibold rounded-xl hover:bg-[#0891B2]/5 transition-colors text-center">
                  <i class="fa-solid fa-pen-to-square mr-1"></i> Edit
                </button>
                <a href="paket.php?action=hapus&id=<?= $id_p; ?>" 
                   onclick="return confirm('Apakah Anda yakin ingin menghapus paket foto ini?')" 
                   class="px-3.5 py-2 bg-white border border-gray-200 text-rose-600 font-semibold rounded-xl hover:bg-rose-50 transition-colors">
                  <i class="fa-solid fa-trash"></i>
                </a>
              </div>
            </div>
            <?php 
              endwhile;
            else :
            ?>
            <div class="col-span-2 p-8 text-center text-xs text-gray-400 bg-gray-50 rounded-xl border border-dashed">
              Belum ada data paket foto.
            </div>
            <?php endif; ?>
          </div>
        </div>

      </div>

    </div>
  </main>

  <script>
    function editPaket(id, nama, harga, durasi, kapasitas, backdrop, foto_edit, deskripsi) {
      document.getElementById('formTitle').innerHTML = '<i class="fa-solid fa-pen-to-square text-[#0891B2]"></i><span>Edit Paket Foto</span>';
      document.getElementById('formAksi').value = 'edit';
      document.getElementById('formIdPaket').value = id;
      document.getElementById('inputNama').value = nama;
      document.getElementById('inputHarga').value = harga;
      document.getElementById('inputDurasi').value = durasi;
      document.getElementById('inputKapasitas').value = kapasitas;
      document.getElementById('inputBackdrop').value = backdrop;
      document.getElementById('inputFotoEdit').value = foto_edit;
      document.getElementById('inputDeskripsi').value = deskripsi;
      
      document.getElementById('btnSubmit').innerText = 'Perbarui Paket';
      document.getElementById('btnReset').classList.remove('hidden');
    }

    function resetForm() {
      document.getElementById('formTitle').innerHTML = '<i class="fa-solid fa-plus text-[#0891B2]"></i><span>Tambah Paket Foto</span>';
      document.getElementById('formAksi').value = 'tambah';
      document.getElementById('formIdPaket').value = '';
      document.getElementById('paketForm').reset();
      
      document.getElementById('btnSubmit').innerText = 'Simpan Paket';
      document.getElementById('btnReset').classList.add('hidden');
    }
  </script>

</body>
</html>
<?php
session_start();
// 1. Hubungkan ke Database SIMAVIS
include '../koneksi.php';

// Helper Format Tanggal Bahasa Indonesia
function formatTanggalIndo($tanggal) {
    if (!$tanggal) return '-';
    // Gunakan nama bulan lengkap agar seragam dengan laporan bulanan
    $bulan = [
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    $split = explode('-', date('Y-m-d', strtotime($tanggal)));
    return $split[2] . ' ' . $bulan[(int)$split[1]] . ' ' . $split[0];
}

// 2. TANGANI FILTER & PENCARIAN
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, trim($_GET['search'])) : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'semua';

// Query Utama: Mengambil Data Pesanan Booking + Cek Keberadaan Foto di album_foto
$query = "
    SELECT b.*, 
           (SELECT COUNT(*) FROM album_foto a WHERE a.id_pelanggan = b.id_pelanggan) AS jumlah_foto_album
    FROM booking b
    WHERE 1=1
";

// Filter Pencarian (Nama Lengkap / Kode Booking)
if (!empty($search)) {
    $query .= " AND (b.nama_lengkap LIKE '%$search%' OR b.kode_booking LIKE '%$search%')";
}

// Filter Status Foto
if ($status_filter === 'siap') {
    $query .= " HAVING jumlah_foto_album > 0";
} elseif ($status_filter === 'belum') {
    $query .= " HAVING jumlah_foto_album = 0";
}

$query .= " ORDER BY b.id DESC";
$result = mysqli_query($conn, $query);

// Hitung total order untuk angka badge sidebar
$q_total_order = mysqli_query($conn, "SELECT COUNT(*) AS total FROM booking");
$r_total_order = mysqli_fetch_assoc($q_total_order);
$total_order = $r_total_order['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SIMAVIS - Galeri Pesanan</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <!-- Tailwind CSS CDN -->
  <script src="https://cdn.tailwindcss.com"></script>
  <!-- Google Font: Instrument Sans -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>
    body {
      font-family: 'Instrument Sans', sans-serif;
    }
    .custom-scrollbar::-webkit-scrollbar {
      width: 6px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
      background: #f1f1f1;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
      background: #cbd5e1;
      border-radius: 4px;
    }
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
  <main class="flex-1 h-full flex flex-col min-w-0 overflow-hidden bg-white">
    
    <!-- Area Data dengan Scroll Bar -->
    <div class="flex-1 overflow-y-auto p-8 lg:p-10 flex flex-col gap-6 custom-scrollbar">
      
      <!-- HEADER PAGE -->
      <div class="flex justify-between items-center w-full">
        <div class="flex flex-col gap-1">
          <h1 class="text-[#2B2544] text-2xl lg:text-[30px] font-bold">Galeri Pesanan</h1>
          <p class="text-[#6C6287] text-sm font-normal">Daftar folder pesanan berdasarkan nama pelanggan dan ID booking</p>
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

      <!-- FILTER & SEARCH BAR FORM -->
      <form method="GET" action="" class="p-4 bg-white shadow-[0px_10px_24px_-8px_rgba(15,23,42,0.08)] rounded-2xl outline outline-1 outline-[#E5E7EB] -outline-offset-1 flex flex-wrap justify-between items-center gap-4">
        
        <!-- Search Input -->
        <div class="flex items-center gap-4 flex-wrap">
          <div class="w-[320px] px-4 py-2 bg-white rounded-xl outline outline-1 outline-[#E5E7EB] -outline-offset-1 flex items-center gap-2">
            <svg class="w-4 h-4 text-[#6C6287]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input type="text" name="search" value="<?= htmlspecialchars($search); ?>" placeholder="Cari nama pelanggan atau ID pesanan" class="w-full text-xs text-[#2B2544] placeholder-[#6C6287] bg-transparent focus:outline-none" />
          </div>

          <button type="submit" class="px-4 py-2 bg-[#0891B2] text-white font-semibold text-xs rounded-xl shadow-sm hover:bg-[#06748f] transition-colors">
            Cari
          </button>
        </div>

        <!-- Status Foto Filter Tabs -->
        <div class="flex items-center gap-2">
          <a href="galeri.php?status=semua<?= !empty($search) ? '&search=' . urlencode($search) : ''; ?>" 
             class="px-4 py-2 <?= ($status_filter === 'semua') ? 'bg-[#0891B2]/13 text-[#0891B2] outline-[#0891B2]' : 'bg-white text-[#6C6287] outline-[#E5E7EB] hover:bg-gray-50'; ?> font-semibold text-xs rounded-xl outline outline-1 -outline-offset-1 transition-colors">
             Semua
          </a>
          <a href="galeri.php?status=siap<?= !empty($search) ? '&search=' . urlencode($search) : ''; ?>" 
             class="px-4 py-2 <?= ($status_filter === 'siap') ? 'bg-[#0891B2]/13 text-[#0891B2] outline-[#0891B2]' : 'bg-white text-[#6C6287] outline-[#E5E7EB] hover:bg-gray-50'; ?> font-semibold text-xs rounded-xl outline outline-1 -outline-offset-1 transition-colors">
             Foto siap
          </a>
          <a href="galeri.php?status=belum<?= !empty($search) ? '&search=' . urlencode($search) : ''; ?>" 
             class="px-4 py-2 <?= ($status_filter === 'belum') ? 'bg-[#0891B2]/13 text-[#0891B2] outline-[#0891B2]' : 'bg-white text-[#6C6287] outline-[#E5E7EB] hover:bg-gray-50'; ?> font-semibold text-xs rounded-xl outline outline-1 -outline-offset-1 transition-colors">
             Belum ada foto
          </a>
        </div>

      </form>

      <!-- TABLE FOLDER CONTAINER -->
      <div class="p-6 bg-white shadow-[0px_10px_24px_-8px_rgba(15,23,42,0.08)] rounded-2xl outline outline-1 outline-[#E5E7EB] -outline-offset-1 flex flex-col gap-2">
        
        <!-- Table Header -->
        <div class="px-4 py-3 bg-[#F3F0FA] rounded-lg flex items-center gap-3 text-xs font-semibold text-[#6C6287]">
          <div class="w-[260px]">Folder pesanan</div>
          <div class="w-[130px]">Tanggal</div>
          <div class="w-[190px]">Paket</div>
          <div class="w-[180px]">Status Foto</div>
          <div class="flex-1 text-right">Aksi</div>
        </div>

        <!-- Dynamic Rows From Database -->
        <?php if ($result && mysqli_num_rows($result) > 0) : 
            while ($row = mysqli_fetch_assoc($result)) :
                $is_foto_ready = ($row['jumlah_foto_album'] > 0 || $row['status_sesi'] === 'Selesai');
        ?>
        <div class="px-4 py-3.5 border-b border-[#EAE6FA] hover:bg-[#F8FBFF] transition-colors flex items-center gap-3 text-xs">
          <!-- Folder Pesanan -->
          <div class="w-[260px] flex flex-col gap-1">
            <span class="font-bold text-[#0891B2] text-sm"><?= htmlspecialchars($row['nama_lengkap'] ?? 'Pelanggan'); ?></span>
            <span class="text-[#6C6287] text-[12px]"><?= htmlspecialchars($row['kode_booking']); ?></span>
          </div>

          <!-- Tanggal Sesi -->
          <div class="w-[130px] text-[#6C6287]"><?= formatTanggalIndo($row['tanggal_sesi']); ?></div>

          <!-- Nama Paket -->
          <div class="w-[190px] text-[#2B2544] font-medium"><?= htmlspecialchars($row['nama_paket'] ?? '-'); ?></div>

          <!-- Status Foto Badge -->
          <div class="w-[180px]">
            <?php if ($is_foto_ready) : ?>
              <span class="px-2.5 py-1 bg-[#E6FBF2] text-[#10B981] font-semibold rounded-lg text-[12px]">Foto siap</span>
            <?php else : ?>
              <span class="px-2.5 py-1 bg-[#F3F0FA] text-[#6C6287] font-semibold rounded-lg text-[12px]">Belum ada foto</span>
            <?php endif; ?>
          </div>

          <!-- Tombol Aksi (Mengarah ke UnggahFoto.php dengan membawa ID pesanan) -->
          <div class="flex-1 flex justify-end">
            <a href="UnggahFoto.php?id_booking=<?= $row['id']; ?>" class="px-3 py-2 bg-[#0891B2] hover:bg-[#06748f] transition-colors text-white font-bold text-xs rounded-lg flex items-center gap-1.5 shadow-sm">
              <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
              <span>Buka folder</span>
            </a>
          </div>
        </div>
        <?php endwhile; 
        else : ?>
        <div class="p-8 text-center text-xs text-[#6C6287]">
          Tidak ada folder pesanan yang ditemukan.
        </div>
        <?php endif; ?>

      </div>

    </div>
  </main>

</body>
</html>
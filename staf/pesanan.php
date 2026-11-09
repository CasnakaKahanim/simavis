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

// 3. PARAMETER FILTER & SEARCH
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$status_filter = isset($_GET['status']) ? trim($_GET['status']) : '';

// 4. SUSUN QUERY DATABASE
$where_clauses = [];

if (!empty($search)) {
    $search_escaped = mysqli_real_escape_string($conn, $search);
    $where_clauses[] = "(kode_booking LIKE '%$search_escaped%' OR nama_lengkap LIKE '%$search_escaped%' OR no_whatsapp LIKE '%$search_escaped%')";
}

if (!empty($status_filter) && $status_filter !== 'Semua') {
    $status_escaped = mysqli_real_escape_string($conn, $status_filter);
    $where_clauses[] = "status_pembayaran = '$status_escaped'";
}

$sql_where = "";
if (count($where_clauses) > 0) {
    $sql_where = "WHERE " . implode(' AND ', $where_clauses);
}

// Query Ambil Seluruh Data Pesanan
$query_pesanan = "SELECT * FROM booking $sql_where ORDER BY id DESC";
$res_pesanan = mysqli_query($conn, $query_pesanan);

// Total Order untuk Badge Navigasi Sidebar
$q_total_badge = mysqli_query($conn, "SELECT COUNT(*) AS total FROM booking");
$r_total_badge = mysqli_fetch_assoc($q_total_badge);
$total_order_badge = $r_total_badge['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SIMAVIS - Daftar Pesanan</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <!-- Tailwind CSS CDN -->
  <script src="https://cdn.tailwindcss.com"></script>
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
            <a href="pesanan.php" class="w-full px-4 py-3 bg-[#0891B2] rounded-xl flex justify-start items-center gap-3 text-white font-semibold text-sm shadow-md">
                <i class="fa-solid fa-cart-shopping w-5 text-center"></i>
                <span class="flex-1">Pesanan</span>
            </a>

            <!-- Galeri & QR -->
            <a href="galeri.php" class="w-full px-4 py-3 rounded-xl flex justify-start items-center gap-3 text-white/85 hover:bg-white/10 transition-colors font-medium text-sm">
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
      <div class="flex justify-between items-center w-full pb-2">
        <div class="flex flex-col gap-1">
          <h1 class="text-[#2B2544] text-2xl lg:text-[30px] font-bold">Pesanan</h1>
          <p class="text-[#6C6287] text-sm font-normal">Kelola seluruh pesanan dan reservasi Vibes Studio Foto</p>
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

      <!-- FILTER & SEARCH BAR -->
      <form method="GET" action="" class="p-4 bg-white shadow-[0px_10px_24px_-8px_rgba(15,23,42,0.08)] rounded-2xl outline outline-1 outline-[#E5E7EB] -outline-offset-1 flex flex-wrap justify-between items-center gap-4">
        
        <!-- Search Field -->
        <div class="flex items-center gap-4 flex-wrap">
          <div class="w-[260px] px-4 py-2 bg-white rounded-xl outline outline-1 outline-[#E5E7EB] -outline-offset-1 flex items-center gap-2 focus-within:outline-[#0891B2]">
            <i class="fa-solid fa-magnifying-glass text-[#6C6287] text-xs"></i>
            <input type="text" name="search" value="<?= htmlspecialchars($search); ?>" placeholder="Cari nama, ID booking..." class="w-full text-xs text-[#2B2544] placeholder-[#6C6287] bg-transparent focus:outline-none" />
          </div>

          <?php if (!empty($search) || !empty($status_filter)) : ?>
            <a href="pesanan.php" class="text-xs text-rose-500 font-semibold hover:underline flex items-center gap-1">
              <i class="fa-solid fa-rotate-left"></i> Reset Filter
            </a>
          <?php endif; ?>
        </div>

        <!-- Status Filter Buttons -->
        <div class="flex items-center gap-2">
          <a href="pesanan.php?status=Semua<?= !empty($search) ? '&search='.urlencode($search) : ''; ?>" 
             class="px-4 py-2 font-semibold text-xs rounded-xl border transition-colors <?= (empty($status_filter) || $status_filter === 'Semua') ? 'bg-[#0891B2] text-white border-[#0891B2]' : 'bg-white text-[#6C6287] border-[#E5E7EB] hover:bg-gray-50'; ?>">Semua</a>

          <a href="pesanan.php?status=Lunas<?= !empty($search) ? '&search='.urlencode($search) : ''; ?>" 
             class="px-4 py-2 font-semibold text-xs rounded-xl border transition-colors <?= ($status_filter === 'Lunas') ? 'bg-emerald-600 text-white border-emerald-600' : 'bg-white text-[#6C6287] border-[#E5E7EB] hover:bg-gray-50'; ?>">Lunas</a>

          <a href="pesanan.php?status=Pending<?= !empty($search) ? '&search='.urlencode($search) : ''; ?>" 
             class="px-4 py-2 font-semibold text-xs rounded-xl border transition-colors <?= ($status_filter === 'Pending') ? 'bg-amber-500 text-white border-amber-500' : 'bg-white text-[#6C6287] border-[#E5E7EB] hover:bg-gray-50'; ?>">Pending</a>

          <a href="pesanan.php?status=Batal<?= !empty($search) ? '&search='.urlencode($search) : ''; ?>" 
             class="px-4 py-2 font-semibold text-xs rounded-xl border transition-colors <?= ($status_filter === 'Batal') ? 'bg-rose-600 text-white border-rose-600' : 'bg-white text-[#6C6287] border-[#E5E7EB] hover:bg-gray-50'; ?>">Batal</a>
        </div>

      </form>

      <!-- TABLE CONTAINER (DATA PESANAN DARI DB) -->
      <div class="p-6 bg-white shadow-[0px_10px_24px_-8px_rgba(15,23,42,0.08)] rounded-2xl outline outline-1 outline-[#E5E7EB] -outline-offset-1 flex flex-col gap-2 overflow-x-auto custom-scrollbar">
        
        <!-- Table Header -->
        <div class="px-4 py-3 bg-[#F3F0FA] rounded-lg flex items-center gap-3 text-xs font-semibold text-[#6C6287] min-w-[750px]">
          <div class="w-[130px]">ID Pesanan</div>
          <div class="w-[140px]">Tanggal & Jam</div>
          <div class="flex-1">Pelanggan</div>
          <div class="w-[160px]">Paket Foto</div>
          <div class="w-[90px]">Jumlah</div>
          <div class="w-[100px]">Status</div>
          <div class="w-[120px] text-right">Aksi</div>
        </div>

        <!-- Perulangan Data Pesanan PHP -->
        <?php if ($res_pesanan && mysqli_num_rows($res_pesanan) > 0) : 
            while ($row = mysqli_fetch_assoc($res_pesanan)) :
                $id_b        = $row['id'];
                $kode        = $row['kode_booking'] ?? ('BOOK-'.$id_b);
                $nama_p      = $row['nama_lengkap'] ?? 'Pelanggan';
                $wa          = $row['no_whatsapp'] ?? '-';
                $tgl         = formatTanggalIndo($row['tanggal_sesi'] ?? date('Y-m-d'));
                $jam         = $row['jam_sesi'] ?? '10:00';
                $paket       = $row['nama_paket'] ?? 'Studio Photo';
                $peserta     = $row['jumlah_peserta'] ?? '2 orang';
                $status      = $row['status_pembayaran'] ?? 'Pending';

                // Tentukan Badge Warna Status
                $badge_class = 'bg-amber-50 text-amber-600 border-amber-200';
                if ($status === 'Lunas') {
                    $badge_class = 'bg-emerald-50 text-emerald-600 border-emerald-200';
                } elseif ($status === 'Batal') {
                    $badge_class = 'bg-rose-50 text-rose-600 border-rose-200';
                }
        ?>
        <div class="px-4 py-3 bg-[#F8FBFF] border-b border-[#EAE6FA] rounded-xl flex items-center gap-3 text-xs min-w-[750px] hover:bg-gray-50 transition-colors">
          <div class="w-[130px] font-bold text-[#0891B2]"><?= htmlspecialchars($kode); ?></div>
          <div class="w-[140px] text-[#2B2544] font-medium">
            <div><?= $tgl; ?></div>
            <div class="text-[10px] text-gray-500"><i class="fa-regular fa-clock mr-1"></i><?= htmlspecialchars($jam); ?></div>
          </div>
          <div class="flex-1 font-semibold text-[#2B2544]">
            <div><?= htmlspecialchars($nama_p); ?></div>
            <div class="text-[10px] text-gray-400 font-normal"><?= htmlspecialchars($wa); ?></div>
          </div>
          <div class="w-[160px] text-[#6C6287] truncate font-medium"><?= htmlspecialchars($paket); ?></div>
          <div class="w-[90px] text-[#2B2544] font-medium"><?= htmlspecialchars($peserta); ?></div>
          <div class="w-[100px]">
            <span class="px-2.5 py-1 rounded-lg text-[11px] font-bold border <?= $badge_class; ?>">
              <?= htmlspecialchars($status); ?>
            </span>
          </div>
          <div class="w-[120px] text-right flex items-center justify-end gap-2">
            <a href="UnggahFoto.php?id_booking=<?= $id_b; ?>" title="Unggah Foto Sesi" class="p-2 bg-white border border-gray-200 text-[#0891B2] hover:bg-[#0891B2] hover:text-white rounded-lg transition-colors shadow-xs">
              <i class="fa-solid fa-cloud-arrow-up"></i>
            </a>
          </div>
        </div>
        <?php 
            endwhile;
        else : 
        ?>
        <div class="p-8 text-center text-xs text-gray-400 bg-gray-50 rounded-xl border border-dashed my-2">
          Belum ada data pesanan yang tercatat dalam sistem.
        </div>
        <?php endif; ?>

      </div>

    </div>
  </main>

</body>
</html>
<?php
session_start();
// 1. Pelindung Sesi Login Staf
if (!isset($_SESSION['staf_logged_in']) || $_SESSION['staf_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}
// 2. Hubungkan ke Database SIMAVIS
include '../koneksi.php';

// Handler AJAX untuk Update Status Lunas Tanpa Reload Halaman
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax_lunas'])) {
    $id_b_lunas = intval($_POST['id_booking']);
    $update = mysqli_query($conn, "UPDATE booking SET status_pembayaran = 'Lunas' WHERE id = $id_b_lunas");
    
    header('Content-Type: application/json');
    if ($update) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => mysqli_error($conn)]);
    }
    exit;
}

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
    if ($status_filter === 'Pending') {
        $where_clauses[] = "(status_pembayaran = 'Pending' OR status_pembayaran = 'Belum Bayar' OR status_pembayaran LIKE '%Belum Bayar%' OR status_pembayaran = '' OR status_pembayaran IS NULL)";
    } else {
        $status_escaped = mysqli_real_escape_string($conn, $status_filter);
        $where_clauses[] = "status_pembayaran = '$status_escaped'";
    }
}
$sql_where = "";
if (count($where_clauses) > 0) {
    $sql_where = "WHERE " . implode(' AND ', $where_clauses);
}

// Query Ambil Seluruh Data Pesanan
$query_pesanan = "SELECT * FROM booking $sql_where ORDER BY id DESC";
$res_pesanan = mysqli_query($conn, $query_pesanan);
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
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>
    body { font-family: 'Instrument Sans', sans-serif; }
    .custom-scrollbar::-webkit-scrollbar { width: 6px; height: 6px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: #f1f1f1; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
    
    @media (max-width: 768px) {
      aside { display: none !important; }
      .scroll-content { padding-bottom: 160px !important; }
    }
    @media (min-width: 769px) {
      .mobile-bottom-nav { display: none !important; }
    }

    @media print {
      body * { visibility: hidden; }
      #strukThermal, #strukThermal * { visibility: visible; }
      #strukThermal { 
        position: absolute; 
        left: 0; 
        top: 0; 
        width: 80mm;
        font-family: 'Courier New', Courier, monospace !important;
        background: white !important;
        color: black !important;
        padding: 10px;
        box-shadow: none !important;
        border: none !important;
      }
      .no-print { display: none !important; }
    }
  </style>
</head>
<body class="bg-[#F8FAFC] flex h-screen w-screen overflow-hidden">
  
  <!-- ================= SIDEBAR ================= -->
  <aside class="w-[260px] h-full flex-shrink-0 p-5 pt-8 pb-8 flex flex-col justify-between items-start border-r border-[#164E63]"
         style="background: linear-gradient(180deg, #0F597A 0%, #0A4061 60%, #082E4D 100%);">
    <div class="w-full flex flex-col justify-start items-start gap-8">
      <div class="flex justify-start items-center gap-3">
        <img src="../user/asset/logo.jpeg" alt="Logo SIMAVIS" class="w-10 h-10 rounded-lg object-cover shadow-xs" />
        <div class="flex flex-col justify-start items-start gap-0.5">
          <div class="text-white text-base font-bold">SIMAVIS</div>
          <div class="text-[#22D3EE]/80 text-[11px] font-medium uppercase tracking-wider">Vibes Studio Foto</div>
        </div>
      </div>
      <nav class="w-full flex flex-col gap-2">
            <a href="dashboard.php" class="w-full px-4 py-3 rounded-xl flex justify-start items-center gap-3 text-white/85 hover:bg-white/10 transition-colors font-medium text-sm">
                <i class="fa-solid fa-house w-5 text-center"></i>
                <span class="flex-1">Dashboard</span>
            </a>
            <a href="pesanan.php" class="w-full px-4 py-3 bg-[#0891B2] rounded-xl flex justify-start items-center gap-3 text-white font-semibold text-sm shadow-md">
                <i class="fa-solid fa-cart-shopping w-5 text-center"></i>
                <span class="flex-1">Pesanan</span>
            </a>
            <a href="galeri.php" class="w-full px-4 py-3 rounded-xl flex justify-start items-center gap-3 text-white/85 hover:bg-white/10 transition-colors font-medium text-sm">
                <i class="fa-solid fa-images w-5 text-center"></i>
                <span class="flex-1">Galeri & QR</span>
            </a>
            <a href="paket.php" class="w-full px-4 py-3 rounded-xl flex justify-start items-center gap-3 text-white/85 hover:bg-white/10 transition-colors font-medium text-sm">
            <i class="fa-solid fa-boxes-packing w-5 text-center"></i>
            <span class="flex-1">Paket & Tema</span>
            </a>
            <a href="laporan.php" class="w-full px-4 py-3 rounded-xl flex justify-start items-center gap-3 text-white/85 hover:bg-white/10 transition-colors font-medium text-sm">
                <i class="fa-solid fa-chart-pie w-5 text-center"></i>
                <span class="flex-1">Laporan</span>
            </a>
        </nav>
    </div>
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
    <div class="flex-1 overflow-y-auto p-4 sm:p-8 lg:p-10 flex flex-col gap-6 custom-scrollbar scroll-content">
      
      <!-- HEADER PAGE -->
      <div class="flex justify-between items-center w-full pb-2 gap-2">
        <div class="flex flex-col gap-1">
          <h1 class="text-[#2B2544] text-xl sm:text-2xl lg:text-[30px] font-bold">Pesanan</h1>
          <p class="text-[#6C6287] text-xs sm:text-sm font-normal">Kelola seluruh pesanan dan reservasi Vibes Studio Foto</p>
        </div>
        <div class="flex items-center gap-3 shrink-0">
          <div class="px-3 py-2 bg-white shadow-xs rounded-xl outline outline-1 outline-[#EAE6FA] -outline-offset-1 flex items-center gap-2">
            <svg class="w-4 h-4 sm:w-5 sm:h-5 text-[#2B2544] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            <span class="text-[#2B2544] text-[11px] sm:text-xs font-semibold whitespace-nowrap">
              <span class="hidden sm:inline">Hari Ini: </span><?= formatTanggalIndo(date('Y-m-d')); ?>
            </span>
          </div>
        </div>
      </div>

      <!-- FILTER & SEARCH BAR -->
      <form method="GET" action="" class="p-4 bg-white shadow-[0px_10px_24px_-8px_rgba(15,23,42,0.08)] rounded-2xl outline outline-1 outline-[#E5E7EB] -outline-offset-1 flex flex-wrap justify-between items-center gap-4">
        <div class="flex items-center gap-4 flex-wrap w-full sm:w-auto">
          <div class="w-full sm:w-[260px] px-4 py-2 bg-white rounded-xl outline outline-1 outline-[#E5E7EB] -outline-offset-1 flex items-center gap-2 focus-within:outline-[#0891B2]">
            <i class="fa-solid fa-magnifying-glass text-[#6C6287] text-xs"></i>
            <input type="text" name="search" value="<?= htmlspecialchars($search); ?>" placeholder="Cari nama, ID booking..." class="w-full text-xs text-[#2B2544] placeholder-[#6C6287] bg-transparent focus:outline-none" />
          </div>
          <?php if (!empty($search) || !empty($status_filter)) : ?>
            <a href="pesanan.php" class="text-xs text-rose-500 font-semibold hover:underline flex items-center gap-1">
              <i class="fa-solid fa-rotate-left"></i> Reset Filter
            </a>
          <?php endif; ?>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
          <a href="pesanan.php?status=Semua<?= !empty($search) ? '&search='.urlencode($search) : ''; ?>" 
             class="px-3 py-1.5 sm:px-4 sm:py-2 font-semibold text-xs rounded-xl border transition-colors <?= (empty($status_filter) || $status_filter === 'Semua') ? 'bg-[#0891B2] text-white border-[#0891B2]' : 'bg-white text-[#6C6287] border-[#E5E7EB] hover:bg-gray-50'; ?>">Semua</a>
          <a href="pesanan.php?status=Lunas<?= !empty($search) ? '&search='.urlencode($search) : ''; ?>" 
             class="px-3 py-1.5 sm:px-4 sm:py-2 font-semibold text-xs rounded-xl border transition-colors <?= ($status_filter === 'Lunas') ? 'bg-emerald-600 text-white border-emerald-600' : 'bg-white text-[#6C6287] border-[#E5E7EB] hover:bg-gray-50'; ?>">Lunas</a>
          <a href="pesanan.php?status=Pending<?= !empty($search) ? '&search='.urlencode($search) : ''; ?>" 
             class="px-3 py-1.5 sm:px-4 sm:py-2 font-semibold text-xs rounded-xl border transition-colors <?= ($status_filter === 'Pending') ? 'bg-amber-500 text-white border-amber-500' : 'bg-white text-[#6C6287] border-[#E5E7EB] hover:bg-gray-50'; ?>">Pending</a>
          <a href="pesanan.php?status=Batal<?= !empty($search) ? '&search='.urlencode($search) : ''; ?>" 
             class="px-3 py-1.5 sm:px-4 sm:py-2 font-semibold text-xs rounded-xl border transition-colors <?= ($status_filter === 'Batal') ? 'bg-rose-600 text-white border-rose-600' : 'bg-white text-[#6C6287] border-[#E5E7EB] hover:bg-gray-50'; ?>">Batal</a>
        </div>
      </form>

      <!-- TABLE CONTAINER -->
      <div class="p-2 sm:p-6 bg-transparent md:bg-white md:shadow-[0px_10px_24px_-8px_rgba(15,23,42,0.08)] rounded-2xl md:outline md:outline-1 md:outline-[#E5E7EB] -outline-offset-1 flex flex-col gap-4 overflow-x-auto custom-scrollbar mb-0 md:mb-0">
        
        <!-- Table Header Desktop -->
        <div class="hidden md:flex px-4 py-3 bg-[#F3F0FA] rounded-lg items-center gap-3 text-xs font-semibold text-[#6C6287] min-w-[750px]">
          <div class="w-[140px]">ID Pesanan</div>
          <div class="w-[150px]">Tanggal & Jam</div>
          <div class="flex-1">Pelanggan</div>
          <div class="w-[170px]">Paket & Tema Foto</div>
          <div class="w-[70px]">Jumlah</div>
          <div class="w-[100px]">Status</div>
          <div class="w-[110px] text-right">Aksi</div>
        </div>

        <?php if ($res_pesanan && mysqli_num_rows($res_pesanan) > 0) : 
            while ($row = mysqli_fetch_assoc($res_pesanan)) :
                $id_b      = $row['id'];
                $kode      = $row['kode_booking'] ?? ('BOOK-'.$id_b);
                $nama_p    = $row['nama_lengkap'] ?? 'Pelanggan';
                $wa        = $row['no_whatsapp'] ?? '-';
                $tgl       = formatTanggalIndo($row['tanggal_sesi'] ?? date('Y-m-d'));
                $jam       = $row['jam_sesi'] ?? '10:00';
                $paket     = $row['nama_paket'] ?? 'Studio Photo';
                $peserta   = $row['jumlah_peserta'] ?? '2 orang';
                $harga     = $row['total_harga'] ?? 0;
                
                // Ambil info tema/background dari kolom catatan atau kolom khusus jika ada
                $catatan_db = $row['catatan'] ?? '';
                $tema_tampil = 'Standard';
                if (!empty($catatan_db)) {
                    if (strpos($catatan_db, 'Tema/Background:') !== false) {
                        $parts = explode('|', $catatan_db);
                        $tema_tampil = trim(str_replace('Tema/Background:', '', $parts[0]));
                    } else {
                        $tema_tampil = $catatan_db;
                    }
                }
                
                $raw_status = trim($row['status_pembayaran'] ?? '');
                if ($raw_status === '' || stripos($raw_status, 'Belum Bayar') !== false) {
                    $status = 'Pending';
                } else {
                    $status = $raw_status;
                }

                $badge_class = 'bg-amber-50 text-amber-600 border-amber-200';
                if ($status === 'Lunas') {
                    $badge_class = 'bg-emerald-50 text-emerald-600 border-emerald-200';
                } elseif ($status === 'Batal') {
                    $badge_class = 'bg-rose-50 text-rose-600 border-rose-200';
                }
        ?>

        <!-- 1. TAMPILAN DESKTOP -->
        <div class="hidden md:flex px-4 py-3 bg-white border-b border-[#EAE6FA] rounded-xl items-center gap-3 text-xs min-w-[750px] hover:bg-gray-50 transition-colors">
          <div class="w-[140px] font-bold text-[#0891B2]"><?= htmlspecialchars($kode); ?></div>
          <div class="w-[150px] text-[#2B2544] font-medium">
            <div><?= $tgl; ?></div>
            <div class="text-[10px] text-gray-500"><i class="fa-regular fa-clock mr-1"></i><?= htmlspecialchars($jam); ?></div>
          </div>
          <div class="flex-1 font-semibold text-[#2B2544]">
            <div><?= htmlspecialchars($nama_p); ?></div>
            <div class="text-[10px] text-gray-400 font-normal"><?= htmlspecialchars($wa); ?></div>
          </div>
          
          <!-- Paket & Tema Foto -->
          <div class="w-[170px] flex flex-col truncate">
            <span class="font-bold text-[#2B2544] truncate"><?= htmlspecialchars($paket); ?></span>
            <span class="text-[10px] text-[#0891B2] font-semibold truncate"><i class="fa-solid fa-palette mr-1"></i><?= htmlspecialchars($tema_tampil); ?></span>
          </div>

          <div class="w-[70px] text-[#2B2544] font-medium"><?= htmlspecialchars($peserta); ?></div>
          
          <div class="w-[100px] flex flex-col gap-1 items-start">
            <span class="px-2.5 py-1 rounded-lg text-[11px] font-bold border <?= $badge_class; ?>">
              <?= htmlspecialchars($status); ?>
            </span>

            <?php if ($status === 'Pending') : ?>
              <button type="button" onclick="bukaModalStruk('<?= htmlspecialchars($kode); ?>', '<?= htmlspecialchars($nama_p); ?>', '<?= htmlspecialchars($wa); ?>', '<?= $tgl; ?> (<?= htmlspecialchars($jam); ?>)', '<?= htmlspecialchars($paket); ?> - <?= htmlspecialchars($tema_tampil); ?> (<?= htmlspecialchars($peserta); ?>)', 'Rp <?= number_format($harga, 0, ',', '.'); ?>', <?= $id_b; ?>)" class="px-2.5 py-0.5 bg-amber-500 hover:bg-amber-600 text-white rounded text-[10px] font-bold flex items-center gap-1 transition-colors shadow-2xs">
                <i class="fa-solid fa-wallet"></i>
                <span>Lunas</span>
              </button>
            <?php endif; ?>
          </div>

          <div class="w-[110px] text-right flex items-center justify-end">
            <a href="../user/booking.php?edit_id=<?= $id_b; ?>" title="Edit Pesanan" class="px-2.5 py-1.5 bg-white border border-gray-200 text-[#0891B2] hover:bg-[#0891B2] hover:text-white rounded-lg font-semibold text-[11px] flex items-center gap-1.5 transition-colors shadow-2xs">
              <i class="fa-solid fa-pen-to-square"></i>
              <span>Edit Pesanan</span>
            </a>
          </div>
        </div>

        <!-- 2. TAMPILAN MOBILE -->
        <div class="md:hidden bg-white rounded-3xl p-5 border border-gray-100 shadow-[0px_4px_20px_rgba(0,0,0,0.03)] flex flex-col gap-4">
          
          <!-- Baris 1: ID Pesanan & Badge Status -->
          <div class="flex justify-between items-center pb-3 border-b border-gray-100">
            <div class="flex flex-col">
              <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400">ID PESANAN</span>
              <span class="text-sm font-bold text-[#0891B2]"><?= htmlspecialchars($kode); ?></span>
            </div>
            <span class="px-3 py-1 rounded-xl text-xs font-bold border <?= $badge_class; ?>">
              <?= htmlspecialchars($status); ?>
            </span>
          </div>

          <!-- Baris 2: Pelanggan & Jadwal Sesi -->
          <div class="grid grid-cols-2 gap-3 text-xs">
            <div class="flex flex-col gap-0.5">
              <span class="text-[10px] text-gray-400 font-medium">Pelanggan</span>
              <span class="font-bold text-[#2B2544]"><?= htmlspecialchars($nama_p); ?></span>
              <span class="text-[11px] text-gray-400"><?= htmlspecialchars($wa); ?></span>
            </div>
            <div class="flex flex-col gap-0.5">
              <span class="text-[10px] text-gray-400 font-medium">Jadwal Sesi</span>
              <span class="font-bold text-[#2B2544]"><?= $tgl; ?></span>
              <span class="text-[11px] text-gray-500 flex items-center gap-1">
                <i class="fa-regular fa-clock text-gray-400"></i> <?= htmlspecialchars($jam); ?>
              </span>
            </div>
          </div>

          <!-- Baris 3: Paket, Tema & Peserta + Harga Total -->
          <div class="flex justify-between items-end pt-1 pb-1">
            <div class="flex flex-col gap-0.5">
              <span class="text-[10px] text-gray-400 font-medium">Paket & Tema Foto</span>
              <span class="font-bold text-[#2B2544] text-xs">
                <?= htmlspecialchars($paket); ?>
              </span>
              <span class="text-[11px] text-[#0891B2] font-semibold flex items-center gap-1">
                <i class="fa-solid fa-palette text-[10px]"></i> Tema: <?= htmlspecialchars($tema_tampil); ?> (<?= intval($peserta); ?> org)
              </span>
            </div>
            <div class="text-right">
              <span class="text-base font-bold text-[#2B2544]">
                Rp <?= number_format($harga, 0, ',', '.'); ?>
              </span>
            </div>
          </div>

          <!-- Baris 4: Tombol Aksi Sejajar -->
          <div class="flex items-center gap-2 pt-2 border-t border-gray-100">
            <?php if ($status === 'Pending') : ?>
              <button type="button" onclick="bukaModalStruk('<?= htmlspecialchars($kode); ?>', '<?= htmlspecialchars($nama_p); ?>', '<?= htmlspecialchars($wa); ?>', '<?= $tgl; ?> (<?= htmlspecialchars($jam); ?>)', '<?= htmlspecialchars($paket); ?> - <?= htmlspecialchars($tema_tampil); ?> (<?= htmlspecialchars($peserta); ?>)', 'Rp <?= number_format($harga, 0, ',', '.'); ?>', <?= $id_b; ?>)" class="flex-1 py-2.5 bg-[#F59E0B] hover:bg-amber-600 text-white rounded-xl text-xs font-bold flex items-center justify-center gap-2 shadow-xs transition-colors">
                <i class="fa-solid fa-wallet"></i> Konfirmasi Lunas
              </button>
            <?php endif; ?>
            
            <a href="../user/booking.php?edit_id=<?= $id_b; ?>" class="flex-1 py-2.5 bg-white border border-gray-200 text-[#0891B2] hover:bg-[#0891B2] hover:text-white rounded-xl font-bold text-xs flex items-center justify-center gap-2 transition-colors shadow-2xs">
              <i class="fa-solid fa-pen-to-square"></i> Edit Pesanan
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

  <!-- ================= MODAL POP-UP & STRUK KASIR THERMAL ================= -->
  <div id="modalStruk" class="fixed inset-0 bg-black/60 z-50 hidden flex items-center justify-center p-4 backdrop-blur-xs">
    <div class="bg-white rounded-2xl w-full max-w-md shadow-2xl border border-gray-200 overflow-hidden flex flex-col">
      
      <div class="p-4 bg-[#0F597A] text-white flex justify-between items-center no-print">
        <span class="text-xs font-semibold flex items-center gap-1.5" id="modalTitle">
          <i class="fa-solid fa-cash-register"></i> Konfirmasi Pembayaran
        </span>
        <button onclick="tutupModalStruk()" class="text-white hover:text-gray-200 text-sm font-bold px-2">✕</button>
      </div>

      <div class="p-6 bg-gray-50 flex justify-center">
        <div id="strukThermal" class="bg-white w-full max-w-[320px] p-4 rounded-lg shadow-sm border border-gray-200 text-gray-800 font-mono text-[11px] flex flex-col gap-2">
          
          <div class="text-center pb-2 border-b border-dashed border-gray-400">
            <h3 class="font-bold text-sm tracking-wide">VIBES STUDIO FOTO</h3>
            <p class="text-[9px] text-gray-500">Jatibarang, Indramayu</p>
            <p class="text-[9px] text-gray-500">Telp: 0831-5071-9977</p>
          </div>

          <div class="py-1 border-b border-dashed border-gray-400 text-[10px] space-y-0.5">
            <div class="flex justify-between"><span>No Booking :</span><span class="font-bold" id="sKode">-</span></div>
            <div class="flex justify-between"><span>Tanggal    :</span><span id="sJadwal">-</span></div>
            <div class="flex justify-between"><span>Pelanggan  :</span><span id="sNama">-</span></div>
            <div class="flex justify-between"><span>WhatsApp   :</span><span id="sWa">-</span></div>
          </div>

          <div class="py-2 border-b border-dashed border-gray-400">
            <div class="font-bold mb-1">Rincian Layanan:</div>
            <div class="flex justify-between">
              <span id="sPaket" class="truncate max-w-[180px]">-</span>
              <span id="sHarga1">-</span>
            </div>
          </div>

          <div class="py-2 border-b border-dashed border-gray-400 flex justify-between text-xs font-bold">
            <span>TOTAL TAGIHAN:</span>
            <span id="sTotal" class="text-cyan-700">-</span>
          </div>

          <div class="text-center py-2">
            <span id="sBadgeStatus" class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-amber-100 text-amber-800">
              PENDING (BELUM LUNAS)
            </span>
          </div>

          <div class="text-center text-[9px] text-gray-400 pt-1 border-t border-dashed border-gray-300">
            *** TERIMA KASIH ATAS KUNJUNGAN ANDA ***<br>Simpan struk ini sebagai bukti pembayaran sah.
          </div>

        </div>
      </div>

      <div class="p-4 bg-white border-t border-gray-200 flex gap-2 no-print">
        <button type="button" id="btnKembali" onclick="tutupModalStruk()" class="px-4 h-9 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold text-xs rounded-xl transition-colors">
          Batal
        </button>

        <input type="hidden" id="mIdBooking">

        <button type="button" id="btnKonfirmasiLunas" onclick="prosesPelunasanAjax()" class="flex-1 h-9 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl flex items-center justify-center gap-1.5 transition-colors shadow-sm">
          <i class="fa-solid fa-check"></i>
          <span>Konfirmasi Lunas</span>
        </button>

        <button type="button" id="btnCetakStruk" onclick="window.print()" class="flex-1 h-9 bg-[#0891B2] hover:bg-[#06748f] text-white font-bold text-xs rounded-xl flex items-center justify-center gap-1.5 transition-colors shadow-sm hidden">
          <i class="fa-solid fa-print"></i>
          <span>Cetak Struk Fisik</span>
        </button>
      </div>

    </div>
  </div>

  <!-- ================= MOBILE BOTTOM NAVIGATION ================= -->
  <div class="mobile-bottom-nav fixed bottom-0 left-0 right-0 h-16 bg-white border-t border-gray-200 z-50 flex justify-around items-center px-2 shadow-lg">
    <a href="dashboard.php" class="flex flex-col items-center justify-center flex-1 h-full text-gray-400 hover:text-[#0891B2] transition-colors">
      <i class="fa-solid fa-house text-lg"></i>
      <span class="text-[10px] font-semibold mt-1">Beranda</span>
    </a>
    <a href="pesanan.php" class="flex flex-col items-center justify-center flex-1 h-full text-[#0891B2] transition-colors">
      <i class="fa-solid fa-cart-shopping text-lg"></i>
      <span class="text-[10px] font-semibold mt-1">Pesanan</span>
    </a>
    <a href="galeri.php" class="flex flex-col items-center justify-center flex-1 h-full text-gray-400 hover:text-[#0891B2] transition-colors">
      <i class="fa-solid fa-images text-lg"></i>
      <span class="text-[10px] font-semibold mt-1">Galeri</span>
    </a>
    <a href="paket.php" class="flex flex-col items-center justify-center flex-1 h-full text-gray-400 hover:text-[#0891B2] transition-colors">
      <i class="fa-solid fa-boxes-packing text-lg"></i>
      <span class="text-[10px] font-semibold mt-1">Paket</span>
    </a>
    <a href="laporan.php" class="flex flex-col items-center justify-center flex-1 h-full text-gray-400 hover:text-[#0891B2] transition-colors">
      <i class="fa-solid fa-chart-pie text-lg"></i>
      <span class="text-[10px] font-semibold mt-1">Laporan</span>
    </a>
  </div>

  <script>
    let statusBerubah = false;

    function bukaModalStruk(kode, nama, wa, jadwal, paket, total, id) {
      statusBerubah = false;
      document.getElementById('sKode').innerText = kode;
      document.getElementById('sNama').innerText = nama;
      document.getElementById('sWa').innerText = wa;
      document.getElementById('sJadwal').innerText = jadwal;
      document.getElementById('sPaket').innerText = paket;
      document.getElementById('sHarga1').innerText = total;
      document.getElementById('sTotal').innerText = total;
      document.getElementById('mIdBooking').value = id;

      const badge = document.getElementById('sBadgeStatus');
      badge.innerText = 'PENDING (BELUM LUNAS)';
      badge.className = 'px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-amber-100 text-amber-800';
      
      document.getElementById('modalTitle').innerHTML = '<i class="fa-solid fa-cash-register"></i> Konfirmasi Pembayaran';
      document.getElementById('btnKonfirmasiLunas').classList.remove('hidden');
      document.getElementById('btnCetakStruk').classList.add('hidden');
      document.getElementById('btnKembali').innerText = 'Batal';

      document.getElementById('modalStruk').classList.remove('hidden');
    }

    function prosesPelunasanAjax() {
      const idBooking = document.getElementById('mIdBooking').value;
      if (!idBooking) return;

      const formData = new FormData();
      formData.append('ajax_lunas', '1');
      formData.append('id_booking', idBooking);

      fetch('pesanan.php', {
        method: 'POST',
        body: formData
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          statusBerubah = true;
          
          const badge = document.getElementById('sBadgeStatus');
          badge.innerText = 'LUNAS (PAID)';
          badge.className = 'px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-emerald-100 text-emerald-800';

          document.getElementById('modalTitle').innerHTML = '<i class="fa-solid fa-receipt"></i> Struk Pembayaran Sah';

          document.getElementById('btnKonfirmasiLunas').classList.add('hidden');
          document.getElementById('btnCetakStruk').classList.remove('hidden');
          document.getElementById('btnKembali').innerText = 'Tutup';
        } else {
          alert('Gagal memperbarui status: ' + (data.message || 'Terjadi kesalahan'));
        }
      })
      .catch(err => {
        console.error(err);
        alert('Terjadi kesalahan koneksi saat memproses pelunasan.');
      });
    }

    function tutupModalStruk() {
      document.getElementById('modalStruk').classList.add('hidden');
      if (statusBerubah) {
        window.location.reload();
      }
    }
  </script>

</body>
</html>
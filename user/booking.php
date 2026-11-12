<?php
session_start();

if (file_exists('koneksi.php')) {
    include 'koneksi.php';
} else {
    include '../koneksi.php';
}

$edit_id = isset($_GET['edit_id']) ? intval($_GET['edit_id']) : 0;
$is_edit_mode = ($edit_id > 0);

$data_booking = [];
if ($is_edit_mode) {
    $q_edit = mysqli_query($conn, "SELECT * FROM booking WHERE id = $edit_id LIMIT 1");
    $data_booking = mysqli_fetch_assoc($q_edit);
    if (!$data_booking) {
        echo "<script>alert('Data pesanan tidak ditemukan!'); window.location='../staf/pesanan.php';</script>";
        exit;
    }
    $id_pelanggan = $data_booking['id_pelanggan'];
} else {
    if (!isset($_SESSION['id_pelanggan'])) {
        header("Location: login.php");
        exit;
    }
    $id_pelanggan = $_SESSION['id_pelanggan'];
}

$sukses = "";
$error = "";

// Endpoint AJAX Internal: Mengecek jam terpakai untuk tanggal tertentu
if (isset($_GET['ajax_check_date'])) {
    $tgl = mysqli_real_escape_string($conn, $_GET['ajax_check_date']);
    $exclude_id = isset($_GET['exclude_id']) ? intval($_GET['exclude_id']) : 0;
    
    $query_booked = mysqli_query($conn, "SELECT jam_sesi FROM booking WHERE tanggal_sesi = '$tgl' AND id != $exclude_id");
    $booked_slots = [];
    while ($b = mysqli_fetch_assoc($query_booked)) {
        $booked_slots[] = trim($b['jam_sesi']);
    }
    header('Content-Type: application/json');
    echo json_encode($booked_slots);
    exit;
}

$query_pelanggan_data = mysqli_query($conn, "SELECT * FROM pelanggan WHERE id = $id_pelanggan");
$data_plg = mysqli_fetch_assoc($query_pelanggan_data);
$query_paket = mysqli_query($conn, "SELECT * FROM paket_foto");

$catatan_value = '';
if ($is_edit_mode) {
    if (isset($data_booking['catatan'])) $catatan_value = $data_booking['catatan'];
    elseif (isset($data_booking['catatan_fotografer'])) $catatan_value = $data_booking['catatan_fotografer'];
    elseif (isset($data_booking['keterangan'])) $catatan_value = $data_booking['keterangan'];
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama_lengkap   = mysqli_real_escape_string($conn, $_POST['nama_lengkap']);
    $no_whatsapp    = mysqli_real_escape_string($conn, $_POST['no_whatsapp']);
    $email          = mysqli_real_escape_string($conn, $_POST['email']);
    $jumlah_peserta = intval($_POST['jumlah_peserta']);
    $nama_paket     = mysqli_real_escape_string($conn, $_POST['nama_paket']);
    $background     = mysqli_real_escape_string($conn, $_POST['background'] ?? 'Standard');
    $harga_paket    = intval($_POST['harga_paket']);
    $biaya_tambahan = intval($_POST['biaya_tambahan']);
    $total_harga    = $harga_paket + $biaya_tambahan;
    $tanggal_sesi   = mysqli_real_escape_string($conn, $_POST['tanggal_sesi']);
    $jam_sesi       = mysqli_real_escape_string($conn, $_POST['jam_sesi']);
    $catatan_user   = mysqli_real_escape_string($conn, $_POST['catatan'] ?? '');
    
    $catatan        = "Tema/Background: " . $background . ($catatan_user ? " | Catatan: " . $catatan_user : "");

    $exclude_sql = $is_edit_mode ? " AND id != $edit_id" : "";
    $cek_double = mysqli_query($conn, "SELECT id FROM booking WHERE tanggal_sesi = '$tanggal_sesi' AND jam_sesi = '$jam_sesi' $exclude_sql");
    
    if (mysqli_num_rows($cek_double) > 0) {
        $error = "Maaf! Jam sesi $jam_sesi pada tanggal tersebut baru saja dipesan oleh orang lain. Silakan pilih jam lain.";
    } elseif (!empty($nama_lengkap) && !empty($no_whatsapp) && !empty($nama_paket) && !empty($tanggal_sesi) && !empty($jam_sesi)) {
        
        $str_jumlah_peserta = $jumlah_peserta . " Orang";

        if ($is_edit_mode) {
            $update_query = mysqli_query($conn, "UPDATE booking SET 
                nama_lengkap = '$nama_lengkap',
                no_whatsapp = '$no_whatsapp',
                email = '$email',
                jumlah_peserta = '$str_jumlah_peserta',
                nama_paket = '$nama_paket',
                harga_paket = $harga_paket,
                total_harga = $total_harga,
                tanggal_sesi = '$tanggal_sesi',
                jam_sesi = '$jam_sesi',
                catatan = '$catatan'
                WHERE id = $edit_id
            ");

            if ($update_query) {
                echo "<script>alert('Pesanan berhasil diperbarui!'); window.location='../staf/pesanan.php';</script>";
                exit;
            } else {
                $error = "Gagal memperbarui database: " . mysqli_error($conn);
            }
        } else {
            $kode_booking = "SMV-" . date('ymd') . "-" . rand(100, 999);
            $_SESSION['booking_data'] = [
                'id_pelanggan'   => $id_pelanggan,
                'kode_booking'   => $kode_booking,
                'nama_lengkap'   => $nama_lengkap,
                'no_whatsapp'    => $no_whatsapp,
                'email'          => $email,
                'jumlah_peserta' => $str_jumlah_peserta,
                'nama_paket'     => $nama_paket,
                'background'     => $background,
                'harga_paket'    => $harga_paket,
                'biaya_tambahan' => $biaya_tambahan,
                'total_harga'    => $total_harga,
                'tanggal_sesi'   => $tanggal_sesi,
                'jam_sesi'       => $jam_sesi,
                'catatan'        => $catatan
            ];
            header("Location: pembayaran.php");
            exit;
        }
    } else {
        $error = "Mohon lengkapi semua data wajib termasuk pilihan jam sesi!";
    }
}
?>

<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <title>SIMAVIS - <?= $is_edit_mode ? 'Edit Pesanan' : 'Booking Sesi Foto'; ?></title>
  
  <link rel="icon" type="image/jpeg" href="asset/logo.jpeg">
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = { darkMode: 'class' }
  </script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Caveat:wght@700&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    body { font-family: 'Inter', sans-serif; }
    .font-handwriting { font-family: 'Caveat', cursive; }
    .slot-disabled {
      background-color: #F1F5F9 !important;
      color: #94A3B8 !important;
      border-color: #E2E8F0 !important;
      text-decoration: line-through;
      cursor: not-allowed !important;
      opacity: 0.6;
    }
    .slot-selected {
      background-color: #3E2723 !important;
      color: #ffffff !important;
      border-color: #3E2723 !important;
    }
  </style>
</head>
<body class="bg-[#FDFBF7] dark:bg-[#1A1614] text-[#3E2723] dark:text-[#F5EBE6] antialiased transition-colors duration-300">

  <!-- TOP BAR -->
  <div class="w-full bg-[#3E2723] dark:bg-[#2A1B18] text-[#EFEBE9] text-[10px] sm:text-[11px] py-1.5 px-4 text-center font-medium tracking-wide flex justify-center items-center gap-2">
    <i class="fa-solid fa-camera-retro text-[#D7CCC8]"></i>
    <span>VIBES STUDIO JATIBARANG · FORMULIR BOOKING SESI FOTO</span>
  </div>

  <main class="w-full max-w-[1140px] mx-auto px-4 sm:px-6 lg:px-12 py-8 sm:py-12">
    
    <div class="flex justify-between items-center mb-8">
      <div>
        <span class="text-[#5D4037] dark:text-[#D7CCC8] text-xs font-bold tracking-widest uppercase">RESERVASI ONLINE</span>
        <h1 class="text-2xl sm:text-3xl font-bold text-[#3E2723] dark:text-white mt-1">
          <?= $is_edit_mode ? 'Edit Pesanan: ' . htmlspecialchars($data_booking['kode_booking']) : 'Booking Sesi Foto'; ?>
        </h1>
      </div>
      <?php if ($is_edit_mode): ?>
        <a href="../staf/pesanan.php" class="px-4 py-2 bg-white dark:bg-[#231E1B] border border-[#D7CCC8] text-[#3E2723] dark:text-white text-xs font-semibold rounded-xl transition-all">
          <i class="fa-solid fa-arrow-left mr-1"></i> Kembali
        </a>
      <?php else: ?>
        <a href="web_informasi.php" class="px-4 py-2 bg-white dark:bg-[#231E1B] border border-[#D7CCC8]/60 dark:border-[#4E342E] text-[#3E2723] dark:text-white text-xs font-semibold rounded-xl shadow-xs transition-all flex items-center gap-2">
          <i class="fa-solid fa-house"></i>
          <span>Kembali ke Beranda</span>
        </a>
      <?php endif; ?>
    </div>

    <?php if (!empty($error)) { ?>
      <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-600 text-xs sm:text-sm rounded-xl font-medium flex items-center gap-2">
        <i class="fa-solid fa-circle-exclamation"></i>
        <span><?= $error; ?></span>
      </div>
    <?php } ?>

    <form action="" method="POST" class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-8 items-start">
      
      <!-- KOLOM KIRI -->
      <div class="lg:col-span-7 xl:col-span-8 flex flex-col gap-6">
        
        <!-- STEP 1: PILIH PAKET -->
        <div class="p-5 sm:p-6 bg-white dark:bg-[#231E1B] rounded-2xl border border-[#D7CCC8]/60 dark:border-[#4E342E] shadow-sm flex flex-col gap-4">
          <div class="flex items-center gap-3">
            <span class="w-6 h-6 rounded-full bg-[#F5EBE6] dark:bg-[#3E2723] text-[#5D4037] dark:text-[#D7CCC8] font-bold text-xs flex items-center justify-center">1</span>
            <h2 class="font-bold text-sm text-[#3E2723] dark:text-white">Pilih Paket Sesi Foto</h2>
          </div>
          <p class="text-xs text-[#6D4C41] dark:text-gray-300 -mt-2">Pilih Vibes Package yang ingin Anda abadikan.</p>

          <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-1">
            <?php 
            $selected_paket_nama = $is_edit_mode ? $data_booking['nama_paket'] : (isset($_GET['paket']) ? $_GET['paket'] : '');
            while($pkt = mysqli_fetch_assoc($query_paket)) { 
                $is_checked = (!empty($selected_paket_nama) && trim($selected_paket_nama) === trim($pkt['nama_paket']));
                
                $tipe_key = 'couple';
                $base_kapasitas = 2;
                if (stripos($pkt['nama_paket'], 'Bestie') !== false) { $tipe_key = 'bestie'; $base_kapasitas = 3; }
                elseif (stripos($pkt['nama_paket'], 'Gang') !== false) { $tipe_key = 'gang'; $base_kapasitas = 5; }
            ?>
            <label class="cursor-pointer border border-[#D7CCC8]/60 dark:border-[#4E342E] bg-[#FDFBF7] dark:bg-[#1A1614] rounded-xl p-4 flex flex-col justify-between gap-3 hover:border-[#5D4037] transition-all has-[:checked]:border-[#3E2723] has-[:checked]:bg-[#F5EBE6]/40 dark:has-[:checked]:bg-[#3E2723]/30">
              <div class="flex justify-between items-start">
                <span class="text-xs font-bold text-[#3E2723] dark:text-white"><?= htmlspecialchars($pkt['nama_paket']); ?></span>
                <input type="radio" 
                       name="nama_paket" 
                       value="<?= htmlspecialchars($pkt['nama_paket']); ?>" 
                       data-harga="<?= $pkt['harga']; ?>" 
                       data-basekap="<?= $base_kapasitas; ?>"
                       data-tipe="<?= $tipe_key; ?>"
                       class="accent-[#3E2723]" 
                       required 
                       <?= $is_checked ? 'checked' : ''; ?> 
                       onchange="updateSummary(this)">
              </div>
              <span class="text-[11px] text-[#6D4C41] dark:text-gray-400"><?= htmlspecialchars($pkt['kapasitas']); ?></span>
              <span class="text-xs font-extrabold text-[#3E2723] dark:text-white">Rp <?= number_format($pkt['harga'], 0, ',', '.'); ?></span>
            </label>
            <?php } ?>
          </div>
          <input type="hidden" name="harga_paket" id="hargaInput" value="<?= $is_edit_mode ? $data_booking['harga_paket'] : '0'; ?>">
          <input type="hidden" name="biaya_tambahan" id="biayaTambahanInput" value="0">
        </div>

        <!-- STEP 2: PILIH TEMA / BACKGROUND (DINAMIS SESUAI PAKET) -->
        <div class="p-5 sm:p-6 bg-white dark:bg-[#231E1B] rounded-2xl border border-[#D7CCC8]/60 dark:border-[#4E342E] shadow-sm flex flex-col gap-4">
          <div class="flex items-center gap-3">
            <span class="w-6 h-6 rounded-full bg-[#F5EBE6] dark:bg-[#3E2723] text-[#5D4037] dark:text-[#D7CCC8] font-bold text-xs flex items-center justify-center">2</span>
            <h2 class="font-bold text-sm text-[#3E2723] dark:text-white">Pilih Tema & Background</h2>
          </div>
          <p class="text-xs text-[#6D4C41] dark:text-gray-300 -mt-2">Pilihan tema akan menyesuaikan dengan paket foto yang Anda pilih.</p>

          <div id="containerPilihanTema" class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-1">
            <!-- Diisi dinamis oleh JavaScript berdasarkan paket -->
          </div>
          <input type="hidden" name="background" id="backgroundInput" value="Polkadot Theme">
        </div>

        <!-- STEP 3: TANGGAL & JAM SESI -->
        <div class="p-5 sm:p-6 bg-white dark:bg-[#231E1B] rounded-2xl border border-[#D7CCC8]/60 dark:border-[#4E342E] shadow-sm flex flex-col gap-4">
          <div class="flex items-center gap-3">
            <span class="w-6 h-6 rounded-full bg-[#F5EBE6] dark:bg-[#3E2723] text-[#5D4037] dark:text-[#D7CCC8] font-bold text-xs flex items-center justify-center">3</span>
            <h2 class="font-bold text-sm text-[#3E2723] dark:text-white">Pilih Tanggal & Waktu Sesi</h2>
          </div>
          <p class="text-xs text-[#6D4C41] dark:text-gray-300 -mt-2">Semua jadwal dalam Waktu Indonesia Barat (WIB).</p>

          <div class="grid grid-cols-1 md:grid-cols-12 gap-5 pt-1 items-start">
            
            <div class="md:col-span-5 flex flex-col gap-1.5">
              <label class="text-xs font-semibold text-[#3E2723] dark:text-gray-200">Tanggal Sesi</label>
              <input type="date" 
                     id="tanggalSesiInput" 
                     name="tanggal_sesi" 
                     value="<?= $is_edit_mode ? $data_booking['tanggal_sesi'] : date('Y-m-d'); ?>" 
                     min="<?= date('Y-m-d'); ?>" 
                     class="w-full h-11 px-4 bg-[#FDFBF7] dark:bg-[#1A1614] rounded-xl border border-[#D7CCC8]/60 dark:border-[#4E342E] text-xs outline-none focus:border-[#5D4037] font-semibold text-[#3E2723] dark:text-white" 
                     required 
                     onchange="loadSlotJam(this.value)" />
            </div>

            <div class="md:col-span-7 flex flex-col gap-2.5">
              <div class="flex justify-between items-center">
                <span class="text-xs font-semibold text-[#3E2723] dark:text-gray-200">Pilih Jam Sesi</span>
                <span class="text-[11px] text-[#5D4037] dark:text-[#D7CCC8]" id="statusSlotInfo">Memuat slot...</span>
              </div>

              <div id="gridJamSesi" class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                <!-- Diisi otomatis oleh JS -->
              </div>

              <input type="hidden" name="jam_sesi" id="jamSesiInput" value="<?= $is_edit_mode ? trim($data_booking['jam_sesi']) : ''; ?>" required>
            </div>

          </div>
        </div>

        <!-- STEP 4: DATA KLIEN & JUMLAH PESERTA -->
        <div class="p-5 sm:p-6 bg-white dark:bg-[#231E1B] rounded-2xl border border-[#D7CCC8]/60 dark:border-[#4E342E] shadow-sm flex flex-col gap-4">
          <div class="flex items-center gap-3">
            <span class="w-6 h-6 rounded-full bg-[#F5EBE6] dark:bg-[#3E2723] text-[#5D4037] dark:text-[#D7CCC8] font-bold text-xs flex items-center justify-center">4</span>
            <h2 class="font-bold text-sm text-[#3E2723] dark:text-white">Informasi Klien & Jumlah Peserta</h2>
          </div>
          <p class="text-xs text-[#6D4C41] dark:text-gray-300 -mt-2">Gunakan tombol plus/minus untuk menambah jumlah orang jika melebihi kapasitas dasar (otomatis nambah biaya Rp 15.000/orang).</p>

          <?php 
            $jml_val = 2;
            if ($is_edit_mode) {
                preg_match('/\d+/', $data_booking['jumlah_peserta'], $matches);
                $jml_val = isset($matches[0]) ? intval($matches[0]) : 2;
            }
          ?>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-1">
            <div class="flex flex-col gap-1.5">
              <label class="text-xs font-semibold text-[#3E2723] dark:text-gray-200">Nama Lengkap *</label>
              <input type="text" name="nama_lengkap" value="<?= htmlspecialchars($is_edit_mode ? $data_booking['nama_lengkap'] : $data_plg['nama']); ?>" class="w-full h-11 px-4 bg-[#FDFBF7] dark:bg-[#1A1614] rounded-xl border border-[#D7CCC8]/60 dark:border-[#4E342E] text-xs text-[#3E2723] dark:text-white outline-none focus:border-[#5D4037]" required />
            </div>
            <div class="flex flex-col gap-1.5">
              <label class="text-xs font-semibold text-[#3E2723] dark:text-gray-200">Alamat Email *</label>
              <input type="email" name="email" value="<?= htmlspecialchars($is_edit_mode ? ($data_booking['email'] ?? '') : $data_plg['email']); ?>" class="w-full h-11 px-4 bg-[#FDFBF7] dark:bg-[#1A1614] rounded-xl border border-[#D7CCC8]/60 dark:border-[#4E342E] text-xs text-[#3E2723] dark:text-white outline-none focus:border-[#5D4037]" required />
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="flex flex-col gap-1.5">
              <label class="text-xs font-semibold text-[#3E2723] dark:text-gray-200">Nomor WhatsApp *</label>
              <input type="tel" name="no_whatsapp" value="<?= htmlspecialchars($is_edit_mode ? $data_booking['no_whatsapp'] : $data_plg['no_hp']); ?>" class="w-full h-11 px-4 bg-[#FDFBF7] dark:bg-[#1A1614] rounded-xl border border-[#D7CCC8]/60 dark:border-[#4E342E] text-xs text-[#3E2723] dark:text-white outline-none focus:border-[#5D4037]" required />
            </div>
            <div class="flex flex-col gap-1.5">
              <label class="text-xs font-semibold text-[#3E2723] dark:text-gray-200">Jumlah Peserta (Orang) *</label>
              
              <!-- Kontrol Tombol Plus Minus Interaktif -->
              <div class="flex items-center h-11 bg-[#FDFBF7] dark:bg-[#1A1614] rounded-xl border border-[#D7CCC8]/60 dark:border-[#4E342E] overflow-hidden">
                <button type="button" onclick="ubahPeserta(-1)" class="w-12 h-full bg-[#F5EBE6] dark:bg-[#3E2723]/50 text-[#3E2723] dark:text-white font-bold hover:bg-[#D7CCC8] transition-colors flex items-center justify-center">
                  <i class="fa-solid fa-minus text-xs"></i>
                </button>
                <input type="number" 
                       id="jumlahPesertaInput" 
                       name="jumlah_peserta" 
                       min="1" 
                       max="20" 
                       value="<?= $jml_val; ?>" 
                       class="w-full h-full bg-transparent text-center text-xs font-bold text-[#3E2723] dark:text-white outline-none" 
                       required 
                       readonly />
                <button type="button" onclick="ubahPeserta(1)" class="w-12 h-full bg-[#F5EBE6] dark:bg-[#3E2723]/50 text-[#3E2723] dark:text-white font-bold hover:bg-[#D7CCC8] transition-colors flex items-center justify-center">
                  <i class="fa-solid fa-plus text-xs"></i>
                </button>
              </div>

              <span id="infoKapasitas" class="text-[10px] text-[#6D4C41] dark:text-gray-400">Kapasitas dasar paket: 2 orang. (Kelebihan dikenakan biaya Rp 15K/orang)</span>
            </div>
          </div>

          <div class="flex flex-col gap-1.5">
            <label class="text-xs font-semibold text-[#3E2723] dark:text-gray-200">Catatan Tambahan (Opsional)</label>
            <textarea name="catatan" rows="3" placeholder="Contoh: Permintaan khusus gaya atau properti." class="w-full p-3 bg-[#FDFBF7] dark:bg-[#1A1614] rounded-xl border border-[#D7CCC8]/60 dark:border-[#4E342E] text-xs text-[#3E2723] dark:text-white outline-none focus:border-[#5D4037]"><?= htmlspecialchars($catatan_value); ?></textarea>
          </div>
        </div>

      </div>

      <!-- KOLOM KANAN (SUMMARY CARD) -->
      <div class="lg:col-span-5 xl:col-span-4 lg:sticky lg:top-6">
        <div class="p-5 sm:p-6 bg-white dark:bg-[#231E1B] rounded-2xl border border-[#D7CCC8]/60 dark:border-[#4E342E] shadow-sm flex flex-col gap-5">
          
          <div class="flex justify-between items-center border-b border-[#D7CCC8]/40 dark:border-[#4E342E] pb-3">
            <h3 class="font-bold text-sm text-[#3E2723] dark:text-white">Ringkasan Pesanan</h3>
            <span class="text-xs text-[#5D4037] dark:text-[#D7CCC8] font-semibold">SIMAVIS</span>
          </div>

          <div class="flex flex-col gap-2.5 text-xs text-[#6D4C41] dark:text-gray-300">
            <div class="flex justify-between items-center font-semibold text-[#3E2723] dark:text-white">
              <span id="summaryPaket"><?= $is_edit_mode ? htmlspecialchars($data_booking['nama_paket']) : 'Belum memilih paket'; ?></span>
              <span id="summaryHarga">Rp 0</span>
            </div>
            <div class="flex justify-between items-center">
              <span>Tema / Background:</span>
              <span id="summaryBackground" class="font-semibold text-[#3E2723] dark:text-white">-</span>
            </div>
            <div class="flex justify-between items-center">
              <span>Jumlah Peserta:</span>
              <span id="summaryPeserta" class="font-semibold text-[#3E2723] dark:text-white">2 Orang</span>
            </div>
            <div class="flex justify-between items-center">
              <span class="flex items-center gap-1.5"><i class="fa-solid fa-calendar-days text-[#5D4037] dark:text-[#D7CCC8]"></i> Jadwal Sesi:</span>
              <span id="summaryJadwal" class="font-bold text-[#5D4037] dark:text-[#D7CCC8]"><?= $is_edit_mode ? htmlspecialchars($data_booking['jam_sesi']) : 'Pilih jam'; ?></span>
            </div>
          </div>

          <hr class="border-[#D7CCC8]/40 dark:border-[#4E342E]">

          <div class="flex flex-col gap-2 text-xs">
            <div class="flex justify-between">
              <span>Harga Dasar Paket</span>
              <span id="subTotalPaket" class="font-semibold text-[#3E2723] dark:text-white">Rp 0</span>
            </div>
            <div class="flex justify-between" id="rowBiayaTambahan" style="display: none;">
              <span id="labelBiayaTambahan">Extra Person</span>
              <span id="valBiayaTambahan" class="font-semibold text-[#3E2723] dark:text-white">Rp 0</span>
            </div>
            <div class="flex justify-between text-sm font-extrabold text-[#3E2723] dark:text-white pt-2 border-t border-[#D7CCC8]/40 dark:border-[#4E342E]">
              <span>Total Biaya</span>
              <span id="totalBiaya">Rp 0</span>
            </div>
          </div>

          <button type="submit" class="w-full h-11 bg-[#3E2723] hover:bg-[#5D4037] text-white text-xs font-bold rounded-xl shadow-sm transition-colors flex items-center justify-center gap-2">
            <span><?= $is_edit_mode ? 'Simpan Perubahan Pesanan' : 'Lanjut ke Pembayaran'; ?></span>
            <i class="fa-solid fa-arrow-right text-[10px]"></i>
          </button>

        </div>
      </div>

    </form>

  </main>

  <script>
    const masterSlotJam = [
      "09.00 - 09.15",
      "10.30 - 10.45",
      "12.00 - 12.15",
      "13.00 - 13.15",
      "14.30 - 14.45",
      "16.00 - 16.15",
      "17.30 - 17.45",
      "19.00 - 19.15",
      "20.00 - 20.15"
    ];

    const temaList = {
      couple: [
        { nama: "Polkadot Theme", desc: "Nuansa ceria dengan pola titik-titik estetis." },
        { nama: "Flowers Theme", desc: "Dekorasi bunga romantis dan hangat." },
        { nama: "Minimalist Solid", desc: "Warna polos bersih (Putih/Abu/Coklat/Pink)." }
      ],
      bestie: [
        { nama: "Neon Vibes", desc: "Pencahayaan warna-warni energik & kekinian." },
        { nama: "Y2K Aesthetic", desc: "Gaya retro milenial yang seru dan berdamage." },
        { nama: "Homey Blue", desc: "Background eksklusif bernuansa biru lembut." }
      ],
      gang: [
        { nama: "Streetwear", desc: "Gaya urban modern yang cocok untuk banyak orang." },
        { nama: "Retro Vintage", desc: "Nuansa klasik jadul yang unik." },
        { nama: "Full Crew Basic", desc: "Background luas muat banyak orang." }
      ]
    };

    const editModeId = <?= $is_edit_mode ? $edit_id : 0; ?>;
    const selectedJamAwal = "<?= $is_edit_mode ? trim($data_booking['jam_sesi']) : ''; ?>";

    window.addEventListener('DOMContentLoaded', () => {
      const initialDate = document.getElementById('tanggalSesiInput').value;
      loadSlotJam(initialDate);

      const checkedRadio = document.querySelector('input[name="nama_paket"]:checked');
      if (checkedRadio) {
        updateSummary(checkedRadio);
      } else {
        const firstRadio = document.querySelector('input[name="nama_paket"]');
        if(firstRadio) {
          firstRadio.checked = true;
          updateSummary(firstRadio);
        }
      }
    });

    function loadSlotJam(tanggal) {
      const grid = document.getElementById('gridJamSesi');
      const statusInfo = document.getElementById('statusSlotInfo');
      const jamInput = document.getElementById('jamSesiInput');
      
      grid.innerHTML = '<span class="text-xs text-gray-400 col-span-3">Mengecek slot...</span>';

      fetch(`booking.php?ajax_check_date=${tanggal}&exclude_id=${editModeId}`)
        .then(response => response.json())
        .then(bookedSlots => {
          grid.innerHTML = "";
          let slotTersediaCount = 0;

          masterSlotJam.forEach(jam => {
            const isBooked = bookedSlots.includes(jam);
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.innerText = jam;
            btn.className = 'py-2.5 px-2 rounded-xl border border-[#D7CCC8]/60 dark:border-[#4E342E] text-xs font-semibold transition-all text-center';

            if (editModeId > 0 && jam === selectedJamAwal) {
              btn.classList.add('slot-selected');
              jamInput.value = jam;
            }

            if (isBooked && jam !== selectedJamAwal) {
              btn.classList.add('slot-disabled');
              btn.disabled = true;
            } else {
              slotTersediaCount++;
              if (!btn.classList.contains('slot-selected')) {
                btn.classList.add('hover:border-[#5D4037]', 'hover:text-[#5D4037]', 'bg-[#FDFBF7]', 'dark:bg-[#1A1614]', 'text-[#3E2723]', 'dark:text-white');
              }
              btn.onclick = () => selectJam(btn, jam);
            }

            grid.appendChild(btn);
          });

          statusInfo.innerText = `${slotTersediaCount} sesi tersedia`;
        })
        .catch(err => {
          grid.innerHTML = '<span class="text-xs text-red-500 col-span-3">Gagal memuat jadwal.</span>';
        });
    }

    function selectJam(buttonElem, jamValue) {
      const allButtons = document.querySelectorAll('#gridJamSesi button:not(.slot-disabled)');
      allButtons.forEach(btn => {
        btn.classList.remove('slot-selected');
        btn.classList.add('bg-[#FDFBF7]', 'dark:bg-[#1A1614]', 'text-[#3E2723]', 'dark:text-white');
      });

      buttonElem.classList.remove('bg-[#FDFBF7]', 'dark:bg-[#1A1614]', 'text-[#3E2723]', 'dark:text-white');
      buttonElem.classList.add('slot-selected');

      document.getElementById('jamSesiInput').value = jamValue;
      document.getElementById('summaryJadwal').innerText = jamValue;
    }

    let baseKapasitasPaket = 2; 

    function updateSummary(radio) {
      var namaPaket = radio.value;
      var harga = parseInt(radio.getAttribute('data-harga')) || 0;
      baseKapasitasPaket = parseInt(radio.getAttribute('data-basekap')) || 2;
      var tipePaket = radio.getAttribute('data-tipe') || 'couple';
      
      document.getElementById('summaryPaket').innerText = namaPaket;
      document.getElementById('summaryHarga').innerText = "Rp " + harga.toLocaleString('id-ID');
      document.getElementById('subTotalPaket').innerText = "Rp " + harga.toLocaleString('id-ID');
      document.getElementById('hargaInput').value = harga;

      document.getElementById('infoKapasitas').innerText = `Kapasitas dasar paket: ${baseKapasitasPaket} orang. (Kelebihan dikenakan biaya tambahan Rp 15.000/orang)`;

      renderPilihanTema(tipePaket);

      const inputPeserta = document.getElementById('jumlahPesertaInput');
      if(parseInt(inputPeserta.value) < baseKapasitasPaket) {
        inputPeserta.value = baseKapasitasPaket;
      }

      hitungBiayaTambahan();
    }

    function renderPilihanTema(tipe) {
      const container = document.getElementById('containerPilihanTema');
      container.innerHTML = "";
      const listTema = temaList[tipe] || temaList.couple;

      listTema.forEach((t, idx) => {
        const isChecked = idx === 0 ? 'checked' : '';
        if(idx === 0) {
          document.getElementById('backgroundInput').value = t.nama;
          document.getElementById('summaryBackground').innerText = t.nama;
        }

        const div = document.createElement('label');
        div.className = "cursor-pointer border border-[#D7CCC8]/60 dark:border-[#4E342E] bg-[#FDFBF7] dark:bg-[#1A1614] rounded-xl p-3 flex flex-col justify-between gap-1 hover:border-[#5D4037] has-[:checked]:border-[#3E2723] has-[:checked]:bg-[#F5EBE6]/40 dark:has-[:checked]:bg-[#3E2723]/30 transition-all";
        div.innerHTML = `
          <div class="flex justify-between items-center">
            <span class="text-xs font-bold text-[#3E2723] dark:text-white">${t.nama}</span>
            <input type="radio" name="pilihan_tema_radio" value="${t.nama}" class="accent-[#3E2723]" ${isChecked} onchange="pilihTema(this)">
          </div>
          <span class="text-[10px] text-[#6D4C41] dark:text-gray-400">${t.desc}</span>
        `;
        container.appendChild(div);
      });
    }

    function pilihTema(radio) {
      document.getElementById('backgroundInput').value = radio.value;
      document.getElementById('summaryBackground').innerText = radio.value;
    }

    function ubahPeserta(nilai) {
      const inputPeserta = document.getElementById('jumlahPesertaInput');
      let currentVal = parseInt(inputPeserta.value) || 1;
      let newVal = currentVal + nilai;
      if (newVal < 1) newVal = 1;
      if (newVal > 20) newVal = 20;
      inputPeserta.value = newVal;
      hitungBiayaTambahan();
    }

    function hitungBiayaTambahan() {
      const inputPeserta = document.getElementById('jumlahPesertaInput');
      const jumlahOrang = parseInt(inputPeserta.value) || 1;
      const hargaDasar = parseInt(document.getElementById('hargaInput').value) || 0;

      document.getElementById('summaryPeserta').innerText = jumlahOrang + " Orang";

      let biayaTambahan = 0;
      if (jumlahOrang > baseKapasitasPaket) {
        let extraOrang = jumlahOrang - baseKapasitasPaket;
        biayaTambahan = extraOrang * 15000;
      }

      document.getElementById('biayaTambahanInput').value = biayaTambahan;

      const rowExtra = document.getElementById('rowBiayaTambahan');
      if (biayaTambahan > 0) {
        rowExtra.style.display = "flex";
        document.getElementById('labelBiayaTambahan').innerText = `Extra Person (${jumlahOrang - baseKapasitasPaket} orang)`;
        document.getElementById('valBiayaTambahan').innerText = "Rp " + biayaTambahan.toLocaleString('id-ID');
      } else {
        rowExtra.style.display = "none";
      }

      const totalAkhir = hargaDasar + biayaTambahan;
      document.getElementById('totalBiaya').innerText = "Rp " + totalAkhir.toLocaleString('id-ID');
    }

    if (localStorage.getItem('theme') === 'dark') {
      document.documentElement.classList.add('dark');
    }
  </script>
</body>
</html>
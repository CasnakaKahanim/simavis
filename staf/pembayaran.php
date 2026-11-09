<?php
session_start();
if (!isset($_SESSION['staf_logged_in']) || $_SESSION['staf_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

include '../koneksi.php';

function formatTanggalIndo($tanggal) {
    if (!$tanggal) return '-';
    $bulan = [
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    $split = explode('-', date('Y-m-d', strtotime($tanggal)));
    return $split[2] . ' ' . $bulan[(int)$split[1]] . ' ' . $split[0];
}

// Tangkap Data dari Walk-in
$nama_lengkap   = trim($_POST['nama_lengkap'] ?? 'Nadia Putri');
$no_whatsapp    = trim($_POST['no_whatsapp'] ?? '081234567890');
$email          = trim($_POST['email'] ?? 'nadia.putri@email.com');
$jumlah_peserta = trim($_POST['jumlah_peserta'] ?? '2 orang');
$jam_sesi       = trim($_POST['jam_sesi'] ?? '10:00 WIB');
$id_paket       = intval($_POST['id_paket'] ?? 2);

// Ambil Detail Paket
$q_p = mysqli_query($conn, "SELECT * FROM paket_foto WHERE id = $id_paket");
if (!$q_p || mysqli_num_rows($q_p) == 0) {
    $q_p = mysqli_query($conn, "SELECT * FROM paket WHERE id = $id_paket");
}
$r_p = mysqli_fetch_assoc($q_p);

$nama_paket  = $r_p['nama_paket'] ?? $r_p['nama'] ?? 'Couple & Friends';
$total_harga = intval($r_p['harga'] ?? 750000);

// Simpan transaksi tunai langsung
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['proses_pembayaran_tunai'])) {
    $kode_booking      = $_POST['order_id_cust'] ?? ('SIM-' . date('ymd') . '-' . rand(100, 999));
    $tanggal_sesi      = date('Y-m-d');
    $status_pembayaran = 'Lunas';

    $nama_lengkap_db   = mysqli_real_escape_string($conn, $_POST['nama_lengkap']);
    $no_whatsapp_db    = mysqli_real_escape_string($conn, $_POST['no_whatsapp']);
    $email_db          = mysqli_real_escape_string($conn, $_POST['email']);
    $jumlah_peserta_db = mysqli_real_escape_string($conn, $_POST['jumlah_peserta']);
    $jam_sesi_db        = mysqli_real_escape_string($conn, $_POST['jam_sesi']);
    $nama_paket_db     = mysqli_real_escape_string($conn, $_POST['nama_paket']);
    $total_harga_db    = intval($_POST['total_harga']);

    $query_insert = "INSERT INTO booking (kode_booking, nama_lengkap, no_whatsapp, email, jumlah_peserta, nama_paket, total_harga, tanggal_sesi, jam_sesi, status_pembayaran) 
                     VALUES ('$kode_booking', '$nama_lengkap_db', '$no_whatsapp_db', '$email_db', '$jumlah_peserta_db', '$nama_paket_db', '$total_harga_db', '$tanggal_sesi', '$jam_sesi_db', '$status_pembayaran')";

    if (mysqli_query($conn, $query_insert)) {
        header("Location: BookingSelesai.php");
        exit;
    } else {
        $error_simpan = "Gagal menyimpan transaksi: " . mysqli_error($conn);
    }
}

$q_total_order = mysqli_query($conn, "SELECT COUNT(*) AS total FROM booking");
$r_total_order = mysqli_fetch_assoc($q_total_order);
$total_order_badge = $r_total_order['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SIMAVIS - Pembayaran Walk-in</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
            <!-- Dashboard (AKTIF) -->
            <a href="dashboard.php" class="w-full px-4 py-3 bg-[#0891B2] rounded-xl flex justify-start items-center gap-3 text-white font-semibold text-sm shadow-md">
                <i class="fa-solid fa-house w-5 text-center"></i>
                <span class="flex-1">Dashboard</span>
            </a>

            <!-- Pesanan (DISABLED) -->
            <a href="javascript:void(0)" onclick="return false;" class="w-full px-4 py-3 rounded-xl flex justify-start items-center gap-3 text-white/40 font-medium text-sm cursor-not-allowed select-none">
                <i class="fa-solid fa-cart-shopping w-5 text-center"></i>
                <span class="flex-1">Pesanan</span>
            </a>

            <!-- Galeri & QR (DISABLED) -->
            <a href="javascript:void(0)" onclick="return false;" class="w-full px-4 py-3 rounded-xl flex justify-start items-center gap-3 text-white/40 font-medium text-sm cursor-not-allowed select-none">
                <i class="fa-solid fa-images w-5 text-center"></i>
                <span class="flex-1">Galeri & QR</span>
            </a>

            <!-- Paket & Tema Foto (DISABLED) -->
            <a href="javascript:void(0)" onclick="return false;" class="w-full px-4 py-3 rounded-xl flex justify-start items-center gap-3 text-white/40 font-medium text-sm cursor-not-allowed select-none">
                <i class="fa-solid fa-boxes-packing w-5 text-center"></i>
                <span class="flex-1">Paket & Tema</span>
            </a>
            
            <!-- Laporan (DISABLED) -->
            <a href="javascript:void(0)" onclick="return false;" class="w-full px-4 py-3 rounded-xl flex justify-start items-center gap-3 text-white/40 font-medium text-sm cursor-not-allowed select-none">
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

  <!-- MAIN CONTENT -->
  <main class="flex-1 h-full flex flex-col min-w-0 overflow-hidden bg-white">
    
    <!-- Area Data dengan Scroll Bar -->
    <div class="flex-1 overflow-y-auto p-8 lg:p-10 flex flex-col gap-6 custom-scrollbar">
    

      <div class="flex justify-between items-center w-full">
        <div class="flex flex-col gap-1">
          <a href="dashboard.php" class="flex items-center gap-2 text-[#0891B2] text-xs font-semibold hover:underline w-fit">
          <i class="fa-solid fa-arrow-left"></i>
          <span>Kembali ke dashboard</span>
          </a>
          <h1 class="text-[#2B2544] text-2xl lg:text-[28px] font-bold">Pembayaran</h1>
          <p class="text-[#6C6287] text-sm font-normal">Terima pembayaran tunai atau scan QRIS via Midtrans.</p>
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

      <?php if (isset($error_simpan)) : ?>
        <div class="p-4 bg-rose-50 border border-rose-200 text-rose-700 text-xs font-semibold rounded-2xl flex items-center gap-2">
          <i class="fa-solid fa-triangle-exclamation text-rose-600"></i>
          <span><?= htmlspecialchars($error_simpan); ?></span>
        </div>
      <?php endif; ?>

      <form id="paymentForm" action="" method="POST" class="p-8 bg-white shadow-sm rounded-[24px] border border-gray-200 flex flex-col gap-6">
        
        <input type="hidden" name="nama_lengkap" value="<?= htmlspecialchars($nama_lengkap); ?>">
        <input type="hidden" name="no_whatsapp" value="<?= htmlspecialchars($no_whatsapp); ?>">
        <input type="hidden" name="email" value="<?= htmlspecialchars($email); ?>">
        <input type="hidden" name="jumlah_peserta" value="<?= htmlspecialchars($jumlah_peserta); ?>">
        <input type="hidden" name="jam_sesi" value="<?= htmlspecialchars($jam_sesi); ?>">
        <input type="hidden" name="nama_paket" value="<?= htmlspecialchars($nama_paket); ?>">
        <input type="hidden" name="total_harga" value="<?= $total_harga; ?>">
        <input type="hidden" name="proses_pembayaran_tunai" value="1">

        

        <!-- RINGKASAN PESANAN -->
        <div class="p-6 bg-white rounded-2xl border border-gray-200 flex flex-col gap-4">
          <div class="flex justify-between items-center">
            <div>
              <div class="text-[#2B2544] text-lg font-bold"><?= htmlspecialchars($nama_paket); ?></div>
              <div class="text-[#6C6287] text-xs mt-0.5">Pelanggan: <?= htmlspecialchars($nama_lengkap); ?> • <?= htmlspecialchars($no_whatsapp); ?></div>
            </div>
            <div class="text-[#0891B2] text-2xl font-bold">Rp <?= number_format($total_harga, 0, ',', '.'); ?></div>
          </div>
          <div class="text-xs text-gray-500 font-medium">
            <i class="fa-regular fa-clock mr-1 text-[#0891B2]"></i> Sesi Foto: <?= htmlspecialchars($jam_sesi); ?> (<?= formatTanggalIndo(date('Y-m-d')); ?>)
          </div>
        </div>

        <!-- PILIHAN METODE PEMBAYARAN -->
        <div class="flex flex-col gap-4">
          <h2 class="text-[#2B2544] text-sm font-bold">Pilih Metode Pembayaran</h2>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            
            <!-- Midtrans QRIS / Online -->
            <div onclick="bayarViaMidtrans()" class="p-6 bg-[#FAF9F6] border-2 border-gray-200 hover:border-[#0891B2] rounded-2xl flex flex-col justify-between items-center gap-4 cursor-pointer transition-colors shadow-xs">
              <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 bg-[#0891B2] rounded-full"></span>
                <span class="text-[#2B2544] font-bold text-sm">Midtrans (QRIS / E-Wallet / VA)</span>
              </div>
              <div class="p-3 bg-white border border-gray-200 rounded-xl flex items-center justify-center">
                <i class="fa-solid fa-[#0891B2] fa-qrcode text-5xl text-[#0891B2]"></i>
              </div>
              <p class="text-center text-[#6C6287] text-xs font-semibold">Klik untuk bayar online via Midtrans Pop-up</p>
            </div>

            <!-- Cash / Uang Tunai -->
            <div class="p-6 bg-white border-2 border-[#0891B2] rounded-2xl flex flex-col justify-between gap-4">
              <div class="flex items-center justify-center gap-2">
                <span class="w-2.5 h-2.5 bg-emerald-500 rounded-full"></span>
                <span class="text-[#2B2544] font-bold text-sm">Uang Tunai (Cash)</span>
              </div>

              <div class="flex flex-col gap-3">
                <div class="flex flex-col gap-1">
                  <label class="text-[#6C6287] text-[11px] font-semibold">Nominal Diterima</label>
                  <input type="number" id="cashInput" value="<?= $total_harga; ?>" oninput="calculateChange()" class="p-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm font-semibold text-[#2B2544] focus:outline-none focus:border-[#0891B2]" />
                </div>
                
                <div class="flex flex-col gap-1">
                  <label class="text-[#6C6287] text-[11px] font-semibold">Uang Kembali</label>
                  <div id="changeOutput" class="p-2.5 bg-emerald-50 text-emerald-700 font-bold text-sm rounded-xl">
                    Rp 0
                  </div>
                </div>
              </div>

              <button type="submit" class="w-full py-3 bg-[#0891B2] hover:bg-[#06748f] transition-colors text-white font-bold rounded-xl text-xs shadow-md">
                Konfirmasi Pembayaran Tunai
              </button>
            </div>

          </div>
        </div>

      </form>

    </div>
  </main>

  <!-- MIDTRANS SNAP JS CDN -->
  <script src="<?= MIDTRANS_SNAP_URL; ?>" data-client-key="<?= MIDTRANS_CLIENT_KEY; ?>"></script>

  <script>
    const totalPrice = <?= $total_harga; ?>;

    function calculateChange() {
      const cashInput = document.getElementById('cashInput').value;
      const changeOutput = document.getElementById('changeOutput');
      const change = cashInput - totalPrice;

      if (change >= 0) {
        changeOutput.innerText = 'Rp ' + change.toLocaleString('id-ID');
        changeOutput.className = 'p-2.5 bg-emerald-50 text-emerald-700 font-bold text-sm rounded-xl';
      } else {
        changeOutput.innerText = 'Uang Kurang Rp ' + Math.abs(change).toLocaleString('id-ID');
        changeOutput.className = 'p-2.5 bg-rose-50 text-rose-600 font-bold text-sm rounded-xl';
      }
    }

    function bayarViaMidtrans() {
      const formElement = document.getElementById('paymentForm');
      const formData = new FormData(formElement);

      fetch('proses_midtrans.php', {
        method: 'POST',
        body: formData
      })
      .then(res => res.json())
      .then(data => {
        if (data.status === 'success') {
          snap.pay(data.snap_token, {
            onSuccess: function(result) {
              alert('Pembayaran Midtrans Berhasil!');
              window.location.href = 'BookingSelesai.php';
            },
            onPending: function(result) {
              alert('Menunggu Pembayaran...');
              window.location.href = 'pesanan.php';
            },
            onError: function(result) {
              alert('Pembayaran Gagal!');
            }
          });
        } else {
          alert('Error Midtrans: ' + data.message);
        }
      })
      .catch(err => {
        alert('Gagal terhubung ke Midtrans.');
      });
    }

    calculateChange();
  </script>

</body>
</html>
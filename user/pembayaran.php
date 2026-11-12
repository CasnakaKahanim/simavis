<?php
session_start();

if (file_exists('koneksi.php')) {
    include 'koneksi.php';
} else {
    include '../koneksi.php';
}

if (!isset($_SESSION['id_pelanggan']) || !isset($_SESSION['booking_data'])) {
    header("Location: booking.php");
    exit;
}

$b_data       = $_SESSION['booking_data'];
$total_harga  = $b_data['total_harga'];
$minimal_dp   = ceil($total_harga * 0.5); // Perhitungan DP 50%

// Ambil data pelanggan untuk navbar atas
$id_pelanggan = $_SESSION['id_pelanggan'];
$query_pelanggan = mysqli_query($conn, "SELECT * FROM pelanggan WHERE id = $id_pelanggan");
$pelanggan = mysqli_fetch_assoc($query_pelanggan);

function formatTanggalIndo($tanggal) {
    if (!$tanggal) return '-';
    $bulan = [
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    $split = explode('-', date('Y-m-d', strtotime($tanggal)));
    return $split[2] . ' ' . $bulan[(int)$split[1]] . ' ' . $split[0];
}

// Proses jika user memilih "Bayar Nanti di Studio"
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bayar_nanti'])) {
    $id_pelanggan      = $b_data['id_pelanggan'];
    $kode_booking      = $b_data['kode_booking'];
    $nama_lengkap      = mysqli_real_escape_string($conn, $b_data['nama_lengkap']);
    $no_whatsapp       = mysqli_real_escape_string($conn, $b_data['no_whatsapp']);
    $email             = mysqli_real_escape_string($conn, $b_data['email']);
    $jumlah_peserta    = mysqli_real_escape_string($conn, $b_data['jumlah_peserta']);
    $nama_paket        = mysqli_real_escape_string($conn, $b_data['nama_paket']);
    $harga_paket       = $b_data['harga_paket'];
    $biaya_tambahan    = mysqli_real_escape_string($conn, $b_data['biaya_tambahan']);
    $tanggal_sesi      = $b_data['tanggal_sesi'];
    $jam_sesi          = $b_data['jam_sesi'];
    $status_pembayaran = 'Belum Bayar (Bayar di Studio)';
    $status_sesi       = 'Mendatang';

    $query_insert = "INSERT INTO booking (id_pelanggan, nama_lengkap, no_whatsapp, email, jumlah_peserta, nama_paket, harga_paket, biaya_tambahan, total_harga, tanggal_sesi, jam_sesi, kode_booking, status_pembayaran, status_sesi) 
                     VALUES ('$id_pelanggan', '$nama_lengkap', '$no_whatsapp', '$email', '$jumlah_peserta', '$nama_paket', '$harga_paket', '$biaya_tambahan', '$total_harga', '$tanggal_sesi', '$jam_sesi', '$kode_booking', '$status_pembayaran', '$status_sesi')";

    if (mysqli_query($conn, $query_insert)) {
        unset($_SESSION['booking_data']);
        header("Location: akun.php?msg=success_bayar_nanti");
        exit;
    } else {
        $error_msg = "Gagal memproses booking: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <title>SIMAVIS - Pembayaran Booking</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <script src="<?= defined('MIDTRANS_SNAP_URL') ? MIDTRANS_SNAP_URL : 'https://app.sandbox.midtrans.com/snap/snap.js'; ?>" data-client-key="<?= defined('MIDTRANS_CLIENT_KEY') ? MIDTRANS_CLIENT_KEY : ''; ?>"></script>
  <style> body { font-family: 'Inter', sans-serif; } </style>
</head>
<body class="bg-[#F6F8FC] text-[#0C4A6E] min-h-screen flex flex-col">

  <!-- ================= NAVBAR ATAS ================= -->
  <header class="w-full px-4 sm:px-6 lg:px-12 py-4 flex justify-between items-center text-xs text-[#64748B] border-b border-[#E2E8F0] bg-white">
    <div class="flex items-center gap-2">
      <img src="asset/logo.jpeg" alt="Logo" class="w-6 h-6 rounded-md object-cover">
      <span class="font-semibold text-[#0C4A6E]">Vibes Studio Jatibarang</span>
    </div>
    <a href="booking.php" class="text-[#0891B2] font-semibold hover:underline">← Kembali</a>
  </header>

  <!-- ================= MAIN CONTAINER ================= -->
  <main class="w-full max-w-[700px] mx-auto px-4 py-8 flex flex-col gap-6 grow justify-center">

    <div class="flex items-center justify-between">
      <a href="booking.php" class="flex items-center gap-2 text-[#0891B2] text-xs font-semibold hover:underline">
        <i class="fa-solid fa-arrow-left"></i>
        <span>Kembali ke ubah detail booking</span>
      </a>
    </div>

    <?php if (isset($error_msg)): ?>
      <div class="p-4 bg-red-100 border border-red-300 text-red-700 text-xs font-semibold rounded-xl">
        <?= htmlspecialchars($error_msg); ?>
      </div>
    <?php endif; ?>

    <!-- Summary Box (Disamakan dengan Gaya Kartu Booking) -->
    <div class="p-5 sm:p-6 bg-white rounded-2xl border border-[#E2E8F0] shadow-md flex flex-col gap-5">
      
      <div class="flex justify-between items-center border-b border-[#E2E8F0] pb-4">
        <h3 class="font-bold text-sm text-[#0C4A6E]">Sesi pilihan Anda</h3>
        <span class="text-xs text-[#0891B2] font-semibold">SIMAVIS</span>
      </div>

      <div class="flex flex-col gap-3 text-xs text-[#64748B]">
        <div class="flex justify-between items-center font-semibold text-[#0C4A6E]">
          <span><?= htmlspecialchars($b_data['nama_paket']); ?></span>
          <span id="summaryHargaDisplay">Rp <?= number_format($total_harga, 0, ',', '.'); ?></span>
        </div>
        
        <div class="flex justify-between items-center">
          <span class="flex items-center gap-1.5"><i class="fa-solid fa-calendar-days text-[#0891B2]"></i> Sesi Terpilih:</span>
          <span class="font-bold text-[#0891B2]"><?= formatTanggalIndo($b_data['tanggal_sesi']); ?> · Jam <?= htmlspecialchars($b_data['jam_sesi']); ?></span>
        </div>
        
        <div class="flex items-center gap-2">
          <span><i class="fa-solid fa-location-dot text-[#0891B2]"></i> Vibes Studio, Jatibarang</span>
        </div>
      </div>

      <hr class="border-[#E2E8F0]">

      <div class="flex flex-col gap-2 text-xs">
        <div class="flex justify-between">
          <span>Harga Paket</span>
          <span class="font-semibold text-[#0C4A6E]">Rp <?= number_format($total_harga, 0, ',', '.'); ?></span>
        </div>
        <div class="flex justify-between text-sm font-bold text-[#0C4A6E] pt-2 border-t border-[#E2E8F0]">
          <span>Total biaya</span>
          <span id="totalBiayaDisplay">Rp <?= number_format($total_harga, 0, ',', '.'); ?></span>
        </div>
      </div>

      <!-- Pilihan Tipe Pembayaran Online (Lunas vs DP) -->
      <div class="flex flex-col gap-2 pt-2 border-t border-[#E2E8F0]">
        <label class="text-xs font-bold text-[#0C4A6E]">Pilih Nominal Pembayaran Online:</label>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
          <label class="border-2 border-[#0891B2] bg-[#E6F7F8]/30 p-3 rounded-xl flex items-center justify-between cursor-pointer">
            <div class="flex items-center gap-2">
              <input type="radio" name="payment_type" value="full" checked onchange="updatePayAmount()" class="accent-[#0891B2]">
              <span class="text-xs font-bold text-[#0C4A6E]">Bayar Lunas</span>
            </div>
            <span class="text-xs font-bold text-[#0891B2]">Rp <?= number_format($total_harga, 0, ',', '.'); ?></span>
          </label>

          <label class="border-2 border-[#E2E8F0] p-3 rounded-xl flex items-center justify-between cursor-pointer hover:border-[#0891B2]">
            <div class="flex items-center gap-2">
              <input type="radio" name="payment_type" value="dp" onchange="updatePayAmount()" class="accent-[#0891B2]">
              <span class="text-xs font-bold text-[#0C4A6E]">Uang Muka (DP 50%)</span>
            </div>
            <span class="text-xs font-bold text-[#0891B2]">Rp <?= number_format($minimal_dp, 0, ',', '.'); ?></span>
          </label>
        </div>
      </div>
    </div>

    <!-- Opsi Pembayaran -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

      <!-- OPSI 1: MIDTRANS ONLINE -->
      <div class="bg-white p-5 sm:p-6 rounded-2xl border-2 border-[#0891B2] shadow-sm flex flex-col justify-between gap-4">
        <div>
          <div class="flex items-center gap-2 mb-2">
            <span class="w-2.5 h-2.5 bg-[#0891B2] rounded-full"></span>
            <span class="text-[#0C4A6E] font-bold text-xs">Midtrans (QRIS / VA / E-Wallet)</span>
          </div>
          <p class="text-[11px] text-[#64748B]">Bayar otomatis secara online lewat QRIS, GoPay, ShopeePay, atau Transfer Bank.</p>
        </div>

        <button type="button" onclick="bayarOnlineMidtrans()" class="w-full h-12 bg-[#0891B2] hover:bg-[#06748f] text-white font-bold rounded-xl text-xs shadow-md transition-all flex items-center justify-center gap-2">
          <i class="fa-solid fa-qrcode"></i>
          <span id="btnTextMidtrans">Bayar Lunas Rp <?= number_format($total_harga, 0, ',', '.'); ?></span>
        </button>
      </div>

      <!-- OPSI 2: BAYAR NANTI DI STUDIO -->
      <form action="" method="POST" class="bg-white p-5 sm:p-6 rounded-2xl border border-[#E2E8F0] shadow-sm flex flex-col justify-between gap-4">
        <input type="hidden" name="bayar_nanti" value="1">
        <div>
          <div class="flex items-center gap-2 mb-2">
            <span class="w-2.5 h-2.5 bg-amber-500 rounded-full"></span>
            <span class="text-[#0C4A6E] font-bold text-xs">Bayar Nanti di Studio</span>
          </div>
          <p class="text-[11px] text-[#64748B]">Amankan jadwal slot foto sekarang, lalu lakukan pembayaran langsung saat tiba di studio.</p>
        </div>

        <button type="submit" class="w-full h-12 bg-[#F1F5F9] hover:bg-[#E2E8F0] text-[#0C4A6E] font-bold rounded-xl text-xs transition-all border border-[#E2E8F0] flex items-center justify-center gap-2">
          <i class="fa-solid fa-store"></i>
          <span>Konfirmasi & Bayar di Studio</span>
        </button>
      </form>

    </div>

  </main>

  <!-- Form Hidden Data untuk Midtrans cURL -->
  <form id="midtransForm">
    <input type="hidden" name="nama_lengkap" value="<?= htmlspecialchars($b_data['nama_lengkap']); ?>">
    <input type="hidden" name="no_whatsapp" value="<?= htmlspecialchars($b_data['no_whatsapp']); ?>">
    <input type="hidden" name="email" value="<?= htmlspecialchars($b_data['email']); ?>">
    <input type="hidden" name="jumlah_peserta" value="<?= htmlspecialchars($b_data['jumlah_peserta']); ?>">
    <input type="hidden" name="tanggal_sesi" value="<?= htmlspecialchars($b_data['tanggal_sesi']); ?>">
    <input type="hidden" name="jam_sesi" value="<?= htmlspecialchars($b_data['jam_sesi']); ?>">
    <input type="hidden" name="nama_paket" id="inputNamaPaket" value="<?= htmlspecialchars($b_data['nama_paket']); ?>">
    <input type="hidden" name="total_harga" id="inputTotalHarga" value="<?= $total_harga; ?>">
    <input type="hidden" name="kode_booking" value="<?= htmlspecialchars($b_data['kode_booking']); ?>">
  </form>

  <script>
    const fullPrice = <?= $total_harga; ?>;
    const dpPrice = <?= $minimal_dp; ?>;
    const rawPaket = "<?= htmlspecialchars($b_data['nama_paket']); ?>";

    function updatePayAmount() {
      const isDp = document.querySelector('input[name="payment_type"]:checked').value === 'dp';
      const inputHarga = document.getElementById('inputTotalHarga');
      const inputPaket = document.getElementById('inputNamaPaket');
      const btnText = document.getElementById('btnTextMidtrans');
      const summaryHargaDisplay = document.getElementById('summaryHargaDisplay');
      const totalBiayaDisplay = document.getElementById('totalBiayaDisplay');

      if (isDp) {
        inputHarga.value = dpPrice;
        inputPaket.value = rawPaket + " (DP 50%)";
        btnText.innerText = "Bayar DP Rp " + dpPrice.toLocaleString('id-ID');
        summaryHargaDisplay.innerText = "Rp " + dpPrice.toLocaleString('id-ID');
        totalBiayaDisplay.innerText = "Rp " + dpPrice.toLocaleString('id-ID');
      } else {
        inputHarga.value = fullPrice;
        inputPaket.value = rawPaket;
        btnText.innerText = "Bayar Lunas Rp " + fullPrice.toLocaleString('id-ID');
        summaryHargaDisplay.innerText = "Rp " + fullPrice.toLocaleString('id-ID');
        totalBiayaDisplay.innerText = "Rp " + fullPrice.toLocaleString('id-ID');
      }
    }

    function bayarOnlineMidtrans() {
      const formElement = document.getElementById('midtransForm');
      const formData = new FormData(formElement);

      fetch('../staf/proses_midtrans.php', {
        method: 'POST',
        body: formData
      })
      .then(res => res.json())
      .then(data => {
        if (data.status === 'success') {
          snap.pay(data.snap_token, {
            onSuccess: function(result) {
              alert('Pembayaran Midtrans Berhasil!');
              window.location.href = 'akun.php?msg=success_online';
            },
            onPending: function(result) {
              alert('Menunggu pembayaran Anda...');
              window.location.href = 'akun.php?msg=pending';
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
        alert('Gagal terhubung ke server pembayaran.');
      });
    }
  </script>

</body>
</html>
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

$id_pelanggan = $_SESSION['id_pelanggan'];$sukses = "";
$error = "";

// 1. Endpoint AJAX Internal: Mengecek jam terpakai untuk tanggal tertentu
if (isset($_GET['ajax_check_date'])) {$tgl = mysqli_real_escape_string($conn,$_GET['ajax_check_date']);
    $query_booked = mysqli_query($conn, "SELECT jam_sesi FROM booking WHERE tanggal_sesi = '$tgl'");
    $booked_slots = [];
    while ($b = mysqli_fetch_assoc($query_booked)) {
        $booked_slots[] =$b['jam_sesi'];
    }
    header('Content-Type: application/json');
    echo json_encode($booked_slots);
    exit;
}

// 2. Ambil data profil & paket foto
$query_pelanggan_data = mysqli_query($conn, "SELECT * FROM pelanggan WHERE id = $id_pelanggan");
$data_plg = mysqli_fetch_assoc($query_pelanggan_data);
$query_paket = mysqli_query($conn, "SELECT * FROM paket_foto");

// 3. Simpan data booking
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama_lengkap  = mysqli_real_escape_string($conn, $_POST['nama_lengkap']);$no_whatsapp   = mysqli_real_escape_string($conn,$_POST['no_whatsapp']);
    $email         = mysqli_real_escape_string($conn, $_POST['email']);$jumlah_peserta= mysqli_real_escape_string($conn,$_POST['jumlah_peserta']);
    $nama_paket    = mysqli_real_escape_string($conn, $_POST['nama_paket']);$harga_paket   = intval($_POST['harga_paket']);$biaya_tambahan= 0;
    $total_harga   = $harga_paket +$biaya_tambahan;
    $tanggal_sesi  = mysqli_real_escape_string($conn, $_POST['tanggal_sesi']);$jam_sesi      = mysqli_real_escape_string($conn,$_POST['jam_sesi']);
    $catatan       = mysqli_real_escape_string($conn, $_POST['catatan']);$kode_booking  = "SMV-" . date('ymd') . "-" . rand(100, 999);
    $status_pembayaran = "Lunas";
    $status_sesi   = "Mendatang";

    // Proteksi backend: Cek ganda apakah jam tersebut mendadak sudah diambil orang lain
    $cek_double = mysqli_query($conn, "SELECT id FROM booking WHERE tanggal_sesi = '$tanggal_sesi' AND jam_sesi = '$jam_sesi'");
    
    if (mysqli_num_rows($cek_double) > 0) {$error = "Maaf! Jam sesi $jam_sesi pada tanggal tersebut baru saja dipesan oleh orang lain. Silakan pilih jam lain.";
    } elseif (!empty($nama_lengkap) && !empty($no_whatsapp) && !empty($nama_paket) && !empty($tanggal_sesi) && !empty($jam_sesi)) {$query_insert = "INSERT INTO booking (id_pelanggan, nama_lengkap, no_whatsapp, email, jumlah_peserta, nama_paket, harga_paket, biaya_tambahan, total_harga, tanggal_sesi, jam_sesi, kode_booking, status_pembayaran, status_sesi) 
                         VALUES ('$id_pelanggan', '$nama_lengkap', '$no_whatsapp', '$email', '$jumlah_peserta', '$nama_paket', '$harga_paket', '$biaya_tambahan', '$total_harga', '$tanggal_sesi', '$jam_sesi', '$kode_booking', '$status_pembayaran', '$status_sesi')";
        
        if (mysqli_query($conn, $query_insert)) {$sukses = "Booking berhasil! Mengalihkan ke riwayat akun...";
            header("refresh:2;url=akun.php");
        } else {
            $error = "Gagal menyimpan booking: " . mysqli_error($conn);
        }
    } else {
        $error = "Mohon lengkapi semua data wajib termasuk pilihan jam sesi!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SIMAVIS - Booking Sesi Foto</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <style>
    body { font-family: 'Inter', sans-serif; }
    .slot-disabled {
      background-color: #F1F5F9 !important;
      color: #94A3B8 !important;
      border-color: #E2E8F0 !important;
      text-decoration: line-through;
      cursor: not-allowed !important;
      opacity: 0.6;
    }
    .slot-selected {
      background-color: #0891B2 !important;
      color: #ffffff !important;
      border-color: #0891B2 !important;
    }
  </style>
</head>
<body class="bg-[#F6F8FC] text-[#0C4A6E] antialiased">

    <header class="w-full px-6 lg:px-12 py-4 flex justify-between items-center text-xs text-[#64748B] border-b border-[#E2E8F0] bg-white">
        <div class="flex items-center gap-2">
        <img src="asset/logo.jpeg" alt="Logo" class="w-6 h-6 rounded-md object-cover">
        <span>Vibes Studio Jatibarang</span>
        </div>
        <a href="akun.php" class="text-[#0891B2] font-semibold hover:underline">← Kembali ke Akun & Riwayat</a>
    </header>

  <!-- MAIN CONTAINER -->
  <main class="w-full max-w-[1200px] mx-auto px-4 sm:px-6 lg:px-12 py-8">
    
    <h1 class="text-xl sm:text-2xl font-bold mb-6 text-[#0C4A6E]">Booking sesi foto di Vibes Studio Jatibarang</h1>

    <?php if (!empty($sukses)) { ?>
      <div class="mb-6 p-4 bg-[#EAF7F1] border border-[#16795E]/30 text-[#16795E] text-xs rounded-xl font-medium"><?= $sukses; ?></div>
    <?php } ?>
    <?php if (!empty($error)) { ?>
      <div class="mb-6 p-4 bg-red-100 border border-red-400 text-red-700 text-xs rounded-xl font-medium"><?= $error; ?></div>
    <?php } ?>

    <form action="" method="POST" class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
      
      <!-- KOLOM KIRI -->
      <div class="lg:col-span-7 xl:col-span-8 flex flex-col gap-8">
        
        <!-- STEP 2: DETAIL SESI / PAKET -->
        <div class="p-6 bg-white rounded-2xl border border-[#E2E8F0] shadow-2xs flex flex-col gap-4">
          <div class="flex items-center gap-3">
            <span class="w-6 h-6 rounded-full bg-[#E6F7F8] text-[#0891B2] font-bold text-xs flex items-center justify-center">2</span>
            <h2 class="font-semibold text-sm">Detail sesi</h2>
          </div>
          <p class="text-xs text-[#64748B] -mt-2">Pilih paket untuk melihat jadwal dan estimasi durasi foto.</p>

          <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-2">
            <?php 
            while($pkt = mysqli_fetch_assoc($query_paket)) { 
            ?>
            <label class="cursor-pointer border border-[#E2E8F0] rounded-xl p-4 flex flex-col justify-between gap-3 hover:border-[#0891B2] transition-all has-[:checked]:border-[#0891B2] has-[:checked]:bg-[#E6F7F8]/30">
              <div class="flex justify-between items-start">
                <span class="text-xs font-semibold text-[#0C4A6E]"><?= htmlspecialchars($pkt['nama_paket']); ?></span>
                <input type="radio" name="nama_paket" value="<?= htmlspecialchars($pkt['nama_paket']); ?>" data-harga="<?= $pkt['harga']; ?>" class="accent-[#0891B2]" required onchange="updateSummary(this)">
              </div>
              <span class="text-[11px] text-[#64748B]">Sesi foto profesional</span>
              <span class="text-xs font-bold text-[#0C4A6E]">Rp <?= number_format($pkt['harga'], 0, ',', '.'); ?></span>
            </label>
            <?php } ?>
          </div>
          <input type="hidden" name="harga_paket" id="hargaInput" value="0">
        </div>

        <!-- STEP 3: KALENDER TANGGAL & SESI JAM DINAMIS -->
        <div class="p-6 bg-white rounded-2xl border border-[#E2E8F0] shadow-2xs flex flex-col gap-4">
          <div class="flex items-center gap-3">
            <span class="w-6 h-6 rounded-full bg-[#E6F7F8] text-[#0891B2] font-bold text-xs flex items-center justify-center">3</span>
            <h2 class="font-semibold text-sm">Kapan Anda ingin berfoto?</h2>
          </div>
          <p class="text-xs text-[#64748B] -mt-2">Semua jadwal dalam Waktu Indonesia Barat (WIB).</p>

          <div class="grid grid-cols-1 md:grid-cols-12 gap-6 pt-2 items-start">
            
            <!-- Tanggal Input / Mini Selector -->
            <div class="md:col-span-5 flex flex-col gap-2">
              <label class="text-xs font-semibold text-[#0C4A6E]">Pilih Tanggal Sesi</label>
              <input type="date" 
                     id="tanggalSesiInput" 
                     name="tanggal_sesi" 
                     value="<?= date('Y-m-d'); ?>" 
                     min="<?= date('Y-m-d'); ?>" 
                     class="w-full h-11 px-4 bg-[#F8FAFC] rounded-xl border border-[#E2E8F0] text-xs outline-none focus:border-[#0891B2] font-semibold text-[#0891B2]" 
                     required 
                     onchange="loadSlotJam(this.value)" />
              <span class="text-[11px] text-[#64748B]">Pilih tanggal di atas untuk melihat jam yang masih tersedia.</span>
            </div>

            <!-- Slot Jam Grid -->
            <div class="md:col-span-7 flex flex-col gap-3">
              <div class="flex justify-between items-center">
                <span id="labelTanggalTerpilih" class="text-xs font-bold text-[#0C4A6E]">Jadwal Jam Sesi</span>
                <span class="text-[11px] text-[#0891B2]" id="statusSlotInfo">Memuat slot...</span>
              </div>

              <!-- Container Tombol Jam -->
              <div id="gridJamSesi" class="grid grid-cols-2 sm:grid-cols-3 gap-2.5">
                <!-- Diisi otomatis oleh JavaScript -->
              </div>

              <input type="hidden" name="jam_sesi" id="jamSesiInput" required>

              <div class="flex items-center gap-2 pt-2 text-[11px] text-[#64748B]">
                <span class="w-2.5 h-2.5 bg-gray-200 rounded-sm inline-block"></span>
                <span>Abu-abu / Dicoret = Sudah dipesan (tidak bisa diklik)</span>
              </div>
            </div>

          </div>
        </div>

        <!-- STEP 4: DATA CLIENT -->
        <div class="p-6 bg-white rounded-2xl border border-[#E2E8F0] shadow-2xs flex flex-col gap-4">
          <div class="flex items-center gap-3">
            <span class="w-6 h-6 rounded-full bg-[#E6F7F8] text-[#0891B2] font-bold text-xs flex items-center justify-center">4</span>
            <h2 class="font-semibold text-sm">Siapa yang akan datang?</h2>
          </div>
          <p class="text-xs text-[#64748B] -mt-2">Data ini digunakan untuk konfirmasi dan pengingat sesi.</p>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
            <div class="flex flex-col gap-1.5">
              <label class="text-xs font-semibold">Nama lengkap *</label>
              <input type="text" name="nama_lengkap" value="<?= htmlspecialchars($data_plg['nama']); ?>" class="w-full h-11 px-4 bg-[#F8FAFC] rounded-xl border border-[#E2E8F0] text-xs outline-none focus:border-[#0891B2]" required />
            </div>
            <div class="flex flex-col gap-1.5">
              <label class="text-xs font-semibold">Email *</label>
              <input type="email" name="email" value="<?= htmlspecialchars($data_plg['email']); ?>" class="w-full h-11 px-4 bg-[#F8FAFC] rounded-xl border border-[#E2E8F0] text-xs outline-none focus:border-[#0891B2]" required />
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="flex flex-col gap-1.5">
              <label class="text-xs font-semibold">Nomor WhatsApp *</label>
              <input type="text" name="no_whatsapp" value="<?= htmlspecialchars($data_plg['no_hp']); ?>" class="w-full h-11 px-4 bg-[#F8FAFC] rounded-xl border border-[#E2E8F0] text-xs outline-none focus:border-[#0891B2]" required />
            </div>
            <div class="flex flex-col gap-1.5">
              <label class="text-xs font-semibold">Jumlah peserta *</label>
              <input type="text" name="jumlah_peserta" placeholder="Contoh: 2 Orang" class="w-full h-11 px-4 bg-[#F8FAFC] rounded-xl border border-[#E2E8F0] text-xs outline-none focus:border-[#0891B2]" required />
            </div>
          </div>

          <div class="flex flex-col gap-1.5">
            <label class="text-xs font-semibold">Catatan untuk fotografer (opsional)</label>
            <textarea name="catatan" rows="3" placeholder="Contoh: Ingin nuansa casual dengan backdrop cerah." class="w-full p-4 bg-[#F8FAFC] rounded-xl border border-[#E2E8F0] text-xs outline-none focus:border-[#0891B2]"></textarea>
          </div>
        </div>

      </div>

      <!-- KOLOM KANAN (SUMMARY CARD) -->
      <div class="lg:col-span-5 xl:col-span-4 sticky top-6">
        <div class="p-6 bg-white rounded-2xl border border-[#E2E8F0] shadow-md flex flex-col gap-5">
          
          <div class="flex justify-between items-center border-b border-[#E2E8F0] pb-4">
            <h3 class="font-bold text-sm text-[#0C4A6E]">Sesi pilihan Anda</h3>
            <span class="text-xs text-[#0891B2] font-semibold">SIMAVIS</span>
          </div>

          <div class="flex flex-col gap-3 text-xs text-[#64748B]">
            <div class="flex justify-between items-center font-semibold text-[#0C4A6E]">
              <span id="summaryPaket">Pilih paket di samping</span>
              <span id="summaryHarga">Rp 0</span>
            </div>
            <div class="flex justify-between items-center">
              <span>📅 Sesi Terpilih:</span>
              <span id="summaryJadwal" class="font-bold text-[#0891B2]">Belum memilih jam</span>
            </div>
            <div class="flex items-center gap-2"><span>📍 Vibes Studio, Jatibarang</span></div>
          </div>

          <hr class="border-[#E2E8F0]">

          <div class="flex flex-col gap-2 text-xs">
            <div class="flex justify-between">
              <span>Harga Paket</span>
              <span id="subTotalPaket" class="font-semibold text-[#0C4A6E]">Rp 0</span>
            </div>
            <div class="flex justify-between text-sm font-bold text-[#0C4A6E] pt-2 border-t border-[#E2E8F0]">
              <span>Total biaya</span>
              <span id="totalBiaya">Rp 0</span>
            </div>
          </div>

          <button type="submit" class="w-full py-3.5 bg-[#0891B2] hover:bg-[#06748f] text-white text-xs font-bold rounded-xl shadow-lg transition-all flex items-center justify-center gap-2">
            <span>Konfirmasi & Simpan Booking</span>
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
          </button>

        </div>
      </div>

    </form>

  </main>

  <!-- JAVASCRIPT UNTUK SLOT JAM & CEK DATABASE -->
  <script>
    // Daftar Pilihan Jam Studio
    const masterSlotJam = [
      "09.00 - 09.15",
      "10.30 - 10.45",
      "12.00 - 12.15",
      "13.00 - 13.15",
      "14.30 - 14.45",
      "16.00 - 16.15",
      "17.30 - 17.45"
    ];

    // Ambil elemen tanggal saat ini saat halaman dimuat
    window.addEventListener('DOMContentLoaded', () => {
      const initialDate = document.getElementById('tanggalSesiInput').value;
      loadSlotJam(initialDate);
    });

    // Fungsi Ajax Fetch Slot
    function loadSlotJam(tanggal) {
      const grid = document.getElementById('gridJamSesi');
      const statusInfo = document.getElementById('statusSlotInfo');
      const jamInput = document.getElementById('jamSesiInput');
      
      jamInput.value = ""; // Reset pilihan jam
      document.getElementById('summaryJadwal').innerText = "Pilih jam sesi";
      grid.innerHTML = '<span class="text-xs text-gray-400 col-span-3">Mengecek ketersediaan jam...</span>';

      fetch(`booking.php?ajax_check_date=${tanggal}`)
        .then(response => response.json())
        .then(bookedSlots => {
          grid.innerHTML = "";
          let slotTersediaCount = 0;

          masterSlotJam.forEach(jam => {
            const isBooked = bookedSlots.includes(jam);
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.innerText = jam;
            btn.className = 'py-2.5 px-3 rounded-xl border border-[#E2E8F0] text-xs font-semibold transition-all text-center';

            if (isBooked) {
              // Jika sudah dipesan: Nonaktifkan & Coret (seperti di gambar)
              btn.classList.add('slot-disabled');
              btn.disabled = true;
              btn.title = 'Sudah dipesan oleh orang lain';
            } else {
              // Jika masih kosong: Bisa diklik
              slotTersediaCount++;
              btn.classList.add('hover:border-[#0891B2]', 'hover:text-[#0891B2]', 'bg-white', 'text-[#0C4A6E]');
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

    // Fungsi saat user mengeklik tombol jam yang tersedia
    function selectJam(buttonElem, jamValue) {
      // Clear style pilihan jam sebelumnya
      const allButtons = document.querySelectorAll('#gridJamSesi button:not(.slot-disabled)');
      allButtons.forEach(btn => {
        btn.classList.remove('slot-selected');
        btn.classList.add('bg-white', 'text-[#0C4A6E]');
      });

      // Highlight tombol yang baru dipilih
      buttonElem.classList.remove('bg-white', 'text-[#0C4A6E]');
      buttonElem.classList.add('slot-selected');

      // Set value ke hidden input & summary card
      document.getElementById('jamSesiInput').value = jamValue;
      document.getElementById('summaryJadwal').innerText = jamValue;
    }

    // Update Summary Paket & Total Biaya
    function updateSummary(radio) {
      var namaPaket = radio.value;
      var harga = parseInt(radio.getAttribute('data-harga')) || 0;
      
      document.getElementById('summaryPaket').innerText = namaPaket;
      document.getElementById('summaryHarga').innerText = "Rp " + harga.toLocaleString('id-ID');
      document.getElementById('subTotalPaket').innerText = "Rp " + harga.toLocaleString('id-ID');
      document.getElementById('hargaInput').value = harga;
      document.getElementById('totalBiaya').innerText = "Rp " + harga.toLocaleString('id-ID');
    }
  </script>
</body>
</html>
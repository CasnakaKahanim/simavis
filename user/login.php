<?php
session_start();

// Cek apakah file koneksi.php ada di folder yang sama, jika tidak sesuaikan
if (file_exists('koneksi.php')) {
    include 'koneksi.php';
} else {
    include '../koneksi.php';
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama  = isset($_POST['nama']) ? mysqli_real_escape_string($conn, $_POST['nama']) : '';
    $email = isset($_POST['email']) ? mysqli_real_escape_string($conn, $_POST['email']) : '';
    $no_hp = isset($_POST['no_hp']) ? mysqli_real_escape_string($conn, $_POST['no_hp']) : '';

    if (!empty($email) && !empty($nama) && !empty($no_hp)) {
        $cek_pelanggan = mysqli_query($conn, "SELECT * FROM pelanggan WHERE email = '$email'");

        if (mysqli_num_rows($cek_pelanggan) > 0) {
            $row = mysqli_fetch_assoc($cek_pelanggan);
            $_SESSION['id_pelanggan'] = $row['id'];
            $_SESSION['nama_pelanggan'] = $row['nama'];
        } else {
            $words = explode(" ", $nama);
            $inisial = strtoupper(substr($words[0], 0, 1) . (isset($words[1]) ? substr($words[1], 0, 1) : ''));

            $insert = mysqli_query($conn, "INSERT INTO pelanggan (nama, email, no_hp, inisial) VALUES ('$nama', '$email', '$no_hp', '$inisial')");
            
            if ($insert) {
                $_SESSION['id_pelanggan'] = mysqli_insert_id($conn);
                $_SESSION['nama_pelanggan'] = $nama;
            } else {
                $error = "Gagal mendaftarkan akun otomatis ke database.";
            }
        }

        if (empty($error)) {
            header("Location: akun.php");
            exit;
        }
    } else {
        $error = "Semua kolom wajib diisi!";
    }
}
?>
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <title>SIMAVIS - Masuk Klien</title>
  
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
  </style>
</head>
<body class="bg-[#FDFBF7] dark:bg-[#1A1614] text-[#3E2723] dark:text-[#F5EBE6] antialiased min-h-screen flex flex-col justify-between transition-colors duration-300">

  <!-- ================= TOP BAR ================= -->
  <div class="w-full bg-[#3E2723] dark:bg-[#2A1B18] text-[#EFEBE9] text-[10px] sm:text-[11px] py-2 px-4 text-center font-medium tracking-wide flex justify-center items-center gap-2">
    <i class="fa-solid fa-camera-retro text-[#D7CCC8]"></i>
    <span>VIBES STUDIO JATIBARANG · AKSES AKUN & RIWAYAT BOOKING</span>
  </div>

  <!-- ================= MAIN CONTAINER SPLIT ================= -->
  <div class="w-full flex-1 flex flex-col lg:flex-row items-center justify-center">
    
    <!-- KOLOM KIRI: Hanya tampil di Desktop/Laptop -->
    <div class="hidden lg:flex w-[45%] xl:w-[500px] p-8 lg:p-12 flex-col justify-between items-center text-center bg-[#F5EBE6]/60 dark:bg-[#231E1B] border-r border-[#D7CCC8]/60 dark:border-[#4E342E] min-h-[calc(100vh-37px)]">
      <div class="w-full flex justify-start">
        <a href="web_informasi.php" class="inline-flex items-center gap-2 text-xs font-semibold text-[#5D4037] dark:text-[#D7CCC8] hover:underline bg-white/80 dark:bg-[#1A1614] px-3 py-1.5 rounded-xl border border-[#D7CCC8]/60 dark:border-[#4E342E] shadow-xs transition-all">
          <i class="fa-solid fa-arrow-left"></i>
          <span>Kembali ke Beranda</span>
        </a>
      </div>

      <div class="flex flex-col items-center gap-5 my-auto py-8">
        <div class="w-24 h-24 sm:w-28 sm:h-28 rounded-3xl bg-white dark:bg-[#1A1614] border border-[#D7CCC8]/60 dark:border-[#4E342E] shadow-md flex justify-center items-center overflow-hidden p-2">
          <img src="asset/logo.jpeg" alt="Logo SIMAVIS" class="w-full h-full object-cover rounded-2xl" onerror="this.src='https://placehold.co/100x100'" />
        </div>
        <div class="flex flex-col gap-1.5">
          <h1 class="text-[#3E2723] dark:text-white text-2xl lg:text-4xl font-handwriting">Explore your imagination</h1>
          <p class="text-[#6D4C41] dark:text-gray-300 text-xs lg:text-sm font-normal max-w-[340px] leading-relaxed">
            Masuk untuk mulai booking jadwal sesi foto dan mengelola album digital Anda di SIMAVIS.
          </p>
        </div>
      </div>

      <div class="text-[#8D6E63] dark:text-gray-400 text-xs font-medium">
        © 2026 SIMAVIS · Vibes Studio Jatibarang
      </div>
    </div>

    <!-- KOLOM KANAN / TAMPILAN UTAMA -->
    <div class="flex-1 w-full p-4 sm:p-8 lg:p-12 flex flex-col justify-center items-center bg-[#FDFBF7] dark:bg-[#1A1614]">
      
      <!-- Tombol Kembali untuk HP -->
      <div class="w-full max-w-[440px] mb-3 flex lg:hidden justify-start">
        <a href="web_informasi.php" class="inline-flex items-center gap-1.5 text-xs font-semibold text-[#5D4037] dark:text-[#D7CCC8] hover:underline bg-white dark:bg-[#231E1B] px-3 py-1.5 rounded-xl border border-[#D7CCC8]/60 dark:border-[#4E342E] shadow-xs">
          <i class="fa-solid fa-arrow-left"></i>
          <span>Kembali ke Beranda</span>
        </a>
      </div>

      <!-- Card Form -->
      <div class="w-full max-w-[440px] p-5 sm:p-8 bg-white dark:bg-[#231E1B] rounded-2xl border border-[#D7CCC8]/60 dark:border-[#4E342E] shadow-sm flex flex-col gap-5">
        
        <!-- LOGO TENGAH KHUSUS HP -->
        <div class="flex lg:hidden flex-col items-center justify-center gap-1.5 pt-1 pb-2 border-b border-[#D7CCC8]/40 dark:border-[#4E342E]">
          <img src="asset/logo.jpeg" alt="Logo SIMAVIS" class="w-14 h-14 rounded-2xl object-cover border border-[#D7CCC8]/60 shadow-sm" onerror="this.src='https://placehold.co/60x60'" />
          <span class="text-[#3E2723] dark:text-white text-base font-extrabold tracking-tight mt-1">SIMAVIS</span>
          <span class="text-[#5D4037] dark:text-[#D7CCC8] text-[10px] font-bold tracking-widest uppercase">Vibes Studio Jatibarang</span>
        </div>

        <div class="flex flex-col gap-1 text-center sm:text-left">
          <h2 class="text-[#3E2723] dark:text-white text-xl sm:text-2xl font-bold">Selamat Datang!</h2>
          <p class="text-[#6D4C41] dark:text-gray-300 text-xs leading-relaxed">
            Masukkan data Anda untuk masuk. Jika belum terdaftar, akun baru akan otomatis dibuat.
          </p>
        </div>

        <?php if (!empty($error)) { ?>
          <div class="p-3 bg-red-50 dark:bg-red-950/40 border border-red-200 dark:border-red-800 text-red-600 dark:text-red-400 text-xs rounded-xl flex items-center gap-2">
            <i class="fa-solid fa-circle-exclamation"></i>
            <span><?= $error; ?></span>
          </div>
        <?php } ?>

        <!-- Form Utama -->
        <form class="flex flex-col gap-3.5" action="" method="POST">
          
          <div class="flex flex-col gap-1.5">
            <label class="text-[#3E2723] dark:text-gray-200 text-xs font-semibold">Nama Lengkap</label>
            <div class="relative">
              <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-[#8D6E63]">
                <i class="fa-solid fa-user text-xs"></i>
              </span>
              <input type="text" 
                     name="nama" 
                     placeholder="Contoh: Nadia Putri" 
                     class="w-full h-11 pl-10 pr-4 bg-[#FDFBF7] dark:bg-[#1A1614] rounded-xl border border-[#D7CCC8]/60 dark:border-[#4E342E] text-[#3E2723] dark:text-white text-xs font-medium focus:outline-none focus:border-[#5D4037] transition-all"
                     required />
            </div>
          </div>

          <div class="flex flex-col gap-1.5">
            <label class="text-[#3E2723] dark:text-gray-200 text-xs font-semibold">Alamat Email</label>
            <div class="relative">
              <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-[#8D6E63]">
                <i class="fa-solid fa-envelope text-xs"></i>
              </span>
              <input type="email" 
                     name="email" 
                     placeholder="email@domain.com" 
                     class="w-full h-11 pl-10 pr-4 bg-[#FDFBF7] dark:bg-[#1A1614] rounded-xl border border-[#D7CCC8]/60 dark:border-[#4E342E] text-[#3E2723] dark:text-white text-xs font-medium focus:outline-none focus:border-[#5D4037] transition-all"
                     required />
            </div>
          </div>

          <div class="flex flex-col gap-1.5">
            <label class="text-[#3E2723] dark:text-gray-200 text-xs font-semibold">Nomor WhatsApp</label>
            <div class="relative">
              <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-[#8D6E63]">
                <i class="fa-brands fa-whatsapp text-xs"></i>
              </span>
              <input type="tel" 
                     name="no_hp" 
                     placeholder="081234567890" 
                     class="w-full h-11 pl-10 pr-4 bg-[#FDFBF7] dark:bg-[#1A1614] rounded-xl border border-[#D7CCC8]/60 dark:border-[#4E342E] text-[#3E2723] dark:text-white text-xs font-medium focus:outline-none focus:border-[#5D4037] transition-all"
                     required />
            </div>
          </div>

          <button type="submit" 
                  class="w-full h-11 bg-[#3E2723] hover:bg-[#5D4037] transition-colors text-white font-bold text-xs rounded-xl shadow-sm flex items-center justify-center gap-2 mt-1">
            <span>Masuk / Lanjutkan</span>
            <i class="fa-solid fa-arrow-right text-[10px]"></i>
          </button>

        </form>

        <!-- Pilihan Opsi Login Lainnya -->
        <div class="flex items-center my-1">
          <div class="flex-1 border-t border-[#D7CCC8]/40 dark:border-[#4E342E]"></div>
          <span class="px-3 text-[#8D6E63] dark:text-gray-400 text-[11px] font-medium uppercase">Atau masuk dengan</span>
          <div class="flex-1 border-t border-[#D7CCC8]/40 dark:border-[#4E342E]"></div>
        </div>

        <div class="grid grid-cols-2 gap-3">
          <!-- Tombol Logo Google (Statik / Belum aktif) -->
          <div class="h-10 px-3 border border-[#D7CCC8]/60 dark:border-[#4E342E] bg-[#FDFBF7] dark:bg-[#1A1614] rounded-xl flex items-center justify-center gap-2 text-xs font-semibold text-[#3E2723] dark:text-white select-none cursor-default">
            <i class="fa-brands fa-google text-red-500 text-sm"></i>
            <span>Google</span>
          </div>
          
          <!-- Tombol Logo WhatsApp (Statik / Belum aktif) -->
          <div class="h-10 px-3 border border-[#D7CCC8]/60 dark:border-[#4E342E] bg-[#FDFBF7] dark:bg-[#1A1614] rounded-xl flex items-center justify-center gap-2 text-xs font-semibold text-[#3E2723] dark:text-white select-none cursor-default">
            <i class="fa-brands fa-whatsapp text-emerald-500 text-base"></i>
            <span>WhatsApp</span>
          </div>
        </div>

      </div>

    </div>

  </div>

  <script>
    if (localStorage.getItem('theme') === 'dark') {
      document.documentElement.classList.add('dark');
    }
  </script>
</body>
</html>
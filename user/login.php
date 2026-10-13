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
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SIMAVIS - Masuk Klien</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@700;800;900&display=swap" rel="stylesheet">
  <style>
    body { font-family: 'Inter', sans-serif; margin: 0; padding: 0; overflow: hidden; }
    .font-outfit { font-family: 'Outfit', sans-serif; }
  </style>
</head>
<body class="bg-gray-100 flex h-screen w-screen overflow-hidden">

  <div class="w-full h-full flex flex-col lg:flex-row overflow-hidden">
    
    <!-- KOLOM KIRI (BRAND / BANNER) -->
    <div class="w-full lg:w-[40%] xl:w-[546px] h-full p-8 lg:p-12 flex flex-col justify-between items-center text-center flex-shrink-0"
         style="background: linear-gradient(180deg, #0D4D66 0%, #083347 100%);">
      <div class="h-4"></div>
      <div class="flex flex-col items-center gap-6 my-auto">
        <div class="w-32 h-32 lg:w-40 lg:h-40 shadow-2xl rounded-[32px] bg-[#0891B2]/20 border border-[#0891B2]/30 flex justify-center items-center backdrop-blur-sm"
             style="box-shadow: 0px 12px 24px rgba(8, 145, 178, 0.25);">
          <span class="text-[#0891B2] text-6xl lg:text-8xl font-outfit font-black select-none">S</span>
        </div>
        <div class="flex flex-col gap-1.5">
          <h1 class="text-white text-3xl lg:text-4xl font-outfit font-extrabold tracking-tight">SIMAVIS</h1>
          <p class="text-[#67E8F9] text-xs font-medium uppercase tracking-wider">
            Sistem Manajemen Informasi Studio Foto
          </p>
        </div>
      </div>
      <div class="text-white text-xs lg:text-sm font-semibold">
        Vibes Studio Foto Jatibarang
      </div>
    </div>

    <!-- KOLOM KANAN (FORM LOGIN / AUTO-REGISTER) -->
    <div class="flex-1 h-full p-6 lg:p-12 flex flex-col justify-between items-center relative bg-cover bg-center overflow-hidden"
         style="background-image: linear-gradient(0deg, rgba(255, 255, 255, 0.85) 0%, rgba(255, 255, 255, 0.85) 100%), url('https://placehold.co/820x768');">
      <div class="h-2"></div>

      <!-- Glassmorphism Form Card -->
      <div class="w-full max-w-[460px] p-8 sm:p-10 bg-white/70 backdrop-blur-md shadow-xl rounded-3xl border border-white/60 flex flex-col gap-6 my-auto">
        
        <div class="flex flex-col gap-2">
          <h2 class="text-[#0C4A6E] text-3xl font-outfit font-extrabold">Selamat Datang!</h2>
          <p class="text-[#64748B] text-sm font-normal leading-relaxed">
            Masukkan data Anda. Jika belum terdaftar, akun akan dibuatkan otomatis.
          </p>
        </div>

        <?php if (!empty($error)) { ?>
          <div class="p-3 bg-red-100 border border-red-400 text-red-700 text-xs rounded-xl">
            <?= $error; ?>
          </div>
        <?php } ?>

        <form class="flex flex-col gap-4" action="" method="POST">
          
          <div class="flex flex-col gap-1.5">
            <label class="text-[#0F172A] text-xs font-semibold">Nama Lengkap</label>
            <input type="text" 
                   name="nama" 
                   placeholder="Contoh: Nadia Putri" 
                   class="w-full h-11 px-4 bg-[#F8FAFC] rounded-xl outline outline-1 outline-[#E2E8F0] -outline-offset-1 text-[#0F172A] text-sm font-normal focus:outline-[#0891B2] focus:bg-white transition-all"
                   required />
          </div>

          <div class="flex flex-col gap-1.5">
            <label class="text-[#0F172A] text-xs font-semibold">Alamat Email</label>
            <input type="email" 
                   name="email" 
                   placeholder="email@domain.com" 
                   class="w-full h-11 px-4 bg-[#F8FAFC] rounded-xl outline outline-1 outline-[#E2E8F0] -outline-offset-1 text-[#0F172A] text-sm font-normal focus:outline-[#0891B2] focus:bg-white transition-all"
                   required />
          </div>

          <div class="flex flex-col gap-1.5">
            <label class="text-[#0F172A] text-xs font-semibold">Nomor WhatsApp</label>
            <input type="text" 
                   name="no_hp" 
                   placeholder="081234567890" 
                   class="w-full h-11 px-4 bg-[#F8FAFC] rounded-xl outline outline-1 outline-[#E2E8F0] -outline-offset-1 text-[#0F172A] text-sm font-normal focus:outline-[#0891B2] focus:bg-white transition-all"
                   required />
          </div>

          <button type="submit" 
                  class="w-full py-3 bg-[#0891B2] hover:bg-[#06748f] transition-all text-white text-sm font-bold rounded-xl shadow-lg shadow-[#0891B2]/20 active:scale-[0.99] mt-2">
            Masuk / Lanjutkan ke Riwayat
          </button>

        </form>

      </div>

      <div class="flex items-center gap-2 text-[#64748B] text-xs font-normal">
        <span>v2.0.1</span>
        <span class="w-1 h-1 bg-[#64748B]/50 rounded-full"></span>
        <span>Vibes Studio Foto Jatibarang</span>
      </div>

    </div>

  </div>

</body>
</html>
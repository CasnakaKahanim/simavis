<?php
session_start();
include '../koneksi.php';

// Proses Logout otomatis jika mengakses login.php dengan parameter ?logout=1 atau langsung reset sesi lama
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: login.php");
    exit;
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = mysqli_real_escape_string($conn, trim($_POST['username']));
    $password = $_POST['password'];

    if (!empty($username) && !empty($password)) {
        // Cek ke tabel staf atau admin
        $query = mysqli_query($conn, "SELECT * FROM staf WHERE username = '$username' OR email = '$username' LIMIT 1");
        if (!$query || mysqli_num_rows($query) === 0) {
            $query = mysqli_query($conn, "SELECT * FROM admin WHERE username = '$username' LIMIT 1");
        }

        if ($query && $staf = mysqli_fetch_assoc($query)) {
            if (password_verify($password, $staf['password']) || $password === $staf['password']) {
                $_SESSION['staf_logged_in'] = true;
                $_SESSION['id_staf']        = $staf['id'];
                $_SESSION['nama_staf']      = $staf['nama'] ?? $staf['nama_staf'] ?? 'Admin Utama';
                $_SESSION['role']           = $staf['role'] ?? 'Admin Utama';

                header("Location: dashboard.php");
                exit;
            } else {
                $error = "Kata sandi staf tidak sesuai.";
            }
        } else {
            $error = "Akun staf tidak ditemukan.";
        }
    } else {
        $error = "Harap masukkan username dan kata sandi.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login Staf - SIMAVIS Vibes Studio Foto</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>
    body { font-family: 'Instrument Sans', sans-serif; }
  </style>
</head>
<body class="bg-[#F8FAFC] min-h-screen flex items-center justify-center p-4"
      style="background: linear-gradient(135deg, #0F597A 0%, #0A4061 55%, #082E4D 100%);">

  <div class="w-full max-w-[420px] bg-white rounded-3xl shadow-2xl p-8 flex flex-col gap-6 border border-white/20">
    
    <!-- Header Logo -->
    <div class="flex flex-col items-center text-center gap-2">
      <img src="../user/asset/logo.jpeg" alt="Logo SIMAVIS" class="w-14 h-14 rounded-2xl object-cover shadow-sm border border-gray-100" />
      <div class="mt-1">
        <div class="text-[#0891B2] text-[11px] font-bold uppercase tracking-widest">Portal Manajemen Studio</div>
        <h1 class="text-[#2B2544] text-2xl font-bold mt-0.5">Login Staf SIMAVIS</h1>
        <p class="text-[#6C6287] text-xs mt-1">Masuk untuk mengelola pesanan, jadwal sesi, dan galeri foto.</p>
      </div>
    </div>

    <!-- Error Message -->
    <?php if (!empty($error)): ?>
      <div class="p-3.5 bg-rose-50 border border-rose-200 text-rose-600 text-xs rounded-xl font-medium flex items-center gap-2">
        <i class="fa-solid fa-circle-exclamation shrink-0"></i>
        <span><?= htmlspecialchars($error); ?></span>
      </div>
    <?php endif; ?>

    <!-- Form Login -->
    <form action="" method="POST" class="flex flex-col gap-4">
      <div class="flex flex-col gap-1.5">
        <label class="text-xs font-semibold text-[#2B2544]">Username atau Email</label>
        <div class="relative">
          <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-[#6C6287]">
            <i class="fa-solid fa-user-shield text-xs"></i>
          </span>
          <input type="text" name="username" required placeholder="Masukkan username staf"
                 class="w-full h-11 pl-10 pr-4 bg-gray-50 border border-gray-200 rounded-xl text-xs text-[#2B2544] focus:outline-none focus:border-[#0891B2] transition-colors" />
        </div>
      </div>

      <div class="flex flex-col gap-1.5">
        <label class="text-xs font-semibold text-[#2B2544]">Kata Sandi</label>
        <div class="relative">
          <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-[#6C6287]">
            <i class="fa-solid fa-lock text-xs"></i>
          </span>
          <input type="password" id="stafPass" name="password" required placeholder="••••••••"
                 class="w-full h-11 pl-10 pr-10 bg-gray-50 border border-gray-200 rounded-xl text-xs text-[#2B2544] focus:outline-none focus:border-[#0891B2] transition-colors" />
          <button type="button" onclick="toggleStafPass()" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-[#6C6287] hover:text-[#2B2544]">
            <i id="stafEye" class="fa-regular fa-eye text-xs"></i>
          </button>
        </div>
      </div>

      <button type="submit" class="w-full h-11 bg-[#0891B2] hover:bg-[#06748f] text-white font-bold text-xs rounded-xl shadow-md transition-colors flex items-center justify-center gap-2 mt-1">
        <span>Masuk ke Dashboard</span>
        <i class="fa-solid fa-arrow-right text-[10px]"></i>
      </button>
    </form>

    <div class="text-center pt-2 border-t border-gray-100">
      <a href="../user/web_informasi.php" class="text-xs font-semibold text-[#0891B2] hover:underline flex items-center justify-center gap-1.5">
        <i class="fa-solid fa-globe text-[11px]"></i>
        <span>Ke Website Pelanggan</span>
      </a>
    </div>

  </div>

  <script>
    function toggleStafPass() {
      const input = document.getElementById('stafPass');
      const icon = document.getElementById('stafEye');
      if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
      } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
      }
    }
  </script>
</body>
</html>
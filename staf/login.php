<?php
session_start();
// Hubungkan ke file koneksi di luar folder staf (root SIMAVIS)
include '../koneksi.php';

$pesan_error = '';

// Proses saat form login disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (!empty($username) && !empty($password)) {
        // Gunakan prepared statement untuk keamanan ekstra terhadap SQL Injection
        // Sesuaikan nama kolom tabel database Anda jika berbeda (misal: 'username', 'password', 'nama')
        $stmt = mysqli_prepare($conn, "SELECT * FROM staf WHERE username = ? OR email = ? LIMIT 1");
        mysqli_stmt_bind_param($stmt, "ss", $username, $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($result && mysqli_num_rows($result) > 0) {
            $staf = mysqli_fetch_assoc($result);

            // Verifikasi password (mendukung password_hash atau plaintext jika belum dienkripsi)
            $db_password = $staf['password'] ?? '';
            $password_cocok = false;

            if (password_verify($password, $db_password) || $password === $db_password) {
                $password_cocok = true;
            }

            if ($password_cocok) {
                // Set sesi login staf yang sah
                $_SESSION['staf_logged_in'] = true;
                $_SESSION['id_staf'] = $staf['id'] ?? $staf['id_staf'] ?? 1;
                $_SESSION['nama_staf'] = $staf['nama'] ?? $staf['nama_lengkap'] ?? 'Admin Utama';
                $_SESSION['username'] = $staf['username'];

                // Berhasil login, arahkan ke dashboard.php
                header("Location: dashboard.php");
                exit;
            } else {
                $pesan_error = "Kata sandi yang Anda masukkan salah!";
            }
        } else {
            $pesan_error = "Akun staf dengan username/email tersebut tidak terdaftar!";
        }
    } else {
        $pesan_error = "Harap masukkan username dan kata sandi!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SIMAVIS - Login Staf & Admin</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>
    body { font-family: 'Instrument Sans', sans-serif; }
  </style>
</head>
<body class="bg-[#0F597A] flex items-center justify-center h-screen w-screen overflow-hidden"
      style="background: linear-gradient(180deg, #0F597A 0%, #0A4061 60%, #082E4D 100%);">

  <div class="w-full max-w-md p-8 bg-white rounded-3xl shadow-2xl flex flex-col gap-6 mx-4 border border-white/20">
    
    <!-- Logo & Title -->
    <div class="flex flex-col items-center text-center gap-2">
      <div class="w-14 h-14 bg-[#0891B2] rounded-2xl flex items-center justify-center text-white text-2xl font-bold shadow-lg shadow-[#0891B2]/30">
        S
      </div>
      <h1 class="text-[#2B2544] text-2xl font-bold">Login Staf SIMAVIS</h1>
      <p class="text-[#6C6287] text-xs">Vibes Studio Foto Jatibarang</p>
    </div>

    <!-- Notifikasi Error Jika Gagal Login -->
    <?php if (!empty($pesan_error)) : ?>
      <div class="p-3 bg-red-50 border border-red-200 text-red-700 text-xs rounded-xl font-medium text-center">
        ⚠️ <?= htmlspecialchars($pesan_error); ?>
      </div>
    <?php endif; ?>

    <!-- Form Login -->
    <form action="" method="POST" class="flex flex-col gap-4">
      <div class="flex flex-col gap-1.5">
        <label class="text-[#2B2544] text-xs font-semibold">Username atau Email Staf</label>
        <input type="text" name="username" required placeholder="Masukkan username/email..." 
               class="w-full px-4 py-3 bg-[#F8FAFC] border border-[#E2E8F0] rounded-xl text-xs text-[#2B2544] focus:outline-none focus:border-[#0891B2] transition-colors" />
      </div>

      <div class="flex flex-col gap-1.5">
        <label class="text-[#2B2544] text-xs font-semibold">Kata Sandi (Password)</label>
        <input type="password" name="password" required placeholder="••••••••" 
               class="w-full px-4 py-3 bg-[#F8FAFC] border border-[#E2E8F0] rounded-xl text-xs text-[#2B2544] focus:outline-none focus:border-[#0891B2] transition-colors" />
      </div>

      <button type="submit" class="w-full py-3.5 bg-[#0891B2] hover:bg-[#06748f] transition-colors text-white font-bold text-xs rounded-xl shadow-md shadow-[#0891B2]/30 mt-2">
        Masuk ke Dashboard →
      </button>
    </form>

    <div class="text-center pt-2 border-t border-gray-100">
      <a href="Start2.php" class="text-[#6C6287] hover:text-[#0891B2] text-xs font-medium transition-colors">
        ← Kembali ke Halaman Utama
      </a>
    </div>

  </div>

</body>
</html>
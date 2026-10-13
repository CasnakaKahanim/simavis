<?php
// Konfigurasi Database MySQL
$host = "localhost";
$user = "root";
$pass = "";
$db   = "simavis";

// Membuat koneksi menggunakan MySQLi
$conn = mysqli_connect($host, $user, $pass, $db);

// Cek apakah koneksi berhasil atau gagal
if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

// Set charset agar karakter khusus (seperti huruf beraksen atau emoji) terbaca dengan baik
mysqli_set_charset($conn, "utf8");
?>
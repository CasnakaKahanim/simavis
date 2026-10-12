<?php
$host = "localhost";
$user = "root";     // Default XAMPP
$pass = "";         // Default XAMPP kosong
$db   = "db_simavis";

$koneksi = mysqli_connect($host, $user, $pass, $db);

if (!$koneksi) {
    die("Koneksi Database Gagal: " . mysqli_connect_error());
}
?>
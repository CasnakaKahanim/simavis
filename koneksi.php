<?php
// ==========================================
// 1. KONFIGURASI DATABASE MYSQL
// ==========================================
$host = "localhost";
$user = "root";
$pass = "";
$db   = "simavis";

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

mysqli_set_charset($conn, "utf8");

// ==========================================
// 2. KONFIGURASI MIDTRANS PAYMENT GATEWAY (SANDBOX)
// ==========================================
define('MIDTRANS_CLIENT_KEY', 'Mid-client-m-wjBGGMAiljo-f');
define('MIDTRANS_SERVER_KEY', 'Mid-server-yQnk_8PalXc4Uj98Gw-qsxxG');

// WAJIB FALSE karena menggunakan Dashboard & Key Sandbox
define('MIDTRANS_IS_PRODUCTION', false); 

// Endpoint URL API Midtrans Snap JS (Sandbox)
define('MIDTRANS_SNAP_URL', MIDTRANS_IS_PRODUCTION 
    ? 'https://app.midtrans.com/snap/snap.js' 
    : 'https://app.sandbox.midtrans.com/snap/snap.js');
?>
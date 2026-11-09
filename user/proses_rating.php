<?php
session_start();
include '../koneksi.php'; // Sesuaikan path koneksi jika berbeda

// Pastikan pelanggan sudah login
if (!isset($_SESSION['id_pelanggan'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil data dari form modal rating
    $id_booking   = intval($_POST['id_booking']);
    $rating       = intval($_POST['rating']);
    $ulasan       = mysqli_real_escape_string($conn, $_POST['ulasan']);
    $id_pelanggan = $_SESSION['id_pelanggan'];

    // Validasi sederhana
    if ($id_booking <= 0 || $rating < 1 || $rating > 5) {
        header("Location: akun.php?status=error_invalid");
        exit;
    }

    // Update data rating dan ulasan pada tabel booking milik pelanggan yang bersangkutan
    $query = "UPDATE booking SET rating = $rating, ulasan = '$ulasan' WHERE id = $id_booking AND id_pelanggan = $id_pelanggan";
    
    if (mysqli_query($conn, $query)) {
        // Berhasil disimpan, kembali ke halaman akun dengan status sukses
        header("Location: akun.php?status=success_rated");
        exit;
    } else {
        // Tangani jika terjadi error pada kueri database
        echo "Gagal menyimpan rating: " . mysqli_error($conn);
    }
} else {
    // Jika diakses secara langsung tanpa metode POST, alihkan kembali ke halaman akun
    header("Location: akun.php");
    exit;
}
?>
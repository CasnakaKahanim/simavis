<?php
session_start();
include '../koneksi.php';

if (!isset($_SESSION['id_pelanggan'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_booking   = intval($_POST['id_booking'] ?? 0);
    $rating       = intval($_POST['rating'] ?? 0);
    $ulasan       = mysqli_real_escape_string($conn, $_POST['ulasan'] ?? '');
    $id_pelanggan = $_SESSION['id_pelanggan'];

    if ($rating < 1 || $rating > 5) {
        header("Location: akun.php?status=error_invalid");
        exit;
    }

    // Jika id_booking tidak ada (0), ambil booking terakhir milik pelanggan ini yang belum dinilai
    if ($id_booking <= 0) {
        $q_cek = mysqli_query($conn, "SELECT id FROM booking WHERE id_pelanggan = $id_pelanggan ORDER BY id DESC LIMIT 1");
        if ($q_cek && mysqli_num_rows($q_cek) > 0) {
            $data_b = mysqli_fetch_assoc($q_cek);
            $id_booking = $data_b['id'];
        }
    }

    if ($id_booking > 0) {
        $query = "UPDATE booking SET rating = $rating, ulasan = '$ulasan' WHERE id = $id_booking AND id_pelanggan = $id_pelanggan";
        if (mysqli_query($conn, $query)) {
            header("Location: akun.php?status=success_rated");
            exit;
        }
    }

    header("Location: akun.php?status=error_invalid");
    exit;
} else {
    header("Location: akun.php");
    exit;
}
?>
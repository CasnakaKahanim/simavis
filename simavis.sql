-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 10, 2026 at 04:31 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `simavis`
--

-- --------------------------------------------------------

--
-- Table structure for table `album_foto`
--

CREATE TABLE `album_foto` (
  `id` int(11) NOT NULL,
  `id_pelanggan` int(11) DEFAULT 1,
  `nama_album` varchar(100) NOT NULL,
  `tanggal_foto` varchar(50) NOT NULL,
  `jumlah_foto` varchar(50) NOT NULL,
  `gambar_url` varchar(255) NOT NULL,
  `status_album` varchar(50) DEFAULT 'Siap diunduh'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `album_foto`
--

INSERT INTO `album_foto` (`id`, `id_pelanggan`, `nama_album`, `tanggal_foto`, `jumlah_foto`, `gambar_url`, `status_album`) VALUES
(1, 1, 'Personal Portrait', '20 Sep 2026', '5 foto edit', 'https://placehold.co/378x190', 'Siap diunduh'),
(2, 1, 'Family Moments', '2 Agu 2026', '15 foto edit', 'https://placehold.co/378x190', 'Siap diunduh'),
(3, 2, 'Album Personal Portrait', '14 Oct 2026', '1 foto edit', '../uploads/SIMAVIS_1791944253_199.webp', 'Siap diunduh'),
(4, 2, 'Album Personal Portrait', '14 Oct 2026', '1 foto edit', '../uploads/SIMAVIS_1791946773_135.jpeg', 'Siap diunduh'),
(5, 2, 'Album Personal Portrait', '19 Oct 2026', '1 foto edit', '../uploads/SIMAVIS_1792375270_941.jpg', 'Siap diunduh'),
(6, 1, 'Album Extra Person', '19 Oct 2026', '1 foto edit', '../uploads/SIMAVIS_1792375488_153.jpg', 'Siap diunduh'),
(7, 2, 'Album Personal Portrait', '19 Oct 2026', '1 foto edit', '../uploads/SIMAVIS_1792375595_910.jpg', 'Siap diunduh'),
(8, 3, 'Album Extra Person', '20 Oct 2026', '1 foto edit', '../uploads/SIMAVIS_1792472510_968.png', 'Siap diunduh'),
(9, 3, 'Album Extra Person', '20 Oct 2026', '1 foto edit', '../uploads/SIMAVIS_1792472528_492.png', 'Siap diunduh'),
(10, 2, 'Album Extra Person', '20 Oct 2026', '1 foto edit', '../uploads/SIMAVIS_1792472638_870.webp', 'Siap diunduh'),
(11, 4, 'Album Family Moments', '20 Oct 2026', '1 foto edit', '../uploads/SIMAVIS_1792473204_813.jpeg', 'Siap diunduh'),
(12, 4, 'Album Family Moments', '20 Oct 2026', '1 foto edit', '../uploads/SIMAVIS_1792473326_750.jpeg', 'Siap diunduh'),
(13, 2, 'Album Extra Person', '27 Oct 2026', '1 foto edit', '../uploads/SIMAVIS_1793076695_371.png', 'Siap diunduh');

-- --------------------------------------------------------

--
-- Table structure for table `booking`
--

CREATE TABLE `booking` (
  `id` int(11) NOT NULL,
  `id_pelanggan` int(11) DEFAULT 1,
  `nama_lengkap` varchar(100) NOT NULL,
  `no_whatsapp` varchar(20) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `jumlah_peserta` varchar(20) NOT NULL,
  `nama_paket` varchar(100) NOT NULL,
  `harga_paket` int(11) NOT NULL,
  `biaya_tambahan` int(11) DEFAULT 0,
  `total_harga` int(11) NOT NULL,
  `tanggal_sesi` date NOT NULL,
  `jam_sesi` varchar(50) NOT NULL,
  `kode_booking` varchar(50) NOT NULL,
  `status_pembayaran` varchar(50) DEFAULT 'Lunas',
  `status_sesi` enum('Mendatang','Selesai','Batal') DEFAULT 'Mendatang',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `rating` int(11) DEFAULT 0,
  `ulasan` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `booking`
--

INSERT INTO `booking` (`id`, `id_pelanggan`, `nama_lengkap`, `no_whatsapp`, `email`, `jumlah_peserta`, `nama_paket`, `harga_paket`, `biaya_tambahan`, `total_harga`, `tanggal_sesi`, `jam_sesi`, `kode_booking`, `status_pembayaran`, `status_sesi`, `created_at`, `rating`, `ulasan`) VALUES
(1, 1, 'Nadia Putri', '081234567890', 'nadia.putri@email.com', '2 orang', 'Couple & Friends', 650000, 100000, 750000, '2026-10-17', '13.00–14.00 WIB', 'SMV-261017-024', 'Lunas', 'Mendatang', '2026-10-13 05:02:31', 0, NULL),
(2, 1, 'Nadia Putri', '081234567890', 'nadia.putri@email.com', '1 orang', 'Personal Portrait', 350000, 0, 350000, '2026-09-20', '10.00 WIB', 'SMV-260920-018', 'Lunas', 'Selesai', '2026-10-13 05:02:31', 0, NULL),
(3, 1, 'Nadia Putri', '081234567890', 'nadia.putri@email.com', '5 orang', 'Family Moments', 950000, 0, 950000, '2026-08-02', '14.30 WIB', 'SMV-260802-009', 'Lunas', 'Selesai', '2026-10-13 05:02:31', 0, NULL),
(4, 1, 'Nadia Putri', '081234567890', 'nadia.putri@email.com', '2 orang', 'Couple & Friends', 650000, 100000, 750000, '2026-10-17', '13.00–14.00 WIB', 'SMV-261013-239', 'Lunas', 'Mendatang', '2026-10-13 05:07:17', 0, NULL),
(5, 1, 'Rifki', '081234567890', 'RifkiMaulana@gmail.com', '1 orang', 'Couple & Friends', 650000, 100000, 750000, '2026-10-17', '13.00–14.00 WIB', 'SMV-261013-883', 'Lunas', 'Mendatang', '2026-10-13 05:08:50', 0, NULL),
(6, 1, 'Rifki', '081234567890', 'RifkiMaulana@gmail.com', '1 orang', 'Couple & Friends', 650000, 100000, 750000, '2026-10-17', '13.00–14.00 WIB', 'SMV-261013-993', 'Lunas', 'Mendatang', '2026-10-13 05:09:04', 0, NULL),
(7, 1, 'Nadia Putri', '081234567890', 'nadia.putri@email.com', '2 orang', 'Couple & Friends', 650000, 100000, 750000, '2026-10-17', '13.00–14.00 WIB', 'SMV-261013-947', 'Lunas', 'Mendatang', '2026-10-13 05:26:23', 0, NULL),
(8, 2, 'Rifki Maulana', '083150719977', 'rm9320791@gmail.com', '1 orang', 'Personal Portrait', 350000, 0, 350000, '2026-06-11', '12.00', 'SMV-261013-116', 'Lunas', 'Mendatang', '2026-10-13 05:40:13', 0, NULL),
(9, 2, 'Rifki Maulana', '083150719977', 'rm9320791@gmail.com', '1 orang', 'Personal Portrait', 350000, 0, 350000, '2026-10-14', '14.30 - 14.45', 'SMV-261014-145', 'Lunas', 'Selesai', '2026-10-14 01:30:47', 0, NULL),
(10, 2, 'Naka', '083150719977', 'rm9320791@gmail.com', '2 orang', 'Extra Person', 35000, 0, 35000, '2026-10-14', '09.00 - 09.15', 'SMV-261014-744', 'Lunas', 'Mendatang', '2026-10-14 05:55:56', 0, NULL),
(11, 2, 'Maul', '083150719977', 'rm9320791@gmail.com', '1 orang', 'Extra Person', 35000, 0, 35000, '2026-10-15', '17.30 - 17.45', 'SMV-261015-975', 'Lunas', 'Mendatang', '2026-10-15 13:53:08', 0, NULL),
(12, 3, 'naka', '083150719977', 'naka@gmail.com', '2 orang', 'Extra Person', 35000, 0, 35000, '2026-10-15', '09.00 - 09.15', 'SMV-261015-888', 'Lunas', 'Selesai', '2026-10-15 14:10:05', 0, NULL),
(13, 1, 'Ervan Dhika', '081234567890', 'nadia.putri@email.com', '2 orang', 'Extra Person', 0, 0, 35000, '2026-10-15', '16:26 WIB', 'SIM-261015-401', 'Lunas', 'Mendatang', '2026-10-15 14:26:06', 0, NULL),
(14, 1, 'Nadia Putri', '081234567890', 'nadia.putri@email.com', '2 orang', 'Couple & Friends', 0, 0, 50000, '2026-10-15', '16:43 WIB', 'SIM-261015-368', 'Lunas', 'Mendatang', '2026-10-15 14:43:49', 0, NULL),
(15, 3, 'naka', '083150719977', 'naka@gmail.com', '1 orang', 'Extra Person', 35000, 0, 35000, '2026-10-15', '10.30 - 10.45', 'SMV-261016-224', 'Lunas', 'Mendatang', '2026-10-16 01:39:55', 0, NULL),
(16, 3, 'naka', '083150719977', 'naka@gmail.com', '1 orang', 'Couple & Friends', 50000, 0, 50000, '2026-10-16', '12.00 - 12.15', 'SMV-261016-168', 'Lunas', 'Mendatang', '2026-10-16 01:41:50', 0, NULL),
(17, 1, 'Rifki Maulana', '081234567890', 'nadia.putri@email.com', '1 orang', 'Extra Person', 0, 0, 35000, '2026-10-16', '09:00 WIB', 'SIM-261016-635', 'Lunas', 'Selesai', '2026-10-16 02:06:28', 0, NULL),
(18, 1, 'Casnaka', '081234567890', 'nadia.putri@email.com', '2 orang', 'Extra Person', 0, 0, 1, '2026-10-20', '16:00 WIB', 'SIM-261020-764', 'Lunas', 'Mendatang', '2026-10-20 04:52:56', 0, NULL),
(19, 2, 'Rifki Maulana', '083150719977', 'rm9320791@gmail.com', '1 orang', 'Extra Person', 1, 0, 1, '2026-10-20', '13.00 - 13.15', 'SMV-261020-381', 'Belum Bayar (Bayar di Studio)', 'Selesai', '2026-10-20 04:56:06', 0, NULL),
(20, 4, 'Nakka', '083115995105', 'casnakakahanim@gmail.com', '2', 'Family Moments', 950000, 0, 950000, '2026-10-20', '12.00 - 12.15', 'SMV-261020-857', 'Belum Bayar (Bayar di Studio)', 'Selesai', '2026-10-20 05:09:55', 0, NULL),
(21, 1, 'Nadia Putri', '081234567890', 'nadia.putri@email.com', '2 orang', 'Couple & Friends', 0, 0, 50000, '2026-10-20', '09:00 WIB', 'SIM-261020-441', 'Lunas', 'Mendatang', '2026-10-20 05:12:46', 0, NULL),
(22, 4, 'Nakka', '083115995105', 'casnakakahanim@gmail.com', '1', 'Family Moments', 950000, 0, 950000, '2026-10-20', '14.30 - 14.45', 'SMV-261020-628', 'Belum Bayar (Bayar di Studio)', 'Selesai', '2026-10-20 05:14:26', 0, NULL),
(23, 1, 'Nadia Putri', '081234567890', 'nadia.putri@email.com', '2 orang', 'Couple & Friends', 0, 0, 50000, '2026-10-20', '10:00 WIB', 'SIM-261020-504', 'Lunas', 'Mendatang', '2026-10-20 06:44:25', 0, NULL),
(24, 1, 'Nadia Putri', '081234567890', 'nadia.putri@email.com', '2 orang', 'Couple & Friends', 0, 0, 50000, '2026-10-20', '11:00 WIB', 'SIM-261020-478', 'Lunas', 'Mendatang', '2026-10-20 06:45:20', 0, NULL),
(25, 1, 'Nadia Putri', '081234567890', 'nadia.putri@email.com', '2 orang', 'Couple & Friends', 0, 0, 50000, '2026-10-20', '20:00 WIB', 'SIM-261020-283', 'Lunas', 'Mendatang', '2026-10-20 06:51:33', 0, NULL),
(26, 1, 'Nadia Putri', '081234567890', 'nadia.putri@email.com', '2 orang', 'Couple & Friends', 0, 0, 50000, '2026-10-20', '13:00 WIB', 'SIM-261020-900', 'Lunas', 'Mendatang', '2026-10-20 06:53:35', 0, NULL),
(27, 1, 'Nadia Putri', '081234567890', 'nadia.putri@email.com', '2 orang', 'Couple & Friends', 0, 0, 50000, '2026-10-20', '14:00 WIB', 'SIM-261020-714', 'Lunas', 'Mendatang', '2026-10-20 06:56:21', 0, NULL),
(28, 1, 'Nadia Putri', '081234567890', 'nadia.putri@email.com', '2 orang', 'Couple & Friends', 0, 0, 50000, '2026-10-20', '15:00 WIB', 'SIM-261020-156', 'Lunas', 'Mendatang', '2026-10-20 06:59:39', 0, NULL),
(29, 1, 'Nadia Putri', '081234567890', 'nadia.putri@email.com', '2 orang', 'Couple & Friends', 0, 0, 50000, '2026-10-20', '17:00 WIB', 'SIM-261020-757', 'Lunas', 'Mendatang', '2026-10-20 07:12:01', 0, NULL),
(30, 4, 'Nakka', '083115995105', 'casnakakahanim@gmail.com', '3', 'Couple & Friends', 50000, 0, 50000, '2026-10-20', '09.00 - 09.15', 'SMV-261020-486', 'Belum Bayar (Bayar di Studio)', 'Mendatang', '2026-10-20 08:12:58', 0, NULL),
(31, 2, 'Rifki Maulana', '083150719977', 'rm9320791@gmail.com', '1', 'Extra Person', 25000, 0, 25000, '2026-10-25', '09.00 - 09.15', 'SMV-261025-245', 'Belum Bayar (Bayar di Studio)', 'Mendatang', '2026-10-25 15:34:52', 0, NULL),
(32, 2, 'Rifki Maulana', '083150719977', 'rm9320791@gmail.com', '1', 'Extra Person', 25000, 0, 25000, '2026-10-27', '14.30 - 14.45', 'SMV-261027-310', 'Belum Bayar (Bayar di Studio)', 'Selesai', '2026-10-27 04:51:06', 0, NULL),
(33, 2, 'Rifki Maulana', '083150719977', 'rm9320791@gmail.com', '1', 'Extra Person', 15000, 0, 15000, '2026-10-30', '09.00 - 09.15', 'SMV-261030-656', 'Belum Bayar (Bayar di Studio)', 'Mendatang', '2026-10-30 02:43:28', 0, NULL),
(34, 2, 'Rifki Maulana', '083150719977', 'rm9320791@gmail.com', '1', 'Extra Person', 15000, 0, 15000, '2026-11-10', '10.30 - 10.45', 'SMV-261110-544', 'Belum Bayar (Bayar di Studio)', 'Mendatang', '2026-11-10 03:19:04', 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `paket_foto`
--

CREATE TABLE `paket_foto` (
  `id` int(11) NOT NULL,
  `nama_paket` varchar(100) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `harga` int(11) NOT NULL,
  `durasi` varchar(50) DEFAULT NULL,
  `kapasitas` varchar(50) DEFAULT NULL,
  `backdrop` varchar(50) DEFAULT NULL,
  `foto_edit` varchar(50) DEFAULT NULL,
  `is_populer` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `paket_foto`
--

INSERT INTO `paket_foto` (`id`, `nama_paket`, `deskripsi`, `harga`, `durasi`, `kapasitas`, `backdrop`, `foto_edit`, `is_populer`) VALUES
(1, 'Extra Person', 'Untuk profil profesional atau merayakan diri sendiri.', 15000, '7 menit', '1 orang', '3 pilihan backdrop', '10 foto', 0),
(2, 'Couple & Friends', 'Chemistry dan tawa yang layak diabadikan.', 30000, '10 menit', '2–4 orang', '2 pilihan backdrop', '10 foto edit resolusi tinggi', 1),
(3, 'Family Moments', 'Potret hangat untuk setiap generasi keluarga.', 100000, '40 menit', 'Hingga 6 orang', '3 pilihan backdrop', '15 foto edit + cetak 20 × 30 cm', 0);

-- --------------------------------------------------------

--
-- Table structure for table `pelanggan`
--

CREATE TABLE `pelanggan` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `no_hp` varchar(20) NOT NULL,
  `inisial` varchar(5) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pelanggan`
--

INSERT INTO `pelanggan` (`id`, `nama`, `email`, `no_hp`, `inisial`) VALUES
(1, 'Nadia Anggraini', 'nadia.anggraini@gmail.com', '+62 812 3456 7890', 'NA'),
(2, 'Rifki Maulana', 'rm9320791@gmail.com', '083150719977', 'RM'),
(3, 'naka', 'naka@gmail.com', '083150719977', 'N'),
(4, 'Nakka', 'casnakakahanim@gmail.com', '083115995105', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `staf`
--

CREATE TABLE `staf` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `staf`
--

INSERT INTO `staf` (`id`, `nama`, `username`, `email`, `password`, `created_at`) VALUES
(1, 'Rifki Maulana', 'admin', 'rm9320791@gmail.com\r\n', '123', '2026-10-14 02:45:43'),
(2, 'Rifki Maulana', 'owner', 'owner@gmail.com', '123', '2026-10-25 15:27:19');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `email`, `password`, `nama_lengkap`) VALUES
(1, 'admin@vibesstudio.com', '123456789', 'Admin Vibes Studio');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `album_foto`
--
ALTER TABLE `album_foto`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `booking`
--
ALTER TABLE `booking`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `paket_foto`
--
ALTER TABLE `paket_foto`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pelanggan`
--
ALTER TABLE `pelanggan`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `staf`
--
ALTER TABLE `staf`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `album_foto`
--
ALTER TABLE `album_foto`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `booking`
--
ALTER TABLE `booking`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `paket_foto`
--
ALTER TABLE `paket_foto`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `pelanggan`
--
ALTER TABLE `pelanggan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `staf`
--
ALTER TABLE `staf`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

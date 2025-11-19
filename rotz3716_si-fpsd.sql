-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Nov 19, 2025 at 12:43 PM
-- Server version: 10.11.6-MariaDB-cll-lve
-- PHP Version: 8.4.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `rotz3716_si-fpsd`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `level` enum('superadmin','admin') DEFAULT 'admin',
  `last_login` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id`, `username`, `password`, `nama_lengkap`, `level`, `last_login`) VALUES
(1, 'admin', '$2y$10$d82x09.FwY0Vw5YcFoxOXeOkwL6wSGYegNalF1dAZg8inF9Zhsze6', 'Administrator Sistem', 'superadmin', '2025-11-19 11:13:16');

-- --------------------------------------------------------

--
-- Table structure for table `surat`
--

CREATE TABLE `surat` (
  `id` int(11) NOT NULL,
  `nama_mahasiswa` varchar(100) DEFAULT NULL,
  `nim_mahasiswa` varchar(20) DEFAULT NULL,
  `email_mahasiswa` varchar(100) DEFAULT NULL,
  `jenjang` varchar(10) DEFAULT NULL,
  `semester` int(11) DEFAULT NULL,
  `program_studi` varchar(50) DEFAULT NULL,
  `alamat_lengkap_mahasiswa` text DEFAULT NULL,
  `tahun_akademik` varchar(20) DEFAULT NULL,
  `lama_masa_studi` varchar(20) DEFAULT NULL,
  `ipk` decimal(3,2) DEFAULT NULL,
  `alasan_keperluan` text DEFAULT NULL,
  `tanggal_bulan_tahun` date DEFAULT NULL,
  `status` enum('dalam proses','dapat diambil') DEFAULT 'dalam proses',
  `nomor_surat` varchar(50) DEFAULT NULL,
  `jenis_surat` enum('Surat Keterangan','Surat Pernyataan Masih Kuliah','Surat Keterangan/Rekomendasi') DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `surat`
--

INSERT INTO `surat` (`id`, `nama_mahasiswa`, `nim_mahasiswa`, `email_mahasiswa`, `jenjang`, `semester`, `program_studi`, `alamat_lengkap_mahasiswa`, `tahun_akademik`, `lama_masa_studi`, `ipk`, `alasan_keperluan`, `tanggal_bulan_tahun`, `status`, `nomor_surat`, `jenis_surat`, `created_at`, `updated_at`) VALUES
(6, 'Nathania kaminina mufidah', '2201485', 'nathania@gmail.com', 'S1', 7, 'pendidikan seni musik', 'jl.hj samali', '2022/2024', NULL, NULL, NULL, '2025-11-13', 'dalam proses', 'SPMK/2025/2903', 'Surat Pernyataan Masih Kuliah', '2025-11-13 07:08:14', '2025-11-13 07:08:14'),
(10, 'Nama Lengkap ', '2598348', 'emailkamu@gmail.com', 'S1', 2, 'Program Studi Kamu', 'Bandung', '2028/2030', NULL, 3.75, NULL, '2025-11-19', 'dapat diambil', 'SKR/2025/2252', 'Surat Keterangan/Rekomendasi', '2025-11-19 04:17:04', '2025-11-19 04:17:44'),
(11, 'Nama Lengkap ', '2598348', 'emailkamu@gmail.com', 'S1', 2, 'Program Studi Kamu', 'Bandung', '2028/2030', NULL, NULL, 'tes', '2025-11-19', 'dalam proses', 'SK/2025/5892', 'Surat Keterangan', '2025-11-19 05:01:01', '2025-11-19 05:01:01'),
(12, 'Nama Lengkap ', '2598348', 'emailkamu@gmail.com', 'S1', 2, 'Program Studi Kamu', 'Bandung', '2028/2030', NULL, NULL, NULL, '2025-11-19', 'dalam proses', 'SPMK/2025/7240', 'Surat Pernyataan Masih Kuliah', '2025-11-19 05:37:46', '2025-11-19 05:37:46');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `surat`
--
ALTER TABLE `surat`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `surat`
--
ALTER TABLE `surat`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

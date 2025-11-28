-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Nov 28, 2025 at 11:33 AM
-- Server version: 8.4.3
-- PHP Version: 8.3.26

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_penglolaan_uang`
--

-- --------------------------------------------------------

--
-- Table structure for table `budgets`
--

CREATE TABLE `budgets` (
  `id_anggaran` int UNSIGNED NOT NULL,
  `id_pengguna` int UNSIGNED NOT NULL,
  `id_kategori` int UNSIGNED NOT NULL,
  `jumlah_anggaran` decimal(18,2) NOT NULL,
  `bulan` int NOT NULL,
  `tahun` int NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `budgets`
--

INSERT INTO `budgets` (`id_anggaran`, `id_pengguna`, `id_kategori`, `jumlah_anggaran`, `bulan`, `tahun`, `created_at`, `updated_at`) VALUES
(8, 9, 2, 2500000.00, 11, 2025, '2025-11-21 11:32:59', '2025-11-21 11:34:00');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id_kategori` int UNSIGNED NOT NULL,
  `nama_kategori` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `tipe_kategori` enum('Manual','Dinamis') COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id_kategori`, `nama_kategori`, `tipe_kategori`) VALUES
(1, 'Makanan & Minuman', 'Manual'),
(2, 'Transportasi', 'Manual'),
(3, 'Tagihan & Utilitas', 'Manual'),
(4, 'Hiburan', 'Manual'),
(5, 'Gaji Bulanan', 'Manual'),
(6, 'Bonus', 'Manual'),
(7, 'Hadiah', 'Manual'),
(8, 'Bioskop', 'Dinamis'),
(9, 'makan', 'Dinamis'),
(10, 'DP rumah', 'Dinamis');

-- --------------------------------------------------------

--
-- Table structure for table `currencies`
--

CREATE TABLE `currencies` (
  `id_mata_uang` int UNSIGNED NOT NULL,
  `kode_mata_uang` varchar(5) COLLATE utf8mb4_general_ci NOT NULL,
  `nama_mata_uang` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `nilai_tukar` decimal(18,4) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `currencies`
--

INSERT INTO `currencies` (`id_mata_uang`, `kode_mata_uang`, `nama_mata_uang`, `nilai_tukar`) VALUES
(1, 'IDR', 'Rupiah Indonesia', 1.0000),
(2, 'USD', 'US Dollar', 15500.0000);

-- --------------------------------------------------------

--
-- Table structure for table `debts`
--

CREATE TABLE `debts` (
  `id_utang` int UNSIGNED NOT NULL,
  `id_pengguna` int UNSIGNED NOT NULL,
  `jumlah_utang` decimal(18,2) NOT NULL,
  `tanggal_tenggat` date NOT NULL,
  `status_utang` enum('Belum Dibayar','Dibayar Sebagian','Lunas') COLLATE utf8mb4_general_ci DEFAULT 'Belum Dibayar'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `debts`
--

INSERT INTO `debts` (`id_utang`, `id_pengguna`, `jumlah_utang`, `tanggal_tenggat`, `status_utang`) VALUES
(1, 9, 1500000.00, '2025-12-06', 'Belum Dibayar');

-- --------------------------------------------------------

--
-- Table structure for table `expenses`
--

CREATE TABLE `expenses` (
  `id_pengeluaran` int UNSIGNED NOT NULL,
  `id_pengguna` int UNSIGNED NOT NULL,
  `id_kategori` int UNSIGNED NOT NULL,
  `id_metode` int UNSIGNED NOT NULL,
  `id_mata_uang` int UNSIGNED NOT NULL,
  `jumlah` decimal(18,2) NOT NULL,
  `tanggal` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `financial_goals`
--

CREATE TABLE `financial_goals` (
  `id_tujuan` int UNSIGNED NOT NULL,
  `id_pengguna` int UNSIGNED NOT NULL,
  `nama_tujuan` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `total_target` decimal(18,2) NOT NULL,
  `tanggal_target` date NOT NULL,
  `status` enum('Tercapai','Belum Tercapai') COLLATE utf8mb4_general_ci DEFAULT 'Belum Tercapai'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `financial_goals`
--

INSERT INTO `financial_goals` (`id_tujuan`, `id_pengguna`, `nama_tujuan`, `total_target`, `tanggal_target`, `status`) VALUES
(4, 9, 'DP rumah', 50000000.00, '2025-11-28', 'Tercapai');

-- --------------------------------------------------------

--
-- Table structure for table `financial_predictions`
--

CREATE TABLE `financial_predictions` (
  `id_prediksi` int UNSIGNED NOT NULL,
  `id_pengguna` int UNSIGNED NOT NULL,
  `bulan_prediksi` int NOT NULL,
  `estimasi_pemasukan` decimal(18,2) DEFAULT NULL,
  `estimasi_pengeluaran` decimal(18,2) DEFAULT NULL,
  `prediksi_saldo_akhir` decimal(18,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `financial_predictions`
--

INSERT INTO `financial_predictions` (`id_prediksi`, `id_pengguna`, `bulan_prediksi`, `estimasi_pemasukan`, `estimasi_pengeluaran`, `prediksi_saldo_akhir`) VALUES
(3, 9, 12, 0.00, 0.00, 0.00);

-- --------------------------------------------------------

--
-- Table structure for table `incomes`
--

CREATE TABLE `incomes` (
  `id_pemasukan` int UNSIGNED NOT NULL,
  `id_pengguna` int UNSIGNED NOT NULL,
  `id_metode` int UNSIGNED NOT NULL,
  `id_mata_uang` int UNSIGNED NOT NULL,
  `jumlah` decimal(18,2) NOT NULL,
  `sumber` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `tanggal` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `incomes`
--

INSERT INTO `incomes` (`id_pemasukan`, `id_pengguna`, `id_metode`, `id_mata_uang`, `jumlah`, `sumber`, `tanggal`) VALUES
(5, 10, 3, 1, 12000.00, 'Trading', '2025-10-27'),
(6, 12, 3, 1, 5000000.00, 'Gaji', '2025-10-28');

-- --------------------------------------------------------

--
-- Table structure for table `payment_methods`
--

CREATE TABLE `payment_methods` (
  `id_metode` int UNSIGNED NOT NULL,
  `nama_metode` varchar(50) COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payment_methods`
--

INSERT INTO `payment_methods` (`id_metode`, `nama_metode`) VALUES
(1, 'Tunai'),
(2, 'Transfer Bank'),
(3, 'Kartu Kredit'),
(4, 'E-Wallet');

-- --------------------------------------------------------

--
-- Table structure for table `receivables`
--

CREATE TABLE `receivables` (
  `id_piutang` int UNSIGNED NOT NULL,
  `id_pengguna` int UNSIGNED NOT NULL,
  `jumlah_piutang` decimal(18,2) NOT NULL,
  `tanggal_tenggat` date NOT NULL,
  `status_piutang` enum('Belum Diterima','Diterima Sebagian','Lunas') COLLATE utf8mb4_general_ci DEFAULT 'Belum Diterima'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `social_user_relations`
--

CREATE TABLE `social_user_relations` (
  `id_relasi` int UNSIGNED NOT NULL,
  `id_pengguna1` int UNSIGNED NOT NULL,
  `id_pengguna2` int UNSIGNED NOT NULL,
  `status_relasi` enum('Pending','Accepted') COLLATE utf8mb4_general_ci DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `social_user_relations`
--

INSERT INTO `social_user_relations` (`id_relasi`, `id_pengguna1`, `id_pengguna2`, `status_relasi`) VALUES
(1, 11, 10, 'Pending');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id_pengguna` int UNSIGNED NOT NULL,
  `nama` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `kata_sandi` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `tanggal_daftar` date NOT NULL,
  `status_akun` enum('Aktif','Tidak Aktif') COLLATE utf8mb4_general_ci DEFAULT 'Aktif',
  `role` enum('user','admin') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'user'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id_pengguna`, `nama`, `email`, `kata_sandi`, `tanggal_daftar`, `status_akun`, `role`) VALUES
(9, 'ZAKY MPS', 'manggazaky@gmail.com', '$2y$10$tEjquFAEES6gqO1uxHK08ufMrBpjm0gdVJfPbIK.4a.2lVs8W4ntq', '2025-10-25', 'Aktif', 'user'),
(10, 'Timoty Ronald', 'Ronald@gmail.com', '$2y$10$RtGg.eZ4axo.tgmfoB7KKuRLKl0bDktdrYbVBkC7luDYEgNzAx832', '2025-10-27', 'Aktif', 'user'),
(11, 'Axel', 'Axel@gmail.com', '$2y$10$6xk4hb/Wve6XZiPMX1K1Buwo7qlLzlFSCVTOBQQ8V0bVunjqm8yZK', '2025-10-27', 'Aktif', 'user'),
(12, 'gevura', 'gevura@gmail.com', '$2y$10$MEjZ7NSMaSrBg2cZxnOJMu./UsgDsZKMAz0zypMa4udN/Pfjkwk4e', '2025-10-28', 'Aktif', 'user');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `budgets`
--
ALTER TABLE `budgets`
  ADD PRIMARY KEY (`id_anggaran`),
  ADD UNIQUE KEY `unique_budget` (`id_pengguna`,`id_kategori`,`bulan`,`tahun`),
  ADD KEY `id_pengguna` (`id_pengguna`),
  ADD KEY `id_kategori` (`id_kategori`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id_kategori`);

--
-- Indexes for table `currencies`
--
ALTER TABLE `currencies`
  ADD PRIMARY KEY (`id_mata_uang`),
  ADD UNIQUE KEY `kode_mata_uang` (`kode_mata_uang`);

--
-- Indexes for table `debts`
--
ALTER TABLE `debts`
  ADD PRIMARY KEY (`id_utang`),
  ADD KEY `id_pengguna` (`id_pengguna`);

--
-- Indexes for table `expenses`
--
ALTER TABLE `expenses`
  ADD PRIMARY KEY (`id_pengeluaran`),
  ADD KEY `id_kategori` (`id_kategori`),
  ADD KEY `id_metode` (`id_metode`),
  ADD KEY `id_mata_uang` (`id_mata_uang`),
  ADD KEY `idx_pengguna` (`id_pengguna`);

--
-- Indexes for table `financial_goals`
--
ALTER TABLE `financial_goals`
  ADD PRIMARY KEY (`id_tujuan`),
  ADD KEY `id_pengguna` (`id_pengguna`);

--
-- Indexes for table `financial_predictions`
--
ALTER TABLE `financial_predictions`
  ADD PRIMARY KEY (`id_prediksi`),
  ADD KEY `id_pengguna` (`id_pengguna`);

--
-- Indexes for table `incomes`
--
ALTER TABLE `incomes`
  ADD PRIMARY KEY (`id_pemasukan`),
  ADD KEY `id_pengguna` (`id_pengguna`),
  ADD KEY `id_metode` (`id_metode`),
  ADD KEY `id_mata_uang` (`id_mata_uang`);

--
-- Indexes for table `payment_methods`
--
ALTER TABLE `payment_methods`
  ADD PRIMARY KEY (`id_metode`);

--
-- Indexes for table `receivables`
--
ALTER TABLE `receivables`
  ADD PRIMARY KEY (`id_piutang`),
  ADD KEY `id_pengguna` (`id_pengguna`);

--
-- Indexes for table `social_user_relations`
--
ALTER TABLE `social_user_relations`
  ADD PRIMARY KEY (`id_relasi`),
  ADD UNIQUE KEY `id_pengguna1` (`id_pengguna1`,`id_pengguna2`),
  ADD KEY `id_pengguna2` (`id_pengguna2`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id_pengguna`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `budgets`
--
ALTER TABLE `budgets`
  MODIFY `id_anggaran` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id_kategori` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `currencies`
--
ALTER TABLE `currencies`
  MODIFY `id_mata_uang` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `debts`
--
ALTER TABLE `debts`
  MODIFY `id_utang` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `expenses`
--
ALTER TABLE `expenses`
  MODIFY `id_pengeluaran` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `financial_goals`
--
ALTER TABLE `financial_goals`
  MODIFY `id_tujuan` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `financial_predictions`
--
ALTER TABLE `financial_predictions`
  MODIFY `id_prediksi` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `incomes`
--
ALTER TABLE `incomes`
  MODIFY `id_pemasukan` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `payment_methods`
--
ALTER TABLE `payment_methods`
  MODIFY `id_metode` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `receivables`
--
ALTER TABLE `receivables`
  MODIFY `id_piutang` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `social_user_relations`
--
ALTER TABLE `social_user_relations`
  MODIFY `id_relasi` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id_pengguna` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `budgets`
--
ALTER TABLE `budgets`
  ADD CONSTRAINT `budgets_ibfk_1` FOREIGN KEY (`id_pengguna`) REFERENCES `users` (`id_pengguna`) ON DELETE CASCADE,
  ADD CONSTRAINT `budgets_ibfk_2` FOREIGN KEY (`id_kategori`) REFERENCES `categories` (`id_kategori`);

--
-- Constraints for table `debts`
--
ALTER TABLE `debts`
  ADD CONSTRAINT `debts_ibfk_1` FOREIGN KEY (`id_pengguna`) REFERENCES `users` (`id_pengguna`) ON DELETE CASCADE;

--
-- Constraints for table `expenses`
--
ALTER TABLE `expenses`
  ADD CONSTRAINT `expenses_ibfk_1` FOREIGN KEY (`id_pengguna`) REFERENCES `users` (`id_pengguna`) ON DELETE CASCADE,
  ADD CONSTRAINT `expenses_ibfk_2` FOREIGN KEY (`id_kategori`) REFERENCES `categories` (`id_kategori`),
  ADD CONSTRAINT `expenses_ibfk_3` FOREIGN KEY (`id_metode`) REFERENCES `payment_methods` (`id_metode`),
  ADD CONSTRAINT `expenses_ibfk_4` FOREIGN KEY (`id_mata_uang`) REFERENCES `currencies` (`id_mata_uang`);

--
-- Constraints for table `financial_goals`
--
ALTER TABLE `financial_goals`
  ADD CONSTRAINT `financial_goals_ibfk_1` FOREIGN KEY (`id_pengguna`) REFERENCES `users` (`id_pengguna`) ON DELETE CASCADE;

--
-- Constraints for table `financial_predictions`
--
ALTER TABLE `financial_predictions`
  ADD CONSTRAINT `financial_predictions_ibfk_1` FOREIGN KEY (`id_pengguna`) REFERENCES `users` (`id_pengguna`) ON DELETE CASCADE;

--
-- Constraints for table `incomes`
--
ALTER TABLE `incomes`
  ADD CONSTRAINT `incomes_ibfk_1` FOREIGN KEY (`id_pengguna`) REFERENCES `users` (`id_pengguna`) ON DELETE CASCADE,
  ADD CONSTRAINT `incomes_ibfk_2` FOREIGN KEY (`id_metode`) REFERENCES `payment_methods` (`id_metode`),
  ADD CONSTRAINT `incomes_ibfk_3` FOREIGN KEY (`id_mata_uang`) REFERENCES `currencies` (`id_mata_uang`);

--
-- Constraints for table `receivables`
--
ALTER TABLE `receivables`
  ADD CONSTRAINT `receivables_ibfk_1` FOREIGN KEY (`id_pengguna`) REFERENCES `users` (`id_pengguna`) ON DELETE CASCADE;

--
-- Constraints for table `social_user_relations`
--
ALTER TABLE `social_user_relations`
  ADD CONSTRAINT `social_user_relations_ibfk_1` FOREIGN KEY (`id_pengguna1`) REFERENCES `users` (`id_pengguna`) ON DELETE CASCADE,
  ADD CONSTRAINT `social_user_relations_ibfk_2` FOREIGN KEY (`id_pengguna2`) REFERENCES `users` (`id_pengguna`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

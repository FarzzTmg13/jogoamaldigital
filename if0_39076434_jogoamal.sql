-- phpMyAdmin SQL Dump
-- version 4.9.0.1
-- https://www.phpmyadmin.net/
--
-- Host: sql303.infinityfree.com
-- Generation Time: Jun 01, 2025 at 08:27 AM
-- Server version: 10.6.19-MariaDB
-- PHP Version: 7.2.22

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `if0_39076434_jogoamal`
--

-- --------------------------------------------------------

--
-- Table structure for table `payment_methods`
--

CREATE TABLE `payment_methods` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `type` enum('cash','bank','digital') NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payment_methods`
--

INSERT INTO `payment_methods` (`id`, `name`, `type`, `is_active`, `created_at`) VALUES
(1, 'Tunai', 'cash', 1, '2025-04-11 11:23:14'),
(2, 'Transfer Bank', 'bank', 1, '2025-04-11 11:23:14'),
(3, 'OVO', 'digital', 1, '2025-04-11 11:23:14'),
(4, 'Dana', 'digital', 1, '2025-04-11 11:23:14'),
(5, 'Gopay', 'digital', 1, '2025-04-11 11:23:14'),
(6, 'ShopeePay', 'digital', 1, '2025-04-11 11:23:14'),
(7, 'Bank Mandiri', 'bank', 1, '2025-04-11 11:23:14'),
(8, 'Bank BCA', 'bank', 1, '2025-04-11 11:23:14'),
(9, 'Bank BRI', 'bank', 1, '2025-04-11 11:23:14'),
(10, 'Bank BNI', 'bank', 1, '2025-04-11 11:23:14'),
(11, 'LinkAja', 'digital', 1, '2025-04-11 11:23:14');

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id` int(11) NOT NULL,
  `jenis` enum('pemasukan','pengeluaran') NOT NULL,
  `nama` varchar(255) NOT NULL,
  `deskripsi` text NOT NULL,
  `jumlah` double NOT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `payment_reference` varchar(100) DEFAULT NULL,
  `tanggal` date NOT NULL DEFAULT curdate()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`id`, `jenis`, `nama`, `deskripsi`, `jumlah`, `payment_method`, `payment_reference`, `tanggal`) VALUES
(106, 'pemasukan', 'Fariz Husain Albar', 'Zakat', 100000, 'Tunai', '', '2025-06-01'),
(107, 'pemasukan', 'Gahyaka Ararya Fairuz', 'Infaq', 750000, 'ShopeePay', '08192876478', '2025-06-01'),
(108, 'pengeluaran', 'Faiz Satria Ahimsa', 'Membeli Al-Quran', 280000, 'Bank BRI', '2810-01-011822', '2025-06-01');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','user') NOT NULL DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`, `created_at`) VALUES
(1, 'admin', '$2y$10$a28YoQARuwm0NwIqabQCA.8WRLrvEP6TqkIpK.MaX4nuw7zsL0kIy', 'admin', '2025-03-22 10:21:06'),
(3, 'user', '$2y$10$u5R.fidNllPmgctRCXnSS.ZKMB9z7LQczdS3n2C4xAQyJFJazhaCm', 'user', '2025-03-22 10:23:07'),
(15, 'admin123', '$2y$10$vSK5yvj0AshP5cOfD3DJBu68SKtMuKMWngsyl9vBIJlxtaeuyQt3u', 'user', '2025-06-01 09:31:49'),
(16, 'farizzz', '$2y$10$/2u7Cmgn5K.b1IVPINecY.D.bCBxzKoJBAnyDKr.rEvBmpvwWP/wO', 'user', '2025-06-01 09:52:30'),
(17, 'fariz', '$2y$10$5AfokoaG0T.9yFsKlvau5exyNrx/VGmjTq7oLTiIStZ8wtbE2FZMC', 'user', '2025-06-01 12:22:35');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `payment_methods`
--
ALTER TABLE `payment_methods`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `payment_methods`
--
ALTER TABLE `payment_methods`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=115;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

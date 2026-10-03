-- phpMyAdmin SQL Dump
-- version 4.6.6
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Oct 03, 2026 at 04:49 AM
-- Server version: 5.7.17-log
-- PHP Version: 5.6.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `logistics_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `shipments`
--

CREATE TABLE `shipments` (
  `id` int(11) NOT NULL,
  `tracking_number` varchar(50) NOT NULL,
  `sender` varchar(100) NOT NULL,
  `receiver` varchar(100) NOT NULL,
  `destination` varchar(255) NOT NULL,
  `weight_kg` decimal(10,2) DEFAULT '0.00',
  `price` decimal(10,2) DEFAULT '0.00',
  `status` enum('Pending','In Transit','Out for Delivery','Delivered') DEFAULT 'Pending',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `shipments`
--

INSERT INTO `shipments` (`id`, `tracking_number`, `sender`, `receiver`, `destination`, `weight_kg`, `price`, `status`, `updated_at`) VALUES
(1, 'TRK935F380B', 'sak', 'loso', 'borlikhamxai', '2.00', '10000.00', 'Delivered', '2026-08-25 07:51:45'),
(2, 'TRK57C94920', 'jjj', 'fff', 'borlikhamxai', '3.00', '10000.00', 'Delivered', '2026-08-25 07:58:51'),
(3, 'TRK0F0D00B2', 'fff', 'eee', 'thangone', '4.00', '10000.00', 'Delivered', '2026-08-25 07:59:00'),
(4, 'TRKCAD5F7D9', 'ww', 'ww', 'ww', '3.00', '10000.00', 'Delivered', '2026-08-25 07:59:20'),
(5, 'TRK1D5C9E26', 'ttt', 'ttt', 'borlikhamxai', '3.00', '10000.00', 'Delivered', '2026-08-25 10:31:49'),
(6, 'TRK121A8F21', 'gfg', 'rrr', 'thaphabath', '2.00', '30000.00', 'Delivered', '2026-09-01 01:00:58'),
(7, 'TRKBE2EB8B1', 'eee', 'eee', 'eee', '4.00', '25000.00', 'Delivered', '2026-09-30 05:52:22'),
(8, 'TRK70D2C8B5', 'dfdfd', 'ewew', 'llll', '3.00', '10000.00', 'Delivered', '2026-09-30 08:44:44'),
(9, 'TRK4344D8EC', 'dfdfd', 'ewew', 'llll', '3.00', '10000.00', 'Delivered', '2026-09-30 08:44:43'),
(10, 'TRK99EDCF49', 'hhh', 'hhh', 'hhh', '3.00', '10000.00', 'Pending', '2026-10-03 04:20:59'),
(11, 'TRK64549A71', 'hhh', 'hhh', 'hhh', '3.00', '10000.00', 'Delivered', '2026-10-03 04:46:25');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','driver') DEFAULT 'driver',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`, `created_at`) VALUES
(1, 'admin', '0192023a7bbd73250516f069df18b500', 'admin', '2026-08-25 07:03:53'),
(2, 'driver1', 'c974f63abee678d0e103167ad9c813a5', 'driver', '2026-08-25 07:03:53');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `shipments`
--
ALTER TABLE `shipments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `tracking_number` (`tracking_number`);

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
-- AUTO_INCREMENT for table `shipments`
--
ALTER TABLE `shipments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;
--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

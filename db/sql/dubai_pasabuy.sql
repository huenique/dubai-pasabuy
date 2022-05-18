-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 18, 2022 at 08:10 AM
-- Server version: 10.4.22-MariaDB
-- PHP Version: 8.1.2

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `dubai_pasabuy`
--

-- --------------------------------------------------------

--
-- Table structure for table `current_items`
--

CREATE TABLE `current_items` (
  `id` varchar(7) NOT NULL,
  `name` varchar(128) NOT NULL,
  `cost_aed` varchar(256) DEFAULT NULL,
  `cost_php` varchar(256) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `current_items`
--

INSERT INTO `current_items` (`id`, `name`, `cost_aed`, `cost_php`) VALUES
('175c206', 'Perfume', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `next_items`
--

CREATE TABLE `next_items` (
  `id` varchar(7) NOT NULL,
  `name` varchar(128) NOT NULL,
  `cost_aed` varchar(256) DEFAULT NULL,
  `cost_php` varchar(256) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `next_items`
--

INSERT INTO `next_items` (`id`, `name`, `cost_aed`, `cost_php`) VALUES
('30da5bd', 'Cologne', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(320) NOT NULL,
  `password` varchar(256) NOT NULL,
  `cart` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `current_items`
--
ALTER TABLE `current_items`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `next_items`
--
ALTER TABLE `next_items`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

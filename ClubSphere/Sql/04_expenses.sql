-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 12, 2026 at 07:52 PM
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
-- Database: `clubsphere`
--

-- --------------------------------------------------------

--
-- Table structure for table `equipment`
--

CREATE TABLE `equipment` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `category` varchar(50) NOT NULL,
  `condition` varchar(50) NOT NULL,
  `status` varchar(50) NOT NULL,
  `assigned_to` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `equipment`
--

INSERT INTO `equipment` (`id`, `name`, `category`, `condition`, `status`, `assigned_to`) VALUES
(4, 'rom', 'rom', 'Good', 'In Use', 'sayma'),
(5, 'Ram', 'Ram', 'New', 'Maintenance', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `expenses`
--

CREATE TABLE `expenses` (
  `id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `category` varchar(50) NOT NULL,
  `date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `expenses`
--

INSERT INTO `expenses` (`id`, `title`, `amount`, `category`, `date`) VALUES
(1, 'repaid', 5.00, 'logistics', '2026-09-12');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `u_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `uni_id` varchar(30) NOT NULL,
  `email_id` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('Member','Moderator','Admin') DEFAULT 'Member',
  `game_type` varchar(50) DEFAULT NULL,
  `ranking` varchar(50) DEFAULT NULL,
  `social_link` varchar(255) DEFAULT NULL,
  `status` enum('Pending','Approved','Rejected') DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`u_id`, `name`, `uni_id`, `email_id`, `password`, `role`, `game_type`, `ranking`, `social_link`, `status`, `created_at`) VALUES
(1, 'Srity', '93939', 'srity@gmail.com', '$2y$10$PrfKUCwVz4TTqbGqD3kC3ue7Vbrghww7W2ajNFToL.22bebgH4YGC', 'Member', NULL, NULL, NULL, 'Approved', '2026-09-05 11:59:54'),
(2, 'Jotey', '2222', 'jotey@gmail.com', '$2y$10$KRX.4kZ0wUirehGLOYrd7eeI0YP8qZ2wj/sVcpl59jF8JQ8trpmrq', 'Member', NULL, NULL, NULL, 'Approved', '2026-09-08 07:51:47'),
(5, 'Maria', '2121', 'maria@gmail.com', '$2y$10$EKwDvg6poncTYqm/c8sEFOCFpDIqbGnWbajNJK13k2k5cI5wvMIOK', 'Member', NULL, NULL, NULL, 'Rejected', '2026-09-08 08:12:51'),
(6, 'MariaJ                ', '                222222', 'mj@gmail.com', '$2y$10$Fk4Ln2O9uoExhFuEnmIujeXb/ULFtPWtHnnNA7FuD9sw1ULHp1/nG', 'Admin', 'Age of the Empire', '4', 'http.ig.com', 'Approved', '2026-09-08 09:41:21'),
(7, '                Nafisa                ', '                              ', 'nafisa@gmail.com', '$2y$10$lkNaEL51uy0jk.FS2.z9PeDlNS1fkT5Kd80p9hAVKBaJWWhAf5rAe', 'Member', NULL, NULL, NULL, 'Approved', '2026-09-09 14:57:20'),
(8, 'Panda                ', '                9090', 'panda@gmail.com', '$2y$10$KW4HFBjegY2OFQR6dVqIgePwifbXgiEfTeJbhPGyAtYMBysWK/aum', 'Member', NULL, NULL, NULL, 'Approved', '2026-09-11 05:38:56'),
(9, '                Shehzil                ', '                1212          ', 'shehzil@gmail.com', '$2y$10$FB8rrLKuVnOayKbQLOT0cuTkyBpz9fGb2a7eHKv/wOlvQvA6tppWK', 'Moderator', NULL, NULL, NULL, 'Approved', '2026-09-11 11:30:26'),
(10, 'Glowynowy                ', '               2121', 'glow@gmail.com', '$2y$10$lPGfxg1Hdxw1HrulDn9unOIoIJB7u3X6XeoADWJIaGDEBMV.w7Eb6', 'Member', NULL, NULL, NULL, 'Approved', '2026-09-11 11:35:23'),
(11, 'Bibek                ', '                3333', 'bibek@gmail.com', '$2y$10$YcxpQTL2TwEPrTi2TLyyiOU/c0k0VrQ1UVinIjhfWj8Etn8CM2OsS', 'Member', 'Valorant', '3', 'http.ig.com', 'Approved', '2026-09-11 11:41:08'),
(12, '  Sayma             ', '    4131            ', 'sayma@gmail.com', '$2y$10$W0kz/L9mWFvvjFCZvVX0rOcqAB7zMTae8bhE6w1JGJzHT9yXfTUmi', 'Member', NULL, NULL, NULL, 'Approved', '2026-09-11 16:24:47'),
(13, ' Moon               ', '   8888             ', 'moon@gmail.com', '$2y$10$yBevTIYiqjNTDlFXpJ7GCOK94OdyUsDicf1cNey5GFZ3t7fx4db6.', 'Member', NULL, NULL, NULL, 'Pending', '2026-09-12 05:08:44'),
(14, 'Potato                ', ' 4352               ', 'potato@gmail.com', '$2y$10$FoRq.uF4lgw6.s9fvaJKNetqOWxL2AR.7a8V9GXwIEHRsPcWYf03u', 'Member', NULL, NULL, NULL, 'Pending', '2026-09-12 05:31:01');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `equipment`
--
ALTER TABLE `equipment`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `expenses`
--
ALTER TABLE `expenses`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`u_id`),
  ADD UNIQUE KEY `uni_id` (`uni_id`),
  ADD UNIQUE KEY `email_id` (`email_id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `equipment`
--
ALTER TABLE `equipment`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `expenses`
--
ALTER TABLE `expenses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `u_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

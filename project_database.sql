-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 14, 2026 at 06:17 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.1.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `project_database`
--

-- --------------------------------------------------------

--
-- Table structure for table `login_attempts`
--

CREATE TABLE `login_attempts` (
  `attempt_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `username_entered` varchar(100) NOT NULL,
  `success_flag` tinyint(1) NOT NULL DEFAULT 0,
  `fail_count` int(11) NOT NULL DEFAULT 0,
  `lock_until` datetime DEFAULT NULL,
  `attempt_time` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `login_attempts`
--

INSERT INTO `login_attempts` (`attempt_id`, `user_id`, `username_entered`, `success_flag`, `fail_count`, `lock_until`, `attempt_time`) VALUES
(1, 1, 'irfan', 0, 1, NULL, '2026-01-14 11:27:48'),
(2, 1, 'irfan', 0, 2, NULL, '2026-01-14 11:30:07'),
(3, 1, 'irfan', 0, 3, '2026-01-14 04:30:17', '2026-01-14 11:30:12'),
(4, 1, 'irfan', 1, 0, NULL, '2026-01-14 11:30:28'),
(5, 1, 'irfan', 0, 1, NULL, '2026-01-14 14:11:23'),
(6, 1, 'irfan', 0, 2, NULL, '2026-01-14 14:13:02'),
(7, 1, 'irfan', 1, 0, NULL, '2026-01-14 14:13:16'),
(8, 1, 'irfan', 1, 0, NULL, '2026-01-14 14:21:05'),
(9, 1, 'irfan', 1, 0, NULL, '2026-01-14 14:23:22'),
(10, 1, 'irfan', 1, 0, NULL, '2026-01-14 14:27:00'),
(11, 1, 'irfan', 1, 0, NULL, '2026-01-14 14:30:15'),
(12, 1, 'irfan', 1, 0, NULL, '2026-01-14 14:31:06'),
(13, 1, 'irfan', 1, 0, NULL, '2026-01-14 14:37:53'),
(14, 1, 'irfan', 1, 0, NULL, '2026-01-14 14:45:24'),
(15, 1, 'irfan', 1, 0, NULL, '2026-01-14 14:47:13'),
(16, 1, 'irfan', 1, 0, NULL, '2026-01-14 14:47:37'),
(17, 1, 'irfan', 0, 1, NULL, '2026-01-15 00:56:55'),
(18, 1, 'irfan', 0, 2, NULL, '2026-01-15 00:57:00'),
(19, 1, 'irfan', 0, 3, '2026-01-14 17:57:09', '2026-01-15 00:57:04'),
(20, 1, 'irfan', 1, 0, NULL, '2026-01-15 00:57:22'),
(21, 2, 'javier', 0, 1, NULL, '2026-01-15 01:07:23'),
(22, 2, 'javier', 1, 0, NULL, '2026-01-15 01:07:34');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `role` enum('admin','manager','employee','hr') NOT NULL DEFAULT 'employee',
  `status` enum('active','inactive','suspended') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `password_hash`, `email`, `role`, `status`, `created_at`, `updated_at`) VALUES
(1, 'irfan', '$2y$10$ABVecqDGf1lBLXPyhK19cutjQ368jRjEt89sHmtk4NvPZEs5nOJKe', 'irfan@test.com', 'admin', 'active', '2026-01-14 03:25:49', '2026-01-14 03:25:49'),
(2, 'javier', '$2y$10$ABVecqDGf1lBLXPyhK19cutjQ368jRjEt89sHmtk4NvPZEs5nOJKe', 'javier@test.com', 'hr', 'active', '2026-01-14 17:06:55', '2026-01-14 17:06:55');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `login_attempts`
--
ALTER TABLE `login_attempts`
  ADD PRIMARY KEY (`attempt_id`),
  ADD KEY `idx_login_attempts_user_id` (`user_id`),
  ADD KEY `idx_login_attempts_username` (`username_entered`),
  ADD KEY `idx_login_attempts_attempt_time` (`attempt_time`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `uq_users_username` (`username`),
  ADD UNIQUE KEY `uq_users_email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `login_attempts`
--
ALTER TABLE `login_attempts`
  MODIFY `attempt_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

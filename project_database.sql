-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 24, 2026 at 08:24 AM
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
-- Table structure for table `hr_actions`
--

CREATE TABLE `hr_actions` (
  `action_id` int(11) NOT NULL,
  `hr_user_id` int(11) NOT NULL,
  `record_type` enum('leave','mc') NOT NULL,
  `record_id` int(11) NOT NULL,
  `action_taken` enum('approve','reject','edit','delete') NOT NULL,
  `hr_comments` text DEFAULT NULL,
  `action_timestamp` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `previous_status` enum('unapproved','approved','rejected') NOT NULL,
  `new_status` enum('approved','rejected','unapproved') NOT NULL,
  `user_agent` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `hr_actions`
--

INSERT INTO `hr_actions` (`action_id`, `hr_user_id`, `record_type`, `record_id`, `action_taken`, `hr_comments`, `action_timestamp`, `previous_status`, `new_status`, `user_agent`) VALUES
(1, 2, 'leave', 1, 'approve', '0', '2026-01-17 20:12:35', 'unapproved', 'approved', 1),
(4, 2, 'mc', 1, 'reject', '0', '2026-01-17 20:26:01', 'unapproved', 'rejected', 1),
(5, 2, 'mc', 1, 'approve', '0', '2026-01-17 20:28:50', 'unapproved', 'approved', 1),
(6, 2, 'mc', 1, 'approve', '0', '2026-01-17 20:28:59', 'unapproved', 'approved', 1),
(7, 2, 'leave', 1, 'approve', '0', '2026-01-17 20:29:12', 'unapproved', 'approved', 1),
(8, 2, 'mc', 1, 'approve', '0', '2026-01-17 20:30:35', 'rejected', 'approved', 1),
(9, 2, 'leave', 1, 'approve', '0', '2026-01-17 20:30:40', 'approved', '', 1),
(10, 2, 'mc', 1, 'approve', '0', '2026-01-17 20:30:47', 'approved', '', 1),
(11, 2, 'mc', 1, 'reject', '0', '2026-01-17 20:31:01', 'unapproved', 'rejected', 1),
(12, 2, 'leave', 1, 'reject', '0', '2026-01-17 20:31:04', 'unapproved', 'rejected', 1),
(13, 2, 'mc', 1, 'approve', '0', '2026-01-17 20:31:13', 'rejected', '', 1),
(14, 2, 'leave', 1, 'approve', '0', '2026-01-17 20:31:23', 'rejected', 'approved', 1),
(15, 2, 'leave', 1, 'approve', '0', '2026-01-17 20:31:28', 'approved', 'rejected', 1),
(16, 2, 'mc', 1, 'reject', '0', '2026-01-17 20:34:22', 'unapproved', 'rejected', 1),
(17, 2, 'mc', 1, 'approve', '0', '2026-01-17 20:34:26', 'rejected', 'unapproved', 1),
(18, 2, 'leave', 1, 'approve', '0', '2026-01-17 20:34:32', 'rejected', 'approved', 1),
(19, 2, 'leave', 1, 'approve', '0', '2026-01-17 20:34:35', 'approved', 'unapproved', 1),
(20, 2, 'mc', 1, 'approve', '0', '2026-01-17 20:35:05', 'unapproved', 'approved', 1),
(21, 2, 'leave', 1, 'approve', '0', '2026-01-17 20:35:42', 'unapproved', 'approved', 1),
(22, 2, 'leave', 1, 'approve', '0', '2026-01-17 20:35:51', 'approved', 'unapproved', 1),
(23, 2, 'leave', 1, 'approve', '0', '2026-01-17 20:35:54', 'unapproved', 'approved', 1),
(24, 2, 'leave', 1, 'approve', '0', '2026-01-17 20:36:04', 'approved', 'approved', 1),
(25, 2, 'leave', 1, 'approve', '0', '2026-01-17 20:36:54', 'approved', 'approved', 1),
(26, 2, 'mc', 1, 'approve', '0', '2026-01-17 20:39:18', 'unapproved', 'approved', 1),
(27, 2, 'leave', 1, 'approve', '0', '2026-01-17 20:39:26', 'approved', 'approved', 1),
(28, 2, 'leave', 1, 'approve', '0', '2026-01-17 20:39:30', 'approved', 'unapproved', 1),
(29, 2, 'leave', 1, 'approve', '0', '2026-01-17 20:39:33', 'unapproved', 'approved', 1),
(30, 2, 'leave', 1, 'approve', '0', '2026-01-17 20:39:42', 'approved', 'unapproved', 1),
(31, 2, 'leave', 1, 'reject', '0', '2026-01-17 20:39:55', 'unapproved', 'rejected', 1),
(32, 2, 'leave', 1, 'approve', '0', '2026-01-17 20:42:05', 'rejected', 'rejected', 1),
(33, 2, 'leave', 1, 'approve', '0', '2026-01-17 20:42:10', 'rejected', 'unapproved', 1),
(34, 2, 'leave', 1, 'reject', '0', '2026-01-17 20:42:14', 'unapproved', 'rejected', 1),
(35, 2, 'leave', 1, 'approve', '0', '2026-01-17 20:42:19', 'rejected', 'rejected', 1),
(36, 2, 'leave', 1, 'approve', '0', '2026-01-17 20:42:22', 'rejected', 'unapproved', 1),
(37, 2, 'leave', 1, 'approve', '0', '2026-01-17 20:42:26', 'unapproved', 'approved', 1),
(38, 2, 'leave', 1, 'approve', '0', '2026-01-17 20:42:44', 'approved', 'unapproved', 1),
(39, 2, 'leave', 1, 'approve', '0', '2026-01-17 20:42:47', 'unapproved', 'approved', 1),
(40, 2, 'leave', 1, 'approve', '0', '2026-01-17 20:44:08', 'approved', 'unapproved', 1),
(41, 2, 'leave', 1, 'approve', '0', '2026-01-17 20:44:11', 'unapproved', 'approved', 1),
(42, 2, 'leave', 1, 'approve', '0', '2026-01-17 20:44:15', 'approved', 'unapproved', 1),
(43, 2, 'leave', 1, 'approve', '0', '2026-01-17 20:44:16', 'unapproved', 'approved', 1),
(44, 2, 'leave', 1, 'approve', '0', '2026-01-17 20:44:24', 'approved', 'rejected', 1),
(45, 2, 'leave', 1, 'approve', '0', '2026-01-17 20:44:28', 'rejected', 'unapproved', 1),
(46, 2, 'leave', 1, 'reject', '0', '2026-01-17 20:44:32', 'unapproved', 'rejected', 1),
(47, 2, 'leave', 1, 'approve', '0', '2026-01-17 20:44:39', 'rejected', 'unapproved', 1),
(48, 2, 'leave', 1, 'approve', '0', '2026-01-17 20:44:42', 'unapproved', 'approved', 1),
(49, 2, 'leave', 1, 'approve', '0', '2026-01-17 20:44:48', 'approved', 'unapproved', 1),
(50, 2, 'leave', 1, 'approve', '0', '2026-01-17 20:44:54', 'unapproved', 'approved', 1),
(51, 2, 'leave', 1, 'approve', '0', '2026-01-17 20:44:59', 'approved', 'rejected', 1),
(52, 2, 'leave', 1, 'approve', '0', '2026-01-17 20:45:08', 'rejected', 'unapproved', 1),
(53, 2, 'leave', 1, 'approve', '0', '2026-01-17 20:45:11', 'unapproved', 'approved', 1),
(54, 2, 'leave', 1, 'approve', '0', '2026-01-17 20:46:04', 'approved', 'unapproved', 1),
(55, 2, 'leave', 1, 'approve', '0', '2026-01-17 20:46:06', 'unapproved', 'approved', 1),
(56, 2, 'leave', 1, 'approve', '0', '2026-01-17 20:46:12', 'approved', 'rejected', 1),
(57, 2, 'leave', 1, 'approve', '0', '2026-01-17 20:46:18', 'rejected', 'approved', 1),
(58, 2, 'leave', 1, 'approve', '0', '2026-01-17 20:46:23', 'approved', 'unapproved', 1),
(59, 2, 'leave', 1, 'approve', '0', '2026-01-17 20:46:25', 'unapproved', 'approved', 1),
(60, 2, 'leave', 1, 'approve', '0', '2026-01-17 20:46:30', 'approved', 'unapproved', 1),
(61, 2, 'leave', 1, 'approve', '0', '2026-01-17 20:46:32', 'unapproved', 'approved', 1),
(62, 2, 'leave', 1, 'approve', '0', '2026-01-17 20:46:35', 'approved', 'unapproved', 1),
(63, 2, 'leave', 1, 'approve', '0', '2026-01-17 20:46:37', 'unapproved', 'approved', 1),
(64, 2, 'leave', 1, 'approve', '0', '2026-01-17 20:48:05', 'approved', 'approved', 1),
(65, 2, 'leave', 1, 'approve', '0', '2026-01-17 20:49:18', 'approved', 'approved', 1),
(66, 2, 'leave', 1, 'approve', '0', '2026-01-17 20:49:25', 'approved', 'unapproved', 1),
(67, 2, 'leave', 1, 'approve', '0', '2026-01-17 20:49:29', 'unapproved', 'approved', 1),
(68, 2, 'leave', 1, 'approve', '0', '2026-01-17 20:49:33', 'unapproved', 'approved', 1),
(69, 2, 'mc', 1, 'approve', NULL, '2026-01-17 20:50:59', 'unapproved', 'approved', 1),
(70, 2, 'mc', 1, 'approve', NULL, '2026-01-17 20:51:00', 'approved', 'approved', 1),
(71, 2, 'mc', 1, 'approve', '0', '2026-01-17 20:51:12', 'approved', 'unapproved', 1),
(72, 2, 'mc', 1, 'approve', NULL, '2026-01-17 20:51:15', 'unapproved', 'approved', 1),
(73, 2, 'mc', 1, 'approve', '0', '2026-01-17 20:51:19', 'approved', 'unapproved', 1),
(74, 2, 'mc', 1, 'reject', NULL, '2026-01-17 20:51:22', 'unapproved', 'rejected', 1),
(75, 2, 'mc', 1, 'edit', '0', '2026-01-17 20:56:33', 'rejected', 'approved', 1),
(76, 2, 'mc', 1, 'edit', '0', '2026-01-17 20:56:40', 'approved', 'unapproved', 1),
(77, 2, 'mc', 1, 'approve', NULL, '2026-01-17 20:56:47', 'unapproved', 'approved', 1),
(78, 2, 'mc', 1, 'delete', '0', '2026-01-17 20:56:52', 'unapproved', 'approved', 1),
(79, 2, 'mc', 1, 'delete', '0', '2026-01-17 20:59:10', 'unapproved', 'approved', 1),
(80, 2, 'leave', 1, 'approve', '0', '2026-01-17 20:59:12', 'unapproved', 'approved', 1),
(81, 2, 'leave', 1, 'edit', '0', '2026-01-17 20:59:19', 'approved', 'rejected', 1),
(82, 2, 'leave', 1, 'edit', '0', '2026-01-17 21:00:24', 'rejected', 'approved', 1),
(83, 2, 'leave', 1, 'delete', '0', '2026-01-17 21:00:27', 'unapproved', 'approved', 1),
(84, 2, 'mc', 1, 'approve', NULL, '2026-01-17 21:01:01', 'unapproved', 'approved', 1),
(85, 2, 'mc', 1, 'edit', '0', '2026-01-17 21:01:05', 'approved', 'unapproved', 1),
(86, 2, 'mc', 1, 'approve', NULL, '2026-01-17 21:02:39', 'unapproved', 'approved', 1),
(87, 2, 'mc', 1, 'edit', '0', '2026-01-17 21:02:48', 'approved', 'rejected', 1),
(88, 2, 'mc', 1, 'edit', '0', '2026-01-17 21:02:51', 'rejected', 'unapproved', 1),
(89, 2, 'mc', 1, 'approve', NULL, '2026-01-17 21:02:54', 'unapproved', 'approved', 1),
(90, 2, 'mc', 1, 'delete', '0', '2026-01-17 21:02:58', 'unapproved', 'approved', 1),
(91, 2, 'mc', 1, 'delete', '0', '2026-01-17 21:03:53', 'unapproved', 'approved', 1),
(92, 2, 'mc', 1, 'delete', '0', '2026-01-17 21:04:07', 'unapproved', 'approved', 1),
(93, 2, 'mc', 1111, 'approve', NULL, '2026-01-17 21:05:53', 'unapproved', 'approved', 1),
(94, 2, 'mc', 1111, 'edit', '0', '2026-01-17 21:06:32', 'approved', 'unapproved', 1),
(95, 2, 'mc', 1111, 'approve', NULL, '2026-01-17 21:06:35', 'unapproved', 'approved', 1),
(96, 2, 'mc', 1111, 'delete', '0', '2026-01-17 21:06:38', 'unapproved', 'approved', 1),
(97, 2, 'mc', 123, 'approve', NULL, '2026-01-21 06:07:15', 'unapproved', 'approved', 1),
(98, 2, 'mc', 123, 'edit', '0', '2026-01-21 06:07:30', 'approved', 'rejected', 1),
(99, 2, 'mc', 123, 'edit', '0', '2026-01-21 06:07:35', 'rejected', 'unapproved', 1),
(100, 2, 'mc', 123, 'approve', NULL, '2026-01-21 06:07:39', 'unapproved', 'approved', 1),
(101, 2, 'mc', 123, 'approve', NULL, '2026-01-21 06:15:14', 'approved', 'approved', 1),
(102, 2, 'mc', 123, 'approve', NULL, '2026-01-21 06:15:30', 'approved', 'approved', 1),
(103, 2, 'mc', 123, 'edit', '0', '2026-01-21 06:16:13', 'approved', 'rejected', 1),
(104, 2, 'mc', 123, 'edit', '0', '2026-01-21 06:16:18', 'rejected', 'approved', 1),
(105, 2, 'mc', 123, 'edit', '0', '2026-01-21 06:17:06', 'approved', 'approved', 1),
(106, 2, 'mc', 123, 'edit', '0', '2026-01-21 06:17:14', 'approved', 'rejected', 1),
(107, 2, 'mc', 123, 'edit', '0', '2026-01-21 06:17:18', 'rejected', 'approved', 1),
(108, 2, 'mc', 123, 'edit', '0', '2026-01-21 06:18:30', 'approved', 'approved', 1),
(109, 2, 'mc', 123, 'edit', '0', '2026-01-21 06:18:39', 'approved', 'rejected', 1),
(110, 2, 'mc', 123, 'edit', '0', '2026-01-21 06:18:45', 'rejected', 'approved', 1),
(111, 2, 'mc', 123, 'edit', '0', '2026-01-21 06:22:10', 'approved', 'approved', 1),
(112, 2, 'mc', 123, 'edit', '0', '2026-01-21 06:23:33', 'approved', 'approved', 1),
(113, 2, 'mc', 123, 'edit', '0', '2026-01-21 06:24:06', 'approved', 'rejected', 1),
(114, 2, 'mc', 123, 'edit', '0', '2026-01-21 06:24:13', 'rejected', 'approved', 1),
(115, 2, 'mc', 123, 'edit', '0', '2026-01-21 06:24:21', 'approved', 'unapproved', 1),
(116, 2, 'mc', 123, 'approve', NULL, '2026-01-21 06:24:27', 'unapproved', 'approved', 1),
(117, 2, 'mc', 123, 'edit', '0', '2026-01-21 06:24:35', 'approved', 'unapproved', 1),
(118, 2, 'mc', 123, 'reject', NULL, '2026-01-21 06:24:37', 'unapproved', 'rejected', 1),
(119, 2, 'mc', 123, 'edit', '0', '2026-01-21 06:24:46', 'rejected', 'approved', 1),
(120, 2, 'mc', 123, 'edit', '0', '2026-01-21 06:25:06', 'approved', 'rejected', 1),
(121, 2, 'mc', 123, 'edit', '0', '2026-01-21 06:25:13', 'rejected', 'unapproved', 1),
(122, 2, 'mc', 123, 'approve', NULL, '2026-01-21 06:25:17', 'unapproved', 'approved', 1),
(123, 2, 'mc', 123, 'edit', '0', '2026-01-21 09:31:10', 'approved', 'rejected', 1),
(124, 2, 'mc', 123, 'edit', '0', '2026-01-21 09:31:37', 'rejected', 'approved', 1),
(125, 2, 'mc', 123, 'edit', '0', '2026-01-21 09:31:57', 'approved', 'rejected', 1),
(126, 2, 'mc', 123, 'edit', '0', '2026-01-21 09:35:56', 'rejected', 'unapproved', 1),
(127, 2, 'mc', 123, 'reject', NULL, '2026-01-24 03:50:55', 'unapproved', 'rejected', 1),
(128, 2, 'mc', 123, 'edit', '0', '2026-01-24 03:51:04', 'rejected', 'approved', 1),
(129, 2, 'mc', 123, 'edit', '0', '2026-01-24 03:53:32', 'approved', 'approved', 1),
(130, 2, 'mc', 123, 'edit', '0', '2026-01-24 03:53:44', 'approved', 'approved', 1),
(131, 2, 'mc', 123, 'edit', '0', '2026-01-24 03:54:59', 'approved', 'approved', 1),
(132, 2, 'mc', 123, 'edit', '0', '2026-01-24 03:55:08', 'approved', 'rejected', 1),
(133, 2, 'mc', 123, 'edit', '0', '2026-01-24 03:55:12', 'rejected', 'unapproved', 1),
(134, 2, 'mc', 123, 'edit', '0', '2026-01-24 04:00:32', 'unapproved', 'unapproved', 1),
(135, 2, 'mc', 123, 'edit', '0', '2026-01-24 04:03:36', 'unapproved', 'unapproved', 1),
(136, 2, 'mc', 123, 'edit', '0', '2026-01-24 04:05:00', 'unapproved', 'unapproved', 1),
(137, 2, 'mc', 123, 'edit', '0', '2026-01-24 04:07:24', 'unapproved', 'unapproved', 1),
(138, 2, 'mc', 123, 'edit', '0', '2026-01-24 04:09:53', 'unapproved', 'unapproved', 1),
(139, 2, 'mc', 123, 'edit', '0', '2026-01-24 04:10:07', 'unapproved', 'unapproved', 1),
(140, 2, 'mc', 123, 'edit', '0', '2026-01-24 04:10:35', 'unapproved', 'unapproved', 1),
(141, 2, 'mc', 123, 'edit', '0', '2026-01-24 04:12:10', 'unapproved', 'unapproved', 1),
(142, 2, 'mc', 123, 'edit', '0', '2026-01-24 04:13:12', 'unapproved', 'unapproved', 1),
(143, 2, 'mc', 123, 'edit', '0', '2026-01-24 04:14:30', 'unapproved', 'unapproved', 1),
(144, 2, 'leave', 2, 'approve', '0', '2026-01-24 04:14:54', 'unapproved', 'approved', 1),
(145, 2, 'mc', 123, 'reject', NULL, '2026-01-24 04:14:58', 'unapproved', 'rejected', 1),
(146, 2, 'mc', 123, 'reject', NULL, '2026-01-24 04:16:30', 'rejected', 'rejected', 1),
(147, 2, 'mc', 123, 'reject', NULL, '2026-01-24 04:17:32', 'rejected', 'rejected', 1),
(148, 2, 'mc', 123, 'reject', NULL, '2026-01-24 04:20:21', 'rejected', 'rejected', 1),
(149, 2, 'mc', 123, 'reject', NULL, '2026-01-24 04:20:26', 'rejected', 'rejected', 1),
(150, 2, 'mc', 30, 'reject', NULL, '2026-01-24 04:54:23', 'unapproved', 'rejected', 1),
(151, 2, 'mc', 30, 'edit', '0', '2026-01-24 04:54:30', 'rejected', 'approved', 1),
(152, 2, 'mc', 30, 'edit', '0', '2026-01-24 04:55:23', 'approved', 'approved', 1),
(153, 2, 'mc', 30, 'edit', '0', '2026-01-24 04:55:41', 'approved', 'approved', 1),
(154, 2, 'leave', 30, 'approve', '0', '2026-01-24 04:56:04', 'unapproved', 'approved', 1),
(155, 2, 'leave', 30, 'edit', '0', '2026-01-24 04:56:11', 'approved', 'unapproved', 1),
(156, 2, 'leave', 30, 'reject', '0', '2026-01-24 04:56:20', 'unapproved', 'rejected', 1),
(157, 2, 'leave', 30, 'edit', '0', '2026-01-24 04:56:23', 'rejected', 'unapproved', 1),
(158, 2, 'leave', 30, 'approve', '0', '2026-01-24 04:56:26', 'unapproved', 'approved', 1),
(159, 2, 'leave', 30, 'edit', '0', '2026-01-24 04:56:30', 'approved', 'unapproved', 1),
(160, 2, 'mc', 30, 'edit', '0', '2026-01-24 06:26:51', 'approved', 'unapproved', 1),
(161, 2, 'mc', 30, 'reject', NULL, '2026-01-24 06:26:57', 'unapproved', 'rejected', 1),
(162, 2, 'leave', 30, 'approve', '0', '2026-01-24 06:27:03', 'unapproved', 'approved', 1),
(163, 2, 'leave', 30, 'edit', '0', '2026-01-24 06:27:12', 'approved', 'rejected', 1),
(164, 2, 'leave', 30, 'edit', '0', '2026-01-24 06:27:19', 'rejected', 'rejected', 1),
(165, 2, 'leave', 30, 'edit', '0', '2026-01-24 06:27:24', 'rejected', 'unapproved', 1),
(166, 3, 'leave', 2, 'edit', '0', '2026-01-24 06:51:08', 'approved', 'rejected', 3),
(167, 3, 'leave', 30, 'reject', '0', '2026-01-24 06:51:15', 'unapproved', 'rejected', 3),
(168, 3, 'leave', 2, 'edit', '0', '2026-01-24 06:51:19', 'rejected', 'unapproved', 3),
(169, 2, 'leave', 2, 'approve', '0', '2026-01-24 06:52:04', 'unapproved', 'approved', 2),
(170, 3, 'leave', 2, 'edit', '0', '2026-01-24 06:52:25', 'approved', 'unapproved', 3),
(171, 3, 'leave', 30, 'edit', '0', '2026-01-24 06:52:29', 'rejected', 'unapproved', 3),
(172, 2, 'leave', 2, 'approve', '0', '2026-01-24 06:52:42', 'unapproved', 'approved', 2),
(173, 2, 'leave', 30, 'reject', '0', '2026-01-24 06:52:47', 'unapproved', 'rejected', 2),
(174, 3, 'leave', 2, 'edit', '0', '2026-01-24 06:56:38', 'approved', 'unapproved', 3);

-- --------------------------------------------------------

--
-- Table structure for table `leave_requests`
--

CREATE TABLE `leave_requests` (
  `leave_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `leave_type` int(11) NOT NULL,
  `start_date` int(11) NOT NULL,
  `end_date` int(11) NOT NULL,
  `reason` text DEFAULT NULL,
  `status` enum('approved','rejected','unapproved') NOT NULL DEFAULT 'unapproved',
  `submitted_at` int(11) NOT NULL,
  `approved_at` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `leave_requests`
--

INSERT INTO `leave_requests` (`leave_id`, `user_id`, `leave_type`, `start_date`, `end_date`, `reason`, `status`, `submitted_at`, `approved_at`) VALUES
(2, 4, 1, 12, 221, '', 'unapproved', 9, 0),
(30, 4, 1, 12, 221, '', 'rejected', 9, 10);

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
-- Table structure for table `mc_records`
--

CREATE TABLE `mc_records` (
  `mc_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `clinic_name` int(11) NOT NULL,
  `start_date` int(11) NOT NULL,
  `end_date` int(11) NOT NULL,
  `mc_file_path` int(11) NOT NULL,
  `mime_type` int(11) NOT NULL,
  `verification_status` enum('approved','rejected','unapproved') NOT NULL DEFAULT 'unapproved',
  `submitted_at` int(11) NOT NULL,
  `verified_at` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `mc_records`
--

INSERT INTO `mc_records` (`mc_id`, `user_id`, `clinic_name`, `start_date`, `end_date`, `mc_file_path`, `mime_type`, `verification_status`, `submitted_at`, `verified_at`) VALUES
(30, 3, 123, 1, 1, 233, 1, 'rejected', 1, 4),
(123, 1, 123, 111, 222, 123, 123, 'rejected', 213, 0);

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
(2, 'javier', '$2y$10$ABVecqDGf1lBLXPyhK19cutjQ368jRjEt89sHmtk4NvPZEs5nOJKe', 'javier@test.com', 'hr', 'active', '2026-01-14 17:06:55', '2026-01-14 17:06:55'),
(3, 'testemployee1', '$2y$10$CpQ1qBbXWs4UsvZrk5wFR.dEH0VQ6qdSCI0F5xGSy3dJZn/4YguTm', 'abc@test.com', 'employee', 'active', '2026-01-21 06:20:59', '2026-01-21 06:20:59'),
(4, 'testmanager1', '$2y$10$cKerb404bvj5O9dYC3RkU.ZgeQtfWQj1/sFYKK8LT0E0Q/dqCVe.2', 'asdf@test.com', 'manager', 'inactive', '2026-01-21 06:22:05', '2026-01-21 09:33:46');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `hr_actions`
--
ALTER TABLE `hr_actions`
  ADD PRIMARY KEY (`action_id`),
  ADD KEY `user_agent` (`user_agent`);

--
-- Indexes for table `leave_requests`
--
ALTER TABLE `leave_requests`
  ADD PRIMARY KEY (`leave_id`);

--
-- Indexes for table `login_attempts`
--
ALTER TABLE `login_attempts`
  ADD PRIMARY KEY (`attempt_id`),
  ADD KEY `idx_login_attempts_user_id` (`user_id`),
  ADD KEY `idx_login_attempts_username` (`username_entered`),
  ADD KEY `idx_login_attempts_attempt_time` (`attempt_time`);

--
-- Indexes for table `mc_records`
--
ALTER TABLE `mc_records`
  ADD PRIMARY KEY (`mc_id`);

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
-- AUTO_INCREMENT for table `hr_actions`
--
ALTER TABLE `hr_actions`
  MODIFY `action_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=175;

--
-- AUTO_INCREMENT for table `leave_requests`
--
ALTER TABLE `leave_requests`
  MODIFY `leave_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `login_attempts`
--
ALTER TABLE `login_attempts`
  MODIFY `attempt_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `hr_actions`
--
ALTER TABLE `hr_actions`
  ADD CONSTRAINT `fk_user_id` FOREIGN KEY (`user_agent`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

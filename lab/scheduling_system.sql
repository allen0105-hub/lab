-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 28, 2025 at 06:50 AM
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
-- Database: `scheduling_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `name`, `password`, `created_at`) VALUES
(1, 'Admin', '$2y$10$LYRy4kdAm5i7IXoIB8guIeeWEczfQ2me1Pl0TTFJ9G.rh0MGaPRPu', '2025-09-06 17:00:30');

-- --------------------------------------------------------

--
-- Table structure for table `reservations`
--

CREATE TABLE `reservations` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `schedule_id` int(11) DEFAULT NULL,
  `day_date` date NOT NULL,
  `hour` tinyint(4) NOT NULL,
  `reservation_type` varchar(50) NOT NULL,
  `reason` text NOT NULL,
  `section` varchar(50) DEFAULT NULL,
  `status` enum('Pending','Approved','Denied') DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `admin_removed` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reservations`
--

INSERT INTO `reservations` (`id`, `user_id`, `schedule_id`, `day_date`, `hour`, `reservation_type`, `reason`, `section`, `status`, `created_at`, `admin_removed`) VALUES
(9, 1, NULL, '2025-09-12', 15, 'Group', 'FSDGS', NULL, 'Denied', '2025-09-08 06:18:43', 0),
(10, 1, NULL, '2025-09-13', 19, 'Solo', 'SDFSG', NULL, 'Denied', '2025-09-08 06:18:49', 0),
(11, 1, NULL, '2025-09-13', 11, 'Group', 'fdsS', NULL, 'Denied', '2025-09-08 06:18:55', 0),
(12, 2, NULL, '2025-09-12', 9, 'Class', 'sfdfsdfs', NULL, 'Denied', '2025-09-08 06:19:27', 0),
(13, 2, NULL, '2025-09-09', 9, 'Group', 'sdssa', NULL, 'Approved', '2025-09-08 06:19:33', 0),
(14, 2, NULL, '2025-09-08', 15, 'Group', 'sDFfsDS', NULL, 'Denied', '2025-09-08 06:19:39', 0),
(15, 5, NULL, '2025-09-09', 8, 'Class', 'sadsadad', NULL, 'Denied', '2025-09-08 12:47:46', 0),
(16, 3, NULL, '2025-09-12', 14, 'Solo', 'asfdffa', NULL, 'Denied', '2025-09-08 15:28:29', 0),
(17, 3, NULL, '2025-09-13', 20, 'Group', 'sadsfds', NULL, 'Denied', '2025-09-08 18:15:34', 0),
(18, 2, 5, '2025-09-09', 7, 'Solo', 'Allen\n', NULL, 'Pending', '2025-09-08 21:00:03', 0),
(19, 3, NULL, '2025-09-09', 14, 'Solo', 'haidagfug\n', NULL, 'Pending', '2025-09-09 04:19:06', 0),
(20, 3, NULL, '2025-09-10', 8, 'Solo', '', NULL, 'Approved', '2025-09-09 04:37:11', 0),
(21, 3, NULL, '2025-09-10', 10, 'Group', '', NULL, 'Approved', '2025-09-09 04:37:18', 0),
(22, 3, NULL, '2025-09-10', 9, 'Solo', '', NULL, 'Denied', '2025-09-09 04:37:23', 0),
(23, 1, NULL, '2025-09-10', 8, 'Group', 'asfafs', NULL, 'Denied', '2025-09-09 04:38:08', 0),
(24, 1, NULL, '2025-09-10', 9, 'Group', '', NULL, 'Denied', '2025-09-09 04:38:13', 0),
(25, 1, NULL, '2025-09-10', 10, 'Group', '', NULL, 'Denied', '2025-09-09 05:36:34', 0),
(26, 1, NULL, '2025-09-10', 11, 'Group', 'DFSdfF', NULL, 'Pending', '2025-09-09 05:36:45', 0),
(27, 2, NULL, '2025-09-10', 8, 'Group', '', NULL, 'Denied', '2025-09-09 05:37:09', 0),
(28, 2, NULL, '2025-09-10', 9, 'Group', '', NULL, 'Denied', '2025-09-09 05:37:15', 0),
(29, 2, NULL, '2025-09-10', 10, 'Class', '', NULL, 'Denied', '2025-09-09 05:37:23', 0),
(30, 2, NULL, '2025-09-10', 11, 'Class', 'FDsdfsd', NULL, 'Pending', '2025-09-09 05:37:29', 0),
(31, 3, NULL, '2025-09-13', 13, 'Class', 'sdgs', NULL, 'Pending', '2025-09-09 06:15:20', 0),
(32, 3, NULL, '2025-09-11', 10, 'Solo', '', NULL, 'Denied', '2025-09-09 06:49:53', 0),
(33, 8, NULL, '2025-09-17', 12, 'Solo', '', NULL, 'Approved', '2025-09-15 09:57:36', 0),
(34, 4, NULL, '2025-09-17', 8, 'Solo', 'wala', NULL, 'Pending', '2025-09-16 19:20:35', 0),
(35, 7, NULL, '2025-09-19', 7, 'Class', '', NULL, 'Denied', '2025-09-17 19:50:20', 0),
(36, 7, NULL, '2025-09-20', 7, 'Group', '', NULL, 'Denied', '2025-09-17 19:50:26', 0),
(37, 7, NULL, '2025-09-21', 7, 'Group', '', NULL, 'Denied', '2025-09-17 19:50:32', 0),
(38, 7, NULL, '2025-09-19', 8, 'Group', '', NULL, 'Denied', '2025-09-17 21:02:17', 0),
(39, 7, NULL, '2025-09-20', 8, 'Solo', 'gaadgadf', NULL, 'Pending', '2025-09-17 21:02:24', 0),
(40, 7, NULL, '2025-09-21', 8, 'Group', '', NULL, 'Pending', '2025-09-17 21:02:29', 0),
(41, 2, NULL, '2025-09-28', 21, 'Solo', '', NULL, 'Denied', '2025-09-28 05:18:00', 0),
(42, 2, NULL, '2025-09-28', 15, 'Class', 'ff', NULL, 'Pending', '2025-09-28 06:49:23', 0),
(43, 2, NULL, '2025-09-28', 19, 'Solo', '', NULL, 'Denied', '2025-09-28 07:43:32', 0),
(44, 7, NULL, '2025-09-28', 21, 'Solo', '', NULL, 'Denied', '2025-09-28 11:35:38', 0),
(45, 9, NULL, '2025-09-30', 17, 'Solo', '', NULL, 'Denied', '2025-09-29 06:11:30', 0),
(46, 6, NULL, '2025-09-30', 9, 'Solo', 'uiadiGU', NULL, 'Pending', '2025-09-29 07:41:56', 0),
(47, 6, NULL, '2025-09-30', 10, 'Solo', '', NULL, 'Denied', '2025-09-29 07:47:12', 0),
(48, 9, NULL, '2025-10-02', 16, 'Group', '', NULL, 'Denied', '2025-09-29 08:03:05', 0),
(49, 7, NULL, '2025-09-30', 12, 'Solo', '', NULL, 'Approved', '2025-09-29 10:04:30', 0),
(50, 7, NULL, '2025-10-03', 7, 'Group', 'dsfag', NULL, 'Pending', '2025-10-02 11:43:17', 0),
(51, 7, NULL, '2025-10-09', 9, 'Solo', '', NULL, 'Denied', '2025-10-07 07:08:04', 0),
(52, 7, NULL, '2025-10-09', 12, 'Group', '', NULL, 'Denied', '2025-10-07 09:12:45', 0),
(53, 7, NULL, '2025-10-09', 18, 'Group', '', NULL, 'Denied', '2025-10-07 10:21:53', 0),
(54, 7, NULL, '2025-10-09', 19, 'Solo', '', NULL, 'Denied', '2025-10-08 07:39:50', 0),
(55, 7, NULL, '2025-10-10', 18, 'Solo', 'sdfag', NULL, 'Pending', '2025-10-08 08:13:06', 0),
(56, 7, NULL, '2025-10-10', 19, 'Solo', 'daf', NULL, 'Pending', '2025-10-08 08:13:13', 0),
(57, 7, NULL, '2025-10-10', 20, 'Group', 'fafd', NULL, 'Pending', '2025-10-08 08:13:19', 0),
(58, 7, NULL, '2025-10-10', 21, 'Solo', 'fdkahgkhgkf', NULL, 'Pending', '2025-10-08 08:14:47', 0),
(59, 7, NULL, '2025-10-09', 21, 'Group', 'dsdgakjd', NULL, 'Pending', '2025-10-08 08:15:09', 0),
(60, 6, NULL, '2025-10-11', 13, 'Solo', 'dsfagf', NULL, 'Pending', '2025-10-08 08:18:33', 0),
(61, 6, NULL, '2025-10-11', 14, 'Solo', 'sdfaf', NULL, 'Pending', '2025-10-08 08:18:40', 0),
(62, 6, NULL, '2025-10-10', 17, 'Solo', 'sdgadf', NULL, 'Pending', '2025-10-08 08:18:47', 0),
(63, 9, NULL, '2025-10-10', 10, 'Group', '', NULL, 'Approved', '2025-10-08 08:24:18', 0),
(64, 9, NULL, '2025-10-10', 12, 'Solo', 'sdfa', NULL, 'Pending', '2025-10-08 08:24:44', 0),
(65, 9, NULL, '2025-10-10', 11, 'Solo', '', NULL, 'Approved', '2025-10-08 08:25:08', 0),
(66, 9, NULL, '2025-10-10', 14, 'Solo', 'sdfaf', NULL, 'Pending', '2025-10-08 08:25:14', 0),
(67, 9, NULL, '2025-10-10', 9, 'Solo', '', NULL, 'Denied', '2025-10-08 08:25:54', 0),
(68, 9, NULL, '2025-10-10', 21, 'Group', 'dfs', NULL, 'Pending', '2025-10-08 08:26:01', 0),
(69, 9, NULL, '2025-10-09', 21, 'Group', 'dsfsa', NULL, 'Pending', '2025-10-08 08:33:31', 0),
(70, 7, NULL, '2025-10-10', 9, 'Solo', '', NULL, 'Approved', '2025-10-08 14:48:18', 0),
(71, 7, NULL, '2025-10-09', 12, 'Solo', '', NULL, 'Denied', '2025-10-08 14:48:34', 0),
(72, 7, NULL, '2025-10-09', 12, 'Solo', '', NULL, 'Denied', '2025-10-08 14:49:17', 0),
(73, 7, NULL, '2025-10-09', 12, 'Solo', '', NULL, 'Denied', '2025-10-08 14:49:17', 0),
(74, 7, NULL, '2025-10-09', 12, 'Solo', '', NULL, 'Approved', '2025-10-08 14:49:18', 0),
(75, 7, NULL, '2025-10-09', 12, 'Solo', '', NULL, 'Denied', '2025-10-08 14:49:18', 0),
(76, 7, NULL, '2025-10-09', 12, 'Solo', '', NULL, 'Denied', '2025-10-08 14:49:18', 0),
(77, 7, NULL, '2025-10-09', 12, 'Solo', '', NULL, 'Denied', '2025-10-08 14:49:18', 0),
(78, 7, NULL, '2025-10-09', 12, 'Solo', '', NULL, 'Denied', '2025-10-08 14:49:18', 0),
(79, 7, NULL, '2025-10-11', 9, 'Group', '', NULL, 'Denied', '2025-10-08 15:18:04', 0),
(80, 7, NULL, '2025-10-12', 9, 'Solo', '', NULL, 'Approved', '2025-10-08 15:18:11', 0),
(81, 7, NULL, '2025-10-10', 17, 'Group', 'dgfad', NULL, 'Pending', '2025-10-08 15:29:39', 0),
(82, 7, NULL, '2025-10-10', 16, 'Solo', 'sdads', NULL, 'Pending', '2025-10-08 15:31:09', 0),
(83, 7, NULL, '2025-10-11', 10, 'Solo', 'dsDS', NULL, 'Pending', '2025-10-09 04:14:19', 0),
(84, 7, NULL, '2025-10-12', 10, 'Group', '', NULL, 'Denied', '2025-10-09 04:14:36', 0),
(85, 6, NULL, '2025-10-12', 10, 'Solo', '', NULL, 'Approved', '2025-10-11 14:43:04', 0),
(86, 6, NULL, '2025-10-12', 11, 'Class', 'ufutyfuytg', NULL, 'Pending', '2025-10-11 15:06:53', 0),
(87, 7, NULL, '2025-10-23', 7, 'Solo', 'UHFLSIDU', NULL, 'Pending', '2025-10-22 07:33:51', 0),
(88, 7, NULL, '2025-10-23', 8, 'Solo', 'AGFAHSDL', NULL, 'Pending', '2025-10-22 07:43:10', 0),
(89, 7, NULL, '2025-10-23', 21, 'Group', '', NULL, 'Denied', '2025-10-22 08:01:24', 0),
(90, 11, NULL, '2025-10-23', 10, 'Solo', '', '4-HOPE', 'Approved', '2025-10-22 08:28:47', 0),
(91, 12, NULL, '2025-10-26', 8, 'Group', '', '5-hope', 'Approved', '2025-10-22 08:37:27', 0),
(92, 11, NULL, '2025-10-26', 9, 'Group', '', '4-HOPE', 'Approved', '2025-10-22 08:38:27', 0),
(93, 11, NULL, '2025-10-26', 11, 'Group', '', '4-HOPE', 'Approved', '2025-10-22 08:40:36', 0),
(94, 11, NULL, '2025-10-25', 8, 'Group', 'SSB', '4-HOPE', 'Pending', '2025-10-24 08:15:51', 0),
(95, 11, NULL, '2025-10-25', 9, 'Group', 'FSKDJBLkjflKJ', '4-HOPE', 'Pending', '2025-10-24 09:13:23', 0),
(96, 11, NULL, '2025-10-29', 8, 'Class', '', '4-HOPE', 'Denied', '2025-10-28 02:14:03', 0),
(97, 11, NULL, '2025-10-30', 8, 'Group', '', '4-HOPE', 'Approved', '2025-10-28 02:14:14', 0),
(98, 11, NULL, '2025-10-31', 8, 'Class', 'hands on', '4-HOPE', 'Pending', '2025-10-28 02:14:22', 0),
(99, 11, NULL, '2025-11-01', 8, 'Solo', 'research', '4-HOPE', 'Pending', '2025-10-28 02:14:33', 0),
(100, 11, NULL, '2025-11-02', 8, 'Group', 'research', '4-HOPE', 'Pending', '2025-10-28 02:14:42', 0),
(101, 13, NULL, '2025-10-29', 8, 'Solo', '', '4-Amethy', 'Approved', '2025-10-28 02:15:22', 0);

-- --------------------------------------------------------

--
-- Table structure for table `schedule`
--

CREATE TABLE `schedule` (
  `id` int(11) NOT NULL,
  `day_date` date DEFAULT NULL,
  `day` enum('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday') NOT NULL,
  `hour` tinyint(4) NOT NULL,
  `section` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `schedule`
--

INSERT INTO `schedule` (`id`, `day_date`, `day`, `hour`, `section`, `created_at`) VALUES
(1, '2025-09-07', 'Monday', 7, NULL, '2025-09-06 20:15:07'),
(2, '2025-09-07', 'Monday', 21, NULL, '2025-09-06 20:15:20'),
(3, '2025-09-07', 'Monday', 15, NULL, '2025-09-06 21:35:49'),
(4, '2025-09-07', 'Monday', 9, NULL, '2025-09-06 23:45:33'),
(5, '2025-09-09', 'Monday', 7, NULL, '2025-09-08 04:47:23'),
(6, '2025-09-10', 'Monday', 7, NULL, '2025-09-08 04:47:33'),
(7, '2025-09-11', 'Monday', 7, NULL, '2025-09-08 04:47:42'),
(8, '2025-09-11', 'Monday', 15, NULL, '2025-09-08 04:47:52'),
(9, '2025-09-12', 'Monday', 7, NULL, '2025-09-08 04:48:02'),
(10, '2025-09-10', 'Monday', 21, NULL, '2025-09-08 04:48:08'),
(11, '2025-09-11', 'Monday', 17, NULL, '2025-09-08 04:48:12'),
(12, '2025-09-12', 'Monday', 19, NULL, '2025-09-08 04:48:16'),
(13, '2025-09-14', 'Monday', 21, NULL, '2025-09-08 04:48:20'),
(14, '2025-09-08', 'Monday', 18, NULL, '2025-09-08 04:48:23'),
(15, '2025-09-10', 'Monday', 17, NULL, '2025-09-08 04:48:28'),
(16, '2025-09-13', 'Monday', 15, NULL, '2025-09-08 04:48:31'),
(17, '2025-09-14', 'Monday', 13, NULL, '2025-09-08 04:48:35'),
(18, '2025-09-11', 'Monday', 12, NULL, '2025-09-08 04:48:38'),
(19, '2025-09-09', 'Monday', 11, NULL, '2025-09-08 04:48:41'),
(20, '2025-09-09', 'Monday', 12, NULL, '2025-09-08 04:48:45'),
(21, '2025-09-09', 'Monday', 13, NULL, '2025-09-08 04:48:49'),
(22, '2025-09-13', 'Monday', 7, NULL, '2025-09-08 04:48:55'),
(23, '2025-09-13', 'Monday', 7, NULL, '2025-09-08 04:49:07'),
(24, '2025-09-20', 'Monday', 20, NULL, '2025-09-08 08:21:25'),
(25, '2025-09-09', 'Monday', 8, NULL, '2025-09-08 12:52:17'),
(26, '2025-09-17', 'Monday', 14, NULL, '2025-09-15 09:51:03'),
(27, '2025-09-16', 'Monday', 11, NULL, '2025-09-15 09:53:37'),
(28, '2025-09-17', 'Monday', 7, NULL, '2025-09-16 06:28:37'),
(29, '2025-09-21', 'Monday', 21, NULL, '2025-09-17 19:43:23'),
(30, '2025-09-21', 'Monday', 13, NULL, '2025-09-18 08:28:57'),
(31, '2025-09-21', 'Monday', 16, NULL, '2025-09-18 08:29:02'),
(32, '2025-09-28', 'Monday', 20, NULL, '2025-09-28 05:25:37'),
(33, '2025-09-28', 'Monday', 21, NULL, '2025-09-28 11:45:25'),
(34, '2025-09-30', 'Monday', 7, NULL, '2025-09-29 06:08:30'),
(35, '2025-09-30', 'Monday', 8, NULL, '2025-09-29 07:38:05'),
(36, '2025-09-30', 'Monday', 9, NULL, '2025-09-29 07:46:55'),
(37, '2025-09-30', 'Monday', 10, NULL, '2025-09-29 08:07:59'),
(38, '2025-09-30', 'Monday', 11, NULL, '2025-09-29 10:01:18'),
(39, '2025-10-08', 'Monday', 8, NULL, '2025-10-07 06:24:00'),
(40, '2025-10-09', 'Monday', 8, NULL, '2025-10-07 06:24:40'),
(41, '2025-10-10', 'Monday', 8, NULL, '2025-10-07 06:24:45'),
(42, '2025-10-11', 'Monday', 8, NULL, '2025-10-07 06:24:51'),
(43, '2025-10-12', 'Monday', 8, NULL, '2025-10-07 06:24:58'),
(44, '2025-10-10', 'Monday', 13, NULL, '2025-10-08 07:26:23'),
(45, '2025-10-09', 'Monday', 17, NULL, '2025-10-08 07:26:39'),
(46, '2025-10-09', 'Monday', 9, NULL, '2025-10-08 08:05:18'),
(47, '2025-10-09', 'Monday', 14, NULL, '2025-10-08 08:08:23'),
(48, '2025-10-09', 'Monday', 10, NULL, '2025-10-08 08:08:41'),
(49, '2025-10-09', 'Monday', 10, NULL, '2025-10-08 08:08:49'),
(50, '2025-10-09', 'Monday', 10, NULL, '2025-10-08 08:08:57'),
(51, '2025-10-09', 'Monday', 16, NULL, '2025-10-08 08:09:07'),
(52, '2025-10-09', 'Monday', 7, NULL, '2025-10-08 08:09:12'),
(53, '2025-10-12', 'Monday', 21, NULL, '2025-10-08 08:09:22'),
(54, '2025-10-09', 'Monday', 18, NULL, '2025-10-08 08:09:43'),
(55, '2025-10-11', 'Monday', 21, NULL, '2025-10-08 08:09:56'),
(56, '2025-10-10', 'Monday', 21, NULL, '2025-10-08 08:26:36'),
(57, '2025-10-10', 'Monday', 21, NULL, '2025-10-08 08:26:42'),
(58, '2025-10-10', 'Monday', 14, NULL, '2025-10-08 08:29:38'),
(59, '2025-10-10', 'Monday', 7, NULL, '2025-10-08 08:31:30'),
(60, '2025-10-11', 'Monday', 7, NULL, '2025-10-08 08:32:48'),
(61, '2025-10-12', 'Monday', 7, NULL, '2025-10-08 08:32:56'),
(62, '2025-10-12', 'Monday', 13, NULL, '2025-10-09 04:11:16'),
(63, '2025-10-09', 'Monday', 14, NULL, '2025-10-09 05:52:01'),
(64, '2025-10-23', 'Monday', 7, '4-HOPE', '2025-10-22 07:51:37'),
(65, '2025-10-23', 'Monday', 8, '1-AMETHY', '2025-10-22 07:52:43'),
(66, '2025-10-23', 'Monday', 9, '1-GLORY', '2025-10-22 07:53:19'),
(67, '2025-10-24', 'Monday', 7, '1-HOPE', '2025-10-22 07:54:18'),
(68, '2025-10-25', 'Monday', 7, '4-HOPE', '2025-10-22 07:54:28'),
(69, '2025-10-26', 'Monday', 7, '4-HOPE', '2025-10-22 07:54:35'),
(70, '2025-10-24', 'Monday', 8, '1-POSEIDON', '2025-10-22 07:55:10'),
(71, '2025-10-25', 'Monday', 8, '4-HOPE', '2025-10-24 09:09:02'),
(72, '2025-10-26', 'Monday', 8, '1-POSEIDON', '2025-10-24 09:09:17'),
(73, '2025-10-24', 'Monday', 18, '2-INSPIRE', '2025-10-24 09:09:34'),
(74, '2025-10-24', 'Monday', 19, '4-HOPE', '2025-10-24 09:09:44'),
(75, '2025-10-24', 'Monday', 20, '4-HOPE', '2025-10-24 09:09:56'),
(76, '2025-10-24', 'Monday', 21, '4-HOPE', '2025-10-24 09:10:02'),
(77, '2025-10-26', 'Monday', 9, '4-HOPE', '2025-10-24 09:11:01'),
(78, '2025-10-26', 'Monday', 10, '4-HOPE', '2025-10-24 09:11:06'),
(79, '2025-10-26', 'Monday', 11, '4-HOPE', '2025-10-24 09:11:10'),
(80, '2025-10-26', 'Monday', 12, '4-HOPE', '2025-10-24 09:11:15'),
(81, '2025-10-26', 'Monday', 13, '4-HOPE', '2025-10-24 09:11:19'),
(82, '2025-10-26', 'Monday', 14, '4-HOPE', '2025-10-24 09:11:24'),
(83, '2025-10-26', 'Monday', 15, '1-POSEIDON', '2025-10-24 09:11:36'),
(84, '2025-10-26', 'Monday', 16, '1-POSEIDON', '2025-10-24 09:11:41'),
(85, '2025-10-26', 'Monday', 17, '1-POSEIDON', '2025-10-24 09:11:46'),
(86, '2025-10-26', 'Monday', 18, '1-POSEIDON', '2025-10-24 09:11:51'),
(87, '2025-10-26', 'Monday', 19, '1-POSEIDON', '2025-10-24 09:11:56'),
(88, '2025-10-26', 'Monday', 20, '1-POSEIDON', '2025-10-24 09:12:01'),
(89, '2025-10-26', 'Monday', 21, '1-POSEIDON', '2025-10-24 09:12:06'),
(90, '2025-10-29', 'Monday', 7, '4-HOPE', '2025-10-28 02:01:27'),
(91, '2025-10-30', 'Monday', 7, '4-HOPE', '2025-10-28 02:01:32'),
(92, '2025-10-31', 'Monday', 7, '4-HOPE', '2025-10-28 02:01:36'),
(93, '2025-11-01', 'Monday', 7, '4-HOPE', '2025-10-28 02:01:41'),
(94, '2025-11-02', 'Monday', 7, '4-HOPE', '2025-10-28 02:01:46'),
(95, '2025-10-29', 'Monday', 9, '1-POSEIDON', '2025-10-28 02:02:03'),
(96, '2025-10-30', 'Monday', 9, '1-POSEIDON', '2025-10-28 02:02:09'),
(97, '2025-10-31', 'Monday', 9, '1-POSEIDON', '2025-10-28 02:02:15'),
(98, '2025-11-01', 'Monday', 9, '1-POSEIDON', '2025-10-28 02:02:23'),
(99, '2025-11-02', 'Monday', 9, '1-POSEIDON', '2025-10-28 02:02:28'),
(100, '2025-10-29', 'Monday', 11, '2-INSPIRE', '2025-10-28 02:02:41'),
(101, '2025-10-28', 'Monday', 11, '2-INSPIRE', '2025-10-28 02:02:46'),
(102, '2025-10-30', 'Monday', 11, '2-INSPIRE', '2025-10-28 02:02:52'),
(103, '2025-10-31', 'Monday', 11, '2-INSPIRE', '2025-10-28 02:02:56'),
(104, '2025-11-01', 'Monday', 11, '2-INSPIRE', '2025-10-28 02:03:00'),
(105, '2025-11-02', 'Monday', 11, '2-INSPIRE', '2025-10-28 02:03:07'),
(106, '2025-10-28', 'Monday', 13, '3-KIND', '2025-10-28 02:05:14'),
(107, '2025-10-29', 'Monday', 13, '3-KIND', '2025-10-28 02:05:21'),
(108, '2025-10-30', 'Monday', 13, '3-KIND', '2025-10-28 02:05:26'),
(109, '2025-10-31', 'Monday', 13, '3-KIND', '2025-10-28 02:05:31'),
(110, '2025-11-01', 'Monday', 13, '3-KIND', '2025-10-28 02:05:37'),
(111, '2025-11-02', 'Monday', 13, '3-KIND', '2025-10-28 02:06:15'),
(112, '2025-10-28', 'Monday', 15, '2-AMETHY', '2025-10-28 02:06:29'),
(113, '2025-10-29', 'Monday', 15, '2-AMETHY', '2025-10-28 02:06:34'),
(114, '2025-10-30', 'Monday', 15, '2-AMETHY', '2025-10-28 02:06:39'),
(115, '2025-10-31', 'Monday', 15, '2-AMETHY', '2025-10-28 02:06:44'),
(116, '2025-11-01', 'Monday', 15, '2-AMETHY', '2025-10-28 02:06:49'),
(117, '2025-11-02', 'Monday', 15, '2-AMETHY', '2025-10-28 02:06:55'),
(118, '2025-10-28', 'Monday', 17, '4-JOY', '2025-10-28 02:07:31'),
(119, '2025-10-29', 'Monday', 17, '4-JOY', '2025-10-28 02:07:36'),
(120, '2025-10-30', 'Monday', 17, '4-JOY', '2025-10-28 02:07:42'),
(121, '2025-10-31', 'Monday', 17, '4-JOY', '2025-10-28 02:07:49'),
(122, '2025-11-01', 'Monday', 17, '4-JOY', '2025-10-28 02:07:54'),
(123, '2025-11-02', 'Monday', 17, '4-JOY', '2025-10-28 02:07:59');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `section` varchar(100) NOT NULL,
  `classification` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `section`, `classification`, `created_at`) VALUES
(11, 'Allen', '4-HOPE', 'Faculty', '2025-10-22 08:23:01'),
(12, 'gwapa', '5-hope', 'Faculty', '2025-10-22 08:31:20'),
(13, 'charm', '4-Amethy', 'Teacher', '2025-10-28 02:15:10');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `reservations`
--
ALTER TABLE `reservations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `schedule_id` (`schedule_id`);

--
-- Indexes for table `schedule`
--
ALTER TABLE `schedule`
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
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `reservations`
--
ALTER TABLE `reservations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=102;

--
-- AUTO_INCREMENT for table `schedule`
--
ALTER TABLE `schedule`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=124;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `reservations`
--
ALTER TABLE `reservations`
  ADD CONSTRAINT `reservations_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reservations_ibfk_2` FOREIGN KEY (`schedule_id`) REFERENCES `schedule` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

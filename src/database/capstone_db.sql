-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 15, 2026 at 05:33 PM
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
-- Database: `capstone_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `archives`
--

CREATE TABLE `archives` (
  `archive_id` int(11) NOT NULL,
  `original_id` int(11) NOT NULL,
  `type` enum('Report','Message') NOT NULL,
  `sender_name` varchar(255) DEFAULT NULL,
  `content` text DEFAULT NULL,
  `reason_type` varchar(100) DEFAULT NULL,
  `archived_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `archives`
--

INSERT INTO `archives` (`archive_id`, `original_id`, `type`, `sender_name`, `content`, `reason_type`, `archived_at`) VALUES
(1, 25, 'Message', 'cayoh anicete', 'hi', NULL, '2026-02-12 15:33:00'),
(2, 33, 'Report', 'Cayoh Anicete', 'report', 'Verbal Abuse', '2026-02-12 15:33:23'),
(3, 33, 'Report', 'Cayoh Anicete', 'report', 'Verbal Abuse', '2026-02-12 16:19:07'),
(4, 33, 'Report', 'Cayoh Anicete', 'report', 'Verbal Abuse', '2026-02-12 16:19:12'),
(5, 33, 'Report', 'Cayoh Anicete', 'report', 'Verbal Abuse', '2026-02-12 16:19:14'),
(6, 33, '', 'Ariana Punsalang', 'asdasdasd', NULL, '2026-02-12 16:21:55'),
(7, 24, 'Message', 'Vladimir Borja', 'hi', NULL, '2026-02-12 16:27:31'),
(8, 20, '', 'Kristine Tuazon', '[MISSING DOG] Name: otlum | Last Seen: test | Contact: 12313131', NULL, '2026-02-13 11:26:05');

-- --------------------------------------------------------

--
-- Table structure for table `contact_messages`
--

CREATE TABLE `contact_messages` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `contact` varchar(50) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contact_messages`
--

INSERT INTO `contact_messages` (`id`, `name`, `email`, `contact`, `subject`, `message`, `created_at`) VALUES
(1, ' ', '', '', '', '', '2026-02-08 18:42:02'),
(2, '', '', '', '', '', '2026-02-08 18:45:32'),
(3, '', '', '', '', '', '2026-02-08 18:49:58'),
(4, 'kristine tuazon', 'kristinetuazon16@gmail.com', '3821', 'CAPSTONE', '3:00AM', '2026-02-08 18:57:34'),
(5, 'Ariana Punsalang', 'anairadump@gmail.com', '0929384194', 'MATH', '3;06am', '2026-02-08 19:00:39'),
(6, 'Faith Guevarra', 'Faye@gmail.com', '09318424195', 'Testing', 'BDADNDJFJAJLA AFJAJFL HSDAJD SDAJD', '2026-02-08 19:06:14'),
(7, 'kristine Guevarra', 'kristinetuazon16@gmail.com123', '4690-', 'FGK.L', 'HHKLKM KKJKLM VFUIPOM NKLN', '2026-02-08 19:07:46'),
(8, 'asdafd fdsfsdf', 'Faye@gmail.com', '0986543213', 'CAPSTONE', 'hsand', '2026-02-09 09:38:36'),
(9, 'fsaf ffs', 'kristinetuazon11@yahoo.com', '023', 'fhbs', 'jfdbnjsd', '2026-02-09 09:39:31'),
(10, 'Ariana Punsalang', 'anairadump@gmail.com', '098668', 'CAPSTONE', 'ndand fsanfmask kfs', '2026-02-09 09:43:54'),
(11, 'vlad BORJA', 'vlad@gmail.com', '0832831', 'HFA', 'DFSJF', '2026-02-09 10:03:24'),
(12, 'sda sjfh', 'fdsjnf@gmail.com', '938', 'hjafd', 'fjds', '2026-02-09 10:11:56'),
(13, 'sda sjfh', 'fdsjnf@gmail.com', '938', 'hjafd', 'fjds', '2026-02-09 10:12:26'),
(14, 'cafe beni', 'cafe@gmail.com', '09856476567', 'CAPSTONE', 'sdhad had', '2026-02-09 10:22:18'),
(15, 'cafe beni', 'cafe@gmail.com', '090867677', 'CAPSTONE', 'nakooo', '2026-02-09 10:23:03'),
(16, 'Ariana Punsalang', 'anairadump@gmail.com', '09218372', 'CAPSTONE', 'working', '2026-02-09 10:24:02'),
(17, 'cafe beni', 'cafe@gmail.com', '0823', 'Testing', 'WORKINGG??', '2026-02-09 10:24:32'),
(18, 'cafe beni', 'cafe@gmail.com', '83291', 'Testing ULIT', 'haha', '2026-02-09 10:25:35'),
(19, 'kristine tuazon', 'kristinetuazon16@gmail.com', '98', 'Testing', 'swal', '2026-02-09 10:29:12'),
(20, 'DAHS SDHA', 'kristinetuazon16@gmail.com', '6762880', 'DHASJ', 'DASNM,', '2026-02-09 10:34:25'),
(21, 'cafe beni', 'cafe@gmail.com', '67890', 'Testing ULIT', 'HAYY', '2026-02-09 10:34:56'),
(22, 'cafe beni', 'cafe@gmail.com', '64782930-', 'TESTING', 'TESTING SWAL', '2026-02-09 10:38:23'),
(23, 'Vladimir Borja', 'vladimirborja013@gmail.com', '09999999', 'test', 'hi', '2026-02-11 03:12:11');

-- --------------------------------------------------------

--
-- Table structure for table `establishments`
--

CREATE TABLE `establishments` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `requester_id` int(11) DEFAULT NULL,
  `status` enum('active','pending') NOT NULL,
  `type` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `address` varchar(255) NOT NULL,
  `latitude` decimal(10,8) NOT NULL,
  `longitude` decimal(11,8) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `login_history`
--

CREATE TABLE `login_history` (
  `login_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `login_time` timestamp NOT NULL DEFAULT current_timestamp(),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `login_status` enum('success','failed') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `login_history`
--

INSERT INTO `login_history` (`login_id`, `user_id`, `login_time`, `ip_address`, `user_agent`, `login_status`) VALUES
(3, 4, '2026-01-29 18:02:11', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'success'),
(4, 5, '2026-01-29 18:09:43', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'success'),
(5, 5, '2026-01-29 18:18:13', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'success'),
(6, 6, '2026-02-03 06:36:24', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'success'),
(7, 7, '2026-02-03 06:42:09', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'success'),
(8, 7, '2026-02-03 09:19:23', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'success'),
(9, 7, '2026-02-03 12:00:59', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'success'),
(10, 5, '2026-02-03 14:53:41', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'success'),
(11, 7, '2026-02-03 15:04:39', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'success'),
(12, 7, '2026-02-03 15:05:57', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'success'),
(13, 5, '2026-02-03 15:40:35', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'success'),
(14, 7, '2026-02-03 15:40:51', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'success'),
(15, 5, '2026-02-03 15:44:21', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'success'),
(16, 5, '2026-02-03 15:45:42', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'success'),
(17, 5, '2026-02-03 15:45:57', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'success'),
(18, 7, '2026-02-03 15:58:32', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'success'),
(19, 5, '2026-02-03 15:59:22', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'success'),
(20, 7, '2026-02-03 16:18:06', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'success'),
(21, 5, '2026-02-03 16:20:03', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'success'),
(22, 7, '2026-02-03 16:40:34', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'success'),
(23, 5, '2026-02-03 17:04:55', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'success'),
(24, 7, '2026-02-03 17:05:13', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'success'),
(25, 7, '2026-02-03 17:08:16', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'success'),
(26, 7, '2026-02-03 17:08:35', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'success'),
(27, 5, '2026-02-03 17:11:37', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'success'),
(28, 7, '2026-02-03 17:16:05', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'success'),
(29, 7, '2026-02-06 12:23:15', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'success'),
(30, 7, '2026-02-06 13:39:04', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'success'),
(31, 7, '2026-02-06 17:00:09', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'success'),
(32, 6, '2026-02-06 17:00:59', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'success'),
(33, 6, '2026-02-06 17:48:49', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'success'),
(34, 6, '2026-02-06 18:13:00', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'success'),
(35, 7, '2026-02-06 18:13:20', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'success'),
(36, 6, '2026-02-06 18:13:46', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'success'),
(37, 7, '2026-02-06 19:14:56', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'success'),
(38, 6, '2026-02-06 19:21:07', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'success'),
(39, 7, '2026-02-06 19:21:25', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'success'),
(40, 6, '2026-02-06 19:22:47', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'success'),
(41, 6, '2026-02-06 19:28:40', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'success'),
(42, 7, '2026-02-06 19:30:50', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'success'),
(43, 8, '2026-02-06 19:33:25', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'success'),
(44, 7, '2026-02-06 20:23:54', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'success'),
(45, 7, '2026-02-06 20:28:19', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'success'),
(46, 7, '2026-02-06 20:37:13', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'success'),
(47, 6, '2026-02-06 20:38:02', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'success'),
(48, 5, '2026-02-06 20:45:45', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'success'),
(49, 7, '2026-02-06 20:47:49', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'success'),
(50, 7, '2026-02-06 21:09:25', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'success'),
(51, 6, '2026-02-06 21:11:35', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'success'),
(52, 6, '2026-02-06 21:21:01', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'success'),
(53, 7, '2026-02-06 21:22:37', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'success'),
(54, 7, '2026-02-08 16:07:52', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'success'),
(55, 7, '2026-02-08 16:26:06', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'success'),
(56, 7, '2026-02-09 09:38:11', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'success'),
(57, 5, '2026-02-09 10:41:47', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'success'),
(58, 5, '2026-02-09 10:48:43', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'success'),
(59, 6, '2026-02-10 09:37:50', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'success'),
(60, 7, '2026-02-10 09:44:23', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'success'),
(61, 7, '2026-02-10 09:53:35', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'success'),
(62, 6, '2026-02-10 10:06:23', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'success'),
(63, 6, '2026-02-10 10:17:57', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'success'),
(64, 7, '2026-02-11 03:05:51', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'success'),
(65, 6, '2026-02-11 03:06:49', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'success'),
(66, 5, '2026-02-11 03:08:52', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'success'),
(67, 9, '2026-02-11 03:10:37', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'success'),
(68, 7, '2026-02-11 03:17:13', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'success'),
(69, 6, '2026-02-11 03:19:49', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'success'),
(70, 5, '2026-02-11 03:20:48', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'success'),
(71, 7, '2026-02-11 06:26:59', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'success'),
(72, 6, '2026-02-11 06:28:54', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'success'),
(73, 10, '2026-02-11 06:31:48', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'success'),
(74, 6, '2026-02-11 07:09:10', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'success'),
(75, 7, '2026-02-11 07:49:06', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'success'),
(76, 6, '2026-02-11 07:49:30', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'success'),
(77, 9, '2026-02-11 07:50:46', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'success'),
(78, 6, '2026-02-11 07:51:06', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'success'),
(79, 9, '2026-02-11 07:51:46', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'success'),
(80, 6, '2026-02-11 08:46:11', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'success'),
(81, 9, '2026-02-11 08:46:29', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'success'),
(82, 5, '2026-02-11 09:07:57', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'success'),
(83, 6, '2026-02-11 09:08:21', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'success'),
(84, 11, '2026-02-11 09:13:53', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'success'),
(85, 6, '2026-02-11 09:18:57', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'success'),
(86, 11, '2026-02-11 09:20:59', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'success'),
(87, 5, '2026-02-11 09:47:06', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'success'),
(88, 5, '2026-02-11 10:06:21', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'success'),
(89, 6, '2026-02-11 10:06:55', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'success'),
(90, 6, '2026-02-11 12:01:22', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'success'),
(91, 7, '2026-02-11 12:02:16', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'success'),
(92, 6, '2026-02-11 12:02:38', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'success'),
(93, 7, '2026-02-11 12:14:20', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'success'),
(94, 6, '2026-02-11 12:14:41', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'success'),
(95, 5, '2026-02-11 14:30:40', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'success'),
(96, 9, '2026-02-11 15:55:10', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'success'),
(97, 9, '2026-02-11 16:13:53', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'success'),
(98, 9, '2026-02-11 16:14:49', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'success'),
(99, 5, '2026-02-11 16:15:38', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'success'),
(100, 7, '2026-02-11 16:28:26', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'success'),
(101, 6, '2026-02-12 01:30:47', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'success'),
(102, 7, '2026-02-12 01:31:37', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'success'),
(103, 6, '2026-02-12 01:35:32', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'success'),
(104, 7, '2026-02-12 01:43:27', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'success'),
(105, 6, '2026-02-12 01:52:58', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'success'),
(106, 9, '2026-02-12 02:38:56', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'success'),
(107, 5, '2026-02-12 03:22:21', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'success'),
(108, 9, '2026-02-12 03:22:43', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'success'),
(109, 9, '2026-02-12 11:32:12', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'success'),
(110, 5, '2026-02-12 12:23:41', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'success'),
(111, 9, '2026-02-12 12:24:03', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'success'),
(112, 5, '2026-02-12 12:31:32', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'success'),
(113, 5, '2026-02-12 12:39:15', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'success'),
(114, 5, '2026-02-12 12:40:27', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'success'),
(115, 5, '2026-02-12 15:03:55', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'success'),
(116, 9, '2026-02-12 15:24:33', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'success'),
(117, 6, '2026-02-12 16:24:28', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'success'),
(118, 5, '2026-02-12 16:25:57', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'success'),
(119, 5, '2026-02-12 16:26:40', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'success'),
(120, 5, '2026-02-12 16:31:08', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'success'),
(121, 9, '2026-02-12 16:34:37', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'success'),
(122, 5, '2026-02-12 16:37:09', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'success'),
(123, 5, '2026-02-12 16:39:37', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'success'),
(124, 9, '2026-02-12 16:55:31', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'success'),
(125, 5, '2026-02-12 16:55:54', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'success'),
(126, 9, '2026-02-12 17:02:45', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'success'),
(127, 5, '2026-02-12 17:03:24', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'success'),
(128, 9, '2026-02-12 17:13:32', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'success'),
(129, 9, '2026-02-12 17:24:07', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'success'),
(130, 5, '2026-02-12 17:31:08', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'success'),
(131, 5, '2026-02-13 10:57:58', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'success'),
(132, 5, '2026-02-13 11:00:10', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'success'),
(133, 5, '2026-02-13 11:00:43', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'success'),
(134, 5, '2026-02-13 11:01:29', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'success'),
(135, 2, '2026-02-13 12:26:34', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'success'),
(136, 5, '2026-02-13 14:02:14', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'success'),
(137, 2, '2026-02-13 15:13:51', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'success'),
(138, 5, '2026-02-13 15:16:23', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'success'),
(139, 5, '2026-02-14 12:35:10', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'success'),
(140, 2, '2026-02-15 03:55:24', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'success'),
(141, 5, '2026-02-15 03:55:56', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'success'),
(142, 2, '2026-02-15 04:35:29', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'success'),
(143, 5, '2026-02-15 05:07:12', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'success'),
(144, 5, '2026-02-15 06:18:07', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'success'),
(145, 2, '2026-02-15 07:06:58', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'success'),
(146, 5, '2026-02-15 07:08:11', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'success'),
(147, 2, '2026-02-15 07:29:13', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'success'),
(148, 2, '2026-02-15 07:30:05', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'success'),
(149, 9, '2026-02-15 07:31:06', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'success'),
(150, 2, '2026-02-15 08:58:17', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'success'),
(151, 2, '2026-02-15 09:29:35', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'success');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `post_id`, `user_id`, `message`, `is_read`, `created_at`) VALUES
(1, 34, 9, 'Vlad Borja liked your post.', 0, '2026-02-15 07:29:26'),
(2, 34, 9, 'Vlad Borja liked your post.', 0, '2026-02-15 07:30:09'),
(3, 34, 9, 'Vlad Borja commented: \"312321312...\"', 0, '2026-02-15 07:30:33');

-- --------------------------------------------------------

--
-- Table structure for table `pets`
--

CREATE TABLE `pets` (
  `pet_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `pet_name` varchar(100) NOT NULL,
  `category` enum('Lost','Found','Pending') NOT NULL,
  `gender` varchar(20) DEFAULT NULL,
  `breed` varchar(100) DEFAULT 'Unknown',
  `color` varchar(50) DEFAULT 'Not Specified',
  `last_seen_location` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `image_url` varchar(255) DEFAULT 'uploads/default_pet.png',
  `contact_number` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pets`
--

INSERT INTO `pets` (`pet_id`, `user_id`, `pet_name`, `category`, `gender`, `breed`, `color`, `last_seen_location`, `description`, `image_url`, `contact_number`, `created_at`) VALUES
(19, 11, 'tsesf', 'Pending', 'Male', 'Dog', 'brown', 'Test', 'adadaw', 'uploads/1770802913_sinoo.gif', '3131312', '2026-02-11 09:41:53'),
(21, 6, 'sino ka', 'Found', 'Male', 'Dog', 'black', 'angeles', 'nawawala', 'uploads/1770811765_sino.jpg', '09123456789', '2026-02-11 12:09:25'),
(22, 6, 'muwah', 'Found', 'Unknown', 'bakla', 'black', 'dito lang', 'aaa', 'uploads/1770811788_heart.jpg', '09999999', '2026-02-11 12:09:48'),
(23, 6, 'otlum', 'Pending', 'Male', 'Dog', 'black', 'angeles', 'asd', 'uploads/1770861809_heart.jpg', '09123456789', '2026-02-12 02:03:29'),
(24, 9, 'otlum', 'Lost', 'Female', 'Dog', 'white', 'sanfer', 'hi', 'uploads/1770865886_otlum.jpg', '09123456789', '2026-02-12 03:11:26');

-- --------------------------------------------------------

--
-- Table structure for table `posts`
--

CREATE TABLE `posts` (
  `post_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `content` text NOT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `posts`
--

INSERT INTO `posts` (`post_id`, `user_id`, `content`, `image_url`, `created_at`) VALUES
(3, 6, 'sino to', 'uploads/1770404670_otlum.jpg', '2026-02-06 19:04:30'),
(6, 7, 'spiderman\\\\\\\\r\\\\\\\\nsssss', 'uploads/1770409060_GyiYtEVbMAAn5Tc.jpg', '2026-02-06 20:17:40'),
(8, 7, '[MISSING DOG] Name: margiel | Last Seen: dito lang | Contact: 123', NULL, '2026-02-06 20:28:00'),
(11, 7, '[MISSING DOG] Name: margiel | Last Seen: dito lang | Contact: 123asdasda', 'uploads/missing_1770410244_526744371_734164873116640_6121404047598711197_n.png', '2026-02-06 20:37:24'),
(12, 7, '[MISSING DOG] Name: margielaaa | Last Seen: dito lang | Contact: 123', 'uploads/missing_1770410882_GyiYtEVbMAAn5Tc.jpg', '2026-02-06 20:48:02'),
(14, 7, '[MISSING DOG] Name: margielaaaaaaaaa | Last Seen: dito langaaaa | Contact: 0920 ikaw na bahala sa pito ejrer', 'uploads/missing_1770412209_526744371_734164873116640_6121404047598711197_n.png', '2026-02-06 21:10:09'),
(15, 7, 'hey', 'uploads/1770715114_sinoo.gif', '2026-02-10 09:18:34'),
(16, 7, 'bbbb e', 'uploads/1770717654_sinoo.gif', '2026-02-10 10:00:54'),
(17, 6, '[MISSING DOG] Nam1e: margiel | Last Seen: dito lang | Contact: 123', 'uploads/missing_1770718695_otlum.jpg', '2026-02-10 10:18:15'),
(18, 7, 'test1', 'uploads/1770779161_sinoo.gif', '2026-02-11 03:06:01'),
(21, 7, 'test', 'uploads/1770791314_otlum.jpg', '2026-02-11 06:28:34'),
(29, 7, 'hi', '', '2026-02-12 01:52:23'),
(30, 7, 'hi', 'uploads/1770861151_Borja (Assignment_ How globalization affects religious beliefs and practices).pdf', '2026-02-12 01:52:31'),
(34, 9, 'hi', 'uploads/1770913450_heart.jpg', '2026-02-12 16:24:10');

-- --------------------------------------------------------

--
-- Table structure for table `post_comments`
--

CREATE TABLE `post_comments` (
  `comment_id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `comment_text` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `post_comments`
--

INSERT INTO `post_comments` (`comment_id`, `post_id`, `user_id`, `comment_text`, `created_at`) VALUES
(2, 21, 10, 'test 2', '2026-02-11 06:43:41'),
(18, 21, 6, 'bababa', '2026-02-11 12:43:30'),
(25, 21, 7, 'bababa', '2026-02-11 17:40:00'),
(28, 34, 5, 'a', '2026-02-15 04:19:07'),
(29, 34, 2, '312321312', '2026-02-15 07:30:33');

-- --------------------------------------------------------

--
-- Table structure for table `post_likes`
--

CREATE TABLE `post_likes` (
  `like_id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `post_likes`
--

INSERT INTO `post_likes` (`like_id`, `post_id`, `user_id`, `created_at`) VALUES
(2, 21, 10, '2026-02-11 06:43:22'),
(4, 18, 10, '2026-02-11 06:43:25'),
(12, 21, 6, '2026-02-11 12:43:27'),
(18, 21, 7, '2026-02-11 17:39:58'),
(23, 34, 2, '2026-02-15 07:30:09');

-- --------------------------------------------------------

--
-- Table structure for table `post_reports`
--

CREATE TABLE `post_reports` (
  `report_id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `report_type` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `post_reports`
--

INSERT INTO `post_reports` (`report_id`, `post_id`, `user_id`, `report_type`, `description`, `created_at`) VALUES
(3, 3, 7, 'Hate Speech', 'asd', '2026-02-06 20:05:32'),
(4, 3, 7, 'Inappropriate Content', 'aa', '2026-02-06 20:08:59'),
(5, 3, 7, 'Spam', 'a', '2026-02-06 20:12:11'),
(6, 3, 7, 'Verbal Abuse', 'asd', '2026-02-06 20:15:56'),
(11, 16, 6, 'Fake Content', 'a', '2026-02-10 10:18:45'),
(13, 18, 9, 'Fake Content', 'hhi', '2026-02-11 03:11:12'),
(15, 21, 6, 'Fake Content', 'testnow', '2026-02-11 06:30:18'),
(35, 30, 6, 'Inappropriate', 'dwqeqwe', '2026-02-12 01:53:26'),
(38, 29, 6, 'Fake Content', 'heh', '2026-02-12 01:57:09');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `username` varchar(100) DEFAULT NULL,
  `is_verified` tinyint(1) NOT NULL,
  `bio` text NOT NULL,
  `email` varchar(255) NOT NULL,
  `verification_code` varchar(100) NOT NULL,
  `phone_number` varchar(20) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('user','admin') NOT NULL DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `last_login` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `profile_image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `full_name`, `username`, `is_verified`, `bio`, `email`, `verification_code`, `phone_number`, `password`, `role`, `created_at`, `updated_at`, `last_login`, `is_active`, `profile_image`) VALUES
(2, 'Vlad Borja', NULL, 0, '', 'vladimirborja298@gmail.com', '', '09123456789', '$2y$10$vjFICjPhuwKCKfhCO1gBveIwW3zhneTy99WAV5IfBAtd7ShMcUPja', 'user', '2026-01-29 17:38:50', '2026-02-15 09:29:35', '2026-02-15 09:29:35', 1, NULL),
(4, 'margiel escalante', NULL, 0, '', 'info@bb88advertising.com', '', '09650561211', '$2y$10$fADemELEvOqwjJHIqak8..aZpgbW4WpskH3Tml2kxVEgJU2Y3M5Mi', 'user', '2026-01-29 18:00:50', '2026-01-29 18:02:11', '2026-01-29 18:02:11', 1, NULL),
(5, 'Karl Vladimir Borjaa', 'Karlaaa', 0, 'SINO TO?!', 'vladimirborja013@gmail.com', '', '09650561211', '$2y$10$vjFICjPhuwKCKfhCO1gBveIwW3zhneTy99WAV5IfBAtd7ShMcUPja', 'admin', '2026-01-29 18:09:12', '2026-02-15 07:08:11', '2026-02-15 07:08:11', 1, '../uploads/profile_pics/user_5_1770917682.png'),
(6, 'Ariana Punsalang', NULL, 0, '', 'anairadump@gmail.com', '', '09915676315', '$2y$10$/KwFozircYcrk5Vpi2oaoO06.jvnzmD0E5AtTkNOXtOoDLsB7S8tO', 'user', '2026-02-03 06:36:07', '2026-02-12 16:24:28', '2026-02-12 16:24:28', 1, NULL),
(7, 'Kristine Tuazon', NULL, 0, 'bioooo', 'kristinetuazon16@gmail.com', '', '09318424195', '$2y$10$Js0vpcHQpVXsUSAHj22kF.b5gOPTckaIMYjwS9nazPKEnPXvJgWui', 'user', '2026-02-03 06:41:44', '2026-02-12 01:43:27', '2026-02-12 01:43:27', 1, NULL),
(8, 'Mico Cuenco', NULL, 0, '', 'micocuenco@gmail.com', '', '09123456789', '$2y$10$5TbZ4Vpu14roc6HtE2ijzeRSQjL3QQxHxdfK9LNe6CduMDB1wro9O', 'user', '2026-02-06 19:32:34', '2026-02-06 19:33:25', '2026-02-06 19:33:25', 1, NULL),
(9, 'Cayoh Anicete', NULL, 0, '', 'cayohanicete@gmail.com', '', '09123456789', '$2y$10$vjFICjPhuwKCKfhCO1gBveIwW3zhneTy99WAV5IfBAtd7ShMcUPja', 'user', '2026-02-11 03:10:13', '2026-02-15 07:31:06', '2026-02-15 07:31:06', 1, NULL),
(10, 'margiel escalante', NULL, 0, '', 'margielescalante@gmail.com', '', '09123456789', '$2y$10$tJ6ojSH5oqJUchJHtZDGbuMJOKjQADmyrlhiP01MQfVfB4Fbbdcn.', 'user', '2026-02-11 06:26:03', '2026-02-11 06:31:48', '2026-02-11 06:31:48', 1, NULL),
(11, 'Christian Aguas', NULL, 0, '', 'acdeocera.bb88@gmail.com', '', '09201172065', '$2y$10$OIYShiZROiBhCVG3/k1nROqr8aQhykXnTTsa9I9QlFNxv2ns4jqKS', 'user', '2026-02-11 09:13:39', '2026-02-12 17:01:12', '2026-02-11 09:20:59', 1, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `archives`
--
ALTER TABLE `archives`
  ADD PRIMARY KEY (`archive_id`);

--
-- Indexes for table `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `establishments`
--
ALTER TABLE `establishments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `requester_id` (`requester_id`);

--
-- Indexes for table `login_history`
--
ALTER TABLE `login_history`
  ADD PRIMARY KEY (`login_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_login_time` (`login_time`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `post_id` (`post_id`);

--
-- Indexes for table `pets`
--
ALTER TABLE `pets`
  ADD PRIMARY KEY (`pet_id`),
  ADD KEY `fk_pet_user` (`user_id`);

--
-- Indexes for table `posts`
--
ALTER TABLE `posts`
  ADD PRIMARY KEY (`post_id`),
  ADD KEY `fk_user_post` (`user_id`);

--
-- Indexes for table `post_comments`
--
ALTER TABLE `post_comments`
  ADD PRIMARY KEY (`comment_id`),
  ADD KEY `fk_comments_post` (`post_id`),
  ADD KEY `fk_comments_user` (`user_id`);

--
-- Indexes for table `post_likes`
--
ALTER TABLE `post_likes`
  ADD PRIMARY KEY (`like_id`),
  ADD UNIQUE KEY `unique_like` (`post_id`,`user_id`),
  ADD KEY `fk_likes_user` (`user_id`);

--
-- Indexes for table `post_reports`
--
ALTER TABLE `post_reports`
  ADD PRIMARY KEY (`report_id`),
  ADD KEY `fk_report_post` (`post_id`),
  ADD KEY `fk_report_user` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_phone` (`phone_number`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `archives`
--
ALTER TABLE `archives`
  MODIFY `archive_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `contact_messages`
--
ALTER TABLE `contact_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `establishments`
--
ALTER TABLE `establishments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `login_history`
--
ALTER TABLE `login_history`
  MODIFY `login_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=152;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `pets`
--
ALTER TABLE `pets`
  MODIFY `pet_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `posts`
--
ALTER TABLE `posts`
  MODIFY `post_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `post_comments`
--
ALTER TABLE `post_comments`
  MODIFY `comment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `post_likes`
--
ALTER TABLE `post_likes`
  MODIFY `like_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `post_reports`
--
ALTER TABLE `post_reports`
  MODIFY `report_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=73;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `establishments`
--
ALTER TABLE `establishments`
  ADD CONSTRAINT `establishments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `establishments_ibfk_2` FOREIGN KEY (`requester_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `login_history`
--
ALTER TABLE `login_history`
  ADD CONSTRAINT `login_history_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `notifications_ibfk_2` FOREIGN KEY (`post_id`) REFERENCES `posts` (`post_id`);

--
-- Constraints for table `pets`
--
ALTER TABLE `pets`
  ADD CONSTRAINT `fk_pet_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `posts`
--
ALTER TABLE `posts`
  ADD CONSTRAINT `fk_user_post` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `post_comments`
--
ALTER TABLE `post_comments`
  ADD CONSTRAINT `fk_comments_post` FOREIGN KEY (`post_id`) REFERENCES `posts` (`post_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_comments_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `post_likes`
--
ALTER TABLE `post_likes`
  ADD CONSTRAINT `fk_likes_post` FOREIGN KEY (`post_id`) REFERENCES `posts` (`post_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_likes_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `post_reports`
--
ALTER TABLE `post_reports`
  ADD CONSTRAINT `fk_report_post` FOREIGN KEY (`post_id`) REFERENCES `posts` (`post_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_report_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

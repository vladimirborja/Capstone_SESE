-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 27, 2026 at 08:23 PM
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
-- Table structure for table `admin_logs`
--

CREATE TABLE `admin_logs` (
  `log_id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `admin_name` varchar(255) NOT NULL,
  `affected_user_id` int(11) DEFAULT NULL,
  `affected_user_name` varchar(255) DEFAULT NULL,
  `action_type` varchar(80) NOT NULL,
  `old_status` varchar(80) DEFAULT NULL,
  `new_status` varchar(80) DEFAULT NULL,
  `reason` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_logs`
--

INSERT INTO `admin_logs` (`log_id`, `admin_id`, `admin_name`, `affected_user_id`, `affected_user_name`, `action_type`, `old_status`, `new_status`, `reason`, `created_at`) VALUES
(1, 5, 'Karl Vladimir Borjaa', 7, 'Kristine Tuazon', 'user_role_update', 'admin', 'user', 'd', '2026-02-27 17:39:14'),
(2, 5, 'Karl Vladimir Borjaa', 7, 'Kristine Tuazon', 'establishment_rejection', 'pending', 'rejected', 'Rejected establishment \"marquee\". Reason: bad', '2026-02-27 17:48:29'),
(3, 5, 'Karl Vladimir Borjaa', 7, 'Kristine Tuazon', 'establishment_approval', 'pending', 'approved', 'Approved establishment: SM Clark', '2026-02-27 17:48:35'),
(4, 5, 'Karl Vladimir Borjaa', 7, 'Kristine Tuazon', 'establishment_approval', 'pending', 'approved', 'Approved establishment: marquee', '2026-02-27 17:50:56'),
(5, 5, 'Karl Vladimir Borjaa', 7, 'Kristine Tuazon', 'establishment_rejection', 'pending', 'rejected', 'Rejected establishment \"SM Clark\". Reason: test', '2026-02-27 18:00:39'),
(6, 5, 'Karl Vladimir Borjaa', 7, 'Kristine Tuazon', 'pet_post_approval', 'waiting_approval', 'lost', 'Approved pet post: otlum (LOST)', '2026-02-27 19:15:25'),
(7, 5, 'Karl Vladimir Borjaa', 7, 'Kristine Tuazon', 'pet_post_rejection', 'waiting_approval', 'rejected', 'Rejected pet post: tsesf. Reason: test', '2026-02-27 19:16:05'),
(8, 5, 'Karl Vladimir Borjaa', 7, 'Kristine Tuazon', 'pet_post_approval', 'waiting_approval', 'for_adoption', 'Approved pet post: muwah (FOR_ADOPTION)', '2026-02-27 19:16:51');

-- --------------------------------------------------------

--
-- Table structure for table `adoption_notifications`
--

CREATE TABLE `adoption_notifications` (
  `notification_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `adoption_notifications`
--

INSERT INTO `adoption_notifications` (`notification_id`, `user_id`, `message`, `is_read`, `created_at`) VALUES
(1, 7, 'Karl Vladimir Borjaa sent an adoption request for your pet post.', 0, '2026-02-27 17:09:49'),
(2, 5, 'Your adoption request for micophobia was approved. Contact owner: Kristine Tuazon (09318424195).', 0, '2026-02-27 17:10:34'),
(3, 7, 'Karl Vladimir Borjaa sent an adoption request for your pet post.', 0, '2026-02-27 17:11:24'),
(4, 5, 'Your adoption request for otlum was declined. Reason: test', 0, '2026-02-27 17:11:32'),
(5, 7, 'Karl Vladimir Borjaa sent an adoption request for your pet post.', 0, '2026-02-27 17:19:06'),
(6, 5, 'Your adoption request for muwah was approved. Contact owner: Kristine Tuazon (09318424195).', 0, '2026-02-27 17:19:14'),
(7, 5, 'Kristine Tuazon sent an adoption request for your pet post.', 0, '2026-02-27 17:19:52'),
(8, 7, 'Your adoption request for otlum111 was declined. Reason: test', 0, '2026-02-27 17:20:00'),
(9, 7, 'Karl Vladimir Borjaa sent an adoption request for your pet post.', 0, '2026-02-27 17:33:53'),
(10, 5, 'Your adoption request for otlummmm was declined. Reason: testt', 0, '2026-02-27 17:58:07'),
(11, 7, 'Your lost & found post \"otlum\" has been approved and is now live.', 0, '2026-02-27 19:15:25'),
(12, 7, 'Your lost & found post \"tsesf\" was rejected. Reason: test', 0, '2026-02-27 19:16:05'),
(13, 7, 'Your lost & found post \"muwah\" has been approved and is now live.', 0, '2026-02-27 19:16:51'),
(14, 7, 'Karl Vladimir Borjaa sent an adoption request for your pet post.', 0, '2026-02-27 19:17:28'),
(15, 5, 'Your adoption request for muwah was approved. Contact owner: Kristine Tuazon (09318424195).', 0, '2026-02-27 19:18:01');

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
(8, 20, '', 'Kristine Tuazon', '[MISSING DOG] Name: otlum | Last Seen: test | Contact: 12313131', NULL, '2026-02-13 11:26:05'),
(11, 23, 'Message', 'Vladimir Borja', 'hi', NULL, '2026-02-15 15:56:07'),
(14, 37, '', 'Kristine Tuazon', 'asdasdasd', NULL, '2026-02-15 16:00:01'),
(15, 35, '', 'Ariana Punsalang', 'hi', NULL, '2026-02-15 16:00:03'),
(16, 29, '', 'Kristine Tuazon', 'hi', NULL, '2026-02-15 16:00:13'),
(17, 38, '', 'Karl Vladimir Borjaa', 'hi', NULL, '2026-02-16 05:20:13'),
(18, 30, '', 'Kristine Tuazon', 'hi', NULL, '2026-02-20 18:16:37'),
(19, 22, 'Message', 'cafe beni', 'TESTING SWAL', NULL, '2026-02-20 18:16:44'),
(20, 49, '', 'Karl Vladimir Borjaa', 'd', NULL, '2026-02-20 18:22:49'),
(21, 21, '', 'Kristine Tuazon', 'test', NULL, '2026-02-23 05:36:07'),
(22, 18, '', 'Kristine Tuazon', 'test1', NULL, '2026-02-23 05:36:10'),
(23, 16, '', 'Kristine Tuazon', 'bbbb e', NULL, '2026-02-23 05:36:12'),
(24, 21, 'Message', 'cafe beni', 'HAYY', NULL, '2026-02-23 05:36:24'),
(25, 20, 'Message', 'DAHS SDHA', 'DASNM,', NULL, '2026-02-23 05:36:26');

-- --------------------------------------------------------

--
-- Table structure for table `business_claims`
--

CREATE TABLE `business_claims` (
  `claim_id` int(11) NOT NULL,
  `establishment_id` int(11) NOT NULL,
  `requester_user_id` int(11) NOT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `reason` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(26, 'Vladimir Borja', 'vladimirborja013@gmail.com', '09650561211', 'hi', 'test', '2026-02-23 15:40:45');

-- --------------------------------------------------------

--
-- Table structure for table `establishments`
--

CREATE TABLE `establishments` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `requester_id` int(11) DEFAULT NULL,
  `status` enum('active','pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `type` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `address` varchar(255) NOT NULL,
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(11,7) DEFAULT NULL,
  `barangay` varchar(120) DEFAULT NULL,
  `policies` text DEFAULT NULL,
  `pet_types_allowed` varchar(255) DEFAULT NULL,
  `venue_size` enum('Small','Medium','Large') DEFAULT NULL,
  `operating_hours` varchar(120) DEFAULT NULL,
  `contact_number` varchar(30) DEFAULT NULL,
  `social_links` varchar(255) DEFAULT NULL,
  `featured` tinyint(1) NOT NULL DEFAULT 0,
  `guidelines_accepted` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `rejection_reason` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `establishments`
--

INSERT INTO `establishments` (`id`, `user_id`, `requester_id`, `status`, `type`, `name`, `description`, `address`, `latitude`, `longitude`, `barangay`, `policies`, `pet_types_allowed`, `venue_size`, `operating_hours`, `contact_number`, `social_links`, `featured`, `guidelines_accepted`, `created_at`, `rejection_reason`) VALUES
(12, 5, 6, 'active', 'Restaurant / Cafe', 'PREMIER PERLINE VETERINARY CLINIC', 'test', 'Malino', 15.1461125, 120.5800986, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, '2026-02-27 18:12:30', NULL),
(20, 5, NULL, 'active', 'Restaurant / Cafe', 'test', 'test', 'test', 15.1452136, 120.5806992, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, '2026-02-27 18:12:30', NULL),
(21, 5, 6, 'active', 'Mall / Shopping Center', 'PREMIER PERLINE VETERINARY CLINIC', 'asd', '196 C TINIO BLDG. MAC ARTHUR HI-WAY BALIBAGO, ANGELES CITY', 15.1484322, 120.5745204, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, '2026-02-27 18:12:30', NULL),
(22, 5, 6, 'active', 'Mall / Shopping Center', 'PREMIER PERLINE VETERINARY CLINIC', 'asd', '196 C TINIO BLDG. MAC ARTHUR HI-WAY BALIBAGO, ANGELES CITY', 15.1484322, 120.5745204, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, '2026-02-27 18:12:30', NULL),
(23, 5, 6, 'active', 'Restaurant / Cafe', 'PREMIER PERLINE VETERINARY CLINIC', 'asd', '196 C TINIO BLDG. MAC ARTHUR HI-WAY BALIBAGO, ANGELES CITY', 15.1474588, 120.5790905, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, '2026-02-27 18:12:30', NULL),
(24, 5, NULL, 'active', 'Park / Recreational Area', 'PREMIER PERLINE VETERINARY CLINIC', 'nuu', 'holy angel university', 15.1023553, 120.6260833, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, '2026-02-27 18:12:30', NULL),
(25, 5, 7, 'active', 'Hotel / Resort', 'Alfa Mart', 'test', 'Malino', 15.1467546, 120.5763449, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, '2026-02-27 18:12:30', NULL),
(26, 5, 6, 'active', 'Restaurant / Cafe', 'Alfa Mart', 'test', 'dito lang', 15.1470901, 120.5804431, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, '2026-02-27 18:12:30', NULL),
(27, 5, NULL, 'active', 'Restaurant / Cafe', 'test', 'sasa', 'test', 15.2121082, 120.6538321, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, '2026-02-27 18:12:30', NULL),
(28, 5, NULL, 'active', 'Hotel / Resort', 'tambay coffee', 'asd', 'Malino', 15.1664510, 120.5083429, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, '2026-02-27 18:12:30', NULL),
(29, 7, NULL, 'active', 'Restaurant / Cafe', 'tambay coffee', 'da', '196 C TINIO BLDG. MAC ARTHUR HI-WAY BALIBAGO, ANGELES CITY', 15.1460503, 120.5696283, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, '2026-02-27 18:12:30', NULL),
(30, 7, NULL, 'active', 'Park / Recreational Area', 'test', 'd', 'holy angel university', 15.1471274, 120.5799270, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, '2026-02-27 18:12:30', NULL),
(31, 5, NULL, 'active', 'Restaurant / Cafe', 'tambay coffee', 'asd', 'asd', 15.1473759, 120.5779322, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, '2026-02-27 18:12:30', NULL),
(34, 7, 7, 'approved', 'Mall / Shopping Center', 'marquee', 'test', 'test', 15.1523000, 120.5901000, 'cutcut', 'test', 'Dogs, Birds', 'Medium', '8:00 AM - 8:00 PM', '09650561211', '', 0, 1, '2026-02-27 17:50:52', NULL),
(35, NULL, 7, 'rejected', 'Mall / Shopping Center', 'SM Clark', 'test', 'test', 15.1456000, 120.5912000, 'market area', 'test', 'Small Animals', 'Medium', '8:00 AM - 8:00 PM', '09650561211', '', 0, 1, '2026-02-27 18:00:28', 'test');

-- --------------------------------------------------------

--
-- Table structure for table `establishment_records`
--

CREATE TABLE `establishment_records` (
  `record_id` int(11) NOT NULL,
  `establishment_id` int(11) NOT NULL,
  `establishment_name` varchar(255) NOT NULL,
  `category` varchar(120) DEFAULT NULL,
  `barangay` varchar(120) DEFAULT NULL,
  `submitted_by_user_id` int(11) DEFAULT NULL,
  `submitted_by_name` varchar(255) DEFAULT NULL,
  `status` enum('approved','rejected') NOT NULL,
  `admin_id` int(11) NOT NULL,
  `admin_name` varchar(255) NOT NULL,
  `submitted_at` timestamp NULL DEFAULT NULL,
  `actioned_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `rejection_reason` text DEFAULT NULL
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
(136, 5, '2026-02-13 14:02:14', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'success'),
(138, 5, '2026-02-13 15:16:23', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'success'),
(139, 5, '2026-02-14 12:35:10', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'success'),
(141, 5, '2026-02-15 03:55:56', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'success'),
(143, 5, '2026-02-15 05:07:12', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'success'),
(144, 5, '2026-02-15 06:18:07', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'success'),
(146, 5, '2026-02-15 07:08:11', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'success'),
(149, 9, '2026-02-15 07:31:06', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'success'),
(152, 7, '2026-02-15 09:36:01', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'success'),
(153, 5, '2026-02-15 09:36:35', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'success'),
(154, 6, '2026-02-15 09:36:51', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'success'),
(155, 5, '2026-02-15 09:54:29', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'success'),
(156, 5, '2026-02-15 09:56:56', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 Edg/145.0.0.0', 'success'),
(157, 6, '2026-02-15 10:05:19', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'success'),
(158, 7, '2026-02-15 10:12:44', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'success'),
(159, 6, '2026-02-15 10:13:14', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'success'),
(160, 5, '2026-02-15 10:45:39', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'success'),
(161, 5, '2026-02-15 10:45:49', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'success'),
(162, 7, '2026-02-15 14:05:24', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'success'),
(163, 7, '2026-02-15 14:28:45', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'success'),
(164, 7, '2026-02-15 15:01:23', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'success'),
(165, 5, '2026-02-15 15:19:54', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'success'),
(166, 5, '2026-02-15 15:55:38', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'success'),
(167, 7, '2026-02-15 15:56:27', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'success'),
(168, 5, '2026-02-15 15:57:02', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'success'),
(169, 7, '2026-02-15 16:00:35', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'success'),
(170, 7, '2026-02-16 05:01:58', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'success'),
(171, 6, '2026-02-16 05:14:51', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'success'),
(172, 7, '2026-02-16 05:16:52', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'success'),
(173, 6, '2026-02-16 05:17:31', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'success'),
(174, 5, '2026-02-16 05:18:57', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'success'),
(175, 7, '2026-02-17 07:41:30', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'success'),
(176, 5, '2026-02-17 08:00:24', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'success'),
(177, 5, '2026-02-17 09:39:59', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'success'),
(178, 7, '2026-02-17 09:40:23', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'success'),
(179, 5, '2026-02-17 09:56:35', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'success'),
(180, 5, '2026-02-17 09:56:47', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'success'),
(181, 5, '2026-02-17 09:56:55', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'success'),
(182, 7, '2026-02-17 09:57:12', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'success'),
(183, 6, '2026-02-17 09:58:06', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'success'),
(184, 7, '2026-02-17 12:23:05', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'success'),
(185, 5, '2026-02-20 15:08:49', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'success'),
(186, 5, '2026-02-20 15:54:11', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'success'),
(187, 5, '2026-02-20 16:05:12', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'success'),
(188, 5, '2026-02-20 16:19:40', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'success'),
(189, 7, '2026-02-20 16:24:33', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'success'),
(190, 5, '2026-02-20 17:34:23', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'success'),
(191, 5, '2026-02-20 17:36:22', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'success'),
(192, 7, '2026-02-20 17:37:16', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'success'),
(193, 5, '2026-02-20 17:37:47', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'success'),
(194, 5, '2026-02-20 18:08:12', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'success'),
(195, 7, '2026-02-20 18:21:47', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'success'),
(196, 5, '2026-02-20 18:22:08', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'success'),
(197, 5, '2026-02-22 07:27:01', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'success'),
(198, 5, '2026-02-22 07:34:23', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'success'),
(199, 5, '2026-02-23 00:39:38', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'success'),
(200, 5, '2026-02-23 05:34:40', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'success'),
(201, 5, '2026-02-23 05:35:34', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'success'),
(202, 5, '2026-02-23 06:21:03', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'success'),
(203, 5, '2026-02-23 06:21:29', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'success'),
(204, 5, '2026-02-23 06:43:20', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'success'),
(205, 5, '2026-02-23 06:49:53', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'success'),
(206, 6, '2026-02-23 06:58:07', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'success'),
(207, 5, '2026-02-23 07:00:07', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'success'),
(208, 7, '2026-02-23 07:41:14', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'success'),
(209, 5, '2026-02-23 07:41:32', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'success'),
(210, 7, '2026-02-23 08:04:50', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'success'),
(211, 5, '2026-02-23 08:14:48', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'success'),
(212, 7, '2026-02-23 08:20:22', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'success'),
(213, 5, '2026-02-23 09:39:38', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'success'),
(214, 5, '2026-02-23 09:46:38', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'success'),
(215, 5, '2026-02-23 09:48:32', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'success'),
(216, 5, '2026-02-23 14:20:13', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'success'),
(217, 5, '2026-02-23 14:35:14', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'success'),
(218, 5, '2026-02-23 19:00:17', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'success'),
(219, 5, '2026-02-24 01:22:45', '::1', 'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Mobile Safari/537.36', 'success'),
(220, 5, '2026-02-24 01:30:41', '::1', 'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Mobile Safari/537.36', 'success'),
(221, 5, '2026-02-24 01:39:20', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'success'),
(222, 5, '2026-02-24 02:06:26', '::1', 'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Mobile Safari/537.36', 'success'),
(223, 5, '2026-02-27 17:08:33', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'success'),
(224, 7, '2026-02-27 17:38:14', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'success'),
(225, 5, '2026-02-27 17:38:32', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'success'),
(226, 6, '2026-02-27 17:39:55', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'success'),
(227, 6, '2026-02-27 17:42:45', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'success'),
(228, 5, '2026-02-27 17:45:00', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'success');

-- --------------------------------------------------------

--
-- Table structure for table `lost_found_review_records`
--

CREATE TABLE `lost_found_review_records` (
  `record_id` int(11) NOT NULL,
  `pet_id` int(11) NOT NULL,
  `pet_name` varchar(255) NOT NULL,
  `post_type` varchar(40) NOT NULL,
  `submitted_by_user_id` int(11) DEFAULT NULL,
  `submitted_by_name` varchar(255) DEFAULT NULL,
  `status` enum('approved','rejected') NOT NULL,
  `admin_id` int(11) NOT NULL,
  `admin_name` varchar(255) NOT NULL,
  `rejection_reason` text DEFAULT NULL,
  `submitted_at` timestamp NULL DEFAULT NULL,
  `actioned_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lost_found_review_records`
--

INSERT INTO `lost_found_review_records` (`record_id`, `pet_id`, `pet_name`, `post_type`, `submitted_by_user_id`, `submitted_by_name`, `status`, `admin_id`, `admin_name`, `rejection_reason`, `submitted_at`, `actioned_at`) VALUES
(1, 32, 'otlum', 'lost', 7, 'Kristine Tuazon', 'approved', 5, 'Karl Vladimir Borjaa', NULL, '2026-02-27 19:10:29', '2026-02-27 19:15:25'),
(2, 33, 'tsesf', 'lost', 7, 'Kristine Tuazon', 'rejected', 5, 'Karl Vladimir Borjaa', 'test', '2026-02-27 19:15:56', '2026-02-27 19:16:05'),
(3, 34, 'muwah', 'for_adoption', 7, 'Kristine Tuazon', 'approved', 5, 'Karl Vladimir Borjaa', NULL, '2026-02-27 19:16:37', '2026-02-27 19:16:51');

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
(3, 34, 9, 'Vlad Borja commented: \"312321312...\"', 0, '2026-02-15 07:30:33'),
(6, 34, 9, 'Ariana Punsalang commented: \"test 1...\"', 0, '2026-02-15 10:11:17'),
(7, 34, 9, 'Ariana Punsalang liked your post.', 0, '2026-02-15 10:11:22'),
(14, 39, 7, 'Ariana Punsalang liked your post.', 1, '2026-02-17 09:58:11'),
(15, 39, 7, 'Ariana Punsalang commented: \"test...\"', 1, '2026-02-17 09:58:15'),
(16, 36, 6, 'Kristine Tuazon commented: \"hi...\"', 1, '2026-02-17 10:48:53'),
(17, 46, 7, 'Karl Vladimir Borjaa liked your post.', 1, '2026-02-20 15:49:49'),
(18, 46, 7, 'Karl Vladimir Borjaa commented: \"test...\"', 1, '2026-02-20 15:49:52'),
(19, 36, 6, 'Kristine Tuazon liked your post.', 1, '2026-02-20 16:24:53'),
(20, 36, 6, 'Kristine Tuazon commented: \"test...\"', 1, '2026-02-20 16:24:56'),
(21, 46, 7, 'Karl Vladimir Borjaa liked your post.', 1, '2026-02-20 17:36:28'),
(22, 46, 7, 'Karl Vladimir Borjaa commented: \"baboy...\"', 1, '2026-02-20 17:36:32'),
(25, 48, 5, 'Kristine Tuazon commented: \"g...\"', 1, '2026-02-23 08:04:58'),
(26, 48, 5, 'Kristine Tuazon liked your post.', 1, '2026-02-23 08:05:00'),
(27, 48, 5, 'Kristine Tuazon liked your post.', 1, '2026-02-23 08:20:52'),
(28, 48, 5, 'Kristine Tuazon commented: \"g...\"', 1, '2026-02-23 08:20:54'),
(29, 48, 5, 'Kristine Tuazon commented: \"hahaha...\"', 1, '2026-02-23 08:26:58'),
(30, 48, 5, 'Kristine Tuazon liked your post.', 1, '2026-02-23 09:36:29'),
(31, 51, 7, 'Karl Vladimir Borjaa liked your post.', 1, '2026-02-24 01:33:20'),
(32, 51, 7, 'Karl Vladimir Borjaa commented: \"hi...\"', 1, '2026-02-24 01:33:22');

-- --------------------------------------------------------

--
-- Table structure for table `pets`
--

CREATE TABLE `pets` (
  `pet_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `pet_name` varchar(100) NOT NULL,
  `category` enum('Lost','Found','Pending','lost','found','pending','for_adoption','For Adoption','adopted','Adopted','waiting_approval','rejected','resolved') NOT NULL DEFAULT 'waiting_approval',
  `gender` varchar(20) DEFAULT NULL,
  `breed` varchar(100) DEFAULT 'Unknown',
  `color` varchar(50) DEFAULT 'Not Specified',
  `last_seen_location` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `image_url` varchar(255) DEFAULT 'uploads/default_pet.png',
  `contact_number` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `pet_type` varchar(50) DEFAULT NULL,
  `size` enum('Small','Medium','Large') DEFAULT NULL,
  `last_seen_date` date DEFAULT NULL,
  `last_seen_barangay` varchar(120) DEFAULT NULL,
  `requested_category` varchar(40) DEFAULT NULL,
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(11,7) DEFAULT NULL,
  `owner_with_pet_image_url` varchar(255) DEFAULT NULL,
  `verification_status` enum('pending','approved','rejected') DEFAULT 'pending',
  `verification_reason` text DEFAULT NULL,
  `reward_offered` tinyint(1) NOT NULL DEFAULT 0,
  `reward_details` varchar(255) DEFAULT NULL,
  `guidelines_accepted` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pets`
--

INSERT INTO `pets` (`pet_id`, `user_id`, `pet_name`, `category`, `gender`, `breed`, `color`, `last_seen_location`, `description`, `image_url`, `contact_number`, `created_at`, `pet_type`, `size`, `last_seen_date`, `last_seen_barangay`, `requested_category`, `latitude`, `longitude`, `owner_with_pet_image_url`, `verification_status`, `verification_reason`, `reward_offered`, `reward_details`, `guidelines_accepted`) VALUES
(19, 11, 'tsesf', 'Pending', 'Male', 'Dog', 'brown', 'Test', 'adadaw', 'uploads/1770802913_sinoo.gif', '3131312', '2026-02-11 09:41:53', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'pending', NULL, 0, NULL, 0),
(21, 6, 'sino ka', 'Found', 'Male', 'Dog', 'black', 'angeles', 'nawawala', 'uploads/1770811765_sino.jpg', '09123456789', '2026-02-11 12:09:25', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'pending', NULL, 0, NULL, 0),
(22, 6, 'muwah', 'Found', 'Unknown', 'bakla', 'black', 'dito lang', 'aaa', 'uploads/1770811788_heart.jpg', '09999999', '2026-02-11 12:09:48', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'pending', NULL, 0, NULL, 0),
(23, 6, 'otlum', 'Pending', 'Male', 'Dog', 'black', 'angeles', 'asd', 'uploads/1770861809_heart.jpg', '09123456789', '2026-02-12 02:03:29', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'pending', NULL, 0, NULL, 0),
(24, 9, 'otlum', 'Lost', 'Female', 'Dog', 'white', 'sanfer', 'hi', 'uploads/1770865886_otlum.jpg', '09123456789', '2026-02-12 03:11:26', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'pending', NULL, 0, NULL, 0),
(25, 6, 'otlum', 'Found', 'Male', 'bird', 'brown', 'dito lang', 'test', 'uploads/1771218978_sino.jpg', '09123456789', '2026-02-16 05:16:18', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'pending', NULL, 0, NULL, 0),
(26, 7, 'micophobia', 'adopted', 'Unknown', 'Puspin', 'White/Gray', 'Capaya', 'test\n\nAdoption Info:\ntest\nRequirements: test', 'uploads/1772212149_people.png', '09650561211', '2026-02-27 17:09:09', 'Dog', 'Large', '0000-00-00', 'capaya', NULL, 15.1712000, 120.5934000, 'uploads/1772212149_6.png', 'pending', NULL, 0, NULL, 1),
(27, 7, 'otlum', 'for_adoption', 'Unknown', 'ewan', 'black', 'Amsic', 'test\n\nAdoption Info:\ntest\nRequirements: test', 'uploads/1772212265_people.png', 'test', '2026-02-27 17:11:05', 'Dog', 'Large', '0000-00-00', 'amsic', NULL, 15.1456000, 120.5891000, 'uploads/1772212265_6.png', 'pending', NULL, 0, NULL, 1),
(28, 7, 'muwah', 'adopted', 'Unknown', 'ewan', 'whte/gray', 'Agapito Del Rosario', 'test\n\nAdoption Info:\ntest\nRequirements: test', 'uploads/1772212707_people.png', '09999999', '2026-02-27 17:18:27', 'Dog', 'Small', '0000-00-00', 'agapito del rosario', NULL, 15.1335000, 120.5923000, '', 'pending', NULL, 0, NULL, 1),
(29, 5, 'otlum111', 'for_adoption', 'Unknown', 'Puspin', 'whte/gray', 'Margot', 'test\n\nAdoption Info:\ntest\nRequirements: test', 'uploads/1772212777_people.png', 'test', '2026-02-27 17:19:37', 'Dog', 'Small', '0000-00-00', 'margot', NULL, 15.1423000, 120.5667000, '', 'pending', NULL, 0, NULL, 1),
(30, 7, 'otlummmm', 'for_adoption', 'Unknown', 'Puspin', 'red', 'Amsic', 'test\n\nAdoption Info:\ntest\nRequirements: test', 'uploads/1772213317_sinoo.gif', 'test', '2026-02-27 17:28:37', 'Dog', 'Medium', '0000-00-00', 'amsic', NULL, 15.1456000, 120.5891000, '', 'pending', NULL, 0, NULL, 1),
(31, 7, 'heart', 'Lost', 'Unknown', 'ewan', 'brown', 'Pandan', 'test', 'uploads/1772214879_heart.jpg', '09999999', '2026-02-27 17:54:39', 'Dog', 'Small', '2026-02-23', 'pandan', NULL, 15.1289000, 120.5778000, 'uploads/1772214879_micophobia.png', 'pending', NULL, 0, NULL, 1),
(32, 7, 'otlum', 'Lost', 'Unknown', 'Puspin', 'White/Gray', 'Marisol', 'a', 'uploads/1772219429_dog1.png', '09650561211', '2026-02-27 19:10:29', 'Dog', 'Large', '2026-02-04', 'marisol', 'lost', 15.1378000, 120.5845000, 'uploads/1772219429_5.png', 'approved', NULL, 1, '3333', 1),
(33, 7, 'tsesf', 'rejected', 'Unknown', 'Puspin', 'whte/gray', 'Amsic', 'asd', 'uploads/1772219756_cat1.png', '09123456789', '2026-02-27 19:15:56', 'Cat', 'Small', '2026-02-09', 'amsic', 'lost', 15.1456000, 120.5891000, 'uploads/1772219756_dog1.png', 'rejected', 'test', 1, '555', 1),
(34, 7, 'muwah', 'adopted', 'Unknown', 'Puspin', 'black', 'Tabun', 'test\n\nAdoption Info:\ntest\nRequirements: test', 'uploads/1772219797_cat1.png', 'test', '2026-02-27 19:16:37', 'Cat', 'Small', '0000-00-00', 'tabun', 'for_adoption', 15.1256000, 120.5845000, '', 'approved', NULL, 0, '', 1);

-- --------------------------------------------------------

--
-- Table structure for table `pet_bookmarks`
--

CREATE TABLE `pet_bookmarks` (
  `bookmark_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `pet_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pet_bookmarks`
--

INSERT INTO `pet_bookmarks` (`bookmark_id`, `user_id`, `pet_id`, `created_at`) VALUES
(3, 5, 31, '2026-02-27 18:04:17');

-- --------------------------------------------------------

--
-- Table structure for table `pet_responses`
--

CREATE TABLE `pet_responses` (
  `response_id` int(11) NOT NULL,
  `pet_id` int(11) NOT NULL,
  `responder_user_id` int(11) NOT NULL,
  `owner_user_id` int(11) DEFAULT NULL,
  `adopter_name` varchar(255) DEFAULT NULL,
  `adopter_contact` varchar(50) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `decline_reason` text DEFAULT NULL,
  `status` enum('pending','approved','declined','resolved') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `decided_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pet_responses`
--

INSERT INTO `pet_responses` (`response_id`, `pet_id`, `responder_user_id`, `owner_user_id`, `adopter_name`, `adopter_contact`, `message`, `decline_reason`, `status`, `created_at`, `decided_at`) VALUES
(1, 26, 5, 7, 'Karl Vladimir Borjaa', '09650561211', 'About Me: Vlad\n\nWhy I want to adopt / living situation:\ntest\n\nAdditional Message:\ntest', NULL, 'approved', '2026-02-27 17:09:49', '2026-02-27 17:10:34'),
(2, 27, 5, 7, 'Karl Vladimir Borjaa', '09650561211', 'About Me: Vlad\n\nWhy I want to adopt / living situation:\ntest\n\nAdditional Message:\ntest', 'test', 'declined', '2026-02-27 17:11:24', '2026-02-27 17:11:32'),
(3, 28, 5, 7, 'Karl Vladimir Borjaa', '09650561211', 'About Me: Vlad\n\nWhy I want to adopt / living situation:\ntestttt\n\nAdditional Message:\ntesstttt', NULL, 'approved', '2026-02-27 17:19:06', '2026-02-27 17:19:14'),
(4, 29, 7, 5, 'Kristine Tuazon', '09318424195', 'About Me: test\n\nWhy I want to adopt / living situation:\ntest\n\nAdditional Message:\ntest', 'test', 'declined', '2026-02-27 17:19:52', '2026-02-27 17:20:00'),
(5, 30, 5, 7, 'Karl Vladimir Borjaa', '09650561211', 'Adoption Application\nName: Karl Vladimir Borjaa\nAge: 21\nOccupation: single\nContact: 09650561211\nLiving Type: House\nOutdoor Space: test\nPet Experience: Yes\nExperience Details: test\n\nWhy adopt this pet:\ntestttttttttttttttttttttttttttttttttttttttttttttttttttttttttttttttt\n\nAdditional Message:\ntest', 'testt', 'declined', '2026-02-27 17:33:53', '2026-02-27 17:58:07'),
(6, 34, 5, 7, 'Karl Vladimir Borjaa', '09650561211', 'Adoption Application\nName: Karl Vladimir Borjaa\nAge: 22\nOccupation: single\nContact: 09650561211\nLiving Type: House\nOutdoor Space: test\nPet Experience: Yes\nExperience Details: test\n\nWhy adopt this pet:\ntesttesttesttesttesttesttesttesttesttest\n\nAdditional Message:\ntest', NULL, 'approved', '2026-02-27 19:17:28', '2026-02-27 19:18:01');

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
(17, 6, '[MISSING DOG] Nam1e: margiel | Last Seen: dito lang | Contact: 123', 'uploads/missing_1770718695_otlum.jpg', '2026-02-10 10:18:15'),
(34, 9, 'hi', 'uploads/1770913450_heart.jpg', '2026-02-12 16:24:10'),
(36, 6, 'asasdasdasdas', 'uploads/1771150351_heart.jpg', '2026-02-15 10:12:31'),
(39, 7, 'hi', 'uploads/1771322266_otlum1.jpg', '2026-02-17 09:57:46'),
(40, 7, 'hi', 'uploads/1771323942_heart.jpg', '2026-02-17 10:25:42'),
(41, 7, 'nbjk\\r\\nasdasd', '', '2026-02-17 11:58:38'),
(42, 7, 'test\\ntest\\ntest', '', '2026-02-17 12:02:24'),
(43, 7, 'test\ntest', '', '2026-02-17 12:05:36'),
(44, 7, 'test\ntest\ntest\ntest', '', '2026-02-17 12:05:51'),
(45, 7, 'hi\nhi\nhi\ntest\nasdasd', 'uploads/1771330019_sinoo.gif', '2026-02-17 12:06:59'),
(46, 7, 'bjh jhj guuvr', '', '2026-02-17 12:07:20'),
(47, 5, 'tesst', '', '2026-02-20 18:19:39'),
(48, 5, 'test', 'uploads/1771611650_sinoo.gif', '2026-02-20 18:19:48'),
(51, 7, 'HI', 'uploads/1771839405_Ella es Baby, la cachorrita corgi que vas a querer abrazar en este momento (2).jpg', '2026-02-23 09:36:45');

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
(28, 34, 5, 'a', '2026-02-15 04:19:07'),
(33, 34, 6, 'test 1', '2026-02-15 10:11:17'),
(41, 39, 6, 'test', '2026-02-17 09:58:15'),
(42, 36, 7, 'hi', '2026-02-17 10:48:53'),
(43, 46, 5, 'test', '2026-02-20 15:49:52'),
(44, 36, 7, 'test', '2026-02-20 16:24:56'),
(45, 46, 5, 'baboy', '2026-02-20 17:36:32'),
(46, 48, 5, 'test', '2026-02-20 18:20:05'),
(48, 48, 7, 'g', '2026-02-23 08:04:58'),
(49, 48, 7, 'g', '2026-02-23 08:20:54'),
(50, 48, 7, 'hahaha', '2026-02-23 08:26:58'),
(51, 51, 5, 'hi', '2026-02-24 01:33:22');

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
(26, 34, 6, '2026-02-15 10:11:22'),
(36, 39, 6, '2026-02-17 09:58:11'),
(39, 36, 7, '2026-02-20 16:24:53'),
(40, 46, 5, '2026-02-20 17:36:28'),
(41, 48, 5, '2026-02-20 18:20:03'),
(45, 48, 7, '2026-02-23 09:36:29'),
(46, 51, 5, '2026-02-24 01:33:20');

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
(78, 46, 5, 'Fake Content', '0', '2026-02-23 07:21:26'),
(79, 40, 5, 'Inappropriate', 'hi', '2026-02-23 07:21:40'),
(80, 48, 7, 'Fake Content', 'd', '2026-02-23 08:20:59'),
(81, 48, 7, 'Verbal Abuse', 'a', '2026-02-23 08:26:41'),
(82, 48, 7, 'Spam', 'asd', '2026-02-23 08:26:50'),
(83, 48, 7, 'Verbal Abuse', 'a', '2026-02-23 08:27:20'),
(84, 51, 5, 'Verbal Abuse', 'da', '2026-02-23 14:37:15'),
(85, 51, 5, 'Fake Content', 'hi\\r\\n', '2026-02-24 01:33:31');

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
  `verification_code` varchar(100) DEFAULT NULL,
  `google_id` varchar(255) DEFAULT NULL,
  `phone_number` varchar(20) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('user','admin','super_admin','business_owner','veterinarian','salon_owner') NOT NULL DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `last_login` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `profile_image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `full_name`, `username`, `is_verified`, `bio`, `email`, `verification_code`, `google_id`, `phone_number`, `password`, `role`, `created_at`, `updated_at`, `last_login`, `is_active`, `profile_image`) VALUES
(4, 'margiel escalante', NULL, 0, '', 'info@bb88advertising.com', '', '', '09650561211', '$2y$10$fADemELEvOqwjJHIqak8..aZpgbW4WpskH3Tml2kxVEgJU2Y3M5Mi', 'user', '2026-01-29 18:00:50', '2026-01-29 18:02:11', '2026-01-29 18:02:11', 1, NULL),
(5, 'Karl Vladimir Borjaa', 'Karlaaaaaaaaaa', 1, 'SINO TO?!', 'vladimirborja013@gmail.com', '', '', '09650561211', '$2y$10$qpS58OYVf9xG8yJRhiv4JuXKojMJBD0QvVbWiMXJsoMNp4vbticYG', 'admin', '2026-01-29 18:09:12', '2026-02-27 17:45:00', '2026-02-27 17:45:00', 1, '../uploads/profile_pics/user_5_1772212175.jpeg'),
(6, 'Ariana Punsalang', NULL, 1, '', 'anairadump@gmail.com', '', '', '09915676315', '$2y$10$/KwFozircYcrk5Vpi2oaoO06.jvnzmD0E5AtTkNOXtOoDLsB7S8tO', 'user', '2026-02-03 06:36:07', '2026-02-27 17:42:45', '2026-02-27 17:42:45', 1, '../uploads/profile_pics/user_6_1771150867.png'),
(7, 'Kristine Tuazon', NULL, 1, 'bioooo', 'kristinetuazon16@gmail.com', '', '', '09318424195', '$2y$10$Js0vpcHQpVXsUSAHj22kF.b5gOPTckaIMYjwS9nazPKEnPXvJgWui', 'user', '2026-02-03 06:41:44', '2026-02-27 17:39:14', '2026-02-27 17:38:14', 1, '../uploads/profile_pics/user_7_1771609052.jpg'),
(8, 'Mico Cuenco', NULL, 0, '', 'micocuenco@gmail.com', '', '', '09123456789', '$2y$10$5TbZ4Vpu14roc6HtE2ijzeRSQjL3QQxHxdfK9LNe6CduMDB1wro9O', 'user', '2026-02-06 19:32:34', '2026-02-06 19:33:25', '2026-02-06 19:33:25', 1, NULL),
(9, 'Cayoh Anicete', NULL, 0, '', 'cayohanicete@gmail.com', '', '', '09123456789', '$2y$10$vjFICjPhuwKCKfhCO1gBveIwW3zhneTy99WAV5IfBAtd7ShMcUPja', 'user', '2026-02-11 03:10:13', '2026-02-20 18:18:23', '2026-02-15 07:31:06', 0, NULL),
(10, 'margiel escalante', NULL, 0, '', 'margielescalante@gmail.com', '', '', '09123456789', '$2y$10$tJ6ojSH5oqJUchJHtZDGbuMJOKjQADmyrlhiP01MQfVfB4Fbbdcn.', 'user', '2026-02-11 06:26:03', '2026-02-20 18:18:21', '2026-02-11 06:31:48', 1, NULL),
(11, 'Christian Aguas', NULL, 0, '', 'acdeocera.bb88@gmail.com', '', '', '09201172065', '$2y$10$OIYShiZROiBhCVG3/k1nROqr8aQhykXnTTsa9I9QlFNxv2ns4jqKS', 'user', '2026-02-11 09:13:39', '2026-02-12 17:01:12', '2026-02-11 09:20:59', 1, NULL),
(12, 'Cess Pascual', NULL, 0, '', 'princesstimbang03@gmail.com', '', '', '09123456789', '$2y$10$5PMDLFah4V/QQH1J1h7Gu.HHHrRMPPFGR2hBhZ0brkXhP9mUePhE2', 'user', '2026-02-17 12:18:23', '2026-02-23 07:41:01', NULL, 0, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_logs`
--
ALTER TABLE `admin_logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `idx_admin_logs_admin` (`admin_id`),
  ADD KEY `idx_admin_logs_action` (`action_type`),
  ADD KEY `idx_admin_logs_date` (`created_at`);

--
-- Indexes for table `adoption_notifications`
--
ALTER TABLE `adoption_notifications`
  ADD PRIMARY KEY (`notification_id`),
  ADD KEY `idx_adopt_notif_user` (`user_id`),
  ADD KEY `idx_adopt_notif_date` (`created_at`);

--
-- Indexes for table `archives`
--
ALTER TABLE `archives`
  ADD PRIMARY KEY (`archive_id`);

--
-- Indexes for table `business_claims`
--
ALTER TABLE `business_claims`
  ADD PRIMARY KEY (`claim_id`),
  ADD KEY `idx_claim_est` (`establishment_id`),
  ADD KEY `idx_claim_user` (`requester_user_id`);

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
-- Indexes for table `establishment_records`
--
ALTER TABLE `establishment_records`
  ADD PRIMARY KEY (`record_id`),
  ADD KEY `idx_est_records_status` (`status`),
  ADD KEY `idx_est_records_establishment` (`establishment_id`),
  ADD KEY `idx_est_records_actioned_at` (`actioned_at`);

--
-- Indexes for table `login_history`
--
ALTER TABLE `login_history`
  ADD PRIMARY KEY (`login_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_login_time` (`login_time`);

--
-- Indexes for table `lost_found_review_records`
--
ALTER TABLE `lost_found_review_records`
  ADD PRIMARY KEY (`record_id`),
  ADD UNIQUE KEY `uniq_lf_review_pet` (`pet_id`),
  ADD KEY `idx_lf_review_status` (`status`),
  ADD KEY `idx_lf_review_actioned` (`actioned_at`);

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
-- Indexes for table `pet_bookmarks`
--
ALTER TABLE `pet_bookmarks`
  ADD PRIMARY KEY (`bookmark_id`),
  ADD UNIQUE KEY `uniq_user_pet_bookmark` (`user_id`,`pet_id`),
  ADD KEY `idx_bookmark_pet` (`pet_id`);

--
-- Indexes for table `pet_responses`
--
ALTER TABLE `pet_responses`
  ADD PRIMARY KEY (`response_id`),
  ADD UNIQUE KEY `uniq_pet_adopter` (`pet_id`,`responder_user_id`),
  ADD KEY `idx_pet_response_pet` (`pet_id`),
  ADD KEY `idx_pet_response_user` (`responder_user_id`),
  ADD KEY `idx_pet_response_owner` (`owner_user_id`);

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
-- AUTO_INCREMENT for table `admin_logs`
--
ALTER TABLE `admin_logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `adoption_notifications`
--
ALTER TABLE `adoption_notifications`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `archives`
--
ALTER TABLE `archives`
  MODIFY `archive_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `business_claims`
--
ALTER TABLE `business_claims`
  MODIFY `claim_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `contact_messages`
--
ALTER TABLE `contact_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `establishments`
--
ALTER TABLE `establishments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `establishment_records`
--
ALTER TABLE `establishment_records`
  MODIFY `record_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `login_history`
--
ALTER TABLE `login_history`
  MODIFY `login_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=229;

--
-- AUTO_INCREMENT for table `lost_found_review_records`
--
ALTER TABLE `lost_found_review_records`
  MODIFY `record_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `pets`
--
ALTER TABLE `pets`
  MODIFY `pet_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `pet_bookmarks`
--
ALTER TABLE `pet_bookmarks`
  MODIFY `bookmark_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `pet_responses`
--
ALTER TABLE `pet_responses`
  MODIFY `response_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `posts`
--
ALTER TABLE `posts`
  MODIFY `post_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

--
-- AUTO_INCREMENT for table `post_comments`
--
ALTER TABLE `post_comments`
  MODIFY `comment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

--
-- AUTO_INCREMENT for table `post_likes`
--
ALTER TABLE `post_likes`
  MODIFY `like_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- AUTO_INCREMENT for table `post_reports`
--
ALTER TABLE `post_reports`
  MODIFY `report_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=86;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admin_logs`
--
ALTER TABLE `admin_logs`
  ADD CONSTRAINT `fk_admin_logs_admin` FOREIGN KEY (`admin_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `adoption_notifications`
--
ALTER TABLE `adoption_notifications`
  ADD CONSTRAINT `fk_adopt_notif_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `business_claims`
--
ALTER TABLE `business_claims`
  ADD CONSTRAINT `fk_claim_est` FOREIGN KEY (`establishment_id`) REFERENCES `establishments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_claim_user` FOREIGN KEY (`requester_user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

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
-- Constraints for table `pet_bookmarks`
--
ALTER TABLE `pet_bookmarks`
  ADD CONSTRAINT `fk_bookmark_pet` FOREIGN KEY (`pet_id`) REFERENCES `pets` (`pet_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_bookmark_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `pet_responses`
--
ALTER TABLE `pet_responses`
  ADD CONSTRAINT `fk_pet_response_owner` FOREIGN KEY (`owner_user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_pet_response_pet` FOREIGN KEY (`pet_id`) REFERENCES `pets` (`pet_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_pet_response_user` FOREIGN KEY (`responder_user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

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

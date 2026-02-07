-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 07, 2026 at 02:11 AM
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
(53, 7, '2026-02-06 21:22:37', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'success');

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
(1, 6, 'asd', 'uploads/1770402983_otlum.jpg', '2026-02-06 18:36:23'),
(3, 6, 'sino to', 'uploads/1770404670_otlum.jpg', '2026-02-06 19:04:30'),
(6, 7, 'spiderman\\\\\\\\r\\\\\\\\nsssss', 'uploads/1770409060_GyiYtEVbMAAn5Tc.jpg', '2026-02-06 20:17:40'),
(8, 7, '[MISSING DOG] Name: margiel | Last Seen: dito lang | Contact: 123', NULL, '2026-02-06 20:28:00'),
(11, 7, '[MISSING DOG] Name: margiel | Last Seen: dito lang | Contact: 123asdasda', 'uploads/missing_1770410244_526744371_734164873116640_6121404047598711197_n.png', '2026-02-06 20:37:24'),
(12, 7, '[MISSING DOG] Name: margielaaa | Last Seen: dito lang | Contact: 123', 'uploads/missing_1770410882_GyiYtEVbMAAn5Tc.jpg', '2026-02-06 20:48:02'),
(14, 7, '[MISSING DOG] Name: margielaaaaaaaaa | Last Seen: dito langaaaa | Contact: 0920 ikaw na bahala sa pito', 'uploads/missing_1770412209_526744371_734164873116640_6121404047598711197_n.png', '2026-02-06 21:10:09');

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
(1, 1, 6, 'test', '2026-02-06 18:36:27'),
(2, 2, 6, 'asd', '2026-02-06 18:56:13'),
(3, 2, 6, 'sino to', '2026-02-06 19:03:17'),
(4, 1, 6, 'sino to', '2026-02-06 19:03:40'),
(5, 3, 6, 'sino ka', '2026-02-06 19:04:38'),
(6, 3, 6, 'babioy', '2026-02-06 19:13:22'),
(7, 4, 7, 'sino ka', '2026-02-06 19:21:50'),
(8, 4, 8, 'balagbaw ka ha', '2026-02-06 19:33:50'),
(9, 4, 8, 'asdasd', '2026-02-06 19:42:21'),
(10, 4, 8, 'balagbaw ka ha', '2026-02-06 20:02:26'),
(11, 4, 7, 'asd', '2026-02-06 20:06:37'),
(12, 4, 7, 'asd', '2026-02-06 20:13:45'),
(13, 5, 7, 'asd', '2026-02-06 20:15:48'),
(14, 6, 7, 'sts', '2026-02-06 20:18:40'),
(15, 6, 7, 'ww', '2026-02-06 20:22:05'),
(16, 7, 7, 'asd', '2026-02-06 20:24:45'),
(17, 8, 7, 'asd', '2026-02-06 20:28:32'),
(18, 8, 7, 'ddd', '2026-02-06 20:30:49'),
(19, 9, 7, 'w', '2026-02-06 20:32:25'),
(20, 11, 7, 'babioy', '2026-02-06 20:37:30'),
(21, 12, 7, 'asdasd', '2026-02-06 20:48:06'),
(22, 13, 7, 'test ko', '2026-02-06 21:09:50'),
(23, 14, 6, 'sino to', '2026-02-06 21:21:22'),
(24, 14, 7, 'balagbaw', '2026-02-06 21:23:06');

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
(5, 2, 6, '2026-02-06 19:03:44'),
(8, 1, 6, '2026-02-06 19:13:18'),
(11, 4, 7, '2026-02-06 19:21:48'),
(12, 3, 6, '2026-02-06 19:22:55'),
(19, 4, 6, '2026-02-06 19:24:44'),
(30, 4, 8, '2026-02-06 20:02:19'),
(31, 6, 7, '2026-02-06 20:23:57'),
(34, 3, 7, '2026-02-06 20:24:05'),
(35, 1, 7, '2026-02-06 20:24:07'),
(36, 7, 7, '2026-02-06 20:24:43'),
(37, 8, 7, '2026-02-06 20:28:29'),
(39, 9, 7, '2026-02-06 20:33:04'),
(40, 10, 7, '2026-02-06 20:34:36'),
(41, 11, 7, '2026-02-06 20:37:27'),
(42, 12, 7, '2026-02-06 20:48:05'),
(43, 13, 7, '2026-02-06 21:09:46'),
(44, 14, 6, '2026-02-06 21:11:47'),
(45, 12, 6, '2026-02-06 21:11:50'),
(46, 14, 7, '2026-02-06 21:23:00');

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
(2, 3, 7, 'Fake Content', 'ayoko', '2026-02-06 19:15:14'),
(3, 3, 7, 'Hate Speech', 'asd', '2026-02-06 20:05:32'),
(4, 3, 7, 'Inappropriate Content', 'aa', '2026-02-06 20:08:59'),
(5, 3, 7, 'Spam', 'a', '2026-02-06 20:12:11'),
(6, 3, 7, 'Verbal Abuse', 'asd', '2026-02-06 20:15:56'),
(7, 3, 7, 'Fake Content', 'asdasdasd', '2026-02-06 20:24:18'),
(8, 11, 6, 'Fake Content', 'asdasdasd', '2026-02-06 20:38:09'),
(9, 14, 6, 'Fake Content', 'testtt', '2026-02-06 21:11:44'),
(10, 14, 6, 'Fake Content', 'asdasdasd', '2026-02-06 21:21:33');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone_number` varchar(20) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('user','admin') NOT NULL DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `last_login` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `full_name`, `email`, `phone_number`, `password`, `role`, `created_at`, `updated_at`, `last_login`, `is_active`) VALUES
(2, 'Vlad Borja', 'vladimirborja298@gmail.com', '09123456789', '$2y$10$YQ5bYjvD9r8vr1kHQaKLZ.QjeTuAcNdP5xwXs5pCcwbJBD757uj8K', 'user', '2026-01-29 17:38:50', '2026-01-29 17:38:50', NULL, 1),
(4, 'margiel escalante', 'info@bb88advertising.com', '09650561211', '$2y$10$fADemELEvOqwjJHIqak8..aZpgbW4WpskH3Tml2kxVEgJU2Y3M5Mi', 'user', '2026-01-29 18:00:50', '2026-01-29 18:02:11', '2026-01-29 18:02:11', 1),
(5, 'Karl Vladimir Borja', 'vladimirborja013@gmail.com', '09650561211', '$2y$10$NJ/lcONneIkIa6C/oaqrsOxE5LYGtyit/hfpzK1m7YVgYvMkIZkRS', 'admin', '2026-01-29 18:09:12', '2026-02-06 20:45:45', '2026-02-06 20:45:45', 1),
(6, 'Ariana Punsalang', 'anairadump@gmail.com', '09915676315', '$2y$10$/KwFozircYcrk5Vpi2oaoO06.jvnzmD0E5AtTkNOXtOoDLsB7S8tO', 'user', '2026-02-03 06:36:07', '2026-02-06 21:21:01', '2026-02-06 21:21:01', 1),
(7, 'Kristine Tuazon', 'kristinetuazon16@gmail.com', '09318424195', '$2y$10$Js0vpcHQpVXsUSAHj22kF.b5gOPTckaIMYjwS9nazPKEnPXvJgWui', 'user', '2026-02-03 06:41:44', '2026-02-06 21:22:37', '2026-02-06 21:22:37', 1),
(8, 'Mico Cuenco', 'micocuenco@gmail.com', '09123456789', '$2y$10$5TbZ4Vpu14roc6HtE2ijzeRSQjL3QQxHxdfK9LNe6CduMDB1wro9O', 'user', '2026-02-06 19:32:34', '2026-02-06 19:33:25', '2026-02-06 19:33:25', 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `login_history`
--
ALTER TABLE `login_history`
  ADD PRIMARY KEY (`login_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_login_time` (`login_time`);

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
  ADD KEY `fk_comment_user` (`user_id`);

--
-- Indexes for table `post_likes`
--
ALTER TABLE `post_likes`
  ADD PRIMARY KEY (`like_id`),
  ADD UNIQUE KEY `unique_like` (`post_id`,`user_id`),
  ADD KEY `fk_like_user` (`user_id`);

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
-- AUTO_INCREMENT for table `login_history`
--
ALTER TABLE `login_history`
  MODIFY `login_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;

--
-- AUTO_INCREMENT for table `posts`
--
ALTER TABLE `posts`
  MODIFY `post_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `post_comments`
--
ALTER TABLE `post_comments`
  MODIFY `comment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `post_likes`
--
ALTER TABLE `post_likes`
  MODIFY `like_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- AUTO_INCREMENT for table `post_reports`
--
ALTER TABLE `post_reports`
  MODIFY `report_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `login_history`
--
ALTER TABLE `login_history`
  ADD CONSTRAINT `login_history_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `posts`
--
ALTER TABLE `posts`
  ADD CONSTRAINT `fk_user_post` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `post_comments`
--
ALTER TABLE `post_comments`
  ADD CONSTRAINT `fk_comment_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `post_likes`
--
ALTER TABLE `post_likes`
  ADD CONSTRAINT `fk_like_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

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

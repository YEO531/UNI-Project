-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 01, 2025 at 05:42 AM
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
-- Database: `leave_management_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(50) NOT NULL,
  `description` text NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `activity_logs`
--

INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, 4, 'Login', 'User logged in', NULL, NULL, '2025-06-18 06:53:12'),
(2, 3, 'Login', 'User logged in', NULL, NULL, '2025-06-18 06:57:01'),
(3, 3, 'approve_leave', 'Approved leave application ID: 3', NULL, NULL, '2025-06-18 06:57:19'),
(4, 3, 'Login', 'User logged in', NULL, NULL, '2025-06-18 07:01:06'),
(5, 4, 'Login', 'User logged in', NULL, NULL, '2025-06-18 07:02:31'),
(6, 3, 'Login', 'User logged in', NULL, NULL, '2025-06-18 07:23:36'),
(7, 3, 'approve_leave', 'Approved leave application ID: 1', NULL, NULL, '2025-06-18 07:42:32'),
(8, 3, 'Login', 'User logged in', NULL, NULL, '2025-06-18 08:04:50'),
(9, 3, 'Login', 'User logged in', NULL, NULL, '2025-06-18 08:04:51'),
(10, 3, 'Login', 'User logged in', NULL, NULL, '2025-06-18 09:23:19'),
(11, 3, 'Login', 'User logged in', NULL, NULL, '2025-06-18 13:05:28'),
(12, 4, 'Login', 'User logged in', NULL, NULL, '2025-06-18 15:00:37'),
(13, 4, 'Login', 'User logged in', NULL, NULL, '2025-06-19 01:45:52'),
(14, 3, 'Login', 'User logged in', NULL, NULL, '2025-06-19 01:47:38'),
(15, 3, 'Login', 'User logged in', NULL, NULL, '2025-06-19 03:49:49'),
(16, NULL, 'Registration', 'New user registration', NULL, NULL, '2025-06-19 04:19:33'),
(17, NULL, 'Login', 'User logged in', NULL, NULL, '2025-06-19 04:19:58'),
(18, NULL, 'Login', 'User logged in', NULL, NULL, '2025-06-19 06:35:12'),
(19, NULL, 'Login', 'User logged in', NULL, NULL, '2025-06-19 06:35:18'),
(20, 3, 'Login', 'User logged in', NULL, NULL, '2025-06-19 06:35:38'),
(21, NULL, 'Registration', 'New user registration', NULL, NULL, '2025-06-19 06:37:30'),
(22, NULL, 'Registration', 'New user registration', NULL, NULL, '2025-06-19 06:40:51'),
(23, NULL, 'Registration', 'New user registration', NULL, NULL, '2025-06-19 06:48:55'),
(24, NULL, 'Registration', 'New user registration', NULL, NULL, '2025-06-19 06:50:03'),
(25, NULL, 'Registration', 'New user registration', NULL, NULL, '2025-06-19 06:59:23'),
(26, NULL, 'Login', 'User logged in', NULL, NULL, '2025-06-19 06:59:36'),
(27, 3, 'Login', 'User logged in', NULL, NULL, '2025-06-19 07:00:51'),
(28, 3, 'update_leave', 'Updated leave application ID: 4', NULL, NULL, '2025-06-19 07:34:23'),
(29, 3, 'update_leave', 'Updated leave application ID: 4', NULL, NULL, '2025-06-19 07:34:32'),
(30, 3, 'update_leave', 'Updated leave application ID: 4', NULL, NULL, '2025-06-19 07:34:44'),
(31, 3, 'update_leave', 'Updated leave application ID: 4', NULL, NULL, '2025-06-19 07:35:12'),
(32, 3, 'update_leave', 'Updated leave application ID: 4', NULL, NULL, '2025-06-19 07:35:45'),
(33, 3, 'update_leave', 'Updated leave application ID: 4', NULL, NULL, '2025-06-19 07:35:56'),
(34, 3, 'update_leave', 'Updated leave application ID: 4', NULL, NULL, '2025-06-19 07:36:43'),
(35, 3, 'update_leave', 'Updated leave application ID: 4', NULL, NULL, '2025-06-19 07:36:50'),
(36, 3, 'update_leave', 'Updated leave application ID: 4', NULL, NULL, '2025-06-19 07:36:56'),
(37, 3, 'update_leave', 'Updated leave application ID: 4', NULL, NULL, '2025-06-19 07:37:46'),
(38, 3, 'Login', 'User logged in', NULL, NULL, '2025-06-21 06:44:50'),
(39, 3, 'Login', 'User logged in', NULL, NULL, '2025-06-22 07:47:53'),
(40, 3, 'Login', 'User logged in', NULL, NULL, '2025-06-22 07:52:37'),
(41, 4, 'Login', 'User logged in', NULL, NULL, '2025-06-22 07:53:12'),
(42, 3, 'Login', 'User logged in', NULL, NULL, '2025-06-22 08:00:01'),
(43, 3, 'Login', 'User logged in', NULL, NULL, '2025-06-22 08:01:14'),
(44, 3, 'Login', 'User logged in', NULL, NULL, '2025-06-22 08:04:06'),
(45, 3, 'Login', 'User logged in', NULL, NULL, '2025-06-22 08:06:37'),
(46, 3, 'Login', 'User logged in', NULL, NULL, '2025-06-22 08:16:24'),
(47, 3, 'Login', 'User logged in', NULL, NULL, '2025-06-22 08:37:33'),
(48, 3, 'Login', 'User logged in', NULL, NULL, '2025-06-22 08:39:13'),
(49, 3, 'Login', 'User logged in', NULL, NULL, '2025-06-22 08:40:56'),
(50, 3, 'Login', 'User logged in', NULL, NULL, '2025-06-22 08:42:21'),
(52, 3, 'Login', 'User logged in', NULL, NULL, '2025-06-22 08:42:56'),
(53, 3, 'update_leave', 'Updated leave application ID: 3', NULL, NULL, '2025-06-22 09:01:49'),
(54, 3, 'approve_leave', 'Approved leave application ID: 2', NULL, NULL, '2025-06-22 09:02:04'),
(55, 3, 'approve_leave', 'Approved leave application ID: 5', NULL, NULL, '2025-06-22 09:04:03'),
(56, 3, 'approve_leave', 'Approved leave application ID: 6', NULL, NULL, '2025-06-22 09:05:02'),
(57, 3, 'update_leave', 'Updated leave application ID: 4', NULL, NULL, '2025-06-22 09:06:39'),
(58, 3, 'update_leave', 'Updated leave application ID: 3', NULL, NULL, '2025-06-22 09:07:31'),
(59, 3, 'Login', 'User logged in', NULL, NULL, '2025-06-22 09:17:40'),
(60, 3, 'Login', 'User logged in', NULL, NULL, '2025-06-22 09:22:08'),
(61, 3, 'update_leave', 'Updated leave application ID: 8', NULL, NULL, '2025-06-22 09:22:43'),
(62, 3, 'update_leave', 'Updated leave application ID: 8', NULL, NULL, '2025-06-22 09:22:55'),
(63, 3, 'Login', 'User logged in', NULL, NULL, '2025-06-24 01:37:55'),
(64, 3, 'update_leave', 'Updated leave application ID: 9', NULL, NULL, '2025-06-24 01:41:42'),
(65, 3, 'update_leave', 'Updated leave application ID: 9', NULL, NULL, '2025-06-24 01:41:51'),
(66, 3, 'update_leave', 'Updated leave application ID: 6', NULL, NULL, '2025-06-24 01:42:17'),
(67, 3, 'update_leave', 'Updated leave application ID: 6', NULL, NULL, '2025-06-24 01:42:21'),
(68, 3, 'update_leave', 'Updated leave application ID: 5', NULL, NULL, '2025-06-24 01:42:25'),
(69, 3, 'update_leave', 'Updated leave application ID: 4', NULL, NULL, '2025-06-24 01:42:29'),
(70, NULL, 'Login', 'User logged in', NULL, NULL, '2025-06-24 02:32:53'),
(71, 3, 'Login', 'User logged in', NULL, NULL, '2025-06-24 02:34:32'),
(72, 3, 'approve_leave', 'Approved leave application ID: 13', NULL, NULL, '2025-06-24 02:34:42'),
(73, 3, 'approve_leave', 'Approved leave application ID: 12', NULL, NULL, '2025-06-24 02:34:44'),
(74, 3, 'reject_leave', 'Rejected leave application ID: 11', NULL, NULL, '2025-06-24 02:37:08'),
(75, 3, 'update_leave', 'Updated leave application ID: 11', NULL, NULL, '2025-06-24 02:38:05'),
(76, 3, 'reject_leave', 'Rejected leave application ID: 11', NULL, NULL, '2025-06-24 02:38:09'),
(77, 3, 'reject_leave', 'Rejected leave application ID: 11', NULL, NULL, '2025-06-24 02:43:09'),
(78, 3, 'reject_leave', 'Rejected leave application ID: 11', NULL, NULL, '2025-06-24 02:48:10'),
(79, 3, 'Profile Update', 'User updated their profile information', NULL, NULL, '2025-06-24 02:58:46'),
(80, 3, 'Profile Update', 'User updated their profile information', NULL, NULL, '2025-06-24 02:58:54'),
(81, 3, 'Profile Update', 'User updated their profile information', NULL, NULL, '2025-06-24 02:59:00'),
(82, 3, 'Profile Update', 'User updated their profile information', NULL, NULL, '2025-06-24 02:59:10'),
(83, 3, 'Profile Update', 'User updated their profile information', NULL, NULL, '2025-06-24 02:59:36'),
(84, 3, 'Profile Update', 'User updated their profile information', NULL, NULL, '2025-06-24 02:59:51'),
(85, 3, 'Login', 'User logged in', NULL, NULL, '2025-06-24 03:00:04'),
(86, 3, 'Profile Update', 'User updated their profile information', NULL, NULL, '2025-06-24 03:01:43'),
(87, 3, 'Profile Update', 'User updated their profile information', NULL, NULL, '2025-06-24 03:04:51'),
(88, 3, 'approve_leave', 'Approved leave application ID: 27', NULL, NULL, '2025-06-24 03:32:03'),
(89, 4, 'Login', 'User logged in', NULL, NULL, '2025-06-24 03:34:18'),
(90, 3, 'Login', 'User logged in', NULL, NULL, '2025-06-24 03:34:46'),
(91, 4, 'Login', 'User logged in', NULL, NULL, '2025-06-24 03:40:39'),
(92, 3, 'Login', 'User logged in', NULL, NULL, '2025-06-24 03:42:25'),
(93, 3, 'reject_leave', 'Rejected leave application ID: 28', NULL, NULL, '2025-06-24 03:42:40'),
(94, 4, 'Login', 'User logged in', NULL, NULL, '2025-06-24 03:42:56'),
(95, 4, 'Profile Update', 'User updated their profile information', NULL, NULL, '2025-06-24 03:43:48'),
(96, 4, 'Profile Update', 'User updated their profile information', NULL, NULL, '2025-06-24 03:50:52'),
(97, 4, 'Profile Update', 'User updated their profile information', NULL, NULL, '2025-06-24 03:55:39'),
(98, 4, 'Login', 'User logged in', NULL, NULL, '2025-06-30 11:26:07'),
(99, 4, 'Login', 'User logged in', NULL, NULL, '2025-06-30 11:29:50'),
(100, 3, 'Login', 'User logged in', NULL, NULL, '2025-06-30 11:32:32'),
(101, 3, 'Login', 'User logged in', NULL, NULL, '2025-07-01 02:34:08');

-- --------------------------------------------------------

--
-- Table structure for table `admin_actions`
--

CREATE TABLE `admin_actions` (
  `id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `action_type` enum('approve_leave','reject_leave','edit_leave','delete_leave','add_user','edit_user','delete_user','system_setting','bulk_action','report_generated') NOT NULL,
  `target_type` enum('leave_application','user','system','bulk','report') NOT NULL,
  `target_id` int(11) DEFAULT NULL,
  `details` text NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_actions`
--

INSERT INTO `admin_actions` (`id`, `admin_id`, `action_type`, `target_type`, `target_id`, `details`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, 3, 'approve_leave', 'leave_application', 3, 'Approved leave application ID: 3', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-18 06:57:19'),
(2, 3, 'approve_leave', 'leave_application', 1, 'Approved leave application ID: 1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-18 07:42:32'),
(3, 3, 'edit_leave', 'leave_application', 4, 'Updated leave application ID: 4 (Total days: 105)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-19 07:34:23'),
(4, 3, 'edit_leave', 'leave_application', 4, 'Updated leave application ID: 4 (Total days: 105)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-19 07:34:32'),
(5, 3, 'edit_leave', 'leave_application', 4, 'Updated leave application ID: 4 (Total days: 105)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-19 07:34:44'),
(6, 3, 'edit_leave', 'leave_application', 4, 'Updated leave application ID: 4 (Total days: 105)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-19 07:35:12'),
(7, 3, 'edit_leave', 'leave_application', 4, 'Updated leave application ID: 4 (Total days: 136)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-19 07:35:45'),
(8, 3, 'edit_leave', 'leave_application', 4, 'Updated leave application ID: 4 (Total days: 167)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-19 07:35:56'),
(9, 3, 'edit_leave', 'leave_application', 4, 'Updated leave application ID: 4 (Total days: 167)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-19 07:36:43'),
(10, 3, 'edit_leave', 'leave_application', 4, 'Updated leave application ID: 4 (Total days: 167)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-19 07:36:50'),
(11, 3, 'edit_leave', 'leave_application', 4, 'Updated leave application ID: 4 (Total days: 167)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-19 07:36:56'),
(12, 3, 'edit_leave', 'leave_application', 4, 'Updated leave application ID: 4 (Total days: 14)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-19 07:37:46'),
(13, 3, 'edit_leave', 'leave_application', 3, 'Updated leave application ID: 3 (Total days: 3)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-22 09:01:49'),
(14, 3, 'approve_leave', 'leave_application', 2, 'Approved leave application ID: 2', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-22 09:02:04'),
(15, 3, 'approve_leave', 'leave_application', 5, 'Approved leave application ID: 5', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-22 09:04:03'),
(16, 3, 'approve_leave', 'leave_application', 6, 'Approved leave application ID: 6', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-22 09:05:02'),
(17, 3, 'edit_leave', 'leave_application', 4, 'Updated leave application ID: 4 (Total days: 14)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-22 09:06:39'),
(18, 3, 'edit_leave', 'leave_application', 3, 'Updated leave application ID: 3 (Total days: 3)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-22 09:07:31'),
(19, 3, 'edit_leave', 'leave_application', 8, 'Updated leave application ID: 8 (Total days: 2)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-22 09:22:43'),
(20, 3, 'edit_leave', 'leave_application', 8, 'Updated leave application ID: 8 (Total days: 2)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-22 09:22:55'),
(21, 3, 'edit_leave', 'leave_application', 9, 'Updated leave application ID: 9 (Total days: 1)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-24 01:41:42'),
(22, 3, 'edit_leave', 'leave_application', 9, 'Updated leave application ID: 9 (Total days: 1)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-24 01:41:51'),
(23, 3, 'edit_leave', 'leave_application', 6, 'Updated leave application ID: 6 (Total days: 1)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-24 01:42:17'),
(24, 3, 'edit_leave', 'leave_application', 6, 'Updated leave application ID: 6 (Total days: 1)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-24 01:42:21'),
(25, 3, 'edit_leave', 'leave_application', 5, 'Updated leave application ID: 5 (Total days: 1)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-24 01:42:25'),
(26, 3, 'edit_leave', 'leave_application', 4, 'Updated leave application ID: 4 (Total days: 14)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-24 01:42:29'),
(27, 3, 'approve_leave', 'leave_application', 13, 'Approved leave application ID: 13', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-24 02:34:42'),
(28, 3, 'approve_leave', 'leave_application', 12, 'Approved leave application ID: 12', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-24 02:34:44'),
(29, 3, 'reject_leave', 'leave_application', 11, 'Rejected leave application ID: 11. Reason: test', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-24 02:37:08'),
(30, 3, 'edit_leave', 'leave_application', 11, 'Updated leave application ID: 11 (Total days: 14)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-24 02:38:05'),
(31, 3, 'reject_leave', 'leave_application', 11, 'Rejected leave application ID: 11. Reason: tesr', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-24 02:38:09'),
(32, 3, 'reject_leave', 'leave_application', 11, 'Rejected leave application ID: 11. Reason: tesr', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-24 02:43:09'),
(33, 3, 'reject_leave', 'leave_application', 11, 'Rejected leave application ID: 11. Reason: tesr', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-24 02:48:10'),
(34, 3, 'approve_leave', 'leave_application', 27, 'Approved leave application ID: 27', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-24 03:32:03'),
(35, 3, 'reject_leave', 'leave_application', 28, 'Rejected leave application ID: 28. Reason: no reason', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-24 03:42:40');

-- --------------------------------------------------------

--
-- Table structure for table `admin_bulk_actions`
--

CREATE TABLE `admin_bulk_actions` (
  `id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `action_type` enum('bulk_approve','bulk_reject','bulk_edit','bulk_delete','bulk_export','bulk_notify') NOT NULL,
  `target_count` int(11) NOT NULL,
  `completed_count` int(11) NOT NULL DEFAULT 0,
  `failed_count` int(11) NOT NULL DEFAULT 0,
  `status` enum('pending','processing','completed','failed','cancelled') NOT NULL DEFAULT 'pending',
  `parameters` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`parameters`)),
  `result_summary` text DEFAULT NULL,
  `started_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `completed_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `admin_dashboard_widgets`
--

CREATE TABLE `admin_dashboard_widgets` (
  `id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `widget_type` enum('statistics','chart','table','calendar','recent_activity','pending_approvals','quick_actions') NOT NULL,
  `widget_title` varchar(255) NOT NULL,
  `widget_config` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`widget_config`)),
  `position` int(11) NOT NULL DEFAULT 0,
  `is_visible` tinyint(1) NOT NULL DEFAULT 1,
  `is_collapsed` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `admin_notifications`
--

CREATE TABLE `admin_notifications` (
  `id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `notification_type` enum('pending_approval','system_alert','user_request','report_ready','security_alert','bulk_action_complete') NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `priority` enum('low','medium','high','urgent') NOT NULL DEFAULT 'medium',
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `action_url` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_notifications`
--

INSERT INTO `admin_notifications` (`id`, `admin_id`, `notification_type`, `title`, `message`, `priority`, `is_read`, `action_url`, `created_at`) VALUES
(1, 3, 'pending_approval', 'Leave Request Approved', 'Leave application #3 has been approved', 'medium', 0, NULL, '2025-06-18 06:57:19'),
(2, 3, 'pending_approval', 'Leave Request Approved', 'Leave application #1 has been approved', 'medium', 0, NULL, '2025-06-18 07:42:32'),
(3, 3, 'pending_approval', 'Leave Request Approved', 'Leave application #2 has been approved', 'medium', 0, NULL, '2025-06-22 09:02:04'),
(4, 3, 'pending_approval', 'Leave Request Approved', 'Leave application #5 has been approved', 'medium', 0, NULL, '2025-06-22 09:04:03'),
(5, 3, 'pending_approval', 'Leave Request Approved', 'Leave application #6 has been approved', 'medium', 0, NULL, '2025-06-22 09:05:02'),
(6, 3, 'pending_approval', 'Leave Request Approved', 'Leave application #13 has been approved', 'medium', 0, NULL, '2025-06-24 02:34:42'),
(7, 3, 'pending_approval', 'Leave Request Approved', 'Leave application #12 has been approved', 'medium', 0, NULL, '2025-06-24 02:34:44'),
(8, 3, 'pending_approval', 'Leave Request Rejected', 'Leave application #11 has been rejected', 'medium', 0, NULL, '2025-06-24 02:37:08'),
(9, 3, 'pending_approval', 'Leave Request Rejected', 'Leave application #11 has been rejected', 'medium', 0, NULL, '2025-06-24 02:38:09'),
(10, 3, 'pending_approval', 'Leave Request Rejected', 'Leave application #11 has been rejected', 'medium', 0, NULL, '2025-06-24 02:43:09'),
(11, 3, 'pending_approval', 'Leave Request Rejected', 'Leave application #11 has been rejected', 'medium', 0, NULL, '2025-06-24 02:48:10'),
(12, 3, 'pending_approval', 'Leave Request Approved', 'Leave application #27 has been approved', 'medium', 0, NULL, '2025-06-24 03:32:03'),
(13, 3, 'pending_approval', 'Leave Request Rejected', 'Leave application #28 has been rejected', 'medium', 0, NULL, '2025-06-24 03:42:40');

-- --------------------------------------------------------

--
-- Table structure for table `admin_permissions`
--

CREATE TABLE `admin_permissions` (
  `id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `permission_type` enum('view_applications','approve_applications','reject_applications','edit_applications','delete_applications','manage_users','manage_departments','manage_leave_types','view_reports','generate_reports','system_settings','audit_logs','bulk_actions') NOT NULL,
  `is_granted` tinyint(1) NOT NULL DEFAULT 1,
  `granted_by` int(11) DEFAULT NULL,
  `granted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `admin_reports`
--

CREATE TABLE `admin_reports` (
  `id` int(11) NOT NULL,
  `report_name` varchar(255) NOT NULL,
  `report_type` enum('leave_summary','department_summary','user_summary','approval_summary','custom') NOT NULL,
  `generated_by` int(11) NOT NULL,
  `parameters` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`parameters`)),
  `file_path` varchar(500) DEFAULT NULL,
  `file_size` int(11) DEFAULT NULL,
  `download_count` int(11) DEFAULT 0,
  `is_public` tinyint(1) NOT NULL DEFAULT 0,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `admin_settings`
--

CREATE TABLE `admin_settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text NOT NULL,
  `setting_type` enum('boolean','integer','string','json','date') NOT NULL DEFAULT 'string',
  `category` enum('dashboard','notifications','reports','security','approval','system') NOT NULL DEFAULT 'system',
  `description` text DEFAULT NULL,
  `is_editable` tinyint(1) NOT NULL DEFAULT 1,
  `created_by` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_settings`
--

INSERT INTO `admin_settings` (`id`, `setting_key`, `setting_value`, `setting_type`, `category`, `description`, `is_editable`, `created_by`, `updated_by`, `created_at`, `updated_at`) VALUES
(1, 'dashboard_refresh_interval', '300', 'integer', 'dashboard', 'Dashboard auto-refresh interval in seconds', 1, NULL, NULL, '2025-06-18 06:49:55', '2025-06-18 06:49:55'),
(2, 'max_pending_display', '50', 'integer', 'dashboard', 'Maximum pending applications to display on dashboard', 1, NULL, NULL, '2025-06-18 06:49:55', '2025-06-18 06:49:55'),
(3, 'approval_notification_email', '1', 'boolean', 'notifications', 'Send email notifications for pending approvals', 1, NULL, NULL, '2025-06-18 06:49:55', '2025-06-18 06:49:55'),
(4, 'bulk_action_limit', '100', 'integer', 'approval', 'Maximum items for bulk actions', 1, NULL, NULL, '2025-06-18 06:49:55', '2025-06-18 06:49:55'),
(5, 'report_retention_days', '90', 'integer', 'reports', 'Number of days to keep generated reports', 1, NULL, NULL, '2025-06-18 06:49:55', '2025-06-18 06:49:55'),
(6, 'audit_log_retention_days', '365', 'integer', 'security', 'Number of days to keep audit logs', 1, NULL, NULL, '2025-06-18 06:49:55', '2025-06-18 06:49:55'),
(7, 'auto_approve_emergency_leave', '0', 'boolean', 'approval', 'Automatically approve emergency leave requests', 1, NULL, NULL, '2025-06-18 06:49:55', '2025-06-18 06:49:55'),
(8, 'require_rejection_reason', '1', 'boolean', 'approval', 'Require reason when rejecting leave applications', 1, NULL, NULL, '2025-06-18 06:49:55', '2025-06-18 06:49:55'),
(9, 'dashboard_widgets', '[\"statistics\",\"pending_approvals\",\"recent_activity\",\"quick_actions\"]', 'json', 'dashboard', 'Default dashboard widgets configuration', 1, NULL, NULL, '2025-06-18 06:49:55', '2025-06-18 06:49:55'),
(10, 'admin_session_timeout', '3600', 'integer', 'security', 'Admin session timeout in seconds', 1, NULL, NULL, '2025-06-18 06:49:55', '2025-06-18 06:49:55');

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(255) NOT NULL,
  `details` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `calendar_notes`
--

CREATE TABLE `calendar_notes` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `note_date` date NOT NULL,
  `note` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `calendar_notes`
--

INSERT INTO `calendar_notes` (`id`, `user_id`, `note_date`, `note`, `created_at`, `updated_at`) VALUES
(1, 3, '2025-06-18', 'test', '2025-06-18 07:01:24', '2025-06-18 07:01:24'),
(10, 4, '2025-06-26', 'test', '2025-06-30 11:27:44', '2025-06-30 11:27:44');

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `head_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`id`, `name`, `description`, `head_id`, `created_at`, `updated_at`) VALUES
(2, 'Human Resources', 'HR department', NULL, '2025-06-18 06:49:55', '2025-06-18 06:49:55'),
(3, 'Information Technology', 'IT department', NULL, '2025-06-18 06:49:55', '2025-06-18 06:49:55'),
(4, 'Finance', 'Finance and accounting', NULL, '2025-06-18 06:49:55', '2025-06-18 06:49:55'),
(5, 'Operations', 'Operations management', NULL, '2025-06-18 06:49:55', '2025-06-18 06:49:55'),
(6, 'Administration', 'General administration and management', NULL, '2025-06-18 07:04:24', '2025-06-18 07:04:24'),
(8, 'Business Development', 'Business growth and strategic partnerships', NULL, '2025-06-18 08:15:24', '2025-06-24 03:58:38'),
(17, 'Communications', 'Internal and external communications', NULL, '2025-06-18 08:15:24', '2025-06-18 08:15:24'),
(18, 'Compliance', 'Regulatory compliance and risk management', NULL, '2025-06-18 08:15:24', '2025-06-18 08:15:24'),
(19, 'Customer Service', 'Customer support and service', NULL, '2025-06-18 08:15:24', '2025-06-18 08:15:24'),
(20, 'Engineering', 'Engineering and technical development', NULL, '2025-06-18 08:15:24', '2025-06-18 08:15:24'),
(21, 'Facilities', 'Facilities management and maintenance', NULL, '2025-06-18 08:15:24', '2025-06-18 08:15:24'),
(25, 'Legal', 'Legal affairs and compliance', NULL, '2025-06-18 08:15:24', '2025-06-18 08:15:24'),
(26, 'Marketing', 'Marketing and brand management', NULL, '2025-06-18 08:15:24', '2025-06-18 08:15:24'),
(28, 'Product Management', 'Product strategy and development', NULL, '2025-06-18 08:15:24', '2025-06-18 08:15:24'),
(29, 'Quality Assurance', 'Quality control and testing', NULL, '2025-06-18 08:15:24', '2025-06-18 08:15:24'),
(30, 'Research and Development', 'R&D and innovation', NULL, '2025-06-18 08:15:24', '2025-06-18 08:15:24'),
(31, 'Sales', 'Sales and revenue generation', NULL, '2025-06-18 08:15:24', '2025-06-18 08:15:24'),
(32, 'Security', 'Security and safety management', NULL, '2025-06-18 08:15:24', '2025-06-18 08:15:24'),
(33, 'Training', 'Employee training and development', NULL, '2025-06-18 08:15:24', '2025-06-18 08:15:24'),
(34, 'Administration', 'Administrative operations and management', NULL, '2025-06-18 08:19:23', '2025-06-18 08:19:23'),
(35, 'Business Development', 'Business growth and strategic partnerships', NULL, '2025-06-18 08:19:23', '2025-06-18 08:19:23'),
(36, 'Communications', 'Internal and external communications', NULL, '2025-06-18 08:19:23', '2025-06-18 08:19:23'),
(37, 'Compliance', 'Regulatory compliance and risk management', NULL, '2025-06-18 08:19:23', '2025-06-18 08:19:23'),
(38, 'Customer Service', 'Customer support and service', NULL, '2025-06-18 08:19:23', '2025-06-18 08:19:23'),
(39, 'Engineering', 'Engineering and technical development', NULL, '2025-06-18 08:19:23', '2025-06-18 08:19:23'),
(40, 'Facilities', 'Facilities management and maintenance', NULL, '2025-06-18 08:19:23', '2025-06-18 08:19:23'),
(41, 'Finance', 'Financial operations and accounting', NULL, '2025-06-18 08:19:23', '2025-06-18 08:19:23'),
(42, 'Human Resources', 'HR and personnel management', NULL, '2025-06-18 08:19:23', '2025-06-18 08:19:23'),
(43, 'Information Technology', 'IT infrastructure and support', NULL, '2025-06-18 08:19:23', '2025-06-18 08:19:23'),
(44, 'Legal', 'Legal affairs and compliance', NULL, '2025-06-18 08:19:23', '2025-06-18 08:19:23'),
(45, 'Marketing', 'Marketing and brand management', NULL, '2025-06-18 08:19:23', '2025-06-18 08:19:23'),
(46, 'Operations', 'Operations management', NULL, '2025-06-18 08:19:23', '2025-06-18 08:19:23'),
(47, 'Product Management', 'Product strategy and development', NULL, '2025-06-18 08:19:23', '2025-06-18 08:19:23'),
(48, 'Quality Assurance', 'Quality control and testing', NULL, '2025-06-18 08:19:23', '2025-06-18 08:19:23'),
(49, 'Research and Development', 'R&D and innovation', NULL, '2025-06-18 08:19:23', '2025-06-18 08:19:23'),
(50, 'Sales', 'Sales and revenue generation', NULL, '2025-06-18 08:19:23', '2025-06-18 08:19:23'),
(51, 'Security', 'Security and safety management', NULL, '2025-06-18 08:19:23', '2025-06-18 08:19:23'),
(52, 'Training', 'Employee training and development', NULL, '2025-06-18 08:19:23', '2025-06-18 08:19:23');

-- --------------------------------------------------------

--
-- Table structure for table `leave_applications`
--

CREATE TABLE `leave_applications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `leave_type_id` int(11) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `total_days` int(11) NOT NULL,
  `reason` text NOT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `leave_applications`
--

INSERT INTO `leave_applications` (`id`, `user_id`, `leave_type_id`, `start_date`, `end_date`, `total_days`, `reason`, `status`, `approved_by`, `approved_at`, `created_at`, `updated_at`) VALUES
(1, 4, 4, '2025-06-18', '2025-08-21', 65, 'Child Born', 'approved', 3, '2025-06-18 07:42:32', '2025-06-18 06:53:52', '2025-06-18 07:42:32'),
(2, 4, 6, '2025-06-18', '2025-06-20', 3, 'accompaying my wife for a prenatal checkup', 'approved', 3, '2025-06-22 09:02:04', '2025-06-18 06:54:55', '2025-06-22 09:02:04'),
(28, 4, 1, '2025-06-24', '2025-06-24', 1, 'test', 'rejected', 3, '2025-06-24 03:42:40', '2025-06-24 03:42:17', '2025-06-24 03:42:40'),
(29, 4, 4, '2025-06-30', '2025-07-03', 4, 'test', 'pending', NULL, NULL, '2025-06-30 11:31:12', '2025-06-30 11:31:12');

-- --------------------------------------------------------

--
-- Table structure for table `leave_balances`
--

CREATE TABLE `leave_balances` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `leave_type_id` int(11) NOT NULL,
  `total_days` int(11) NOT NULL,
  `used_days` int(11) NOT NULL DEFAULT 0,
  `balance` int(11) NOT NULL DEFAULT 0,
  `year` int(4) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `leave_balances`
--

INSERT INTO `leave_balances` (`id`, `user_id`, `leave_type_id`, `total_days`, `used_days`, `balance`, `year`, `created_at`, `updated_at`) VALUES
(25, 3, 1, 14, 0, 14, 2025, '2025-06-18 07:48:34', '2025-06-18 07:48:34'),
(26, 3, 2, 14, 0, 14, 2025, '2025-06-18 07:48:34', '2025-06-18 07:48:34'),
(27, 3, 3, 90, 2, 88, 2025, '2025-06-18 07:48:34', '2025-06-22 09:05:02'),
(28, 3, 4, 7, 0, 7, 2025, '2025-06-18 07:48:34', '2025-06-18 07:58:43'),
(29, 3, 5, 30, 0, 30, 2025, '2025-06-18 07:48:34', '2025-06-18 07:48:34'),
(30, 3, 6, 3, 1, 2, 2025, '2025-06-18 07:48:34', '2025-06-24 03:32:03'),
(37, 4, 1, 14, 0, 14, 2025, '2025-06-18 07:48:34', '2025-06-18 07:48:34'),
(38, 4, 2, 14, 0, 14, 2025, '2025-06-18 07:48:34', '2025-06-18 07:48:34'),
(39, 4, 3, 90, 0, 90, 2025, '2025-06-18 07:48:34', '2025-06-18 07:48:34'),
(40, 4, 4, 7, 0, 7, 2025, '2025-06-18 07:48:34', '2025-06-18 07:58:43'),
(41, 4, 5, 30, 0, 30, 2025, '2025-06-18 07:48:34', '2025-06-18 07:48:34'),
(42, 4, 6, 3, 3, 0, 2025, '2025-06-18 07:48:34', '2025-06-22 09:02:04');

-- --------------------------------------------------------

--
-- Table structure for table `leave_types`
--

CREATE TABLE `leave_types` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `max_days` int(11) NOT NULL,
  `requires_approval` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `leave_types`
--

INSERT INTO `leave_types` (`id`, `name`, `description`, `max_days`, `requires_approval`, `created_at`, `updated_at`) VALUES
(1, 'Annual Leave', 'Regular annual leave entitlement', 14, 1, '2025-06-18 06:49:55', '2025-06-18 06:49:55'),
(2, 'Sick Leave', 'Medical leave with doctor\'s certificate', 14, 1, '2025-06-18 06:49:55', '2025-06-18 06:49:55'),
(3, 'Maternity Leave', 'Leave for childbirth and care', 90, 1, '2025-06-18 06:49:55', '2025-06-18 06:49:55'),
(4, 'Paternity Leave', 'Leave for new fathers', 90, 1, '2025-06-18 06:49:55', '2025-06-18 09:24:05'),
(5, 'Unpaid Leave', 'Leave without pay', 30, 1, '2025-06-18 06:49:55', '2025-06-18 06:49:55'),
(6, 'Emergency Leave', 'Leave for emergency situations', 3, 1, '2025-06-18 06:49:55', '2025-06-18 06:49:55');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `type` enum('info','success','warning','error') NOT NULL DEFAULT 'info',
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `title`, `message`, `type`, `is_read`, `created_at`) VALUES
(1, 4, 'Leave Request Approved', 'Your leave request from 2025-06-18 to 2025-06-20 has been approved.', 'success', 0, '2025-06-18 06:57:19'),
(2, 4, 'Leave Request Approved', 'Your leave request from 2025-06-18 to 2025-08-21 has been approved.', 'success', 0, '2025-06-18 07:42:32'),
(3, 4, 'Leave Request Approved', 'Your leave request from 2025-06-18 to 2025-06-20 has been approved.', 'success', 0, '2025-06-22 09:02:04'),
(4, 3, 'Leave Request Approved', 'Your leave request from 2025-06-22 to 2025-06-22 has been approved.', 'success', 0, '2025-06-22 09:04:03'),
(5, 3, 'Leave Request Approved', 'Your leave request from 2025-06-26 to 2025-06-26 has been approved.', 'success', 0, '2025-06-22 09:05:02'),
(12, 3, 'Leave Request Approved', 'Your leave request from 2025-06-24 to 2025-06-24 has been approved.', 'success', 0, '2025-06-24 03:32:03'),
(13, 4, 'Leave Request Rejected', 'Your leave request from 2025-06-24 to 2025-06-24 has been rejected. Reason: no reason', 'error', 0, '2025-06-24 03:42:40');

-- --------------------------------------------------------

--
-- Table structure for table `positions`
--

CREATE TABLE `positions` (
  `id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `department_id` int(11) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `positions`
--

INSERT INTO `positions` (`id`, `title`, `department_id`, `description`, `created_at`, `updated_at`) VALUES
(2, 'HR Manager', 2, 'Manages human resources', '2025-06-18 06:49:55', '2025-06-18 06:49:55'),
(3, 'IT Manager', 3, 'Manages IT infrastructure', '2025-06-18 06:49:55', '2025-06-18 06:49:55'),
(4, 'Finance Manager', 4, 'Manages financial operations', '2025-06-18 06:49:55', '2025-06-18 06:49:55'),
(5, 'Operations Manager', 5, 'Manages daily operations', '2025-06-18 06:49:55', '2025-06-18 06:49:55'),
(6, 'HR Manager', 2, 'Manages human resources', '2025-06-18 07:04:24', '2025-06-18 07:04:24'),
(7, 'IT Manager', 3, 'Manages IT infrastructure', '2025-06-18 07:04:24', '2025-06-18 07:04:24'),
(8, 'Finance Manager', 4, 'Manages financial operations', '2025-06-18 07:04:24', '2025-06-18 07:04:24'),
(9, 'Operations Manager', 5, 'Manages daily operations', '2025-06-18 07:04:24', '2025-06-18 07:04:24'),
(10, 'HR Manager', 2, 'Manages human resources', '2025-06-18 07:25:15', '2025-06-18 07:25:15'),
(11, 'IT Manager', 3, 'Manages IT infrastructure', '2025-06-18 07:25:15', '2025-06-18 07:25:15'),
(12, 'Finance Manager', 4, 'Manages financial operations', '2025-06-18 07:25:15', '2025-06-18 07:25:15'),
(13, 'Operations Manager', 5, 'Manages daily operations', '2025-06-18 07:25:15', '2025-06-18 07:25:15');

-- --------------------------------------------------------

--
-- Table structure for table `public_holidays`
--

CREATE TABLE `public_holidays` (
  `id` int(11) NOT NULL,
  `date` date NOT NULL,
  `name` varchar(100) NOT NULL,
  `image_url` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `public_holidays`
--

INSERT INTO `public_holidays` (`id`, `date`, `name`, `image_url`) VALUES
(1, '2025-06-27', 'Awal Muharram', 'https://images.unsplash.com/photo-1690735027049-01cb7292e565?q=80&w=1169&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D'),
(2, '2025-08-31', 'Hari Merdeka', 'https://plus.unsplash.com/premium_photo-1700955569575-439bed2d50f2?q=80&w=1170&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D'),
(3, '2025-09-05', 'Prophet Muhammad\'s Birthday', 'https://images.unsplash.com/photo-1670602328279-82c100b3dfa8?q=80&w=1170&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D'),
(4, '2025-09-16', 'Malaysia Day', 'https://images.unsplash.com/photo-1660084237470-21294dbb7527?q=80&w=735&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D'),
(5, '2025-12-25', 'Christmas Day', 'https://images.unsplash.com/photo-1702076679528-4ee89a3583d5?q=80&w=1010&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D');

-- --------------------------------------------------------

--
-- Table structure for table `system_settings`
--

CREATE TABLE `system_settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `system_settings`
--

INSERT INTO `system_settings` (`id`, `setting_key`, `setting_value`, `description`, `created_at`, `updated_at`) VALUES
(1, 'company_name', 'LeaveTrack', 'Company name', '2025-06-18 06:49:55', '2025-06-18 06:49:55'),
(2, 'company_email', 'admin@leavetrack.com', 'Company email address', '2025-06-18 06:49:55', '2025-06-18 06:49:55'),
(3, 'leave_year_start', '01-01', 'Start date of leave year (MM-DD)', '2025-06-18 06:49:55', '2025-06-18 06:49:55'),
(4, 'max_consecutive_days', '14', 'Maximum consecutive leave days allowed', '2025-06-18 06:49:55', '2025-06-18 06:49:55'),
(5, 'notification_email', '1', 'Enable email notifications (1=yes, 0=no)', '2025-06-18 06:49:55', '2025-06-18 06:49:55'),
(6, 'approval_required', '1', 'Require approval for leave applications (1=yes, 0=no)', '2025-06-18 06:49:55', '2025-06-18 06:49:55');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `employee_id` varchar(50) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `department` varchar(100) NOT NULL,
  `position` varchar(100) NOT NULL,
  `role` enum('employee','admin','super_admin') NOT NULL DEFAULT 'employee',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `department_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `employee_id`, `first_name`, `last_name`, `email`, `password`, `department`, `position`, `role`, `created_at`, `updated_at`, `department_id`) VALUES
(3, '00000', 'Mr', 'Shohel', 'shohel@gmail.com', '$2y$10$qSYO7wIwNpRH9sx7fKxY8OR2UMsAyfDCFkY1TBYArL9uFvM4KSrTO', 'Human Resources', 'Manager', 'admin', '2025-06-18 06:52:14', '2025-06-24 03:55:26', 2),
(4, '12121813', 'LEE', 'JIA SHENG', 'leejiasheng@gmail.com', '$2y$10$J.FbcQtjBKAEZOmfrp2DNu6XTDs36c1HejIlUXjY6tQFjL.mgG0D.', 'Communications', 'Senior IT', 'employee', '2025-06-18 06:53:02', '2025-06-24 03:57:45', 17);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `admin_actions`
--
ALTER TABLE `admin_actions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `admin_id` (`admin_id`),
  ADD KEY `action_type` (`action_type`),
  ADD KEY `target_type` (`target_type`),
  ADD KEY `created_at` (`created_at`);

--
-- Indexes for table `admin_bulk_actions`
--
ALTER TABLE `admin_bulk_actions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `admin_id` (`admin_id`),
  ADD KEY `action_type` (`action_type`),
  ADD KEY `status` (`status`);

--
-- Indexes for table `admin_dashboard_widgets`
--
ALTER TABLE `admin_dashboard_widgets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `admin_id` (`admin_id`),
  ADD KEY `widget_type` (`widget_type`);

--
-- Indexes for table `admin_notifications`
--
ALTER TABLE `admin_notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `admin_id` (`admin_id`),
  ADD KEY `notification_type` (`notification_type`),
  ADD KEY `is_read` (`is_read`),
  ADD KEY `created_at` (`created_at`);

--
-- Indexes for table `admin_permissions`
--
ALTER TABLE `admin_permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `admin_permission` (`admin_id`,`permission_type`),
  ADD KEY `permission_type` (`permission_type`),
  ADD KEY `granted_by` (`granted_by`);

--
-- Indexes for table `admin_reports`
--
ALTER TABLE `admin_reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `report_type` (`report_type`),
  ADD KEY `generated_by` (`generated_by`),
  ADD KEY `created_at` (`created_at`);

--
-- Indexes for table `admin_settings`
--
ALTER TABLE `admin_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`),
  ADD KEY `category` (`category`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `calendar_notes`
--
ALTER TABLE `calendar_notes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `note_date` (`note_date`);

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `head_id` (`head_id`);

--
-- Indexes for table `leave_applications`
--
ALTER TABLE `leave_applications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `leave_type_id` (`leave_type_id`),
  ADD KEY `approved_by` (`approved_by`);

--
-- Indexes for table `leave_balances`
--
ALTER TABLE `leave_balances`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_leave_year` (`user_id`,`leave_type_id`,`year`),
  ADD KEY `leave_type_id` (`leave_type_id`),
  ADD KEY `idx_balance` (`balance`);

--
-- Indexes for table `leave_types`
--
ALTER TABLE `leave_types`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `positions`
--
ALTER TABLE `positions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `department_id` (`department_id`);

--
-- Indexes for table `public_holidays`
--
ALTER TABLE `public_holidays`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `system_settings`
--
ALTER TABLE `system_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `employee_id` (`employee_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `fk_users_department_id` (`department_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=102;

--
-- AUTO_INCREMENT for table `admin_actions`
--
ALTER TABLE `admin_actions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `admin_bulk_actions`
--
ALTER TABLE `admin_bulk_actions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `admin_dashboard_widgets`
--
ALTER TABLE `admin_dashboard_widgets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `admin_notifications`
--
ALTER TABLE `admin_notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `admin_permissions`
--
ALTER TABLE `admin_permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT for table `admin_reports`
--
ALTER TABLE `admin_reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `admin_settings`
--
ALTER TABLE `admin_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `calendar_notes`
--
ALTER TABLE `calendar_notes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

--
-- AUTO_INCREMENT for table `leave_applications`
--
ALTER TABLE `leave_applications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `leave_balances`
--
ALTER TABLE `leave_balances`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=75;

--
-- AUTO_INCREMENT for table `leave_types`
--
ALTER TABLE `leave_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `positions`
--
ALTER TABLE `positions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `public_holidays`
--
ALTER TABLE `public_holidays`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `system_settings`
--
ALTER TABLE `system_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD CONSTRAINT `activity_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `admin_actions`
--
ALTER TABLE `admin_actions`
  ADD CONSTRAINT `admin_actions_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `admin_bulk_actions`
--
ALTER TABLE `admin_bulk_actions`
  ADD CONSTRAINT `admin_bulk_actions_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `admin_dashboard_widgets`
--
ALTER TABLE `admin_dashboard_widgets`
  ADD CONSTRAINT `admin_dashboard_widgets_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `admin_notifications`
--
ALTER TABLE `admin_notifications`
  ADD CONSTRAINT `admin_notifications_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `admin_permissions`
--
ALTER TABLE `admin_permissions`
  ADD CONSTRAINT `admin_permissions_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `admin_permissions_ibfk_2` FOREIGN KEY (`granted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `admin_reports`
--
ALTER TABLE `admin_reports`
  ADD CONSTRAINT `admin_reports_ibfk_1` FOREIGN KEY (`generated_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `admin_settings`
--
ALTER TABLE `admin_settings`
  ADD CONSTRAINT `admin_settings_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `admin_settings_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD CONSTRAINT `audit_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `calendar_notes`
--
ALTER TABLE `calendar_notes`
  ADD CONSTRAINT `calendar_notes_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `departments`
--
ALTER TABLE `departments`
  ADD CONSTRAINT `departments_ibfk_1` FOREIGN KEY (`head_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `leave_applications`
--
ALTER TABLE `leave_applications`
  ADD CONSTRAINT `leave_applications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `leave_applications_ibfk_2` FOREIGN KEY (`leave_type_id`) REFERENCES `leave_types` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `leave_applications_ibfk_3` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `leave_balances`
--
ALTER TABLE `leave_balances`
  ADD CONSTRAINT `leave_balances_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `leave_balances_ibfk_2` FOREIGN KEY (`leave_type_id`) REFERENCES `leave_types` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `positions`
--
ALTER TABLE `positions`
  ADD CONSTRAINT `positions_ibfk_1` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_users_department` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_users_department_id` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

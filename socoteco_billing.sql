-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 13, 2025 at 07:26 AM
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
-- Database: `socoteco_billing`
--

-- --------------------------------------------------------

--
-- Table structure for table `audit_trail`
--

CREATE TABLE `audit_trail` (
  `audit_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `action` varchar(100) NOT NULL,
  `table_name` varchar(50) DEFAULT NULL,
  `record_id` int(11) DEFAULT NULL,
  `old_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`old_values`)),
  `new_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`new_values`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `audit_trail`
--

INSERT INTO `audit_trail` (`audit_id`, `user_id`, `action`, `table_name`, `record_id`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, 4, 'User login', 'users', 4, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-09-01 08:20:18'),
(2, 4, 'User logout', 'users', 4, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-09-01 08:32:31'),
(3, 4, 'User login', 'users', 4, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-09-01 08:32:37'),
(4, 4, 'User logout', 'users', 4, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-09-01 08:33:29'),
(5, 4, 'User login', 'users', 4, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-09-01 08:35:45'),
(6, 4, 'Customer created', 'customers', 0, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-09-01 08:40:01'),
(7, 4, 'Meter reading created', 'meter_readings', 0, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-09-01 08:41:04'),
(8, 4, 'Bill generated', 'bills', 0, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-09-01 08:41:32'),
(9, 4, 'User login', 'users', 4, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-15 17:41:23'),
(10, 4, 'Priority system settings updated', 'system_settings', NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-15 17:56:08'),
(11, 4, 'User login', 'users', 4, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-15 19:50:53'),
(12, 4, 'User created', 'users', 0, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-16 13:48:11'),
(13, 4, 'User created', 'users', 0, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-16 13:48:45'),
(14, 4, 'User updated', 'users', 5, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-16 13:48:54'),
(15, 4, 'User updated', 'users', 5, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-16 13:48:59'),
(16, 4, 'User logout', 'users', 4, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-16 13:49:36'),
(17, 5, 'User login', 'users', 5, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-16 13:49:42'),
(18, 5, 'User logout', 'users', 5, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-16 13:50:08'),
(19, 6, 'User login', 'users', 6, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-16 13:50:13'),
(20, 6, 'User logout', 'users', 6, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-16 13:50:29'),
(21, 4, 'User login', 'users', 4, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-16 13:53:01'),
(22, 4, 'User logout', 'users', 4, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-16 14:47:03'),
(23, 6, 'User login', 'users', 6, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-16 14:47:09'),
(24, 6, 'Meter reading created', 'meter_readings', 0, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-16 14:47:58'),
(25, 6, 'User logout', 'users', 6, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-16 14:48:19'),
(26, 5, 'User login', 'users', 5, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-16 14:48:31'),
(27, 5, 'Bill generated', 'bills', 0, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-16 14:50:18'),
(28, 5, 'User logout', 'users', 5, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-16 16:46:48'),
(29, 4, 'User login', 'users', 4, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-16 16:46:52'),
(30, 4, 'User login', 'users', 4, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-18 00:38:02'),
(31, 4, 'Customer created', 'customers', 0, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-08 11:06:47'),
(32, 4, 'User created', 'users', 0, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-08 11:11:37'),
(33, 7, 'User login', 'users', 7, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-08 11:11:53'),
(34, 7, 'Meter reading created', 'meter_readings', 0, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-08 11:13:30'),
(35, 7, 'Meter reading created', 'meter_readings', 0, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-08 11:14:14'),
(36, 7, 'User logout', 'users', 7, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-08 11:14:58'),
(37, 5, 'User login', 'users', 5, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-08 11:15:11'),
(38, 5, 'Bill generated', 'bills', 0, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-08 11:15:38'),
(39, 5, 'Bill generated', 'bills', 0, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-08 11:16:03'),
(40, 5, 'Payment recorded', 'payments', 3, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-08 11:18:18'),
(41, 7, 'User login', 'users', 7, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-08 11:33:06'),
(42, 4, 'User login', 'users', 4, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 OPR/122.0.0.0', '2025-10-19 10:59:19'),
(43, 4, 'Customer created', 'customers', 0, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 OPR/122.0.0.0', '2025-10-19 13:32:05'),
(44, 4, 'Meter reading created', 'meter_readings', 0, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 OPR/122.0.0.0', '2025-10-19 13:32:45'),
(45, 4, 'Bill generated', 'bills', 0, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 OPR/122.0.0.0', '2025-10-19 13:34:00'),
(46, 4, 'Customer updated', 'customers', 3, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 OPR/122.0.0.0', '2025-10-19 13:38:46'),
(47, 4, 'Customer updated', 'customers', 3, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 OPR/122.0.0.0', '2025-10-19 13:41:21'),
(51, 4, 'User login', 'users', 4, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-05 11:31:13'),
(52, 4, 'User login', 'users', 4, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-05 11:31:35'),
(53, 4, 'User login', 'users', 4, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-05 11:34:45'),
(54, 4, 'User login', 'users', 4, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-05 11:36:05'),
(55, 4, 'User login', 'users', 4, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-05 11:37:19'),
(56, 4, 'User login', 'users', 4, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-11 17:10:49'),
(57, 4, 'User login', 'users', 4, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-11 17:11:35');

-- --------------------------------------------------------

--
-- Table structure for table `bills`
--

CREATE TABLE `bills` (
  `bill_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `reading_id` int(11) NOT NULL,
  `bill_number` varchar(20) NOT NULL,
  `billing_period_start` date NOT NULL,
  `billing_period_end` date NOT NULL,
  `consumption` decimal(10,2) NOT NULL,
  `generation_charge` decimal(10,2) NOT NULL,
  `distribution_charge` decimal(10,2) NOT NULL,
  `transmission_charge` decimal(10,2) DEFAULT 0.00,
  `system_loss_charge` decimal(10,2) DEFAULT 0.00,
  `vat` decimal(10,2) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `due_date` date NOT NULL,
  `status` enum('pending','paid','overdue','cancelled') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bills`
--

INSERT INTO `bills` (`bill_id`, `customer_id`, `reading_id`, `bill_number`, `billing_period_start`, `billing_period_end`, `consumption`, `generation_charge`, `distribution_charge`, `transmission_charge`, `system_loss_charge`, `vat`, `total_amount`, `due_date`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'SOC2025090001', '2025-09-01', '2025-09-30', 123.00, 553.50, 147.60, 98.40, 61.50, 103.32, 964.32, '2025-09-16', 'pending', '2025-09-01 08:41:32', '2025-09-01 08:41:32'),
(2, 1, 2, 'SOC2025090002', '2025-09-01', '2025-09-30', 377.00, 1696.50, 452.40, 301.60, 188.50, 316.68, 2955.68, '2025-10-01', 'pending', '2025-09-16 14:50:18', '2025-09-16 14:50:18'),
(3, 1, 3, 'TEST2025070001', '2025-07-01', '2025-07-31', 185.50, 834.75, 222.60, 148.40, 92.75, 155.82, 1454.32, '2025-08-15', 'pending', '2025-09-16 15:15:38', '2025-09-16 15:15:38'),
(4, 1, 4, 'TEST2025080001', '2025-08-01', '2025-08-31', 212.30, 955.35, 254.76, 169.84, 106.15, 178.33, 1664.43, '2025-09-15', 'pending', '2025-09-16 15:15:38', '2025-09-16 15:15:38'),
(5, 2, 6, 'SOC2025100001', '2025-10-01', '2025-10-31', 197.00, 886.50, 236.40, 157.60, 98.50, 165.48, 1544.48, '2025-10-23', 'pending', '2025-10-08 11:15:38', '2025-10-08 11:15:38'),
(6, 2, 5, 'SOC2025100002', '2025-10-01', '2025-10-31', 123.00, 553.50, 147.60, 98.40, 61.50, 103.32, 964.32, '2025-10-23', 'paid', '2025-10-08 11:16:03', '2025-10-08 11:18:18'),
(7, 3, 7, 'SOC2025100003', '2025-10-01', '2025-10-31', 100.00, 450.00, 120.00, 80.00, 50.00, 84.00, 784.00, '2025-11-03', 'pending', '2025-10-19 13:34:00', '2025-10-19 13:34:00'),
(8, 1, 10, 'SOC2025100004', '2025-10-01', '2025-10-31', 730.00, 3285.00, 876.00, 584.00, 365.00, 613.20, 5723.20, '2025-11-08', 'pending', '2025-10-24 04:52:55', '2025-10-24 04:52:55'),
(9, 2, 13, 'SOC2025100005', '2025-10-01', '2025-10-31', 280.00, 1260.00, 336.00, 224.00, 140.00, 235.20, 2195.20, '2025-11-08', 'pending', '2025-10-24 05:19:50', '2025-10-24 05:19:50'),
(10, 1, 14, 'SOC2025100006', '2025-11-01', '2025-11-30', 2.20, 9.90, 2.64, 1.76, 1.10, 1.85, 17.25, '2025-11-08', 'pending', '2025-10-24 06:13:01', '2025-10-24 06:13:01');

-- --------------------------------------------------------

--
-- Table structure for table `chat_messages`
--

CREATE TABLE `chat_messages` (
  `message_id` int(11) NOT NULL,
  `session_id` int(11) NOT NULL,
  `sender_type` enum('customer','admin') NOT NULL,
  `sender_customer_id` int(11) DEFAULT NULL,
  `sender_user_id` int(11) DEFAULT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `is_read_by_admin` tinyint(1) DEFAULT 0,
  `read_by_admin_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `chat_messages`
--

INSERT INTO `chat_messages` (`message_id`, `session_id`, `sender_type`, `sender_customer_id`, `sender_user_id`, `message`, `is_read`, `is_read_by_admin`, `read_by_admin_at`, `created_at`) VALUES
(1, 1, 'customer', 1, NULL, 'hello nigga', 1, 1, '2025-09-10 19:16:17', '2025-09-10 19:15:17'),
(2, 1, 'admin', NULL, 4, 'sup a hole', 1, 1, '2025-09-10 19:17:03', '2025-09-10 19:16:03'),
(3, 1, 'customer', 1, NULL, 'hello bro', 1, 1, '2025-09-15 19:51:23', '2025-09-15 19:50:23'),
(4, 2, 'customer', 1, NULL, 'hello', 1, 1, '2025-09-15 19:52:22', '2025-09-15 19:51:22'),
(5, 2, 'admin', NULL, 4, 'sup, are you okay', 1, 1, '2025-09-15 20:03:41', '2025-09-15 20:02:41'),
(6, 2, 'admin', NULL, 4, 'how may i help you sir', 1, 1, '2025-09-15 20:03:50', '2025-09-15 20:02:50'),
(7, 2, 'customer', 1, NULL, 'What is my current bill amount?', 1, 1, '2025-09-16 16:44:40', '2025-09-16 16:43:40'),
(8, 2, 'customer', 1, NULL, 'When is my bill due date?', 1, 1, '2025-09-16 16:44:44', '2025-09-16 16:43:44'),
(9, 2, 'customer', 1, NULL, 'How can I make a payment?', 1, 1, '2025-09-16 16:44:45', '2025-09-16 16:43:45'),
(10, 4, 'admin', NULL, NULL, 'Hi! How can we help you today?', 1, 1, '2025-09-16 16:50:37', '2025-09-16 16:49:37'),
(11, 4, 'customer', 1, NULL, 'What is my current bill amount?', 1, 1, '2025-09-16 16:50:45', '2025-09-16 16:49:45'),
(12, 5, 'admin', NULL, NULL, 'Hi asdg sdgsdg! ???? Welcome to SOCOTECO customer support. We\'re here to help you with your electricity billing needs. Our support hours are Monday-Friday 8:00 AM - 5:00 PM. How can we assist you today?', 1, 1, '2025-09-17 20:34:49', '2025-09-17 20:33:49'),
(13, 5, 'customer', 1, NULL, 'hello', 1, 1, '2025-09-17 21:13:44', '2025-09-17 21:12:44'),
(14, 5, 'customer', 1, NULL, 'nigga', 1, 1, '2025-09-17 21:13:59', '2025-09-17 21:12:59'),
(15, 5, 'customer', 1, NULL, 'stupid', 1, 1, '2025-09-17 21:14:20', '2025-09-17 21:13:20'),
(16, 6, 'admin', NULL, NULL, 'Hi asdg sdgsdg! ???? Welcome to SOCOTECO customer support. We\'re here to help you with your electricity billing needs. Our support hours are Monday-Friday 8:00 AM - 5:00 PM. How can we assist you today?', 1, 1, '2025-09-17 21:24:27', '2025-09-17 21:23:27'),
(17, 6, 'customer', 1, NULL, 'nigga', 1, 1, '2025-09-17 21:27:44', '2025-09-17 21:26:44'),
(18, 6, 'customer', 1, NULL, 'nigga', 1, 1, '2025-09-17 21:30:17', '2025-09-17 21:29:17'),
(19, 6, 'customer', 1, NULL, 'nigga', 1, 1, '2025-09-17 21:30:53', '2025-09-17 21:29:53'),
(20, 7, 'admin', NULL, NULL, 'Hi asdg sdgsdg! ???? Welcome to SOCOTECO customer support. We\'re here to help you with your electricity billing needs. Our support hours are Monday-Friday 8:00 AM - 5:00 PM. How can we assist you today?', 1, 1, '2025-09-17 21:31:16', '2025-09-17 21:30:16'),
(21, 7, 'customer', 1, NULL, 'nigga', 1, 1, '2025-09-17 21:31:21', '2025-09-17 21:30:21'),
(22, 7, 'customer', 1, NULL, 'commit suicide', 1, 1, '2025-09-17 21:31:47', '2025-09-17 21:30:47'),
(23, 8, 'admin', NULL, NULL, 'Hi asdg sdgsdg! ???? Welcome to SOCOTECO customer support. We\'re here to help you with your electricity billing needs. Our support hours are Monday-Friday 8:00 AM - 5:00 PM. How can we assist you today?', 1, 1, '2025-09-17 21:35:20', '2025-09-17 21:34:20'),
(24, 8, 'customer', 1, NULL, 'n1gga', 1, 1, '2025-09-17 21:35:44', '2025-09-17 21:34:44'),
(25, 9, 'admin', NULL, NULL, 'Hi asdg sdgsdg! ???? Welcome to SOCOTECO customer support. We\'re here to help you with your electricity billing needs. Our support hours are Monday-Friday 8:00 AM - 5:00 PM. How can we assist you today?', 1, 1, '2025-09-17 21:36:51', '2025-09-17 21:35:51'),
(26, 9, 'customer', 1, NULL, 'safhasd', 1, 1, '2025-09-18 07:41:24', '2025-09-18 07:40:24'),
(27, 10, 'admin', NULL, NULL, 'Hi Noli Fin! ???? Welcome to SOCOTECO customer support. We\'re here to help you with your electricity billing needs. Our support hours are Monday-Friday 8:00 AM - 5:00 PM. How can we assist you today?', 1, 1, '2025-10-08 11:09:06', '2025-10-08 11:08:06'),
(28, 10, 'customer', 2, NULL, 'When is my bill due date?', 1, 1, '2025-10-08 11:10:05', '2025-10-08 11:09:05'),
(29, 10, 'admin', NULL, 4, 'next mknth', 1, 1, '2025-10-08 11:38:00', '2025-10-08 11:37:00'),
(30, 11, 'admin', NULL, NULL, 'Hi Noli Fin! ???? Welcome to SOCOTECO customer support. We\'re here to help you with your electricity billing needs. Our support hours are Monday-Friday 8:00 AM - 5:00 PM. How can we assist you today?', 1, 1, '2025-10-08 11:38:51', '2025-10-08 11:37:51'),
(31, 11, 'customer', 2, NULL, 'ulol', 1, 1, '2025-10-08 11:41:05', '2025-10-08 11:40:05'),
(32, 12, 'admin', NULL, NULL, 'Hi asdg sdgsdg! ???? Welcome to SOCOTECO customer support. We\'re here to help you with your electricity billing needs. Our support hours are Monday-Friday 8:00 AM - 5:00 PM. How can we assist you today?', 1, 1, '2025-10-22 09:44:53', '2025-10-22 09:43:53'),
(33, 12, 'customer', 1, NULL, 'What is my current bill amount?', 1, 1, '2025-10-22 09:44:57', '2025-10-22 09:43:57'),
(34, 12, 'customer', 1, NULL, 'When is my bill due date?', 1, 1, '2025-10-23 19:21:59', '2025-10-23 19:21:37'),
(35, 12, 'customer', 1, NULL, 'What is my current bill amount?', 1, 1, '2025-10-23 19:36:11', '2025-10-23 19:24:35'),
(36, 12, 'admin', NULL, 4, 'es', 1, 0, NULL, '2025-10-23 19:25:28'),
(37, 12, 'customer', 1, NULL, 'What is my current bill amount?', 1, 1, '2025-10-23 19:36:11', '2025-10-23 19:34:48'),
(38, 12, 'customer', 1, NULL, 'How can I make a payment?', 1, 1, '2025-10-23 19:43:03', '2025-10-23 19:39:00'),
(39, 12, 'admin', NULL, 1, 'asfas', 1, 0, NULL, '2025-10-23 19:39:46'),
(40, 12, 'customer', 1, NULL, 'What is my current bill amount?', 1, 1, '2025-10-23 19:43:13', '2025-10-23 19:43:09'),
(41, 12, 'customer', 1, NULL, 'What is my current bill amount?', 1, 1, '2025-10-23 19:47:33', '2025-10-23 19:47:25'),
(42, 12, 'customer', 1, NULL, 'What is my current bill amount?', 1, 1, '2025-10-23 19:47:58', '2025-10-23 19:47:41'),
(43, 13, 'admin', NULL, NULL, 'Hi Mike Acenas! ???? Welcome to SOCOTECO customer support. We\'re here to help you with your electricity billing needs. Our support hours are Monday-Friday 8:00 AM - 5:00 PM. How can we assist you today?', 1, 1, NULL, '2025-11-05 01:40:04'),
(44, 12, 'customer', 1, NULL, 'What is my current bill amount?', 1, 1, '2025-11-11 17:10:56', '2025-11-11 17:10:16'),
(45, 14, 'admin', NULL, NULL, 'Hi asdg sdgsdg! ???? Welcome to SOCOTECO customer support. We\'re here to help you with your electricity billing needs. Our support hours are Monday-Friday 8:00 AM - 5:00 PM. How can we assist you today?', 1, 1, NULL, '2025-11-11 17:11:53'),
(46, 14, 'customer', 1, NULL, 'What is my current bill amount?', 1, 1, '2025-11-11 17:12:44', '2025-11-11 17:12:08'),
(47, 14, 'customer', 1, NULL, 'When is my bill due date?', 1, 1, '2025-11-11 17:12:44', '2025-11-11 17:12:11'),
(48, 14, 'customer', 1, NULL, 'How can I make a payment?', 1, 1, '2025-11-11 17:12:44', '2025-11-11 17:12:12'),
(49, 14, 'admin', NULL, 4, 'hello sir!', 1, 0, NULL, '2025-11-11 17:12:57'),
(50, 15, 'admin', NULL, NULL, 'Hi asdg sdgsdg! ???? Welcome to SOCOTECO customer support. We\'re here to help you with your electricity billing needs. Our support hours are Monday-Friday 8:00 AM - 5:00 PM. How can we assist you today?', 1, 1, NULL, '2025-11-11 17:13:14');

-- --------------------------------------------------------

--
-- Table structure for table `chat_moderation_logs`
--

CREATE TABLE `chat_moderation_logs` (
  `log_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `session_id` int(11) DEFAULT NULL,
  `message` text NOT NULL,
  `severity` enum('low','medium','high') NOT NULL,
  `flagged_words` text DEFAULT NULL,
  `severity_score` int(11) DEFAULT 0,
  `action_taken` enum('monitor','warn','block') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `chat_moderation_logs`
--

INSERT INTO `chat_moderation_logs` (`log_id`, `customer_id`, `session_id`, `message`, `severity`, `flagged_words`, `severity_score`, `action_taken`, `created_at`) VALUES
(1, 1, 6, 'fuck', 'medium', 'fuck', 3, 'warn', '2025-09-17 21:26:24'),
(2, 1, 7, 'fuck', 'medium', 'fuck', 3, 'warn', '2025-09-17 21:30:24'),
(3, 1, 7, 'bitch', 'medium', 'bitch', 3, 'warn', '2025-09-17 21:30:28'),
(4, 1, 7, 'kill yourself', 'medium', 'kill', 3, 'warn', '2025-09-17 21:30:38'),
(5, 1, 8, 'nigger', 'high', 'nigger', 4, 'block', '2025-09-17 21:34:27'),
(6, 1, 8, 'nigga', 'high', 'nigga', 4, 'block', '2025-09-17 21:34:34'),
(7, 1, 8, 'suicide', 'high', 'suicide', 4, 'block', '2025-09-17 21:34:41'),
(8, 1, 9, 'fuck', 'high', 'fuck', 4, 'block', '2025-09-18 07:40:34'),
(9, 2, 11, 'fuck', 'high', 'fuck', 4, 'block', '2025-10-08 11:39:49'),
(10, 2, 11, 'bitch', 'high', 'bitch', 4, 'block', '2025-10-08 11:39:56'),
(11, 1, 12, 'bitch', 'high', 'bitch', 4, 'block', '2025-10-22 09:44:23');

-- --------------------------------------------------------

--
-- Table structure for table `chat_sessions`
--

CREATE TABLE `chat_sessions` (
  `session_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `status` enum('open','closed') DEFAULT 'open',
  `last_activity` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `chat_sessions`
--

INSERT INTO `chat_sessions` (`session_id`, `customer_id`, `status`, `last_activity`, `created_at`) VALUES
(1, 1, 'closed', '2025-09-15 19:51:09', '2025-09-15 18:58:05'),
(2, 1, 'closed', '2025-09-16 16:46:59', '2025-09-15 19:51:15'),
(3, 1, 'closed', '2025-09-16 16:49:34', '2025-09-16 16:47:08'),
(4, 1, 'closed', '2025-09-17 20:11:06', '2025-09-16 16:49:37'),
(5, 1, 'closed', '2025-09-17 21:13:29', '2025-09-17 20:33:49'),
(6, 1, 'closed', '2025-09-17 21:30:09', '2025-09-17 21:23:27'),
(7, 1, 'closed', '2025-09-17 21:31:06', '2025-09-17 21:30:16'),
(8, 1, 'closed', '2025-09-17 21:35:23', '2025-09-17 21:34:20'),
(9, 1, 'closed', '2025-10-22 09:43:52', '2025-09-17 21:35:51'),
(10, 2, 'closed', '2025-10-08 11:37:47', '2025-10-08 11:08:06'),
(11, 2, 'open', '2025-10-08 11:40:05', '2025-10-08 11:37:51'),
(12, 1, 'closed', '2025-11-11 17:10:59', '2025-10-22 09:43:53'),
(13, 3, 'open', '2025-11-05 01:40:04', '2025-11-05 01:40:04'),
(14, 1, 'closed', '2025-11-11 17:13:04', '2025-11-11 17:11:53'),
(15, 1, 'open', '2025-11-11 17:13:14', '2025-11-11 17:13:14');

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `customer_id` int(11) NOT NULL,
  `account_number` varchar(20) NOT NULL,
  `meter_number` varchar(20) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `middle_name` varchar(50) DEFAULT NULL,
  `address` text NOT NULL,
  `barangay` varchar(100) NOT NULL,
  `municipality` varchar(100) NOT NULL,
  `province` varchar(100) DEFAULT 'South Cotabato',
  `contact_number` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `category_id` int(11) NOT NULL,
  `connection_date` date NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`customer_id`, `account_number`, `meter_number`, `first_name`, `last_name`, `middle_name`, `address`, `barangay`, `municipality`, `province`, `contact_number`, `email`, `category_id`, `connection_date`, `is_active`, `created_at`, `updated_at`) VALUES
(1, '124124', '12312', 'asdg', 'sdgsdg', 'hdsf', 'ZONE 1 PUROK VICENTE FIN', 'asd', 'aasga', 'South Cotabato', '12351', 'wmoon146@gmail.com', 1, '2025-09-01', 1, '2025-09-01 08:40:01', '2025-09-01 08:40:01'),
(2, '121212', '323232', 'Noli', 'Fin', 'Mampao', 'sa balay', 'magsaysay', 'Polomolok', 'South Cotabato', '963 101 4585', 'wmoon146@gmail.com', 1, '2025-10-08', 1, '2025-10-08 11:06:47', '2025-10-08 11:06:47'),
(3, '1', '1', 'Mike', 'Acenas', '', 'Silway 8 Pol. Sot. Cot.', 'Silway 8', 'Polomolok', 'South Cotabato', '9629737496', 'mikeacenas2715@gmail.com', 1, '2025-10-19', 1, '2025-10-19 13:32:05', '2025-10-19 13:41:21');

-- --------------------------------------------------------

--
-- Table structure for table `customer_categories`
--

CREATE TABLE `customer_categories` (
  `category_id` int(11) NOT NULL,
  `category_name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `base_rate` decimal(10,4) DEFAULT 0.0000,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customer_categories`
--

INSERT INTO `customer_categories` (`category_id`, `category_name`, `description`, `base_rate`, `created_at`) VALUES
(1, 'Residential', 'Residential customers', 8.5000, '2025-09-01 08:06:40'),
(2, 'Commercial', 'Commercial establishments', 9.2000, '2025-09-01 08:06:40'),
(3, 'Industrial', 'Industrial customers', 8.8000, '2025-09-01 08:06:40'),
(4, 'Government', 'Government offices and facilities', 8.0000, '2025-09-01 08:06:40'),
(5, 'Residential', 'Residential customers', 8.5000, '2025-09-01 08:17:10'),
(6, 'Commercial', 'Commercial establishments', 9.2000, '2025-09-01 08:17:10'),
(7, 'Industrial', 'Industrial customers', 8.8000, '2025-09-01 08:17:10'),
(8, 'Government', 'Government offices and facilities', 8.0000, '2025-09-01 08:17:10');

-- --------------------------------------------------------

--
-- Table structure for table `customer_moderation_status`
--

CREATE TABLE `customer_moderation_status` (
  `customer_id` int(11) NOT NULL,
  `warning_count` int(11) DEFAULT 0,
  `block_count` int(11) DEFAULT 0,
  `is_temporarily_blocked` tinyint(1) DEFAULT 0,
  `blocked_until` timestamp NULL DEFAULT NULL,
  `last_warning_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

CREATE TABLE `feedback` (
  `feedback_id` int(10) UNSIGNED NOT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `customer_name` varchar(120) DEFAULT NULL,
  `message` text NOT NULL,
  `category` enum('customer_support','service','website') DEFAULT 'customer_support',
  `status` enum('pending','reviewed') DEFAULT 'pending',
  `is_read_by_admin` tinyint(1) DEFAULT 0,
  `read_by_admin_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `feedback`
--

INSERT INTO `feedback` (`feedback_id`, `customer_id`, `customer_name`, `message`, `category`, `status`, `is_read_by_admin`, `read_by_admin_at`, `created_at`) VALUES
(1, 1, NULL, 'hi sir good sir', 'customer_support', 'pending', 1, '2025-10-23 19:16:03', '2025-09-29 20:52:14'),
(5, 1, NULL, 'hello, your system is so shi, stupid ahh dumb ahh', 'customer_support', 'pending', 1, '2025-10-23 19:16:03', '2025-09-09 15:40:48'),
(6, 1, NULL, 'errr hello', 'customer_support', 'pending', 1, '2025-10-23 19:16:03', '2025-09-29 21:02:00'),
(8, 1, NULL, 'HELLO', 'customer_support', 'pending', 1, '2025-10-23 19:16:03', '2025-10-08 05:24:45'),
(9, 2, NULL, 'Whats up', 'customer_support', 'pending', 1, '2025-10-23 19:16:03', '2025-10-08 11:08:43'),
(10, 2, NULL, 'good', 'customer_support', 'pending', 1, '2025-10-23 19:16:03', '2025-10-08 11:38:14'),
(11, 1, NULL, 'good', 'customer_support', 'reviewed', 1, '2025-10-23 19:16:03', '2025-10-22 09:45:00'),
(12, 1, NULL, 'good service', 'service', 'pending', 1, '2025-10-23 19:16:03', '2025-10-23 19:01:24'),
(13, 1, NULL, 'clean ahh website', 'website', 'pending', 1, '2025-10-23 19:16:03', '2025-10-23 19:01:38'),
(14, 1, NULL, 'hi', 'customer_support', 'pending', 1, '2025-10-23 19:16:03', '2025-10-23 19:15:47'),
(15, 1, NULL, 'maayo errr', 'website', 'pending', 1, '2025-10-23 19:16:03', '2025-10-23 19:15:54'),
(17, 1, NULL, 'yeye', 'website', 'pending', 1, '2025-10-23 19:47:58', '2025-10-23 19:47:49'),
(18, 1, NULL, 'very good service, love it', 'service', 'reviewed', 1, '2025-11-11 17:24:10', '2025-11-11 17:23:40');

-- --------------------------------------------------------

--
-- Table structure for table `feedback_flags`
--

CREATE TABLE `feedback_flags` (
  `feedback_id` int(10) UNSIGNED NOT NULL,
  `customer_id` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `feedback_likes`
--

CREATE TABLE `feedback_likes` (
  `feedback_id` int(10) UNSIGNED NOT NULL,
  `customer_id` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `feedback_likes`
--

INSERT INTO `feedback_likes` (`feedback_id`, `customer_id`, `created_at`) VALUES
(8, 1, '2025-10-08 05:24:50'),
(8, 2, '2025-10-08 11:08:37');

-- --------------------------------------------------------

--
-- Table structure for table `feedback_replies`
--

CREATE TABLE `feedback_replies` (
  `reply_id` int(10) UNSIGNED NOT NULL,
  `feedback_id` int(11) NOT NULL,
  `admin_user_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `feedback_replies`
--

INSERT INTO `feedback_replies` (`reply_id`, `feedback_id`, `admin_user_id`, `message`, `created_at`) VALUES
(1, 5, 4, 'good', '2025-09-29 20:47:32'),
(13, 5, 4, 'no, its great nigga', '2025-09-09 16:05:11'),
(14, 5, 4, 'asgasd', '2025-09-09 16:05:22'),
(16, 18, 4, 'thank you, glad we could be helpful to you!', '2025-11-11 17:24:33');

-- --------------------------------------------------------

--
-- Table structure for table `meter_readings`
--

CREATE TABLE `meter_readings` (
  `reading_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `reading_date` date NOT NULL,
  `previous_reading` decimal(10,2) NOT NULL,
  `current_reading` decimal(10,2) NOT NULL,
  `consumption` decimal(10,2) NOT NULL,
  `reading_type` enum('actual','estimated','adjusted') DEFAULT 'actual',
  `meter_reader_id` int(11) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `meter_readings`
--

INSERT INTO `meter_readings` (`reading_id`, `customer_id`, `reading_date`, `previous_reading`, `current_reading`, `consumption`, `reading_type`, `meter_reader_id`, `notes`, `created_at`) VALUES
(1, 1, '2025-09-01', 0.00, 123.00, 123.00, 'actual', 4, 'kalas kuryente', '2025-09-01 08:41:04'),
(2, 1, '2025-09-16', 123.00, 500.00, 377.00, 'actual', 6, 'horrishiii kalasag kuryente oip dato man', '2025-09-16 14:47:58'),
(3, 1, '2025-07-31', 1000.00, 1185.50, 185.50, 'actual', NULL, 'Test data (month -2)', '2025-09-16 15:15:38'),
(4, 1, '2025-08-31', 1185.50, 1397.80, 212.30, 'actual', NULL, 'Test data (last month)', '2025-09-16 15:15:38'),
(5, 2, '2025-10-08', 0.00, 123.00, 123.00, 'actual', 7, 'oks tama', '2025-10-08 11:13:30'),
(6, 2, '2025-10-08', 123.00, 320.00, 197.00, 'actual', 7, 'asbsd', '2025-10-08 11:14:14'),
(7, 3, '2025-10-19', 0.00, 100.00, 100.00, 'actual', 4, '', '2025-10-19 13:32:45'),
(10, 1, '2025-10-24', 500.00, 1230.00, 730.00, 'actual', NULL, '', '2025-10-24 04:45:54'),
(11, 1, '2025-10-24', 1230.00, 1230.00, 0.00, 'actual', NULL, '', '2025-10-24 04:46:06'),
(12, 1, '2025-10-24', 1230.00, 123.00, 0.00, 'actual', NULL, '', '2025-10-24 04:52:32'),
(13, 2, '2025-10-24', 320.00, 600.00, 280.00, 'actual', NULL, '', '2025-10-24 05:19:37'),
(14, 1, '2025-11-27', 1397.80, 1400.00, 2.20, 'actual', NULL, '', '2025-10-24 06:12:19');

-- --------------------------------------------------------

--
-- Table structure for table `moderation_settings`
--

CREATE TABLE `moderation_settings` (
  `setting_id` int(11) NOT NULL,
  `setting_name` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `moderation_settings`
--

INSERT INTO `moderation_settings` (`setting_id`, `setting_name`, `setting_value`, `description`, `created_at`, `updated_at`) VALUES
(1, 'warning_threshold', '3', 'Number of warnings before temporary block', '2025-09-17 21:10:28', '2025-09-17 21:10:28'),
(2, 'block_threshold', '5', 'Severity score threshold for blocking messages', '2025-09-17 21:10:28', '2025-09-17 21:10:28'),
(3, 'temporary_block_duration', '3600', 'Temporary block duration in seconds (1 hour)', '2025-09-17 21:10:28', '2025-09-17 21:10:28'),
(4, 'ai_moderation_enabled', 'false', 'Enable AI-powered content moderation', '2025-09-17 21:10:28', '2025-09-17 21:10:28'),
(5, 'profanity_filter_enabled', 'true', 'Enable profanity word filtering', '2025-09-17 21:10:28', '2025-09-17 21:10:28'),
(6, 'caps_filter_enabled', 'true', 'Enable excessive caps detection', '2025-09-17 21:10:28', '2025-09-17 21:10:28'),
(7, 'spam_filter_enabled', 'true', 'Enable spam pattern detection', '2025-09-17 21:10:28', '2025-09-17 21:10:28');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `payment_id` int(11) NOT NULL,
  `bill_id` int(11) NOT NULL,
  `payment_date` date NOT NULL,
  `amount_paid` decimal(10,2) NOT NULL,
  `payment_method` enum('cash','check','bank_transfer','online') DEFAULT 'cash',
  `or_number` varchar(20) NOT NULL,
  `cashier_id` int(11) NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`payment_id`, `bill_id`, `payment_date`, `amount_paid`, `payment_method`, `or_number`, `cashier_id`, `notes`, `created_at`) VALUES
(1, 1, '2025-09-01', 964.32, 'cash', 'OR2025000001', 4, 'oksoks', '2025-09-01 08:42:34'),
(2, 2, '2025-09-18', 2955.68, 'cash', 'OR2025000002', 4, 'asfa', '2025-09-18 00:47:29'),
(3, 6, '2025-10-08', 964.32, 'cash', 'OR2025000003', 5, '', '2025-10-08 11:18:18');

-- --------------------------------------------------------

--
-- Table structure for table `payment_history`
--

CREATE TABLE `payment_history` (
  `history_id` int(11) NOT NULL,
  `bill_id` int(11) NOT NULL,
  `payment_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payment_history`
--

INSERT INTO `payment_history` (`history_id`, `bill_id`, `payment_id`, `amount`, `payment_date`, `created_at`) VALUES
(2, 6, 3, 964.32, '2025-10-08', '2025-10-08 11:18:18');

-- --------------------------------------------------------

--
-- Table structure for table `priority_numbers`
--

CREATE TABLE `priority_numbers` (
  `priority_id` int(11) NOT NULL,
  `priority_number` varchar(10) NOT NULL,
  `category` enum('payment','claims','registration') NOT NULL DEFAULT 'payment',
  `customer_id` int(11) NOT NULL,
  `service_date` date NOT NULL,
  `generated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('pending','served','expired','cancelled','skipped') DEFAULT 'pending',
  `served_at` timestamp NULL DEFAULT NULL,
  `served_by` int(11) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `priority_numbers`
--

INSERT INTO `priority_numbers` (`priority_id`, `priority_number`, `category`, `customer_id`, `service_date`, `generated_at`, `status`, `served_at`, `served_by`, `notes`, `created_at`, `updated_at`) VALUES
(4, 'P001', 'payment', 2, '2025-11-05', '2025-11-05 01:43:28', 'served', '2025-11-05 01:44:03', 1, NULL, '2025-11-05 01:43:28', '2025-11-05 01:44:03'),
(5, 'P002', 'payment', 1, '2025-11-05', '2025-11-05 01:43:35', 'served', '2025-11-05 01:44:16', 1, NULL, '2025-11-05 01:43:35', '2025-11-05 01:44:16'),
(6, 'P003', 'payment', 3, '2025-11-05', '2025-11-05 01:43:52', 'served', '2025-11-05 01:44:24', 1, NULL, '2025-11-05 01:43:52', '2025-11-05 01:44:24'),
(7, 'P004', 'payment', 2, '2025-11-05', '2025-11-05 02:20:00', 'served', '2025-11-05 02:20:34', 1, NULL, '2025-11-05 02:20:00', '2025-11-05 02:20:34'),
(8, 'P005', 'payment', 1, '2025-11-05', '2025-11-05 02:20:21', 'skipped', '2025-11-05 02:33:37', 1, NULL, '2025-11-05 02:20:21', '2025-11-05 02:33:37'),
(9, 'P006', 'payment', 3, '2025-11-05', '2025-11-05 02:20:56', 'skipped', '2025-11-05 02:21:03', 1, NULL, '2025-11-05 02:20:56', '2025-11-05 02:21:03'),
(10, 'P007', 'payment', 3, '2025-11-05', '2025-11-05 02:24:26', 'skipped', '2025-11-05 02:24:36', 1, NULL, '2025-11-05 02:24:26', '2025-11-05 02:24:36'),
(11, 'P008', 'payment', 3, '2025-11-05', '2025-11-05 02:33:30', 'served', '2025-11-05 02:33:37', 1, NULL, '2025-11-05 02:33:30', '2025-11-05 02:33:37'),
(12, 'C001', 'claims', 3, '2025-11-05', '2025-11-05 02:33:49', 'served', '2025-11-05 02:33:54', 1, NULL, '2025-11-05 02:33:49', '2025-11-05 02:33:54'),
(13, 'R001', 'registration', 2, '2025-11-05', '2025-11-05 02:34:03', 'served', '2025-11-05 02:34:07', 1, NULL, '2025-11-05 02:34:03', '2025-11-05 02:34:07'),
(14, '14', 'payment', 1, '2025-11-05', '2025-11-05 11:20:12', 'served', '2025-11-05 11:21:44', 1, NULL, '2025-11-05 11:20:12', '2025-11-05 11:21:44'),
(15, '13', 'claims', 1, '2025-11-05', '2025-11-05 11:20:59', 'served', '2025-11-05 11:22:06', 1, NULL, '2025-11-05 11:20:59', '2025-11-05 11:22:06'),
(16, 'P001', 'payment', 1, '2025-11-12', '2025-11-11 17:20:34', 'served', '2025-11-11 17:21:24', 4, NULL, '2025-11-11 17:20:34', '2025-11-11 17:21:24');

-- --------------------------------------------------------

--
-- Table structure for table `priority_number_history`
--

CREATE TABLE `priority_number_history` (
  `history_id` int(11) NOT NULL,
  `priority_id` int(11) NOT NULL,
  `action` varchar(50) NOT NULL,
  `old_status` varchar(20) DEFAULT NULL,
  `new_status` varchar(20) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `priority_number_history`
--

INSERT INTO `priority_number_history` (`history_id`, `priority_id`, `action`, `old_status`, `new_status`, `user_id`, `ip_address`, `user_agent`, `created_at`) VALUES
(6, 4, 'generated', NULL, 'pending', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-05 01:43:28'),
(7, 5, 'generated', NULL, 'pending', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-05 01:43:35'),
(8, 6, 'generated', NULL, 'pending', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-05 01:43:52'),
(9, 4, 'served', 'pending', 'served', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-05 01:44:03'),
(10, 5, 'served', 'pending', 'served', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-05 01:44:16'),
(11, 6, 'served', 'pending', 'served', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-05 01:44:24'),
(12, 7, 'generated', NULL, 'pending', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-05 02:20:00'),
(13, 8, 'generated', NULL, 'pending', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-05 02:20:21'),
(14, 7, 'served', 'pending', 'served', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-05 02:20:34'),
(15, 8, 'served', 'pending', 'served', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-05 02:20:44'),
(16, 9, 'generated', NULL, 'pending', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-05 02:20:56'),
(17, 9, 'skipped', 'pending', 'skipped', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-05 02:21:03'),
(18, 10, 'generated', NULL, 'pending', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-05 02:24:26'),
(19, 10, 'skipped', 'pending', 'skipped', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-05 02:24:36'),
(20, 11, 'generated', NULL, 'pending', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-05 02:33:30'),
(21, 8, 'skipped', 'served', 'skipped', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-05 02:33:37'),
(22, 11, 'served', 'pending', 'served', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-05 02:33:37'),
(23, 12, 'generated', NULL, 'pending', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-05 02:33:49'),
(24, 12, 'served', 'pending', 'served', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-05 02:33:54'),
(25, 13, 'generated', NULL, 'pending', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-05 02:34:03'),
(26, 13, 'served', 'pending', 'served', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-05 02:34:07'),
(27, 14, 'served', 'pending', 'served', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-05 11:21:44'),
(28, 15, 'served', 'pending', 'served', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-05 11:22:06'),
(29, 16, 'generated', NULL, 'pending', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-11 17:20:34'),
(30, 16, 'served', 'pending', 'served', 4, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-11 17:21:24');

-- --------------------------------------------------------

--
-- Table structure for table `priority_queue_status`
--

CREATE TABLE `priority_queue_status` (
  `status_id` int(11) NOT NULL,
  `category` enum('payment','claims','registration') NOT NULL DEFAULT 'payment',
  `current_priority_number` varchar(10) DEFAULT NULL,
  `last_served_number` varchar(10) DEFAULT NULL,
  `queue_date` date NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `daily_capacity` int(11) DEFAULT 500,
  `served_count` int(11) DEFAULT 0,
  `timer_interval` int(11) DEFAULT 5,
  `lunch_break_start` time DEFAULT '12:00:00',
  `lunch_break_end` time DEFAULT '13:00:00',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `priority_queue_status`
--

INSERT INTO `priority_queue_status` (`status_id`, `category`, `current_priority_number`, `last_served_number`, `queue_date`, `is_active`, `daily_capacity`, `served_count`, `timer_interval`, `lunch_break_start`, `lunch_break_end`, `created_at`, `updated_at`) VALUES
(0, 'payment', '14', '14', '2025-11-05', 1, 500, 14, 5, '12:00:00', '13:00:00', '2025-11-05 01:43:28', '2025-11-05 11:21:44'),
(0, 'payment', 'P001', 'P001', '2025-11-12', 1, 500, 1, 5, '12:00:00', '13:00:00', '2025-11-11 17:20:34', '2025-11-11 17:21:24'),
(0, 'claims', '13', '13', '2025-11-05', 1, 500, 2, 5, '12:00:00', '13:00:00', '2025-11-05 02:33:49', '2025-11-05 11:22:06'),
(0, 'registration', 'R001', 'R001', '2025-11-05', 1, 500, 1, 5, '12:00:00', '13:00:00', '2025-11-05 02:34:03', '2025-11-05 02:34:07');

-- --------------------------------------------------------

--
-- Table structure for table `system_settings`
--

CREATE TABLE `system_settings` (
  `setting_id` int(11) NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `description` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `system_settings`
--

INSERT INTO `system_settings` (`setting_id`, `setting_key`, `setting_value`, `description`, `updated_at`) VALUES
(1, 'company_name', 'SOCOTECO II', 'Electric Cooperative Name', '2025-09-01 08:06:40'),
(2, 'company_address', 'Koronadal City, South Cotabato', 'Company Address', '2025-09-01 08:06:40'),
(3, 'vat_rate', '12', 'VAT Percentage', '2025-09-01 08:06:40'),
(4, 'penalty_rate', '2', 'Monthly Penalty Rate', '2025-09-01 08:06:40'),
(5, 'due_days', '15', 'Days from bill date to due date', '2025-09-01 08:06:40'),
(6, 'generation_rate', '4.5000', 'Generation charge per kWh', '2025-09-01 08:06:40'),
(7, 'distribution_rate', '1.2000', 'Distribution charge per kWh', '2025-09-01 08:06:40'),
(8, 'transmission_rate', '0.8000', 'Transmission charge per kWh', '2025-09-01 08:06:40'),
(9, 'system_loss_rate', '0.5000', 'System loss charge per kWh', '2025-09-01 08:06:40'),
(11, 'priority_daily_capacity', '500', NULL, '2025-09-15 17:56:08'),
(12, 'priority_advance_days', '7', NULL, '2025-09-15 17:56:08'),
(13, 'priority_expiry_hours', '24', NULL, '2025-09-15 17:56:08'),
(14, 'priority_notification_enabled', '1', NULL, '2025-09-15 17:56:08'),
(15, 'priority_auto_assign_days', '1', NULL, '2025-09-15 17:56:08'),
(16, 'priority_weekend_service', '0', NULL, '2025-09-15 17:56:08'),
(17, 'priority_break_start', '12:00', NULL, '2025-09-15 17:56:08'),
(18, 'priority_break_end', '13:00', NULL, '2025-09-15 17:56:08'),
(19, 'priority_timer_interval', '5', 'Timer interval in minutes for priority number calling', '2025-10-23 20:49:59'),
(20, 'priority_lunch_start', '12:00', 'Lunch break start time', '2025-10-23 20:49:59'),
(21, 'priority_lunch_end', '13:00', 'Lunch break end time', '2025-10-23 20:49:59'),
(22, 'priority_daily_capacity_per_category', '500', 'Daily capacity per category (payment, claims, registration)', '2025-10-23 20:49:59'),
(27, 'priority_service_start', '06:00', NULL, '2025-11-05 10:53:05'),
(28, 'priority_service_end', '22:00', NULL, '2025-11-05 10:53:05'),
(29, 'priority_calling_start', '07:00', NULL, '2025-11-05 10:53:05'),
(30, 'priority_calling_end', '22:00', NULL, '2025-11-05 10:53:05'),
(33, 'priority_auto_reset_daily', '1', NULL, '2025-11-05 10:53:05');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `role` enum('admin','cashier','meter_reader','customer') NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `password`, `full_name`, `email`, `role`, `is_active`, `created_at`, `updated_at`) VALUES
(4, 'admin', '$2y$10$b61uZ4nyV3OVcaU2qzOxQOVgwUEKVqIqhMVJDkJS9i/sJFNHQ3Jyu', 'System Administrator', 'admin@socoteco2.com', 'admin', 1, '2025-09-01 08:19:15', '2025-09-01 08:20:15'),
(5, 'noli', '$2y$10$ENW13EyylWXibRvPm3pzl.j92Y6ie7aCYxkGORrMTErqYiLJeIY0y', 'Noli Fin', 'wmoon146@gmail.com', 'cashier', 1, '2025-09-16 13:48:11', '2025-09-16 13:48:59'),
(6, 'brent', '$2y$10$VjOrWGfHUnAKr1wkdFgh/uvvJ2MAZUleSHPFsv/jn.Wcz8Kn4w9TK', 'Brent Delos Santos', 'delosniga123@gmail.com', 'meter_reader', 1, '2025-09-16 13:48:45', '2025-09-16 13:48:45'),
(7, 'Grin', '$2y$10$eHX0LUd6LpFDN3nAIy7VIeT3.4tL2ygv2PF31jh4C6MWGb9E14Hre', 'justin', 'justinjas@gmail.com', 'meter_reader', 1, '2025-10-08 11:11:37', '2025-10-08 11:11:37');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `audit_trail`
--
ALTER TABLE `audit_trail`
  ADD PRIMARY KEY (`audit_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `bills`
--
ALTER TABLE `bills`
  ADD PRIMARY KEY (`bill_id`),
  ADD UNIQUE KEY `bill_number` (`bill_number`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `reading_id` (`reading_id`);

--
-- Indexes for table `chat_messages`
--
ALTER TABLE `chat_messages`
  ADD PRIMARY KEY (`message_id`),
  ADD KEY `sender_customer_id` (`sender_customer_id`),
  ADD KEY `sender_user_id` (`sender_user_id`),
  ADD KEY `idx_session_created_at` (`session_id`,`created_at`),
  ADD KEY `idx_unread` (`session_id`,`is_read`),
  ADD KEY `idx_admin_unread` (`is_read_by_admin`,`created_at`);

--
-- Indexes for table `chat_moderation_logs`
--
ALTER TABLE `chat_moderation_logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `session_id` (`session_id`),
  ADD KEY `idx_moderation_logs_customer` (`customer_id`),
  ADD KEY `idx_moderation_logs_created` (`created_at`),
  ADD KEY `idx_moderation_logs_severity` (`severity`);

--
-- Indexes for table `chat_sessions`
--
ALTER TABLE `chat_sessions`
  ADD PRIMARY KEY (`session_id`),
  ADD KEY `customer_id` (`customer_id`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`customer_id`),
  ADD UNIQUE KEY `account_number` (`account_number`),
  ADD UNIQUE KEY `meter_number` (`meter_number`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `customer_categories`
--
ALTER TABLE `customer_categories`
  ADD PRIMARY KEY (`category_id`);

--
-- Indexes for table `customer_moderation_status`
--
ALTER TABLE `customer_moderation_status`
  ADD PRIMARY KEY (`customer_id`),
  ADD KEY `idx_moderation_status_blocked` (`is_temporarily_blocked`,`blocked_until`);

--
-- Indexes for table `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`feedback_id`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `idx_feedback_created_at` (`created_at`);

--
-- Indexes for table `feedback_flags`
--
ALTER TABLE `feedback_flags`
  ADD PRIMARY KEY (`feedback_id`,`customer_id`),
  ADD KEY `idx_feedback` (`feedback_id`),
  ADD KEY `idx_customer` (`customer_id`);

--
-- Indexes for table `feedback_likes`
--
ALTER TABLE `feedback_likes`
  ADD PRIMARY KEY (`feedback_id`,`customer_id`),
  ADD KEY `idx_feedback` (`feedback_id`),
  ADD KEY `idx_customer` (`customer_id`);

--
-- Indexes for table `feedback_replies`
--
ALTER TABLE `feedback_replies`
  ADD PRIMARY KEY (`reply_id`),
  ADD KEY `admin_user_id` (`admin_user_id`),
  ADD KEY `idx_feedback_created_at` (`feedback_id`,`created_at`),
  ADD KEY `idx_replies_feedback_id` (`feedback_id`),
  ADD KEY `idx_replies_created_at` (`created_at`);

--
-- Indexes for table `meter_readings`
--
ALTER TABLE `meter_readings`
  ADD PRIMARY KEY (`reading_id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `meter_reader_id` (`meter_reader_id`);

--
-- Indexes for table `moderation_settings`
--
ALTER TABLE `moderation_settings`
  ADD PRIMARY KEY (`setting_id`),
  ADD UNIQUE KEY `setting_name` (`setting_name`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`payment_id`),
  ADD UNIQUE KEY `or_number` (`or_number`),
  ADD KEY `bill_id` (`bill_id`),
  ADD KEY `cashier_id` (`cashier_id`);

--
-- Indexes for table `payment_history`
--
ALTER TABLE `payment_history`
  ADD PRIMARY KEY (`history_id`),
  ADD KEY `bill_id` (`bill_id`),
  ADD KEY `payment_id` (`payment_id`);

--
-- Indexes for table `priority_numbers`
--
ALTER TABLE `priority_numbers`
  ADD PRIMARY KEY (`priority_id`),
  ADD KEY `idx_category_date` (`category`,`service_date`),
  ADD KEY `idx_category_status` (`category`,`status`);

--
-- Indexes for table `priority_number_history`
--
ALTER TABLE `priority_number_history`
  ADD PRIMARY KEY (`history_id`);

--
-- Indexes for table `priority_queue_status`
--
ALTER TABLE `priority_queue_status`
  ADD UNIQUE KEY `category_date` (`category`,`queue_date`);

--
-- Indexes for table `system_settings`
--
ALTER TABLE `system_settings`
  ADD PRIMARY KEY (`setting_id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `audit_trail`
--
ALTER TABLE `audit_trail`
  MODIFY `audit_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=58;

--
-- AUTO_INCREMENT for table `bills`
--
ALTER TABLE `bills`
  MODIFY `bill_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `chat_messages`
--
ALTER TABLE `chat_messages`
  MODIFY `message_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT for table `chat_moderation_logs`
--
ALTER TABLE `chat_moderation_logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `chat_sessions`
--
ALTER TABLE `chat_sessions`
  MODIFY `session_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `customer_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `customer_categories`
--
ALTER TABLE `customer_categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `feedback`
--
ALTER TABLE `feedback`
  MODIFY `feedback_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `feedback_replies`
--
ALTER TABLE `feedback_replies`
  MODIFY `reply_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `meter_readings`
--
ALTER TABLE `meter_readings`
  MODIFY `reading_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `moderation_settings`
--
ALTER TABLE `moderation_settings`
  MODIFY `setting_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `payment_history`
--
ALTER TABLE `payment_history`
  MODIFY `history_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `priority_numbers`
--
ALTER TABLE `priority_numbers`
  MODIFY `priority_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `priority_number_history`
--
ALTER TABLE `priority_number_history`
  MODIFY `history_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `system_settings`
--
ALTER TABLE `system_settings`
  MODIFY `setting_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=56;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `audit_trail`
--
ALTER TABLE `audit_trail`
  ADD CONSTRAINT `audit_trail_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `bills`
--
ALTER TABLE `bills`
  ADD CONSTRAINT `bills_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`),
  ADD CONSTRAINT `bills_ibfk_2` FOREIGN KEY (`reading_id`) REFERENCES `meter_readings` (`reading_id`);

--
-- Constraints for table `chat_moderation_logs`
--
ALTER TABLE `chat_moderation_logs`
  ADD CONSTRAINT `chat_moderation_logs_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `chat_moderation_logs_ibfk_2` FOREIGN KEY (`session_id`) REFERENCES `chat_sessions` (`session_id`) ON DELETE SET NULL;

--
-- Constraints for table `customers`
--
ALTER TABLE `customers`
  ADD CONSTRAINT `customers_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `customer_categories` (`category_id`);

--
-- Constraints for table `customer_moderation_status`
--
ALTER TABLE `customer_moderation_status`
  ADD CONSTRAINT `customer_moderation_status_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`) ON DELETE CASCADE;

--
-- Constraints for table `meter_readings`
--
ALTER TABLE `meter_readings`
  ADD CONSTRAINT `meter_readings_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`),
  ADD CONSTRAINT `meter_readings_ibfk_2` FOREIGN KEY (`meter_reader_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`bill_id`) REFERENCES `bills` (`bill_id`),
  ADD CONSTRAINT `payments_ibfk_2` FOREIGN KEY (`cashier_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `payment_history`
--
ALTER TABLE `payment_history`
  ADD CONSTRAINT `payment_history_ibfk_1` FOREIGN KEY (`bill_id`) REFERENCES `bills` (`bill_id`),
  ADD CONSTRAINT `payment_history_ibfk_2` FOREIGN KEY (`payment_id`) REFERENCES `payments` (`payment_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

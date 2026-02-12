-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 07, 2026 at 09:33 PM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.1.17

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `deenlink_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `comment_likes`
--

CREATE TABLE `comment_likes` (
  `comment_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `comment_likes`
--

INSERT INTO `comment_likes` (`comment_id`, `user_id`, `created_at`) VALUES
(3, 30, '2026-02-02 01:33:18'),
(4, 30, '2026-02-02 01:42:27');

-- --------------------------------------------------------

--
-- Table structure for table `failed_login_attempts`
--

CREATE TABLE `failed_login_attempts` (
  `id` int(11) NOT NULL,
  `identifier` varchar(100) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `attempt_time` datetime DEFAULT current_timestamp(),
  `user_agent` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token_hash` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `password_reset_tokens`
--

INSERT INTO `password_reset_tokens` (`id`, `user_id`, `token_hash`, `expires_at`, `used_at`, `created_at`) VALUES
(1, 2, '2367e76e3af6cca628822a27c1d761d03d791c32696230004784bfb2a653f223', '2026-01-30 00:12:51', '2026-01-29 23:43:37', '2026-01-29 23:42:51'),
(2, 2, '697ac1c7498793d477efd9e01903c8c13c9790a66edcbb125f26f18346507600', '2026-01-30 00:17:13', '2026-01-29 23:48:00', '2026-01-29 23:47:13'),
(3, 25, '3fefa9d5705fe62e35f0a6b9bd1d1e94fefdafe88dab716cb322e580870e4a4c', '2026-01-30 00:28:01', NULL, '2026-01-29 23:58:01'),
(4, 2, 'f52967b9ba0e9ea41c6767446d83c94e9b3211de461a96a936fa2711a2ece5b1', '2026-01-30 00:31:35', '2026-01-30 00:02:12', '2026-01-30 00:01:35'),
(5, 2, '10b6e7d4db723c691b0d0027af9417556d58af3ccf67987500fbb5e60b2ca255', '2026-01-30 00:32:24', NULL, '2026-01-30 00:02:24');

-- --------------------------------------------------------

--
-- Table structure for table `posts`
--

CREATE TABLE `posts` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `content_text` text DEFAULT NULL,
  `visibility` enum('public','followers') DEFAULT 'public',
  `is_deleted` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `posts`
--

INSERT INTO `posts` (`id`, `user_id`, `content_text`, `visibility`, `is_deleted`, `created_at`, `updated_at`) VALUES
(1, 2, 'Usman and Fadila story is getting to an end', 'public', 0, '2026-02-02 00:37:01', '2026-02-02 00:37:01'),
(2, 30, 'Hello, This is a reminder to fast tomorrow is thurday, may Allah bless you All', 'public', 1, '2026-02-02 00:40:45', '2026-02-02 14:53:47'),
(3, 30, 'assasasa', 'public', 1, '2026-02-02 01:07:31', '2026-02-02 15:02:29'),
(4, 30, 'zsdadas', 'public', 0, '2026-02-02 01:12:43', '2026-02-02 01:12:43'),
(5, 2, 'Hello world', 'public', 0, '2026-02-02 02:00:58', '2026-02-02 02:00:58'),
(6, 2, 'eweewew', 'public', 1, '2026-02-02 10:36:04', '2026-02-03 00:36:39'),
(7, 2, 'wqwqwqwq', 'public', 0, '2026-02-02 10:50:25', '2026-02-02 10:50:25'),
(8, 2, 'wwewew', 'public', 0, '2026-02-02 10:51:12', '2026-02-02 10:51:12'),
(9, 2, 'wwqwqqw', 'public', 0, '2026-02-02 10:56:31', '2026-02-02 10:56:31'),
(10, 30, 'ssasasa', 'public', 1, '2026-02-02 14:24:07', '2026-02-02 14:53:53'),
(11, 25, 'Hello World', 'public', 0, '2026-02-03 00:20:01', '2026-02-03 00:20:01'),
(12, 31, 'Official Account', 'public', 0, '2026-02-05 12:56:57', '2026-02-05 12:56:57');

-- --------------------------------------------------------

--
-- Table structure for table `post_comments`
--

CREATE TABLE `post_comments` (
  `id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `comment_text` text NOT NULL,
  `is_deleted` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `post_comments`
--

INSERT INTO `post_comments` (`id`, `post_id`, `user_id`, `comment_text`, `is_deleted`, `created_at`) VALUES
(1, 1, 2, 'This is just the beginning', 0, '2026-02-02 00:37:29'),
(2, 1, 30, 'You did great by the way', 0, '2026-02-02 00:38:53'),
(3, 1, 30, 'assa', 0, '2026-02-02 00:42:30'),
(4, 1, 30, 'sdsd', 0, '2026-02-02 01:06:59'),
(5, 3, 30, 'sasasa', 0, '2026-02-02 01:07:38'),
(6, 3, 30, 'cfd', 0, '2026-02-02 01:13:49'),
(7, 3, 30, 'sSS', 0, '2026-02-02 01:56:25'),
(8, 4, 2, 'hello', 0, '2026-02-02 01:57:19'),
(9, 11, 2, 'saassa', 0, '2026-02-03 01:19:15');

-- --------------------------------------------------------

--
-- Table structure for table `post_likes`
--

CREATE TABLE `post_likes` (
  `post_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `post_likes`
--

INSERT INTO `post_likes` (`post_id`, `user_id`, `created_at`) VALUES
(1, 2, '2026-02-02 02:01:10'),
(1, 30, '2026-02-02 00:38:31'),
(2, 30, '2026-02-02 00:40:50'),
(6, 30, '2026-02-02 14:54:25'),
(8, 2, '2026-02-02 13:44:38'),
(9, 2, '2026-02-02 13:44:40'),
(11, 2, '2026-02-03 00:20:33'),
(11, 25, '2026-02-03 00:20:03'),
(12, 31, '2026-02-05 12:57:03');

-- --------------------------------------------------------

--
-- Table structure for table `post_media`
--

CREATE TABLE `post_media` (
  `id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `media_type` enum('image') DEFAULT 'image',
  `file_1080` varchar(255) NOT NULL,
  `file_360` varchar(255) NOT NULL,
  `width` int(11) DEFAULT NULL,
  `height` int(11) DEFAULT NULL,
  `size_bytes` int(11) DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `post_media`
--

INSERT INTO `post_media` (`id`, `post_id`, `media_type`, `file_1080`, `file_360`, `width`, `height`, `size_bytes`, `sort_order`, `created_at`) VALUES
(1, 1, 'image', '2026/02/post_1_94ef4b88a0c3_1080.jpg', '2026/02/post_1_94ef4b88a0c3_360.jpg', 800, 800, 65246, 0, '2026-02-02 00:37:01'),
(2, 5, 'image', '2026/02/post_5_bc84ed0525eb_1080.jpg', '2026/02/post_5_bc84ed0525eb_360.jpg', 800, 800, 82577, 0, '2026-02-02 02:00:58'),
(3, 6, 'image', '2026/02/post_6_6bd2f5a1321a_1080.jpg', '2026/02/post_6_6bd2f5a1321a_360.jpg', 200, 204, 12488, 0, '2026-02-02 10:36:04'),
(4, 7, 'image', '2026/02/post_7_b0bfab6fb45e_1080.jpg', '2026/02/post_7_b0bfab6fb45e_360.jpg', 1024, 1024, 101902, 0, '2026-02-02 10:50:26'),
(5, 12, 'image', '2026/02/post_12_44a605d27652_1080.jpg', '2026/02/post_12_44a605d27652_360.jpg', 1080, 1434, 138605, 0, '2026-02-05 12:56:59');

-- --------------------------------------------------------

--
-- Table structure for table `scholars`
--

CREATE TABLE `scholars` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `display_name` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `fields_of_knowledge` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`fields_of_knowledge`)),
  `other_field` varchar(100) DEFAULT NULL,
  `madhhab` varchar(50) DEFAULT NULL,
  `institute` varchar(200) NOT NULL,
  `years_of_study` int(11) NOT NULL,
  `teachers` text DEFAULT NULL,
  `approval_status` enum('pending','approved','rejected') DEFAULT 'pending',
  `approval_notes` text DEFAULT NULL,
  `reviewed_by` int(11) DEFAULT NULL,
  `reviewed_at` datetime DEFAULT NULL,
  `certificate_path` varchar(500) DEFAULT NULL,
  `recommendation_path` varchar(500) DEFAULT NULL,
  `verification_links` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `scholars`
--

INSERT INTO `scholars` (`id`, `user_id`, `display_name`, `phone`, `fields_of_knowledge`, `other_field`, `madhhab`, `institute`, `years_of_study`, `teachers`, `approval_status`, `approval_notes`, `reviewed_by`, `reviewed_at`, `certificate_path`, `recommendation_path`, `verification_links`, `created_at`, `updated_at`) VALUES
(1, 32, 'sheikh hamza', '96566565767667', '[\"hadith\",\"tafsir\",\"aqeedah\",\"quran\"]', NULL, 'sjjsksakjsa', 'sasasa', 12, 'sasasasa', 'pending', NULL, NULL, NULL, 'certificate_1_1770419051.jpg', NULL, NULL, '2026-02-07 00:04:11', '2026-02-07 00:04:11'),
(2, 34, 'sheikh Mansur', '09031528732', '[\"hadith\",\"aqeedah\"]', NULL, 'aaaa', 'jggggj', 54, 'jhjhjhjh', 'pending', NULL, NULL, NULL, 'certificate_2_1770469134.jpg', NULL, NULL, '2026-02-07 13:58:54', '2026-02-07 13:58:54');

-- --------------------------------------------------------

--
-- Table structure for table `scholar_documents`
--

CREATE TABLE `scholar_documents` (
  `id` int(11) NOT NULL,
  `scholar_id` int(11) NOT NULL,
  `document_type` enum('certificate','recommendation','other') NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_size` int(11) DEFAULT NULL,
  `mime_type` varchar(100) DEFAULT NULL,
  `uploaded_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `scholar_documents`
--

INSERT INTO `scholar_documents` (`id`, `scholar_id`, `document_type`, `file_path`, `file_name`, `file_size`, `mime_type`, `uploaded_at`) VALUES
(1, 1, 'certificate', 'certificate_1_1770419051.jpg', 'default_profile.jpg', 11833, 'image/jpeg', '2026-02-07 00:04:11'),
(2, 2, 'certificate', 'certificate_2_1770469134.jpg', 'default_profile.jpg', 11833, 'image/jpeg', '2026-02-07 13:58:54');

-- --------------------------------------------------------

--
-- Table structure for table `scholar_verification_links`
--

CREATE TABLE `scholar_verification_links` (
  `id` int(11) NOT NULL,
  `scholar_id` int(11) NOT NULL,
  `link` varchar(500) NOT NULL,
  `link_type` varchar(50) DEFAULT 'other',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `gender` enum('male','female') DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `profile_image` varchar(255) DEFAULT 'default_profile.jpg',
  `deenpoints_balance` int(11) DEFAULT 100,
  `bio` text DEFAULT NULL,
  `is_private` tinyint(1) DEFAULT 0,
  `hide_charity_balance` tinyint(1) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `user_type` enum('user','scholar','admin') NOT NULL DEFAULT 'user',
  `is_email_verified` tinyint(1) DEFAULT 0,
  `two_factor_enabled` tinyint(1) DEFAULT 0,
  `two_factor_secret` varchar(255) DEFAULT NULL,
  `failed_login_attempts` int(11) DEFAULT 0,
  `account_locked_until` datetime DEFAULT NULL,
  `last_login` datetime DEFAULT NULL,
  `last_password_change` datetime DEFAULT NULL,
  `last_login_ip` varchar(45) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `email_verification_token_hash` varchar(255) DEFAULT NULL,
  `email_verification_expires_at` datetime DEFAULT NULL,
  `last_username_change` datetime DEFAULT NULL,
  `last_full_name_change` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password_hash`, `full_name`, `gender`, `country`, `phone`, `profile_image`, `deenpoints_balance`, `bio`, `is_private`, `hide_charity_balance`, `is_active`, `user_type`, `is_email_verified`, `two_factor_enabled`, `two_factor_secret`, `failed_login_attempts`, `account_locked_until`, `last_login`, `last_password_change`, `last_login_ip`, `created_at`, `updated_at`, `email_verification_token_hash`, `email_verification_expires_at`, `last_username_change`, `last_full_name_change`) VALUES
(1, 'testuser', 'test@example.com', '$2y$12$ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuv', 'Test User', 'male', 'Nigeria', NULL, 'default_profile.jpg', 500, 'This is a test account for development purposes', 0, 0, 1, '', 1, 0, NULL, 1, NULL, NULL, NULL, NULL, '2026-01-21 00:44:56', '2026-02-07 16:58:07', NULL, NULL, NULL, NULL),
(2, 'pixela', 'useeman32@gmail.com', '$2y$10$MAvzMlpb2kufXVNvhjxkuOnjimnA9LfT1C8fgVkJFliYMsmcLwmki', 'Usman Ahmadsd', NULL, NULL, NULL, 'profile_2_1769815147.jpg', 115, 'InshaAllah i will succedaasdsa', 0, 0, 1, '', 1, 0, NULL, 0, NULL, '2026-02-07 13:48:21', '2026-01-30 00:02:12', '::1', '2026-01-21 11:47:53', '2026-02-07 13:48:21', NULL, NULL, '2026-01-31 00:42:42', '2026-01-31 00:43:20'),
(3, 'pixels', 'atta@gmail.com', '$2y$10$S2MsRtijoY8faGAYZyx6HeIel2phLdmWYwM4XJOKCb743lWI5VcUa', 'Usman Ahmaddd', NULL, NULL, NULL, 'default_profile.jpg', 100, NULL, 0, 0, 1, '', 0, 0, NULL, 0, NULL, NULL, NULL, NULL, '2026-01-21 11:58:42', '2026-01-28 15:12:16', NULL, NULL, NULL, NULL),
(4, 'Pixel32', 'useeman31@gmail.com', '$2y$10$uZ/Rg3WHr1eyFNSnPtexjuhwDJkC1dceVY6gJQJJkCZAH/VnyzFPG', 'Usman Ahmad', NULL, NULL, NULL, 'default_profile.jpg', 100, NULL, 0, 0, 1, '', 0, 0, NULL, 0, NULL, NULL, NULL, NULL, '2026-01-21 23:41:18', '2026-01-28 15:12:16', NULL, NULL, NULL, NULL),
(5, 'Pixel33', 'assalam@mail.com', '$2y$10$t1PVVJfADpBFVH9EE7sk0eVj9JI0vjlSo55JxAzJPYa/PQ0No9/WO', 'Usman Ahmad', NULL, NULL, NULL, 'default_profile.jpg', 100, NULL, 0, 0, 1, '', 0, 0, NULL, 0, NULL, NULL, NULL, NULL, '2026-01-21 23:59:55', '2026-01-28 15:12:16', NULL, NULL, NULL, NULL),
(6, 'sddsd', 'useeman32@ssgmail.com', '$2y$10$0kx2u3JMrx6RtyQD6SZ.nuHYYztPqV7PBPSnYBwGGn4xueb4Pu/X6', 'Usman Ahmad', 'male', 'Nigeria', NULL, 'default_profile.jpg', 100, NULL, 0, 0, 1, '', 0, 0, NULL, 0, NULL, NULL, NULL, NULL, '2026-01-22 00:12:38', '2026-01-22 00:12:38', NULL, NULL, NULL, NULL),
(7, 'Indil', 'wakkala@gmail.com', '$2y$10$Wjy3T1Cm6C/609gwvSvOVu54/c5FG7uRnTWbwKK0M64idwDtddul.', 'Fadila Wakkala', 'female', 'Nigeria', NULL, 'default_profile.jpg', 100, NULL, 0, 0, 1, '', 0, 0, NULL, 0, NULL, NULL, NULL, NULL, '2026-01-23 00:44:24', '2026-01-23 00:44:24', NULL, NULL, NULL, NULL),
(8, 'Pixelssad', 'useeman32ssa@gmail.com', '$2y$10$K7YfbEoNgthwW7Mv4u9Q0u/.p1HUbF//TZl3PUo4r0dYPpuog5UJW', 'Usman Ahmad', 'male', 'Nigeria', NULL, 'default_profile.jpg', 100, NULL, 0, 0, 1, '', 0, 0, NULL, 0, NULL, NULL, NULL, NULL, '2026-01-23 00:46:51', '2026-01-23 00:46:51', NULL, NULL, NULL, NULL),
(9, 'Pixel322', 'useemaans32@gmail.com', '$2y$10$4ZehwyGT63HiaymELlm0Ae/rCXXMqiGYnK8nZczPRoOTi69xevQOa', 'Usman Ahmad', 'male', 'Nigeria', NULL, 'default_profile.jpg', 100, NULL, 0, 0, 1, '', 0, 0, NULL, 0, NULL, NULL, NULL, NULL, '2026-01-23 00:53:17', '2026-01-23 00:53:17', NULL, NULL, NULL, NULL),
(10, 'Pixel32q2', 'useeman3s2@gmail.com', '$2y$10$sqVWATa.fLnwp17e83kHk.mRdNBocTs8fYQof5UL0UHG8WOVa7DhS', 'Usman Ahmad', 'male', 'Nigeria', NULL, 'default_profile.jpg', 100, NULL, 0, 0, 1, '', 0, 0, NULL, 0, NULL, NULL, NULL, NULL, '2026-01-23 01:25:38', '2026-01-23 01:25:38', NULL, NULL, NULL, NULL),
(11, 'pixel333', 'useeman32@gmail.comm', '$2y$10$oB4DidwB.zrgArQk.pHcAOGlaAvusgLHZzTG1HS8PR4rtgoElpQ3G', 'Usman Ahmad', 'male', 'Nigeria', NULL, 'default_profile.jpg', 100, NULL, 0, 0, 1, '', 0, 0, NULL, 0, NULL, NULL, NULL, NULL, '2026-01-28 12:07:55', '2026-01-28 12:07:55', NULL, NULL, NULL, NULL),
(12, 'hamz', 'useeman3a2@gmail.com', '$2y$10$VcpEJcIJFTarZPwsXJTHb.QUdc9VuLVGjsNXaYG6mAhMVpzDutGc6', 'Usman Ahmad', 'male', 'Nigeria', NULL, 'default_profile.jpg', 100, NULL, 0, 0, 1, '', 0, 0, NULL, 0, NULL, NULL, NULL, NULL, '2026-01-28 14:16:10', '2026-01-28 14:16:10', NULL, NULL, NULL, NULL),
(13, 'aaaa', 'useeman3@gmail.com', '$2y$10$28Y6wTQd2r5Gq19nSz5xBekvz99pKiYyIlq6lChd1FA7Y7v5mCK9a', 'Usman Ahmad', 'male', 'Nigeria', NULL, 'default_profile.jpg', 100, NULL, 0, 0, 1, '', 0, 0, NULL, 0, NULL, NULL, NULL, NULL, '2026-01-28 20:21:59', '2026-01-28 20:21:59', '87f00b5ee3c8a3d31bd1a20fedcb48f54e4378d6ab54544e5dcef3d4e9a3b743', '2026-01-29 20:21:59', NULL, NULL),
(14, 'fadila', 'fadila@gmail.com', '$2y$10$SzQzowAG0maXHLcPEr9Ah.VHEP.4VgvexGb8KvD0BLt5Irg2zm9C.', 'Fadila Wakkala', 'female', 'Nigeria', NULL, 'default_profile.jpg', 100, NULL, 0, 0, 1, '', 1, 0, NULL, 0, NULL, '2026-01-29 01:33:52', NULL, '::1', '2026-01-29 01:25:34', '2026-01-29 01:33:52', NULL, NULL, NULL, NULL),
(15, 'muttaka', 'muttaka@gmail.com', '$2y$10$1VyEUBhuPFxIpCOfZHsmxupmK3z37IINLGHPaMhQCSYMJRy7WDIeq', 'Muttaka', 'male', 'Argentina', NULL, 'default_profile.jpg', 100, NULL, 0, 0, 1, '', 0, 0, NULL, 0, NULL, NULL, NULL, NULL, '2026-01-29 01:36:37', '2026-01-29 01:36:37', '64c357c383984762b894037fd5fa57d33d000afe141fd71d08667a0da278f67c', '2026-01-30 01:36:37', NULL, NULL),
(16, 'muttakaa', 'muttakka@gmail.com', '$2y$10$.k7I35/d171UHsrQSRvPxO2UvoNkHKQnMxRa4f1gq9X1EqaZTc7ly', 'Muttaka', 'male', 'Argentina', NULL, 'default_profile.jpg', 100, NULL, 0, 0, 1, '', 0, 0, NULL, 0, NULL, NULL, NULL, NULL, '2026-01-29 01:37:53', '2026-01-29 01:37:53', 'e4d3158e0ee87547a06c847e12c768117f1247a7c96f20e6ba7510f58c89cab9', '2026-01-30 01:37:53', NULL, NULL),
(17, 'aisha', 'aiabubakar@gmail.com', '$2y$10$kXNZ2wb3gucKw/e01gLc3O4s.jmMmyG74wzjCHfJolni3CkaON.ju', 'Aisha Abubakar', 'female', 'Mongolia', NULL, 'default_profile.jpg', 100, NULL, 0, 0, 1, '', 1, 0, NULL, 0, NULL, '2026-01-29 12:26:54', NULL, '::1', '2026-01-29 12:23:24', '2026-01-29 12:26:54', NULL, NULL, NULL, NULL),
(18, 'wwee', 'rufai@gmail.com', '$2y$10$Qmf/iizYkIRA/i8tOocAC.07gHBEx5VydUnur7rGW0nxNv12e/2GO', 'sanusi rufai', 'male', 'Nigeria', NULL, 'default_profile.jpg', 100, NULL, 0, 0, 1, '', 1, 0, NULL, 0, NULL, '2026-01-29 12:29:50', NULL, '::1', '2026-01-29 12:27:45', '2026-01-29 12:29:50', NULL, NULL, NULL, NULL),
(19, 'anka', 'anka@gmail.com', '$2y$10$6Bmz6oJweeCbpuG04My6R.Q9dsly50nIma0.yEqBT0ndT8vLLqGgu', 'Muntari Anka', 'male', 'Nigeria', NULL, 'default_profile.jpg', 100, NULL, 0, 0, 1, '', 1, 0, NULL, 0, NULL, '2026-01-29 12:43:23', NULL, '::1', '2026-01-29 12:41:24', '2026-01-29 12:43:23', NULL, NULL, NULL, NULL),
(20, 'hindatu', 'hindu@gmail.com', '$2y$10$SqhuXC5v1qerotQmsKFDmOT2l60A5eJzN9yXFyV7JQllodzW6.9KG', 'Hindatu Unknown', 'male', 'Nigeria', NULL, 'default_profile.jpg', 100, NULL, 0, 0, 1, '', 1, 0, NULL, 0, NULL, '2026-01-29 12:53:38', NULL, '::1', '2026-01-29 12:47:55', '2026-01-29 12:53:38', NULL, NULL, NULL, NULL),
(21, 'fatima', 'fatima@gmail.com', '$2y$10$1RifHxyD2FAcqz.zeBr7xenZpWyu2f1b8fhg4uQmlKRuTNRwIIk.y', 'Fatima Lawal', 'male', 'Nigeria', NULL, 'default_profile.jpg', 100, NULL, 0, 0, 0, '', 0, 0, NULL, 0, NULL, NULL, NULL, NULL, '2026-01-29 12:54:41', '2026-01-29 12:54:41', '38803f8dc5cf3949481e99c18232715a984d70af50af6a5ffe4c3c165c571f9b', '2026-01-30 12:54:41', NULL, NULL),
(22, 'fatimaa', 'fatimaa@gmail.com', '$2y$10$ky7Jz6ohnybljK4zZZPpKeq0TEvDfEw.ViIK3bh6/vogMsQvoPZ6e', 'Fatima Lawal', 'male', 'Nigeria', NULL, 'default_profile.jpg', 100, NULL, 0, 0, 0, '', 0, 0, NULL, 0, NULL, NULL, NULL, NULL, '2026-01-29 12:58:42', '2026-01-29 12:58:42', '836a840c963ce0c993e689d74d51c5f5059b91f8ebaa70b38c421d0b9bc04f61', '2026-01-30 12:58:42', NULL, NULL),
(23, 'hauwa', 'bello@gmail.com', '$2y$10$Pf6mnSk75OgXyt44aRTgluIyFlbFz/9THy/LkV/VBqm7g2gdoXVCm', 'Huawa Bello', 'female', 'Nigeria', NULL, 'default_profile.jpg', 100, NULL, 0, 0, 1, '', 1, 0, NULL, 1, NULL, NULL, NULL, NULL, '2026-01-29 13:19:01', '2026-01-29 13:20:28', NULL, NULL, NULL, NULL),
(24, 'hauwaa', 'belloo@gmail.com', '$2y$10$z954SjZs4lyYiSCAE2OiXOtp2IeT/S8zpexURlLnK8tCI6.iJ5pfG', 'Huawa Bello', 'female', 'Nigeria', NULL, 'default_profile.jpg', 100, NULL, 0, 0, 0, '', 0, 0, NULL, 0, NULL, NULL, NULL, NULL, '2026-01-29 13:20:41', '2026-01-29 13:20:41', 'babbe0e224071eaf5f634b1e36b24b7aad3cc430b0441704b20337399f78eeb1', '2026-01-30 13:20:41', NULL, NULL),
(25, 'com', 'usman@gmail.com', '$2y$10$n5GqF9/yYr0t3n./Wgy2N.Tl9ky69rZCnJttjnzTAN4NUva7PZEAO', 'Hamza Usman', 'male', 'Nigeria', NULL, 'default_profile.jpg', 100, NULL, 0, 0, 1, '', 1, 0, NULL, 0, NULL, '2026-02-07 13:47:45', NULL, '::1', '2026-01-29 21:51:47', '2026-02-07 13:47:45', NULL, NULL, NULL, NULL),
(26, 'mantala', 'garba@gmail.com', '$2y$10$4I2dG.f1se7f2VogwYbf6OSrl/wwGzrc7c.O/kL140TM0Y9nXuAtW', 'mantala garba', 'male', 'Nigeria', NULL, 'default_profile.jpg', 100, NULL, 0, 0, 0, '', 0, 0, NULL, 0, NULL, NULL, NULL, NULL, '2026-01-29 22:11:37', '2026-01-29 22:12:42', 'd182dcfc4cacdf324ebf3c58f52a5915dc001283c9247f75cb9d588264c72675', '2026-01-30 22:12:42', NULL, NULL),
(27, 'tilawa', 'tilawa@gmail.com', '$2y$10$7quQSQhKLUBuy3kuamh8Su1wNeFonmsUUROT76exJuUWL3Ws94/x2', 'tilawa quran', 'male', 'Nigeria', NULL, 'default_profile.jpg', 100, NULL, 0, 0, 0, '', 0, 0, NULL, 0, NULL, NULL, NULL, NULL, '2026-01-29 22:21:45', '2026-01-29 22:21:45', '9a500f41866cd40d8d65d21a230ec9c9c70d5a73e874a48d4a3db3dcc5b94581', '2026-01-30 22:21:45', NULL, NULL),
(28, 'farku', 'farku@gmail.com', '$2y$10$QQbohPZKQ8uKSu1VWJcW7uNUauXSTjzLH6if0WysjReftXz3U6iSa', 'Faekuu', 'male', 'Nigeria', NULL, 'default_profile.jpg', 100, NULL, 0, 0, 0, '', 0, 0, NULL, 0, NULL, NULL, NULL, NULL, '2026-01-29 22:22:23', '2026-01-29 22:22:23', '157cfcf6dad77125090fbe7f8b52fbe55b6629caa78c84a27e9899621b5f5bbc', '2026-01-30 22:22:23', NULL, NULL),
(29, 'tusa', 'tusa@gmail.com', '$2y$10$bun4LKiBs3MB9uEiOH02V.96TRa2KjWj7imZmSU5ht20sjr5CYPHy', 'Tilawa Hmaza', 'male', 'Nigeria', NULL, 'default_profile.jpg', 100, NULL, 0, 0, 1, '', 1, 0, NULL, 0, NULL, '2026-01-29 22:27:45', NULL, '::1', '2026-01-29 22:26:45', '2026-01-29 22:27:45', NULL, NULL, NULL, NULL),
(30, 'aishaaa', 'aishaa@gmail.com', '$2y$10$WHeCiVebA.qrB5SFxfeduus9MvppheEEALEz2xQiei2MTMgquw0vS', 'Abubakar Besse Abdullahi', 'male', 'Nigeria', NULL, 'profile_30_1769979686.jpg', 105, 'Mahsha allah', 0, 0, 1, '', 1, 0, NULL, 0, NULL, '2026-02-03 00:25:49', NULL, '::1', '2026-01-29 22:54:13', '2026-02-03 00:35:33', NULL, NULL, '2026-01-31 00:53:30', NULL),
(31, 'Pixel', 'useeman33@gmail.com', '$2y$10$ZteL1pV3qpmnqI0oyzwad.3Kvq2sgG01qcOJHqdVN2CZfwDBy7Kji', 'Usman Ahmad', 'male', 'Nigeria', NULL, 'default_profile.jpg', 105, NULL, 0, 0, 1, '', 1, 0, NULL, 0, NULL, '2026-02-05 12:56:18', NULL, '::1', '2026-02-05 12:54:41', '2026-02-05 12:56:27', NULL, NULL, NULL, NULL),
(32, 'hamza', 'hamza@gmail.com', '$2y$10$8WwPJuDfUNi28eNt8.x0Ou7wJItjH4PftOC95sYXOyuGRjcY8Qw5S', 'Hamza Sheikh', 'male', 'Albania', '96566565767667', 'default_profile.jpg', 105, NULL, 0, 0, 1, 'scholar', 1, 0, NULL, 0, NULL, '2026-02-07 00:08:41', NULL, '::1', '2026-02-07 00:04:11', '2026-02-07 00:08:56', NULL, NULL, NULL, NULL),
(33, 'covering', 'covering@gmail.com', '$2y$10$SFqqsSh/o.46E9u2Dh6UEubO68Up8KrUtD9zZzVCi9z5FrBXRCyfG', 'Daddy covering', 'male', 'Nigeria', NULL, 'default_profile.jpg', 100, NULL, 0, 0, 1, 'user', 1, 0, NULL, 0, NULL, '2026-02-07 13:31:58', NULL, '::1', '2026-02-07 13:31:09', '2026-02-07 13:31:58', NULL, NULL, NULL, NULL),
(34, 'mansur', 'mansur@gmail.com', '$2y$10$6zqz5jPUfcXWlDZe2.gaWejJGTwKqpfV3tvn.jIX7o4Y50bB55TQe', 'Mansur Isah', 'male', 'Nigeria', '09031528732', 'default_profile.jpg', 105, NULL, 0, 0, 1, 'scholar', 1, 0, NULL, 0, NULL, '2026-02-07 21:19:21', NULL, '::1', '2026-02-07 13:58:54', '2026-02-07 21:19:21', NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user_activity_log`
--

CREATE TABLE `user_activity_log` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `activity_type` varchar(50) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `details` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_daily_checkins`
--

CREATE TABLE `user_daily_checkins` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `checkin_date` date NOT NULL,
  `points_awarded` int(11) NOT NULL DEFAULT 5,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_daily_checkins`
--

INSERT INTO `user_daily_checkins` (`id`, `user_id`, `checkin_date`, `points_awarded`, `created_at`) VALUES
(1, 30, '2026-02-03', 5, '2026-02-03 00:35:33'),
(2, 2, '2026-02-03', 5, '2026-02-03 00:36:42'),
(3, 2, '2026-02-04', 5, '2026-02-04 23:13:36'),
(4, 2, '2026-02-05', 5, '2026-02-05 00:28:53'),
(5, 31, '2026-02-05', 5, '2026-02-05 12:56:27'),
(6, 32, '2026-02-07', 5, '2026-02-07 00:08:56'),
(7, 34, '2026-02-07', 5, '2026-02-07 14:01:46');

-- --------------------------------------------------------

--
-- Table structure for table `user_follows`
--

CREATE TABLE `user_follows` (
  `follower_id` int(11) NOT NULL,
  `following_id` int(11) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ;

--
-- Dumping data for table `user_follows`
--

INSERT INTO `user_follows` (`follower_id`, `following_id`, `created_at`) VALUES
(2, 25, '2026-02-03 00:20:40'),
(2, 30, '2026-02-03 00:18:28'),
(25, 2, '2026-02-03 00:19:31'),
(25, 30, '2026-02-03 00:19:33'),
(30, 2, '2026-02-03 00:17:24');

-- --------------------------------------------------------

--
-- Table structure for table `user_sessions`
--

CREATE TABLE `user_sessions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `session_token_hash` varchar(255) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `device_info` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `last_activity` datetime DEFAULT current_timestamp(),
  `expires_at` datetime NOT NULL,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_sessions`
--

INSERT INTO `user_sessions` (`id`, `user_id`, `session_token_hash`, `ip_address`, `user_agent`, `device_info`, `created_at`, `last_activity`, `expires_at`, `is_active`) VALUES
(1, 6, '4dac2b5789583cf23ce8baf33b0546e3923a81e855b6a206b3d89a90fe150acc', '::1', NULL, NULL, '2026-01-22 00:12:44', '2026-01-23 14:29:10', '2026-02-21 00:12:44', 0),
(2, 7, 'e1a2f9bed0ae90c183aba8245a7841498b00e118bb1cdcf3cd33eb345231fa19', '::1', NULL, NULL, '2026-01-23 00:44:32', '2026-01-23 14:29:10', '2026-02-22 00:44:32', 0),
(3, 9, '643e720716c2a9e0fbbe8dcec92d353085025ba2bb2fabadc0c57d4e3b25beab', '::1', NULL, NULL, '2026-01-23 00:53:17', '2026-01-23 14:29:10', '2026-02-22 00:53:17', 1),
(4, 9, 'bcb785c1407b243b2dc0b7074f76a6a5e9415c05caa10118018e926c2ce14464', '::1', NULL, NULL, '2026-01-23 00:53:27', '2026-01-23 14:29:10', '2026-02-22 00:53:27', 0),
(5, 10, '02b05b993a12df0bf0299c68fb83a703b14e4e75403424718e7b06d42a821995', '::1', NULL, NULL, '2026-01-23 01:25:38', '2026-01-23 14:29:10', '2026-02-22 01:25:38', 0),
(6, 2, 'f8339e9fc71827e21fb0a7b7d3ded9fe994c9a9a37391b4881e7f033dc185343', '::1', NULL, NULL, '2026-01-28 00:22:57', '2026-01-28 00:22:57', '2026-02-27 00:22:57', 0),
(7, 11, '61db8fd5d7bab77536152c1cba06a11aefff2da9cb3030043d8dbb5cdf4c0601', '::1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', NULL, '2026-01-28 12:07:55', '2026-01-28 12:07:55', '2026-02-27 12:07:55', 0),
(8, 12, '6a75e865254f930a23d9905b73a5400e6989c62df601531503f4dd1dffa69de0', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', NULL, '2026-01-28 14:16:10', '2026-01-28 14:16:10', '2026-02-27 14:16:10', 0),
(9, 13, '0b1182cbc175f8979c64880658dbe3084dc58a9634519aa4423e166f85892b88', '::1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', NULL, '2026-01-28 20:21:59', '2026-01-28 20:21:59', '2026-02-27 20:21:59', 1),
(10, 14, 'ad25035aaeee76575a7eca9fd2b9cb67e2e6d4c083d30e5e33aed23f48fa089a', '::1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', NULL, '2026-01-29 01:33:52', '2026-01-29 01:33:52', '2026-01-31 01:33:52', 0),
(11, 17, 'a62695924ed281b207b9518df8d12327c191646da78d29b4ee9d0304a3bc427e', '::1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', NULL, '2026-01-29 12:26:54', '2026-01-29 12:26:54', '2026-01-31 12:26:54', 0),
(12, 18, 'e48d8fcabcc2fca49266169145fc59f24c1d3aa7acc430ba10e8fdecab45cf34', '::1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', NULL, '2026-01-29 12:29:50', '2026-01-29 12:29:50', '2026-01-31 12:29:50', 0),
(13, 19, '70c2a18dc4e7baa5933b03244e2e1c5008d92ba2e0b84ac73e8e7a252d503784', '::1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', NULL, '2026-01-29 12:43:23', '2026-01-29 12:43:23', '2026-01-31 12:43:23', 0),
(14, 2, 'e2d8f25ea1a558e1f6f5afed502f7224b290b062b21fc366e92a776875265e38', '::1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', NULL, '2026-01-29 12:45:18', '2026-01-29 12:45:18', '2026-01-31 12:45:18', 0),
(15, 20, 'a83921363ed71658ccbc7392f6e739ca12b7cf3093c25b2a9b555533f0b9785f', '::1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', NULL, '2026-01-29 12:53:38', '2026-01-29 12:53:38', '2026-01-31 12:53:38', 0),
(16, 25, '64e389c56e882e351f4199f860f0149070db16653a8794acfa95aa1645734f89', '::1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', NULL, '2026-01-29 21:53:19', '2026-01-29 21:53:19', '2026-01-31 21:53:19', 0),
(17, 29, '19d95da051816df14ad6254dbe0c936a6201245166000f3cd3dd1388633c9191', '::1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', NULL, '2026-01-29 22:27:45', '2026-01-29 22:27:45', '2026-01-31 22:27:45', 0),
(18, 2, '72a0160baf1eed662dfb1b50f79e30573ae1ea400e3ef467d7b76914a43e3d5a', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', NULL, '2026-01-29 23:43:59', '2026-01-29 23:43:59', '2026-01-31 23:43:59', 0),
(19, 2, 'c9ab7ca26a27ef02cb9126de7f2c7725502215cf5a22b636f4a699df9508643a', '::1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', NULL, '2026-01-30 00:58:37', '2026-01-30 00:58:37', '2026-02-01 00:58:37', 0),
(20, 2, 'd781094194c1b622223aecc6e30647837b951d008fe5d8d8737c6b871e331c36', '::1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', NULL, '2026-01-30 11:08:41', '2026-01-30 11:08:41', '2026-02-01 11:08:41', 0),
(21, 30, 'ae226c4a6c190ee9f2bc5053012dac9123434036b1f2dcee1b8e238bb134a356', '::1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', NULL, '2026-01-30 11:10:16', '2026-01-30 11:10:16', '2026-02-01 11:10:16', 0),
(22, 2, 'ef37424988125f807c2d2d8fdb0777f23ca072f0f7a75f284b94bbb25f527965', '::1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', NULL, '2026-01-30 11:11:19', '2026-01-30 11:11:19', '2026-02-01 11:11:19', 0),
(23, 2, 'f101c30cda199c18bc33e1c81740c5edf2bb2b3eb37777e0ef256e31910a3c05', '::1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', NULL, '2026-01-30 12:39:39', '2026-01-30 12:39:39', '2026-02-01 12:39:39', 0),
(24, 2, 'b7b266e0e69e082c65ee3d65029d8e31f87d0fb1fa65ba15c0f8f04d2c6b5ef5', '::1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', NULL, '2026-01-30 23:49:38', '2026-01-30 23:49:38', '2026-02-01 23:49:38', 0),
(25, 2, '49feeb84ea6c26b071a9c6c4e1c4ab939eebbee045dde985715dbdc68f239ff2', '::1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', NULL, '2026-01-31 00:44:20', '2026-01-31 00:44:20', '2026-02-02 00:44:20', 0),
(26, 30, 'ec9b55771a7301b160556b2e0de1e738487ddb2f060d7d6ebf4a6e11cfa687c0', '::1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', NULL, '2026-01-31 00:53:12', '2026-01-31 00:53:12', '2026-02-02 00:53:12', 0),
(27, 2, '8a223dd85a262f4e5a567b90a59d0b7fe7454ae1dbf4d52aa463972ec0a43a1f', '::1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', NULL, '2026-02-02 00:26:28', '2026-02-02 00:26:28', '2026-02-04 00:26:28', 0),
(28, 30, 'ea203c74f3d2f41b6ac671627b2a42e303faca182d0348f4b663da8810d80c64', '::1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', NULL, '2026-02-02 00:38:23', '2026-02-02 00:38:23', '2026-02-04 00:38:23', 0),
(29, 2, 'a58d922bb054c886b277c23b03e925b2cd6894b5bea0f58e2d4b059de103cb6e', '::1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', NULL, '2026-02-02 01:57:05', '2026-02-02 01:57:05', '2026-02-04 01:57:05', 0),
(30, 30, 'fccc329a1a3c43cf1b791bfa52f2499ebe1cc8d88f4987325f0d7418600b122f', '::1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', NULL, '2026-02-02 13:52:55', '2026-02-02 13:52:55', '2026-02-04 13:52:55', 0),
(31, 2, 'd3543144e411139e1a0f234ba97a4813fae187ed1091cb188a199676bc22d08a', '::1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', NULL, '2026-02-02 15:26:17', '2026-02-02 15:26:17', '2026-02-04 15:26:17', 0),
(32, 30, '6377a149b0dc888420b64cbba77431b1e2156274c1dc60ae50d751d63a464931', '::1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', NULL, '2026-02-02 22:33:37', '2026-02-02 22:33:37', '2026-02-04 22:33:37', 0),
(33, 2, '907df512706aa3e6b27c5559679463535931137e58b9de4cb3d4f63b081268d2', '::1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', NULL, '2026-02-03 00:18:06', '2026-02-03 00:18:06', '2026-02-05 00:18:06', 0),
(34, 25, '73d31932e522d8fa5dbfcc54a23cbd419d96b2a3783f187c15d965e3be9547f4', '::1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', NULL, '2026-02-03 00:19:20', '2026-02-03 00:19:20', '2026-02-05 00:19:20', 0),
(35, 2, '090a5f1fc9f29748cb1c2aa9385a8671c640b17fdd182ced8a83746dd51b38f6', '::1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', NULL, '2026-02-03 00:20:26', '2026-02-03 00:20:26', '2026-02-05 00:20:26', 0),
(36, 30, '03ac0e58f64b6fd51c0ca33218df6f1e50bfc4763b563f4bc4b9230c12c2d429', '::1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', NULL, '2026-02-03 00:25:49', '2026-02-03 00:25:49', '2026-02-05 00:25:49', 0),
(37, 2, 'af29995ee5d00eeb59e0e7a6237d3cddaab216f96b50fa78fca51de0ba07ef94', '::1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', NULL, '2026-02-03 00:36:33', '2026-02-03 00:36:33', '2026-02-05 00:36:33', 0),
(38, 2, '0dbbbe09b5dc07188cd633e6450207c90a689e6285166ecf3a7c42772f8937df', '::1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', NULL, '2026-02-03 00:50:33', '2026-02-04 13:24:25', '2026-02-05 00:50:33', 0),
(39, 2, '7e2fa79d59159eac20add39a56ffed1dbd5176d4541d21e066b5a139259a9cf0', '::1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', NULL, '2026-02-05 12:53:18', '2026-02-05 12:53:18', '2026-02-07 12:53:18', 0),
(40, 31, '7ab4fdfc809e743ca15e34ed0d852bd499d334f6fcb91caf6fcf9260c2557ee7', '::1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', NULL, '2026-02-05 12:56:18', '2026-02-05 12:56:18', '2026-02-07 12:56:18', 0),
(41, 32, 'd62b942d5f62d9bb9499b5c06fb92fe35d6644af51cf2138ee78b385e142ba51', '::1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', NULL, '2026-02-07 00:06:56', '2026-02-07 00:06:56', '2026-02-09 00:06:56', 1),
(42, 32, '194ade2690411d96a327034676b44b240f818e811dc953caa9e7ecb6a425c8dd', '::1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', NULL, '2026-02-07 00:07:01', '2026-02-07 00:07:01', '2026-02-09 00:07:01', 1),
(43, 32, '1a39c902261622cc1b9008d5c7e2a9e372ea957322337b62d4454e4eed17bcd9', '::1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', NULL, '2026-02-07 00:07:02', '2026-02-07 00:07:02', '2026-02-09 00:07:02', 1),
(44, 32, 'cbcdc0d0ba608d951e0aaa388f5da89e494969d0a6ee00540b7082637a0b9d35', '::1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', NULL, '2026-02-07 00:07:02', '2026-02-07 00:07:02', '2026-02-09 00:07:02', 1),
(45, 32, '78fbea6984a177aaea2fe9ba3718764fdaf4a02119b374bf75fa65d43457db52', '::1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', NULL, '2026-02-07 00:07:02', '2026-02-07 00:07:02', '2026-02-09 00:07:02', 1),
(46, 32, '5c46e6552724d65fac40444d490d0e3daa515dc2825815fcc72080855accb77c', '::1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', NULL, '2026-02-07 00:07:03', '2026-02-07 00:07:03', '2026-02-09 00:07:03', 1),
(47, 25, '2e630d54c7222649e14cf17ce3925e852272546dbac0208258ea120efacad5e8', '::1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', NULL, '2026-02-07 00:07:17', '2026-02-07 00:07:17', '2026-02-09 00:07:17', 1),
(48, 25, 'de7c6943965316ddef0fcce96843bcb7330ca22c48560c8e614125e2787f3f5c', '::1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', NULL, '2026-02-07 00:07:18', '2026-02-07 00:07:18', '2026-02-09 00:07:18', 1),
(49, 25, '7d05b1eac19d634f31c9452045c17274691b6ab248a62086fabb5a5ca248c0e5', '::1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', NULL, '2026-02-07 00:07:18', '2026-02-07 00:07:18', '2026-02-09 00:07:18', 1),
(50, 25, '3b262a901efc6ae7e3a452b03ef8b8d25d285f7c13b7a2d392360dfef1bd663b', '::1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', NULL, '2026-02-07 00:07:21', '2026-02-07 00:07:21', '2026-02-09 00:07:21', 1),
(51, 25, '7410f2de72bae62be0087e57036455b8caed927c84e0fd0e9cb08189e7172d68', '::1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', NULL, '2026-02-07 00:07:21', '2026-02-07 00:07:21', '2026-02-09 00:07:21', 0),
(52, 25, '21ab484b6ae9242de4b624f5bf04d56e6047cc5414595b6bea2fc1b820ef4154', '::1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', NULL, '2026-02-07 00:07:56', '2026-02-07 00:07:56', '2026-02-09 00:07:56', 1),
(53, 25, '815ac02e4103d8a73c0cddfb8a661667ba26a8aca1921c8524491fdf051fdb80', '::1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', NULL, '2026-02-07 00:08:00', '2026-02-07 00:08:00', '2026-02-09 00:08:00', 1),
(54, 25, '67ebb4d41899a5aec118e91e33f6dd0422103105391b5a13292fa9634a63541b', '::1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', NULL, '2026-02-07 00:08:00', '2026-02-07 00:08:00', '2026-02-09 00:08:00', 1),
(55, 25, '55057de204ebb628bde1a464bd6e63946abc55cfb7ff55c1fdd300ab0591455f', '::1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', NULL, '2026-02-07 00:08:01', '2026-02-07 00:08:01', '2026-02-09 00:08:01', 1),
(56, 25, '02a09e4b8370bfc4e403b6b56d367170fc924cf5f178f17b1e3655021a3a528c', '::1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', NULL, '2026-02-07 00:08:01', '2026-02-07 00:08:01', '2026-02-09 00:08:01', 1),
(57, 25, '7d69f3fd1b1207cb548d1f9887c59525d6537cd94b97959d23b409412fee3965', '::1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', NULL, '2026-02-07 00:08:01', '2026-02-07 00:08:01', '2026-02-09 00:08:01', 1),
(58, 25, '0816eb83d3194e432bcf0c92e600dc3ef3fa0ae169eefe95f803aab22fef874c', '::1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', NULL, '2026-02-07 00:08:01', '2026-02-07 00:08:01', '2026-02-09 00:08:01', 1),
(59, 25, '9aca2eeaa7a04c0420404aa01273322fee5cc9d0ab254b28b428f47908e772eb', '::1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', NULL, '2026-02-07 00:08:02', '2026-02-07 00:08:02', '2026-02-09 00:08:02', 1),
(60, 25, '3ac0fea0199f4c6017da700b6f74424aa0ff054b84e3a78f67cb1b54e3e203de', '::1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', NULL, '2026-02-07 00:08:02', '2026-02-07 00:08:02', '2026-02-09 00:08:02', 1),
(61, 25, 'a783af61232c2c1e80ed8933920ac34525ab0393f320c086bd7b6722127ba164', '::1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', NULL, '2026-02-07 00:08:02', '2026-02-07 00:08:02', '2026-02-09 00:08:02', 1),
(62, 25, '4aa5838aa87c87b653004300ad2172bec3024b4718a2d765c1bf352952ac88c9', '::1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', NULL, '2026-02-07 00:08:02', '2026-02-07 00:08:02', '2026-02-09 00:08:02', 1),
(63, 25, '42c138bce9b38d3121e63288544c9c08ff949b9e148475d5d9e426cc5280c93e', '::1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', NULL, '2026-02-07 00:08:03', '2026-02-07 00:08:03', '2026-02-09 00:08:03', 0),
(64, 32, '76a0bee64dfee6a0534a65888231ccb9036f8e24bd47b0615a2e0cb8eeacdf4c', '::1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', NULL, '2026-02-07 00:08:39', '2026-02-07 00:08:39', '2026-02-09 00:08:39', 1),
(65, 32, '3b29519626a67fe74181f9c3a9dd265e152a66bd4a707c498a16cc40feab865f', '::1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', NULL, '2026-02-07 00:08:41', '2026-02-07 00:08:41', '2026-02-09 00:08:41', 0),
(66, 2, '0ad9f08d23153fd24c76987c274a5a8976a1ce48d04b858f02a2d2928d765f9d', '::1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', NULL, '2026-02-07 00:09:23', '2026-02-07 00:09:23', '2026-02-09 00:09:23', 1),
(67, 2, '599835fce3f5cb57099ede7cb5a761569d38467268cd46758e9570a16a4b6f72', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', NULL, '2026-02-07 00:09:29', '2026-02-07 00:09:29', '2026-03-09 00:09:29', 1),
(68, 2, '16db9cef31bc7fd27886ff458ec41e400bbd25bcb7312f05ef93ca1a297f11d4', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', NULL, '2026-02-07 00:09:30', '2026-02-07 00:09:30', '2026-03-09 00:09:30', 0),
(69, 2, '00547b1b174bcb300c73baf6eacffc28fd6bf30b53cd45b3e0ec9eda6fe7669c', '::1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', NULL, '2026-02-07 00:10:15', '2026-02-07 00:10:15', '2026-02-09 00:10:15', 1),
(70, 2, 'f940f03ad2edb58b1ac841463c7605053bfad0f6b553a9a0240b27369af9c654', '::1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', NULL, '2026-02-07 00:10:28', '2026-02-07 00:10:28', '2026-02-09 00:10:28', 1),
(71, 2, '3af8e4435664dc22d9c35a20997043a732a871f9591149e83e64bc58794f7ae8', '::1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', NULL, '2026-02-07 00:10:54', '2026-02-07 00:10:54', '2026-02-09 00:10:54', 1),
(72, 2, 'f25924e243aa764c376c84caa9319623d58705d50d248174858ec68bb9569dcd', '::1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', NULL, '2026-02-07 00:10:55', '2026-02-07 00:10:55', '2026-02-09 00:10:55', 1),
(73, 2, 'e3a252c4adc6c6032f122004d72ddb49217cfc9131b18c06cb6d5731f6bb607c', '::1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', NULL, '2026-02-07 00:16:11', '2026-02-07 00:16:11', '2026-02-09 00:16:11', 0),
(74, 2, '1f7770203e9802c225d49bd4125100d9eb2a4a740f3a2a18f376581cf5784539', '::1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', NULL, '2026-02-07 13:30:00', '2026-02-07 13:30:00', '2026-02-09 13:30:00', 1),
(75, 33, '8e4fb7f973b81962bd0a7b27bc43d7251e745241a2c81562dfc632c4fc1f8696', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', NULL, '2026-02-07 13:31:58', '2026-02-07 13:31:58', '2026-02-09 13:31:58', 0),
(76, 2, '064ee9a4d1b3a21fc2c722b6a43bf9e08ef027beb8e13308cf20c63317ce1a7e', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', NULL, '2026-02-07 13:46:06', '2026-02-07 13:46:06', '2026-02-09 13:46:06', 0),
(77, 2, 'd1a5f111916d8e8fffb43c34f513194806d147e5d5b93a434025925fa9de8fca', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', NULL, '2026-02-07 13:46:43', '2026-02-07 13:46:43', '2026-02-09 13:46:43', 0),
(78, 25, '53db68a56e7cb239c56aab7be291286189053e39726c6fbaf9e18a63ce4075dd', '::1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', NULL, '2026-02-07 13:47:45', '2026-02-07 13:47:45', '2026-02-09 13:47:45', 0),
(79, 2, '54ad2a39f54096bd899dae30f8805b2eaad093ee5fce64ea74eee1dacf0610f9', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', NULL, '2026-02-07 13:48:21', '2026-02-07 13:48:21', '2026-02-09 13:48:21', 0),
(80, 34, '6b0037ef9c57ebb1ac75ff2f0b436f1a1ebc793383866b29c1f517049b53ef25', '::1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', NULL, '2026-02-07 14:00:29', '2026-02-07 14:00:29', '2026-02-09 14:00:29', 0),
(81, 34, '0f12ab1ede646dd7f39951ab2a1308d0de5251acc69e89b3457d6df7c719366a', '::1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', NULL, '2026-02-07 14:02:46', '2026-02-07 14:02:46', '2026-02-09 14:02:46', 0),
(82, 34, '391b9d4fa3d9adda4a84458caa32dde272338f9232844bbd5c03f101c31ac18f', '::1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', NULL, '2026-02-07 14:09:02', '2026-02-07 14:09:02', '2026-02-09 14:09:02', 0),
(83, 34, '56597b1c6c4b995e01f39f0bf8e2a6464dc85fdb772718b8d341a4599f53e120', '::1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', NULL, '2026-02-07 17:41:38', '2026-02-07 17:41:38', '2026-02-09 17:41:38', 0),
(84, 34, 'a237c98b499b61a285b1ac0ec0c071ba225b9896135661412c083ddad39a4f78', '::1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', NULL, '2026-02-07 21:19:21', '2026-02-07 21:19:21', '2026-02-09 21:19:21', 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `comment_likes`
--
ALTER TABLE `comment_likes`
  ADD PRIMARY KEY (`comment_id`,`user_id`),
  ADD KEY `idx_comment_likes_user` (`user_id`);

--
-- Indexes for table `failed_login_attempts`
--
ALTER TABLE `failed_login_attempts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_identifier` (`identifier`),
  ADD KEY `idx_ip_address` (`ip_address`),
  ADD KEY `idx_attempt_time` (`attempt_time`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_token_hash` (`token_hash`),
  ADD KEY `idx_expires_at` (`expires_at`);

--
-- Indexes for table `posts`
--
ALTER TABLE `posts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_posts_created` (`created_at`),
  ADD KEY `idx_posts_user` (`user_id`),
  ADD KEY `idx_user_created` (`user_id`,`created_at`);

--
-- Indexes for table `post_comments`
--
ALTER TABLE `post_comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_comments_post` (`post_id`),
  ADD KEY `idx_comments_created` (`created_at`),
  ADD KEY `fk_comments_user` (`user_id`),
  ADD KEY `idx_post_created` (`post_id`,`created_at`);

--
-- Indexes for table `post_likes`
--
ALTER TABLE `post_likes`
  ADD PRIMARY KEY (`post_id`,`user_id`),
  ADD KEY `idx_likes_user` (`user_id`);

--
-- Indexes for table `post_media`
--
ALTER TABLE `post_media`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_media_post` (`post_id`),
  ADD KEY `idx_post_sort` (`post_id`,`sort_order`);

--
-- Indexes for table `scholars`
--
ALTER TABLE `scholars`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_scholars_user` (`user_id`),
  ADD KEY `idx_approval_status` (`approval_status`),
  ADD KEY `idx_user_id` (`user_id`);

--
-- Indexes for table `scholar_documents`
--
ALTER TABLE `scholar_documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_scholar_id` (`scholar_id`),
  ADD KEY `idx_document_type` (`document_type`);

--
-- Indexes for table `scholar_verification_links`
--
ALTER TABLE `scholar_verification_links`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_scholar_link` (`scholar_id`,`link`(255)),
  ADD KEY `idx_scholar_id` (`scholar_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_username` (`username`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `idx_last_login` (`last_login`),
  ADD KEY `idx_is_active` (`is_active`),
  ADD KEY `idx_phone` (`phone`);

--
-- Indexes for table `user_activity_log`
--
ALTER TABLE `user_activity_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_activity_type` (`activity_type`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `user_daily_checkins`
--
ALTER TABLE `user_daily_checkins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_user_date` (`user_id`,`checkin_date`);

--
-- Indexes for table `user_follows`
--
ALTER TABLE `user_follows`
  ADD PRIMARY KEY (`follower_id`,`following_id`),
  ADD KEY `idx_following` (`following_id`);

--
-- Indexes for table `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_session_token_hash` (`session_token_hash`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_expires_at` (`expires_at`),
  ADD KEY `idx_user_id_active` (`user_id`,`is_active`),
  ADD KEY `idx_last_activity` (`last_activity`),
  ADD KEY `idx_session_token_hash` (`session_token_hash`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `failed_login_attempts`
--
ALTER TABLE `failed_login_attempts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `posts`
--
ALTER TABLE `posts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `post_comments`
--
ALTER TABLE `post_comments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `post_media`
--
ALTER TABLE `post_media`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `scholars`
--
ALTER TABLE `scholars`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `scholar_documents`
--
ALTER TABLE `scholar_documents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `scholar_verification_links`
--
ALTER TABLE `scholar_verification_links`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `user_activity_log`
--
ALTER TABLE `user_activity_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_daily_checkins`
--
ALTER TABLE `user_daily_checkins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `user_sessions`
--
ALTER TABLE `user_sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=85;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `comment_likes`
--
ALTER TABLE `comment_likes`
  ADD CONSTRAINT `fk_comment_likes_comment` FOREIGN KEY (`comment_id`) REFERENCES `post_comments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_comment_likes_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD CONSTRAINT `password_reset_tokens_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `posts`
--
ALTER TABLE `posts`
  ADD CONSTRAINT `fk_posts_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `post_comments`
--
ALTER TABLE `post_comments`
  ADD CONSTRAINT `fk_comments_post` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_comments_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `post_likes`
--
ALTER TABLE `post_likes`
  ADD CONSTRAINT `fk_likes_post` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_likes_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `post_media`
--
ALTER TABLE `post_media`
  ADD CONSTRAINT `fk_media_post` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `scholars`
--
ALTER TABLE `scholars`
  ADD CONSTRAINT `scholars_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `scholar_documents`
--
ALTER TABLE `scholar_documents`
  ADD CONSTRAINT `scholar_documents_ibfk_1` FOREIGN KEY (`scholar_id`) REFERENCES `scholars` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `scholar_verification_links`
--
ALTER TABLE `scholar_verification_links`
  ADD CONSTRAINT `scholar_verification_links_ibfk_1` FOREIGN KEY (`scholar_id`) REFERENCES `scholars` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_activity_log`
--
ALTER TABLE `user_activity_log`
  ADD CONSTRAINT `user_activity_log_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `user_daily_checkins`
--
ALTER TABLE `user_daily_checkins`
  ADD CONSTRAINT `fk_checkin_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_follows`
--
ALTER TABLE `user_follows`
  ADD CONSTRAINT `fk_follower` FOREIGN KEY (`follower_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_following` FOREIGN KEY (`following_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD CONSTRAINT `user_sessions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

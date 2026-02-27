-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 15, 2025 at 07:29 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `university_transport`
--

-- --------------------------------------------------------

--
-- Table structure for table `buses`
--

CREATE TABLE `buses` (
  `id` int(11) NOT NULL,
  `reg_number` varchar(20) NOT NULL,
  `seats` int(11) NOT NULL,
  `is_female_only` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `buses`
--

INSERT INTO `buses` (`id`, `reg_number`, `seats`, `is_female_only`, `created_at`) VALUES
(10, 'Bus-1', 25, 0, '2025-12-14 07:27:38'),
(11, 'Bus-2', 25, 0, '2025-12-14 07:27:46'),
(12, 'Bus-3', 25, 0, '2025-12-14 07:27:57'),
(13, 'Bus-4', 25, 0, '2025-12-14 07:28:06'),
(14, 'Bus-5', 25, 1, '2025-12-14 07:28:12'),
(15, 'Bus-6', 25, 1, '2025-12-14 07:28:20');

-- --------------------------------------------------------

--
-- Table structure for table `bus_assignments`
--

CREATE TABLE `bus_assignments` (
  `id` int(11) NOT NULL,
  `bus_id` int(11) NOT NULL,
  `destination_id` int(11) NOT NULL,
  `time_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bus_assignments`
--

INSERT INTO `bus_assignments` (`id`, `bus_id`, `destination_id`, `time_id`) VALUES
(12, 10, 6, 8),
(13, 10, 7, 9),
(14, 10, 8, 10),
(15, 10, 9, 11),
(16, 10, 10, 12),
(17, 11, 11, 13),
(18, 11, 12, 14),
(19, 11, 13, 15),
(20, 11, 14, 16),
(21, 11, 15, 17),
(22, 11, 16, 18),
(23, 11, 17, 19),
(24, 12, 18, 20),
(25, 12, 19, 21),
(26, 12, 20, 22),
(27, 12, 21, 23),
(28, 12, 22, 24),
(29, 12, 23, 25),
(30, 13, 24, 26),
(31, 13, 25, 27),
(32, 13, 26, 28),
(33, 13, 27, 29),
(34, 14, 28, 30),
(35, 14, 29, 31),
(36, 14, 30, 32),
(37, 14, 31, 33),
(38, 14, 32, 34),
(39, 15, 33, 35),
(40, 15, 34, 36),
(41, 15, 35, 37),
(42, 15, 36, 38),
(43, 15, 37, 39),
(44, 15, 38, 40);

-- --------------------------------------------------------

--
-- Table structure for table `bus_times`
--

CREATE TABLE `bus_times` (
  `id` int(11) NOT NULL,
  `destination_id` int(11) NOT NULL,
  `time` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bus_times`
--

INSERT INTO `bus_times` (`id`, `destination_id`, `time`) VALUES
(6, 6, '07:40:00'),
(7, 6, '14:20:00'),
(8, 6, '10:00:00'),
(9, 7, '10:00:00'),
(10, 8, '10:00:00'),
(11, 9, '10:00:00'),
(12, 10, '10:00:00'),
(13, 11, '10:00:00'),
(14, 12, '10:00:00'),
(15, 13, '10:00:00'),
(16, 14, '10:00:00'),
(17, 15, '10:00:00'),
(18, 16, '10:00:00'),
(19, 17, '10:00:00'),
(20, 18, '10:00:00'),
(21, 19, '10:00:00'),
(22, 20, '10:00:00'),
(23, 21, '10:00:00'),
(24, 22, '10:00:00'),
(25, 23, '10:00:00'),
(26, 24, '10:00:00'),
(27, 25, '10:00:00'),
(28, 26, '10:00:00'),
(29, 27, '10:00:00'),
(30, 28, '10:00:00'),
(31, 29, '10:00:00'),
(32, 30, '10:00:00'),
(33, 31, '10:00:00'),
(34, 32, '10:00:00'),
(35, 33, '10:00:00'),
(36, 34, '10:00:00'),
(37, 35, '10:00:00'),
(38, 36, '10:00:00'),
(39, 37, '10:00:00'),
(40, 38, '10:00:00'),
(71, 6, '14:40:00'),
(72, 7, '14:40:00'),
(73, 8, '14:40:00'),
(74, 9, '14:40:00'),
(75, 10, '14:40:00'),
(76, 11, '14:40:00'),
(77, 12, '14:40:00'),
(78, 13, '14:40:00'),
(79, 14, '14:40:00'),
(80, 15, '14:40:00'),
(81, 16, '14:40:00'),
(82, 17, '14:40:00'),
(83, 18, '14:40:00'),
(84, 19, '14:40:00'),
(85, 20, '14:40:00'),
(86, 21, '14:40:00'),
(87, 22, '14:40:00'),
(88, 23, '14:40:00'),
(89, 24, '14:40:00'),
(90, 25, '14:40:00'),
(91, 26, '14:40:00'),
(92, 27, '14:40:00'),
(93, 28, '14:40:00'),
(94, 29, '14:40:00'),
(95, 30, '14:40:00'),
(96, 31, '14:40:00'),
(97, 32, '14:40:00'),
(98, 33, '14:40:00'),
(99, 34, '14:40:00'),
(100, 35, '14:40:00'),
(101, 36, '14:40:00'),
(102, 37, '14:40:00'),
(103, 38, '14:40:00'),
(134, 6, '18:30:00'),
(135, 7, '18:30:00'),
(136, 8, '18:30:00'),
(137, 9, '18:30:00'),
(138, 10, '18:30:00'),
(139, 11, '18:30:00'),
(140, 12, '18:30:00'),
(141, 13, '18:30:00'),
(142, 14, '18:30:00'),
(143, 15, '18:30:00'),
(144, 16, '18:30:00'),
(145, 17, '18:30:00'),
(146, 18, '18:30:00'),
(147, 19, '18:30:00'),
(148, 20, '18:30:00'),
(149, 21, '18:30:00'),
(150, 22, '18:30:00'),
(151, 23, '18:30:00'),
(152, 24, '18:30:00'),
(153, 25, '18:30:00'),
(154, 26, '18:30:00'),
(155, 27, '18:30:00'),
(156, 28, '18:30:00'),
(157, 29, '18:30:00'),
(158, 30, '18:30:00'),
(159, 31, '18:30:00'),
(160, 32, '18:30:00'),
(161, 33, '18:30:00'),
(162, 34, '18:30:00'),
(163, 35, '18:30:00'),
(164, 36, '18:30:00'),
(165, 37, '18:30:00'),
(166, 38, '18:30:00'),
(197, 6, '22:20:00'),
(198, 7, '22:20:00'),
(199, 8, '22:20:00'),
(200, 9, '22:20:00'),
(201, 10, '22:20:00'),
(202, 11, '22:20:00'),
(203, 12, '22:20:00'),
(204, 13, '22:20:00'),
(205, 14, '22:20:00'),
(206, 15, '22:20:00'),
(207, 16, '22:20:00'),
(208, 17, '22:20:00'),
(209, 18, '22:20:00'),
(210, 19, '22:20:00'),
(211, 20, '22:20:00'),
(212, 21, '22:20:00'),
(213, 22, '22:20:00'),
(214, 23, '22:20:00'),
(215, 24, '22:20:00'),
(216, 25, '22:20:00'),
(217, 26, '22:20:00'),
(218, 27, '22:20:00'),
(219, 28, '22:20:00'),
(220, 29, '22:20:00'),
(221, 30, '22:20:00'),
(222, 31, '22:20:00'),
(223, 32, '22:20:00'),
(224, 33, '22:20:00'),
(225, 34, '22:20:00'),
(226, 35, '22:20:00'),
(227, 36, '22:20:00'),
(228, 37, '22:20:00'),
(229, 38, '22:20:00'),
(260, 39, '07:40:00'),
(261, 40, '07:40:00'),
(262, 41, '07:40:00'),
(263, 42, '07:40:00'),
(264, 43, '07:40:00'),
(265, 44, '07:40:00'),
(266, 45, '07:40:00'),
(267, 46, '07:40:00'),
(268, 47, '07:40:00'),
(269, 48, '07:40:00'),
(270, 49, '07:40:00'),
(271, 50, '07:40:00'),
(272, 51, '07:40:00'),
(273, 52, '07:40:00'),
(274, 53, '07:40:00'),
(275, 54, '07:40:00'),
(276, 55, '07:40:00'),
(277, 56, '07:40:00'),
(278, 57, '07:40:00'),
(279, 58, '07:40:00'),
(280, 59, '07:40:00'),
(281, 60, '07:40:00'),
(282, 61, '07:40:00'),
(283, 62, '07:40:00'),
(284, 63, '07:40:00'),
(285, 64, '07:40:00'),
(286, 65, '07:40:00'),
(287, 66, '07:40:00'),
(288, 67, '07:40:00'),
(289, 68, '07:40:00'),
(290, 69, '07:40:00'),
(291, 70, '07:40:00'),
(292, 71, '07:40:00'),
(323, 39, '14:20:00'),
(324, 40, '14:20:00'),
(325, 41, '14:20:00'),
(326, 42, '14:20:00'),
(327, 43, '14:20:00'),
(328, 44, '14:20:00'),
(329, 45, '14:20:00'),
(330, 46, '14:20:00'),
(331, 47, '14:20:00'),
(332, 48, '14:20:00'),
(333, 49, '14:20:00'),
(334, 50, '14:20:00'),
(335, 51, '14:20:00'),
(336, 52, '14:20:00'),
(337, 53, '14:20:00'),
(338, 54, '14:20:00'),
(339, 55, '14:20:00'),
(340, 56, '14:20:00'),
(341, 57, '14:20:00'),
(342, 58, '14:20:00'),
(343, 59, '14:20:00'),
(344, 60, '14:20:00'),
(345, 61, '14:20:00'),
(346, 62, '14:20:00'),
(347, 63, '14:20:00'),
(348, 64, '14:20:00'),
(349, 65, '14:20:00'),
(350, 66, '14:20:00'),
(351, 67, '14:20:00'),
(352, 68, '14:20:00'),
(353, 69, '14:20:00'),
(354, 70, '14:20:00'),
(355, 71, '14:20:00'),
(386, 39, '17:45:00'),
(387, 40, '17:45:00'),
(388, 41, '17:45:00'),
(389, 42, '17:45:00'),
(390, 43, '17:45:00'),
(391, 44, '17:45:00'),
(392, 45, '17:45:00'),
(393, 46, '17:45:00'),
(394, 47, '17:45:00'),
(395, 48, '17:45:00'),
(396, 49, '17:45:00'),
(397, 50, '17:45:00'),
(398, 51, '17:45:00'),
(399, 52, '17:45:00'),
(400, 53, '17:45:00'),
(401, 54, '17:45:00'),
(402, 55, '17:45:00'),
(403, 56, '17:45:00'),
(404, 57, '17:45:00'),
(405, 58, '17:45:00'),
(406, 59, '17:45:00'),
(407, 60, '17:45:00'),
(408, 61, '17:45:00'),
(409, 62, '17:45:00'),
(410, 63, '17:45:00'),
(411, 64, '17:45:00'),
(412, 65, '17:45:00'),
(413, 66, '17:45:00'),
(414, 67, '17:45:00'),
(415, 68, '17:45:00'),
(416, 69, '17:45:00'),
(417, 70, '17:45:00'),
(418, 71, '17:45:00'),
(449, 39, '18:45:00'),
(450, 40, '18:45:00'),
(451, 41, '18:45:00'),
(452, 42, '18:45:00'),
(453, 43, '18:45:00'),
(454, 44, '18:45:00'),
(455, 45, '18:45:00'),
(456, 46, '18:45:00'),
(457, 47, '18:45:00'),
(458, 48, '18:45:00'),
(459, 49, '18:45:00'),
(460, 50, '18:45:00'),
(461, 51, '18:45:00'),
(462, 52, '18:45:00'),
(463, 53, '18:45:00'),
(464, 54, '18:45:00'),
(465, 55, '18:45:00'),
(466, 56, '18:45:00'),
(467, 57, '18:45:00'),
(468, 58, '18:45:00'),
(469, 59, '18:45:00'),
(470, 60, '18:45:00'),
(471, 61, '18:45:00'),
(472, 62, '18:45:00'),
(473, 63, '18:45:00'),
(474, 64, '18:45:00'),
(475, 65, '18:45:00'),
(476, 66, '18:45:00'),
(477, 67, '18:45:00'),
(478, 68, '18:45:00'),
(479, 69, '18:45:00'),
(480, 70, '18:45:00'),
(481, 71, '18:45:00');

-- --------------------------------------------------------

--
-- Table structure for table `destinations`
--

CREATE TABLE `destinations` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `distance` decimal(5,2) NOT NULL,
  `fare` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `start_map_coords` varchar(50) NOT NULL,
  `start_destination` varchar(100) NOT NULL DEFAULT '',
  `end_destination` varchar(100) NOT NULL DEFAULT '',
  `end_map_coords` varchar(50) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `destinations`
--

INSERT INTO `destinations` (`id`, `name`, `distance`, `fare`, `created_at`, `start_map_coords`, `start_destination`, `end_destination`, `end_map_coords`) VALUES
(6, 'NSU-Abdullahpur (Polwel Market)', 9.80, 100.00, '2025-12-14 06:07:08', '23.8151,90.4230', 'NSU', 'Abdullahpur (Polwel Market)', '23.8798,90.4011'),
(7, 'NSU-Uttara House Building (Janata Bank)', 9.30, 100.00, '2025-12-14 06:08:41', '23.8151,90.4230', 'NSU', 'Uttara House Building (Janata Bank)', '23.8747,90.4004'),
(8, 'NSU-Uttara Azampur (Uttara East Thana)', 8.50, 100.00, '2025-12-14 06:11:42', '23.8151,90.4230', 'NSU', 'Uttara Azampur (Uttara East Thana)', '23.8643,90.3999'),
(9, 'NSU-Uttara Jashimuddin (Foot Over Bridge RAB-1)', 8.90, 100.00, '2025-12-14 06:15:03', '23.8151,90.4230', 'NSU', 'Uttara Jashimuddin (Foot Over Bridge RAB-1)', '23.8613,90.3928'),
(10, 'NSU-Airport (Traffic Police Box)', 6.50, 100.00, '2025-12-14 06:16:44', '23.8151,90.4230', 'NSU', 'Airport (Traffic Police Box)', '23.8502,90.4084'),
(11, 'NSU-Mirpur Bangla College (Foot Over Bridge)', 17.30, 100.00, '2025-12-14 06:21:33', '23.8151,90.4230', 'NSU', 'Mirpur Bangla College (Foot Over Bridge)', '23.7917,90.3497'),
(12, 'NSU-Mirpur-1 (New Market)', 13.40, 100.00, '2025-12-14 06:23:32', '23.8151,90.4230', 'NSU', 'Mirpur-1 (New Market)', '23.7967,90.3511'),
(13, 'NSU-Mirpur-2 (National Bangla High School)', 10.90, 100.00, '2025-12-14 06:24:32', '23.8151107,90.4229817', 'NSU', 'Mirpur-2 (National Bangla High School)', '23.8067483,90.3769734'),
(14, 'NSU-Mirpur-10 (Metro Rail Station)', 11.10, 100.00, '2025-12-14 06:26:01', '23.8151,90.4230', 'NSU', 'Mirpur-10 (Metro Rail Station)', '23.8077,90.3658'),
(15, 'NSU-Mirpur-11 (Metro Rail Station)', 11.10, 100.00, '2025-12-14 06:27:19', '23.8151107,90.4229817', 'NSU', 'Mirpur-11 (Metro Rail Station)', '23.8183388,90.3769734'),
(16, 'NSU-Mirpur-12 (CNG Station/ Mirpur Ceramic)', 10.20, 100.00, '2025-12-14 06:28:18', '23.8151107,90.4229817', 'NSU', 'Mirpur-12 (CNG Station/ Mirpur Ceramic)', '23.8225976,90.3764589'),
(17, 'NSU-ECB Square (Jatri Chhawni)', 6.30, 100.00, '2025-12-14 06:29:06', '23.8151107,90.4229817', 'NSU', 'ECB Square (Jatri Chhawni)', '23.8224267,90.4013304'),
(18, 'NSU-Mohammadpur (Japan Garden City)', 15.60, 100.00, '2025-12-14 06:30:32', '23.8151107,90.4229817', 'NSU', 'Mohammadpur (Japan Garden City)', '23.7917413,90.3525791'),
(19, 'NSU-Mohammadpur Opposite of Suchana Community Center (Probal Housing)', 15.70, 100.00, '2025-12-14 06:32:07', '23.8151107,90.4229817', 'NSU', 'Mohammadpur Opposite of Suchana Community Center (Probal Housing)', '23.7917413,90.3530616'),
(20, 'NSU-Syamoli Bus Stand (Hotel Mohammadia)', 15.30, 100.00, '2025-12-14 06:32:55', '23.8151107,90.4229817', 'NSU', 'Syamoli Bus Stand (Hotel Mohammadia)', '23.7917413,90.3564441'),
(21, 'NSU-Agargoan Metro Rail Station', 12.80, 100.00, '2025-12-14 06:34:42', '23.8151107,90.4229817', 'NSU', 'Agargoan Metro Rail Station', '23.7891108,90.3638412'),
(22, 'NSU-BAF Shaheen College', 9.80, 100.00, '2025-12-14 06:35:36', '23.8151107,90.4229817', 'NSU', 'BAF Shaheen College', '23.8007472,90.3893282'),
(23, 'NSU-Banani Rail Station', 8.70, 100.00, '2025-12-14 06:36:16', '23.8151107,90.4229817', 'NSU', 'Banani Rail Station', '23.8067483,90.3942694'),
(24, 'NSU-Jigatola Bus Stand (Japan Bangladesh Hospital)', 16.30, 100.00, '2025-12-14 06:37:06', '23.8151107,90.4229817', 'NSU', 'Jigatola Bus Stand (Japan Bangladesh Hospital)', '23.7799405,90.3610189'),
(25, 'NSU-Dhanmondi-27 (Rapa Plaza)', 14.20, 100.00, '2025-12-14 06:38:14', '23.8151107,90.4229817', 'NSU', 'Dhanmondi-27 (Rapa Plaza)', '23.7894923,90.3609005'),
(26, 'NSU-Khamarbari Mor', 12.40, 100.00, '2025-12-14 06:39:06', '23.8151107,90.4229817', 'NSU', 'Khamarbari Mor', '23.7917413,90.3655042'),
(27, 'NSU-Mohakhali Fly Over (Banani End)', 10.70, 100.00, '2025-12-14 06:40:50', '23.8151107,90.4229817', 'NSU', 'Mohakhali Fly Over (Banani End)', '23.8007472,90.3893282'),
(28, 'NSU-Azimpur (Matri Sadan Hospital)', 18.10, 100.00, '2025-12-14 06:41:56', '23.8151107,90.4229817', 'NSU', 'Azimpur (Matri Sadan Hospital)', '23.776547,90.3655859'),
(29, 'NSU-Katabon Bus Stand', 16.40, 100.00, '2025-12-14 06:42:48', '23.8151107,90.4229817', 'NSU', 'Katabon Bus Stand', '23.7389539,90.3903245'),
(30, 'NSU-Bangla Motor Pharmacy Council Office', 14.80, 100.00, '2025-12-14 06:43:44', '23.8151107,90.4229817', 'NSU', 'Bangla Motor Pharmacy Council Office', '23.7860213,90.3673715'),
(31, 'NSU-Mogbazar (NCC Bank)', 13.50, 100.00, '2025-12-14 06:44:59', '23.8151107,90.4229817', 'NSU', 'Mogbazar (NCC Bank)', '23.7871432,90.3708776'),
(32, 'NSU-Gulshan Niketon Gate-1 (Jatri Chhawni)', 15.30, 100.00, '2025-12-14 06:45:41', '23.8151107,90.4229817', 'NSU', 'Gulshan Niketon Gate-1 (Jatri Chhawni)', '23.7891108,90.3708776'),
(33, 'NSU-Notre Dame College', 16.40, 100.00, '2025-12-14 06:46:21', '23.8151107,90.4229817', 'NSU', 'Notre Dame College', '23.7755631,90.3708776'),
(34, 'NSU-Rajarbag Bus Stand', 14.60, 100.00, '2025-12-14 06:47:04', '23.8151107,90.4229817', 'NSU', 'Rajarbag Bus Stand', '23.783185,90.3708776'),
(35, 'NSU-Khilgaon Bagicha Jame Masjid', 16.10, 100.00, '2025-12-14 06:47:48', '23.8151107,90.4229817', 'NSU', 'Khilgaon Bagicha Jame Masjid', '23.7831348,90.3708776'),
(36, 'NSU-Malibagh Rail Gate (Ibne Sina Hospital)', 14.30, 100.00, '2025-12-14 06:48:27', '23.8151107,90.4229817', 'NSU', 'Malibagh Rail Gate (Ibne Sina Hospital)', '23.7860042,90.3708776'),
(37, 'NSU-Malibag Abul Hotel', 15.00, 100.00, '2025-12-14 06:49:10', '23.8151107,90.4229817', 'NSU', 'Malibag Abul Hotel', '23.7859305,90.3708776'),
(38, 'NSU-Rampura Bridge (Opposite of BTV)', 7.30, 100.00, '2025-12-14 06:49:49', '23.8151107,90.4229817', 'NSU', 'Rampura Bridge (Opposite of BTV)', '23.7859305,90.3708776'),
(39, 'Abdullahpur (Polwel Market)-NSU', 9.80, 100.00, '2025-12-14 06:51:49', '23.8798,90.4011', 'Abdullahpur (Polwel Market)', 'NSU', '23.8151,90.4230'),
(40, 'Uttara House Building (Janata Bank)-NSU', 9.30, 100.00, '2025-12-14 06:52:17', '23.8747,90.4004', 'Uttara House Building (Janata Bank)', 'NSU', '23.8151,90.4230'),
(41, 'Uttara Azampur (Uttara East Thana)-NSU', 8.50, 100.00, '2025-12-14 06:53:00', '23.8643,90.3999', 'Uttara Azampur (Uttara East Thana)', 'NSU', '23.8151,90.4230'),
(42, 'Uttara Jashimuddin (Foot Over Bridge RAB-1)-NSU', 8.90, 100.00, '2025-12-14 06:53:20', '23.8613,90.3928', 'Uttara Jashimuddin (Foot Over Bridge RAB-1)', 'NSU', '23.8151,90.4230'),
(43, 'Airport (Traffic Police Box)-NSU', 6.50, 100.00, '2025-12-14 06:53:47', '23.8502,90.4084', 'Airport (Traffic Police Box)', 'NSU', '23.8151,90.4230'),
(44, 'Mirpur Bangla College (Foot Over Bridge)-NSU', 17.30, 100.00, '2025-12-14 06:54:15', '23.7917,90.3497', 'Mirpur Bangla College (Foot Over Bridge)', 'NSU', '23.8151,90.4230'),
(45, 'Mirpur-1 (New Market)-NSU', 13.40, 100.00, '2025-12-14 06:54:37', '23.7967,90.3511', 'Mirpur-1 (New Market)', 'NSU', '23.8151,90.4230'),
(46, 'Mirpur-2 (National Bangla High School)-NSU', 10.90, 100.00, '2025-12-14 06:54:52', '23.8067,90.3770', 'Mirpur-2 (National Bangla High School)', 'NSU', '23.8151,90.4230'),
(47, 'Mirpur-10 (Metro Rail Station)-NSU', 11.10, 100.00, '2025-12-14 06:55:14', '23.8077,90.3658', 'Mirpur-10 (Metro Rail Station)', 'NSU', '23.8151,90.4230'),
(48, 'Mirpur-11 (Metro Rail Station)-NSU', 11.10, 100.00, '2025-12-14 06:55:32', '23.8183,90.3770', 'Mirpur-11 (Metro Rail Station)', 'NSU', '23.8151,90.4230'),
(49, 'Mirpur-12 (CNG Station/ Mirpur Ceramic)-NSU', 10.20, 100.00, '2025-12-14 06:55:56', '23.8226,90.3765', 'Mirpur-12 (CNG Station/ Mirpur Ceramic)', 'NSU', '23.8151,90.4230'),
(50, 'ECB Square (Jatri Chhawni)-NSU', 6.30, 100.00, '2025-12-14 06:56:17', '23.8224,90.4013', 'ECB Square (Jatri Chhawni)', 'NSU', '23.8151,90.4230'),
(51, 'Mohammadpur (Japan Garden City)-NSU', 15.60, 100.00, '2025-12-14 06:56:36', '23.7917,90.3526', 'Mohammadpur (Japan Garden City)', 'NSU', '23.8151,90.4230'),
(52, 'Mohammadpur Opposite of Suchana Community Center (Probal Housing)-NSU', 15.70, 100.00, '2025-12-14 06:56:58', '23.7917,90.3531', 'Mohammadpur Opposite of Suchana Community Center (Probal Housing)', 'NSU', '23.8151,90.4230'),
(53, 'Syamoli Bus Stand (Hotel Mohammadia)-NSU', 15.30, 100.00, '2025-12-14 06:57:15', '23.7917,90.3564', 'Syamoli Bus Stand (Hotel Mohammadia)', 'NSU', '23.8151,90.4230'),
(54, 'Agargoan Metro Rail Station-NSU', 12.80, 100.00, '2025-12-14 06:57:29', '23.7891,90.3638', 'Agargoan Metro Rail Station', 'NSU', '23.8151,90.4230'),
(55, 'BAF Shaheen College-NSU', 9.80, 100.00, '2025-12-14 06:57:47', '23.8007,90.3893', 'BAF Shaheen College', 'NSU', '23.8151,90.4230'),
(56, 'Banani Rail Station-NSU', 8.70, 100.00, '2025-12-14 06:58:04', '23.8067,90.3943', 'Banani Rail Station', 'NSU', '23.8151,90.4230'),
(57, 'Jigatola Bus Stand (Japan Bangladesh Hospital)-NSU', 16.30, 100.00, '2025-12-14 06:58:23', '23.7799,90.3610', 'Jigatola Bus Stand (Japan Bangladesh Hospital)', 'NSU', '23.8151,90.4230'),
(58, 'Dhanmondi-27 (Rapa Plaza)-NSU', 14.20, 100.00, '2025-12-14 06:58:37', '23.7895,90.3609', 'Dhanmondi-27 (Rapa Plaza)', 'NSU', '23.8151,90.4230'),
(59, 'Khamarbari Mor-NSU', 12.40, 100.00, '2025-12-14 06:58:57', '23.7917,90.3655', 'Khamarbari Mor', 'NSU', '23.8151,90.4230'),
(60, 'Mohakhali Fly Over (Banani End)-NSU', 10.70, 100.00, '2025-12-14 06:59:13', '23.8007,90.3893', 'Mohakhali Fly Over (Banani End)', 'NSU', '23.8151,90.4230'),
(61, 'Azimpur (Matri Sadan Hospital)-NSU', 18.10, 100.00, '2025-12-14 06:59:35', '23.7765,90.3656', 'Azimpur (Matri Sadan Hospital)', 'NSU', '23.8151,90.4230'),
(62, 'Katabon Bus Stand-NSU', 16.40, 100.00, '2025-12-14 06:59:55', '23.7390,90.3903', 'Katabon Bus Stand', 'NSU', '23.8151,90.4230'),
(63, 'Bangla Motor Pharmacy Council Office-NSU', 14.80, 100.00, '2025-12-14 07:00:10', '23.7860,90.3674', 'Bangla Motor Pharmacy Council Office', 'NSU', '23.8151,90.4230'),
(64, 'Mogbazar (NCC Bank)-NSU', 13.50, 100.00, '2025-12-14 07:00:29', '23.7871,90.3709', 'Mogbazar (NCC Bank)', 'NSU', '23.8151,90.4230'),
(65, 'Gulshan Niketon Gate-1 (Jatri Chhawni)-NSU', 15.30, 100.00, '2025-12-14 07:00:44', '23.7891,90.3709', 'Gulshan Niketon Gate-1 (Jatri Chhawni)', 'NSU', '23.8151,90.4230'),
(66, 'Notre Dame College-NSU', 16.40, 100.00, '2025-12-14 07:01:00', '23.7756,90.3709', 'Notre Dame College', 'NSU', '23.8151,90.4230'),
(67, 'Rajarbag Bus Stand-NSU', 14.60, 100.00, '2025-12-14 07:01:17', '23.7832,90.3709', 'Rajarbag Bus Stand', 'NSU', '23.8151,90.4230'),
(68, 'Khilgaon Bagicha Jame Masjid-NSU', 16.10, 100.00, '2025-12-14 07:01:34', '23.7831,90.3709', 'Khilgaon Bagicha Jame Masjid', 'NSU', '23.8151,90.4230'),
(69, 'Malibagh Rail Gate (Ibne Sina Hospital)-NSU', 14.30, 14.30, '2025-12-14 07:01:56', '23.7860,90.3709', 'Malibagh Rail Gate (Ibne Sina Hospital)', 'NSU', '23.8151,90.4230'),
(70, 'Malibag Abul Hotel-NSU', 15.00, 100.00, '2025-12-14 07:02:12', '23.7859,90.3709', 'Malibag Abul Hotel', 'NSU', '23.8151,90.4230'),
(71, 'Rampura Bridge (Opposite of BTV)-NSU', 7.30, 100.00, '2025-12-14 07:02:26', '23.7859,90.3709', 'Rampura Bridge (Opposite of BTV)', 'NSU', '23.8151,90.4230');

-- --------------------------------------------------------

--
-- Table structure for table `payment_options`
--

CREATE TABLE `payment_options` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payment_options`
--

INSERT INTO `payment_options` (`id`, `name`) VALUES
(1, 'online'),
(2, 'cash'),
(3, 'card');

-- --------------------------------------------------------

--
-- Table structure for table `rides`
--

CREATE TABLE `rides` (
  `id` int(11) NOT NULL,
  `driver_id` int(11) NOT NULL,
  `bus_id` int(11) NOT NULL,
  `destination_id` int(11) NOT NULL,
  `time_id` int(11) NOT NULL,
  `status` enum('pending','started','cancelled','ended') DEFAULT 'pending',
  `started_at` timestamp NULL DEFAULT NULL,
  `ended_at` timestamp NULL DEFAULT NULL,
  `trip_date` date NOT NULL,
  `last_map_coords` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rides`
--

INSERT INTO `rides` (`id`, `driver_id`, `bus_id`, `destination_id`, `time_id`, `status`, `started_at`, `ended_at`, `trip_date`, `last_map_coords`) VALUES
(14, 3, 10, 7, 9, 'ended', '2025-12-14 16:12:27', '2025-12-14 16:12:34', '2025-12-14', NULL),
(15, 3, 10, 7, 9, 'cancelled', '2025-12-14 16:13:49', NULL, '2025-12-14', NULL),
(16, 3, 10, 7, 9, 'ended', '2025-12-14 16:15:09', '2025-12-14 16:15:13', '2025-12-14', NULL),
(17, 3, 10, 7, 9, 'cancelled', '2025-12-14 16:15:23', NULL, '2025-12-14', NULL),
(18, 3, 10, 7, 9, 'ended', '2025-12-14 22:23:34', '2025-12-14 22:52:15', '2025-12-14', '23.773184,90.390528'),
(19, 7, 10, 7, 9, 'ended', '2025-12-14 22:51:47', '2025-12-14 22:55:05', '2025-12-14', '23.773184,90.390528'),
(20, 3, 10, 7, 9, 'cancelled', '2025-12-15 05:24:28', NULL, '2025-12-15', '23.773184,90.390528');

-- --------------------------------------------------------

--
-- Table structure for table `tickets`
--

CREATE TABLE `tickets` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `destination_id` int(11) NOT NULL,
  `time_id` int(11) NOT NULL,
  `bus_id` int(11) NOT NULL,
  `seats` int(11) NOT NULL,
  `female_only` tinyint(1) DEFAULT 0,
  `payment_method` enum('online','cash') NOT NULL,
  `payment_status` enum('pending','paid') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `trip_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tickets`
--

INSERT INTO `tickets` (`id`, `student_id`, `destination_id`, `time_id`, `bus_id`, `seats`, `female_only`, `payment_method`, `payment_status`, `created_at`, `trip_date`) VALUES
(14, 2, 6, 8, 10, 1, 0, 'online', 'paid', '2025-12-14 13:28:48', '2025-12-14'),
(15, 2, 7, 9, 10, 1, 0, 'cash', 'pending', '2025-12-14 16:51:18', '2025-12-14'),
(16, 2, 7, 9, 10, 1, 0, 'online', 'paid', '2025-12-14 16:51:52', '2025-12-14');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `first_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) DEFAULT NULL,
  `student_id` varchar(20) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `gender` enum('male','female') DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('student','driver','admin') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `driving_license` varchar(50) DEFAULT NULL,
  `nid` varchar(50) DEFAULT NULL,
  `years_of_experience` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `first_name`, `last_name`, `student_id`, `email`, `phone`, `gender`, `password`, `role`, `created_at`, `driving_license`, `nid`, `years_of_experience`) VALUES
(1, 'Admin', 'User', NULL, 'admin@example.com', '01234', NULL, '$2y$10$XQYS1shjAokIMfgTgus0EezJaLH5tl1z9zr6Fny1jqQ81eMhXielq', 'admin', '2025-12-12 18:30:55', NULL, NULL, NULL),
(2, 'Arpita Bardhan', 'Juthi', '222118642', 'arpita.juthi@northsouth.edu', '01857853739', 'female', '$2y$10$0A.Pba1MgzN4oF8qJ7z1BekoQiOgVyhvFf16H4aNLKuyjuE.QFlXO', 'student', '2025-12-12 18:34:22', NULL, NULL, NULL),
(3, 'Nafis', 'Kamal', NULL, 'nafis.kamal@northsouth.edu', '01712345678', NULL, '$2y$10$4g9WZ8XIEjj3uDvS9nRLMuoJcbLrSQL8P80uuOAW5AkFvW5ubTfdy', 'driver', '2025-12-12 18:38:42', '12981028192818219', '192802980132i32', 3),
(4, 'Fatin Ishraq', 'Shabab', '2222699042', 'ishraq.shabab@northsouth.edu', '01812345678', 'male', '$2y$10$Zo8TGTZAJwCbRM/XtIJJL.co/Zpeg1zPdkwVLMyFSn48rcMFSI4R.', 'student', '2025-12-12 18:40:23', NULL, NULL, NULL),
(5, 'Arka', 'Karmoker', '2112343642', 'arka.karmoker@northsouth.edu', '+8801590153299', 'male', '$2y$10$PSWhTNK4LqQoeXoiUMgJS.BcQn8FhOhjJZpQeLt4DjA7o8o772OQS', 'student', '2025-12-12 22:01:01', NULL, NULL, NULL),
(7, 'Driver', 'One', NULL, 'driver.one@example.com', '01711234567', NULL, '$2y$10$B8fQN7Q/sSzhcdM7V1oMt.nNyOpU7YqW7KpLzihQ1Ors1owcAa19.', 'driver', '2025-12-14 22:51:09', '982039828392032983', '293820983289', 2);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `buses`
--
ALTER TABLE `buses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `reg_number` (`reg_number`);

--
-- Indexes for table `bus_assignments`
--
ALTER TABLE `bus_assignments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `bus_id` (`bus_id`),
  ADD KEY `destination_id` (`destination_id`),
  ADD KEY `time_id` (`time_id`);

--
-- Indexes for table `bus_times`
--
ALTER TABLE `bus_times`
  ADD PRIMARY KEY (`id`),
  ADD KEY `destination_id` (`destination_id`);

--
-- Indexes for table `destinations`
--
ALTER TABLE `destinations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `payment_options`
--
ALTER TABLE `payment_options`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `rides`
--
ALTER TABLE `rides`
  ADD PRIMARY KEY (`id`),
  ADD KEY `driver_id` (`driver_id`),
  ADD KEY `bus_id` (`bus_id`),
  ADD KEY `destination_id` (`destination_id`),
  ADD KEY `time_id` (`time_id`);

--
-- Indexes for table `tickets`
--
ALTER TABLE `tickets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `destination_id` (`destination_id`),
  ADD KEY `time_id` (`time_id`),
  ADD KEY `bus_id` (`bus_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `buses`
--
ALTER TABLE `buses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `bus_assignments`
--
ALTER TABLE `bus_assignments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT for table `bus_times`
--
ALTER TABLE `bus_times`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=482;

--
-- AUTO_INCREMENT for table `destinations`
--
ALTER TABLE `destinations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=72;

--
-- AUTO_INCREMENT for table `payment_options`
--
ALTER TABLE `payment_options`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `rides`
--
ALTER TABLE `rides`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `tickets`
--
ALTER TABLE `tickets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bus_assignments`
--
ALTER TABLE `bus_assignments`
  ADD CONSTRAINT `bus_assignments_ibfk_1` FOREIGN KEY (`bus_id`) REFERENCES `buses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bus_assignments_ibfk_2` FOREIGN KEY (`destination_id`) REFERENCES `destinations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bus_assignments_ibfk_3` FOREIGN KEY (`time_id`) REFERENCES `bus_times` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `bus_times`
--
ALTER TABLE `bus_times`
  ADD CONSTRAINT `bus_times_ibfk_1` FOREIGN KEY (`destination_id`) REFERENCES `destinations` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `rides`
--
ALTER TABLE `rides`
  ADD CONSTRAINT `rides_ibfk_1` FOREIGN KEY (`driver_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `rides_ibfk_2` FOREIGN KEY (`bus_id`) REFERENCES `buses` (`id`),
  ADD CONSTRAINT `rides_ibfk_3` FOREIGN KEY (`destination_id`) REFERENCES `destinations` (`id`),
  ADD CONSTRAINT `rides_ibfk_4` FOREIGN KEY (`time_id`) REFERENCES `bus_times` (`id`);

--
-- Constraints for table `tickets`
--
ALTER TABLE `tickets`
  ADD CONSTRAINT `tickets_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tickets_ibfk_2` FOREIGN KEY (`destination_id`) REFERENCES `destinations` (`id`),
  ADD CONSTRAINT `tickets_ibfk_3` FOREIGN KEY (`time_id`) REFERENCES `bus_times` (`id`),
  ADD CONSTRAINT `tickets_ibfk_4` FOREIGN KEY (`bus_id`) REFERENCES `buses` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

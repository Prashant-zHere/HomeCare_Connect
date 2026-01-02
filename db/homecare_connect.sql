-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 08, 2025 at 07:19 PM
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
-- Database: `homecare_connect`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id` int(11) NOT NULL,
  `email` varchar(50) NOT NULL,
  `password` varchar(25) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id`, `email`, `password`) VALUES
(1000, 'admin', 'admin');

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `service_provider_id` varchar(50) NOT NULL,
  `date` date NOT NULL,
  `time` time NOT NULL,
  `status` varchar(20) NOT NULL,
  `notes` varchar(500) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `user_id`, `service_provider_id`, `date`, `time`, `status`, `notes`) VALUES
(1, 4, 'SP2025092210454046', '2025-10-07', '21:37:00', 'cancelled', 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when an unknown printer');

-- --------------------------------------------------------

--
-- Table structure for table `service_provider`
--

CREATE TABLE `service_provider` (
  `id` varchar(30) NOT NULL,
  `user_name` varchar(25) DEFAULT NULL,
  `email` varchar(25) DEFAULT NULL,
  `password` varchar(25) DEFAULT NULL,
  `description` varchar(500) DEFAULT NULL,
  `file` varchar(150) DEFAULT NULL,
  `photo` varchar(150) DEFAULT NULL,
  `status` varchar(10) NOT NULL,
  `category` varchar(100) NOT NULL,
  `per` varchar(20) NOT NULL,
  `title` varchar(50) NOT NULL,
  `service_description` varchar(500) NOT NULL,
  `area` varchar(250) NOT NULL,
  `rate` double NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `service_provider`
--

INSERT INTO `service_provider` (`id`, `user_name`, `email`, `password`, `description`, `file`, `photo`, `status`, `category`, `per`, `title`, `service_description`, `area`, `rate`) VALUES
('SP2025092210454046', 'abhay', 'abc@gmail.com', '1', 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book.', 'SP2025092210454046_0_Complaint and Suggestions Management System Summary.pdf', 'default.jpg', 'approved', 'Electrician', 'hourly', 'Electrician', 'asdfgerfgwertgfgwrtbwrtbalksdhjasdkfgaklfdg', 'nashik', 500),
('SP2025092815421388', 'abhay', 'xyz@gmail.com', '1', 'asdfgerfgwertgfgwrtbwrtbalksdhjasdkfgaklfdg\r\nalsdfohgqleiv\r\n\r\na\r\ndfvq\r\nev\r\nqe vuyuyguyagsyu\r\nr vuyuyguyagsyu\r\nvasiudgfyagirfgytyghuiguyguyasrtgfgyu rfjkaugsfudy \r\nqawerfg erg', 'SP2025092815421388_0_upvote cat.png', 'default.jpg', 'rejected', 'Plumber', 'hourly', 'Plumber', 'asdfgerfgwertgfgwrtbwrtbalksdhjasdkfgaklfdg\nalsdfohgqleiv\n\na\ndfvq\nev\nqe vuyuyguyagsyu\nr vuyuyguyagsyu\nvasiudgfyagirfgytyghuiguyguyasrtgfgyu rfjkaugsfudy \nqawerfg erg', 'pimpri', 5000);

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `id` int(11) NOT NULL,
  `user_name` varchar(25) NOT NULL,
  `email` varchar(50) NOT NULL,
  `password` varchar(25) NOT NULL,
  `phone` int(11) NOT NULL,
  `city` varchar(50) NOT NULL,
  `address` varchar(150) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`id`, `user_name`, `email`, `password`, `phone`, `city`, `address`) VALUES
(1, 'Abhay', 'abc@gmail.com', '1', 2147483647, 'adf', 'asdf'),
(4, 'Abhay', 'xyz@gmail.com', '1', 74185296, 'Pune', 'Kharadi Pune - 410050');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `service_provider`
--
ALTER TABLE `service_provider`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

-- phpMyAdmin SQL Dump
-- version 5.2.1deb3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Dec 08, 2025 at 05:40 PM
-- Server version: 8.0.44-0ubuntu0.24.04.2
-- PHP Version: 8.3.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `booking_sys`
--

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

CREATE TABLE `appointments` (
  `apptid` int NOT NULL,
  `userid` int NOT NULL,
  `stylistid` int NOT NULL,
  `serviceid` int NOT NULL,
  `appt_datetime` datetime NOT NULL,
  `status` enum('booked','cancelled','completed') DEFAULT 'booked'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Table structure for table `services`
--

CREATE TABLE `services` (
  `serviceid` int NOT NULL,
  `service_name` varchar(255) NOT NULL,
  `duration` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `services`
--

INSERT INTO `services` (`serviceid`, `service_name`, `duration`) VALUES
(1, 'Haircut', 60),
(2, 'Barber Cut', 60),
(3, 'Children\'s Haircut', 60),
(4, 'Blowout', 45),
(5, 'Add-On Curling Iron', 30),
(6, 'Add-On Flat Iron', 30),
(7, 'Formal/Up-do/Bridal', 75),
(8, 'Color Touch-up', 75),
(9, 'Color Full Head', 75),
(10, 'Color Demi-Plus', 75),
(11, 'Color Vegan Vibrant', 75),
(12, 'Color Half Head Highlights', 105),
(13, 'Color Full Head Highlights', 120),
(14, 'Color Balayage/Ombre', 120),
(15, 'Color Highlights Per Foil', 105),
(16, 'Color Double Process Touch-up', 120),
(17, 'Color Double Process Full Head', 135),
(18, 'Magic Sleek Smoothing Treatment', 150),
(19, 'Botanical Repair Treatment', 30),
(20, 'Nutriplenish Treatment', 30);

-- --------------------------------------------------------

--
-- Table structure for table `stylists`
--

CREATE TABLE `stylists` (
  `stylistid` int NOT NULL,
  `firstname` varchar(100) NOT NULL,
  `lastname` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `stylists`
--

INSERT INTO `stylists` (`stylistid`, `firstname`, `lastname`) VALUES
(1, 'Stylist1', 'Stylist1'),
(2, 'Stylist2', 'Stylist2');

-- --------------------------------------------------------

--
-- Table structure for table `stylist_service`
--

CREATE TABLE `stylist_service` (
  `stylistid` int NOT NULL,
  `serviceid` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `stylist_service`
--

INSERT INTO `stylist_service` (`stylistid`, `serviceid`) VALUES
(1, 1),
(2, 1),
(1, 2),
(1, 3),
(2, 3),
(1, 4),
(2, 4),
(2, 5),
(2, 6),
(1, 7),
(2, 7),
(1, 8),
(2, 8),
(1, 9),
(2, 9),
(1, 10),
(2, 10),
(1, 11),
(2, 11),
(1, 12),
(2, 12),
(1, 13),
(2, 13),
(1, 14),
(2, 14),
(1, 15),
(2, 15),
(1, 16),
(2, 16),
(1, 17),
(2, 17),
(2, 18),
(1, 19),
(2, 19),
(1, 20),
(2, 20);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `userid` int NOT NULL,
  `firstname` varchar(100) NOT NULL,
  `lastname` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `phone` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`apptid`),
  ADD UNIQUE KEY `stylistid` (`stylistid`,`appt_datetime`),
  ADD KEY `userid` (`userid`),
  ADD KEY `serviceid` (`serviceid`);

--
-- Indexes for table `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`serviceid`);

--
-- Indexes for table `stylists`
--
ALTER TABLE `stylists`
  ADD PRIMARY KEY (`stylistid`);

--
-- Indexes for table `stylist_service`
--
ALTER TABLE `stylist_service`
  ADD PRIMARY KEY (`stylistid`,`serviceid`),
  ADD KEY `serviceid` (`serviceid`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`userid`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `apptid` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `serviceid` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `stylists`
--
ALTER TABLE `stylists`
  MODIFY `stylistid` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `userid` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `appointments`
--
ALTER TABLE `appointments`
  ADD CONSTRAINT `appointments_ibfk_1` FOREIGN KEY (`userid`) REFERENCES `users` (`userid`),
  ADD CONSTRAINT `appointments_ibfk_2` FOREIGN KEY (`stylistid`) REFERENCES `stylists` (`stylistid`),
  ADD CONSTRAINT `appointments_ibfk_3` FOREIGN KEY (`serviceid`) REFERENCES `services` (`serviceid`);

--
-- Constraints for table `stylist_service`
--
ALTER TABLE `stylist_service`
  ADD CONSTRAINT `stylist_service_ibfk_1` FOREIGN KEY (`stylistid`) REFERENCES `stylists` (`stylistid`) ON DELETE CASCADE,
  ADD CONSTRAINT `stylist_service_ibfk_2` FOREIGN KEY (`serviceid`) REFERENCES `services` (`serviceid`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

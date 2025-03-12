-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Mar 12, 2025 at 03:54 PM
-- Server version: 5.7.23-23
-- PHP Version: 8.1.31

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `arollice_wnc_farmers_markets`
--
CREATE DATABASE IF NOT EXISTS `arollice_wnc_farmers_markets` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `arollice_wnc_farmers_markets`;

-- --------------------------------------------------------

--
-- Table structure for table `currency`
--

CREATE TABLE `currency` (
  `currency_id` int(11) NOT NULL,
  `currency_name` varchar(50) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `currency`
--

INSERT INTO `currency` (`currency_id`, `currency_name`) VALUES
(8, 'Cash'),
(2, 'Credit/Debit Cards'),
(4, 'Mobile Payments'),
(3, 'SNAP/EBT');

-- --------------------------------------------------------

--
-- Table structure for table `item`
--

CREATE TABLE `item` (
  `item_id` int(11) NOT NULL,
  `item_name` varchar(100) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `item`
--

INSERT INTO `item` (`item_id`, `item_name`) VALUES
(29, 'Apples'),
(45, 'Baked Goods'),
(53, 'beens'),
(30, 'Berries'),
(46, 'Beverages'),
(31, 'Carrots'),
(51, 'Coffee'),
(32, 'Dairy Products'),
(33, 'Eggs'),
(34, 'Flowers'),
(7, 'Fresh Herbs'),
(6, 'Handmade Bread'),
(35, 'Handmade Crafts'),
(36, 'Jam & Preserves'),
(37, 'Lettuce'),
(4, 'Local Honey'),
(50, 'Maple Syrup'),
(38, 'Meat & Poultry'),
(39, 'Mushrooms'),
(40, 'Onions'),
(49, 'Organic eggs'),
(41, 'Peaches'),
(42, 'Peppers'),
(43, 'Pumpkins'),
(47, 'Seafood'),
(52, 'Simple Syrup'),
(44, 'Squash'),
(2, 'Strawberries'),
(1, 'Tomatoes'),
(8, 'Wildflower Bouquets');

-- --------------------------------------------------------

--
-- Table structure for table `market`
--

CREATE TABLE `market` (
  `market_id` int(11) NOT NULL,
  `market_name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `region_id` int(11) NOT NULL,
  `city` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `state_id` int(11) NOT NULL,
  `zip_code` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `parking_info` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `market_open` time NOT NULL DEFAULT '08:00:00',
  `market_close` time NOT NULL DEFAULT '14:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `market`
--

INSERT INTO `market` (`market_id`, `market_name`, `region_id`, `city`, `state_id`, `zip_code`, `parking_info`, `market_open`, `market_close`) VALUES
(1, 'Asheville City Market', 1, 'Asheville', 1, '28801', 'Street parking & nearby lots available', '08:00:00', '14:00:00'),
(2, 'Black Mountain Tailgate Market', 2, 'Black Mountain', 1, '28711', 'Free parking in designated areas', '09:30:00', '15:30:00'),
(3, 'Candler Farmers Market', 3, 'Candler', 1, '28715', 'Limited parking near vendors', '08:00:00', '14:00:00'),
(4, 'Hendersonville Farmers Market', 4, 'Hendersonville', 1, '28792', 'On-site parking available', '10:00:00', '16:00:00'),
(5, 'Waynesville Farmers Market', 5, 'Waynesville', 1, '28786', 'Parking available in surrounding lots', '08:00:00', '14:00:00'),
(6, 'Brevard Farmers Market', 6, 'Brevard', 1, '28712', 'Ample parking near event space', '11:00:00', '15:00:00'),
(7, 'Marshall Farmers Market', 7, 'Marshall', 1, '28753', 'Street parking available', '08:00:00', '14:00:00'),
(8, 'Weaverville Tailgate Market', 8, 'Weaverville', 1, '28787', 'Parking in church lot across the street', '12:00:00', '15:45:00');

-- --------------------------------------------------------

--
-- Table structure for table `market_schedule`
--

CREATE TABLE `market_schedule` (
  `market_id` int(11) NOT NULL,
  `market_day` enum('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday') NOT NULL,
  `last_day_of_season` date DEFAULT NULL,
  `season_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `market_schedule`
--

INSERT INTO `market_schedule` (`market_id`, `market_day`, `last_day_of_season`, `season_id`) VALUES
(1, 'Wednesday', '2025-10-22', 3),
(1, 'Saturday', '2025-10-25', 3),
(2, 'Monday', '2025-09-22', 2),
(2, 'Wednesday', '2024-09-24', 2),
(3, 'Monday', '2025-10-27', 3),
(3, 'Friday', '2025-10-31', 3),
(4, 'Tuesday', '2025-09-23', 2),
(4, 'Thursday', '2025-09-25', 2),
(5, 'Thursday', '2025-10-23', 3),
(5, 'Saturday', '2025-10-25', 3),
(6, 'Friday', '2025-09-26', 2),
(6, 'Saturday', '2024-09-27', 2),
(7, 'Tuesday', '2025-04-29', 1),
(7, 'Saturday', '2025-05-03', 1),
(8, 'Wednesday', '2025-08-27', 2),
(8, 'Saturday', '2025-08-30', 2);

-- --------------------------------------------------------

--
-- Table structure for table `policy_info`
--

CREATE TABLE `policy_info` (
  `policy_id` int(11) NOT NULL,
  `policy_name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `policy_description` text COLLATE utf8_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `policy_info`
--

INSERT INTO `policy_info` (`policy_id`, `policy_name`, `policy_description`) VALUES
(1, 'No Plastic Bags Policy', 'Vendors are encouraged to use compostable or paper bags.'),
(2, 'Organic Certification Required', 'Vendors must provide proof of organic certification to sell organic products.'),
(3, 'Pet-Friendly Market', 'Dogs must be on a leash and well-behaved.'),
(4, 'SNAP/EBT Accepted', 'Participating vendors accept SNAP/EBT payments for fresh produce.');

-- --------------------------------------------------------

--
-- Table structure for table `region`
--

CREATE TABLE `region` (
  `region_id` int(11) NOT NULL,
  `region_name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(10,7) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `region`
--

INSERT INTO `region` (`region_id`, `region_name`, `latitude`, `longitude`) VALUES
(1, 'Asheville', 35.5981680, -82.5523860),
(2, 'Black Mountain', 35.6172610, -82.3237400),
(3, 'Candler', 35.5358890, -82.6939030),
(4, 'Hendersonville', 35.3196980, -82.4673760),
(5, 'Waynesville', 35.4887440, -82.9919790),
(6, 'Brevard', 35.2329180, -82.7329660),
(7, 'Marshall', 35.7982360, -82.6831240),
(8, 'Weaverville', 35.6971860, -82.5633240);

-- --------------------------------------------------------

--
-- Table structure for table `season`
--

CREATE TABLE `season` (
  `season_id` int(11) NOT NULL,
  `season_name` varchar(50) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `season`
--

INSERT INTO `season` (`season_id`, `season_name`) VALUES
(3, 'Fall'),
(1, 'Spring'),
(2, 'Summer'),
(4, 'Winter');

-- --------------------------------------------------------

--
-- Table structure for table `state`
--

CREATE TABLE `state` (
  `state_id` int(11) NOT NULL,
  `state_code` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
  `state_name` varchar(100) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `state`
--

INSERT INTO `state` (`state_id`, `state_code`, `state_name`) VALUES
(1, 'NC', 'North Carolina'),
(2, 'SC', 'South Carolina'),
(3, 'TN', 'Tennessee');

-- --------------------------------------------------------

--
-- Table structure for table `user_account`
--

CREATE TABLE `user_account` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `password_hash` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `role` enum('vendor','admin','user') COLLATE utf8_unicode_ci NOT NULL,
  `vendor_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_login` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `user_account`
--

INSERT INTO `user_account` (`user_id`, `username`, `password_hash`, `email`, `role`, `vendor_id`, `created_at`, `last_login`, `is_active`) VALUES
(1, 'adminUser', '790f48e3ba51e2d0762e7d4a74d4076a62cfb34d44e3dfbc43798fe9ff399602', 'admin@example.com', 'admin', NULL, '2025-02-19 15:47:14', NULL, 1),
(2, 'vendorUser', '4a4a165404d56acf0ddc4946850c61966b0fa43a593092f58a5016b20d048fab', 'vendor@example.com', 'vendor', 1, '2025-02-19 15:47:14', NULL, 1),
(3, 'regularUser', '8e3bde512bf178d26128fdcda19de3ecea6ce26c4edaa177a5e2d49713272443', 'user@example.com', 'user', NULL, '2025-02-19 15:47:14', NULL, 1),
(4, 'AshleeTest', 'a7e1f2224209c49467afe21409e64f3eac45c672d846b67dfc1aa7c5fbd04c25', 'ashlee@example.com', 'admin', NULL, '2025-02-19 15:47:14', NULL, 1),
(11, 'TestAdmin', '$2y$10$ej6j6kSozAAmGabk36CIgu6vKUpgwQKDb/enhzOWIq.4QPFnKXq2.', 'testadmin@email.com', 'admin', NULL, '2025-03-05 00:05:05', NULL, 1),
(13, 'TestVendor', '$2y$10$LguSCcH7fQkiKvJ8M8m41OOW76LWbr7bCnteL9ShgUMpB/lm9GVJK', 'testemail@email.com', 'vendor', 23, '2025-03-05 22:40:31', NULL, 1);

-- --------------------------------------------------------

--
-- Table structure for table `vendor`
--

CREATE TABLE `vendor` (
  `vendor_id` int(11) NOT NULL,
  `vendor_name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `vendor_website` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `vendor_logo` varchar(2083) COLLATE utf8_unicode_ci DEFAULT NULL,
  `vendor_description` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `status` varchar(20) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `vendor`
--

INSERT INTO `vendor` (`vendor_id`, `vendor_name`, `vendor_website`, `vendor_logo`, `vendor_description`, `status`) VALUES
(1, 'Happy Harvest Farms', NULL, NULL, 'Providing fresh produce and seasonal vegetables grown sustainably.', 'approved'),
(2, 'Blue Ridge Organics', NULL, NULL, 'Local organic farm offering fruits, vegetables, and herbs.', 'approved'),
(3, 'Candler Valley Produce', NULL, NULL, 'Family-run farm specializing in heirloom tomatoes and peppers.', 'approved'),
(4, 'Mountain Fresh Meats', NULL, NULL, 'High-quality, locally-sourced meats and poultry.', 'approved'),
(5, 'Hendersonville Honey Co.', NULL, NULL, 'Pure honey harvested from local Hendersonville apiaries.', 'approved'),
(6, 'Waynesville Wildflowers', NULL, NULL, 'Fresh wildflowers and seasonal bouquets from Waynesville.', 'approved'),
(7, 'Brevard Dairy Farms', NULL, NULL, 'Local dairy farm offering artisanal cheeses and fresh milk products.', 'approved'),
(8, 'Weaverville Artisan Bread', NULL, NULL, 'Local vendor offering quality products from Western North Carolina.', 'approved'),
(9, 'Green Thumb Gardens', NULL, NULL, 'Sustainable farm providing organically grown vegetables and herbs.', 'approved'),
(10, 'Highland Cattle Ranch', NULL, NULL, 'Ranch offering premium grass-fed beef from the Highlands.', 'approved'),
(11, 'Riverwood Bakery', NULL, NULL, 'Freshly baked artisan breads, pastries, and desserts.', 'approved'),
(12, 'Appalachian Mushrooms', NULL, NULL, 'Cultivating gourmet mushrooms for culinary enthusiasts.', 'approved'),
(13, 'Sunny Fields Flowers', NULL, NULL, 'Specializing in vibrant floral arrangements from Sunny Fields.', 'approved'),
(14, 'Oak Ridge Maple Syrup', NULL, NULL, 'Craft maple syrup produced locally in Oak Ridge.', 'approved'),
(15, 'Smoky Mountain Goat Cheese', NULL, NULL, 'Artisan goat cheeses made in the Smoky Mountains.', 'approved'),
(16, 'Rolling Hills Coffee Roasters', NULL, NULL, 'Local coffee roasters offering freshly roasted specialty coffees.', 'approved'),
(23, 'TestVendor', NULL, 'uploads/vendor_23_1741196674_67c88d8265b0f.png', 'testing testing testing... TESTING! TESTING!', 'pending');

-- --------------------------------------------------------

--
-- Table structure for table `vendor_currency`
--

CREATE TABLE `vendor_currency` (
  `vendor_id` int(11) NOT NULL,
  `currency_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `vendor_currency`
--

INSERT INTO `vendor_currency` (`vendor_id`, `currency_id`) VALUES
(1, 2),
(1, 8),
(2, 2),
(2, 3),
(3, 2),
(3, 4),
(3, 8),
(4, 2),
(4, 3),
(4, 4),
(5, 2),
(5, 4),
(5, 8),
(6, 2),
(6, 3),
(6, 4),
(6, 8),
(7, 2),
(7, 3),
(7, 4),
(8, 3),
(8, 8),
(9, 2),
(9, 3),
(9, 8),
(10, 2),
(10, 4),
(11, 3),
(11, 8),
(12, 2),
(12, 4),
(13, 2),
(13, 8),
(14, 3),
(14, 4),
(15, 2),
(15, 8),
(16, 3),
(16, 8);

-- --------------------------------------------------------

--
-- Table structure for table `vendor_item`
--

CREATE TABLE `vendor_item` (
  `vendor_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `season_id` int(11) DEFAULT NULL,
  `is_seasonal` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `vendor_item`
--

INSERT INTO `vendor_item` (`vendor_id`, `item_id`, `season_id`, `is_seasonal`) VALUES
(1, 1, 2, 1),
(1, 2, 2, 1),
(1, 29, NULL, 0),
(1, 30, NULL, 0),
(1, 31, NULL, 0),
(1, 37, NULL, 0),
(1, 41, NULL, 0),
(2, 1, NULL, 0),
(2, 2, NULL, 0),
(2, 31, NULL, 0),
(2, 37, NULL, 0),
(2, 42, NULL, 0),
(3, 6, NULL, 0),
(3, 7, NULL, 0),
(3, 8, NULL, 0),
(3, 39, NULL, 0),
(3, 45, NULL, 0),
(3, 46, NULL, 0),
(3, 47, NULL, 0),
(4, 4, 1, 1),
(4, 7, NULL, 0),
(4, 33, NULL, 0),
(4, 38, NULL, 0),
(4, 39, NULL, 0),
(4, 42, NULL, 0),
(5, 4, NULL, 0),
(5, 8, 3, 1),
(5, 34, NULL, 0),
(5, 36, NULL, 0),
(6, 6, NULL, 0),
(6, 7, NULL, 0),
(6, 8, NULL, 0),
(6, 34, NULL, 0),
(6, 45, NULL, 0),
(6, 46, NULL, 0),
(7, 7, 2, 1),
(7, 32, NULL, 0),
(7, 36, NULL, 0),
(7, 37, NULL, 0),
(7, 40, NULL, 0),
(7, 49, NULL, 0),
(8, 6, NULL, 0),
(8, 7, NULL, 0),
(8, 34, NULL, 0),
(8, 45, NULL, 0),
(9, 29, NULL, 0),
(9, 30, NULL, 0),
(9, 31, NULL, 0),
(9, 37, NULL, 0),
(9, 41, NULL, 0),
(10, 33, NULL, 0),
(10, 38, NULL, 0),
(10, 39, NULL, 0),
(10, 42, NULL, 0),
(10, 44, NULL, 0),
(11, 35, NULL, 0),
(11, 36, NULL, 0),
(11, 45, NULL, 0),
(11, 46, NULL, 0),
(12, 34, NULL, 0),
(12, 39, NULL, 0),
(13, 7, NULL, 0),
(13, 8, NULL, 0),
(13, 34, NULL, 0),
(13, 45, NULL, 0),
(13, 46, NULL, 0),
(14, 4, NULL, 0),
(14, 8, NULL, 0),
(14, 34, NULL, 0),
(14, 35, NULL, 0),
(14, 36, NULL, 0),
(14, 50, NULL, 0),
(15, 7, NULL, 0),
(15, 32, NULL, 0),
(15, 36, NULL, 0),
(15, 49, NULL, 0),
(16, 36, NULL, 0),
(16, 45, NULL, 0),
(16, 46, NULL, 0),
(16, 51, NULL, 0),
(16, 52, NULL, 0),
(23, 2, NULL, 0),
(23, 7, NULL, 0),
(23, 31, NULL, 0);

-- --------------------------------------------------------

--
-- Table structure for table `vendor_market`
--

CREATE TABLE `vendor_market` (
  `vendor_id` int(11) NOT NULL,
  `market_id` int(11) NOT NULL,
  `attending_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `vendor_market`
--

INSERT INTO `vendor_market` (`vendor_id`, `market_id`, `attending_date`) VALUES
(1, 1, '2025-06-15'),
(1, 3, NULL),
(1, 6, NULL),
(1, 8, '2024-03-10'),
(2, 2, '2025-06-22'),
(2, 5, NULL),
(2, 7, NULL),
(3, 3, '2025-07-01'),
(3, 4, NULL),
(3, 8, '2024-03-17'),
(4, 3, NULL),
(4, 4, '2025-07-08'),
(4, 6, NULL),
(5, 2, NULL),
(5, 5, '2025-07-15'),
(5, 8, '2024-03-24'),
(6, 2, NULL),
(6, 6, '2025-07-22'),
(6, 7, NULL),
(7, 5, NULL),
(7, 7, '2025-07-29'),
(9, 1, NULL),
(9, 3, NULL),
(9, 4, NULL),
(9, 8, NULL),
(10, 1, NULL),
(10, 3, NULL),
(10, 7, NULL),
(11, 1, NULL),
(11, 2, NULL),
(11, 5, NULL),
(12, 4, NULL),
(12, 8, NULL),
(13, 6, NULL),
(13, 7, NULL),
(14, 1, NULL),
(14, 5, NULL),
(15, 2, NULL),
(15, 6, NULL),
(16, 4, NULL),
(23, 1, NULL),
(23, 2, NULL),
(23, 3, NULL),
(23, 4, NULL),
(23, 5, NULL),
(23, 6, NULL),
(23, 7, NULL),
(23, 8, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `currency`
--
ALTER TABLE `currency`
  ADD PRIMARY KEY (`currency_id`),
  ADD UNIQUE KEY `currency_name` (`currency_name`);

--
-- Indexes for table `item`
--
ALTER TABLE `item`
  ADD PRIMARY KEY (`item_id`),
  ADD UNIQUE KEY `item_name` (`item_name`),
  ADD KEY `item_name_2` (`item_name`);

--
-- Indexes for table `market`
--
ALTER TABLE `market`
  ADD PRIMARY KEY (`market_id`),
  ADD KEY `market_name` (`market_name`),
  ADD KEY `region_id` (`region_id`),
  ADD KEY `state_id` (`state_id`);

--
-- Indexes for table `market_schedule`
--
ALTER TABLE `market_schedule`
  ADD PRIMARY KEY (`market_id`,`market_day`),
  ADD KEY `fk_market_schedule_season` (`season_id`);

--
-- Indexes for table `policy_info`
--
ALTER TABLE `policy_info`
  ADD PRIMARY KEY (`policy_id`),
  ADD UNIQUE KEY `policy_name` (`policy_name`),
  ADD KEY `policy_name_2` (`policy_name`);

--
-- Indexes for table `region`
--
ALTER TABLE `region`
  ADD PRIMARY KEY (`region_id`),
  ADD UNIQUE KEY `region_name` (`region_name`),
  ADD KEY `region_name_2` (`region_name`);

--
-- Indexes for table `season`
--
ALTER TABLE `season`
  ADD PRIMARY KEY (`season_id`),
  ADD UNIQUE KEY `season_name` (`season_name`),
  ADD KEY `season_name_2` (`season_name`);

--
-- Indexes for table `state`
--
ALTER TABLE `state`
  ADD PRIMARY KEY (`state_id`),
  ADD UNIQUE KEY `state_code` (`state_code`);

--
-- Indexes for table `user_account`
--
ALTER TABLE `user_account`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `vendor_id` (`vendor_id`);

--
-- Indexes for table `vendor`
--
ALTER TABLE `vendor`
  ADD PRIMARY KEY (`vendor_id`),
  ADD KEY `vendor_name` (`vendor_name`);

--
-- Indexes for table `vendor_currency`
--
ALTER TABLE `vendor_currency`
  ADD PRIMARY KEY (`vendor_id`,`currency_id`),
  ADD KEY `vendor_id` (`vendor_id`),
  ADD KEY `currency_id` (`currency_id`);

--
-- Indexes for table `vendor_item`
--
ALTER TABLE `vendor_item`
  ADD PRIMARY KEY (`vendor_id`,`item_id`),
  ADD KEY `season_id` (`season_id`),
  ADD KEY `vendor_id` (`vendor_id`),
  ADD KEY `item_id` (`item_id`);

--
-- Indexes for table `vendor_market`
--
ALTER TABLE `vendor_market`
  ADD PRIMARY KEY (`vendor_id`,`market_id`),
  ADD KEY `vendor_id` (`vendor_id`),
  ADD KEY `market_id` (`market_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `currency`
--
ALTER TABLE `currency`
  MODIFY `currency_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `item`
--
ALTER TABLE `item`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;

--
-- AUTO_INCREMENT for table `market`
--
ALTER TABLE `market`
  MODIFY `market_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `policy_info`
--
ALTER TABLE `policy_info`
  MODIFY `policy_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `region`
--
ALTER TABLE `region`
  MODIFY `region_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `season`
--
ALTER TABLE `season`
  MODIFY `season_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `state`
--
ALTER TABLE `state`
  MODIFY `state_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `user_account`
--
ALTER TABLE `user_account`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `vendor`
--
ALTER TABLE `vendor`
  MODIFY `vendor_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `market`
--
ALTER TABLE `market`
  ADD CONSTRAINT `market_ibfk_1` FOREIGN KEY (`region_id`) REFERENCES `region` (`region_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `market_ibfk_2` FOREIGN KEY (`state_id`) REFERENCES `state` (`state_id`) ON DELETE CASCADE;

--
-- Constraints for table `market_schedule`
--
ALTER TABLE `market_schedule`
  ADD CONSTRAINT `fk_market_schedule_season` FOREIGN KEY (`season_id`) REFERENCES `season` (`season_id`),
  ADD CONSTRAINT `market_schedule_ibfk_1` FOREIGN KEY (`market_id`) REFERENCES `market` (`market_id`);

--
-- Constraints for table `user_account`
--
ALTER TABLE `user_account`
  ADD CONSTRAINT `user_account_ibfk_1` FOREIGN KEY (`vendor_id`) REFERENCES `vendor` (`vendor_id`) ON DELETE SET NULL;

--
-- Constraints for table `vendor_currency`
--
ALTER TABLE `vendor_currency`
  ADD CONSTRAINT `vendor_currency_ibfk_1` FOREIGN KEY (`vendor_id`) REFERENCES `vendor` (`vendor_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `vendor_currency_ibfk_2` FOREIGN KEY (`currency_id`) REFERENCES `currency` (`currency_id`) ON DELETE CASCADE;

--
-- Constraints for table `vendor_item`
--
ALTER TABLE `vendor_item`
  ADD CONSTRAINT `vendor_item_ibfk_1` FOREIGN KEY (`vendor_id`) REFERENCES `vendor` (`vendor_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `vendor_item_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `item` (`item_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `vendor_item_ibfk_3` FOREIGN KEY (`season_id`) REFERENCES `season` (`season_id`) ON DELETE SET NULL;

--
-- Constraints for table `vendor_market`
--
ALTER TABLE `vendor_market`
  ADD CONSTRAINT `vendor_market_ibfk_1` FOREIGN KEY (`vendor_id`) REFERENCES `vendor` (`vendor_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `vendor_market_ibfk_2` FOREIGN KEY (`market_id`) REFERENCES `market` (`market_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

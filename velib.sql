-- phpMyAdmin SQL Dump
-- version 4.6.6deb5
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Apr 13, 2020 at 03:18 PM
-- Server version: 10.3.22-MariaDB-0+deb10u1
-- PHP Version: 7.0.33-0+deb9u7

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `velib`
--

-- --------------------------------------------------------

--
-- Table structure for table `stations_info`
--

CREATE TABLE `stations_info` (
  `stationcode` varchar(6) NOT NULL,
  `name` varchar(250) DEFAULT NULL,
  `capacity` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `gps_lat` double DEFAULT NULL,
  `gps_lng` double DEFAULT NULL,
  `is_installed` tinyint(1) DEFAULT NULL,
  `is_returning` tinyint(1) DEFAULT NULL,
  `is_renting` tinyint(1) DEFAULT NULL,
  `last_reported` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `stations_usage`
--

CREATE TABLE `stations_usage` (
  `stationcode` varchar(6) NOT NULL,
  `last_update` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `num_bikes_mechanical` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `num_bikes_ebike` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `num_docks_available` tinyint(3) UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `stations_info`
--
ALTER TABLE `stations_info`
  ADD PRIMARY KEY (`stationcode`);

--
-- Indexes for table `stations_usage`
--
ALTER TABLE `stations_usage`
  ADD PRIMARY KEY (`stationcode`,`last_update`);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

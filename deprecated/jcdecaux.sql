-- phpMyAdmin SQL Dump
-- version 4.6.6deb4
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Feb 10, 2018 at 03:32 PM
-- Server version: 10.1.23-MariaDB-9+deb9u1
-- PHP Version: 7.0.27-0+deb9u1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `velib`
--
CREATE DATABASE IF NOT EXISTS `velib` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `velib`;

-- --------------------------------------------------------

--
-- Table structure for table `stations_info`
--

CREATE TABLE `stations_info` (
  `number` mediumint(9) UNSIGNED NOT NULL,
  `contract_name` varchar(50) NOT NULL,
  `name` varchar(250) NOT NULL,
  `address` varchar(250) NOT NULL,
  `position_lat` double NOT NULL,
  `position_lng` double NOT NULL,
  `banking` varchar(10) NOT NULL,
  `bonus` varchar(10) NOT NULL,
  `bike_stands` tinyint(3) UNSIGNED NOT NULL,
  `status` varchar(15) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `stations_usage`
--

CREATE TABLE `stations_usage` (
  `number` smallint(5) UNSIGNED NOT NULL,
  `last_update` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `available_bike_stands` tinyint(3) UNSIGNED NOT NULL,
  `available_bikes` tinyint(3) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `stations_info`
--
ALTER TABLE `stations_info`
  ADD PRIMARY KEY (`number`,`contract_name`);

--
-- Indexes for table `stations_usage`
--
ALTER TABLE `stations_usage`
  ADD PRIMARY KEY (`number`,`last_update`);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

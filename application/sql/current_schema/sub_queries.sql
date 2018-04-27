-- phpMyAdmin SQL Dump
-- version 4.4.2
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Jul 27, 2015 at 10:58 AM
-- Server version: 5.5.44-0ubuntu0.14.04.1
-- PHP Version: 5.5.9-1ubuntu4.11

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Database: `tsunami`
--

-- --------------------------------------------------------

--
-- Table structure for table `sub_queries`
--

CREATE TABLE IF NOT EXISTS `sub_queries` (
  `id` int(11) NOT NULL,
  `query_id` int(11) NOT NULL,
  `carrier_id` int(11) NOT NULL,
  `vendor_id` int(11) DEFAULT NULL,
  `esn` char(140) NOT NULL,
  `attempts` int(1) DEFAULT '0',
  `started` datetime DEFAULT NULL,
  `canceled` datetime DEFAULT NULL,
  `completed` datetime DEFAULT NULL,
  `failed` datetime DEFAULT NULL,
  `status` char(100) DEFAULT NULL,
  `status_details` text,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `sub_queries`
--
ALTER TABLE `sub_queries`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `sub_queries`
--
ALTER TABLE `sub_queries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
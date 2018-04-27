-- phpMyAdmin SQL Dump
-- version 4.4.2
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Jul 27, 2015 at 10:56 AM
-- Server version: 5.5.44-0ubuntu0.14.04.1
-- PHP Version: 5.5.9-1ubuntu4.11

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Database: `tsunami`
--

-- --------------------------------------------------------

--
-- Table structure for table `carriers`
--

CREATE TABLE IF NOT EXISTS `carriers` (
  `id` int(11) NOT NULL,
  `title` char(140) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `carriers`
--

INSERT INTO `carriers` (`id`, `title`, `active`, `created`, `modified`) VALUES
(1, 'Verizon', 1, '2015-05-14 00:00:00', '2015-05-14 00:00:00'),
(2, 'AT&T', 1, '2015-05-14 00:00:00', '2015-05-24 08:35:49'),
(3, 'Sprint', 1, '2015-05-14 14:48:03', '2015-05-24 08:35:59'),
(4, 'T-Mobile', 1, '2015-05-14 14:48:38', '2015-05-29 08:31:13');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `carriers`
--
ALTER TABLE `carriers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ctitle` (`title`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `carriers`
--
ALTER TABLE `carriers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=5;
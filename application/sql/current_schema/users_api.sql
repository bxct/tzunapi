-- phpMyAdmin SQL Dump
-- version 4.4.2
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Jun 19, 2015 at 02:58 AM
-- Server version: 5.5.43-0ubuntu0.14.04.1
-- PHP Version: 5.5.9-1ubuntu4.9

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Database: `tsunami`
--

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL,
  `public_key` char(140) NOT NULL,
  `private_key` char(140) NOT NULL,
  `username` char(140) DEFAULT NULL,
  `password` char(140) DEFAULT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `upubk` (`public_key`),
  ADD UNIQUE KEY `uuname` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=5;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `public_key`, `private_key`, `username`, `password`, `created`, `modified`) VALUES
(NULL, 'sajeNXgr$zKRkXZBx0OBYAaizpAlL40', 'QtcC0KnT$/Wxt3HHsK1FX6blBH9pZ11', 'tsunamicli', '', NOW(), NOW());

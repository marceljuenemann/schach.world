-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `nsv-ligen`
--

-- --------------------------------------------------------

--
-- Table structure for table `registration_players`
--

CREATE TABLE `registration_players` (
  `id` int(11) NOT NULL,
  `tournament` varchar(20) NOT NULL,
  `tournament_group` varchar(20) NOT NULL,
  `name` varchar(60) NOT NULL,
  `club` varchar(60) DEFAULT NULL,
  `zps` varchar(5) DEFAULT NULL,
  `member_id` varchar(4) DEFAULT NULL,
  `gender` varchar(1) DEFAULT NULL,
  `year_of_birth` int(11) DEFAULT NULL,
  `dwz` int(11) DEFAULT NULL,
  `elo` int(11) DEFAULT NULL,
  `fide_title` varchar(3) DEFAULT NULL,
  `fide_id` int(11) DEFAULT NULL,
  `fide_country` varchar(3) DEFAULT NULL,
  `contact_name` varchar(60) NOT NULL,
  `contact_email` varchar(100) NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


--
-- Indexes for dumped tables
--

--
-- Indexes for table `registration_players`
--
ALTER TABLE `registration_players`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tournament` (`tournament`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `registration_players`
--
ALTER TABLE `registration_players`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

-- phpMyAdmin SQL Dump
-- version 4.2.11
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: Jan 28, 2015 at 05:37 PM
-- Server version: 5.6.21
-- PHP Version: 5.6.3

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `test`
--

-- --------------------------------------------------------

--
-- Table structure for table `progs`
--

CREATE TABLE IF NOT EXISTS `progs` (
`id` int(11) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `sch_id` int(11) NOT NULL,
  `schnip` int(1),
  `dimo` varchar(40) NOT NULL,
  `sch1` text NOT NULL,
  `princ1` varchar(100) NOT NULL,
  `emails1` text NOT NULL,
  `sch2` text NOT NULL,  
  `princ2` varchar(100) NOT NULL,
  `emails2` text NOT NULL,
  `praxi` text NOT NULL,
  `titel` text NOT NULL,
  `subti` text NOT NULL,
  `categ` text NOT NULL,
  `theme` text NOT NULL,
  `goal` text NOT NULL,
  `meth` text NOT NULL,
  `pedia` text NOT NULL,
  `dura` text NOT NULL,
  `m1` text NOT NULL,
  `m2` text NOT NULL,
  `m3` text NOT NULL,
  `m4` text NOT NULL,
  `m5` text NOT NULL,
  `m6` text NOT NULL,
  `visit` text NOT NULL,
  `act` text NOT NULL,
  `prsnt` text NOT NULL,
  `nam1` text NOT NULL,
  `sur1` text NOT NULL,
  `email1` text NOT NULL,
  `mob1` text NOT NULL,
  `eid1` text NOT NULL,
  `his1` text NOT NULL,
  `qua1` text NOT NULL,
  `nam2` text NOT NULL,
  `sur2` text NOT NULL,
  `email2` text NOT NULL,
  `mob2` text NOT NULL,
  `eid2` text NOT NULL,
  `his2` text NOT NULL,
  `qua2` text NOT NULL,
  `nam3` text NOT NULL,
  `sur3` text NOT NULL,
  `email3` text NOT NULL,
  `mob3` text NOT NULL,
  `eid3` text NOT NULL,
  `his3` text NOT NULL,
  `qua3` text NOT NULL,
  `nam4` text NOT NULL,
  `sur4` text NOT NULL,
  `email4` text NOT NULL,
  `mob4` text NOT NULL,
  `eid4` text NOT NULL,
  `his4` text NOT NULL,
  `qua4` text NOT NULL,
  `Nr` int(11) NOT NULL,
  `char` text NOT NULL,
  `grade` text NOT NULL,
  `notes` text NOT NULL,
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `progs`
--
ALTER TABLE `progs`
 ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `progs`
--
ALTER TABLE `progs`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=2;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

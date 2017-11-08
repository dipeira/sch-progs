-- phpMyAdmin SQL Dump
-- version 4.4.14
-- http://www.phpmyadmin.net
--
-- Φιλοξενητής: 127.0.0.1
-- Χρόνος δημιουργίας: 05 Δεκ 2016 στις 14:23:56
-- Έκδοση διακομιστή: 5.6.26
-- Έκδοση PHP: 5.5.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES greek */;

--
-- Βάση δεδομένων: `temp`
--

-- --------------------------------------------------------

--
-- Δομή πίνακα για τον πίνακα `progs`
--

CREATE TABLE IF NOT EXISTS `progs` (
  `id` int(11) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `sch1` int(11) NOT NULL,
  `princ1` varchar(100) NOT NULL,
  `sch2` int(11) NOT NULL,
  `princ2` varchar(100) NOT NULL,
  `nam1` text NOT NULL,
  `email1` text NOT NULL,
  `mob1` text NOT NULL,
  `eid1` enum('ΠΕ05','ΠΕ06','ΠΕ07','ΠΕ08','ΠΕ11','ΠΕ16','ΠΕ18.41','ΠΕ19-20','ΠΕ60','ΠΕ70') NOT NULL,
  `his1` enum('Όχι','Ναι') NOT NULL,
  `qua1` enum('Όχι','Ναι') NOT NULL,
  `nam2` text NOT NULL,
  `email2` text NOT NULL,
  `mob2` text NOT NULL,
  `eid2` enum('ΠΕ05','ΠΕ06','ΠΕ07','ΠΕ08','ΠΕ11','ΠΕ16','ΠΕ18.41','ΠΕ19-20','ΠΕ60','ΠΕ70') NOT NULL,
  `his2` enum('Όχι','Ναι') NOT NULL,
  `qua2` enum('Όχι','Ναι') NOT NULL,
  `nam3` text NOT NULL,
  `email3` text NOT NULL,
  `mob3` text NOT NULL,
  `eid3` enum('ΠΕ05','ΠΕ06','ΠΕ07','ΠΕ08','ΠΕ11','ΠΕ16','ΠΕ18.41','ΠΕ19-20','ΠΕ60','ΠΕ70') NOT NULL,
  `his3` enum('Όχι','Ναι') NOT NULL,
  `qua3` enum('Όχι','Ναι') NOT NULL,
  `titel` text NOT NULL,
  `subti` text NOT NULL,
  `praxi` int(5) NOT NULL,
  `praxidate` text NOT NULL,
  `grade` text NOT NULL,
  `nr` int(11) NOT NULL,
  `nr_boys` int(11) NOT NULL,
  `nr_girls` int(11) NOT NULL,
  `cha` enum('Μικτή ομάδα','Αμιγές τμήμα') NOT NULL,
  `arxeio` enum('Όχι','Ναι') NOT NULL,
  `theme` text NOT NULL,
  `goal` text NOT NULL,
  `meth` text NOT NULL,
  `dura` enum('2 μήνες','3 μήνες','4 μήνες','5 μήνες') NOT NULL,
  `visit` text NOT NULL,
  `foreis` text NOT NULL,
  `pedia` text NOT NULL,
  `categ` enum('Αγωγή Υγείας','Περιβαλλοντική Εκπαίδευση','Πολιτιστικών Θεμάτων') NOT NULL,
  `diax` set('Έντυπη έκδοση','Αναρτήσεις στο διαδίκτυο','Παραγωγή βίντεο','Αφίσες - φυλλάδια','Έκθεση κατασκευών - χειροτεχνιών - φωτογραφιών','Παρεμβάσεις','Άλλο') NOT NULL,
  `diax_other` text NOT NULL,
  `synant` text NOT NULL,
  `m1` text NOT NULL,
  `m2` text NOT NULL,
  `m3` text NOT NULL,
  `m4` text NOT NULL,
  `m5` text NOT NULL,
  `prsnt` enum('Όχι','Ίσως','Ναι') NOT NULL,  
  `notes` text NOT NULL,
  `chk` enum('Όχι','Ναι') NOT NULL,
  `vev` enum('Όχι','Ναι') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Ευρετήρια για άχρηστους πίνακες
--

--
-- Ευρετήρια για πίνακα `progs`
--
ALTER TABLE `progs`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT για άχρηστους πίνακες
--

--
-- AUTO_INCREMENT για πίνακα `progs`
--
ALTER TABLE `progs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

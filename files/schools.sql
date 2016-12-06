-- phpMyAdmin SQL Dump
-- version 4.4.14
-- http://www.phpmyadmin.net
--
-- Φιλοξενητής: 127.0.0.1
-- Χρόνος δημιουργίας: 05 Δεκ 2016 στις 14:23:00
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
-- Δομή πίνακα για τον πίνακα `schools`
--

CREATE TABLE IF NOT EXISTS `schools` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `code` int(11) NOT NULL,
  `type` tinyint(4) NOT NULL,
  `email1` varchar(50) NOT NULL,
  `email2` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=greek;

--
-- Ευρετήρια για άχρηστους πίνακες
--

--
-- Ευρετήρια για πίνακα `schools`
--
ALTER TABLE `schools`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT για άχρηστους πίνακες
--

--
-- AUTO_INCREMENT για πίνακα `schools`
--
ALTER TABLE `schools`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;



SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Table structure for table `progs`
--

CREATE TABLE `progs` (
  `id` int NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `sch1` int NOT NULL,
  `princ1` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `sch2` int DEFAULT NULL,
  `princ2` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `nam1` text NOT NULL,
  `email1` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci,
  `mob1` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci,
  `eid1` enum('ΠΕ05','ΠΕ06','ΠΕ07','ΠΕ08','ΠΕ11','ΠΕ79','ΠΕ86','ΠΕ60','ΠΕ70','ΠΕ91','ΠΕ61','ΠΕ71','Άλλο') CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `his1` enum('Όχι','Ναι') CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `qua1` enum('Όχι','Ναι') CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `nam2` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci,
  `email2` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci,
  `mob2` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci,
  `eid2` enum('ΠΕ05','ΠΕ06','ΠΕ07','ΠΕ08','ΠΕ11','ΠΕ79','ΠΕ86','ΠΕ60','ΠΕ70','ΠΕ91','ΠΕ61','ΠΕ71','Άλλο') CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `his2` enum('','Όχι','Ναι') CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `qua2` enum('','Όχι','Ναι') CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `nam3` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci,
  `email3` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci,
  `mob3` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci,
  `eid3` enum('ΠΕ05','ΠΕ06','ΠΕ07','ΠΕ08','ΠΕ11','ΠΕ79','ΠΕ86','ΠΕ60','ΠΕ70','ΠΕ91','ΠΕ61','ΠΕ71','Άλλο') CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `his3` enum('','Όχι','Ναι') CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `qua3` enum('','Όχι','Ναι') CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `titel` text NOT NULL,
  `categ` enum('Αγωγής Υγείας','Περιβαλλοντικής Εκπαίδευσης','Πολιτιστικών Θεμάτων') CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `subti` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci,
  `praxi` int NOT NULL DEFAULT '0',
  `praxidate` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci,
  `grade` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci,
  `nr` int DEFAULT NULL,
  `nr_boys` int DEFAULT NULL,
  `nr_girls` int DEFAULT NULL,
  `cha` enum('Μικτή ομάδα','Αμιγές τμήμα') NOT NULL,
  `arxeio` enum('Όχι','Ναι') NOT NULL,
  `theme` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci,
  `goal` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci,
  `meth` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci,
  `dura` enum('3 μήνες','4 μήνες','5 μήνες') NOT NULL,
  `month` varchar(20) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `visit` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci,
  `foreis` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci,
  `pedia` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci,
  `diax` set('Έντυπη έκδοση','Αναρτήσεις στο διαδίκτυο','Παραγωγή βίντεο','Αφίσες - φυλλάδια','Έκθεση κατασκευών - χειροτεχνιών - φωτογραφιών','Παρεμβάσεις','Άλλο') CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `diax_other` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci,
  `m1` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci,
  `m2` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci,
  `m3` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci,
  `m4` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci,
  `m5` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci,
  `prsnt` enum('Όχι','Ίσως','Ναι') NOT NULL,
  `notes` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci,
  `chk` enum('Όχι','Ναι') NOT NULL,
  `vev` enum('Όχι','Ναι') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

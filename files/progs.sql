

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Table structure for table `progs`
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
  `eid1` enum('ΠΕ05','ΠΕ06','ΠΕ07','ΠΕ08','ΠΕ11','ΠΕ79','ΠΕ86','ΠΕ60','ΠΕ70','ΠΕ91','ΠΕ61','ΠΕ71','Άλλο') NOT NULL,
  `his1` enum('Όχι','Ναι') NOT NULL,
  `qua1` enum('Όχι','Ναι') NOT NULL,
  `nam2` text NOT NULL,
  `email2` text NOT NULL,
  `mob2` text NOT NULL,
  `eid2` enum('ΠΕ05','ΠΕ06','ΠΕ07','ΠΕ08','ΠΕ11','ΠΕ79','ΠΕ86','ΠΕ60','ΠΕ70','ΠΕ91','ΠΕ61','ΠΕ71','Άλλο') NOT NULL,
  `his2` enum('','Όχι','Ναι') NOT NULL,
  `qua2` enum('','Όχι','Ναι') NOT NULL,
  `nam3` text NOT NULL,
  `email3` text NOT NULL,
  `mob3` text NOT NULL,
  `eid3` enum('ΠΕ05','ΠΕ06','ΠΕ07','ΠΕ08','ΠΕ11','ΠΕ79','ΠΕ86','ΠΕ60','ΠΕ70','ΠΕ91','ΠΕ61','ΠΕ71','Άλλο') NOT NULL,
  `his3` enum('','Όχι','Ναι') NOT NULL,
  `qua3` enum('','Όχι','Ναι') NOT NULL,
  `titel` text NOT NULL,
  `categ` enum('Αγωγής Υγείας','Περιβαλλοντικής Εκπαίδευσης','Πολιτιστικών Θεμάτων') NOT NULL,
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
  `month` varchar(20) NOT NULL,
  `visit` text NOT NULL,
  `foreis` text NOT NULL,
  `pedia` text NOT NULL,
  `diax` set('Έντυπη έκδοση','Αναρτήσεις στο διαδίκτυο','Παραγωγή βίντεο','Αφίσες - φυλλάδια','Έκθεση κατασκευών - χειροτεχνιών - φωτογραφιών','Παρεμβάσεις','Άλλο') NOT NULL,
  `diax_other` text NOT NULL,
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

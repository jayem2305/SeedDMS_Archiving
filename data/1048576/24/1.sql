-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 20, 2025 at 11:31 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `seeddms_dbdoc`
--

-- --------------------------------------------------------

--
-- Table structure for table `tblattributedefinitions`
--

CREATE TABLE `tblattributedefinitions` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `objtype` tinyint(4) NOT NULL DEFAULT 0,
  `type` tinyint(4) NOT NULL DEFAULT 0,
  `multiple` tinyint(4) NOT NULL DEFAULT 0,
  `minvalues` int(11) NOT NULL DEFAULT 0,
  `maxvalues` int(11) NOT NULL DEFAULT 0,
  `valueset` text DEFAULT NULL,
  `regex` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `tblattributedefinitions`
--

INSERT INTO `tblattributedefinitions` (`id`, `name`, `objtype`, `type`, `multiple`, `minvalues`, `maxvalues`, `valueset`, `regex`) VALUES
(1, '1/eevyY9AQ1uYGTUHH5iUzPN93YCSk79MX9Zi3z8674=', 0, 1, 0, 3, 3, 'vNZeAb7dH7ybdXUODOZsPxvCRRoxRdPDAvtpmkExO6w=', 't2YY3PixTIc6Ieg5SuWmuZQlTqMqRkH2iLhI/lU+Wv4=');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tblattributedefinitions`
--
ALTER TABLE `tblattributedefinitions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tblattributedefinitions`
--
ALTER TABLE `tblattributedefinitions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

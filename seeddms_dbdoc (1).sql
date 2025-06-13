-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 02, 2025 at 03:25 PM
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
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` int(11) NOT NULL,
  `document_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL,
  `user` varchar(255) NOT NULL,
  `action` varchar(255) NOT NULL,
  `details` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `audit_logs`
--

INSERT INTO `audit_logs` (`id`, `document_id`, `created_at`, `user`, `action`, `details`) VALUES
(1, 3, '2025-05-30 09:00:00', 'admin', 'Document Viewed', 'User viewed the document details.'),
(2, 3, '2025-05-30 10:15:00', 'jdoe', 'File Uploaded', 'Uploaded file \"contract.pdf\".'),
(3, 3, '2025-05-30 11:30:00', 'asmith', 'Comment Added', 'Added a comment to the document.'),
(4, 3, '2025-05-29 22:08:49', 'admin', 'Document Viewed', 'User viewed the document details.'),
(5, 3, '2025-05-29 22:08:52', 'admin', 'Document Viewed', 'User viewed the document details.'),
(6, 17, '2025-05-29 22:09:10', 'admin', 'Document Viewed', 'User viewed the document details.'),
(7, 17, '2025-05-29 22:10:02', 'admin', 'Document Viewed', 'User \"admin\" viewed the document.'),
(8, 18, '2025-05-29 22:11:39', 'admin', 'Document Added', 'User added a new document.'),
(9, 18, '2025-05-29 22:11:41', 'admin', 'Document Viewed', 'User \"admin\" viewed the document.'),
(10, 18, '2025-05-29 22:13:25', 'admin', 'Document Viewed', 'User \"admin\" viewed the document.'),
(11, 18, '2025-05-29 22:13:27', 'admin', 'Document Downloaded', 'User downloaded a document version.'),
(12, 18, '2025-05-29 22:13:32', 'admin', 'Document Downloaded', 'User downloaded a document version.'),
(13, 17, '2025-05-29 22:13:39', 'admin', 'Document Downloaded', 'User downloaded a document version.'),
(14, 17, '2025-05-29 22:13:41', 'admin', 'Document Viewed', 'User \"admin\" viewed the document.'),
(15, 17, '2025-05-29 22:14:41', 'admin', 'Document Viewed', 'User \"admin\" viewed the document.'),
(16, 17, '2025-05-29 22:15:54', 'admin', 'Document Downloaded', 'admin downloaded the document.'),
(17, 17, '2025-05-29 22:15:55', 'admin', 'Document Viewed', 'User \"admin\" viewed the document.'),
(18, 17, '2025-05-29 22:16:22', 'admin', 'Document Viewed', 'User \"admin\" viewed the document.'),
(19, 17, '2025-05-29 22:16:27', 'admin', 'Document Downloaded', '<b>admin</b> downloaded the document.'),
(20, 17, '2025-05-29 22:16:28', 'admin', 'Document Viewed', 'User \"admin\" viewed the document.'),
(21, 17, '2025-05-29 22:16:34', 'admin', 'Document Viewed', 'User \"admin\" viewed the document.'),
(22, 17, '2025-05-29 22:16:42', 'admin', 'Document Viewed', 'User \"admin\" viewed the document.'),
(23, 17, '2025-05-29 22:16:46', 'admin', 'Document Viewed', 'User \"admin\" viewed the document.'),
(24, 17, '2025-05-29 22:17:02', 'admin', 'Document Viewed', 'User \"admin\" viewed the document.'),
(25, 17, '2025-05-29 22:17:10', 'admin', 'Document Downloaded', 'admin downloaded the document.'),
(26, 17, '2025-05-29 22:17:12', 'admin', 'Document Viewed', 'User \"admin\" viewed the document.'),
(27, 18, '2025-05-29 22:17:20', 'admin', 'Document Downloaded', 'admin downloaded the document.'),
(28, 18, '2025-05-29 22:17:21', 'admin', 'Document Viewed', 'User \"admin\" viewed the document.'),
(29, 18, '2025-05-29 22:18:45', 'admin', 'Document Viewed', 'User \"admin\" viewed the document.'),
(30, 17, '2025-05-29 22:19:33', 'admin', 'Document Downloaded', 'admin downloaded the document.'),
(31, 3, '2025-05-29 22:19:35', 'admin', 'Document Viewed', 'User \"admin\" viewed the document.'),
(32, 3, '2025-05-29 22:19:52', 'admin', 'Document Viewed', 'User \"admin\" viewed the document.'),
(33, 3, '2025-05-29 22:24:25', 'admin', 'Document Viewed', 'User \"admin\" viewed the document.'),
(34, 3, '2025-05-29 22:25:43', 'admin', 'Document Edited', 'User edited the document.'),
(35, 3, '2025-05-29 22:25:43', 'admin', 'Document Viewed', 'User \"admin\" viewed the document.'),
(36, 3, '2025-05-29 22:29:37', 'admin', 'Document Viewed', 'User \"admin\" viewed the document.'),
(37, 3, '2025-05-29 22:29:51', 'admin', 'Document Viewed', 'User \"admin\" viewed the document.'),
(38, 18, '2025-05-29 22:29:57', 'admin', 'Document Viewed', 'User \"admin\" viewed the document.'),
(39, 19, '2025-05-29 22:30:36', 'admin', 'Document Added', 'User added a new document.'),
(40, 19, '2025-05-29 22:30:38', 'admin', 'Document Viewed', 'User \"admin\" viewed the document.'),
(41, 3, '2025-05-30 08:54:18', 'admin', 'Document Viewed', 'User \"admin\" viewed the document.'),
(42, 3, '2025-05-30 08:54:27', 'admin', 'Document Viewed', 'User \"admin\" viewed the document.'),
(43, 3, '2025-05-30 08:59:17', 'admin', 'Document Viewed', 'User \"admin\" viewed the document.'),
(44, 3, '2025-05-30 09:02:30', 'admin', 'Document Viewed', 'User \"admin\" viewed the document.'),
(45, 3, '2025-05-30 09:02:40', 'admin', 'Document Viewed', 'User \"admin\" viewed the document.'),
(46, 3, '2025-05-30 09:02:46', 'admin', 'Document Viewed', 'User \"admin\" viewed the document.'),
(47, 3, '2025-05-30 09:02:52', 'admin', 'Document Viewed', 'User \"admin\" viewed the document.'),
(48, 3, '2025-05-30 09:02:57', 'admin', 'Document Viewed', 'User \"admin\" viewed the document.'),
(49, 3, '2025-05-30 09:07:05', 'admin', 'Document Viewed', 'User \"admin\" viewed the document.'),
(50, 3, '2025-05-30 09:07:16', 'admin', 'Document Viewed', 'User \"admin\" viewed the document.'),
(51, 3, '2025-05-30 09:08:20', 'admin', 'Document Viewed', 'User \"admin\" viewed the document.'),
(52, 3, '2025-05-30 09:16:31', 'admin', 'Document Viewed', 'User \"admin\" viewed the document.'),
(53, 18, '2025-05-30 09:27:08', 'admin', 'Document Viewed', 'User \"admin\" viewed the document.'),
(54, 18, '2025-05-30 09:37:37', 'admin', 'Document Viewed', 'User \"admin\" viewed the document.'),
(55, 18, '2025-05-30 09:51:20', 'admin', 'Document Viewed', 'User \"admin\" viewed the document.'),
(56, 18, '2025-05-30 09:51:45', 'admin', 'Document Viewed', 'User \"admin\" viewed the document.'),
(57, 18, '2025-05-30 09:51:45', 'admin', 'Document Viewed', 'User \"admin\" viewed the document.'),
(58, 18, '2025-05-30 09:51:45', 'admin', 'Document Viewed', 'User \"admin\" viewed the document.'),
(59, 17, '2025-05-30 10:31:01', 'admin', 'Document Downloaded', 'admin downloaded the document.');

-- --------------------------------------------------------

--
-- Table structure for table `tblacls`
--

CREATE TABLE `tblacls` (
  `id` int(11) NOT NULL,
  `target` int(11) NOT NULL DEFAULT 0,
  `targetType` tinyint(4) NOT NULL DEFAULT 0,
  `userID` int(11) NOT NULL DEFAULT -1,
  `groupID` int(11) NOT NULL DEFAULT -1,
  `mode` tinyint(4) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tblacos`
--

CREATE TABLE `tblacos` (
  `id` int(11) NOT NULL,
  `parent` int(11) DEFAULT NULL,
  `model` text NOT NULL,
  `foreignid` int(11) NOT NULL DEFAULT 0,
  `alias` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tblaros`
--

CREATE TABLE `tblaros` (
  `id` int(11) NOT NULL,
  `parent` int(11) DEFAULT NULL,
  `model` text NOT NULL,
  `foreignid` int(11) NOT NULL DEFAULT 0,
  `alias` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `tblaros`
--

INSERT INTO `tblaros` (`id`, `parent`, `model`, `foreignid`, `alias`) VALUES
(1, 0, 'Role', 5, NULL),
(2, 0, 'Role', 3, NULL),
(3, 0, 'Role', 2, NULL),
(4, 0, 'Role', 1, NULL),
(5, 0, 'Role', 8, NULL),
(6, 0, 'Role', 27, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `tblarosacos`
--

CREATE TABLE `tblarosacos` (
  `id` int(11) NOT NULL,
  `aro` int(11) NOT NULL DEFAULT 0,
  `aco` int(11) NOT NULL DEFAULT 0,
  `create` tinyint(4) NOT NULL DEFAULT -1,
  `read` tinyint(4) NOT NULL DEFAULT -1,
  `update` tinyint(4) NOT NULL DEFAULT -1,
  `delete` tinyint(4) NOT NULL DEFAULT -1
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

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
(1, '+9IJmG2jMfGrf1enSWnbyXclwHVDCgfVPvy2Xv+G7xE=', 1, 1, 0, 0, 0, 'Qzw82p3O1vl4B4CEYuQUNYzXjMrEKSyBFMB+2fY2yz4=', '73kL22Q1sXIrLlDL9WQj6JZxY8f1x9tEm3bUawxxzkw=');

-- --------------------------------------------------------

--
-- Table structure for table `tblcachedaccess`
--

CREATE TABLE `tblcachedaccess` (
  `id` int(11) NOT NULL,
  `document` int(11) DEFAULT NULL,
  `user` int(11) DEFAULT NULL,
  `mode` tinyint(4) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tblcategory`
--

CREATE TABLE `tblcategory` (
  `id` int(11) NOT NULL,
  `name` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `tblcategory`
--

INSERT INTO `tblcategory` (`id`, `name`) VALUES
(1, 'oFLOwwEAAdoMfNMSh+wNKZA5lYAyCyUKAOmg2qoVvMM=');

-- --------------------------------------------------------

--
-- Table structure for table `tbldocumentapprovelog`
--

CREATE TABLE `tbldocumentapprovelog` (
  `approveLogID` int(11) NOT NULL,
  `approveID` int(11) NOT NULL DEFAULT 0,
  `status` tinyint(4) NOT NULL DEFAULT 0,
  `comment` text NOT NULL,
  `date` datetime NOT NULL,
  `userID` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbldocumentapprovers`
--

CREATE TABLE `tbldocumentapprovers` (
  `approveID` int(11) NOT NULL,
  `documentID` int(11) NOT NULL DEFAULT 0,
  `version` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `type` tinyint(4) NOT NULL DEFAULT 0,
  `required` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbldocumentattributes`
--

CREATE TABLE `tbldocumentattributes` (
  `id` int(11) NOT NULL,
  `document` int(11) DEFAULT NULL,
  `attrdef` int(11) DEFAULT NULL,
  `value` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbldocumentcategory`
--

CREATE TABLE `tbldocumentcategory` (
  `categoryID` int(11) NOT NULL DEFAULT 0,
  `documentID` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbldocumentcheckouts`
--

CREATE TABLE `tbldocumentcheckouts` (
  `document` int(11) NOT NULL DEFAULT 0,
  `version` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `userID` int(11) NOT NULL DEFAULT 0,
  `date` datetime NOT NULL,
  `filename` varchar(255) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbldocumentcontent`
--

CREATE TABLE `tbldocumentcontent` (
  `id` int(11) NOT NULL,
  `document` int(11) NOT NULL DEFAULT 0,
  `version` smallint(5) UNSIGNED NOT NULL,
  `comment` text DEFAULT NULL,
  `date` int(12) DEFAULT NULL,
  `createdBy` int(11) DEFAULT NULL,
  `dir` varchar(255) NOT NULL DEFAULT '',
  `orgFileName` varchar(150) NOT NULL DEFAULT '',
  `fileType` varchar(10) NOT NULL DEFAULT '',
  `mimeType` varchar(100) NOT NULL DEFAULT '',
  `fileSize` bigint(20) DEFAULT NULL,
  `checksum` char(32) DEFAULT NULL,
  `revisiondate` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `tbldocumentcontent`
--

INSERT INTO `tbldocumentcontent` (`id`, `document`, `version`, `comment`, `date`, `createdBy`, `dir`, `orgFileName`, `fileType`, `mimeType`, `fileSize`, `checksum`, `revisiondate`) VALUES
(3, 3, 1, '9dAh2IIF74gOb3Hsk/DPA15gvzusozuXce8b7zGZqCA=', 1745367177, 1, '3\\', 'Xb5iEelSXgSGggPZyAlQxypyCOj21v8+glM9YApOJMKqusJGjzZgiivIFPjXvs6t', '.png', 'image/png', 228680, '97e402e4ed61778a5c703b5d8e1e682b', NULL),
(23, 17, 1, '', 1748548870, 1, '17\\', 'UKsjX9/pgpGos9KA4pVPwZHalglnw+tAkyg8FuPK183859Uw4qHPgpgAArW/0nUmid/ZokRu4BGedgmw/Lds3P83CR3zX6oeCXG9eeO7oUgb9wNEUraEPFxjrZj98o08', '.docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 913283, 'fd3a0e818c4be0558042304b4d4dcfb1', NULL),
(24, 18, 1, '', 1748549499, 1, '18\\', 'esi5L8K+u2HnFCaBI5jq7g2MxQDz4g2QaD7ZZ2Qn67cLAeDi1gumjW/PmWjPXVmL1PnK9Azg9en6Y+NM4dkCVA==', '.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 58823, 'cdad97d98114bc87330e4c79c0a53608', NULL),
(25, 19, 1, '', 1748550636, 1, '19\\', 'RWMTj7Q8QDEJvZqGK7nq6xi6Ocd8Q3YHBdcw5pC4EzE=', '.pdf', 'application/pdf', 871668, 'ce64ecb9d1c413c295c223abaabf7978', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `tbldocumentcontentattributes`
--

CREATE TABLE `tbldocumentcontentattributes` (
  `id` int(11) NOT NULL,
  `content` int(11) DEFAULT NULL,
  `attrdef` int(11) DEFAULT NULL,
  `value` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbldocumentfiles`
--

CREATE TABLE `tbldocumentfiles` (
  `id` int(11) NOT NULL,
  `document` int(11) NOT NULL DEFAULT 0,
  `version` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `public` tinyint(1) NOT NULL DEFAULT 0,
  `userID` int(11) NOT NULL DEFAULT 0,
  `comment` text DEFAULT NULL,
  `name` varchar(150) DEFAULT NULL,
  `date` int(12) DEFAULT NULL,
  `dir` varchar(255) NOT NULL DEFAULT '',
  `orgFileName` varchar(150) NOT NULL DEFAULT '',
  `fileType` varchar(10) NOT NULL DEFAULT '',
  `mimeType` varchar(100) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `tbldocumentfiles`
--

INSERT INTO `tbldocumentfiles` (`id`, `document`, `version`, `public`, `userID`, `comment`, `name`, `date`, `dir`, `orgFileName`, `fileType`, `mimeType`) VALUES
(5, 3, 0, 0, 1, '', 'BET-22-ACTIVITY-7-2025-Single-Phase-Electric-Motor-Rewinding.docx', 1748550582, '3\\', 'BET-22-ACTIVITY-7-2025-Single-Phase-Electric-Motor-Rewinding.docx', '.docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'),
(6, 18, 0, 0, 1, '', 'BET 22 ACTIVITY 7 ESPARTINEZ.pdf', 1748550605, '18\\', 'BET 22 ACTIVITY 7 ESPARTINEZ.pdf', '.pdf', 'application/pdf'),
(7, 3, 0, 0, 1, '', 'pat cv.pdf', 1748588934, '3\\', 'pat cv.pdf', '.pdf', 'application/pdf');

-- --------------------------------------------------------

--
-- Table structure for table `tbldocumentlinks`
--

CREATE TABLE `tbldocumentlinks` (
  `id` int(11) NOT NULL,
  `document` int(11) NOT NULL DEFAULT 0,
  `target` int(11) NOT NULL DEFAULT 0,
  `userID` int(11) NOT NULL DEFAULT 0,
  `public` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbldocumentlocks`
--

CREATE TABLE `tbldocumentlocks` (
  `document` int(11) NOT NULL DEFAULT 0,
  `userID` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbldocumentreceiptlog`
--

CREATE TABLE `tbldocumentreceiptlog` (
  `receiptLogID` int(11) NOT NULL,
  `receiptID` int(11) NOT NULL DEFAULT 0,
  `status` tinyint(4) NOT NULL DEFAULT 0,
  `comment` text NOT NULL,
  `date` datetime NOT NULL,
  `userID` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbldocumentrecipients`
--

CREATE TABLE `tbldocumentrecipients` (
  `receiptID` int(11) NOT NULL,
  `documentID` int(11) NOT NULL DEFAULT 0,
  `version` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `type` tinyint(4) NOT NULL DEFAULT 0,
  `required` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbldocumentreviewers`
--

CREATE TABLE `tbldocumentreviewers` (
  `reviewID` int(11) NOT NULL,
  `documentID` int(11) NOT NULL DEFAULT 0,
  `version` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `type` tinyint(4) NOT NULL DEFAULT 0,
  `required` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbldocumentreviewlog`
--

CREATE TABLE `tbldocumentreviewlog` (
  `reviewLogID` int(11) NOT NULL,
  `reviewID` int(11) NOT NULL DEFAULT 0,
  `status` tinyint(4) NOT NULL DEFAULT 0,
  `comment` text NOT NULL,
  `date` datetime NOT NULL,
  `userID` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbldocumentrevisionlog`
--

CREATE TABLE `tbldocumentrevisionlog` (
  `revisionLogID` int(11) NOT NULL,
  `revisionID` int(11) NOT NULL DEFAULT 0,
  `status` tinyint(4) NOT NULL DEFAULT 0,
  `comment` text NOT NULL,
  `date` datetime NOT NULL,
  `userID` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbldocumentrevisors`
--

CREATE TABLE `tbldocumentrevisors` (
  `revisionID` int(11) NOT NULL,
  `documentID` int(11) NOT NULL DEFAULT 0,
  `version` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `type` tinyint(4) NOT NULL DEFAULT 0,
  `required` int(11) NOT NULL DEFAULT 0,
  `startdate` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbldocuments`
--

CREATE TABLE `tbldocuments` (
  `id` int(11) NOT NULL,
  `name` varchar(150) DEFAULT NULL,
  `comment` text DEFAULT NULL,
  `date` int(12) DEFAULT NULL,
  `expires` int(12) DEFAULT NULL,
  `owner` int(11) DEFAULT NULL,
  `folder` int(11) DEFAULT NULL,
  `folderList` text NOT NULL,
  `inheritAccess` tinyint(1) NOT NULL DEFAULT 1,
  `defaultAccess` tinyint(4) NOT NULL DEFAULT 0,
  `locked` int(11) NOT NULL DEFAULT -1,
  `keywords` text NOT NULL,
  `sequence` double NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `tbldocuments`
--

INSERT INTO `tbldocuments` (`id`, `name`, `comment`, `date`, `expires`, `owner`, `folder`, `folderList`, `inheritAccess`, `defaultAccess`, `locked`, `keywords`, `sequence`) VALUES
(3, 'O2k0Eqc6A6KJPeGfnoToolMPVSOKR24Gph1CgX7+0Gzi8Ut2nJO486SOmOMR7hkX', 'GPopJ3SxRcuxeUcHDV5vtxYNM0LaHhslTzayFKGTspw=', 1745367177, 0, 1, 1, ':1:', 1, 2, -1, 'JKZmrya+h7dSPpgn0c2UaeGawoQfTm3d6DDhbELwL2o=', 108.2),
(17, 'd6grkHCOhon8t/6PUmYK9M/4/GjgUb/6WAqlK8lZNuVg7R38hyKFOl4biSA2B3NbQHYRc0AgMIiyhF9sHaBE3E46KxarYiYerebFHnephq30G0WtPokoz8uMPzu/qC62', 'GUKcZxzRKXE4d/1S+5F7LyhgxTR1AyY3qiwFtc3dqeA=', 1748548870, 0, 1, 1, ':1:', 1, 2, -1, 'eymUvmjVPj5ffE+ZPfrE2B1j4v87d3JGGiC4hc3f9/M=', 147.5),
(18, 'eobR8SxeXIF1aUQkQ6d9JXQ9WASqaRsLOyp9MzYuEl6FR0Dx+Hn465Y5pAMvIntPenDdoHr6T+g6s8mdekjoGw==', '5HvlHNQEHYCmbyKJC5/hLYRzfyWH0XSkRcXXM76A3hA=', 1748549499, 0, 1, 1, ':1:', 1, 2, -1, 'wSJQE0QXZa8PyyVKYIRNQuyQN2Fjd7CLQK/JHaEox3g=', 170.1),
(19, 'sH8yIpgPkeSFT94pIfdp6vqURJSxDBpTRfCcKnQVPJg=', 'rbjb5Y80e+cC/Oazge79XKtc1Bpv2IjDxD8DLGWAMTM=', 1748550636, 0, 1, 7, ':1:7:', 1, 2, -1, 'gZyLb7RUgtgTU8AqDPFEISpJYcI8zG5JGmEDICxXsrQ=', 37.4);

-- --------------------------------------------------------

--
-- Table structure for table `tbldocumentstatus`
--

CREATE TABLE `tbldocumentstatus` (
  `statusID` int(11) NOT NULL,
  `documentID` int(11) NOT NULL DEFAULT 0,
  `version` smallint(5) UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `tbldocumentstatus`
--

INSERT INTO `tbldocumentstatus` (`statusID`, `documentID`, `version`) VALUES
(3, 3, 1),
(23, 17, 1),
(24, 18, 1),
(25, 19, 1);

-- --------------------------------------------------------

--
-- Table structure for table `tbldocumentstatuslog`
--

CREATE TABLE `tbldocumentstatuslog` (
  `statusLogID` int(11) NOT NULL,
  `statusID` int(11) NOT NULL DEFAULT 0,
  `status` tinyint(4) NOT NULL DEFAULT 0,
  `comment` text NOT NULL,
  `date` datetime NOT NULL,
  `userID` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `tbldocumentstatuslog`
--

INSERT INTO `tbldocumentstatuslog` (`statusLogID`, `statusID`, `status`, `comment`, `date`, `userID`) VALUES
(3, 3, 2, 'New document content submitted', '2025-04-23 08:12:57', 1),
(29, 23, 2, 'New document content submitted', '2025-05-30 04:01:10', 1),
(30, 24, 2, 'New document content submitted', '2025-05-30 04:11:39', 1),
(31, 25, 2, 'New document content submitted', '2025-05-30 04:30:36', 1);

-- --------------------------------------------------------

--
-- Table structure for table `tblevents`
--

CREATE TABLE `tblevents` (
  `id` int(11) NOT NULL,
  `name` varchar(150) DEFAULT NULL,
  `comment` text DEFAULT NULL,
  `start` int(12) DEFAULT NULL,
  `stop` int(12) DEFAULT NULL,
  `date` int(12) DEFAULT NULL,
  `userID` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tblfolderattributes`
--

CREATE TABLE `tblfolderattributes` (
  `id` int(11) NOT NULL,
  `folder` int(11) DEFAULT NULL,
  `attrdef` int(11) DEFAULT NULL,
  `value` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tblfolders`
--

CREATE TABLE `tblfolders` (
  `id` int(11) NOT NULL,
  `name` varchar(70) DEFAULT NULL,
  `parent` int(11) DEFAULT NULL,
  `folderList` text NOT NULL,
  `comment` text DEFAULT NULL,
  `date` int(12) DEFAULT NULL,
  `owner` int(11) DEFAULT NULL,
  `inheritAccess` tinyint(1) NOT NULL DEFAULT 1,
  `defaultAccess` tinyint(4) NOT NULL DEFAULT 0,
  `sequence` double NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `tblfolders`
--

INSERT INTO `tblfolders` (`id`, `name`, `parent`, `folderList`, `comment`, `date`, `owner`, `inheritAccess`, `defaultAccess`, `sequence`) VALUES
(1, 'DMS', 0, '', 'DMS root', 1744267009, 1, 0, 2, 0),
(7, 'kFpwEevuIyxRbZHm36KwJ2exAMf2OpFzSOOtbXKOzmM=', 1, ':1:', 'm237z5XWSjBDaeSFSGPgdRmZ9klUch8kw7HMvIsgXPo=', 1748549947, 1, 1, 2, 1);

-- --------------------------------------------------------

--
-- Table structure for table `tblgroupmembers`
--

CREATE TABLE `tblgroupmembers` (
  `groupID` int(11) NOT NULL DEFAULT 0,
  `userID` int(11) NOT NULL DEFAULT 0,
  `manager` smallint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tblgroups`
--

CREATE TABLE `tblgroups` (
  `id` int(11) NOT NULL,
  `name` varchar(50) DEFAULT NULL,
  `comment` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `tblgroups`
--

INSERT INTO `tblgroups` (`id`, `name`, `comment`) VALUES
(1, 'heyy', ''),
(2, '7CLk40hTSaCn0IsoDqzaitPOj/jXWzReravjubZCEv01HKAIOt', 'BGCDq3pYNfusS4D4R4c81uIkrJvn46Jx7LL3ay8Bokxy85XE9oY9r0tmkEJjbFhHbJylegQB+f1OiF2Z3oM57A=='),
(3, 'yfC8KNZ6Dvxci21kWs2lh+StSzISiqIY2TomgzTPMkJGwYQQ8l', 'xQL2XAyNiMKIIQkFWJo2yQv1emRcQV/mB3Eq6Lu1Vpc9YdJ7hbpSUr7ta76gM3Wz261IQEM46RPFkOnQb0upFw=='),
(4, 'h2VED9mD9WfkJuPqObobrTFtPil7Uj1dBjeuhS4cetDeU1uwIQ', 'CoPsA+nAZq36UWGv/TLTVnSwhUTDz0qvTgT7qzEC097BVr2vPTXJgmFweU+BKTu/4nUTMsN+kVZL5yP9b8Clmw=='),
(5, 'QRAXFozzfItUBhXZMrM0wm6ptJrh/gaE8LMCpNmse6m3vX2VYb', 'PuORqJQBO9TTspqh84TKqXopCdb7j2vasju7/NKtflN+h8Xv3V8+yR4qnTVGDaHVjx0VnUh53hNqbtL+dlkbtQ==');

-- --------------------------------------------------------

--
-- Table structure for table `tblkeywordcategories`
--

CREATE TABLE `tblkeywordcategories` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL DEFAULT '',
  `owner` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `tblkeywordcategories`
--

INSERT INTO `tblkeywordcategories` (`id`, `name`, `owner`) VALUES
(1, '0jsY8BJDpOr+z4yzqAmNHkfHPiOXPgZGAG1QSjvLMoQ=', 1);

-- --------------------------------------------------------

--
-- Table structure for table `tblkeywords`
--

CREATE TABLE `tblkeywords` (
  `id` int(11) NOT NULL,
  `category` int(11) NOT NULL DEFAULT 0,
  `keywords` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `tblkeywords`
--

INSERT INTO `tblkeywords` (`id`, `category`, `keywords`) VALUES
(2, 1, 'Bfk/pCqWQ9jLnWQeQ3iqP9rzhcTCZHbLvAvJzy4tR3I='),
(3, 1, 'bO1OUFJXXB7h2i5cKsxkaX2OB8su+9YubUOf9L1jnPA=');

-- --------------------------------------------------------

--
-- Table structure for table `tblmandatoryapprovers`
--

CREATE TABLE `tblmandatoryapprovers` (
  `userID` int(11) NOT NULL DEFAULT 0,
  `approverUserID` int(11) NOT NULL DEFAULT 0,
  `approverGroupID` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tblmandatoryreviewers`
--

CREATE TABLE `tblmandatoryreviewers` (
  `userID` int(11) NOT NULL DEFAULT 0,
  `reviewerUserID` int(11) NOT NULL DEFAULT 0,
  `reviewerGroupID` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tblnotify`
--

CREATE TABLE `tblnotify` (
  `target` int(11) NOT NULL DEFAULT 0,
  `targetType` int(11) NOT NULL DEFAULT 0,
  `userID` int(11) NOT NULL DEFAULT -1,
  `groupID` int(11) NOT NULL DEFAULT -1
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tblroles`
--

CREATE TABLE `tblroles` (
  `id` int(11) NOT NULL,
  `name` varchar(50) DEFAULT NULL,
  `role` smallint(1) NOT NULL DEFAULT 0,
  `noaccess` varchar(30) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `tblroles`
--

INSERT INTO `tblroles` (`id`, `name`, `role`, `noaccess`) VALUES
(1, 'Admin', 1, ''),
(2, 'Guest', 2, ''),
(3, 'User', 0, ''),
(5, 'test', 0, ''),
(8, 'A6ztNOTPGt4QooLN+aSS0nsSFSevYRDhE4etpdeaxQM=', 0, ''),
(10, '/ZuTrBkmQEpTaf5qIB2w2sMSAQpoSH7ACQl8l9uit9k=', 0, ''),
(11, 'PAOqHgM9im7I9dr2RwSQH8E+ABQ2HsIQJcVSQfUkzC0=', 0, ''),
(13, 'CkkuXQ8bWZFmxBBDlILFp/qeHIzGKIKJoLfXHUwsufw=', 0, ''),
(14, 'M2bvuqhB0S/8y8PvS7jVgbgmpHTDC5d+j0fDSIhv9rdNsq8SzO', 0, ''),
(15, 'SiMsw7/wCWT8633HK3yJvbIEUnF62ubMzD+W4FwDQEYUV21Mbc', 0, ''),
(16, 'TnTPtOxWHX5DbgalgFQXRHduez64yb0AauvzvgdNPNW9T0i1Hz', 8, ''),
(17, 'TDrwYuLTpXtE9ZQBrgv84idjGyY8CX6pz+MSCIQl4GE=', 0, ''),
(18, 'ODXYXaxVBi24SC2/ah5iOdaDV+9APWnbN+PwPUzXigY=', 0, ''),
(19, 'DrEzZNsJ9qX+IXXR/EIXpw4a/ePtakm+G8XjbNo5nIi2ow4Ndr', 0, ''),
(20, '08kclyafJp3devJcQW0KPC96f9sYM0cw6zy3PVj+OXo=', 0, ''),
(21, 'UFIebX3sI80X44l94bnSTUjoLvZIeUmzCg46FeQT/EJ64/nHOR', 0, ''),
(22, '0yJew6odI0FY/yPRI6bAfUEX4iLNdLvKctf3xs01c0M=', 0, ''),
(23, 'qTH+KWF45tkSSzasW6wBHVexgBm25H9VjjkHIs8Tb1w=', 0, ''),
(24, '9L+ff8vtq6A5LxCMWdj0oyi1RkIj4PE3pfQOpLgZFW0=', 0, ''),
(25, 'S+8qmnqDZ7H5VFrB2oioORyjC29Pdu+hslcqxlP0qPI=', 0, ''),
(26, 'mXZBl2NHY/fy5pLWOf1ZgXWqlXwA/lzvkyba4xFlO8k=', 0, ''),
(27, '0L427FbCTmvjDLQVzG7/DP60If6Dw4+X7jbLv7n24V4=', 0, ''),
(29, 'wzVQ7YULyDSjVxjWmaZEj3LJiEO8msT99jHgUBOTkMRceNmJEf', 0, '');

-- --------------------------------------------------------

--
-- Table structure for table `tblschedulertask`
--

CREATE TABLE `tblschedulertask` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `disabled` smallint(1) NOT NULL DEFAULT 0,
  `extension` varchar(100) DEFAULT NULL,
  `task` varchar(100) DEFAULT NULL,
  `frequency` varchar(100) DEFAULT NULL,
  `params` text DEFAULT NULL,
  `nextrun` datetime DEFAULT NULL,
  `lastrun` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tblsessions`
--

CREATE TABLE `tblsessions` (
  `id` varchar(50) NOT NULL DEFAULT '',
  `userID` int(11) NOT NULL DEFAULT 0,
  `lastAccess` int(11) NOT NULL DEFAULT 0,
  `theme` varchar(30) NOT NULL DEFAULT '',
  `language` varchar(30) NOT NULL DEFAULT '',
  `clipboard` text DEFAULT NULL,
  `su` int(11) DEFAULT NULL,
  `splashmsg` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `tblsessions`
--

INSERT INTO `tblsessions` (`id`, `userID`, `lastAccess`, `theme`, `language`, `clipboard`, `su`, `splashmsg`) VALUES
('7a63f9b20630471be7823210cba8ba2a', 1, 1748870663, 'bootstrap4', 'en_GB', NULL, 0, '');

-- --------------------------------------------------------

--
-- Table structure for table `tbltransmittalitems`
--

CREATE TABLE `tbltransmittalitems` (
  `id` int(11) NOT NULL,
  `transmittal` int(11) NOT NULL DEFAULT 0,
  `document` int(11) DEFAULT NULL,
  `version` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `date` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbltransmittals`
--

CREATE TABLE `tbltransmittals` (
  `id` int(11) NOT NULL,
  `name` text NOT NULL,
  `comment` text NOT NULL,
  `userID` int(11) NOT NULL DEFAULT 0,
  `date` datetime DEFAULT NULL,
  `public` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbluserimages`
--

CREATE TABLE `tbluserimages` (
  `id` int(11) NOT NULL,
  `userID` int(11) NOT NULL DEFAULT 0,
  `image` blob NOT NULL,
  `mimeType` varchar(100) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbluserpasswordhistory`
--

CREATE TABLE `tbluserpasswordhistory` (
  `id` int(11) NOT NULL,
  `userID` int(11) NOT NULL DEFAULT 0,
  `pwd` varchar(50) DEFAULT NULL,
  `date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbluserpasswordrequest`
--

CREATE TABLE `tbluserpasswordrequest` (
  `id` int(11) NOT NULL,
  `userID` int(11) NOT NULL DEFAULT 0,
  `hash` varchar(50) DEFAULT NULL,
  `date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tblusers`
--

CREATE TABLE `tblusers` (
  `id` int(11) NOT NULL,
  `login` varchar(50) DEFAULT NULL,
  `pwd` varchar(50) DEFAULT NULL,
  `secret` varchar(50) DEFAULT NULL,
  `fullName` varchar(100) DEFAULT NULL,
  `email` varchar(70) DEFAULT NULL,
  `language` varchar(32) NOT NULL,
  `theme` varchar(32) NOT NULL,
  `comment` text NOT NULL,
  `role` int(11) NOT NULL,
  `hidden` smallint(1) NOT NULL DEFAULT 0,
  `pwdExpiration` datetime DEFAULT NULL,
  `loginfailures` tinyint(4) NOT NULL DEFAULT 0,
  `disabled` smallint(1) NOT NULL DEFAULT 0,
  `quota` bigint(20) DEFAULT NULL,
  `homefolder` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `tblusers`
--

INSERT INTO `tblusers` (`id`, `login`, `pwd`, `secret`, `fullName`, `email`, `language`, `theme`, `comment`, `role`, `hidden`, `pwdExpiration`, `loginfailures`, `disabled`, `quota`, `homefolder`) VALUES
(1, 'admin', '21232f297a57a5a743894a0e4a801fc3', '', 'Administrator', 'info@seeddms.org', 'en_GB', 'bootstrap4', '', 1, 0, NULL, 0, 0, 0, NULL),
(2, 'guest', NULL, '', 'Guest User', NULL, '', '', '', 2, 0, NULL, 0, 0, 0, NULL),
(3, '7JYdKygi4k8NOijBd8C6Iq4ClJL+TDUz9f6q2/2yk9Q=', '161ebd7d45089b3446ee4e0d86dbcf92', NULL, 'BsLaBiQMuc97BwXi43aQSZW+YUBjjNx+ftL1Xj8NBSE=', 'XElZ9qA+6JORTCfzvmKBwr/l23Q7UmNpr+ZraQvo5Pc=', 'en_GB', 'bootstrap4', 'M5zC+IhJisOF5KKpQC2T/ithY5JKIlKqcMqvnsgtTrs=', 3, 0, NULL, 0, 0, 0, NULL),
(4, 'usertest', '964d72e72d053d501f2949969849b96c', NULL, 'User Test', 'usertest@gmail.com', 'en_GB', 'bootstrap4', '7NfucBin4KUrPRarHtGIBQ10ZJ/cQDq72yoyqUV5kGU=', 3, 0, NULL, 0, 0, 0, NULL),
(5, 'rZc8cyBfgtodQxvgrp8TrMKbDDGp3GC8IyMdYUE77wU=', NULL, NULL, 'SypwChM1hcxjc5BlZrIyEVG0EPhAne+8pSNRERoy5CI=', 'MBZ2Ol3P6P72MrPGeKR32oQiIb7ACWdt1wpTvTHl/DOKxjfJ1BiJVwPTIntRynVb', 'en_GB', 'bootstrap4', 'tE/oM5Tq0jBv+yBeFl7+L8jcf7TRVR0RM0TjcxohZmA=', 1, 0, NULL, 0, 0, 0, NULL),
(6, 'adaaa', 'wZVgayZXy+xRil2elDQ7wcSWFWC6Hi3TJS5rjzDuhXEAgfLjeh', NULL, 'ada', 'adasda@gmail.com', 'en_GB', 'bootstrap4', '', 1, 0, NULL, 0, 0, 0, NULL),
(7, 'iApFpFHuWPSIwLSger0vvthUQ3KXCA1zw3jI92FyFY0=', '34ec78fcc91ffb1e54cd85e4a0924332', NULL, 'eq4Cb0Eq895Z7SkbR1qW5cLXPbJjiwj/3jwNyHVs5dg=', 'sf0BDtbiYJP+5Tqz1BCwwdTDg+AG/mLl6oMQ5Du8ABw=', 'en_GB', 'bootstrap4', 'xP7uFXVXGyRpsgQ0l8Z9n6PRNITEeU4oekeomjoVLl0=', 1, 0, NULL, 0, 0, 0, NULL),
(8, 'eiAZFKAl9COzCT2yFSbhZFwd0xpvq2ZPMYD+c7FBToc=', '1a1dc91c907325c69271ddf0c944bc72', NULL, 'qv+dahlU7yq3586n72Jl0aAeWeBuGAGLBFgK9/mFIaY=', 'MRznzi1CbD0pPfy6XvJsmV50dwpe68UetZrgxixyeS0=', 'en_GB', 'bootstrap4', 'q/OY+LRujjsKGKvR64AeSYO1DsL3ZlJsthD2QVfDj6o=', 1, 0, NULL, 0, 0, 0, NULL),
(9, 'PfA0Yz93UXiCP2tcB2SkJdhCOLvlmaIxh1T0BoqN924=', '1ee9cb572c30a8f27ebec15c193d4617', NULL, 'NL9lBRa1Ph4y53GPNyUBDkyKlee6EDyOTmIIFMwze7E=', 'mGGsXp2Nw9KmjJg0eWWKF+FYNA1dS0hMtQJgF4h1j1k=', 'en_GB', 'bootstrap4', 'v+5bY4fHtnK6iEUwMg+WjN0AxoS9zIpOyZ9MGkZ/aE0=', 1, 0, NULL, 0, 0, 0, NULL),
(10, 'HgNCmDS12w7yIyLSqktZmv55m5MrxiPZL+K9ZpYIIWI=', '5ca2aa845c8cd5ace6b016841f100d82', NULL, '+LJyGfgy3lvxN8NzVh66tOQMBJlkLP3eZwO6sNKXeeM=', 'Qo6h5peYVxHalEpxE1QtDDj6R1KFMsDuWFaHkKvfKCo=', 'en_GB', 'bootstrap4', 'NGInX/wuL5dpVoTFUplofbUAP0Y27UkDYmR/ssKiXJw=', 1, 0, NULL, 0, 0, 0, NULL),
(11, 'aaa', '47bce5c74f589f4867dbd57e9ca9f808', NULL, 'dO4izgLN1Ut/xNFbsSQEC8clgXS8xz8Cd4xTiSnW+0M=', 'tFrEfhEPU35a+Fuu3feBLMWpIk8+yWx7RKO/C5KhQsw=', 'en_GB', 'bootstrap4', '', 1, 0, NULL, 0, 0, 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `tblusersubstitutes`
--

CREATE TABLE `tblusersubstitutes` (
  `id` int(11) NOT NULL,
  `user` int(11) DEFAULT NULL,
  `substitute` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tblversion`
--

CREATE TABLE `tblversion` (
  `date` datetime NOT NULL,
  `major` smallint(6) DEFAULT NULL,
  `minor` smallint(6) DEFAULT NULL,
  `subminor` smallint(6) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `tblversion`
--

INSERT INTO `tblversion` (`date`, `major`, `minor`, `subminor`) VALUES
('2025-04-10 14:36:49', 6, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `tblworkflowactions`
--

CREATE TABLE `tblworkflowactions` (
  `id` int(11) NOT NULL,
  `name` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tblworkflowdocumentcontent`
--

CREATE TABLE `tblworkflowdocumentcontent` (
  `id` int(11) NOT NULL,
  `parent` int(11) DEFAULT NULL,
  `workflow` int(11) DEFAULT NULL,
  `document` int(11) DEFAULT NULL,
  `version` smallint(5) DEFAULT NULL,
  `state` int(11) DEFAULT NULL,
  `date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tblworkflowlog`
--

CREATE TABLE `tblworkflowlog` (
  `id` int(11) NOT NULL,
  `workflowdocumentcontent` int(11) NOT NULL DEFAULT 0,
  `userid` int(11) DEFAULT NULL,
  `transition` int(11) DEFAULT NULL,
  `date` datetime NOT NULL,
  `comment` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tblworkflowmandatoryworkflow`
--

CREATE TABLE `tblworkflowmandatoryworkflow` (
  `userid` int(11) DEFAULT NULL,
  `workflow` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tblworkflows`
--

CREATE TABLE `tblworkflows` (
  `id` int(11) NOT NULL,
  `name` text NOT NULL,
  `initstate` int(11) NOT NULL,
  `layoutdata` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tblworkflowstates`
--

CREATE TABLE `tblworkflowstates` (
  `id` int(11) NOT NULL,
  `name` text NOT NULL,
  `visibility` smallint(5) DEFAULT 0,
  `maxtime` int(11) DEFAULT 0,
  `precondfunc` text DEFAULT NULL,
  `documentstatus` smallint(5) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tblworkflowtransitiongroups`
--

CREATE TABLE `tblworkflowtransitiongroups` (
  `id` int(11) NOT NULL,
  `transition` int(11) DEFAULT NULL,
  `groupid` int(11) DEFAULT NULL,
  `minusers` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tblworkflowtransitions`
--

CREATE TABLE `tblworkflowtransitions` (
  `id` int(11) NOT NULL,
  `workflow` int(11) DEFAULT NULL,
  `state` int(11) DEFAULT NULL,
  `action` int(11) DEFAULT NULL,
  `nextstate` int(11) DEFAULT NULL,
  `maxtime` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tblworkflowtransitionusers`
--

CREATE TABLE `tblworkflowtransitionusers` (
  `id` int(11) NOT NULL,
  `transition` int(11) DEFAULT NULL,
  `userid` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tblacls`
--
ALTER TABLE `tblacls`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tblacos`
--
ALTER TABLE `tblacos`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tblaros`
--
ALTER TABLE `tblaros`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tblarosacos`
--
ALTER TABLE `tblarosacos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `aco` (`aco`,`aro`),
  ADD KEY `tblArosAcos_aros` (`aro`);

--
-- Indexes for table `tblattributedefinitions`
--
ALTER TABLE `tblattributedefinitions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `tblcachedaccess`
--
ALTER TABLE `tblcachedaccess`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tblCachedAccess_document` (`document`),
  ADD KEY `tblCachedAccess_user` (`user`);

--
-- Indexes for table `tblcategory`
--
ALTER TABLE `tblcategory`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbldocumentapprovelog`
--
ALTER TABLE `tbldocumentapprovelog`
  ADD PRIMARY KEY (`approveLogID`),
  ADD KEY `tblDocumentApproveLog_approve` (`approveID`),
  ADD KEY `tblDocumentApproveLog_user` (`userID`);

--
-- Indexes for table `tbldocumentapprovers`
--
ALTER TABLE `tbldocumentapprovers`
  ADD PRIMARY KEY (`approveID`),
  ADD UNIQUE KEY `documentID` (`documentID`,`version`,`type`,`required`),
  ADD KEY `indDocumentApproversRequired` (`required`);

--
-- Indexes for table `tbldocumentattributes`
--
ALTER TABLE `tbldocumentattributes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `document` (`document`,`attrdef`),
  ADD KEY `tblDocumentAttributes_attrdef` (`attrdef`);

--
-- Indexes for table `tbldocumentcategory`
--
ALTER TABLE `tbldocumentcategory`
  ADD KEY `tblDocumentCategory_category` (`categoryID`),
  ADD KEY `tblDocumentCategory_document` (`documentID`);

--
-- Indexes for table `tbldocumentcheckouts`
--
ALTER TABLE `tbldocumentcheckouts`
  ADD PRIMARY KEY (`document`),
  ADD KEY `tblDocumentCheckOuts_user` (`userID`);

--
-- Indexes for table `tbldocumentcontent`
--
ALTER TABLE `tbldocumentcontent`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `document` (`document`,`version`);

--
-- Indexes for table `tbldocumentcontentattributes`
--
ALTER TABLE `tbldocumentcontentattributes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `content` (`content`,`attrdef`),
  ADD KEY `tblDocumentContentAttributes_attrdef` (`attrdef`);

--
-- Indexes for table `tbldocumentfiles`
--
ALTER TABLE `tbldocumentfiles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tblDocumentFiles_document` (`document`),
  ADD KEY `tblDocumentFiles_user` (`userID`);

--
-- Indexes for table `tbldocumentlinks`
--
ALTER TABLE `tbldocumentlinks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tblDocumentLinks_document` (`document`),
  ADD KEY `tblDocumentLinks_target` (`target`),
  ADD KEY `tblDocumentLinks_user` (`userID`);

--
-- Indexes for table `tbldocumentlocks`
--
ALTER TABLE `tbldocumentlocks`
  ADD PRIMARY KEY (`document`),
  ADD KEY `tblDocumentLocks_user` (`userID`);

--
-- Indexes for table `tbldocumentreceiptlog`
--
ALTER TABLE `tbldocumentreceiptlog`
  ADD PRIMARY KEY (`receiptLogID`),
  ADD KEY `tblDocumentReceiptLog_receipt` (`receiptID`),
  ADD KEY `tblDocumentReceiptLog_user` (`userID`);

--
-- Indexes for table `tbldocumentrecipients`
--
ALTER TABLE `tbldocumentrecipients`
  ADD PRIMARY KEY (`receiptID`),
  ADD UNIQUE KEY `documentID` (`documentID`,`version`,`type`,`required`),
  ADD KEY `indDocumentRecipientsRequired` (`required`);

--
-- Indexes for table `tbldocumentreviewers`
--
ALTER TABLE `tbldocumentreviewers`
  ADD PRIMARY KEY (`reviewID`),
  ADD UNIQUE KEY `documentID` (`documentID`,`version`,`type`,`required`),
  ADD KEY `indDocumentReviewersRequired` (`required`);

--
-- Indexes for table `tbldocumentreviewlog`
--
ALTER TABLE `tbldocumentreviewlog`
  ADD PRIMARY KEY (`reviewLogID`),
  ADD KEY `tblDocumentReviewLog_review` (`reviewID`),
  ADD KEY `tblDocumentReviewLog_user` (`userID`);

--
-- Indexes for table `tbldocumentrevisionlog`
--
ALTER TABLE `tbldocumentrevisionlog`
  ADD PRIMARY KEY (`revisionLogID`),
  ADD KEY `tblDocumentRevisionLog_revision` (`revisionID`),
  ADD KEY `tblDocumentRevisionLog_user` (`userID`);

--
-- Indexes for table `tbldocumentrevisors`
--
ALTER TABLE `tbldocumentrevisors`
  ADD PRIMARY KEY (`revisionID`),
  ADD UNIQUE KEY `documentID` (`documentID`,`version`,`type`,`required`),
  ADD KEY `indDocumentRevisorsRequired` (`required`);

--
-- Indexes for table `tbldocuments`
--
ALTER TABLE `tbldocuments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tblDocuments_folder` (`folder`),
  ADD KEY `tblDocuments_owner` (`owner`);

--
-- Indexes for table `tbldocumentstatus`
--
ALTER TABLE `tbldocumentstatus`
  ADD PRIMARY KEY (`statusID`),
  ADD UNIQUE KEY `documentID` (`documentID`,`version`);

--
-- Indexes for table `tbldocumentstatuslog`
--
ALTER TABLE `tbldocumentstatuslog`
  ADD PRIMARY KEY (`statusLogID`),
  ADD KEY `statusID` (`statusID`),
  ADD KEY `tblDocumentStatusLog_user` (`userID`);

--
-- Indexes for table `tblevents`
--
ALTER TABLE `tblevents`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tblfolderattributes`
--
ALTER TABLE `tblfolderattributes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `folder` (`folder`,`attrdef`),
  ADD KEY `tblFolderAttributes_attrdef` (`attrdef`);

--
-- Indexes for table `tblfolders`
--
ALTER TABLE `tblfolders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `parent` (`parent`),
  ADD KEY `tblFolders_owner` (`owner`);

--
-- Indexes for table `tblgroupmembers`
--
ALTER TABLE `tblgroupmembers`
  ADD UNIQUE KEY `groupID` (`groupID`,`userID`),
  ADD KEY `tblGroupMembers_user` (`userID`);

--
-- Indexes for table `tblgroups`
--
ALTER TABLE `tblgroups`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tblkeywordcategories`
--
ALTER TABLE `tblkeywordcategories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tblkeywords`
--
ALTER TABLE `tblkeywords`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tblKeywords_category` (`category`);

--
-- Indexes for table `tblmandatoryapprovers`
--
ALTER TABLE `tblmandatoryapprovers`
  ADD PRIMARY KEY (`userID`,`approverUserID`,`approverGroupID`);

--
-- Indexes for table `tblmandatoryreviewers`
--
ALTER TABLE `tblmandatoryreviewers`
  ADD PRIMARY KEY (`userID`,`reviewerUserID`,`reviewerGroupID`);

--
-- Indexes for table `tblnotify`
--
ALTER TABLE `tblnotify`
  ADD PRIMARY KEY (`target`,`targetType`,`userID`,`groupID`);

--
-- Indexes for table `tblroles`
--
ALTER TABLE `tblroles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `tblschedulertask`
--
ALTER TABLE `tblschedulertask`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tblsessions`
--
ALTER TABLE `tblsessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tblSessions_user` (`userID`);

--
-- Indexes for table `tbltransmittalitems`
--
ALTER TABLE `tbltransmittalitems`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `transmittal` (`transmittal`,`document`,`version`),
  ADD KEY `tblTransmittalItems_document` (`document`);

--
-- Indexes for table `tbltransmittals`
--
ALTER TABLE `tbltransmittals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tblTransmittals_user` (`userID`);

--
-- Indexes for table `tbluserimages`
--
ALTER TABLE `tbluserimages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tblUserImages_user` (`userID`);

--
-- Indexes for table `tbluserpasswordhistory`
--
ALTER TABLE `tbluserpasswordhistory`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tblUserPasswordHistory_user` (`userID`);

--
-- Indexes for table `tbluserpasswordrequest`
--
ALTER TABLE `tbluserpasswordrequest`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tblUserPasswordRequest_user` (`userID`);

--
-- Indexes for table `tblusers`
--
ALTER TABLE `tblusers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `login` (`login`),
  ADD KEY `tblUsers_role` (`role`),
  ADD KEY `tblUsers_homefolder` (`homefolder`);

--
-- Indexes for table `tblusersubstitutes`
--
ALTER TABLE `tblusersubstitutes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user` (`user`,`substitute`),
  ADD KEY `tblUserSubstitutes_substitute` (`substitute`);

--
-- Indexes for table `tblworkflowactions`
--
ALTER TABLE `tblworkflowactions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tblworkflowdocumentcontent`
--
ALTER TABLE `tblworkflowdocumentcontent`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tblWorkflowDocument_document` (`document`),
  ADD KEY `tblWorkflowDocument_workflow` (`workflow`),
  ADD KEY `tblWorkflowDocument_state` (`state`),
  ADD KEY `tblWorkflowDocumentContent_parent` (`parent`);

--
-- Indexes for table `tblworkflowlog`
--
ALTER TABLE `tblworkflowlog`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tblWorkflowLog_userid` (`userid`),
  ADD KEY `tblWorkflowLog_transition` (`transition`),
  ADD KEY `tblWorkflowLog_workflowdocumentcontent` (`workflowdocumentcontent`);

--
-- Indexes for table `tblworkflowmandatoryworkflow`
--
ALTER TABLE `tblworkflowmandatoryworkflow`
  ADD UNIQUE KEY `userid` (`userid`,`workflow`),
  ADD KEY `tblWorkflowMandatoryWorkflow_workflow` (`workflow`);

--
-- Indexes for table `tblworkflows`
--
ALTER TABLE `tblworkflows`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tblWorkflow_initstate` (`initstate`);

--
-- Indexes for table `tblworkflowstates`
--
ALTER TABLE `tblworkflowstates`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tblworkflowtransitiongroups`
--
ALTER TABLE `tblworkflowtransitiongroups`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tblWorkflowTransitionGroups_transition` (`transition`),
  ADD KEY `tblWorkflowTransitionGroups_groupid` (`groupid`);

--
-- Indexes for table `tblworkflowtransitions`
--
ALTER TABLE `tblworkflowtransitions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tblWorkflowTransitions_workflow` (`workflow`),
  ADD KEY `tblWorkflowTransitions_state` (`state`),
  ADD KEY `tblWorkflowTransitions_action` (`action`),
  ADD KEY `tblWorkflowTransitions_nextstate` (`nextstate`);

--
-- Indexes for table `tblworkflowtransitionusers`
--
ALTER TABLE `tblworkflowtransitionusers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tblWorkflowTransitionUsers_transition` (`transition`),
  ADD KEY `tblWorkflowTransitionUsers_userid` (`userid`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=60;

--
-- AUTO_INCREMENT for table `tblacls`
--
ALTER TABLE `tblacls`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tblacos`
--
ALTER TABLE `tblacos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tblaros`
--
ALTER TABLE `tblaros`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `tblarosacos`
--
ALTER TABLE `tblarosacos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tblattributedefinitions`
--
ALTER TABLE `tblattributedefinitions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tblcachedaccess`
--
ALTER TABLE `tblcachedaccess`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tblcategory`
--
ALTER TABLE `tblcategory`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tbldocumentapprovelog`
--
ALTER TABLE `tbldocumentapprovelog`
  MODIFY `approveLogID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tbldocumentapprovers`
--
ALTER TABLE `tbldocumentapprovers`
  MODIFY `approveID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tbldocumentattributes`
--
ALTER TABLE `tbldocumentattributes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbldocumentcontent`
--
ALTER TABLE `tbldocumentcontent`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `tbldocumentcontentattributes`
--
ALTER TABLE `tbldocumentcontentattributes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbldocumentfiles`
--
ALTER TABLE `tbldocumentfiles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `tbldocumentlinks`
--
ALTER TABLE `tbldocumentlinks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbldocumentreceiptlog`
--
ALTER TABLE `tbldocumentreceiptlog`
  MODIFY `receiptLogID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbldocumentrecipients`
--
ALTER TABLE `tbldocumentrecipients`
  MODIFY `receiptID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbldocumentreviewers`
--
ALTER TABLE `tbldocumentreviewers`
  MODIFY `reviewID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tbldocumentreviewlog`
--
ALTER TABLE `tbldocumentreviewlog`
  MODIFY `reviewLogID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tbldocumentrevisionlog`
--
ALTER TABLE `tbldocumentrevisionlog`
  MODIFY `revisionLogID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbldocumentrevisors`
--
ALTER TABLE `tbldocumentrevisors`
  MODIFY `revisionID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbldocuments`
--
ALTER TABLE `tbldocuments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `tbldocumentstatus`
--
ALTER TABLE `tbldocumentstatus`
  MODIFY `statusID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `tbldocumentstatuslog`
--
ALTER TABLE `tbldocumentstatuslog`
  MODIFY `statusLogID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `tblevents`
--
ALTER TABLE `tblevents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tblfolderattributes`
--
ALTER TABLE `tblfolderattributes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tblfolders`
--
ALTER TABLE `tblfolders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `tblgroups`
--
ALTER TABLE `tblgroups`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `tblkeywordcategories`
--
ALTER TABLE `tblkeywordcategories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tblkeywords`
--
ALTER TABLE `tblkeywords`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `tblroles`
--
ALTER TABLE `tblroles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `tblschedulertask`
--
ALTER TABLE `tblschedulertask`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbltransmittalitems`
--
ALTER TABLE `tbltransmittalitems`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbltransmittals`
--
ALTER TABLE `tbltransmittals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbluserimages`
--
ALTER TABLE `tbluserimages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbluserpasswordhistory`
--
ALTER TABLE `tbluserpasswordhistory`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbluserpasswordrequest`
--
ALTER TABLE `tbluserpasswordrequest`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tblusers`
--
ALTER TABLE `tblusers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `tblusersubstitutes`
--
ALTER TABLE `tblusersubstitutes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tblworkflowactions`
--
ALTER TABLE `tblworkflowactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tblworkflowdocumentcontent`
--
ALTER TABLE `tblworkflowdocumentcontent`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tblworkflowlog`
--
ALTER TABLE `tblworkflowlog`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tblworkflows`
--
ALTER TABLE `tblworkflows`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tblworkflowstates`
--
ALTER TABLE `tblworkflowstates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tblworkflowtransitiongroups`
--
ALTER TABLE `tblworkflowtransitiongroups`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tblworkflowtransitions`
--
ALTER TABLE `tblworkflowtransitions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tblworkflowtransitionusers`
--
ALTER TABLE `tblworkflowtransitionusers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `tblarosacos`
--
ALTER TABLE `tblarosacos`
  ADD CONSTRAINT `tblArosAcos_acos` FOREIGN KEY (`aco`) REFERENCES `tblacos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tblArosAcos_aros` FOREIGN KEY (`aro`) REFERENCES `tblaros` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tblcachedaccess`
--
ALTER TABLE `tblcachedaccess`
  ADD CONSTRAINT `tblCachedAccess_document` FOREIGN KEY (`document`) REFERENCES `tbldocuments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tblCachedAccess_user` FOREIGN KEY (`user`) REFERENCES `tblusers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tbldocumentapprovelog`
--
ALTER TABLE `tbldocumentapprovelog`
  ADD CONSTRAINT `tblDocumentApproveLog_approve` FOREIGN KEY (`approveID`) REFERENCES `tbldocumentapprovers` (`approveID`) ON DELETE CASCADE,
  ADD CONSTRAINT `tblDocumentApproveLog_user` FOREIGN KEY (`userID`) REFERENCES `tblusers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tbldocumentapprovers`
--
ALTER TABLE `tbldocumentapprovers`
  ADD CONSTRAINT `tblDocumentApprovers_document` FOREIGN KEY (`documentID`) REFERENCES `tbldocuments` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tbldocumentattributes`
--
ALTER TABLE `tbldocumentattributes`
  ADD CONSTRAINT `tblDocumentAttributes_attrdef` FOREIGN KEY (`attrdef`) REFERENCES `tblattributedefinitions` (`id`),
  ADD CONSTRAINT `tblDocumentAttributes_document` FOREIGN KEY (`document`) REFERENCES `tbldocuments` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tbldocumentcategory`
--
ALTER TABLE `tbldocumentcategory`
  ADD CONSTRAINT `tblDocumentCategory_category` FOREIGN KEY (`categoryID`) REFERENCES `tblcategory` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tblDocumentCategory_document` FOREIGN KEY (`documentID`) REFERENCES `tbldocuments` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tbldocumentcheckouts`
--
ALTER TABLE `tbldocumentcheckouts`
  ADD CONSTRAINT `tblDocumentCheckOuts_document` FOREIGN KEY (`document`) REFERENCES `tbldocuments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tblDocumentCheckOuts_user` FOREIGN KEY (`userID`) REFERENCES `tblusers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tbldocumentcontent`
--
ALTER TABLE `tbldocumentcontent`
  ADD CONSTRAINT `tblDocumentContent_document` FOREIGN KEY (`document`) REFERENCES `tbldocuments` (`id`);

--
-- Constraints for table `tbldocumentcontentattributes`
--
ALTER TABLE `tbldocumentcontentattributes`
  ADD CONSTRAINT `tblDocumentContentAttributes_attrdef` FOREIGN KEY (`attrdef`) REFERENCES `tblattributedefinitions` (`id`),
  ADD CONSTRAINT `tblDocumentContentAttributes_document` FOREIGN KEY (`content`) REFERENCES `tbldocumentcontent` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tbldocumentfiles`
--
ALTER TABLE `tbldocumentfiles`
  ADD CONSTRAINT `tblDocumentFiles_document` FOREIGN KEY (`document`) REFERENCES `tbldocuments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tblDocumentFiles_user` FOREIGN KEY (`userID`) REFERENCES `tblusers` (`id`);

--
-- Constraints for table `tbldocumentlinks`
--
ALTER TABLE `tbldocumentlinks`
  ADD CONSTRAINT `tblDocumentLinks_document` FOREIGN KEY (`document`) REFERENCES `tbldocuments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tblDocumentLinks_target` FOREIGN KEY (`target`) REFERENCES `tbldocuments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tblDocumentLinks_user` FOREIGN KEY (`userID`) REFERENCES `tblusers` (`id`);

--
-- Constraints for table `tbldocumentlocks`
--
ALTER TABLE `tbldocumentlocks`
  ADD CONSTRAINT `tblDocumentLocks_document` FOREIGN KEY (`document`) REFERENCES `tbldocuments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tblDocumentLocks_user` FOREIGN KEY (`userID`) REFERENCES `tblusers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tbldocumentreceiptlog`
--
ALTER TABLE `tbldocumentreceiptlog`
  ADD CONSTRAINT `tblDocumentReceiptLog_recipient` FOREIGN KEY (`receiptID`) REFERENCES `tbldocumentrecipients` (`receiptID`) ON DELETE CASCADE,
  ADD CONSTRAINT `tblDocumentReceiptLog_user` FOREIGN KEY (`userID`) REFERENCES `tblusers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tbldocumentrecipients`
--
ALTER TABLE `tbldocumentrecipients`
  ADD CONSTRAINT `tblDocumentRecipients_document` FOREIGN KEY (`documentID`) REFERENCES `tbldocuments` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tbldocumentreviewers`
--
ALTER TABLE `tbldocumentreviewers`
  ADD CONSTRAINT `tblDocumentReviewers_document` FOREIGN KEY (`documentID`) REFERENCES `tbldocuments` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tbldocumentreviewlog`
--
ALTER TABLE `tbldocumentreviewlog`
  ADD CONSTRAINT `tblDocumentReviewLog_review` FOREIGN KEY (`reviewID`) REFERENCES `tbldocumentreviewers` (`reviewID`) ON DELETE CASCADE,
  ADD CONSTRAINT `tblDocumentReviewLog_user` FOREIGN KEY (`userID`) REFERENCES `tblusers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tbldocumentrevisionlog`
--
ALTER TABLE `tbldocumentrevisionlog`
  ADD CONSTRAINT `tblDocumentRevisionLog_revision` FOREIGN KEY (`revisionID`) REFERENCES `tbldocumentrevisors` (`revisionID`) ON DELETE CASCADE,
  ADD CONSTRAINT `tblDocumentRevisionLog_user` FOREIGN KEY (`userID`) REFERENCES `tblusers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tbldocumentrevisors`
--
ALTER TABLE `tbldocumentrevisors`
  ADD CONSTRAINT `tblDocumentRevisors_document` FOREIGN KEY (`documentID`) REFERENCES `tbldocuments` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tbldocuments`
--
ALTER TABLE `tbldocuments`
  ADD CONSTRAINT `tblDocuments_folder` FOREIGN KEY (`folder`) REFERENCES `tblfolders` (`id`),
  ADD CONSTRAINT `tblDocuments_owner` FOREIGN KEY (`owner`) REFERENCES `tblusers` (`id`);

--
-- Constraints for table `tbldocumentstatus`
--
ALTER TABLE `tbldocumentstatus`
  ADD CONSTRAINT `tblDocumentStatus_document` FOREIGN KEY (`documentID`) REFERENCES `tbldocuments` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tbldocumentstatuslog`
--
ALTER TABLE `tbldocumentstatuslog`
  ADD CONSTRAINT `tblDocumentStatusLog_status` FOREIGN KEY (`statusID`) REFERENCES `tbldocumentstatus` (`statusID`) ON DELETE CASCADE,
  ADD CONSTRAINT `tblDocumentStatusLog_user` FOREIGN KEY (`userID`) REFERENCES `tblusers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tblfolderattributes`
--
ALTER TABLE `tblfolderattributes`
  ADD CONSTRAINT `tblFolderAttributes_attrdef` FOREIGN KEY (`attrdef`) REFERENCES `tblattributedefinitions` (`id`),
  ADD CONSTRAINT `tblFolderAttributes_folder` FOREIGN KEY (`folder`) REFERENCES `tblfolders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tblfolders`
--
ALTER TABLE `tblfolders`
  ADD CONSTRAINT `tblFolders_owner` FOREIGN KEY (`owner`) REFERENCES `tblusers` (`id`);

--
-- Constraints for table `tblgroupmembers`
--
ALTER TABLE `tblgroupmembers`
  ADD CONSTRAINT `tblGroupMembers_group` FOREIGN KEY (`groupID`) REFERENCES `tblgroups` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tblGroupMembers_user` FOREIGN KEY (`userID`) REFERENCES `tblusers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tblkeywords`
--
ALTER TABLE `tblkeywords`
  ADD CONSTRAINT `tblKeywords_category` FOREIGN KEY (`category`) REFERENCES `tblkeywordcategories` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tblmandatoryapprovers`
--
ALTER TABLE `tblmandatoryapprovers`
  ADD CONSTRAINT `tblMandatoryApprovers_user` FOREIGN KEY (`userID`) REFERENCES `tblusers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tblmandatoryreviewers`
--
ALTER TABLE `tblmandatoryreviewers`
  ADD CONSTRAINT `tblMandatoryReviewers_user` FOREIGN KEY (`userID`) REFERENCES `tblusers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tblsessions`
--
ALTER TABLE `tblsessions`
  ADD CONSTRAINT `tblSessions_user` FOREIGN KEY (`userID`) REFERENCES `tblusers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tbltransmittalitems`
--
ALTER TABLE `tbltransmittalitems`
  ADD CONSTRAINT `tblTransmittalItem_transmittal` FOREIGN KEY (`transmittal`) REFERENCES `tbltransmittals` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tblTransmittalItems_document` FOREIGN KEY (`document`) REFERENCES `tbldocuments` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tbltransmittals`
--
ALTER TABLE `tbltransmittals`
  ADD CONSTRAINT `tblTransmittals_user` FOREIGN KEY (`userID`) REFERENCES `tblusers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tbluserimages`
--
ALTER TABLE `tbluserimages`
  ADD CONSTRAINT `tblUserImages_user` FOREIGN KEY (`userID`) REFERENCES `tblusers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tbluserpasswordhistory`
--
ALTER TABLE `tbluserpasswordhistory`
  ADD CONSTRAINT `tblUserPasswordHistory_user` FOREIGN KEY (`userID`) REFERENCES `tblusers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tbluserpasswordrequest`
--
ALTER TABLE `tbluserpasswordrequest`
  ADD CONSTRAINT `tblUserPasswordRequest_user` FOREIGN KEY (`userID`) REFERENCES `tblusers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tblusers`
--
ALTER TABLE `tblusers`
  ADD CONSTRAINT `tblUsers_homefolder` FOREIGN KEY (`homefolder`) REFERENCES `tblfolders` (`id`),
  ADD CONSTRAINT `tblUsers_role` FOREIGN KEY (`role`) REFERENCES `tblroles` (`id`);

--
-- Constraints for table `tblusersubstitutes`
--
ALTER TABLE `tblusersubstitutes`
  ADD CONSTRAINT `tblUserSubstitutes_substitute` FOREIGN KEY (`substitute`) REFERENCES `tblusers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tblUserSubstitutes_user` FOREIGN KEY (`user`) REFERENCES `tblusers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tblworkflowdocumentcontent`
--
ALTER TABLE `tblworkflowdocumentcontent`
  ADD CONSTRAINT `tblWorkflowDocumentContent_parent` FOREIGN KEY (`parent`) REFERENCES `tblworkflowdocumentcontent` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tblWorkflowDocument_document` FOREIGN KEY (`document`) REFERENCES `tbldocuments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tblWorkflowDocument_state` FOREIGN KEY (`state`) REFERENCES `tblworkflowstates` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tblWorkflowDocument_workflow` FOREIGN KEY (`workflow`) REFERENCES `tblworkflows` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tblworkflowlog`
--
ALTER TABLE `tblworkflowlog`
  ADD CONSTRAINT `tblWorkflowLog_transition` FOREIGN KEY (`transition`) REFERENCES `tblworkflowtransitions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tblWorkflowLog_userid` FOREIGN KEY (`userid`) REFERENCES `tblusers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tblWorkflowLog_workflowdocumentcontent` FOREIGN KEY (`workflowdocumentcontent`) REFERENCES `tblworkflowdocumentcontent` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tblworkflowmandatoryworkflow`
--
ALTER TABLE `tblworkflowmandatoryworkflow`
  ADD CONSTRAINT `tblWorkflowMandatoryWorkflow_userid` FOREIGN KEY (`userid`) REFERENCES `tblusers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tblWorkflowMandatoryWorkflow_workflow` FOREIGN KEY (`workflow`) REFERENCES `tblworkflows` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tblworkflows`
--
ALTER TABLE `tblworkflows`
  ADD CONSTRAINT `tblWorkflow_initstate` FOREIGN KEY (`initstate`) REFERENCES `tblworkflowstates` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tblworkflowtransitiongroups`
--
ALTER TABLE `tblworkflowtransitiongroups`
  ADD CONSTRAINT `tblWorkflowTransitionGroups_groupid` FOREIGN KEY (`groupid`) REFERENCES `tblgroups` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tblWorkflowTransitionGroups_transition` FOREIGN KEY (`transition`) REFERENCES `tblworkflowtransitions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tblworkflowtransitions`
--
ALTER TABLE `tblworkflowtransitions`
  ADD CONSTRAINT `tblWorkflowTransitions_action` FOREIGN KEY (`action`) REFERENCES `tblworkflowactions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tblWorkflowTransitions_nextstate` FOREIGN KEY (`nextstate`) REFERENCES `tblworkflowstates` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tblWorkflowTransitions_state` FOREIGN KEY (`state`) REFERENCES `tblworkflowstates` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tblWorkflowTransitions_workflow` FOREIGN KEY (`workflow`) REFERENCES `tblworkflows` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tblworkflowtransitionusers`
--
ALTER TABLE `tblworkflowtransitionusers`
  ADD CONSTRAINT `tblWorkflowTransitionUsers_transition` FOREIGN KEY (`transition`) REFERENCES `tblworkflowtransitions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tblWorkflowTransitionUsers_userid` FOREIGN KEY (`userid`) REFERENCES `tblusers` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

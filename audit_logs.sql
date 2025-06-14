-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 14, 2025 at 06:27 PM
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
  `old_value` text DEFAULT NULL,
  `new_value` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `audit_logs`
--

INSERT INTO `audit_logs` (`id`, `document_id`, `created_at`, `user`, `old_value`, `new_value`) VALUES
(6, 21, '2025-06-14 17:40:36', 'aDjjKul9Lu/rdMIN5GRf4x+K3q7qgR4Nugfi2Lqx1q0=', '6oU6pEHaTkaBVbC94Gqd/IIzG2pIvu8suh2bpKbPP6aNNYsDgp22Kh8hRp3YEJWfJZe8eGT5cno6iqKgW9eVVfC13bSPShwqP+KM9t6EHXhqyQ5Fp9d2hUUES3SM5KMj2RDX4pPAm7GfJ3Xh8SZaasqc7lkg8aH2hy6V6iS4ONSVz/nzMButp1GIT75i4VNFqbZmhsG9ljfUOY7vhfyRVmToKRblOhuM4R1hAJNlgGM9JfXXaywiypYzRfzie5Qms5GHpgfSXwik7B7mYUdkmQaDEZcErp3BJde/SOgbGnQ=', '4usapZD8/op57Pse9YRmTR847g/O9vs4HQ2wkg9PhKsdmDcRd/OBLOHzkCOWSVpFGm01pJJI4ytPXWrcCbHA6qpO7LbMlCRYnIIp9+faw6XS5cScsD6ARoY/M979tsAVoixbrLqKZfJH5KicA0DnuQ=='),
(7, 17, '2025-06-14 18:22:11', 'lgdXASgBDEFg434M7U6JlYzYmrQhwysUQb2JF1fCr9M=', 'yNtMIqp0L8AYkBty/fZr5VgUFTIn7zBwRDGzZd4NUHB+DD4I6lfSZEcfANGebKqulIhLXYigj89lRM0bPkiot3soIhZPZGb+Dfd+79yNC1D9Atv2438uMkn659qL2xkyMc0fh87ML/+AD1rvNocu9m2eR6thhALCHp2fsmD5m8WIkUxhywmZNX1wr97+Zits', 'prPa0F4a9E7pci8HnToyc3y76/7fcFNKMammZF+ZfKrlwpA9VjJ93zJIseNz28CZRmvh8FP19S/1DuBAKo+fu4CJsr/QPuRATU8M7rsF7qFYy1dIQTosvf4YfsdKPkUkIHByLVMTi+MFsY8g2qqRkRADM8YsyTc9ZJdV5bF0LsdaVZ5Ie4C5Wdouv4wnV8oqp3ad9zGjdglJuZtowceycDKuz4TLN+4K4Af0ciDw3gXNjQXHgAga4SloSTgwSL9gifLFmHk/cYR7QEWjKnubz/feXkbQp0L55NOaFTfcPBw='),
(8, 17, '2025-06-14 18:22:37', '+BsikVZoREtAyyoUK4OOotOKu/dLXCGuxJQpI2Owo7Y=', 'jNMxaMqoPawK9I++rPxupZa1KvGNNsrBVje1bOu+l1Gh5wdEHVxUrR92MCCpho6nSGlAGDcEIDbGJC50XcPSLZ/xSxImD82MVDgimtBdu/RK987qJs3OFZnYrEdN2q71sJoYcIeLrX1EsV0r/195bHmGST73G5cZwqr6YoPSpqlI8w/cEbFWcgT2qxS+RSu9', 'NGygy8UmyOhzDyeoV8rDYYX6bX97Qe0BnQnqTnoIKrl/SxiJVKD+E7Expwrh63g1e/T4wJR1HUMSz1R0hXnczdBGBVX8IMVAx2ckIF7lI2zJM+BH3aiF/ovmo6DlORCA6cT4NwV31T+1vcEdJGkc44UGK1yKR/nGdfk3iWEGsCxKIehTnLpUt4l5ljrQpsIZBWu4ArmChmU/1hyDAnCnto/xePIhrv2OHrbuDH1e8hzBh8g1LEkXbiD+N/7VP77ls1DoItNVtU2f8nvY5MYr8FuEfK3D13FDpj3VrsryOLzqQoLApvhvFt2AFJ9jwtQk3vfCR7ctdnOPIYPpaPXUY8JXp4SDlxZFxH61B2eYZN++521OFq+btFKF3y/xnoICQbYBoL4wFsIe2hJkY7Ma+BK+nkqpAQ1bDghiKZ+6pWG6FDGqXy3NKDJ07QMkCvI/YAEuz9yRYV9AdJSLHYJJ1xOYWUDDuN7jC68H2i7FfZwcLzeRGvQRNSkv0YuJACBvnbxdnkcjnkqfznMmw26FZA=='),
(9, 17, '2025-06-14 18:22:51', 'afZDqEnlI/jo/MJC+7LrHclAbZ0iNMOdT7DoefFRpuM=', 'X6UHMXBufx5PIubXdf/aPtzSklGK1tKn5aiMJqGcxB9GFVtRI/teLWcN7hWL3s0HO8N16zFM+mKUZ0sp9rSQKcmsMsQ1eawpZDdYRdvvvW4UvgvPOwNKAjSRMG0lg7knRvwjG9gSfyrMB6VHpWXqmQ==', 'yMNZ40lpQO5ywjblmwQqfGzc4tue7JDTUcT8lH2epYlmEpUZDHvtSjETIGvuQIqG6NN9SNKXobrh0xKcx6PqNHnA0Bln6Qd3CECSDWZIDEdKQwQcTchz/pbuV0Sj0ZLscA1/CefMv4mB5BFlJ7n0tIFNO8kLeFHi/d/4Fau9SROPkyAOwmmN2P4+8izC7uavc3fs4yThOgjqkdIacIb+x/XeOpFHKwnW36LJyuwSpDAWrFvqVEXDaqbSNmcuL2/IAzIDUvAnDCEFh+5Pm0sc14ciUuBzcLByW2nUuwI0YOpcfm0685qW0toBVNghT1qlWvX+g9N6Ipcje/rXD9wLXfyI7ya3byKr1Q9dMYE84WBiArJ9j3Iy60NItdS8/akuGtimtiLRbZO0ifD6Ryx+VJjMm4M3kZmaeN1yqt2u5vzs6Q9rIkWQ8sRdp8szWZartdX7yqICtzAZtH0KVd1SwQpo+VkA+MAdWqvMXA/OQKTVf6OzIihaNI+K+rZP42zQ');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

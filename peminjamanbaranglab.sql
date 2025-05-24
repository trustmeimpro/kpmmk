-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: May 24, 2025 at 01:56 PM
-- Server version: 8.0.36
-- PHP Version: 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `peminjamanbaranglab`
--

-- --------------------------------------------------------

--
-- Table structure for table `barang`
--

CREATE TABLE `barang` (
  `id` int NOT NULL,
  `nama_barang` varchar(100) DEFAULT NULL,
  `jumlah` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `barang`
--

INSERT INTO `barang` (`id`, `nama_barang`, `jumlah`) VALUES
(1, 'Labu erlenmeyer gede', 22),
(2, 'Labu erlenmeyer kecil', 9),
(3, 'Gelas kimia kecil', 20),
(4, 'Gelas kimia sedang', 18),
(5, 'Gelas kimia besar', 12),
(6, 'Plate tetes', 6),
(7, 'Gelas ukur besar', 15),
(8, 'Gelas ukur sedang', 3),
(9, 'Gelas ukur kecil', 14),
(10, 'Tabung reaksi', 188),
(11, 'Rak tabung reaksi', 6),
(12, 'Penjepit tabung reaksi', 5),
(13, 'Pipet tetes', 80),
(14, 'Tabung spiritus', 7),
(15, 'Corong gelas kecil', 9),
(16, 'Corong gelas gede', 8),
(17, 'Cawan petri', 14),
(18, 'Kaki tiga', 4);

-- --------------------------------------------------------

--
-- Table structure for table `kasus`
--

CREATE TABLE `kasus` (
  `id` int NOT NULL,
  `kasus_barang` int NOT NULL,
  `nip` int NOT NULL,
  `tanggal` date NOT NULL,
  `waktu_mulai` time NOT NULL,
  `waktu_selesai` time NOT NULL,
  `status_acc` varchar(15) DEFAULT NULL,
  `kondisi` enum('belum selesai','selesai') DEFAULT 'belum selesai'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `kasus`
--

INSERT INTO `kasus` (`id`, `kasus_barang`, `nip`, `tanggal`, `waktu_mulai`, `waktu_selesai`, `status_acc`, `kondisi`) VALUES
(80, 0, 19651231, '2025-05-24', '01:03:00', '04:04:00', 'disetujui', 'selesai'),
(81, 0, 19651231, '2025-05-24', '04:04:00', '04:44:00', 'ditolak', 'selesai'),
(82, 0, 19651231, '2025-05-24', '12:12:00', '03:13:00', 'disetujui', 'selesai');

-- --------------------------------------------------------

--
-- Table structure for table `kasus_barang`
--

CREATE TABLE `kasus_barang` (
  `id` int NOT NULL,
  `kasus_id` int NOT NULL,
  `barang_id` int NOT NULL,
  `qty` int NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `kasus_barang`
--

INSERT INTO `kasus_barang` (`id`, `kasus_id`, `barang_id`, `qty`) VALUES
(30, 80, 17, 4),
(31, 81, 14, 7),
(32, 81, 10, 15),
(33, 81, 12, 4),
(34, 81, 13, 6),
(35, 82, 17, 10),
(36, 82, 16, 1);

-- --------------------------------------------------------

--
-- Table structure for table `login_admin`
--

CREATE TABLE `login_admin` (
  `ID` int NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `login_admin`
--

INSERT INTO `login_admin` (`ID`, `username`, `password`) VALUES
(1, 'admin', 'admin');

-- --------------------------------------------------------

--
-- Table structure for table `nip_guru`
--

CREATE TABLE `nip_guru` (
  `ID` int NOT NULL,
  `nama_guru` varchar(100) NOT NULL,
  `nip` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `nip_guru`
--

INSERT INTO `nip_guru` (`ID`, `nama_guru`, `nip`) VALUES
(1, 'yanto', 19651231);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `barang`
--
ALTER TABLE `barang`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kasus`
--
ALTER TABLE `kasus`
  ADD PRIMARY KEY (`id`),
  ADD KEY `kasus_ibfk_1` (`kasus_barang`),
  ADD KEY `kasus_ibfk_2` (`nip`);

--
-- Indexes for table `kasus_barang`
--
ALTER TABLE `kasus_barang`
  ADD PRIMARY KEY (`id`),
  ADD KEY `kasus_id` (`kasus_id`),
  ADD KEY `barang_id` (`barang_id`);

--
-- Indexes for table `login_admin`
--
ALTER TABLE `login_admin`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `nip_guru`
--
ALTER TABLE `nip_guru`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `nip` (`nip`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `barang`
--
ALTER TABLE `barang`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `kasus`
--
ALTER TABLE `kasus`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=83;

--
-- AUTO_INCREMENT for table `kasus_barang`
--
ALTER TABLE `kasus_barang`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `login_admin`
--
ALTER TABLE `login_admin`
  MODIFY `ID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `nip_guru`
--
ALTER TABLE `nip_guru`
  MODIFY `ID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `kasus_barang`
--
ALTER TABLE `kasus_barang`
  ADD CONSTRAINT `kasus_barang_ibfk_1` FOREIGN KEY (`kasus_id`) REFERENCES `kasus` (`id`),
  ADD CONSTRAINT `kasus_barang_ibfk_2` FOREIGN KEY (`barang_id`) REFERENCES `barang` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

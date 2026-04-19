-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 19, 2026 at 10:27 AM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `balikgamit`
--

-- --------------------------------------------------------

--
-- Table structure for table `category_table`
--

CREATE TABLE `category_table` (
  `Category_ID` varchar(10) NOT NULL,
  `Category` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `category_table`
--

INSERT INTO `category_table` (`Category_ID`, `Category`) VALUES
('CAT 201', 'Electronics'),
('CAT 202', 'Personal Belongings'),
('CAT 203', 'Accessories');

-- --------------------------------------------------------

--
-- Table structure for table `item_table`
--

CREATE TABLE `item_table` (
  `Item_ID` varchar(10) NOT NULL,
  `Item_Name` varchar(100) NOT NULL,
  `Item_Status` varchar(20) DEFAULT NULL,
  `Item_Description` text DEFAULT NULL,
  `Category_ID` varchar(10) DEFAULT NULL,
  `Item_Image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `publication_table`
--

CREATE TABLE `publication_table` (
  `Publication_ID` varchar(10) NOT NULL,
  `User_ID` int(11) DEFAULT NULL,
  `Item_ID` varchar(10) DEFAULT NULL,
  `Date_filed` date DEFAULT NULL,
  `Location` varchar(100) DEFAULT NULL,
  `Claim_Status_ID` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `status_table`
--

CREATE TABLE `status_table` (
  `Claim_Status_ID` varchar(10) NOT NULL,
  `Claim_Status` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `status_table`
--

INSERT INTO `status_table` (`Claim_Status_ID`, `Claim_Status`) VALUES
('STS 301', 'Pending'),
('STS 302', 'Claimed'),
('STS 303', 'Rejected');

-- --------------------------------------------------------

--
-- Table structure for table `user_table`
--

CREATE TABLE `user_table` (
  `User_ID` int(11) NOT NULL,
  `Username` varchar(50) NOT NULL,
  `Password` varchar(255) DEFAULT NULL,
  `First_Name` varchar(50) DEFAULT NULL,
  `Last_Name` varchar(50) DEFAULT NULL,
  `Role` varchar(20) DEFAULT NULL,
  `Email_Address` varchar(100) DEFAULT NULL,
  `Contact_Number` varchar(15) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_table`
--

INSERT INTO `user_table` (`User_ID`, `Username`, `Password`, `First_Name`, `Last_Name`, `Role`, `Email_Address`, `Contact_Number`) VALUES
(1, 'Lenzi', '$2y$10$MNalsEwQpLelW/47CdXxJe.FaeFeBk0sddEZwEaKeR3muwrJpCU.W', 'Lenzi Leoell', 'Madelo', 'Student', '2401109917@student.buksu.edu.ph', '09450660469');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `category_table`
--
ALTER TABLE `category_table`
  ADD PRIMARY KEY (`Category_ID`);

--
-- Indexes for table `item_table`
--
ALTER TABLE `item_table`
  ADD PRIMARY KEY (`Item_ID`),
  ADD KEY `Category_ID` (`Category_ID`);

--
-- Indexes for table `publication_table`
--
ALTER TABLE `publication_table`
  ADD PRIMARY KEY (`Publication_ID`),
  ADD KEY `Item_ID` (`Item_ID`),
  ADD KEY `Claim_Status_ID` (`Claim_Status_ID`),
  ADD KEY `fk_user_publication` (`User_ID`);

--
-- Indexes for table `status_table`
--
ALTER TABLE `status_table`
  ADD PRIMARY KEY (`Claim_Status_ID`);

--
-- Indexes for table `user_table`
--
ALTER TABLE `user_table`
  ADD PRIMARY KEY (`User_ID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `user_table`
--
ALTER TABLE `user_table`
  MODIFY `User_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `item_table`
--
ALTER TABLE `item_table`
  ADD CONSTRAINT `item_table_ibfk_1` FOREIGN KEY (`Category_ID`) REFERENCES `category_table` (`Category_ID`);

--
-- Constraints for table `publication_table`
--
ALTER TABLE `publication_table`
  ADD CONSTRAINT `fk_user_publication` FOREIGN KEY (`User_ID`) REFERENCES `user_table` (`User_ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `publication_table_ibfk_2` FOREIGN KEY (`Item_ID`) REFERENCES `item_table` (`Item_ID`),
  ADD CONSTRAINT `publication_table_ibfk_3` FOREIGN KEY (`Claim_Status_ID`) REFERENCES `status_table` (`Claim_Status_ID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 04, 2026 at 10:51 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `campus_lost_found`
--

-- --------------------------------------------------------

--
-- Table structure for table `category_table`
--

CREATE TABLE `category_table` (
  `Category_ID` int(11) NOT NULL,
  `Category` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `category_table`
--

INSERT INTO `category_table` (`Category_ID`, `Category`) VALUES
(1, 'Electronics'),
(2, 'Personal Belongings'),
(3, 'Accessories');

-- --------------------------------------------------------

--
-- Table structure for table `claims_table`
--

CREATE TABLE `claims_table` (
  `Claim_Request_ID` int(11) NOT NULL,
  `Report_ID` int(11) NOT NULL,
  `User_ID` int(11) NOT NULL,
  `Claim_Note` varchar(255) DEFAULT NULL,
  `Claim_Status` enum('pending','claimed','rejected') NOT NULL DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `claims_table`
--

INSERT INTO `claims_table` (`Claim_Request_ID`, `Report_ID`, `User_ID`, `Claim_Note`, `Claim_Status`) VALUES
(1, 1, 1, 'Cracked nga screen', 'pending'),
(2, 2, 1, 'Color blue daghan kwarta', 'claimed'),
(3, 3, 2, 'Panlalaki nga relo', 'rejected'),
(4, 4, 3, 'Expensive', 'pending'),
(5, 5, 4, 'For car keys', 'claimed'),
(6, 6, 5, 'Color pink', 'rejected'),
(7, 6, 8, 'wuierwgu', 'pending'),
(8, 5, 8, 'hkaewhf', 'pending');

-- --------------------------------------------------------

--
-- Table structure for table `item_table`
--

CREATE TABLE `item_table` (
  `Item_ID` int(11) NOT NULL,
  `Item_Name` varchar(100) NOT NULL,
  `Item_Status` enum('Found','Lost') NOT NULL,
  `Item_Description` varchar(255) DEFAULT NULL,
  `Category_ID` int(11) NOT NULL,
  `Item_Image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `item_table`
--

INSERT INTO `item_table` (`Item_ID`, `Item_Name`, `Item_Status`, `Item_Description`, `Category_ID`, `Item_Image`) VALUES
(1, 'Blue Iphone 13', 'Found', 'Cracked screen', 1, 'iphone_01.jpg'),
(2, 'Leather Wallet', 'Lost', 'Color blue', 2, 'wallet_v2.png'),
(3, 'Silver Watch', 'Found', 'Unisex', 3, 'watch_abc.jpg'),
(4, 'Blue Iphone 13', 'Lost', 'Expensive', 1, 'iphone_01.jpg'),
(5, 'Keys', 'Found', 'For car keys', 3, 'keys.jpg'),
(6, 'NSTP Uniform', 'Found', 'Newly bought, medium sized uniform', 2, 'uniform.jpg'),
(8, 'letstry', 'Lost', 'lezgoooooooooooo', 1, NULL),
(9, 'RedggyItem', 'Lost', 'sdddddddd', 2, 'uploads/1777921969_WIN_20260401_23_34_00_Pro.jpg'),
(10, 'Redggy Canque', 'Lost', 'aca', 1, 'uploads/1777927190_WIN_20260401_23_34_00_Pro.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `reports_table`
--

CREATE TABLE `reports_table` (
  `Report_ID` int(11) NOT NULL,
  `User_ID` int(11) NOT NULL,
  `Item_ID` int(11) NOT NULL,
  `Date_filed` date NOT NULL,
  `Location` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reports_table`
--

INSERT INTO `reports_table` (`Report_ID`, `User_ID`, `Item_ID`, `Date_filed`, `Location`) VALUES
(1, 1, 1, '2026-03-20', 'Cafeteria'),
(2, 1, 2, '2026-03-20', 'Library'),
(3, 2, 3, '2026-04-01', 'Old COT Building'),
(4, 3, 4, '2026-04-10', 'Cafeteria'),
(5, 4, 5, '2026-04-30', 'Parking Lot'),
(6, 5, 6, '2026-04-30', 'Comfort Room'),
(7, 8, 8, '2026-05-28', 'anything'),
(8, 11, 9, '2026-05-05', 'aaaaaaaaaaaaaaaaaaaaa'),
(9, 13, 10, '2026-05-28', 'fe');

-- --------------------------------------------------------

--
-- Table structure for table `user_table`
--

CREATE TABLE `user_table` (
  `User_ID` int(11) NOT NULL,
  `Username` varchar(100) NOT NULL,
  `First_Name` varchar(50) NOT NULL,
  `Last_Name` varchar(50) NOT NULL,
  `Role` enum('Student','Admin') NOT NULL,
  `Email_Address` varchar(100) NOT NULL,
  `Contact_Number` varchar(20) NOT NULL,
  `Password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_table`
--

INSERT INTO `user_table` (`User_ID`, `Username`, `First_Name`, `Last_Name`, `Role`, `Email_Address`, `Contact_Number`, `Password`) VALUES
(1, 'Anthony Miller', 'Anthony', 'Miller', 'Student', 'anthony.miller@example.com', '9451234567', '12345678'),
(2, 'Ceren Ross', 'Ceren', 'Ross', 'Admin', 'ceren.ross@example.com', '9391234567', 'mahalkita212'),
(3, 'Anthony Cora', 'Anthony', 'Cora', 'Student', 'anthony.cora@example.com', '9451234567', '2401101333'),
(4, 'Erica Egama', 'Erica', 'Egama', 'Admin', 'erica.egama@example.com', '9061234567', 'admin'),
(5, 'Red Can', 'Red', 'Can', 'Student', 'red.can@gmail.com', '9066903902', 'cisco'),
(6, 'edggyanque', 'Redggy', 'Canque', 'Student', 'redggycanque@gmail.com', '09656595857', '$2y$10$KpOptGGjMLCKtcVIcKFkte2ZzXQ86QtlbAsxILTAPvgXlTZfogMFS'),
(7, 'redgcanq', 'redg', 'canq', 'Student', 'canred@canred', '1234', '$2y$10$1XRC8fujVJSW8SkFNLqPdOfYvClVUkpMg2/1UP2FgTg/a7qdP24Ea'),
(8, 'rrrr', 'rr', 'rr', 'Student', 'rr@rr', '11', '$2y$10$s525DlBLTlC1esLlF4/Is.7Lzuii.WCuTdic1JGgb9RXTFyqlJZwq'),
(9, 'herlieamallas', 'Cherlie', 'Mamallas', 'Student', 'cherlie@mamallas', '1234', '$2y$10$/ivuNQ5fHTdQAAEp4X4cHObytGxjzOh54nyCoVxwPnpmwm2GRefCm'),
(10, 'ericaegama', 'erica', 'egama', 'Student', 'erica@egama', '111', '$2y$10$9I5kbPAzZfXJ72qLkzdLcuBc.HsoJ0DKxI1JrSeqq.E945IlG2d2C'),
(11, 'aaaa', 'bb', 'bb', 'Student', 'aa@aa', '123', '$2y$10$VpGwTSW.ubunosEaHjwkiOsvRXWU9/rsZszRYjh769kd1pAzSYC.2'),
(12, 'redggycanque', 'redggy', 'canque', 'Student', 'redggycanquee@gmail.com', '09656595857', '$2y$10$Y0x5tqvwc6hA1WHdFQy0aOqGfpAttoAA0gxKlF29pAEw4lJqVMTge'),
(13, 'edggyanque1', 'Redggy', 'Canque', 'Student', 'redggy@canque2', '11', '$2y$10$qP4..8sBPWxlB3kmoFMUwe7G8.Gn2HVX63neuCsTpxXYUM2p1C6Ie');

-- --------------------------------------------------------

--
-- Table structure for table `user_tokens`
--

CREATE TABLE `user_tokens` (
  `Token_ID` int(11) NOT NULL,
  `User_ID` int(11) NOT NULL,
  `Token_Hash` varchar(255) NOT NULL,
  `Expiry` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `category_table`
--
ALTER TABLE `category_table`
  ADD PRIMARY KEY (`Category_ID`);

--
-- Indexes for table `claims_table`
--
ALTER TABLE `claims_table`
  ADD PRIMARY KEY (`Claim_Request_ID`),
  ADD KEY `Report_ID` (`Report_ID`),
  ADD KEY `User_ID` (`User_ID`);

--
-- Indexes for table `item_table`
--
ALTER TABLE `item_table`
  ADD PRIMARY KEY (`Item_ID`),
  ADD KEY `Category_ID` (`Category_ID`);

--
-- Indexes for table `reports_table`
--
ALTER TABLE `reports_table`
  ADD PRIMARY KEY (`Report_ID`),
  ADD KEY `User_ID` (`User_ID`),
  ADD KEY `Item_ID` (`Item_ID`);

--
-- Indexes for table `user_table`
--
ALTER TABLE `user_table`
  ADD PRIMARY KEY (`User_ID`),
  ADD UNIQUE KEY `Email_Address` (`Email_Address`);

--
-- Indexes for table `user_tokens`
--
ALTER TABLE `user_tokens`
  ADD PRIMARY KEY (`Token_ID`),
  ADD KEY `User_ID` (`User_ID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `category_table`
--
ALTER TABLE `category_table`
  MODIFY `Category_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `claims_table`
--
ALTER TABLE `claims_table`
  MODIFY `Claim_Request_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `item_table`
--
ALTER TABLE `item_table`
  MODIFY `Item_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `reports_table`
--
ALTER TABLE `reports_table`
  MODIFY `Report_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `user_table`
--
ALTER TABLE `user_table`
  MODIFY `User_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `user_tokens`
--
ALTER TABLE `user_tokens`
  MODIFY `Token_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `claims_table`
--
ALTER TABLE `claims_table`
  ADD CONSTRAINT `claims_table_ibfk_1` FOREIGN KEY (`Report_ID`) REFERENCES `reports_table` (`Report_ID`),
  ADD CONSTRAINT `claims_table_ibfk_2` FOREIGN KEY (`User_ID`) REFERENCES `user_table` (`User_ID`);

--
-- Constraints for table `item_table`
--
ALTER TABLE `item_table`
  ADD CONSTRAINT `item_table_ibfk_1` FOREIGN KEY (`Category_ID`) REFERENCES `category_table` (`Category_ID`);

--
-- Constraints for table `reports_table`
--
ALTER TABLE `reports_table`
  ADD CONSTRAINT `reports_table_ibfk_1` FOREIGN KEY (`User_ID`) REFERENCES `user_table` (`User_ID`),
  ADD CONSTRAINT `reports_table_ibfk_2` FOREIGN KEY (`Item_ID`) REFERENCES `item_table` (`Item_ID`);

--
-- Constraints for table `user_tokens`
--
ALTER TABLE `user_tokens`
  ADD CONSTRAINT `user_tokens_ibfk_1` FOREIGN KEY (`User_ID`) REFERENCES `user_table` (`User_ID`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

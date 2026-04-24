-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3307
-- Generation Time: Apr 24, 2026 at 11:32 AM
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
-- Table structure for table `claim_request_table`
--

CREATE TABLE `claim_request_table` (
  `Claim_Request_ID` int(11) NOT NULL,
  `Publication_ID` int(11) NOT NULL,
  `User_ID` int(11) NOT NULL,
  `Claim_Date` datetime DEFAULT current_timestamp(),
  `Claim_Note` text DEFAULT NULL,
  `Claim_Status_ID` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `item_table`
--

CREATE TABLE `item_table` (
  `Item_ID` int(11) NOT NULL,
  `Item_Name` varchar(100) NOT NULL,
  `Item_Status` enum('Found','Lost') NOT NULL,
  `Item_Description` text DEFAULT NULL,
  `Category_ID` int(11) DEFAULT NULL,
  `Item_Image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `publication_table`
--

CREATE TABLE `publication_table` (
  `Publication_ID` int(11) NOT NULL,
  `User_ID` int(11) NOT NULL,
  `Item_ID` int(11) NOT NULL,
  `Date_Filed` date DEFAULT NULL,
  `Location` varchar(100) DEFAULT NULL,
  `Claim_Status_ID` int(11) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `status_table`
--

CREATE TABLE `status_table` (
  `Claim_Status_ID` int(11) NOT NULL,
  `Claim_Status` enum('pending','claimed','rejected') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `status_table`
--

INSERT INTO `status_table` (`Claim_Status_ID`, `Claim_Status`) VALUES
(1, 'pending'),
(2, 'claimed'),
(3, 'rejected');

-- --------------------------------------------------------

--
-- Table structure for table `user_table`
--

CREATE TABLE `user_table` (
  `User_ID` int(11) NOT NULL,
  `Username` varchar(50) NOT NULL,
  `Password` varchar(255) NOT NULL,
  `First_Name` varchar(50) DEFAULT NULL,
  `Last_Name` varchar(50) DEFAULT NULL,
  `Role` enum('Student','Admin') NOT NULL,
  `Email_Address` varchar(100) DEFAULT NULL,
  `Contact_Number` varchar(15) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_table`
--

INSERT INTO `user_table` (`User_ID`, `Username`, `Password`, `First_Name`, `Last_Name`, `Role`, `Email_Address`, `Contact_Number`) VALUES
(1, 'Lenzi', '$2y$10$QN0ZqAwSOlWpDDJcsf098.9DjUSsA9UWekCW.D7qKXAqaNa6HdY1W', 'Lenzi Leoell ', 'Madelo', 'Student', '2401109917@student.buksu.edu.ph', '09450660469');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `category_table`
--
ALTER TABLE `category_table`
  ADD PRIMARY KEY (`Category_ID`);

--
-- Indexes for table `claim_request_table`
--
ALTER TABLE `claim_request_table`
  ADD PRIMARY KEY (`Claim_Request_ID`),
  ADD KEY `fk_req_pub` (`Publication_ID`),
  ADD KEY `fk_req_user` (`User_ID`),
  ADD KEY `fk_req_status` (`Claim_Status_ID`);

--
-- Indexes for table `item_table`
--
ALTER TABLE `item_table`
  ADD PRIMARY KEY (`Item_ID`),
  ADD KEY `fk_item_category` (`Category_ID`);

--
-- Indexes for table `publication_table`
--
ALTER TABLE `publication_table`
  ADD PRIMARY KEY (`Publication_ID`),
  ADD KEY `fk_pub_user` (`User_ID`),
  ADD KEY `fk_pub_item` (`Item_ID`),
  ADD KEY `fk_pub_status` (`Claim_Status_ID`);

--
-- Indexes for table `status_table`
--
ALTER TABLE `status_table`
  ADD PRIMARY KEY (`Claim_Status_ID`);

--
-- Indexes for table `user_table`
--
ALTER TABLE `user_table`
  ADD PRIMARY KEY (`User_ID`),
  ADD UNIQUE KEY `Email_Address` (`Email_Address`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `category_table`
--
ALTER TABLE `category_table`
  MODIFY `Category_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `claim_request_table`
--
ALTER TABLE `claim_request_table`
  MODIFY `Claim_Request_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `item_table`
--
ALTER TABLE `item_table`
  MODIFY `Item_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `publication_table`
--
ALTER TABLE `publication_table`
  MODIFY `Publication_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `status_table`
--
ALTER TABLE `status_table`
  MODIFY `Claim_Status_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `user_table`
--
ALTER TABLE `user_table`
  MODIFY `User_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `claim_request_table`
--
ALTER TABLE `claim_request_table`
  ADD CONSTRAINT `fk_req_pub` FOREIGN KEY (`Publication_ID`) REFERENCES `publication_table` (`Publication_ID`),
  ADD CONSTRAINT `fk_req_status` FOREIGN KEY (`Claim_Status_ID`) REFERENCES `status_table` (`Claim_Status_ID`),
  ADD CONSTRAINT `fk_req_user` FOREIGN KEY (`User_ID`) REFERENCES `user_table` (`User_ID`);

--
-- Constraints for table `item_table`
--
ALTER TABLE `item_table`
  ADD CONSTRAINT `fk_item_category` FOREIGN KEY (`Category_ID`) REFERENCES `category_table` (`Category_ID`);

--
-- Constraints for table `publication_table`
--
ALTER TABLE `publication_table`
  ADD CONSTRAINT `fk_pub_item` FOREIGN KEY (`Item_ID`) REFERENCES `item_table` (`Item_ID`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_pub_status` FOREIGN KEY (`Claim_Status_ID`) REFERENCES `status_table` (`Claim_Status_ID`),
  ADD CONSTRAINT `fk_pub_user` FOREIGN KEY (`User_ID`) REFERENCES `user_table` (`User_ID`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

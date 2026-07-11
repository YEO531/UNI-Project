-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 11, 2025 at 08:35 AM
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
-- Database: `hostel_management`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `Admin_ID` int(11) NOT NULL,
  `Admin_Name` varchar(100) NOT NULL,
  `Admin_Email` varchar(100) NOT NULL,
  `Admin_Phone` varchar(20) DEFAULT NULL,
  `Admin_Password` varchar(255) NOT NULL,
  `Office_Location` varchar(200) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`Admin_ID`, `Admin_Name`, `Admin_Email`, `Admin_Phone`, `Admin_Password`, `Office_Location`) VALUES
(1, 'JoGio', 'jojolee123@gmail.com', '018-6745123', '$2y$10$02LD1/UI36R0GJu8EJ6ntOca48jazwqUZuY0L1wuXa1LsyXSd5Jtq', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `appointment`
--

CREATE TABLE `appointment` (
  `Appointment_ID` int(11) NOT NULL,
  `Student_ID` int(11) NOT NULL,
  `Room_ID` int(11) NOT NULL,
  `Appointment_Date` datetime NOT NULL,
  `Status` enum('Pending','Confirmed','Cancelled','Completed') DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `appointment`
--

INSERT INTO `appointment` (`Appointment_ID`, `Student_ID`, `Room_ID`, `Appointment_Date`, `Status`) VALUES
(1, 3, 3, '2025-06-02 14:07:00', 'Completed'),
(2, 8, 4, '2025-06-11 14:45:00', 'Completed');

-- --------------------------------------------------------

--
-- Table structure for table `booking`
--

CREATE TABLE `booking` (
  `Booking_ID` int(11) NOT NULL,
  `Student_ID` int(11) NOT NULL,
  `Room_ID` int(11) NOT NULL,
  `Booking_Date` date NOT NULL,
  `Status` enum('Pending','Confirmed','Cancelled','Completed') DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `booking`
--

INSERT INTO `booking` (`Booking_ID`, `Student_ID`, `Room_ID`, `Booking_Date`, `Status`) VALUES
(1, 1, 1, '2025-05-20', 'Completed'),
(2, 1, 2, '2025-05-20', 'Cancelled'),
(3, 3, 3, '2025-06-01', 'Completed'),
(4, 5, 4, '2025-06-09', 'Completed'),
(5, 5, 4, '2025-06-10', 'Cancelled'),
(6, 8, 3, '2025-06-10', 'Completed');

-- --------------------------------------------------------

--
-- Table structure for table `maintenancelog`
--

CREATE TABLE `maintenancelog` (
  `Log_ID` int(11) NOT NULL,
  `Room_ID` int(11) NOT NULL,
  `Staff_ID` int(11) NOT NULL,
  `Start_Date` date NOT NULL,
  `End_Date` date DEFAULT NULL,
  `Notes` text DEFAULT NULL,
  `Resolution_Notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `maintenancestaff`
--

CREATE TABLE `maintenancestaff` (
  `Staff_ID` int(11) NOT NULL,
  `Staff_Name` varchar(100) NOT NULL,
  `Staff_Email` varchar(100) NOT NULL,
  `Staff_Phone` varchar(20) DEFAULT NULL,
  `Staff_Password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `maintenancestaff`
--

INSERT INTO `maintenancestaff` (`Staff_ID`, `Staff_Name`, `Staff_Email`, `Staff_Phone`, `Staff_Password`) VALUES
(1, 'Muhammad Ali', 'ali@gmail.com', '018-456789', '$2y$10$yamCdFWzHaEAJchrS93cOeXLeJ36XffA.Wk.fSNXrnlcZWXMZOkBm');

-- --------------------------------------------------------

--
-- Table structure for table `payment`
--

CREATE TABLE `payment` (
  `Payment_ID` int(11) NOT NULL,
  `Student_ID` int(11) NOT NULL,
  `Amount` decimal(10,2) NOT NULL,
  `Payment_Date` date NOT NULL,
  `Payment_Method` enum('Credit Card','Debit Card','Bank Transfer','Cash') NOT NULL,
  `Purpose` varchar(100) NOT NULL,
  `Status` enum('Pending','Completed','Failed','Refunded') DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payment`
--

INSERT INTO `payment` (`Payment_ID`, `Student_ID`, `Amount`, `Payment_Date`, `Payment_Method`, `Purpose`, `Status`) VALUES
(1, 3, 20.00, '2025-06-01', 'Credit Card', 'Hello', 'Pending');

-- --------------------------------------------------------

--
-- Table structure for table `repairrequest`
--

CREATE TABLE `repairrequest` (
  `Request_ID` int(11) NOT NULL,
  `Student_ID` int(11) NOT NULL,
  `Room_ID` int(11) NOT NULL,
  `Description` text NOT NULL,
  `repair_type` enum('Toilet','Electrical','Furniture','Door/Window','Paint/Wall','Air Conditioning','Flooring','Lighting','Other') NOT NULL DEFAULT 'Other',
  `Request_Date` date NOT NULL,
  `Scheduled_Date` date DEFAULT NULL,
  `Status` enum('Pending','Scheduled','In Progress','Completed','Cancelled') DEFAULT 'Pending',
  `Staff_ID` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `repairrequest`
--

INSERT INTO `repairrequest` (`Request_ID`, `Student_ID`, `Room_ID`, `Description`, `repair_type`, `Request_Date`, `Scheduled_Date`, `Status`, `Staff_ID`) VALUES
(1, 1, 1, 'Test', 'Other', '2025-06-01', '2025-06-03', 'Completed', NULL),
(2, 3, 3, 'Fan broke', 'Other', '2025-06-01', '2025-06-03', 'In Progress', NULL),
(3, 8, 3, 'Test', 'Air Conditioning', '2025-06-10', NULL, 'Pending', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `room`
--

CREATE TABLE `room` (
  `Room_ID` int(11) NOT NULL,
  `Room_Type` varchar(50) NOT NULL,
  `Capacity` int(11) NOT NULL,
  `Current_Occupancy` int(11) DEFAULT 0,
  `Status` enum('Available','Occupied','Maintenance','Reserved') DEFAULT 'Available'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `room`
--

INSERT INTO `room` (`Room_ID`, `Room_Type`, `Capacity`, `Current_Occupancy`, `Status`) VALUES
(1, 'Single', 2, 1, 'Available'),
(2, 'Single', 1, 1, 'Occupied'),
(3, 'Double', 2, 2, 'Occupied'),
(4, 'Master', 3, 1, 'Available'),
(5, 'Master', 4, 0, 'Available');

-- --------------------------------------------------------

--
-- Table structure for table `student`
--

CREATE TABLE `student` (
  `Student_ID` int(11) NOT NULL,
  `Student_Name` varchar(100) NOT NULL,
  `Student_Email` varchar(100) NOT NULL,
  `Student_Phone` varchar(20) DEFAULT NULL,
  `Student_Password` varchar(255) NOT NULL,
  `Room_ID` int(11) DEFAULT NULL,
  `Course` varchar(100) DEFAULT NULL,
  `Emergency_Contact` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student`
--

INSERT INTO `student` (`Student_ID`, `Student_Name`, `Student_Email`, `Student_Phone`, `Student_Password`, `Room_ID`, `Course`, `Emergency_Contact`) VALUES
(1, 'Deadpool', 'xforce@gmail.com', '017-9807655', '$2y$10$zcgC4xfOEw58IsPQcI83MeQwtsq7ou.g50pjJxY3HEp79dpF4yLma', 1, 'Business Administration', '019-4562323'),
(3, 'Lim Ming Jun', 'lim0190@gmail.com', '019-5678912', '$2y$10$XOTxBKRehMDZFNWnSrnabuczKwabgrmFbTNrgwUKnDL3KL1ULTGeG', 3, NULL, NULL),
(4, 'Leong', 'leong@gmail.com', '012-3434343', '$2y$10$RyvU4990pdSp5shilgw33uU/Y8CVMzkpENZ1td62XTzZ0xl/Poi4q', 2, NULL, NULL),
(5, 'Nithilan', 'hi@gmail.com', '019-456789', '$2y$10$2Q6717b17Yo/Abha8uMcleG8aElfCJ1FGtxb6xyG.r8Z/goVWlFym', 4, 'Engineering', '017-4532456'),
(6, 'Lim Wei Kit', 'lim0990@gmail.com', '019-7834343', '$2y$10$NZ.DDuZ1E23bzfmlRDWApeoti5vXuBo0.RO2cDle2JOG5mq7/0pG2', NULL, 'Computer Science', '017-6835123'),
(7, 'Justin Lee', 'justin@gmail.com', '018-7823434', '$2y$10$GfPd2Rpa9ohjzkGvbtBfp.vWyPb.HFIFX09VszltqQOapKT3HFruu', NULL, 'Accounting', '016-5423212'),
(8, 'Lim Fang Yik', 'lim1234@gmail.com', '018-6745123', '$2y$10$r.BL13RNV9Z9pdt16A48KeM/HJMuUkQrHatJOse3kOZxddRqWxOpC', 3, 'Software Engineering', '017-4532456');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`Admin_ID`),
  ADD UNIQUE KEY `Admin_Email` (`Admin_Email`);

--
-- Indexes for table `appointment`
--
ALTER TABLE `appointment`
  ADD PRIMARY KEY (`Appointment_ID`),
  ADD KEY `Student_ID` (`Student_ID`),
  ADD KEY `Room_ID` (`Room_ID`);

--
-- Indexes for table `booking`
--
ALTER TABLE `booking`
  ADD PRIMARY KEY (`Booking_ID`),
  ADD KEY `Student_ID` (`Student_ID`),
  ADD KEY `Room_ID` (`Room_ID`);

--
-- Indexes for table `maintenancelog`
--
ALTER TABLE `maintenancelog`
  ADD PRIMARY KEY (`Log_ID`),
  ADD KEY `Room_ID` (`Room_ID`),
  ADD KEY `Staff_ID` (`Staff_ID`);

--
-- Indexes for table `maintenancestaff`
--
ALTER TABLE `maintenancestaff`
  ADD PRIMARY KEY (`Staff_ID`),
  ADD UNIQUE KEY `Staff_Email` (`Staff_Email`);

--
-- Indexes for table `payment`
--
ALTER TABLE `payment`
  ADD PRIMARY KEY (`Payment_ID`),
  ADD KEY `Student_ID` (`Student_ID`);

--
-- Indexes for table `repairrequest`
--
ALTER TABLE `repairrequest`
  ADD PRIMARY KEY (`Request_ID`),
  ADD KEY `Student_ID` (`Student_ID`),
  ADD KEY `Room_ID` (`Room_ID`),
  ADD KEY `Staff_ID` (`Staff_ID`);

--
-- Indexes for table `room`
--
ALTER TABLE `room`
  ADD PRIMARY KEY (`Room_ID`);

--
-- Indexes for table `student`
--
ALTER TABLE `student`
  ADD PRIMARY KEY (`Student_ID`),
  ADD UNIQUE KEY `Student_Email` (`Student_Email`),
  ADD KEY `Room_ID` (`Room_ID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `Admin_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `appointment`
--
ALTER TABLE `appointment`
  MODIFY `Appointment_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `booking`
--
ALTER TABLE `booking`
  MODIFY `Booking_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `maintenancelog`
--
ALTER TABLE `maintenancelog`
  MODIFY `Log_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `maintenancestaff`
--
ALTER TABLE `maintenancestaff`
  MODIFY `Staff_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `payment`
--
ALTER TABLE `payment`
  MODIFY `Payment_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `repairrequest`
--
ALTER TABLE `repairrequest`
  MODIFY `Request_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `room`
--
ALTER TABLE `room`
  MODIFY `Room_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `student`
--
ALTER TABLE `student`
  MODIFY `Student_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `appointment`
--
ALTER TABLE `appointment`
  ADD CONSTRAINT `appointment_ibfk_1` FOREIGN KEY (`Student_ID`) REFERENCES `student` (`Student_ID`) ON DELETE CASCADE,
  ADD CONSTRAINT `appointment_ibfk_2` FOREIGN KEY (`Room_ID`) REFERENCES `room` (`Room_ID`) ON DELETE CASCADE;

--
-- Constraints for table `booking`
--
ALTER TABLE `booking`
  ADD CONSTRAINT `booking_ibfk_1` FOREIGN KEY (`Student_ID`) REFERENCES `student` (`Student_ID`) ON DELETE CASCADE,
  ADD CONSTRAINT `booking_ibfk_2` FOREIGN KEY (`Room_ID`) REFERENCES `room` (`Room_ID`) ON DELETE CASCADE;

--
-- Constraints for table `maintenancelog`
--
ALTER TABLE `maintenancelog`
  ADD CONSTRAINT `maintenancelog_ibfk_1` FOREIGN KEY (`Room_ID`) REFERENCES `room` (`Room_ID`) ON DELETE CASCADE,
  ADD CONSTRAINT `maintenancelog_ibfk_2` FOREIGN KEY (`Staff_ID`) REFERENCES `maintenancestaff` (`Staff_ID`) ON DELETE CASCADE;

--
-- Constraints for table `payment`
--
ALTER TABLE `payment`
  ADD CONSTRAINT `payment_ibfk_1` FOREIGN KEY (`Student_ID`) REFERENCES `student` (`Student_ID`) ON DELETE CASCADE;

--
-- Constraints for table `repairrequest`
--
ALTER TABLE `repairrequest`
  ADD CONSTRAINT `repairrequest_ibfk_1` FOREIGN KEY (`Student_ID`) REFERENCES `student` (`Student_ID`) ON DELETE CASCADE,
  ADD CONSTRAINT `repairrequest_ibfk_2` FOREIGN KEY (`Room_ID`) REFERENCES `room` (`Room_ID`) ON DELETE CASCADE,
  ADD CONSTRAINT `repairrequest_ibfk_3` FOREIGN KEY (`Staff_ID`) REFERENCES `maintenancestaff` (`Staff_ID`) ON DELETE SET NULL;

--
-- Constraints for table `student`
--
ALTER TABLE `student`
  ADD CONSTRAINT `student_ibfk_1` FOREIGN KEY (`Room_ID`) REFERENCES `room` (`Room_ID`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

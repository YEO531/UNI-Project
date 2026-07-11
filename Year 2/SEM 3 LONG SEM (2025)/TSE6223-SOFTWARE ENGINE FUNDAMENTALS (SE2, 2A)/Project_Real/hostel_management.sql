-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 17, 2025 at 08:55 AM
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
  `Admin_Password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`Admin_ID`, `Admin_Name`, `Admin_Email`, `Admin_Phone`, `Admin_Password`) VALUES
(1, 'JoGio', 'jojolee123@gmail.com', '018-6745123', '$2y$10$02LD1/UI36R0GJu8EJ6ntOca48jazwqUZuY0L1wuXa1LsyXSd5Jtq');

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

-- --------------------------------------------------------

--
-- Table structure for table `repairrequest`
--

CREATE TABLE `repairrequest` (
  `Request_ID` int(11) NOT NULL,
  `Student_ID` int(11) NOT NULL,
  `Room_ID` int(11) NOT NULL,
  `Description` text NOT NULL,
  `Request_Date` date NOT NULL,
  `Scheduled_Date` date DEFAULT NULL,
  `Status` enum('Pending','Scheduled','In Progress','Completed','Cancelled') DEFAULT 'Pending',
  `Staff_ID` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(1, 'Single', 2, 0, 'Available');

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
  `Room_ID` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student`
--

INSERT INTO `student` (`Student_ID`, `Student_Name`, `Student_Email`, `Student_Phone`, `Student_Password`, `Room_ID`) VALUES
(1, 'Deadpool', 'xforce@gmail.com', '017-9807655', '$2y$10$zcgC4xfOEw58IsPQcI83MeQwtsq7ou.g50pjJxY3HEp79dpF4yLma', NULL);

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
  MODIFY `Appointment_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `booking`
--
ALTER TABLE `booking`
  MODIFY `Booking_ID` int(11) NOT NULL AUTO_INCREMENT;

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
  MODIFY `Payment_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `repairrequest`
--
ALTER TABLE `repairrequest`
  MODIFY `Request_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `room`
--
ALTER TABLE `room`
  MODIFY `Room_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `student`
--
ALTER TABLE `student`
  MODIFY `Student_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

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

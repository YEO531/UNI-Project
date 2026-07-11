// Creation of Database 

CREATE DATABASE EventVenueBookingSystem;
use EventVenueBookingSystem;

CREATE TABLE `venue` (
  `VenueID` varchar(50) NOT NULL,
  `Name` varchar(100) NOT NULL,
  `Location` text NOT NULL,
  `Capacity` int(11) NOT NULL,
  `PricePerHour` decimal(10,2) NOT NULL,
  `AvailabilityStatus` enum('Available','Unavailable','','') NOT NULL
)

CREATE TABLE `venue_facility` (
  `VF_ID` varchar(50) NOT NULL,
  `VenueID` varchar(50) NOT NULL,
  `FacilityID` varchar(50) NOT NULL
) 

CREATE TABLE `user` (
  `UserID` varchar(50) NOT NULL,
  `Name` varchar(100) NOT NULL,
  `Email` varchar(150) NOT NULL,
  `Phone` varchar(15) NOT NULL,
  `Password` varchar(255) NOT NULL,
  `Role` enum('Admin','Customer','','') NOT NULL
) 

CREATE TABLE `review` (
  `ReviewID` varchar(50) NOT NULL,
  `VenueID` varchar(50) NOT NULL,
  `UserID` varchar(50) NOT NULL,
  `Rating` int(11) NOT NULL,
  `Comment` text NOT NULL,
  `ReviewDate` date NOT NULL
) 

CREATE TABLE `payment` (
  `PaymentID` varchar(50) NOT NULL,
  `BookingID` varchar(50) NOT NULL,
  `UserID` varchar(50) NOT NULL,
  `PaymentDate` date NOT NULL,
  `PaymentMethod` enum('Credit Card / Debit Card','Online Transfer','Ewallet','') NOT NULL,
  `Payment Amount` decimal(10,2) NOT NULL,
  `PaymentStatus` enum('Paid','Pending','Failed','') NOT NULL
)

CREATE TABLE `facility` (
  `FacilityID` varchar(50) NOT NULL,
  `FacilityName` varchar(100) NOT NULL,
  `Description` text NOT NULL
) 

CREATE TABLE `booking` (
  `BookingID` varchar(50) NOT NULL,
  `VenueID` varchar(50) NOT NULL,
  `UserID` varchar(50) NOT NULL,
  `BookingDate` date NOT NULL,
  `StartTime` time NOT NULL,
  `EndTime` time NOT NULL,
  `TotalPrice` decimal(10,2) NOT NULL,
  `BookingStatus` enum('Pending','Confirmed','Cancelled','') NOT NULL
)



// Inserting values into the tables


INSERT INTO `booking` (`BookingID`, `VenueID`, `UserID`, `BookingDate`, `StartTime`, `EndTime`, `TotalPrice`, `BookingStatus`) VALUES
('B00001', 'V00001', 'U00002', '2025-02-15', '14:00:00', '18:00:00', 4000.00, 'Confirmed'),
('B00002', 'V00002', 'U00003', '2025-02-20', '09:00:00', '13:00:00', 2000.00, 'Pending'),
('B00003', 'V00003', 'U00004', '2025-03-01', '10:00:00', '15:00:00', 3750.00, 'Confirmed'),
('B00004', 'V00004', 'U00002', '2025-03-05', '16:00:00', '20:00:00', 2400.00, 'Confirmed'),
('B00005', 'V00005', 'U00003', '2025-03-10', '08:00:00', '16:00:00', 16000.00, 'Confirmed'),
('B00006', 'V00001', 'U00001', '2025-03-13', '12:00:00', '18:00:00', 2500.00, 'Confirmed');


INSERT INTO `facility` (`FacilityID`, `FacilityName`, `Description`) VALUES
('F00001', 'Sound System', 'Professional grade audio equipment with speakers and microphones'),
('F00002', 'Projector', 'High-definition projector with 300-inch screen'),
('F00003', 'Catering Kitchen', 'Fully equipped kitchen for event catering'),
('F00004', 'WiFi', 'High-speed wireless internet connection'),
('F00005', 'Stage', 'Elevated platform with lighting system');


INSERT INTO `payment` (`PaymentID`, `BookingID`, `UserID`, `PaymentDate`, `PaymentMethod`, `Payment Amount`, `PaymentStatus`) VALUES
('P00001', 'B00001', 'U00002', '2025-02-01', 'Credit Card / Debit Card', 4000.00, 'Paid'),
('P00002', 'B00002', 'U00003', '2025-02-05', 'Online Transfer', 2000.00, 'Pending'),
('P00003', 'B00003', 'U00004', '2025-02-15', 'Ewallet', 3750.00, 'Paid'),
('P00004', 'B00004', 'U00002', '2025-02-20', 'Credit Card / Debit Card', 2400.00, 'Paid'),
('P00005', 'B00005', 'U00003', '2025-02-25', 'Online Transfer', 16000.00, 'Pending');


INSERT INTO `review` (`ReviewID`, `VenueID`, `UserID`, `Rating`, `Comment`, `ReviewDate`) VALUES
('R00001', 'V00001', 'U00002', 5, 'Excellent venue with great facilities. The sound system was perfect for our event.', '2025-02-16'),
('R00002', 'V00002', 'U00003', 4, 'Beautiful garden setting, perfect for our afternoon event. Could use better parking facilities.', '2025-02-21'),
('R00003', 'V00003', 'U00004', 5, 'Professional setup and helpful staff. Will definitely book again.', '2025-03-02'),
('R00004', 'V00004', 'U00002', 4, 'Amazing view from the rooftop. The sunset made our event spectacular.', '2025-03-06'),
('R00005', 'V00001', 'U00003', 5, 'The grand ballroom exceeded our expectations. Perfect for our corporate event.', '2025-03-11');


INSERT INTO `user` (`UserID`, `Name`, `Email`, `Phone`, `Password`, `Role`) VALUES
('U00001', 'John Smith', 'john.smith@email.com', '+60123456789', 'hashed_password_1', 'Admin'),
('U00002', 'Sarah Lee', 'sarah.lee@email.com', '+60123456790', 'hashed_password_2', 'Customer'),
('U00003', 'Michael Tan', 'michael.tan@email.com', '+60123456791', 'hashed_password_3', 'Customer'),
('U00004', 'Lisa Wong', 'lisa.wong@email.com', '+60123456792', 'hashed_password_4', 'Customer'),
('U00005', 'David Chen', 'david.chen@email.com', '+60123456793', 'hashed_password_5', 'Customer');


INSERT INTO `venue` (`VenueID`, `Name`, `Location`, `Capacity`, `PricePerHour`, `AvailabilityStatus`) VALUES
('V00001', 'Grand Ballroom', '123 Main Street, Kuala Lumpur', 500, 1000.00, 'Available'),
('V00002', 'Garden Pavilion', '456 Park Road, Petaling Jaya', 200, 500.00, 'Available'),
('V00003', 'Conference Hall A', '789 Business Avenue, Shah Alam', 300, 750.00, 'Available'),
('V00004', 'Rooftop Terrace', '321 Sky Tower, Kuala Lumpur', 150, 600.00, 'Available'),
('V00005', 'Exhibition Hall', '654 Convention Center, Putrajaya', 1000, 2000.00, 'Unavailable');



INSERT INTO `venue_facility` (`VF_ID`, `VenueID`, `FacilityID`) VALUES
('VF00001', 'V00001', 'F00001'),
('VF00002', 'V00001', 'F00002'),
('VF00003', 'V00002', 'F00001'),
('VF00004', 'V00003', 'F00004'),
('VF00005', 'V00004', 'F00005');

//Alter Tables

ALTER TABLE `booking`
  ADD PRIMARY KEY (`BookingID`),
  ADD KEY `user` (`UserID`),
  ADD KEY `venue` (`VenueID`);

ALTER TABLE `facility`
  ADD PRIMARY KEY (`FacilityID`);

ALTER TABLE `payment`
  ADD PRIMARY KEY (`PaymentID`),
  ADD KEY `payment` (`BookingID`),
  ADD KEY `user4` (`UserID`);

ALTER TABLE `review`
  ADD PRIMARY KEY (`ReviewID`),
  ADD KEY `user2` (`UserID`),
  ADD KEY `venue2` (`VenueID`);

ALTER TABLE `user`
  ADD PRIMARY KEY (`UserID`);

ALTER TABLE `venue`
  ADD PRIMARY KEY (`VenueID`);

ALTER TABLE `venue_facility`
  ADD PRIMARY KEY (`VF_ID`),
  ADD KEY `Venue3` (`VenueID`),
  ADD KEY `Facility` (`FacilityID`);


// DML Commands

SELECT * FROM booking;
SELECT * FROM venue WHERE Capacity > 300;
SELECT * FROM review WHERE VenueID = 'V00001';

UPDATE booking SET BookingStatus = 'Cancelled' WHERE BookingID = 'B00002';

DELETE FROM user WHERE UserID = 'U00005';


// Nested Query

SELECT * FROM USER
    WHERE USERID IN (
    SELECT DISTINCT USERID FROM BOOKING
    WHERE BOOKINGSTATUS = 'CONFIRMED');


// Grouping 

SELECT BookingStatus, COUNT(*) AS BookingCount
    FROM booking
    GROUP BY BookingStatus;


// Aggregate Function

SELECT
    MAX(PricePerHour) AS MaxPrice,
    MIN(PricePerHour) AS MinPrice,
    AVG(PricePerHour) AS AvgPrice
    FROM venue;


// Query which access more than 1 table

SELECT
    booking.BookingID,
    user.name AS UserName,
    venue.name AS VenueName,
    booking.BookingDate,
    booking.TotalPrice
    FROM booking
    JOIN user ON booking.UserID = user.UserID
    JOIN venue ON booking.VenueID = venue.VenueID;

//Trigger

DELIMITER //
CREATE TRIGGER updateVenueStatus AFTER UPDATE ON booking
FOR EACH ROW
BEGIN
    IF NEW.BookingStatus = 'Confirmed' THEN
        UPDATE venue
        SET AvailabilityStatus = 'Unavailable'
        WHERE VenueID = NEW.VenueID;
    ELSEIF NEW.BookingStatus = 'Cancelled' THEN
        UPDATE venue
        SET AvailabilityStatus = 'Available'
        WHERE VenueID = NEW.VenueID;
    END IF;
END //
DELIMITER ;

Update booking SET bookingStatus = 'Cancelled' Where BookingID =
'B00005';

SELECT * FROM Venue;


//Stored Procedure

DELIMITER //
CREATE PROCEDURE GetUserBookings(IN user_id VARCHAR(50))
BEGIN
    SELECT b.BookingID, v.Name, b.BookingDate, b.StartTime, b.EndTime, b.TotalPrice, b.BookingStatus
    FROM booking b
    JOIN venue v ON b.VenueID = v.VenueID
    WHERE b.UserID = user_id;
END //
DELIMITER ;

CALL GetUserBookings('U00002');


//Function 

DELIMITER //
CREATE FUNCTION GetVenueRevenue(venue_id VARCHAR(50))
RETURNS DECIMAL(10, 2)
BEGIN
    DECLARE revenue DECIMAL(10, 2);
    SELECT SUM(TotalPrice) INTO revenue
    FROM booking
    WHERE VenueID = venue_id AND BookingStatus = 'Confirmed';
 
    RETURN revenue;
END //
DELIMITER ;

INSERT INTO `booking` (`BookingID`, `VenueID`, `UserID`, `BookingDate`, `StartTime`, `EndTime`, `TotalPrice`, `BookingStatus`) VALUES ('B00006', 'V00001', 'U00001', '2025-03-13', '12:00:00', '18:00:00', '2500', 'Confirmed');

SELECT GetVenueRevenue('V00001');



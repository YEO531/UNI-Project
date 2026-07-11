-- MariaDB dump 10.19  Distrib 10.4.28-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: librarymanagementsystem
-- ------------------------------------------------------
-- Server version	10.4.28-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `author`
--

DROP TABLE IF EXISTS `author`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `author` (
  `AUTHOR_ID` char(4) NOT NULL,
  `AUTHOR_FNAME` varchar(50) NOT NULL,
  `AUTHOR_LNAME` varchar(50) NOT NULL,
  PRIMARY KEY (`AUTHOR_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `author`
--

LOCK TABLES `author` WRITE;
/*!40000 ALTER TABLE `author` DISABLE KEYS */;
INSERT INTO `author` VALUES ('0001','George','RR.Martin'),('0002','Rene','Fester Kratz'),('0003','Eric','Matthis'),('0004','Frank','Herbert'),('0005','Brandon','Sanderson');
/*!40000 ALTER TABLE `author` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `book`
--

DROP TABLE IF EXISTS `book`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `book` (
  `BOOK_ID` char(5) NOT NULL,
  `ISBN` char(13) NOT NULL,
  `BOOK_NAME` varchar(40) DEFAULT NULL,
  `BOOK_PUBLISHER` varchar(70) DEFAULT NULL,
  `PUBLICATION_DATE` date DEFAULT NULL,
  `BOOK_CATEGORY` varchar(20) DEFAULT NULL,
  `BOOK_COPIES` int(11) DEFAULT NULL,
  PRIMARY KEY (`BOOK_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `book`
--

LOCK TABLES `book` WRITE;
/*!40000 ALTER TABLE `book` DISABLE KEYS */;
INSERT INTO `book` VALUES ('11012','9781250318541','Mistborn: The Final Empire','Tor Fantasy','2019-09-24','Fantasy',1),('12015','9781524796303','Fire and Blood','Bantam','2018-11-28','Fantasy',2),('20010','9781119345374','Biology For Dummies','For Dummies','2017-03-20','Non-Fic',1),('20013','9781718502703','Python Crash Course','No Starch Press','2023-01-10','Non-Fic',3),('30081','9780441013593','Dune','Penguin Publishing Group','2005-08-02','Sci-Fi',3);
/*!40000 ALTER TABLE `book` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `book_author`
--

DROP TABLE IF EXISTS `book_author`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `book_author` (
  `BOOK_ID` char(5) NOT NULL,
  `AUTHOR_ID` char(4) NOT NULL,
  PRIMARY KEY (`BOOK_ID`,`AUTHOR_ID`),
  KEY `AUTHOR_ID` (`AUTHOR_ID`),
  CONSTRAINT `book_author_ibfk_1` FOREIGN KEY (`BOOK_ID`) REFERENCES `book` (`BOOK_ID`) ON UPDATE CASCADE,
  CONSTRAINT `book_author_ibfk_2` FOREIGN KEY (`AUTHOR_ID`) REFERENCES `author` (`AUTHOR_ID`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `book_author`
--

LOCK TABLES `book_author` WRITE;
/*!40000 ALTER TABLE `book_author` DISABLE KEYS */;
INSERT INTO `book_author` VALUES ('11012','0005'),('12015','0001'),('20010','0002'),('20013','0003'),('30081','0004');
/*!40000 ALTER TABLE `book_author` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `borrowing_details`
--

DROP TABLE IF EXISTS `borrowing_details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `borrowing_details` (
  `BORROW_ID` char(5) NOT NULL,
  `BORROWED_DATE` date DEFAULT NULL,
  `DUE_DATE` date DEFAULT NULL,
  `RETURN_DATE` date DEFAULT NULL,
  `FINE_AMOUNT` decimal(6,2) DEFAULT 0.00,
  `USER_ID` char(10) DEFAULT NULL,
  `BOOK_ID` char(5) DEFAULT NULL,
  `LIB_ID` char(5) DEFAULT NULL,
  PRIMARY KEY (`BORROW_ID`),
  KEY `USER_ID` (`USER_ID`),
  KEY `BOOK_ID` (`BOOK_ID`),
  KEY `LIB_ID` (`LIB_ID`),
  CONSTRAINT `borrowing_details_ibfk_1` FOREIGN KEY (`USER_ID`) REFERENCES `user` (`USER_ID`) ON UPDATE CASCADE,
  CONSTRAINT `borrowing_details_ibfk_2` FOREIGN KEY (`BOOK_ID`) REFERENCES `book` (`BOOK_ID`) ON UPDATE CASCADE,
  CONSTRAINT `borrowing_details_ibfk_3` FOREIGN KEY (`LIB_ID`) REFERENCES `librarian` (`LIB_ID`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `borrowing_details`
--

LOCK TABLES `borrowing_details` WRITE;
/*!40000 ALTER TABLE `borrowing_details` DISABLE KEYS */;
INSERT INTO `borrowing_details` VALUES ('00012','2023-08-23','2023-08-30','2023-08-29',0.00,'1201289310','12015','10012'),('00013','2023-08-25','2023-09-01','2023-08-31',0.00,'1198077102','20010','10120'),('00014','2023-09-02','2023-09-09','2023-09-04',0.00,'1210805131','20013','10082'),('00015','2023-10-02','2023-10-09','2023-10-17',0.00,'1085827321','30081','10110'),('00016','2023-10-18','2023-10-25','2023-10-28',3.00,'1213808944','11012','10112'),('00017','2023-10-13','2023-10-20','2023-10-17',0.00,'1201289310','30081','10012'),('00018','2023-10-20','2023-10-27','2023-10-30',3.00,'1213808944','20013','10082');
/*!40000 ALTER TABLE `borrowing_details` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER updateBook_Copies 
AFTER INSERT ON BORROWING_DETAILS
FOR EACH ROW
BEGIN 
IF NEW.RETURN_DATE IS NULL THEN
UPDATE BOOK
SET BOOK_COPIES = BOOK_COPIES - 1
WHERE BOOK_ID = NEW.BOOK_ID;
END IF;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `librarian`
--

DROP TABLE IF EXISTS `librarian`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `librarian` (
  `LIB_ID` char(5) NOT NULL,
  `LIB_FNAME` varchar(50) NOT NULL,
  `LIB_LNAME` varchar(50) NOT NULL,
  `LIB_EMAIL` varchar(40) DEFAULT NULL,
  `LIB_PHONENUM` varchar(22) DEFAULT NULL,
  PRIMARY KEY (`LIB_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `librarian`
--

LOCK TABLES `librarian` WRITE;
/*!40000 ALTER TABLE `librarian` DISABLE KEYS */;
INSERT INTO `librarian` VALUES ('10012','Tan','Kong Ming','TKM8345@gmail.com','017-5146141'),('10082','Muthu','Ganesh','Muthu123@gmail.com','017-8375232'),('10110','Koh','Jia Xin','KJY0302@gmail.com','016-5821010'),('10112','Siti','Abu Bakar','Siti1024@gmail.com','017-2476050'),('10120','Lee','Keng Hao','LjKH520@gmail.com','017-3523427');
/*!40000 ALTER TABLE `librarian` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user` (
  `USER_ID` char(10) NOT NULL,
  `USER_FNAME` varchar(20) NOT NULL,
  `USER_LNAME` varchar(20) NOT NULL,
  `USER_TYPE` varchar(20) DEFAULT NULL,
  `USER_EMAIL` varchar(40) DEFAULT NULL,
  `USER_PHONENUM` varchar(22) DEFAULT NULL,
  PRIMARY KEY (`USER_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user`
--

LOCK TABLES `user` WRITE;
/*!40000 ALTER TABLE `user` DISABLE KEYS */;
INSERT INTO `user` VALUES ('1085827321','Rahul','Raj','Student','RahulR@gmail.com','018-8976543'),('1198077102','Loh','Jie Ying','Lecturer','LjY0813@gmail.com','012-6768302'),('1201289310','Tan','Yik Xian','Student','txy0916@gmail.com','017-6975101'),('1210805131','Sarwin','Sukumaran','Student','SS0129@gmail.com','017-2309581'),('1213808944','Chan','Zu Meng','Student','CZM3341@gmail.com','012-7834321');
/*!40000 ALTER TABLE `user` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2024-01-23 20:49:07

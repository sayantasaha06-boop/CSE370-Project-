-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 21, 2025 at 03:24 AM
-- Server version: 10.4.22-MariaDB
-- PHP Version: 8.1.2

-- SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
-- START TRANSACTION;
-- SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `catering`
--

-- --------------------------------------------------------

--
-- Table structure for table `customer`
--
CREATE Database catering;
use catering;
CREATE Database catering;
use catering;

CREATE TABLE `customer` (
  `Name` varchar(50) NOT NULL,
  `Customer_ID` int(11) NOT NULL,
  `Email` varchar(50) NOT NULL,
  `Phone` int(11) NOT NULL,
  `Address` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `customer`
--

INSERT INTO `customer` (`Name`, `Customer_ID`, `Email`, `Phone`, `Address`) VALUES
('Miftah', 1, 'abc@gmail.com', 4321321, 'Dhaka');

-- -----------C---------------------------------------------

--
-- Table structure for table `delivary`
--

CREATE TABLE `delivary` (
  `Vehicle no.` int(11) NOT NULL,
  `Driver_Name` varchar(50) NOT NULL,
  `Delivary_time` varchar(20) NOT NULL,
  `Delivered` varchar(20) NOT NULL,
  `Scheduled` varchar(50) NOT NULL,
  `our_for_delivary` varchar(50) NOT NULL,
  `D_ID` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `event`
--

CREATE TABLE `event` (
  `Event_Id` int(11) NOT NULL,
  `Date` int(11) NOT NULL,
  `Location` varchar(50) NOT NULL,
  `Total_Cost` int(11) NOT NULL,
  `Number_of_Guest` int(11) NOT NULL,
  `Wedding` varchar(50) NOT NULL,
  `party` varchar(50) NOT NULL,
  `Corporate` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `menu`
--

CREATE TABLE `menu` (
  `ITEM NAME` varchar(100) NOT NULL,
  `M_id` int(11) NOT NULL,
  `Delivery time` varchar(50) NOT NULL,
  `Price` int(11) NOT NULL,
  `Veg` varchar(50) NOT NULL,
  `Non-Veg` varchar(50) NOT NULL,
  `Drinks` varchar(50) NOT NULL,
  `C_ID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `stuff`
--

CREATE TABLE `stuff` (
  `Name` varchar(50) NOT NULL,
  `Contact_number` int(11) NOT NULL,
  `Staff_Id` int(11) NOT NULL,
  `Availability_Status` varchar(50) NOT NULL,
  `Chef` varchar(50) NOT NULL,
  `Waiter` varchar(50) NOT NULL,
  `Delivary_man` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `customer`
--
ALTER TABLE `customer`
  ADD PRIMARY KEY (`Customer_ID`);

--
-- Indexes for table `delivary`
--
ALTER TABLE `delivary`
  ADD PRIMARY KEY (`D_ID`);

--
-- Indexes for table `event`
--
ALTER TABLE `event`
  ADD PRIMARY KEY (`Event_Id`);

--
-- Indexes for table `menu`
--
ALTER TABLE `menu`
  ADD PRIMARY KEY (`M_id`),
  ADD KEY `new` (`C_ID`);

--
-- Indexes for table `stuff`
--
ALTER TABLE `stuff`
  ADD PRIMARY KEY (`Staff_Id`);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `menu`
--
ALTER TABLE `menu`
  ADD CONSTRAINT `new` FOREIGN KEY (`C_ID`) REFERENCES `customer` (`Customer_ID`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

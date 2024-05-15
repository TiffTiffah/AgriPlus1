-- phpMyAdmin SQL Dump
-- version 5.0.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 14, 2024 at 11:27 PM
-- Server version: 10.4.11-MariaDB
-- PHP Version: 7.4.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `agri`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_log`
--

CREATE TABLE `activity_log` (
  `log_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `activity_message` text NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `activity_log`
--

INSERT INTO `activity_log` (`log_id`, `user_id`, `activity_message`, `timestamp`) VALUES
(1, 36, 'User with email logged in at 2024-05-13 21:57:20', '2024-05-13 18:57:20'),
(2, 36, 'User with email logged in at 2024-05-13 22:01:08', '2024-05-13 19:01:08'),
(3, 36, 'User with email logged in at 2024-05-13 22:03:27', '2024-05-13 19:03:27'),
(4, 36, 'Logged out', '2024-05-13 20:50:52'),
(5, 36, 'User logged in at 2024-05-13 22:53:20', '2024-05-13 19:53:20'),
(6, 36, 'User logged out', '2024-05-13 21:36:55'),
(7, 36, 'User logged out', '2024-05-13 21:38:41'),
(8, 36, 'User logged in at ', '2024-05-13 20:39:38'),
(9, 36, 'User logged in at ', '2024-05-13 20:42:14'),
(10, 36, 'User logged out', '2024-05-13 21:43:44'),
(11, 36, 'User logged in at ', '2024-05-14 07:16:13'),
(12, 36, 'User logged in at ', '2024-05-14 07:26:18'),
(13, 36, 'User logged in at ', '2024-05-14 08:09:36'),
(14, 36, 'User logged out', '2024-05-14 09:10:29'),
(15, 36, 'User logged out', '2024-05-14 09:10:29'),
(16, 36, 'User logged out', '2024-05-14 09:10:29'),
(17, 36, 'User logged out', '2024-05-14 09:10:29'),
(18, 36, 'User logged out', '2024-05-14 09:10:29'),
(19, 36, 'User logged in at ', '2024-05-14 08:10:52'),
(20, 36, 'User logged in at ', '2024-05-14 10:12:02'),
(21, 37, 'User logged out', '2024-05-14 12:31:07'),
(22, 37, 'User logged in at ', '2024-05-14 11:31:17'),
(23, 37, 'User logged out', '2024-05-14 16:28:18'),
(24, 37, 'User logged in at ', '2024-05-14 15:34:31'),
(25, 36, 'User logged in at ', '2024-05-14 15:54:17'),
(26, 36, 'User logged out', '2024-05-14 18:45:02'),
(27, 36, 'User logged in at ', '2024-05-14 17:46:36'),
(28, 36, 'User logged out', '2024-05-14 19:28:18'),
(29, 36, 'User logged in at ', '2024-05-14 19:58:57'),
(30, 36, 'User logged in at ', '2024-05-14 19:59:00'),
(31, 36, 'User logged in at ', '2024-05-14 19:59:03'),
(32, 36, 'User logged in at ', '2024-05-14 19:59:03'),
(33, 36, 'User logged in at ', '2024-05-14 19:59:06'),
(34, 36, 'User logged in at ', '2024-05-14 19:59:06'),
(35, 36, 'User logged in at ', '2024-05-14 19:59:06'),
(36, 36, 'User logged in at ', '2024-05-14 19:59:06'),
(37, 36, 'User logged in at ', '2024-05-14 19:59:06');

-- --------------------------------------------------------

--
-- Table structure for table `areamapping`
--

CREATE TABLE `areamapping` (
  `EncodedValue` int(11) NOT NULL,
  `Area` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `areamapping`
--

INSERT INTO `areamapping` (`EncodedValue`, `Area`) VALUES
(0, 'Albania'),
(1, 'Algeria'),
(2, 'Angola'),
(3, 'Argentina'),
(4, 'Armenia'),
(5, 'Australia'),
(6, 'Austria'),
(7, 'Azerbaijan'),
(8, 'Bahamas'),
(9, 'Bahrain'),
(10, 'Bangladesh'),
(11, 'Belarus'),
(12, 'Belgium'),
(13, 'Botswana'),
(14, 'Brazil'),
(15, 'Bulgaria'),
(16, 'Burkina Faso'),
(17, 'Burundi'),
(18, 'Cameroon'),
(19, 'Canada'),
(20, 'Central African Republic'),
(21, 'Chile'),
(22, 'Colombia'),
(23, 'Croatia'),
(24, 'Denmark'),
(25, 'Dominican Republic'),
(26, 'Ecuador'),
(27, 'Egypt'),
(28, 'El Salvador'),
(29, 'Eritrea'),
(30, 'Estonia'),
(31, 'Finland'),
(32, 'France'),
(33, 'Germany'),
(34, 'Ghana'),
(35, 'Greece'),
(36, 'Guatemala'),
(37, 'Guinea'),
(38, 'Guyana'),
(39, 'Haiti'),
(40, 'Honduras'),
(41, 'Hungary'),
(42, 'India'),
(43, 'Indonesia'),
(44, 'Iraq'),
(45, 'Ireland'),
(46, 'Italy'),
(47, 'Jamaica'),
(48, 'Japan'),
(49, 'Kazakhstan'),
(50, 'Kenya'),
(51, 'Latvia'),
(52, 'Lebanon'),
(53, 'Lesotho'),
(54, 'Libya'),
(55, 'Lithuania'),
(56, 'Madagascar'),
(57, 'Malawi'),
(58, 'Malaysia'),
(59, 'Mali'),
(60, 'Mauritania'),
(61, 'Mauritius'),
(62, 'Mexico'),
(63, 'Montenegro'),
(64, 'Morocco'),
(65, 'Mozambique'),
(66, 'Namibia'),
(67, 'Nepal'),
(68, 'Netherlands'),
(69, 'New Zealand'),
(70, 'Nicaragua'),
(71, 'Niger'),
(72, 'Norway'),
(73, 'Pakistan'),
(74, 'Papua New Guinea'),
(75, 'Peru'),
(76, 'Poland'),
(77, 'Portugal'),
(78, 'Qatar'),
(79, 'Romania'),
(80, 'Rwanda'),
(81, 'Saudi Arabia'),
(82, 'Senegal'),
(83, 'Slovenia'),
(84, 'South Africa'),
(85, 'Spain'),
(86, 'Sri Lanka'),
(87, 'Sudan'),
(88, 'Suriname'),
(89, 'Sweden'),
(90, 'Switzerland'),
(91, 'Tajikistan'),
(92, 'Thailand'),
(93, 'Tunisia'),
(94, 'Turkey'),
(95, 'Uganda'),
(96, 'Ukraine'),
(97, 'United Kingdom'),
(98, 'Uruguay'),
(99, 'Zambia'),
(100, 'Zimbabwe');

-- --------------------------------------------------------

--
-- Table structure for table `crops`
--

CREATE TABLE `crops` (
  `CropID` int(11) NOT NULL,
  `FarmID` int(11) DEFAULT NULL,
  `CropName` varchar(100) DEFAULT NULL,
  `PlantingDate` date DEFAULT NULL,
  `ExpectedHarvestDate` date DEFAULT NULL,
  `LastHarvestYield` int(11) DEFAULT NULL,
  `GrowthStage` varchar(50) DEFAULT NULL,
  `WateringNeeds` varchar(50) DEFAULT NULL,
  `HealthStatus` varchar(50) DEFAULT NULL,
  `CreatedAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `CultivatedArea` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `crops`
--

INSERT INTO `crops` (`CropID`, `FarmID`, `CropName`, `PlantingDate`, `ExpectedHarvestDate`, `LastHarvestYield`, `GrowthStage`, `WateringNeeds`, `HealthStatus`, `CreatedAt`, `CultivatedArea`) VALUES
(16, 28, 'Potatoes', '2024-03-13', '2024-07-13', 456, '', '', '', '2024-05-12 22:54:10', '2.00'),
(17, 28, 'Sorghum', '2024-02-27', '2024-07-28', 74, '', '', '', '2024-05-13 21:29:24', '2.00'),
(18, 28, 'Soybeans', '2024-03-17', '2024-10-14', 78, '', 'Low', 'Good', '2024-05-14 16:55:24', '2.00');

-- --------------------------------------------------------

--
-- Table structure for table `farms`
--

CREATE TABLE `farms` (
  `FarmID` int(11) NOT NULL,
  `UserID` int(11) DEFAULT NULL,
  `FarmName` varchar(255) NOT NULL,
  `Location` varchar(255) NOT NULL,
  `FarmSize` float NOT NULL,
  `IrrigationSystem` varchar(50) NOT NULL,
  `SoilType` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `farms`
--

INSERT INTO `farms` (`FarmID`, `UserID`, `FarmName`, `Location`, `FarmSize`, `IrrigationSystem`, `SoilType`) VALUES
(28, 36, 'Chorong\'i farm', 'Kiambu', 6, 'Sprinkler', 'Loam'),
(29, 37, 'Mian Farms', 'Nairobi', 8, 'Sprinkler', 'Drip');

-- --------------------------------------------------------

--
-- Table structure for table `itemmapping`
--

CREATE TABLE `itemmapping` (
  `EncodedValue` int(11) NOT NULL,
  `Item` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `itemmapping`
--

INSERT INTO `itemmapping` (`EncodedValue`, `Item`) VALUES
(0, 'Cassava'),
(1, 'Maize'),
(2, 'Plantains and others'),
(3, 'Potatoes'),
(4, 'Rice, paddy'),
(5, 'Sorghum'),
(6, 'Soybeans'),
(7, 'Sweet potatoes'),
(8, 'Wheat'),
(9, 'Yams');

-- --------------------------------------------------------

--
-- Table structure for table `model`
--

CREATE TABLE `model` (
  `id` int(11) NOT NULL,
  `model` blob DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `model`
--

INSERT INTO `model` (`id`, `model`) VALUES
(1, 0x433a5c78616d70705c6874646f63735c747261696e5c6d6f64656c2e6a6f626c6962);

-- --------------------------------------------------------

--
-- Table structure for table `soil_data`
--

CREATE TABLE `soil_data` (
  `soilID` int(11) NOT NULL,
  `farmID` int(11) NOT NULL,
  `moisture` varchar(50) NOT NULL,
  `ph` varchar(50) NOT NULL,
  `date` date NOT NULL,
  `temperature` float DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `soil_data`
--

INSERT INTO `soil_data` (`soilID`, `farmID`, `moisture`, `ph`, `date`, `temperature`) VALUES
(1, 28, '23', '7', '2024-05-14', 21),
(2, 29, '56', '8', '2024-05-14', 21),
(3, 28, 'low', '7', '2024-05-14', 21);

-- --------------------------------------------------------

--
-- Table structure for table `tasks`
--

CREATE TABLE `tasks` (
  `taskID` int(11) NOT NULL,
  `taskName` varchar(255) NOT NULL,
  `dueDate` date NOT NULL,
  `createdAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `farmID` int(11) NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `tasks`
--

INSERT INTO `tasks` (`taskID`, `taskName`, `dueDate`, `createdAt`, `farmID`, `status`) VALUES
(7, 'Fetch Sorghum seeds from AGRO', '2024-05-13', '2024-05-13 14:52:09', 28, 'Completed'),
(8, 'Collect soil samples for test', '2024-05-14', '2024-05-13 21:30:39', 28, 'Completed'),
(9, 'Add fertilizer', '2024-05-14', '2024-05-14 12:37:20', 28, 'Completed'),
(10, 'Irrigating the Maize section', '2024-05-14', '2024-05-14 12:37:32', 28, 'pending');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `UserID` int(11) NOT NULL,
  `FullName` varchar(255) NOT NULL,
  `Username` varchar(255) NOT NULL,
  `Email` varchar(255) NOT NULL,
  `PasswordHash` varchar(255) NOT NULL,
  `RegistrationDate` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`UserID`, `FullName`, `Username`, `Email`, `PasswordHash`, `RegistrationDate`) VALUES
(36, 'Anita Wangui', 'Wangui', 'anita22@gmail.com', '$2y$10$wOPLEfdj1EMdRfIVe.gk1OWISPv1lWQtgMvqlkckJVMY4HhX4/ZVy', '2024-05-12 22:50:04'),
(37, 'Azra Mian', 'Azra', 'azra@gmail.com', '$2y$10$zbkRDgv1ReoYR7o6mhXIVu1wUoKvZfumFYWaC1xjttUPVaYsF1z.C', '2024-05-14 11:55:20');

-- --------------------------------------------------------

--
-- Table structure for table `weather_data`
--

CREATE TABLE `weather_data` (
  `id` int(11) NOT NULL,
  `location` varchar(255) DEFAULT NULL,
  `month` varchar(255) DEFAULT NULL,
  `temperature` float DEFAULT NULL,
  `rainfall` float DEFAULT NULL,
  `country` varchar(255) DEFAULT NULL,
  `farmID` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `weather_data`
--

INSERT INTO `weather_data` (`id`, `location`, `month`, `temperature`, `rainfall`, `country`, `farmID`) VALUES
(11, 'Kiambu', 'April', 26.71, 175.334, 'Kenya', 28),
(12, 'Kiambu', 'March', 28.83, 73.056, 'Kenya', 28),
(13, 'Kiambu', 'February', 27.91, 29.2224, 'Kenya', 28),
(14, 'Kiambu', 'January', 26.83, 36.528, 'Kenya', 28),
(15, 'Nairobi', 'April', 27.86, 102.278, 'Kenya', 29),
(16, 'Nairobi', 'March', 30.04, 51.1392, 'Kenya', 29),
(17, 'Nairobi', 'February', 30.95, 29.2224, 'Kenya', 29),
(18, 'Nairobi', 'January', 28.63, 36.528, 'Kenya', 29);

-- --------------------------------------------------------

--
-- Table structure for table `yieldprediction`
--

CREATE TABLE `yieldprediction` (
  `predictionID` int(11) NOT NULL,
  `yield_predicted` float DEFAULT NULL,
  `month` varchar(20) DEFAULT NULL,
  `CropID` int(11) DEFAULT NULL,
  `farmID` int(11) DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `yieldprediction`
--

INSERT INTO `yieldprediction` (`predictionID`, `yield_predicted`, `month`, `CropID`, `farmID`, `timestamp`) VALUES
(51, 44264.3, 'April', 16, 28, '2024-05-12 22:54:13'),
(52, 19179.1, 'March', 16, 28, '2024-05-12 22:54:13'),
(53, 52141.6, 'February', 16, 28, '2024-05-12 22:54:13'),
(54, 61298.6, 'January', 16, 28, '2024-05-12 22:54:13'),
(55, 2855.88, 'April', 17, 28, '2024-05-13 21:29:31'),
(56, 7384.38, 'March', 17, 28, '2024-05-13 21:29:31'),
(57, 9271.53, 'February', 17, 28, '2024-05-13 21:29:31'),
(58, 9143.11, 'January', 17, 28, '2024-05-13 21:29:31'),
(59, 2486.85, 'April', 18, 28, '2024-05-14 16:55:29'),
(60, 7433.09, 'March', 18, 28, '2024-05-14 16:55:29'),
(61, 9334.81, 'February', 18, 28, '2024-05-14 16:55:29'),
(62, 9264.92, 'January', 18, 28, '2024-05-14 16:55:29');

--
-- Triggers `yieldprediction`
--
DELIMITER $$
CREATE TRIGGER `populate_yields_after_insert` AFTER INSERT ON `yieldprediction` FOR EACH ROW BEGIN
    DECLARE current_month VARCHAR(255);
    DECLARE predicted_crop_name VARCHAR(255); -- Assuming cropName is VARCHAR(255)
    DECLARE last_harvest_yield DECIMAL(10, 2); -- Assuming lastHarvestYield is DECIMAL(10, 2)



    -- Get the predicted crop name
    SELECT cropName INTO predicted_crop_name
    FROM crops
    WHERE cropID = NEW.cropID;

    -- Get the last harvest yield from crops
    SELECT lastHarvestYield INTO last_harvest_yield
    FROM crops
    WHERE cropID = NEW.cropID;

    -- Insert data into yields table
    INSERT INTO yields (cropID, cropName, lastHarvestYield, farmID, yield_predicted, month, timestamp)
    VALUES (NEW.cropID, predicted_crop_name, last_harvest_yield, NEW.farmID, NEW.yield_predicted, NEW.month, NOW());
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `yields`
--

CREATE TABLE `yields` (
  `id` int(11) NOT NULL,
  `cropID` int(11) DEFAULT NULL,
  `cropName` varchar(255) DEFAULT NULL,
  `lastHarvestYield` float DEFAULT NULL,
  `farmID` int(11) DEFAULT NULL,
  `yield_predicted` float DEFAULT NULL,
  `month` varchar(20) DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `plantingMonth` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `yields`
--

INSERT INTO `yields` (`id`, `cropID`, `cropName`, `lastHarvestYield`, `farmID`, `yield_predicted`, `month`, `timestamp`, `plantingMonth`) VALUES
(43, 16, 'Potatoes', 456, 28, 44264.3, 'April', '2024-05-12 22:54:13', NULL),
(44, 16, 'Potatoes', 456, 28, 19179.1, 'March', '2024-05-12 22:54:13', NULL),
(45, 16, 'Potatoes', 456, 28, 52141.6, 'February', '2024-05-12 22:54:13', NULL),
(46, 16, 'Potatoes', 456, 28, 61298.6, 'January', '2024-05-12 22:54:13', NULL),
(47, 17, 'Sorghum', 74, 28, 2855.88, 'April', '2024-05-13 21:29:31', NULL),
(48, 17, 'Sorghum', 74, 28, 7384.38, 'March', '2024-05-13 21:29:31', NULL),
(49, 17, 'Sorghum', 74, 28, 9271.53, 'February', '2024-05-13 21:29:31', NULL),
(50, 17, 'Sorghum', 74, 28, 9143.11, 'January', '2024-05-13 21:29:31', NULL),
(51, 18, 'Soybeans', 78, 28, 2486.85, 'April', '2024-05-14 16:55:29', NULL),
(52, 18, 'Soybeans', 78, 28, 7433.09, 'March', '2024-05-14 16:55:29', NULL),
(53, 18, 'Soybeans', 78, 28, 9334.81, 'February', '2024-05-14 16:55:29', NULL),
(54, 18, 'Soybeans', 78, 28, 9264.92, 'January', '2024-05-14 16:55:29', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_log`
--
ALTER TABLE `activity_log`
  ADD PRIMARY KEY (`log_id`);

--
-- Indexes for table `areamapping`
--
ALTER TABLE `areamapping`
  ADD PRIMARY KEY (`EncodedValue`),
  ADD UNIQUE KEY `Area` (`Area`);

--
-- Indexes for table `crops`
--
ALTER TABLE `crops`
  ADD PRIMARY KEY (`CropID`),
  ADD KEY `FarmID` (`FarmID`);

--
-- Indexes for table `farms`
--
ALTER TABLE `farms`
  ADD PRIMARY KEY (`FarmID`),
  ADD KEY `fk_user_id` (`UserID`);

--
-- Indexes for table `itemmapping`
--
ALTER TABLE `itemmapping`
  ADD PRIMARY KEY (`EncodedValue`),
  ADD UNIQUE KEY `Item` (`Item`);

--
-- Indexes for table `model`
--
ALTER TABLE `model`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `soil_data`
--
ALTER TABLE `soil_data`
  ADD PRIMARY KEY (`soilID`),
  ADD KEY `farmID` (`farmID`);

--
-- Indexes for table `tasks`
--
ALTER TABLE `tasks`
  ADD PRIMARY KEY (`taskID`),
  ADD KEY `tasks_ibfk_1` (`farmID`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`UserID`),
  ADD UNIQUE KEY `Username` (`Username`),
  ADD UNIQUE KEY `Email` (`Email`);

--
-- Indexes for table `weather_data`
--
ALTER TABLE `weather_data`
  ADD PRIMARY KEY (`id`),
  ADD KEY `weather_data_ibfk_1` (`farmID`);

--
-- Indexes for table `yieldprediction`
--
ALTER TABLE `yieldprediction`
  ADD PRIMARY KEY (`predictionID`),
  ADD KEY `farmID` (`farmID`),
  ADD KEY `yieldprediction_ibfk_1` (`CropID`);

--
-- Indexes for table `yields`
--
ALTER TABLE `yields`
  ADD PRIMARY KEY (`id`),
  ADD KEY `farmID` (`farmID`),
  ADD KEY `yields_ibfk_1` (`cropID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_log`
--
ALTER TABLE `activity_log`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT for table `crops`
--
ALTER TABLE `crops`
  MODIFY `CropID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `farms`
--
ALTER TABLE `farms`
  MODIFY `FarmID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `model`
--
ALTER TABLE `model`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `soil_data`
--
ALTER TABLE `soil_data`
  MODIFY `soilID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `tasks`
--
ALTER TABLE `tasks`
  MODIFY `taskID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `UserID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT for table `weather_data`
--
ALTER TABLE `weather_data`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `yieldprediction`
--
ALTER TABLE `yieldprediction`
  MODIFY `predictionID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=63;

--
-- AUTO_INCREMENT for table `yields`
--
ALTER TABLE `yields`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=55;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `crops`
--
ALTER TABLE `crops`
  ADD CONSTRAINT `crops_ibfk_1` FOREIGN KEY (`FarmID`) REFERENCES `farms` (`FarmID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `farms`
--
ALTER TABLE `farms`
  ADD CONSTRAINT `fk_user_id` FOREIGN KEY (`UserID`) REFERENCES `users` (`UserID`) ON DELETE CASCADE;

--
-- Constraints for table `soil_data`
--
ALTER TABLE `soil_data`
  ADD CONSTRAINT `soil_data_ibfk_1` FOREIGN KEY (`farmID`) REFERENCES `farms` (`FarmID`);

--
-- Constraints for table `tasks`
--
ALTER TABLE `tasks`
  ADD CONSTRAINT `tasks_ibfk_1` FOREIGN KEY (`farmID`) REFERENCES `farms` (`FarmID`) ON DELETE CASCADE;

--
-- Constraints for table `weather_data`
--
ALTER TABLE `weather_data`
  ADD CONSTRAINT `weather_data_ibfk_1` FOREIGN KEY (`farmID`) REFERENCES `farms` (`FarmID`) ON DELETE CASCADE;

--
-- Constraints for table `yieldprediction`
--
ALTER TABLE `yieldprediction`
  ADD CONSTRAINT `yieldprediction_ibfk_1` FOREIGN KEY (`CropID`) REFERENCES `crops` (`CropID`) ON DELETE CASCADE,
  ADD CONSTRAINT `yieldprediction_ibfk_2` FOREIGN KEY (`farmID`) REFERENCES `farms` (`FarmID`);

--
-- Constraints for table `yields`
--
ALTER TABLE `yields`
  ADD CONSTRAINT `yields_ibfk_1` FOREIGN KEY (`cropID`) REFERENCES `crops` (`CropID`) ON DELETE CASCADE,
  ADD CONSTRAINT `yields_ibfk_2` FOREIGN KEY (`farmID`) REFERENCES `farms` (`FarmID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

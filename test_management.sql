-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: db
-- Generation Time: Apr 05, 2025 at 10:32 PM
-- Server version: 5.7.44
-- PHP Version: 8.2.27

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `test_management`
--

-- --------------------------------------------------------

--
-- Table structure for table `attendees`
--

CREATE TABLE `attendees` (
  `user_id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `attendees`
--

INSERT INTO `attendees` (`user_id`, `event_id`) VALUES
(1, 1),
(3, 1),
(4, 1),
(5, 1),
(6, 1),
(7, 1),
(8, 1),
(3, 2),
(9, 2),
(10, 2),
(11, 2),
(12, 2),
(13, 2),
(1, 4),
(3, 4),
(9, 4),
(10, 4),
(11, 4),
(12, 4),
(13, 4),
(1, 5),
(4, 5),
(5, 5),
(6, 5),
(7, 5),
(8, 5),
(9, 6),
(10, 6),
(11, 6),
(12, 6),
(13, 6),
(4, 7),
(5, 7),
(6, 7),
(7, 7),
(8, 7),
(9, 8),
(10, 8),
(11, 8),
(12, 8),
(13, 8),
(4, 9),
(5, 9),
(6, 9),
(7, 9),
(8, 9),
(9, 10),
(10, 10),
(11, 10),
(12, 10),
(13, 10);

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `id` int(11) NOT NULL,
  `event_name` varchar(255) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `location` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `created_by` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`id`, `event_name`, `start_date`, `end_date`, `location`, `price`, `created_by`) VALUES
(1, 'Event 1', '2025-03-15', '2025-03-16', 'Location 1', 100.00, '1'),
(2, 'Event 2', '2025-03-20', '2025-03-21', 'Location 2', 150.00, '3'),
(4, 'Event 4', '2025-04-10', '2025-04-12', 'Location 4', 200.00, '1'),
(5, 'Event 5', '2025-05-01', '2025-05-02', 'Location 5', 180.00, '7'),
(6, 'Event 6', '2025-05-10', '2025-05-11', 'Location 6', 150.00, '1'),
(7, 'Event 7', '2025-05-15', '2025-05-16', 'Location 7', 180.00, '4'),
(8, 'Event 8', '2025-06-01', '2025-06-02', 'Location 8', 200.00, '5'),
(9, 'Event 9', '2025-06-10', '2025-06-11', 'Location 9', 220.00, '6'),
(10, 'Event 10', '2025-06-20', '2025-06-21', 'Location 10', 250.00, '7');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `api_key` varchar(12) NOT NULL,
  `auth_flag` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `api_key`, `auth_flag`) VALUES
(1, 'John Doe', 'johndoe@example.com', '$2y$10$pJVQuIfMO38ag6XrtuV/Z.KnapbxZr6D4FWIHLX/CWgmMEZ9CTfRO', '416650cb6a88', 0),
(3, 'John Doe', 'johndoe2@example.com', '$2y$10$i0dAHkvCByKEqOhNwm8dGOw.eZcsJvFd5Pdv5JpvlWx2jJVhrxI.G', '8a39caba47b0', 1),
(4, 'Chris Brown', 'user1@example.com', '$2y$10$N/y3xZXN2Sf5p9U5PjmnHu/YuFSSD0ayQWhHe8Jml3siXY5Ps5TNq', '5603ddc63598', 0),
(5, 'Matthew Walker', 'user2@example.com', '$2y$10$RyDsp/VydI5y9ILuWKD3culAEMsEHOC8Se6wQ6i.yBWzCpUDBayKa', '781f860cb7d6', 0),
(6, 'Emily Davis', 'user3@example.com', '$2y$10$Y8M7eCAlJvJWhH0ky1hnoOg09NoRkwIDVNxAX4PQvCL1RZYmrxnkW', '788772cff552', 0),
(7, 'Ethan Carter', 'user4@example.com', '$2y$10$ARU2f03Qm/Gj48o8YiUGZ.6sxzXcjn5EWux36jw.tEbwJkKxxwcle', '040970566030', 0),
(8, 'Ashley Clark', 'user5@example.com', '$2y$10$jGZNKbwquld4omRPEeU0UOJNmAAn/6zG4J.j6rb9AQR8XGz756kx6', '77e1f6ae2f4d', 0),
(9, 'Joshua King', 'user6@example.com', '$2y$10$47aNHajdrgXolwvD89hK..nLmLugQCxAsLdSzWYPl6q1c41NjiKze', '9aae5a0e9736', 0),
(10, 'John Doe', 'user7@example.com', '$2y$10$18Onbm1iljObXVt696nzcuu0DR01T6L0E.vWruQHnjTXLtqV7CvT.', 'e6684bb9fee7', 0),
(11, 'Ashley Clark', 'user8@example.com', '$2y$10$0xDeX7Wn8xpNgssf6iIiteDg9nTR2A10PvgKpV83QpbPfAr4sHYjm', '2107f5ff782e', 0),
(12, 'David Lee', 'user9@example.com', '$2y$10$JG9RD8m1XsTFfhl0rT9ePOEfdGG074FuprOkdjNWtZY5Ta4vrOVjO', '179e7ab0f971', 0),
(13, 'Andrew Adams', 'user10@example.com', '$2y$10$gqi/Qm1FiVTavoPBV7c7RuvNoExIcg1pc/HMnhx19RO/BiafPKuvG', '7530ac9addc1', 0),
(14, 'Kana', 'gavin.pennegan@gmail.com', '$2y$10$6dleRHJy6f2sUkyWR9kkzOsAQuWWO69.t5xJPhEQM15h8SsiQv89O', '8962c6c76481', 0),
(21, 'Kana', 'gavin.pennegan4@gmail.com', '$2y$10$yo4s2vQ2aBGzTE1i25ZLRezzRV3sMzRYi.WX3KSSWk4M.aKhL.uH2', 'b3afd69e68be', 0);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `attendees`
--
ALTER TABLE `attendees`
  ADD PRIMARY KEY (`user_id`,`event_id`),
  ADD KEY `event_id` (`event_id`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `api_key` (`api_key`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `attendees`
--
ALTER TABLE `attendees`
  ADD CONSTRAINT `attendees_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `attendees_ibfk_2` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

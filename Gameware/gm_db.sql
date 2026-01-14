-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 14, 2026 at 11:00 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `gm_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(255) NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`) VALUES
(2, 'action-adventure'),
(4, 'consoles'),
(1, 'horror '),
(3, 'pc');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `price` int(11) NOT NULL,
  `stock` int(11) NOT NULL,
  `image` varchar(255) NOT NULL,
  `category_name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `description`, `price`, `stock`, `image`, `category_name`) VALUES
(8, 'The Last Of Us Part 2', 'The Last of Us Part II is a 2020 action-adventure game developed by Naughty Dog and published by Sony Interactive Entertainment.', 39, 489, 'tlou2.webp', 'horror '),
(10, 'Uncharted 4', 'Uncharted 4: A Thief\'s End is a 2016 action-adventure game developed by Naughty Dog and published by Sony Computer Entertainment. It is the fourth main entry in the Uncharted series', 20, 356, 'uncharted 4.avif', 'action-adventure'),
(11, 'pc', 'monster gaming pc', 200, 100, 'pc.webp', 'pc');

-- --------------------------------------------------------

--
-- Table structure for table `single_order`
--

CREATE TABLE `single_order` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `product_quantity` int(11) NOT NULL,
  `total_amount` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `single_order`
--

INSERT INTO `single_order` (`id`, `user_id`, `product_id`, `product_quantity`, `total_amount`) VALUES
(102, 29, 8, 1, 39),
(103, 29, 10, 4, 80),
(104, 28, 8, 4, 156),
(105, 28, 8, 4, 156),
(106, 28, 8, 9, 351),
(107, 28, 8, 1, 39),
(108, 28, 8, 6, 234),
(109, 28, 8, 6, 234),
(110, 28, 8, 4, 156),
(111, 28, 8, 4, 156),
(112, 28, 8, 3, 117),
(113, 29, 10, 5, 100),
(114, 28, 8, 4, 156),
(115, 28, 8, 8, 312),
(116, 28, 10, 11, 220),
(117, 28, 8, 4, 156),
(118, 28, 10, 4, 80),
(119, 28, 10, 1, 20),
(120, 28, 10, 8, 160),
(121, 28, 10, 4, 80),
(122, 28, 8, 6, 234),
(123, 28, 8, 16, 624),
(124, 28, 10, 10, 200),
(125, 35, 8, 5, 195),
(126, 35, 8, 10, 390),
(127, 35, 10, 7, 140),
(128, 42, 8, 1, 39),
(129, 42, 10, 7, 140),
(130, 35, 10, 1, 20),
(131, 58, 10, 20, 400);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(256) NOT NULL,
  `email` varchar(256) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(255) NOT NULL,
  `address` text NOT NULL,
  `role` enum('admin','user') NOT NULL,
  `verification_code` varchar(64) DEFAULT NULL,
  `is_verified` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `phone`, `address`, `role`, `verification_code`, `is_verified`) VALUES
(35, 'alexis', 'alexis123@gmail.com', '$2y$10$sTrZHsk2tfN0fm/2/AhyI.Y2eyKTMZlSbuWl6WnkLUQ1d8FajjcFy', '8902432341', 'gilid street', 'user', NULL, 0),
(39, 'jepoy', 'jepoy@gmail.com', '$2y$10$RsIGVQwoo.QJTpECtkqyhuFS8BU5NiIqYd9yJAmq65dRJMno4ZI/K', '09099042131', 'stereet', 'user', NULL, 0),
(42, 'ryan gozun', 'ryangozun@gmail.com', '$2y$10$4Q.LhruvCPkrv/vpghBGr.B2NXh5SPRjppmrvAdiZv3pXO6hF98m.', '09099042131', 'fweqfwefefwf', 'user', NULL, 0),
(43, 'admin', 'admin@gmail.com', '$2y$10$VzsEgwF7bsigctqVngQJ0OJwflx4DyoTBsTbdLbiAeMx2hzM/pKpm', '523534523', '1241sadsdfasdf', 'admin', NULL, 0),
(58, 'alexis', 'alexisdefeo01331@gmail.com', '$2y$10$IJYpwTDdw7ClFA1TiE.FveHPbYblxac5kKaB7k8nKpYBUuaQofn1a', '09099024215', 'gilid street', 'user', NULL, 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `single_order`
--
ALTER TABLE `single_order`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `single_order`
--
ALTER TABLE `single_order`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=132;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=59;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

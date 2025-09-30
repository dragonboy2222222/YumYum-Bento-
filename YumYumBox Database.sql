-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Sep 30, 2025 at 02:30 PM
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
-- Database: `yumyumbox`
--

-- --------------------------------------------------------

--
-- Table structure for table `chat_messages`
--

CREATE TABLE `chat_messages` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `session_id` varchar(64) NOT NULL,
  `sender` enum('user','bot') NOT NULL,
  `message` text NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `chat_sessions`
--

CREATE TABLE `chat_sessions` (
  `id` varchar(64) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `checkouts`
--

CREATE TABLE `checkouts` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `total_amount` decimal(8,2) NOT NULL,
  `status` enum('pending','paid','failed','cancelled') DEFAULT 'pending',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `checkouts`
--

INSERT INTO `checkouts` (`id`, `user_id`, `total_amount`, `status`, `created_at`) VALUES
(9, 18, 1500.00, 'paid', '2025-09-15 15:49:39'),
(10, 18, 1400.00, 'paid', '2025-09-15 15:53:16'),
(11, 18, 1200.00, 'paid', '2025-09-15 16:13:37'),
(12, 18, 2800.00, 'paid', '2025-09-15 20:02:40'),
(13, 19, 520.00, 'paid', '2025-09-16 01:40:19'),
(14, 19, 1000.00, 'paid', '2025-09-17 02:17:16'),
(15, 19, 500.00, 'paid', '2025-09-17 11:18:41'),
(16, 18, 400.00, 'paid', '2025-09-19 02:31:01'),
(17, 18, 400.00, 'paid', '2025-09-19 23:24:25'),
(18, 19, 400.00, 'paid', '2025-09-20 10:01:05'),
(19, 19, 400.00, 'paid', '2025-09-30 15:26:32'),
(20, 19, 400.00, 'paid', '2025-09-30 15:34:20'),
(21, 19, 980.00, 'paid', '2025-09-30 15:34:36'),
(22, 19, 400.00, 'paid', '2025-09-30 15:36:43');

-- --------------------------------------------------------

--
-- Table structure for table `deliveries`
--

CREATE TABLE `deliveries` (
  `id` int(11) NOT NULL,
  `subscription_id` int(11) NOT NULL,
  `delivery_date` date NOT NULL,
  `status` enum('pending','delivered','skipped') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lunchboxes`
--

CREATE TABLE `lunchboxes` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(8,2) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `discount_type` enum('percent','fixed') DEFAULT NULL,
  `discount_value` decimal(8,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lunchboxes`
--

INSERT INTO `lunchboxes` (`id`, `name`, `description`, `price`, `image`, `discount_type`, `discount_value`) VALUES
(4, 'Classic Bento', 'This package includes 10 menus for a timeless lunch.\r\n\r\nDescription: Timeless tastes that never go out of style. The Classic collection brings you beloved lunch staples, elevated with fresh, high-quality ingredients. These are the comfort foods you grew up with, perfectly portioned and ready to enjoy.', 500.00, '../lunchbox_images/Gemini_Generated_Image_55xjtk55xjtk55xj.png', NULL, 0.00),
(5, 'Healthy Vegetarian Bento', 'This package includes 10 menus for a vibrant, plant-based lunch.\r\n\r\nDescription: Delicious, plant-based meals that are anything but boring! Our Healthy Vegetarian collection is crafted to showcase the incredible variety and flavor of vegetables, legumes, and grains. Each meal is a complete, satisfying, and vibrant dish that proves eating green is a treat.\r\n\r\n', 350.00, '../lunchbox_images/Gemini_Generated_Image_80xmv880xmv880xm.png', 'percent', 20.00),
(8, 'Protein Power Bento', 'This package includes 10 menus for your busy day.\r\n\r\nDescription: Fuel your day with sustained energy! Our Protein Power collection is for those who need a substantial, filling lunch to keep them going. Each meal is centered around high-quality protein to support muscle maintenance, satiety, and a busy schedule.\r\n\r\n', 500.00, '../lunchbox_images/Gemini_Generated_Image_d2j187d2j187d2j1.png', NULL, 0.00),
(9, 'Kids Bento', 'This package includes 10 menus for your little ones.\r\n\r\nDescription: Make lunchtime fun and nutritious for your little ones! Our Kids\' collection features vibrant, appealing meals packed with the nutrients growing bodies need. Each meal is designed to be kid-friendly, with exciting shapes, colors, and flavors to encourage them to finish every bite.\r\n\r\n', 500.00, '../lunchbox_images/Gemini_Generated_Image_eyawxyeyawxyeyaw.png', NULL, 0.00),
(10, 'Hybrid Bento', 'This package includes 10 menus for a creative lunch.\r\n\r\nDescription: Can\'t decide between a sandwich and a salad? Our Hybrid collection is for you! We\'ve combined the best of both worlds, offering creative and unique meal combinations that provide variety and satisfaction in one delicious package.\r\n\r\n', 520.00, '../lunchbox_images/Gemini_Generated_Image_c4kiq8c4kiq8c4ki.png', NULL, 0.00),
(13, 'Western Bento', 'This package includes 10 menus for a hearty, Western-style lunch.\r\n\r\nDescription: Saddle up for a satisfying lunch! Our Western Style Lunchbox Collection brings you a taste of the American West with bold, hearty flavors and classic comfort foods. Each meal is designed to be substantial and flavorful, perfect for a long day on the ranch or a busy day at the office.', 444.00, '../lunchbox_images/Gemini_Generated_Image_f8jiw6f8jiw6f8ji.png', NULL, 0.00);

-- --------------------------------------------------------

--
-- Table structure for table `menus`
--

CREATE TABLE `menus` (
  `id` int(11) NOT NULL,
  `lunchbox_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `menus`
--

INSERT INTO `menus` (`id`, `lunchbox_id`, `name`, `description`, `image`) VALUES
(7, 4, 'Chicken Salad Croissant', 'Creamy chicken salad on a buttery croissant, with a side of grapes', '1758121816_Gemini_Generated_Image_wn0cq2wn0cq2wn0c.png'),
(8, 4, 'Turkey & Provolone Sandwich', 'Sliced turkey, provolone cheese, lettuce, and tomato on whole-grain bread with a side of chips.', '../lunchbox_images/Gemini_Generated_Image_up6vicup6vicup6v.png'),
(9, 4, 'Ham & Swiss on Rye', 'Classic deli-style sandwich with sliced ham and Swiss cheese on rye bread.', '../lunchbox_images/Gemini_Generated_Image_9x2og99x2og99x2o.png'),
(10, 4, 'Classic Caesar Salad', 'Crisp romaine lettuce with croutons, parmesan cheese, and a creamy Caesar dressing.', '../lunchbox_images/Gemini_Generated_Image_3hfh3u3hfh3u3hfh.png'),
(11, 4, 'Roast Beef & Cheddar Sandwich', 'Sliced roast beef with sharp cheddar cheese on a hoagie roll.', '../lunchbox_images/Gemini_Generated_Image_7sa46d7sa46d7sa4.png'),
(12, 4, 'Italian Hoagie', 'Salami, pepperoni, ham, and provolone with lettuce, tomato, and a drizzle of oil and vinegar.', '../lunchbox_images/Gemini_Generated_Image_9mjkch9mjkch9mjk.png'),
(13, 4, 'Tomato Soup & Grilled Cheese', 'A small container of creamy tomato soup with a perfectly toasted grilled cheese sandwich.', '../lunchbox_images/Gemini_Generated_Image_sfz7n4sfz7n4sfz7.png'),
(14, 4, 'Meatball Sub', 'Small meatballs in marinara sauce on a roll, served with a side of garlic bread.', '../lunchbox_images/Gemini_Generated_Image_6097a36097a36097.png'),
(15, 4, 'BLT Club Sandwich', 'A triple-decker sandwich with crispy bacon, lettuce, and tomato.', '../lunchbox_images/Gemini_Generated_Image_vhdejyvhdejyvhde.png'),
(16, 4, 'Japanses Bento', 'With Salmon,Beans and Egg Omlet.', '../lunchbox_images/Gemini_Generated_Image_55xjtk55xjtk55xj.png');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `checkout_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `method` enum('credit_card','paypal','bank_transfer','cash') NOT NULL,
  `status` enum('pending','completed','failed','refunded') DEFAULT 'pending',
  `transaction_id` varchar(50) DEFAULT NULL,
  `paid_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `user_id`, `checkout_id`, `amount`, `method`, `status`, `transaction_id`, `paid_at`, `created_at`) VALUES
(8, 18, 9, 1500.00, 'credit_card', '', 'TXN68c7da2bb9004', '2025-09-15 15:49:39', '2025-09-15 15:49:39'),
(9, 18, 10, 1400.00, 'credit_card', '', 'TXN68c7db0485fad', '2025-09-15 15:53:16', '2025-09-15 15:53:16'),
(10, 18, 11, 1200.00, 'credit_card', '', 'TXN68c7dfc9399c5', '2025-09-15 16:13:37', '2025-09-15 16:13:37'),
(11, 18, 12, 2800.00, 'credit_card', '', 'TXN68c81578c7f84', '2025-09-15 20:02:40', '2025-09-15 20:02:40'),
(12, 19, 13, 520.00, 'credit_card', '', 'TXN68c8649b1f235', '2025-09-16 01:40:19', '2025-09-16 01:40:19'),
(13, 19, 14, 1000.00, 'credit_card', '', 'TXN68c9bec438f81', '2025-09-17 02:17:16', '2025-09-17 02:17:16'),
(14, 19, 15, 500.00, 'paypal', '', 'TXN68ca3da9c8af9', '2025-09-17 11:18:41', '2025-09-17 11:18:41'),
(15, 18, 16, 400.00, 'credit_card', '', 'TXN68cc64fdb7bfe', '2025-09-19 02:31:01', '2025-09-19 02:31:01'),
(16, 18, 17, 400.00, 'bank_transfer', '', 'TXN68cd8ac125a0c', '2025-09-19 23:24:25', '2025-09-19 23:24:25'),
(17, 19, 18, 400.00, 'credit_card', '', 'TXN68ce1ff9dc3ac', '2025-09-20 10:01:05', '2025-09-20 10:01:05'),
(18, 19, 19, 400.00, 'credit_card', '', 'TXN68db9b40cb59f', '2025-09-30 15:26:32', '2025-09-30 15:26:32'),
(19, 19, 20, 400.00, 'credit_card', '', 'TXN68db9d14b6e01', '2025-09-30 15:34:20', '2025-09-30 15:34:20'),
(20, 19, 21, 980.00, 'credit_card', '', 'TXN68db9d24db0b5', '2025-09-30 15:34:36', '2025-09-30 15:34:36'),
(21, 19, 22, 400.00, 'credit_card', '', 'TXN68db9da3da673', '2025-09-30 15:36:43', '2025-09-30 15:36:43');

-- --------------------------------------------------------

--
-- Table structure for table `plans`
--

CREATE TABLE `plans` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `duration_days` int(11) NOT NULL,
  `discount_type` enum('percent','fixed') DEFAULT NULL,
  `discount_value` decimal(8,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `plans`
--

INSERT INTO `plans` (`id`, `name`, `duration_days`, `discount_type`, `discount_value`) VALUES
(1, '30 Days plan', 30, 'fixed', 100.00),
(2, '60 Days plan', 60, 'fixed', 20.00),
(4, '90 Days plan', 90, 'fixed', 100.00);

-- --------------------------------------------------------

--
-- Table structure for table `profiles`
--

CREATE TABLE `profiles` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `profiles`
--

INSERT INTO `profiles` (`id`, `user_id`, `full_name`, `phone`, `address`, `profile_image`, `created_at`, `updated_at`) VALUES
(12, 19, 'Haiiro Arima', '09447554422', 'Yangon', 'uploads/1757530266_viber_image_2024-12-23_10-39-37-107.jpg', '2025-09-10 18:50:09', '2025-09-10 18:51:06'),
(13, 18, 'Thein Paing Htun', '09447554422', 'Yangon', 'uploads/1757874900_Screenshot 2024-06-09 000833.png', '2025-09-14 18:35:00', '2025-09-14 18:35:00'),
(14, 20, 'af', '09447554422', 'Yangon', 'uploads/1718523102_fam.webp', '2024-06-16 07:31:42', '2024-06-16 07:31:42'),
(15, 21, 'z', '09447554422', 'Yangon', 'uploads/1718527039_IMG_6097.JPG', '2024-06-16 08:37:19', '2024-06-16 08:37:19'),
(17, 25, 'afd', '13223413', 'daf', 'uploads/1758909654_Gemini_Generated_Image_9vsjr09vsjr09vsj.png', '2025-09-26 18:00:38', '2025-09-26 18:00:54');

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `rating` int(1) NOT NULL,
  `review_text` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`id`, `user_id`, `rating`, `review_text`, `created_at`) VALUES
(1, 18, 5, 'We will surely know what is the great', '2025-09-15 18:16:42'),
(2, 18, 5, 'My fav menu is classic bento\r\n', '2025-09-15 18:17:13'),
(3, 19, 3, 'I love the menus too mostly the healthy one', '2025-09-15 18:18:19');

-- --------------------------------------------------------

--
-- Table structure for table `subscriptions`
--

CREATE TABLE `subscriptions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `lunchbox_id` int(11) NOT NULL,
  `plan_id` int(11) NOT NULL,
  `address` varchar(255) DEFAULT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `status` enum('active','paused','cancelled') DEFAULT 'active',
  `checkout_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subscriptions`
--

INSERT INTO `subscriptions` (`id`, `user_id`, `lunchbox_id`, `plan_id`, `address`, `start_date`, `end_date`, `status`, `checkout_id`) VALUES
(31, 18, 4, 4, NULL, '2025-09-15', '2025-10-15', 'active', 9),
(32, 18, 4, 4, 'Sd', '2025-09-15', '2025-10-15', 'active', 10),
(33, 18, 4, 4, 'Sd', '2025-09-15', '2025-10-15', '', 11),
(34, 18, 4, 4, 'daf', '2025-09-15', '2025-10-15', 'active', 12),
(35, 18, 4, 4, 'daf', '2025-09-15', '2025-10-15', 'active', 12),
(36, 19, 10, 1, 'Germany', '2025-09-16', '2025-10-16', 'active', 13),
(37, 19, 9, 2, 'fds', '2025-09-17', '2025-10-17', 'active', 14),
(38, 19, 9, 1, 'Sule Square the office tower ', '2025-09-17', '2025-10-17', 'active', 15),
(39, 18, 4, 1, 'Sule Sqaure,Office Tower,Latha Township', '2025-09-19', '2025-10-19', 'active', 16),
(40, 18, 4, 1, 'Sule Squaare, Latha township', '2025-09-19', '2025-10-19', 'active', 17),
(41, 19, 4, 1, 'Sule', '2025-09-20', '2025-10-20', 'active', 18),
(42, 19, 8, 1, 'asfd', '2025-09-30', '2025-10-30', 'active', 19),
(43, 19, 9, 1, 'adsf', '2025-09-30', '2025-10-30', 'active', 20),
(44, 19, 9, 2, 'afsd', '2025-09-30', '2025-11-29', 'active', 21),
(45, 19, 8, 1, 'fads', '2025-09-30', '2025-10-30', 'active', 22);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('customer','admin') DEFAULT 'customer',
  `created_at` datetime DEFAULT current_timestamp(),
  `spin_used` tinyint(1) NOT NULL DEFAULT 0,
  `discount_type` varchar(10) DEFAULT NULL,
  `discount_value` decimal(10,2) DEFAULT 0.00,
  `discount_redeemed` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `role`, `created_at`, `spin_used`, `discount_type`, `discount_value`, `discount_redeemed`) VALUES
(18, 'thein', 'theinpainghtun@gmail.com', '$2y$10$vb6dmSi0eGZaXxN1JN1a4ezf6D9vd5vPe96Rixxf3zJi05W/tgMc2', 'admin', '2025-09-10 13:43:56', 0, NULL, 0.00, 0),
(19, 'hai', 'haiiroarima@gmail.com', '$2y$10$vb6dmSi0eGZaXxN1JN1a4ezf6D9vd5vPe96Rixxf3zJi05W/tgMc2', 'customer', '2025-09-11 01:19:52', 0, NULL, 0.00, 0),
(20, 'admin', 'admin@gmail.com', '$2y$10$Ak.lk/Wp58GQk6nHCLxAhuHbMADmrDneF1PsD8FyWxlYmamTcVNBe', 'customer', '2024-06-16 14:01:29', 0, NULL, 0.00, 0),
(21, 'zoran', 'zoran@gmail.com', '$2y$10$jfMa1Ss2UKIaa2nX5v1UXu6CDoRDjF0l2SNj3eOnnLoVXcgk8wnH.', 'customer', '2024-06-16 15:07:06', 0, NULL, 0.00, 0),
(23, 'thein12', 'admin33@gmail.com', '$2y$10$m6HcgSubn6973VlW2op5KOqnz/aVG.UN4Y2leYfB4RL8MxML/W43e', 'customer', '2025-09-26 23:58:28', 0, NULL, 0.00, 0),
(24, 'Htinds', 'Htin2@gmail.com', '$2y$10$ecSsNKTPj2grhsNzBKtqcOZ8Gwp8yPaNZSCOwETaMU6HR3xcWIATq', 'customer', '2025-09-27 00:03:26', 0, NULL, 0.00, 0),
(25, 'godadsf', 'god23089@gmail.com', '$2y$10$rYYQj0NMk3x65t7KJ4rxbe6YyI6deKmuFlCciKXhGkg3dX9HiNWcW', 'customer', '2025-09-27 00:24:49', 0, NULL, 0.00, 0);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `chat_messages`
--
ALTER TABLE `chat_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `session_id` (`session_id`);

--
-- Indexes for table `chat_sessions`
--
ALTER TABLE `chat_sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `checkouts`
--
ALTER TABLE `checkouts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `deliveries`
--
ALTER TABLE `deliveries`
  ADD PRIMARY KEY (`id`),
  ADD KEY `subscription_id` (`subscription_id`);

--
-- Indexes for table `lunchboxes`
--
ALTER TABLE `lunchboxes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `menus`
--
ALTER TABLE `menus`
  ADD PRIMARY KEY (`id`),
  ADD KEY `lunchbox_id` (`lunchbox_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `checkout_id` (`checkout_id`);

--
-- Indexes for table `plans`
--
ALTER TABLE `plans`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `profiles`
--
ALTER TABLE `profiles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `subscriptions`
--
ALTER TABLE `subscriptions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `lunchbox_id` (`lunchbox_id`),
  ADD KEY `plan_id` (`plan_id`),
  ADD KEY `checkout_id` (`checkout_id`);

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
-- AUTO_INCREMENT for table `chat_messages`
--
ALTER TABLE `chat_messages`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `checkouts`
--
ALTER TABLE `checkouts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `deliveries`
--
ALTER TABLE `deliveries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `lunchboxes`
--
ALTER TABLE `lunchboxes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `menus`
--
ALTER TABLE `menus`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `plans`
--
ALTER TABLE `plans`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `profiles`
--
ALTER TABLE `profiles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `subscriptions`
--
ALTER TABLE `subscriptions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `chat_messages`
--
ALTER TABLE `chat_messages`
  ADD CONSTRAINT `fk_chat_session` FOREIGN KEY (`session_id`) REFERENCES `chat_sessions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `chat_sessions`
--
ALTER TABLE `chat_sessions`
  ADD CONSTRAINT `fk_chat_sessions_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `checkouts`
--
ALTER TABLE `checkouts`
  ADD CONSTRAINT `checkouts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `deliveries`
--
ALTER TABLE `deliveries`
  ADD CONSTRAINT `deliveries_ibfk_1` FOREIGN KEY (`subscription_id`) REFERENCES `subscriptions` (`id`);

--
-- Constraints for table `menus`
--
ALTER TABLE `menus`
  ADD CONSTRAINT `menus_ibfk_1` FOREIGN KEY (`lunchbox_id`) REFERENCES `lunchboxes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `payments_ibfk_2` FOREIGN KEY (`checkout_id`) REFERENCES `checkouts` (`id`);

--
-- Constraints for table `profiles`
--
ALTER TABLE `profiles`
  ADD CONSTRAINT `fk_profiles_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `subscriptions`
--
ALTER TABLE `subscriptions`
  ADD CONSTRAINT `subscriptions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `subscriptions_ibfk_2` FOREIGN KEY (`lunchbox_id`) REFERENCES `lunchboxes` (`id`),
  ADD CONSTRAINT `subscriptions_ibfk_3` FOREIGN KEY (`plan_id`) REFERENCES `plans` (`id`),
  ADD CONSTRAINT `subscriptions_ibfk_4` FOREIGN KEY (`checkout_id`) REFERENCES `checkouts` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

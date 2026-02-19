-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Feb 16, 2026 at 05:56 AM
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
-- Database: `pizza_hub`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` char(36) NOT NULL,
  `name` varchar(100) NOT NULL,
  `phone` varchar(100) NOT NULL,
  `address` varchar(255) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('super_admin','admin','editor','reader') NOT NULL DEFAULT 'admin',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `name`, `phone`, `address`, `email`, `password`, `role`, `is_active`, `created_at`) VALUES
('1228c882-0bf2-4aa5-9a72-fcb7821f2376', 'Super Admin', '9999999999', 'System Default Address', 'admin@example.com', '$2y$10$Qc9RIQztMJ9RlQvYq5XvVeBVPZoWYT383MV28Umw7dA/rfyGVvDdC', 'super_admin', 1, '2026-01-15 11:14:03'),
('856f3322-10bb-428e-b858-35f447057525', 'AGYA', '09569851705', 'N/A', 'amitit33@gmail.com', '$2y$10$DOon4j30piJTCJ10ac1aWenJQeiLu3crbYB6/7U/ww7smw/A7CS4W', 'editor', 1, '2026-01-15 12:28:32');

-- --------------------------------------------------------

--
-- Table structure for table `api_keys`
--

CREATE TABLE `api_keys` (
  `id` char(36) NOT NULL,
  `api_key` varchar(255) NOT NULL,
  `owner` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` char(36) NOT NULL,
  `name` varchar(150) NOT NULL,
  `image` varchar(255) NOT NULL,
  `status` tinyint(1) DEFAULT 1 COMMENT '1 = active, 0 = inactive',
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `image`, `status`, `sort_order`, `created_at`, `updated_at`) VALUES
('87b0a826-a398-4920-8bc1-c69a7cbdc65c', 'Pizza', 'categories/category_69899b9ab2d137.31019780.png', 1, 0, '2026-02-09 08:32:26', '2026-02-09 08:32:26');

-- --------------------------------------------------------

--
-- Table structure for table `contact_us`
--

CREATE TABLE `contact_us` (
  `id` char(36) NOT NULL,
  `name` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `subject` varchar(150) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contact_us`
--

INSERT INTO `contact_us` (`id`, `name`, `email`, `phone`, `subject`, `message`, `created_at`) VALUES
('560a09bf-6e2a-4bdc-8141-fbb0121c762b', 'amit', 'kbinstitute36@gmail.com', '9026296863', 'just testing purpose it', 'it ok', '2026-01-27 10:21:40');

-- --------------------------------------------------------

--
-- Table structure for table `email_verifications`
--

CREATE TABLE `email_verifications` (
  `id` char(36) NOT NULL,
  `user_id` char(36) NOT NULL,
  `uid` char(36) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `verified` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `extra_toppings`
--

CREATE TABLE `extra_toppings` (
  `id` char(36) NOT NULL,
  `name` varchar(150) NOT NULL,
  `image` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `status` tinyint(1) DEFAULT 1 COMMENT '1 = active, 0 = inactive',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `extra_toppings`
--

INSERT INTO `extra_toppings` (`id`, `name`, `image`, `price`, `status`, `created_at`, `updated_at`) VALUES
('554921dd-47fb-42cc-880d-0bcb634c3bf6', 'Corn', 'toppings/topping_6980559c2a4ea3.90233400.png', 20.00, 1, '2026-02-02 07:43:24', '2026-02-02 07:43:24'),
('5d440130-23b5-4d3a-9cf6-345de9b8fb7e', 'Extra Cheese', 'toppings/topping_698054b373e842.41319133.png', 40.00, 1, '2026-02-02 07:39:31', '2026-02-02 07:39:31'),
('a80ceef7-bb15-4b59-bcf9-957428782a2d', 'Olives', 'toppings/topping_69805509d6d0d3.86169949.png', 30.00, 1, '2026-02-02 07:40:57', '2026-02-02 07:40:57'),
('a87f16f9-f081-401d-b6e4-3fb45b50b652', 'Jalapeno', 'toppings/topping_69808fbab266c8.05935244.png', 30.00, 1, '2026-02-02 07:44:18', '2026-02-02 11:51:22'),
('e44b7ae8-3ccd-4fc0-bfd5-1426aa17b5a2', 'Paneer', 'toppings/topping_69808faf4d83b0.40092149.png', 45.00, 1, '2026-02-02 07:45:02', '2026-02-02 11:51:11'),
('e48d09dc-7811-40a2-9003-04572e2eb440', 'Mushroom', 'toppings/topping_6980553f764179.68908380.png', 35.00, 1, '2026-02-02 07:41:51', '2026-02-02 07:41:51'),
('f962b027-239a-4512-be20-267449fa0a60', 'Capsicum', 'toppings/topping_6980556a844434.75968647.png', 25.00, 1, '2026-02-02 07:42:34', '2026-02-02 07:42:34');

-- --------------------------------------------------------

--
-- Table structure for table `home_banners`
--

CREATE TABLE `home_banners` (
  `id` char(36) NOT NULL,
  `title` varchar(150) NOT NULL,
  `subtitle` varchar(255) DEFAULT NULL,
  `image` varchar(255) NOT NULL,
  `discount_text` varchar(100) DEFAULT NULL,
  `valid_till` date DEFAULT NULL,
  `status` tinyint(1) DEFAULT 1,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `home_banners`
--

INSERT INTO `home_banners` (`id`, `title`, `subtitle`, `image`, `discount_text`, `valid_till`, `status`, `sort_order`, `created_at`) VALUES
('eb5d97d3-e87f-4eec-8648-0a7224b61fd8', 'Pizza Margherita', 'DISC', 'home-banners/banner_69899db31df177.07914522.png', 'UP TO 70% OFF', '2026-02-15', 1, 0, '2026-02-09 08:41:23');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` char(36) NOT NULL,
  `username` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `address` text NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `payment_method` enum('cod','upi','card') DEFAULT 'cod',
  `payment_status` enum('unpaid','paid') DEFAULT 'unpaid',
  `status` enum('pending','accepted','preparing','ready','out_for_delivery','delivered','cancelled') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `username`, `email`, `phone`, `address`, `total_amount`, `payment_method`, `payment_status`, `status`, `created_at`) VALUES
('495ee7d1-18ca-4600-b4de-a4b6c69c8489', 'Amit ', 'dmtoakbar@gmail.com', '9026296803', 'lucknow ', 290.00, 'cod', 'unpaid', 'pending', '2026-02-13 05:31:55'),
('aa0ed45a-1291-4bfc-8250-adddfbba48c9', 'amit kumar', 'dmtoakbar@gmail.com', '9026296803', 'Lucknow junction', 80.00, 'cod', 'unpaid', 'pending', '2026-02-11 07:44:05'),
('e75eead4-309e-49b5-b8c5-bc75eb918eb5', 'Amit ', 'dmtoakbar@gmail.com', '9026296803', 'lucknow ', 213.00, 'cod', 'unpaid', 'pending', '2026-02-13 05:22:25');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` char(36) NOT NULL,
  `product_id` char(36) NOT NULL,
  `product_image` varchar(255) NOT NULL,
  `product_name` varchar(150) NOT NULL,
  `size` varchar(5) NOT NULL,
  `base_price` decimal(10,2) NOT NULL,
  `discount_percentage` decimal(5,2) NOT NULL,
  `final_price` decimal(10,2) NOT NULL,
  `quantity` int(11) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `product_image`, `product_name`, `size`, `base_price`, `discount_percentage`, `final_price`, `quantity`) VALUES
(1, 'aa0ed45a-1291-4bfc-8250-adddfbba48c9', '9edc2abd-7990-4753-b057-5c836d71e351', 'http://localhost:8080/pizza-hub/media/products/product_698c16aac6aa60.65791555.png', 'Margherita Pizza', 'M', 80.00, 0.00, 80.00, 1),
(2, 'e75eead4-309e-49b5-b8c5-bc75eb918eb5', 'e39dc3b7-3732-427b-87d2-f3a9b5db09d1', 'http://56.228.82.72/pizza-hub/media/products/product_698db8d8b483b1.54142794.png', 'Margherita Pizza', 'M', 20.00, 0.00, 20.00, 1),
(3, 'e75eead4-309e-49b5-b8c5-bc75eb918eb5', 'e39dc3b7-3732-427b-87d2-f3a9b5db09d1', 'http://56.228.82.72/pizza-hub/media/products/product_698db8d8b483b1.54142794.png', 'Margherita Pizza', 'M', 20.00, 0.00, 20.00, 1),
(4, 'e75eead4-309e-49b5-b8c5-bc75eb918eb5', '9edc2abd-7990-4753-b057-5c836d71e351', 'http://localhost:8080/pizza-hub/media/products/product_698c16aac6aa60.65791555.png', 'Margherita Pizza', 'M', 80.00, 0.00, 80.00, 1),
(5, '495ee7d1-18ca-4600-b4de-a4b6c69c8489', '9edc2abd-7990-4753-b057-5c836d71e351', 'http://localhost:8080/pizza-hub/media/products/product_698c16aac6aa60.65791555.png', 'Margherita Pizza', 'M', 80.00, 0.00, 80.00, 1),
(6, '495ee7d1-18ca-4600-b4de-a4b6c69c8489', '9edc2abd-7990-4753-b057-5c836d71e351', 'http://localhost:8080/pizza-hub/media/products/product_698c16aac6aa60.65791555.png', 'Margherita Pizza', 'S', 80.00, 0.00, 80.00, 1);

-- --------------------------------------------------------

--
-- Table structure for table `order_item_extras`
--

CREATE TABLE `order_item_extras` (
  `id` int(11) NOT NULL,
  `order_item_id` int(11) NOT NULL,
  `extra_name` varchar(100) NOT NULL,
  `extra_price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_item_extras`
--

INSERT INTO `order_item_extras` (`id`, `order_item_id`, `extra_name`, `extra_price`) VALUES
(1, 3, 'Capsicum', 1.00),
(2, 3, 'Olives', 1.00),
(3, 3, 'Onion', 1.00),
(4, 4, 'Corn', 20.00),
(5, 4, 'Paneer', 45.00),
(6, 4, 'Capsicum', 25.00),
(7, 5, 'Capsicum', 25.00),
(8, 5, 'Jalapeno', 30.00),
(9, 6, 'Paneer', 45.00),
(10, 6, 'Jalapeno', 30.00);

-- --------------------------------------------------------

--
-- Table structure for table `otp_verifications`
--

CREATE TABLE `otp_verifications` (
  `id` char(36) NOT NULL,
  `user_id` char(36) NOT NULL,
  `otp` varchar(10) NOT NULL,
  `purpose` enum('email_verification','password_reset','login','two_factor','phone_verification') NOT NULL,
  `expires_at` datetime NOT NULL,
  `verified` tinyint(1) NOT NULL DEFAULT 0,
  `status` enum('pending','verified','expired') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `otp_verifications`
--

INSERT INTO `otp_verifications` (`id`, `user_id`, `otp`, `purpose`, `expires_at`, `verified`, `status`, `created_at`) VALUES
('0d1ad046-edec-47d6-88e8-e2f98111761c', '27bed7f3-805b-427a-85b6-a080281323da', '073972', 'password_reset', '2026-01-20 10:29:36', 1, 'verified', '2026-01-20 04:49:36'),
('275bcc10-979f-490f-bc70-86e64b53a5e6', '27bed7f3-805b-427a-85b6-a080281323da', '377621', 'password_reset', '2026-01-20 10:45:55', 1, 'verified', '2026-01-20 05:05:55'),
('3ac79710-de4b-4854-9279-c92cea049831', '27bed7f3-805b-427a-85b6-a080281323da', '834246', 'password_reset', '2026-01-14 06:05:34', 1, 'verified', '2026-01-14 04:55:34'),
('683d9a36-4353-45c6-ba43-fc546913d7f9', '1ed8f7b9-ab15-4677-bf46-4a31e19385fb', '328714', 'password_reset', '2026-02-16 10:02:16', 0, 'pending', '2026-02-16 04:22:16'),
('6dc6c9d9-8ee2-4046-a697-6dbd7fe003ac', '1ed8f7b9-ab15-4677-bf46-4a31e19385fb', '118948', 'password_reset', '2026-01-27 16:06:38', 0, 'expired', '2026-01-27 10:26:38'),
('b44e3f29-77f8-4f15-a0be-64f899c34163', '27bed7f3-805b-427a-85b6-a080281323da', '401020', 'password_reset', '2026-01-14 10:40:31', 1, 'verified', '2026-01-14 05:00:31'),
('b8d305ee-fdb4-4748-9576-d21f9a64c9bf', '27bed7f3-805b-427a-85b6-a080281323da', '912874', 'password_reset', '2026-01-14 11:01:46', 1, 'verified', '2026-01-14 05:21:46');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` char(36) NOT NULL,
  `category_id` char(36) NOT NULL,
  `name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) NOT NULL,
  `sizes` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`sizes`)),
  `discount_percentage` decimal(5,2) DEFAULT 0.00,
  `is_popular` tinyint(1) DEFAULT 0,
  `is_featured` tinyint(1) DEFAULT 0,
  `status` tinyint(1) DEFAULT 1 COMMENT '1 = active, 0 = inactive',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `category_id`, `name`, `description`, `image`, `sizes`, `discount_percentage`, `is_popular`, `is_featured`, `status`, `created_at`, `updated_at`) VALUES
('0ff1e817-88fc-4baf-8903-da7feb929ff0', '87b0a826-a398-4920-8bc1-c69a7cbdc65c', 'Farmhouse Pizza', 'Loaded with capsicum, onion, tomato, and mushrooms', 'products/product_698c16e4bc3705.49216219.png', '{\"S\":20,\"M\":30,\"L\":35}', 0.00, 1, 0, 1, '2026-02-11 05:43:00', '2026-02-11 05:43:00'),
('9edc2abd-7990-4753-b057-5c836d71e351', '87b0a826-a398-4920-8bc1-c69a7cbdc65c', 'Margherita Pizza', 'Classic cheese pizza with fresh mozzarella and basil', 'products/product_698c16aac6aa60.65791555.png', '{\"S\":60,\"M\":80,\"L\":100}', 0.00, 1, 0, 1, '2026-02-11 05:42:02', '2026-02-11 05:42:02');

-- --------------------------------------------------------

--
-- Table structure for table `report`
--

CREATE TABLE `report` (
  `id` char(36) NOT NULL,
  `name` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `address` text NOT NULL,
  `order_id` varchar(100) NOT NULL,
  `issue` varchar(150) NOT NULL,
  `issue_message` text NOT NULL,
  `status` enum('pending','in_progress','resolved') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `report`
--

INSERT INTO `report` (`id`, `name`, `email`, `phone`, `address`, `order_id`, `issue`, `issue_message`, `status`, `created_at`, `updated_at`) VALUES
('e523a646-de4a-4893-aaf3-9fad3fe0aaf9', 'amit', 'kbinstitute36@gmail.com', '9026296803', 'lucknow', '25388478', 'Not subject', 'not good as mentioned', 'in_progress', '2026-01-27 10:20:33', '2026-01-27 10:20:58');

-- --------------------------------------------------------

--
-- Table structure for table `static_pages`
--

CREATE TABLE `static_pages` (
  `id` char(36) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `title` varchar(150) NOT NULL,
  `content` longtext NOT NULL,
  `status` tinyint(1) DEFAULT 1 COMMENT '1 = active, 0 = inactive',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `static_pages`
--

INSERT INTO `static_pages` (`id`, `slug`, `title`, `content`, `status`, `created_at`, `updated_at`) VALUES
('09724d5f-e18e-45df-a376-2364a9e0daba', 'faq', 'Frequently Asked Questions', '<h1>Frequently Asked Questions</h1>\n\n<h3>1. How can I place an order?</h3>\n<p>\nSimply browse products, add to cart, and complete checkout.\n</p>\n\n<h3>2. What payment methods are accepted?</h3>\n<p>\nWe accept credit/debit cards, UPI, and online wallets.\n</p>\n\n<h3>3. How can I contact support?</h3>\n<p>\nEmail us at support@example.com.\n</p>', 1, '2026-02-04 06:53:31', '2026-02-04 06:53:31'),
('16f0042e-8682-432f-ba6a-ce572dd15189', 'shipping-policy', 'Shipping Policy', '<p class=\"updated\">Last Updated: January 2025</p>\n\n<h1>Shipping Policy</h1>\n\n<p>\nWe aim to deliver your orders quickly and safely.\n</p>\n\n<h3>1. Processing Time</h3>\n<p>\nOrders are processed within 1–3 business days.\n</p>\n\n<h3>2. Shipping Time</h3>\n<ul>\n<li>Domestic: 3–7 business days</li>\n<li>International: 7–14 business days</li>\n</ul>\n\n<h3>3. Shipping Charges</h3>\n<p>\nShipping charges are calculated at checkout.\n</p>', 1, '2026-02-04 06:53:31', '2026-02-04 06:53:31'),
('5ee55ee1-74fa-4972-bdc0-98d6266d8542', 'refund-policy', 'Refund Policy', '<p class=\"updated\">Last Updated: January 2025</p>\n\n<h1>Refund Policy</h1>\n\n<p>\nAt Your Company Name, customer satisfaction is our priority. This Refund Policy\nexplains when and how refunds are issued.\n</p>\n\n<h3>1. Eligibility for Refunds</h3>\n<ul>\n<li>Damaged, defective, or incorrect items.</li>\n<li>Service not delivered as described.</li>\n<li>Refund request raised within X days of purchase.</li>\n</ul>\n\n<h3>2. Non-Refundable Items</h3>\n<ul>\n<li>Downloaded digital products.</li>\n<li>Customized or personalized items.</li>\n<li>Promotional or clearance items.</li>\n</ul>\n\n<h3>3. Refund Processing</h3>\n<p>\nApproved refunds are processed within 5–7 business days to the original payment method.\n</p>\n\n<h3>4. Contact</h3>\n<p>Email: support@example.com</p>', 1, '2026-02-04 06:53:31', '2026-02-04 06:53:31'),
('8ef069dc-93b6-4270-8616-5bd2ecf7deb0', 'terms-conditions', 'Terms & Conditions', '<p class=\"updated\">Last Updated: January 2025</p>\n\n<h1>Terms & Conditions</h1>\n\n<p>\nBy accessing or using our website, you agree to comply with these terms.\n</p>\n\n<h3>1. Use of Website</h3>\n<p>You agree not to misuse our services or content.</p>\n\n<h3>2. Intellectual Property</h3>\n<p>\nAll content is owned by Your Company Name and protected by law.\n</p>\n\n<h3>3. Limitation of Liability</h3>\n<p>\nWe are not liable for indirect or incidental damages.\n</p>', 1, '2026-02-04 06:53:31', '2026-02-04 06:53:31'),
('a91adb37-3051-4607-aab3-2e923f069e05', 'cancellation-policy', 'Cancellation Policy', '<p class=\"updated\">Last Updated: January 2025</p>\n\n<h1>Cancellation Policy</h1>\n\n<p>\nOrders can be canceled before they are shipped.\n</p>\n\n<h3>1. How to Cancel</h3>\n<ul>\n<li>Contact support with your order ID</li>\n<li>Cancellation must be requested within X hours</li>\n</ul>\n\n<h3>2. Non-Cancellable Orders</h3>\n<p>\nOrders already shipped or delivered cannot be canceled.\n</p>', 1, '2026-02-04 06:53:31', '2026-02-04 06:53:31'),
('b865c7e4-274d-43e8-903b-801f0ee9fa21', 'about-us', 'About Us', '<h1>About Us</h1>\n\n<p>\nYour Company Name was founded with the mission to deliver high-quality products\nand excellent customer service.\n</p>\n\n<p>\nWe believe in transparency, trust, and long-term relationships with our customers.\n</p>', 1, '2026-02-04 06:53:31', '2026-02-04 06:53:31'),
('f61c152c-9706-400a-8bbd-d1723d9dddad', 'privacy-policy', 'Privacy Policy', '<p class=\"updated\">Last Updated: January 2025</p>\n\n<h1>Privacy Policy</h1>\n\n<p>\nYour privacy is important to us. This policy explains how we collect, use,\nand protect your personal information.\n</p>\n\n<h3>1. Information We Collect</h3>\n<ul>\n<li>Name, email, phone number</li>\n<li>Billing and shipping details</li>\n<li>Website usage data</li>\n</ul>\n\n<h3>2. How We Use Information</h3>\n<ul>\n<li>Order processing</li>\n<li>Customer support</li>\n<li>Security and legal compliance</li>\n</ul>\n\n<h3>3. Data Protection</h3>\n<p>\nWe use industry-standard security measures to protect your data.\n</p>', 1, '2026-02-04 06:53:31', '2026-02-04 06:53:31');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` char(36) NOT NULL,
  `name` varchar(100) NOT NULL,
  `phone` varchar(100) NOT NULL,
  `address` varchar(255) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email_verified` varchar(10) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `phone`, `address`, `email`, `password`, `email_verified`, `created_at`) VALUES
('1ed8f7b9-ab15-4677-bf46-4a31e19385fb', 'amit', '9026296803', 'lucknow', 'kbinstitute36@gmail.com', '$2y$10$Sfl.t3Pp8IY8kergl/ApJuL9NElRBGQQWWrLZMvwfJy11IYu40RMq', '0', '2026-01-27 10:15:57'),
('27bed7f3-805b-427a-85b6-a080281323da', 'amit kumar', '9026296803', 'Lucknow junction', 'dmtoakbar@gmail.com', '$2y$10$mNfIDHfXmfKWZPqXEg9l/OSvHIm30pbwsVuddk4n/jLdigPgv0ojG', '0', '2026-01-14 04:07:11');

-- --------------------------------------------------------

--
-- Table structure for table `user_sessions`
--

CREATE TABLE `user_sessions` (
  `id` char(36) NOT NULL,
  `user_id` char(36) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_sessions`
--

INSERT INTO `user_sessions` (`id`, `user_id`, `token`, `created_at`, `expires_at`) VALUES
('1dff59d4-1c51-4532-91b5-057ed2ca02f8', '27bed7f3-805b-427a-85b6-a080281323da', '9d49269d74ffba0f1f6f27cd30f56301e632ef0b9f9a50eb65d508274aac0031', '2026-01-14 09:22:18', '2026-02-13 05:51:54'),
('fdcb225a-7a49-43a1-99a4-f49d00d924d1', '1ed8f7b9-ab15-4677-bf46-4a31e19385fb', '9a2753dc52cf02fee6eeeb962f47ca8573e94f9af7a88f419fef54aa17bf5629', '2026-01-27 10:16:32', '2026-01-27 10:46:32');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `api_keys`
--
ALTER TABLE `api_keys`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `api_key` (`api_key`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `contact_us`
--
ALTER TABLE `contact_us`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `email_verifications`
--
ALTER TABLE `email_verifications`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_uid_token` (`uid`,`token`),
  ADD KEY `fk_email_user` (`user_id`);

--
-- Indexes for table `extra_toppings`
--
ALTER TABLE `extra_toppings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `home_banners`
--
ALTER TABLE `home_banners`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `order_item_extras`
--
ALTER TABLE `order_item_extras`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_item_id` (`order_item_id`);

--
-- Indexes for table `otp_verifications`
--
ALTER TABLE `otp_verifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_purpose` (`user_id`,`purpose`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_product_category` (`category_id`);

--
-- Indexes for table `report`
--
ALTER TABLE `report`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `static_pages`
--
ALTER TABLE `static_pages`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`),
  ADD KEY `fk_user` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `order_item_extras`
--
ALTER TABLE `order_item_extras`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `email_verifications`
--
ALTER TABLE `email_verifications`
  ADD CONSTRAINT `fk_email_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `order_item_extras`
--
ALTER TABLE `order_item_extras`
  ADD CONSTRAINT `order_item_extras_ibfk_1` FOREIGN KEY (`order_item_id`) REFERENCES `order_items` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `otp_verifications`
--
ALTER TABLE `otp_verifications`
  ADD CONSTRAINT `fk_otp_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `fk_product_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD CONSTRAINT `fk_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

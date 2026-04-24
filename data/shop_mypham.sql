-- shop_mypham_v2_full.sql
-- FILE DATABASE HOÀN CHỈNH - IMPORT MỘT LẦN DUY NHẤT
-- Bao gồm: Tạo database, tạo bảng, và dữ liệu mẫu đầy đủ cho toàn bộ hệ thống.

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- 1. TẠO CƠ SỞ DỮ LIỆU
--

CREATE DATABASE IF NOT EXISTS `shop_mypham` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `shop_mypham`;

-- --------------------------------------------------------
-- XÓA BẢNG CŨ NẾU TỒN TẠI ĐỂ TRÁNH XUNG ĐỘT KHI IMPORT LẠI
-- --------------------------------------------------------
DROP TABLE IF EXISTS `wishlist`;
DROP TABLE IF EXISTS `reviews`;
DROP TABLE IF EXISTS `order_items`;
DROP TABLE IF EXISTS `orders`;
DROP TABLE IF EXISTS `products`;
DROP TABLE IF EXISTS `users`;
DROP TABLE IF EXISTS `categories`;

-- --------------------------------------------------------
-- 2. CẤU TRÚC BẢNG
-- --------------------------------------------------------

-- Bảng Danh mục
CREATE TABLE `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Bảng Người dùng
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `fullname` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `role` varchar(20) DEFAULT 'user',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Bảng Sản phẩm
CREATE TABLE `products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `price` int(11) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `status` enum('active','out_of_stock','hidden') DEFAULT 'active',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Bảng Đơn hàng
CREATE TABLE `orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `order_code` varchar(50) NOT NULL,
  `fullname` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `total` int(11) NOT NULL,
  `status` varchar(50) DEFAULT 'Đang xử lý',
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Bảng Chi tiết đơn hàng
CREATE TABLE `order_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `price` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Bảng Đánh giá
CREATE TABLE `reviews` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `rating` int(1) NOT NULL,
  `comment` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_product_review` (`user_id`,`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Bảng Yêu thích
CREATE TABLE `wishlist` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_product_wishlist` (`user_id`,`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- 3. DỮ LIỆU MẪU (DUMP DATA)
-- --------------------------------------------------------

-- Categories
INSERT INTO `categories` (`id`, `name`) VALUES
(1, 'Son môi'),
(2, 'Kem dưỡng'),
(3, 'Sữa rửa mặt'),
(4, 'Nước hoa'),
(5, 'Trang điểm');

-- Users (Mật khẩu mặc định là '123' mã hóa)
INSERT INTO `users` (`id`, `username`, `password`, `fullname`, `phone`, `address`, `email`, `role`) VALUES
(1, 'admin', '202cb962ac59075b964b07152d234b70', 'Quản trị viên', '0987654321', 'Hà Nội', 'admin@myphamxinh.vn', 'admin'),
(2, 'khach1', '202cb962ac59075b964b07152d234b70', 'Nguyễn Thị Kim Yến', '0123456789', 'TP. Hồ Chí Minh', 'kimyen@gmail.com', 'user');

-- Products
INSERT INTO `products` (`id`, `name`, `price`, `image`, `category_id`, `description`, `status`) VALUES
(1, 'Son Dior Addict', 800000, '1.jpg', 1, 'Dòng son cao cấp Dior Addict Lip Glow dưỡng ẩm vượt trội.', 'active'),
(2, 'Son MAC Matte', 500000, '2.jpg', 1, 'Son lì MAC Retro Matte nổi tiếng với độ bám màu cực tốt.', 'active'),
(3, 'Son 3CE Velvet', 350000, '3.jpg', 1, 'Son kem lì 3CE Velvet Lip Tint mịn mượt như nhung.', 'active'),
(4, 'Mặt nạ ngủ Laneige', 600000, '4.jpg', 2, 'Mặt nạ ngủ môi giúp loại bỏ tế bào chết và cấp ẩm sâu.', 'active'),
(5, 'Kem Kiehls Ultra', 900000, '5.jpg', 2, 'Kem dưỡng ẩm cấp nước suốt 24 giờ cho làn da mềm mịn.', 'active'),
(6, 'Kem Nền Innisfree', 450000, '6.jpg', 5, 'Kem nền che phủ tốt, mỏng nhẹ và tự nhiên.', 'active'),
(7, 'SRM Cetaphil Gentle', 300000, '7.jpg', 3, 'Sữa rửa mặt dịu nhẹ an toàn cho mọi loại da nhạy cảm.', 'active'),
(8, 'SRM Senka Perfect', 150000, '8.jpg', 3, 'Sữa rửa mặt tạo bọt mịn giúp làm sạch sâu lỗ chân lông.', 'active'),
(9, 'SRM La Roche-Posay', 400000, '9.jpg', 3, 'Sữa rửa mặt cho da dầu mụn kiểm soát bã nhờn hiệu quả.', 'active');

-- Orders
INSERT INTO `orders` (`id`, `user_id`, `order_code`, `fullname`, `phone`, `address`, `total`, `status`, `created_at`) VALUES
(1, 2, 'ORD-20260424090001', 'Nguyễn Thị Kim Yến', '0123456789', 'Quận 1, TP. HCM', 600000, 'Đã duyệt', '2026-04-24 09:00:00'),
(2, NULL, 'ORD-20260424103002', 'Khách vãng lai', '0909112233', 'Quận 7, TP. HCM', 800000, 'Đang xử lý', '2026-04-24 10:30:00');

-- Order Items
INSERT INTO `order_items` (`id`, `order_id`, `product_name`, `price`, `quantity`) VALUES
(1, 1, 'Mặt nạ ngủ Laneige', 600000, 1),
(2, 2, 'Son Dior Addict', 800000, 1);

-- Reviews
INSERT INTO `reviews` (`id`, `user_id`, `product_id`, `rating`, `comment`, `created_at`) VALUES
(1, 2, 1, 5, 'Sản phẩm tuyệt vời, màu rất xinh!', '2026-04-24 11:00:00'),
(2, 2, 4, 4, 'Dùng rất thích, sáng dậy môi rất mềm.', '2026-04-24 11:15:00');

-- Wishlist
INSERT INTO `wishlist` (`id`, `user_id`, `product_id`, `created_at`) VALUES
(1, 2, 2, '2026-04-24 12:00:00'),
(2, 2, 5, '2026-04-24 12:05:00');

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

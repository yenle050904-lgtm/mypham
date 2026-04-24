-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th4 18, 2026 lúc 09:48 PM
-- Phiên bản máy phục vụ: 10.4.32-MariaDB
-- Phiên bản PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `shop_mypham`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `categories`
--

INSERT INTO `categories` (`id`, `name`) VALUES
(1, 'Son môi'),
(2, 'Kem dưỡng'),
(3, 'Sữa rửa mặt'),
(4, 'Nước hoa'),
(5, 'Trang điểm');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `order_code` varchar(50) DEFAULT NULL,
  `fullname` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `total` int(11) DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `orders`
--

INSERT INTO `orders` (`id`, `order_code`, `fullname`, `phone`, `address`, `total`, `status`, `created_at`) VALUES
(1, 'ORD-20260418205759', 'kim yến', '987654321', 'HCM', 350000, 'Đang xử lý', '2026-04-19 01:57:59'),
(2, 'ORD-20260418205842', 'kim yến', '987654321', 'HCM', 350000, 'Đang xử lý', '2026-04-19 01:58:42'),
(3, 'ORD-20260418205908', 'kim yến', '987654321', 'HCM', 350000, 'Đang xử lý', '2026-04-19 01:59:08'),
(4, 'ORD-20260418205949', 'kim yến', '987654321', 'HCM', 500000, 'Đang xử lý', '2026-04-19 01:59:49'),
(5, 'ORD-20260418210006', 'kim yến', '987654321', 'HCM', 500000, 'Đang xử lý', '2026-04-19 02:00:06'),
(6, 'ORD-20260418210019', 'kim yến', '987654321', 'HCM', 500000, 'Đang xử lý', '2026-04-19 02:00:19'),
(7, 'ORD-20260418210056', 'kim yến', '987654321', 'HN', 500000, 'Đang xử lý', '2026-04-19 02:00:56'),
(8, 'ORD-20260418210706', 'kim yến', '987654321', 'HN', 500000, 'Đang xử lý', '2026-04-19 02:07:06'),
(9, 'ORD-20260418210736', 'kim yến', '987654321', 'hcm', 350000, 'Đang xử lý', '2026-04-19 02:07:36'),
(10, 'ORD-20260418211156', 'kim yến', '987654321', 'hcm', 600000, 'Đã duyệt', '2026-04-19 02:11:56'),
(11, 'ORD-20260418213811', 'kim yến', '1', 'hn', 450000, 'Đang xử lý', '2026-04-19 02:38:11'),
(12, 'ORD-20260418213825', 'kim yến', '1', 'hn', 300000, 'Đang xử lý', '2026-04-19 02:38:25');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `product_name` varchar(255) DEFAULT NULL,
  `price` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_name`, `price`, `quantity`) VALUES
(1, 11, 'Kem Innisfree', 450000, 1),
(2, 11, 'Kem Innisfree', 450000, 1),
(3, 12, 'SRM Cetaphil', 300000, 1),
(4, 12, 'SRM Cetaphil', 300000, 1);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `price` int(11) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `products`
--

INSERT INTO `products` (`id`, `name`, `price`, `image`, `category_id`) VALUES
(1, 'Son Dior', 800000, '1.jpg', 1),
(2, 'Son MAC', 500000, '2.jpg', 1),
(3, 'Son 3CE', 350000, '3.jpg', 1),
(4, 'Kem Laneige', 600000, '4.jpg', 2),
(5, 'Kem Kiehl', 900000, '5.jpg', 2),
(6, 'Kem Innisfree', 450000, '6.jpg', 2),
(7, 'SRM Cetaphil', 300000, '7.jpg', 3),
(8, 'SRM Senka', 150000, '8.jpg', 3),
(9, 'SRM La Roche', 400000, '9.jpg', 3);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `role` varchar(20) DEFAULT 'user'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`) VALUES
(1, 'admin', '202cb962ac59075b964b07152d234b70', 'admin'),
(2, 'khach1', '202cb962ac59075b964b07152d234b70', 'user');

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT cho bảng `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT cho bảng `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT cho bảng `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT cho bảng `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

<?php
require_once 'config/database.php';

try {
    // 1. Tạo bảng coupons
    $conn->exec("CREATE TABLE IF NOT EXISTS `coupons` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `code` varchar(50) NOT NULL,
        `discount_type` enum('percent','fixed') NOT NULL DEFAULT 'fixed',
        `discount_value` int(11) NOT NULL,
        `min_order` int(11) DEFAULT 0,
        `max_uses` int(11) DEFAULT 0,
        `used_count` int(11) DEFAULT 0,
        `is_active` tinyint(1) DEFAULT 1,
        `created_at` datetime DEFAULT current_timestamp(),
        PRIMARY KEY (`id`),
        UNIQUE KEY `code` (`code`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    echo "✅ Đã tạo bảng coupons.<br>";

    // 2. Thêm dữ liệu mẫu
    $stmt = $conn->prepare("INSERT IGNORE INTO `coupons` (`code`, `discount_type`, `discount_value`, `min_order`, `max_uses`) VALUES (?, ?, ?, ?, ?)");
    
    $stmt->execute(['XINCHAO', 'fixed', 50000, 200000, 100]); // Giảm 50k cho đơn từ 200k
    $stmt->execute(['MYPHAM10', 'percent', 10, 500000, 50]); // Giảm 10% cho đơn từ 500k
    $stmt->execute(['SALE2026', 'fixed', 100000, 1000000, 20]); // Giảm 100k cho đơn từ 1tr
    
    echo "✅ Đã thêm dữ liệu mẫu cho coupons.<br>";

} catch (PDOException $e) {
    echo "❌ Lỗi: " . $e->getMessage();
}
?>

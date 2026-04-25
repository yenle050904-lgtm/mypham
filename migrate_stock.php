<?php
require_once 'config/database.php';

try {
    // 1. Thêm cột stock
    $conn->exec("ALTER TABLE products ADD COLUMN stock INT DEFAULT 0 AFTER description");
    echo "✅ Đã thêm cột stock vào bảng products.<br>";

    // 2. Cập nhật dữ liệu mẫu
    // status='active' -> stock=50
    $stmt1 = $conn->prepare("UPDATE products SET stock = 50 WHERE status = 'active'");
    $stmt1->execute();
    echo "✅ Đã cập nhật stock=50 cho sản phẩm active.<br>";

    // status='out_of_stock' -> stock=0
    $stmt2 = $conn->prepare("UPDATE products SET stock = 0 WHERE status = 'out_of_stock'");
    $stmt2->execute();
    echo "✅ Đã cập nhật stock=0 cho sản phẩm out_of_stock.<br>";

} catch (PDOException $e) {
    echo "❌ Lỗi: " . $e->getMessage();
}
?>

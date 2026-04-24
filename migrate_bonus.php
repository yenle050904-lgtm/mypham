<?php
require_once 'config/database.php';

try {
    // 1. Bảng Wishlist
    $conn->exec("CREATE TABLE IF NOT EXISTS wishlist (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        product_id INT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY user_product (user_id, product_id)
    )");

    // 2. Bảng Reviews
    $conn->exec("CREATE TABLE IF NOT EXISTS reviews (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        product_id INT NOT NULL,
        rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
        comment TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    echo "Bonus tables (wishlist, reviews) created successfully.";
} catch (PDOException $e) {
    echo "Creation failed: " . $e->getMessage();
}
?>

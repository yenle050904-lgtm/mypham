<?php
require_once 'config/database.php';

try {
    $conn->exec("ALTER TABLE `orders` ADD COLUMN IF NOT EXISTS `payment_method` VARCHAR(50) DEFAULT 'cod' AFTER `status`;");
    echo "Column 'payment_method' added successfully (or already exists).";
} catch (PDOException $e) {
    echo "Error updating table: " . $e->getMessage();
}
?>

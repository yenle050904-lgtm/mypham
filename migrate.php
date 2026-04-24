<?php
require_once 'config/database.php';

try {
    $conn->exec("ALTER TABLE products ADD COLUMN status ENUM('active', 'out_of_stock', 'hidden') DEFAULT 'active'");
    echo "Migration successful: Column 'status' added.";
} catch (PDOException $e) {
    if ($e->getCode() == '42S21') {
        echo "Column 'status' already exists.";
    } else {
        echo "Migration failed: " . $e->getMessage();
    }
}
?>

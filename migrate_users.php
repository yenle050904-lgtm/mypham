<?php
require_once 'config/database.php';
try {
    $conn->exec("ALTER TABLE users 
                 ADD COLUMN fullname VARCHAR(255) NULL,
                 ADD COLUMN phone VARCHAR(20) NULL,
                 ADD COLUMN address TEXT NULL");
    echo "Users table updated with fullname, phone, address columns.";
} catch (PDOException $e) {
    echo "Update failed or columns already exist: " . $e->getMessage();
}
?>

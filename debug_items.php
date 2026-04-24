<?php
require_once 'config/database.php';
try {
    echo "ORDER_ITEMS TABLE:\n";
    print_r($conn->query("DESCRIBE order_items")->fetchAll(PDO::FETCH_ASSOC));
} catch (Exception $e) {
    echo "Table order_items not found or access error.";
}
?>

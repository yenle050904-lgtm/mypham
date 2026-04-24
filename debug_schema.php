<?php
require_once 'config/database.php';
echo "PRODUCTS TABLE:\n";
print_r($conn->query("DESCRIBE products")->fetchAll(PDO::FETCH_ASSOC));
echo "\nORDERS TABLE:\n";
print_r($conn->query("DESCRIBE orders")->fetchAll(PDO::FETCH_ASSOC));
?>

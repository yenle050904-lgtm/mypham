<?php
require_once 'config/database.php';
print_r($conn->query("DESCRIBE users")->fetchAll(PDO::FETCH_ASSOC));
?>

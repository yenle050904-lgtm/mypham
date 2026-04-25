<?php
header('Content-Type: application/json');

$q = trim($_GET['q'] ?? '');

if (strlen($q) < 2) {
    echo json_encode([]);
    exit;
}

try {
    $stmt = $conn->prepare("
        SELECT id, name, price, sale_price, image 
        FROM products 
        WHERE name LIKE ? AND status = 'active'
        LIMIT 5
    ");
    $stmt->execute(['%' . $q . '%']);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Format lại dữ liệu cho JSON
    foreach ($results as &$item) {
        $item['current_price'] = number_format($item['sale_price'] ?? $item['price']) . ' đ';
        $item['img_url'] = (file_exists('uploads/' . $item['image']) && !empty($item['image'])) ? 'uploads/' . $item['image'] : 'images/' . $item['image'];
        unset($item['image'], $item['price'], $item['sale_price']);
    }

    echo json_encode($results);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Query failed']);
}
exit();

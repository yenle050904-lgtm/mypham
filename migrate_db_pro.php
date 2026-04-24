<?php
require_once 'config/database.php';

try {
    // 1. THÊM CỘT user_id VÀO BẢNG orders (Nếu chưa có)
    try {
        $conn->exec("ALTER TABLE orders ADD COLUMN user_id INT NULL AFTER id");
        echo "Added user_id to orders table.\n";
    } catch (PDOException $e) {}

    // 2. THÊM CHỈ MỤC (INDEXES)
    $conn->exec("CREATE INDEX idx_products_category ON products(category_id)");
    $conn->exec("CREATE INDEX idx_orders_created_at ON orders(created_at)");
    $conn->exec("CREATE INDEX idx_orders_user ON orders(user_id)");
    echo "Indexes created.\n";

    // 3. THÊM KHÓA NGOẠI (FOREIGN KEYS)
    // Lưu ý: Đảm bảo dữ liệu hiện tại không vi phạm (có thể set NULL hoặc xóa record mồ côi)
    
    // products -> categories
    $conn->exec("ALTER TABLE products ADD CONSTRAINT fk_products_category 
                 FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL");
    
    // orders -> users
    $conn->exec("ALTER TABLE orders ADD CONSTRAINT fk_orders_user 
                 FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL");
    
    // order_items -> orders
    $conn->exec("ALTER TABLE order_items ADD CONSTRAINT fk_order_items_order 
                 FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE");
                 
    // wishlist -> users/products
    $conn->exec("ALTER TABLE wishlist ADD CONSTRAINT fk_wishlist_user 
                 FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE");
    $conn->exec("ALTER TABLE wishlist ADD CONSTRAINT fk_wishlist_product 
                 FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE");

    // reviews -> users/products
    $conn->exec("ALTER TABLE reviews ADD CONSTRAINT fk_reviews_user 
                 FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE");
    $conn->exec("ALTER TABLE reviews ADD CONSTRAINT fk_reviews_product 
                 FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE");

    echo "Foreign keys added successfully.\n";

} catch (PDOException $e) {
    echo "DB Improvement Error: " . $e->getMessage();
}
?>

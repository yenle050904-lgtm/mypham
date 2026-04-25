<?php
// ==================== XỬ LÝ TRƯỚC KHI XUẤT HTML ====================
if (!isset($_SESSION['last_order']) || empty($_SESSION['last_order'])) {
    header("Location: index.php");
    exit();
}

$order_data = $_SESSION['last_order'];

// Lấy user_id nếu đang đăng nhập
$user_id = null;
if (isset($_SESSION['user'])) {
    $stmt_u = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $stmt_u->execute([$_SESSION['user']]);
    $user_id = $stmt_u->fetch()['id'] ?? null;
}

// Tạo mã đơn hàng
$order_code = 'ORD-' . date('YmdHis') . rand(10, 99);

// 1. LƯU VÀO BẢNG orders (PDO)
$stmt = $conn->prepare("
    INSERT INTO orders (user_id, order_code, fullname, phone, address, total, status, payment_method) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
");
$status = 'Đang xử lý';
$stmt->execute([
    $user_id, 
    $order_code, 
    $order_data['fullname'], 
    $order_data['phone'], 
    $order_data['address'], 
    $order_data['total'], 
    $status,
    $order_data['payment_method']
]);

// Lấy ID đơn hàng vừa tạo
$order_id = $conn->lastInsertId();

// 2. LƯU SẢN PHẨM VÀO order_items (PDO)
if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    $cart_items = array_count_values($_SESSION['cart']);
    foreach ($cart_items as $p_id => $qty) {
        $res = $conn->prepare("SELECT name, price FROM products WHERE id = ?");
        $res->execute([$p_id]);
        $p = $res->fetch();
        if (!$p) continue;

        $stmt_item = $conn->prepare("INSERT INTO order_items (order_id, product_name, price, quantity) VALUES (?, ?, ?, ?)");
        $stmt_item->execute([$order_id, $p['name'], $p['price'], $qty]);

        // 3. TRỪ STOCK VÀ CẬP NHẬT TRẠNG THÁI (PDO)
        $stmt_stock = $conn->prepare("UPDATE products SET stock = stock - ? WHERE id = ? AND stock >= ?");
        $stmt_stock->execute([$qty, $p_id, $qty]);

        // Kiểm tra nếu hết hàng thì đổi status
        $stmt_check = $conn->prepare("SELECT stock FROM products WHERE id = ?");
        $stmt_check->execute([$p_id]);
        $current_stock = $stmt_check->fetch()['stock'];
        if ($current_stock <= 0) {
            $stmt_status = $conn->prepare("UPDATE products SET status = 'out_of_stock' WHERE id = ?");
            $stmt_status->execute([$p_id]);
        }
    }
}

// Xóa dữ liệu tạm & Cập nhật coupon
if (isset($_SESSION['coupon'])) {
    $cp_id = $_SESSION['coupon']['id'];
    $stmt_cp = $conn->prepare("UPDATE coupons SET used_count = used_count + 1 WHERE id = ?");
    $stmt_cp->execute([$cp_id]);
    unset($_SESSION['coupon']);
}

unset($_SESSION['cart']);
unset($_SESSION['last_order']);
?>

<?php include 'layout/header.php'; ?>

<div class="container py-5 text-center">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-body py-5 px-4 px-md-5">
                    <div class="mb-4">
                        <div class="bg-success bg-opacity-10 text-success rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 100px; height: 100px;">
                            <i class="fa-solid fa-check fs-1"></i>
                        </div>
                    </div>
                    
                    <h2 class="fw-bold text-success mb-2">ĐẶT HÀNG THÀNH CÔNG!</h2>
                    <p class="text-secondary mb-5">Cảm ơn bạn <strong><?= htmlspecialchars($order_data['fullname']) ?></strong> đã tin tưởng mua sắm tại Mỹ Phẩm Xinh.</p>
                    <div class="row g-3 text-start mb-5 text-center">
                        <div class="col-md-4">
                            <div class="bg-light p-4 rounded-4 h-100">
                                <h6 class="fw-bold text-muted small text-uppercase mb-3 text-center">Mã đơn hàng</h6>
                                <h4 class="text-pink fw-bold mb-0 text-center"><?= $order_code ?></h4>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="bg-light p-4 rounded-4 h-100">
                                <h6 class="fw-bold text-muted small text-uppercase mb-3 text-center">Tổng thanh toán</h6>
                                <h4 class="text-danger fw-bold mb-0 text-center"><?= number_format($order_data['total']) ?> đ</h4>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="bg-light p-4 rounded-4 h-100">
                                <h6 class="fw-bold text-muted small text-uppercase mb-3 text-center">Thanh toán</h6>
                                <h4 class="fw-bold mb-0 small text-uppercase text-center"><?= $order_data['payment_method'] == 'cod' ? 'Tiền mặt' : 'Chuyển khoản' ?></h4>
                            </div>
                        </div>
                    </div>

                    <?php if ($order_data['payment_method'] === 'bank'): ?>
                        <div class="alert alert-primary border-0 rounded-4 p-4 mb-5 text-start shadow-sm">
                            <h6 class="fw-bold mb-3"><i class="fa-solid fa-building-columns me-2"></i>Hướng dẫn chuyển khoản nhanh:</h6>
                            <div class="row g-2">
                                <div class="col-6">Ngân hàng:</div><div class="col-6 fw-bold">Vietcombank</div>
                                <div class="col-6">Số tài khoản:</div><div class="col-6 fw-bold">1234567890</div>
                                <div class="col-6">Chủ tài khoản:</div><div class="col-6 fw-bold text-uppercase">SHOP MY PHAM XINH</div>
                                <div class="col-6">Nội dung CK:</div><div class="col-6 fw-bold text-danger"><?= $order_code ?></div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="alert alert-info border-0 rounded-4 p-3 mb-5 small">
                        <i class="fa-solid fa-circle-info me-2"></i>Chúng tôi đã nhận được đơn hàng của bạn và sẽ sớm liên hệ qua số điện thoại để xác nhận vận chuyển.
                    </div>

                    <div class="d-flex flex-column flex-md-row gap-3 justify-content-center">
                        <a href="?page=my_orders" class="btn btn-pink px-5 py-3 rounded-pill shadow-sm">
                            <i class="fa-solid fa-clipboard-list me-2"></i>Xem đơn hàng
                        </a>
                        <a href="index.php" class="btn btn-outline-secondary px-5 py-3 rounded-pill">
                            <i class="fa-solid fa-bag-shopping me-2"></i>Tiếp tục mua sắm
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'layout/footer.php'; ?>
<?php 
include 'layout/header.php'; 

// Kiểm tra quyền truy cập (Admin hoặc chủ đơn hàng)
if (!isset($_SESSION['user'])) {
    die('<div class="container py-5"><div class="alert alert-danger text-center">Vui lòng đăng nhập để xem chi tiết đơn hàng!</div></div>');
}

if (!isset($_GET['id'])) {
    die('<div class="container py-5"><div class="alert alert-danger text-center">Không tìm thấy đơn hàng!</div></div>');
}

$order_id = (int)$_GET['id'];

// Lấy thông tin đơn hàng (PDO)
$stmt = $conn->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->execute([$order_id]);
$order = $stmt->fetch();

if (!$order) {
    die('<div class="container py-5"><div class="alert alert-danger text-center">Đơn hàng không tồn tại!</div></div>');
}

// Lấy ID của user đang đăng nhập
$stmt_u = $conn->prepare("SELECT id FROM users WHERE username = ?");
$stmt_u->execute([$_SESSION['user']]);
$user_row = $stmt_u->fetch();
$current_user_id = $user_row['id'] ?? null;

// Nếu không phải admin, kiểm tra xem có đúng là đơn hàng của mình không (dựa trên user_id)
if (($_SESSION['role'] ?? '') !== 'admin' && (int)$order['user_id'] !== (int)$current_user_id) {
    die('<div class="container py-5"><div class="alert alert-danger text-center">Bạn không có quyền xem đơn hàng của người khác!</div></div>');
}

// Lấy danh sách sản phẩm trong đơn (PDO)
$stmt = $conn->prepare("SELECT * FROM order_items WHERE order_id = ?");
$stmt->execute([$order_id]);
$items = $stmt->fetchAll();
?>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-pink">📦 Chi tiết đơn hàng #<?= htmlspecialchars($order['order_code']) ?></h2>
        <a href="?page=<?= ($_SESSION['role'] ?? '') === 'admin' ? 'admin_orders' : 'my_orders' ?>" class="btn btn-secondary">← Quay lại danh sách</a>
    </div>

    <div class="row">
        <!-- Thông tin đơn hàng -->
        <div class="col-lg-5">
            <div class="card checkout-card mb-4">
                <div class="card-header bg-pink text-white">
                    <h5 class="mb-0">📋 Thông tin đơn hàng</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr><td><strong>Mã đơn:</strong></td><td><?= htmlspecialchars($order['order_code']) ?></td></tr>
                        <tr><td><strong>Ngày đặt:</strong></td><td><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></td></tr>
                        <tr><td><strong>Khách hàng:</strong></td><td><?= htmlspecialchars($order['fullname']) ?></td></tr>
                        <tr><td><strong>SĐT:</strong></td><td><?= htmlspecialchars($order['phone']) ?></td></tr>
                        <tr><td><strong>Địa chỉ:</strong></td><td><?= htmlspecialchars($order['address']) ?></td></tr>
                        <tr><td><strong>Thanh toán:</strong></td>
                            <td><span class="badge bg-<?= ($order['payment_method'] ?? 'cod') == 'cod' ? 'success' : 'primary' ?>"><?= ($order['payment_method'] ?? 'cod') == 'cod' ? 'Tiền mặt (COD)' : 'Chuyển khoản' ?></span></td>
                        </tr>
                        <tr><td><strong>Trạng thái:</strong></td>
                            <td><span class="badge bg-<?= $order['status']=='Đang xử lý' ? 'warning' : ($order['status']=='Đã duyệt' ? 'primary' : 'success') ?> fs-6"><?= $order['status'] ?></span></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- Bảng sản phẩm -->
        <div class="col-lg-7">
            <div class="card checkout-card">
                <div class="card-header bg-pink text-white">
                    <h5 class="mb-0">🛍️ Sản phẩm trong đơn</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Sản phẩm</th>
                                    <th class="text-end">Giá</th>
                                    <th class="text-end">Số lượng</th>
                                    <th class="text-end">Thành tiền</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $total = 0;
                                foreach ($items as $item): 
                                    $subtotal = $item['price'] * $item['quantity'];
                                    $total += $subtotal;
                                ?>
                                <tr>
                                    <td><?= htmlspecialchars($item['product_name']) ?></td>
                                    <td class="text-end"><?= number_format($item['price']) ?> đ</td>
                                    <td class="text-end"><?= $item['quantity'] ?></td>
                                    <td class="text-end fw-bold"><?= number_format($subtotal) ?> đ</td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr class="table-pink">
                                    <th colspan="3" class="text-end fs-5">TỔNG TIỀN</th>
                                    <th class="text-end fs-4 text-danger"><?= number_format($total) ?> đ</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'layout/footer.php'; ?>
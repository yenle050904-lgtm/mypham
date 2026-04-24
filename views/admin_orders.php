<?php
// ==================== XỬ LÝ TRƯỚC KHI XUẤT HTML ====================

// Kiểm tra quyền admin
if (($_SESSION['role'] ?? '') !== 'admin') {
    die('<div class="container py-5"><div class="alert alert-danger text-center">Bạn không có quyền truy cập!</div></div>');
}

$success = '';
$error = '';

// Xóa đơn hàng
if (isset($_GET['delete'])) {
    $order_id = (int)$_GET['delete'];
    $stmt1 = $conn->prepare("DELETE FROM order_items WHERE order_id = ?");
    $stmt1->execute([$order_id]);
    $stmt2 = $conn->prepare("DELETE FROM orders WHERE id = ?");
    if ($stmt2->execute([$order_id])) {
        $success = "✅ Đã xóa đơn hàng thành công!";
    } else {
        $error = "Có lỗi xảy ra khi xóa đơn hàng.";
    }
}

// Cập nhật trạng thái đơn hàng
if (isset($_POST['update_status'])) {
    // CSRF tự động kiểm tra
    $order_id = (int)$_POST['order_id'];
    $new_status = $_POST['status'];
    $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->execute([$new_status, $order_id]);
    $success = "Cập nhật trạng thái thành công!";
}

// --- XỬ LÝ LỌC & TÌM KIẾM ---
$where_clauses = [];
$params = [];

if (isset($_GET['order_code']) && $_GET['order_code'] != '') {
    $where_clauses[] = "order_code LIKE ?";
    $params[] = "%" . $_GET['order_code'] . "%";
}

if (isset($_GET['status']) && $_GET['status'] != '') {
    $where_clauses[] = "status = ?";
    $params[] = $_GET['status'];
}

if (isset($_GET['date_from']) && $_GET['date_from'] != '') {
    $where_clauses[] = "DATE(created_at) >= ?";
    $params[] = $_GET['date_from'];
}

if (isset($_GET['date_to']) && $_GET['date_to'] != '') {
    $where_clauses[] = "DATE(created_at) <= ?";
    $params[] = $_GET['date_to'];
}

$where_sql = "";
if (count($where_clauses) > 0) {
    $where_sql = " WHERE " . implode(" AND ", $where_clauses);
}

$query = "SELECT * FROM orders $where_sql ORDER BY id DESC";
$stmt = $conn->prepare($query);
$stmt->execute($params);
$orders = $stmt->fetchAll();
?>

<?php include 'layout/header.php'; ?>

<div class="container py-5">
    <h2 class="text-center mb-4 text-pink fw-bold font-elegant">📋 Quản lý Đơn hàng - Admin</h2>

    <?php if ($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>

    <!-- Bộ lọc Admin -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <form method="GET" class="row g-3 align-items-end">
                <input type="hidden" name="page" value="admin_orders">
                
                <div class="col-md-3">
                    <label class="form-label small fw-bold">Mã đơn hàng</label>
                    <input type="text" name="order_code" class="form-control" placeholder="Tìm mã đơn..." value="<?= htmlspecialchars($_GET['order_code'] ?? '') ?>">
                </div>

                <div class="col-md-2">
                    <label class="form-label small fw-bold">Trạng thái</label>
                    <select name="status" class="form-select">
                        <option value="">Tất cả</option>
                        <option value="Đang xử lý" <?= (($_GET['status'] ?? '') == 'Đang xử lý') ? 'selected' : '' ?>>Đang xử lý</option>
                        <option value="Đã duyệt" <?= (($_GET['status'] ?? '') == 'Đã duyệt') ? 'selected' : '' ?>>Đã duyệt</option>
                        <option value="Đang giao" <?= (($_GET['status'] ?? '') == 'Đang giao') ? 'selected' : '' ?>>Đang giao</option>
                        <option value="Hoàn thành" <?= (($_GET['status'] ?? '') == 'Hoàn thành') ? 'selected' : '' ?>>Hoàn thành</option>
                        <option value="Đã hủy" <?= (($_GET['status'] ?? '') == 'Đã hủy') ? 'selected' : '' ?>>Đã hủy</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label small fw-bold">Khoảng ngày đặt</label>
                    <div class="input-group">
                        <input type="date" name="date_from" class="form-control" value="<?= htmlspecialchars($_GET['date_from'] ?? '') ?>">
                        <span class="input-group-text">-</span>
                        <input type="date" name="date_to" class="form-control" value="<?= htmlspecialchars($_GET['date_to'] ?? '') ?>">
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-pink w-100"><i class="fa-solid fa-magnifying-glass me-2"></i>Tìm kiếm</button>
                        <a href="?page=admin_orders" class="btn btn-light"><i class="fa-solid fa-rotate-left"></i></a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <?php if (empty($orders)): ?>
        <div class="alert alert-info text-center">Không tìm thấy đơn hàng nào phù hợp với bộ lọc.</div>
    <?php else: ?>
        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light text-dark">
                                <tr>
                                    <th class="ps-3">Mã đơn</th>
                                    <th>Ngày đặt</th>
                                    <th>Khách hàng</th>
                                    <th>Tổng tiền</th>
                                    <th>Thanh toán</th>
                                    <th>Trạng thái</th>
                                    <th class="text-center">Hành động</th>
                                </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $order): ?>
                            <tr>
                                <td class="ps-3"><strong><?= htmlspecialchars($order['order_code']) ?></strong></td>
                                <td><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></td>
                                <td>
                                    <div class="fw-bold"><?= htmlspecialchars($order['fullname']) ?></div>
                                    <div class="small text-muted"><?= htmlspecialchars($order['phone']) ?></div>
                                </td>
                                <td class="fw-bold text-danger"><?= number_format($order['total']) ?> đ</td>
                                <td>
                                    <span class="badge bg-<?= ($order['payment_method'] ?? 'cod') == 'cod' ? 'success' : 'primary' ?> opacity-75">
                                        <?= ($order['payment_method'] ?? 'cod') == 'cod' ? 'TIỀN MẶT' : 'CHUYỂN KHOẢN' ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-<?= $order['status']=='Đang xử lý' ? 'warning' : ($order['status']=='Đã duyệt' ? 'primary' : ($order['status']=='Đã hủy' ? 'secondary' : 'success')) ?>">
                                        <?= htmlspecialchars($order['status']) ?>
                                    </span>
                                </td>
                                <td class="text-center py-3" style="min-width: 200px;">
                                    <div class="d-flex flex-column gap-1 pe-3">
                                        <a href="?page=order_detail&id=<?= $order['id'] ?>" class="btn btn-info btn-sm">
                                            <i class="fa-solid fa-eye me-1"></i>Chi tiết
                                        </a>

                                        <form method="POST" class="d-inline">
                                            <?= csrf_input() ?>
                                            <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                            <div class="input-group input-group-sm">
                                                <select name="status" class="form-select form-select-sm">
                                                    <option value="Đang xử lý" <?= $order['status']=='Đang xử lý'?'selected':'' ?>>Đang xử lý</option>
                                                    <option value="Đã duyệt" <?= $order['status']=='Đã duyệt'?'selected':'' ?>>Đã duyệt</option>
                                                    <option value="Đang giao" <?= $order['status']=='Đang giao'?'selected':'' ?>>Đang giao</option>
                                                    <option value="Hoàn thành" <?= $order['status']=='Hoàn thành'?'selected':'' ?>>Hoàn thành</option>
                                                    <option value="Đã hủy" <?= $order['status']=='Đã hủy'?'selected':'' ?>>Đã hủy</option>
                                                </select>
                                                <button type="submit" name="update_status" class="btn btn-success">Lưu</button>
                                            </div>
                                        </form>

                                        <a href="?page=admin_orders&delete=<?= $order['id'] ?>" class="btn btn-outline-danger btn-sm" onclick="return confirm('Xóa đơn hàng này?')">
                                            <i class="fa-solid fa-trash-can me-1"></i>Xóa
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include 'layout/footer.php'; ?>
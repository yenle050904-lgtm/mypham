<?php
// ==================== XỬ LÝ TRƯỚC KHI XUẤT HTML ====================
if (!isset($_SESSION['user']) || ($_SESSION['role'] ?? '') === 'admin') {
    header("Location: index.php");
    exit();
}

$success = '';
$error = '';

// Lấy ID của user đang đăng nhập
$stmt_u = $conn->prepare("SELECT id FROM users WHERE username = ?");
$stmt_u->execute([$_SESSION['user']]);
$user_row = $stmt_u->fetch();
if (!$user_row) {
    header("Location: ?page=logout");
    exit();
}
$current_user_id = $user_row['id'];

// Xử lý Hủy Đơn Hàng
if (isset($_POST['cancel_order'])) {
    // CSRF tự động kiểm tra
    $order_id = (int)$_POST['order_id'];

    // Chỉ được hủy nếu status là 'Đang xử lý' và đúng là đơn của mình (dựa trên user_id)
    $stmt = $conn->prepare("SELECT status FROM orders WHERE id = ? AND user_id = ?");
    $stmt->execute([$order_id, $current_user_id]);
    $order = $stmt->fetch();

    if ($order && $order['status'] === 'Đang xử lý') {
        $update = $conn->prepare("UPDATE orders SET status = 'Đã hủy' WHERE id = ?");
        if ($update->execute([$order_id])) {
            $success = "Hủy đơn hàng thành công!";
        } else {
            $error = "Có lỗi xảy ra khi hủy đơn hàng.";
        }
    } else {
        $error = "Bạn không thể hủy đơn hàng này.";
    }
}
?>

<?php include 'layout/header.php'; ?>

<div class="container py-5">
    <h2 class="text-center mb-5 text-pink fw-bold">📋 Đơn hàng của tôi</h2>

    <?php if ($success): ?><div class="alert alert-success shadow-sm"><?= $success ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert alert-danger shadow-sm"><?= $error ?></div><?php endif; ?>

    <?php
    $stmt = $conn->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY id DESC");
    $stmt->execute([$current_user_id]);
    $orders = $stmt->fetchAll();
    ?>

    <?php if (empty($orders)): ?>
        <div class="text-center py-5">
            <div class="mb-4 text-muted"><i class="fa-solid fa-clipboard-list fs-1 opacity-25"></i></div>
            <h4 class="text-muted">Bạn chưa có đơn hàng nào.</h4>
            <a href="index.php" class="btn btn-pink mt-3 px-4 shadow-sm">Mua sắm ngay</a>
        </div>
    <?php else: ?>
        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light text-dark">
                            <tr>
                                <th class="ps-4">Mã đơn</th>
                                <th>Ngày đặt</th>
                                <th>Tổng tiền</th>
                                <th>Trạng thái</th>
                                <th class="text-center">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $o): ?>
                            <tr>
                                <td class="ps-4" data-label="Mã đơn"><strong><?= htmlspecialchars($o['order_code']) ?></strong></td>
                                <td data-label="Ngày đặt"><?= date('d/m/Y H:i', strtotime($o['created_at'])) ?></td>
                                <td class="fw-bold text-pink" data-label="Tổng tiền"><?= number_format($o['total']) ?> đ</td>
                                <td data-label="Trạng thái">
                                    <span class="badge bg-<?= $o['status']=='Đang xử lý' ? 'warning' : ($o['status']=='Đã duyệt' ? 'primary' : ($o['status']=='Đã hủy' ? 'secondary' : 'success')) ?>">
                                        <?= htmlspecialchars($o['status']) ?>
                                    </span>
                                </td>
                                <td class="text-center py-3" data-label="Thao tác">
                                    <a href="?page=order_detail&id=<?= $o['id'] ?>" class="btn btn-sm btn-outline-info me-2">
                                        <i class="fa-solid fa-eye me-1"></i>Chi tiết
                                    </a>
                                    
                                    <?php if ($o['status'] === 'Đang xử lý'): ?>
                                        <form method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn hủy đơn hàng này?')">
                                            <?= csrf_input() ?>
                                            <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
                                            <button type="submit" name="cancel_order" class="btn btn-sm btn-outline-danger">
                                                <i class="fa-solid fa-xmark me-1"></i>Hủy đơn
                                            </button>
                                        </form>
                                    <?php endif; ?>
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
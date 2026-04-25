<?php
// ==================== XỬ LÝ TRƯỚC KHI XUẤT HTML ====================
$success = '';
$error = '';

// Kiểm tra quyền admin
if (($_SESSION['role'] ?? '') !== 'admin') {
    die('<div class="container py-5"><div class="alert alert-danger text-center">Bạn không có quyền truy cập!</div></div>');
}

// Xử lý Thêm / Sửa Coupon
if (isset($_POST['add']) || isset($_POST['update'])) {
    $code           = strtoupper(trim($_POST['code']));
    $discount_type  = $_POST['discount_type'];
    $discount_value = (int)$_POST['discount_value'];
    $min_order      = (int)$_POST['min_order'];
    $max_uses       = (int)$_POST['max_uses'];
    $is_active      = isset($_POST['is_active']) ? 1 : 0;

    if (empty($code)) {
        $error = "Mã giảm giá không được để trống!";
    } else {
        if (isset($_POST['add'])) {
            try {
                $stmt = $conn->prepare("INSERT INTO coupons (code, discount_type, discount_value, min_order, max_uses, is_active) VALUES (?, ?, ?, ?, ?, ?)");
                if ($stmt->execute([$code, $discount_type, $discount_value, $min_order, $max_uses, $is_active])) {
                    $success = "Thêm mã giảm giá thành công!";
                }
            } catch (PDOException $e) {
                $error = "Mã giảm giá này đã tồn tại!";
            }
        } else {
            $id = (int)$_POST['id'];
            $stmt = $conn->prepare("UPDATE coupons SET code = ?, discount_type = ?, discount_value = ?, min_order = ?, max_uses = ?, is_active = ? WHERE id = ?");
            if ($stmt->execute([$code, $discount_type, $discount_value, $min_order, $max_uses, $is_active, $id])) {
                $success = "Cập nhật mã giảm giá thành công!";
            } else {
                $error = "Có lỗi xảy ra khi cập nhật.";
            }
        }
    }
}

// Xử lý Xóa
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM coupons WHERE id = ?");
    if ($stmt->execute([$id])) {
        $success = "Xóa mã giảm giá thành công!";
    }
}

// Lấy dữ liệu để sửa
$edit = null;
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM coupons WHERE id = ?");
    $stmt->execute([$id]);
    $edit = $stmt->fetch();
}

$coupons = $conn->query("SELECT * FROM coupons ORDER BY id DESC")->fetchAll();
?>

<?php include 'layout/header.php'; ?>

<div class="container py-5">
    <h2 class="text-center mb-4 text-pink fw-bold font-elegant">🎟️ Quản lý Mã giảm giá (Coupons)</h2>

    <?php if ($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>

    <!-- Form Thêm / Sửa -->
    <div class="card mb-5 shadow-sm border-0">
        <div class="card-body p-4">
            <h4 class="fw-bold mb-4"><?= $edit ? '<i class="fa-solid fa-pen-to-square me-2 text-warning"></i>Sửa mã giảm giá' : '<i class="fa-solid fa-plus me-2 text-pink"></i>Thêm mã giảm giá mới' ?></h4>
            <form method="POST" class="row g-3">
                <?= csrf_input() ?>
                <?php if ($edit): ?>
                    <input type="hidden" name="id" value="<?= $edit['id'] ?>">
                <?php endif; ?>

                <div class="col-md-3">
                    <label class="form-label fw-medium">Mã Coupon</label>
                    <input name="code" class="form-control" value="<?= htmlspecialchars($edit['code'] ?? '') ?>" placeholder="VD: GIAM20K" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-medium">Loại giảm giá</label>
                    <select name="discount_type" class="form-select">
                        <option value="fixed" <?= (isset($edit['discount_type']) && $edit['discount_type'] == 'fixed') ? 'selected' : '' ?>>Số tiền cố định (đ)</option>
                        <option value="percent" <?= (isset($edit['discount_type']) && $edit['discount_type'] == 'percent') ? 'selected' : '' ?>>Phần trăm (%)</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-medium">Giá trị giảm</label>
                    <input name="discount_value" type="number" class="form-control" value="<?= $edit['discount_value'] ?? '' ?>" required min="0">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-medium">Đơn tối thiểu (VNĐ)</label>
                    <input name="min_order" type="number" class="form-control" value="<?= $edit['min_order'] ?? '0' ?>" required min="0">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-medium">Số lượt dùng tối đa</label>
                    <input name="max_uses" type="number" class="form-control" value="<?= $edit['max_uses'] ?? '0' ?>" required min="0" title="0 = không giới hạn">
                    <div class="form-text small">0 = Không giới hạn</div>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" name="is_active" id="isActive" <?= (!isset($edit['is_active']) || $edit['is_active'] == 1) ? 'checked' : '' ?>>
                        <label class="form-check-label fw-bold" for="isActive">Kích hoạt</label>
                    </div>
                </div>

                <div class="col-12 mt-4">
                    <button type="submit" name="<?= $edit ? 'update' : 'add' ?>" class="btn btn-pink px-5">
                         <?= $edit ? 'Cập nhật mã' : 'Lưu mã mới' ?>
                    </button>
                    <?php if ($edit): ?>
                        <a href="?page=admin_coupons" class="btn btn-light ms-2">Hủy bỏ</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <!-- Danh sách Coupon -->
    <h4 class="fw-bold mb-4 px-2"><i class="fa-solid fa-ticket me-2 text-pink"></i>Danh sách Mã giảm giá hiện có</h4>
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Mã</th>
                            <th>Loại</th>
                            <th>Giá trị</th>
                            <th>Đơn tối thiểu</th>
                            <th>Sử dụng</th>
                            <th>Trạng thái</th>
                            <th class="text-center">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($coupons as $cp): ?>
                        <tr>
                            <td class="ps-4"><strong class="text-pink"><?= htmlspecialchars($cp['code']) ?></strong></td>
                            <td><?= $cp['discount_type'] == 'percent' ? 'Phần trăm' : 'Cố định' ?></td>
                            <td class="fw-bold"><?= number_format($cp['discount_value']) ?><?= $cp['discount_type'] == 'percent' ? '%' : ' đ' ?></td>
                            <td><?= number_format($cp['min_order']) ?> đ</td>
                            <td>
                                <span class="badge bg-light text-dark border">
                                    <?= $cp['used_count'] ?> / <?= $cp['max_uses'] > 0 ? $cp['max_uses'] : '∞' ?>
                                </span>
                            </td>
                            <td>
                                <?php if($cp['is_active']): ?>
                                    <span class="badge bg-success">Đang dùng</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Đã tắt</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center pe-4">
                                <a href="?page=admin_coupons&edit=<?= $cp['id'] ?>" class="btn btn-sm btn-light text-warning" title="Sửa">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </a>
                                <a href="?page=admin_coupons&delete=<?= $cp['id'] ?>" class="btn btn-sm btn-light text-danger" onclick="return confirm('Xóa mã giảm giá này?')" title="Xóa">
                                    <i class="fa-solid fa-trash-can"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include 'layout/footer.php'; ?>

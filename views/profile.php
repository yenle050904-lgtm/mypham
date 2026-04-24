<?php
// ==================== XỬ LÝ TRƯỚC KHI XUẤT HTML ====================
if (!isset($_SESSION['user'])) {
    header("Location: ?page=login");
    exit();
}

$username = $_SESSION['user'];
$success = '';
$error = '';

// Lấy thông tin user hiện tại
$stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
$stmt->execute([$username]);
$user = $stmt->fetch();

if (isset($_POST['update_profile'])) {
    // CSRF đã được config/security.php tự động kiểm tra
    $fullname = trim($_POST['fullname']);
    $phone    = trim($_POST['phone']);
    $address  = trim($_POST['address']);

    $update = $conn->prepare("UPDATE users SET fullname = ?, phone = ?, address = ? WHERE username = ?");
    if ($update->execute([$fullname, $phone, $address, $username])) {
        $success = "Cập nhật hồ sơ thành công!";
        // Cập nhật lại dữ liệu hiển thị
        $user['fullname'] = $fullname;
        $user['phone'] = $phone;
        $user['address'] = $address;
    } else {
        $error = "Có lỗi xảy ra, vui lòng thử lại.";
    }
}

if (isset($_POST['change_password'])) {
    $old_pass = $_POST['old_password'];
    $new_pass = $_POST['new_password'];
    $confirm_pass = $_POST['confirm_password'];

    if ($new_pass !== $confirm_pass) {
        $error = "Mật khẩu mới không khớp!";
    } elseif (password_verify($old_pass, $user['password'])) {
        $hashed = password_hash($new_pass, PASSWORD_DEFAULT);
        $update = $conn->prepare("UPDATE users SET password = ? WHERE username = ?");
        if ($update->execute([$hashed, $username])) {
            $success = "Đổi mật khẩu thành công!";
        }
    } else {
        $error = "Mật khẩu cũ không chính xác!";
    }
}
?>

<?php include 'layout/header.php'; ?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <h2 class="fw-bold mb-5 text-center text-pink"><i class="fa-solid fa-id-card-clip me-2"></i>Hồ sơ cá nhân</h2>

            <?php if ($success): ?><div class="alert alert-success shadow-sm"><?= $success ?></div><?php endif; ?>
            <?php if ($error): ?><div class="alert alert-danger shadow-sm"><?= $error ?></div><?php endif; ?>

            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-4 p-md-5">
                    <h5 class="fw-bold mb-4 border-bottom pb-3">Thông tin chi tiết</h5>
                    <form method="POST">
                        <?= csrf_input() ?>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">Tên người dùng (Username)</label>
                                <input type="text" class="form-control bg-light border-0" value="<?= htmlspecialchars($user['username']) ?>" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Họ và tên</label>
                                <input type="text" name="fullname" class="form-control" value="<?= htmlspecialchars($user['fullname'] ?? '') ?>" placeholder="Nhập họ tên đầy đủ">
                            </div>
                            <div class="col-md-12">
                                <label class="form-label small fw-bold">Số điện thoại</label>
                                <input type="tel" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" placeholder="Nhập số điện thoại">
                            </div>
                            <div class="col-md-12">
                                <label class="form-label small fw-bold">Địa chỉ nhận hàng</label>
                                <textarea name="address" class="form-control" rows="3" placeholder="Số nhà, đường, phường, quận..."><?= htmlspecialchars($user['address'] ?? '') ?></textarea>
                            </div>
                            <div class="col-12 mt-4">
                                <button type="submit" name="update_profile" class="btn btn-pink px-5 py-2 rounded-pill">
                                    <i class="fa-solid fa-floppy-disk me-2"></i>Lưu thay đổi
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-4 p-md-5">
                    <h5 class="fw-bold mb-4 border-bottom pb-3 text-warning"><i class="fa-solid fa-key me-2"></i>Đổi mật khẩu</h5>
                    <form method="POST">
                        <?= csrf_input() ?>
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label small fw-bold">Mật khẩu hiện tại</label>
                                <input type="password" name="old_password" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Mật khẩu mới</label>
                                <input type="password" name="new_password" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Xác nhận mật khẩu mới</label>
                                <input type="password" name="confirm_password" class="form-control" required>
                            </div>
                            <div class="col-12 mt-4">
                                <button type="submit" name="change_password" class="btn btn-outline-warning px-5 py-2 rounded-pill">
                                    Xác nhận đổi mật khẩu
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="text-center">
                <a href="?page=my_orders" class="btn btn-link text-pink text-decoration-none">
                    <i class="fa-solid fa-bag-shopping me-1"></i> Quản lý đơn hàng của bạn
                </a>
            </div>
        </div>
    </div>
</div>

<?php include 'layout/footer.php'; ?>

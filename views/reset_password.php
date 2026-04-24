<?php
// ==================== XỬ LÝ RESET MẬT KHẨU ====================
$success_msg = '';
$error = '';
$token = $_GET['token'] ?? '';
$is_valid = false;
$user_email = '';

if (empty($token)) {
    $error = "Thiếu mã xác nhận (Token)!";
} else {
    // Kiểm tra token và thời hạn (1 giờ)
    $stmt = $conn->prepare("SELECT email, created_at FROM password_resets WHERE token = ?");
    $stmt->execute([$token]);
    $reset = $stmt->fetch();

    if ($reset) {
        $created_at = strtotime($reset['created_at']);
        $now = time();
        $diff = ($now - $created_at) / 3600; // Đổi ra giờ

        if ($diff > 1) {
            $error = "Mã xác nhận đã hết hạn (quá 1 giờ). Vui lòng yêu cầu lại!";
        } else {
            $is_valid = true;
            $user_email = $reset['email'];
        }
    } else {
        $error = "Mã xác nhận không hợp lệ hoặc đã qua sử dụng!";
    }
}

// Xử lý khi Submit form đặt lại
if ($is_valid && isset($_POST['reset_password'])) {
    $new_pass = $_POST['new_password'];
    $confirm_pass = $_POST['confirm_password'];

    if (strlen($new_pass) < 1) {
        $error = "Mật khẩu không được để trống!";
    } elseif ($new_pass !== $confirm_pass) {
        $error = "Mật khẩu xác nhận không khớp!";
    } else {
        // Cập nhật mật khẩu mới
        $hashed_password = hash_password($new_pass);
        $update = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
        if ($update->execute([$hashed_password, $user_email])) {
            // Xóa token đã dùng
            $conn->prepare("DELETE FROM password_resets WHERE email = ?")->execute([$user_email]);
            
            $success_msg = "Đặt lại mật khẩu thành công! Bạn có thể đăng nhập ngay.";
            $is_valid = false; // Ẩn form
            header("refresh:3;url=?page=login");
        } else {
            $error = "Đã xảy ra lỗi khi cập nhật mật khẩu.";
        }
    }
}
?>

<?php include 'layout/header.php'; ?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="auth-card">
                <div class="text-center mb-4">
                    <h2 class="fw-bold">🆕 Đặt lại mật khẩu</h2>
                    <p class="text-muted">Nhập mật khẩu mới cho tài khoản: <strong><?= htmlspecialchars($user_email) ?></strong></p>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-danger text-center border-0 shadow-sm"><?= $error ?></div>
                    <?php if (!$is_valid): ?>
                        <div class="text-center mt-3"><a href="?page=forgot_password" class="btn btn-outline-pink btn-sm">Yêu cầu mã mới</a></div>
                    <?php endif; ?>
                <?php endif; ?>

                <?php if ($success_msg): ?>
                    <div class="alert alert-success text-center border-0 shadow-sm">
                        <i class="fa-solid fa-circle-check me-2"></i><?= $success_msg ?>
                        <div class="mt-3 small">Đang chuyển hướng về trang đăng nhập...</div>
                    </div>
                <?php endif; ?>

                <?php if ($is_valid): ?>
                    <form method="POST">
                        <?= csrf_input() ?>
                        <div class="mb-4">
                            <label class="form-label small fw-bold">Mật khẩu mới</label>
                            <input type="password" name="new_password" class="form-control" placeholder="Tối thiểu 3 ký tự" required>
                        </div>

                        <div class="mb-4">
                            <label class="form-label small fw-bold">Xác nhận mật khẩu mới</label>
                            <input type="password" name="confirm_password" class="form-control" placeholder="Nhập lại mật khẩu mới" required>
                        </div>

                        <button type="submit" name="reset_password" class="btn btn-pink w-100">Cập nhật mật khẩu</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include 'layout/footer.php'; ?>

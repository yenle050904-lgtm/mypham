<?php
// ==================== XỬ LÝ QUÊN MẬT KHẨU ====================
$success_msg = '';
$error = '';
$debug_token = '';

if (isset($_POST['forgot_password'])) {
    $email = trim($_POST['email']);

    if (empty($email)) {
        $error = "Vui lòng nhập địa chỉ email!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Email không hợp lệ!";
    } else {
        // Kiểm tra email có trong hệ thống không
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            // Tạo token ngẫu nhiên
            $token = bin2hex(random_bytes(32));
            
            // Lưu vào bảng password_resets (Ghi đè nếu đã có yêu cầu trước đó)
            $stmt = $conn->prepare("INSERT INTO password_resets (email, token, created_at) 
                                    VALUES (?, ?, NOW()) 
                                    ON DUPLICATE KEY UPDATE token = VALUES(token), created_at = NOW()");
            if ($stmt->execute([$email, $token])) {
                $debug_token = $token;
                $success_msg = "Yêu cầu đã được ghi nhận!";
            } else {
                $error = "Có lỗi xảy ra, vui lòng thử lại sau.";
            }
        } else {
            // Để bảo mật, thường không báo "không tồn tại", nhưng đồ án thì có thể báo rõ
            $error = "Email này không tồn tại trong hệ thống!";
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
                    <h2 class="fw-bold">🔐 Quên mật khẩu?</h2>
                    <p class="text-muted">Nhập email của bạn để nhận mã đặt lại mật khẩu</p>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-danger text-center border-0 shadow-sm"><?= $error ?></div>
                <?php endif; ?>

                <?php if ($success_msg): ?>
                    <div class="alert alert-success border-0 shadow-sm">
                        <div class="fw-bold mb-2"><?= $success_msg ?></div>
                        <div class="small">Vì đây là bản demo, Token của bạn là:</div>
                        <div class="bg-white p-2 my-2 border rounded font-monospace text-break small">
                            <?= $debug_token ?>
                        </div>
                        <div class="mt-3">
                            <a href="?page=reset_password&token=<?= $debug_token ?>" class="btn btn-sm btn-success w-100">
                                <i class="fa-solid fa-arrow-right me-1"></i> Truy cập trang Đặt lại mật khẩu
                            </a>
                        </div>
                    </div>
                <?php endif; ?>

                <form method="POST" action="">
                    <?= csrf_input() ?>
                    <div class="mb-4">
                        <label class="form-label small fw-bold">Email tài khoản</label>
                        <input type="email" name="email" class="form-control" placeholder="example@gmail.com" required>
                    </div>

                    <button type="submit" name="forgot_password" class="btn btn-pink w-100 mb-3">Gửi yêu cầu reset</button>
                    
                    <div class="text-center">
                        <a href="?page=login" class="text-decoration-none small text-muted">← Quay lại đăng nhập</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'layout/footer.php'; ?>

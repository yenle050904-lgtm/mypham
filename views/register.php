<?php
// ==================== XỬ LÝ ĐĂNG KÝ TRƯỚC KHI XUẤT HTML ====================
if (isset($_POST['register'])) {
    // CSRF đã được config/security.php tự động kiểm tra cho mọi POST request
    
    $username = trim($_POST['username']);
    $email    = trim($_POST['email']);
    $password = $_POST['password'];
    
    if (empty($username) || empty($email) || empty($password)) {
        $error = "Vui lòng điền đầy đủ thông tin!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Email không hợp lệ!";
    } else {
        // Sử dụng Prepared Statements (PDO)
        $stmt = $conn->prepare("SELECT username, email FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        $existing = $stmt->fetch();
        
        if ($existing) {
            if ($existing['username'] === $username) {
                $error = "Tên đăng nhập đã tồn tại!";
            } else {
                $error = "Email đã được sử dụng!";
            }
        } else {
            $hashed_password = hash_password($password);
            $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
            if ($stmt->execute([$username, $email, $hashed_password])) {
                $success = "Đăng ký thành công! <a href='?page=login'>Đăng nhập ngay</a>";
                header("refresh:2;url=?page=login");
            } else {
                $error = "Có lỗi xảy ra, vui lòng thử lại!";
            }
        }
    }
}
?>

<?php include 'layout/header.php'; ?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="auth-card">
                <div class="text-center mb-4">
                    <h2>🌸 Đăng ký tài khoản</h2>
                </div>

                <?php if (isset($error)): ?>
                    <div class="alert alert-danger text-center"><?= $error ?></div>
                <?php endif; ?>
                <?php if (isset($success)): ?>
                    <div class="alert alert-success text-center"><?= $success ?></div>
                <?php endif; ?>

                <form method="POST">
                    <?= csrf_input() ?>
                    <input type="text" name="username" class="form-control mb-4" placeholder="Tên đăng nhập" required>
                    <input type="email" name="email" class="form-control mb-4" placeholder="Email" required>
                    <input type="password" name="password" class="form-control mb-4" placeholder="Mật khẩu" required>
                    <button type="submit" name="register" class="btn btn-pink w-100 mb-4">Đăng ký</button>
                </form>

                <div class="text-center mt-4">
                    <a href="?page=login">Đã có tài khoản? Đăng nhập</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'layout/footer.php'; ?>
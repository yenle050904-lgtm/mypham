<?php
// ==================== XỬ LÝ ĐĂNG NHẬP TRƯỚC KHI XUẤT HTML ====================
if (isset($_POST['login'])) {
    // CSRF đã được config/security.php tự động kiểm tra
    
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && verify_user_password($password, $user['password'], $username, $conn)) {
        $_SESSION['user'] = $user['username'];
        $_SESSION['role'] = $user['role'];

        // Trường hợp thêm sản phẩm từ trang chủ (chưa đăng nhập)
        if (isset($_GET['redirect']) && $_GET['redirect'] === 'add' && isset($_GET['product_id'])) {
            $pid = (int)$_GET['product_id'];
            if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
            $_SESSION['cart'][] = $pid;
            header("Location: ?page=cart");
            exit();
        } elseif (isset($_GET['redirect'])) {
            header("Location: ?page=" . htmlspecialchars($_GET['redirect']));
            exit();
        } else {
            header("Location: index.php");
            exit();
        }
    } else {
        $error = "Sai tài khoản hoặc mật khẩu!";
    }
}
?>

<?php include 'layout/header.php'; ?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="auth-card">
                <div class="text-center mb-4">
                    <h2>🌸 Đăng nhập</h2>
                    <p>Chào mừng bạn quay lại</p>
                </div>

                <?php if (isset($error)): ?>
                    <div class="alert alert-danger text-center">
                        <?= $error ?>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <?= csrf_input() ?>
                    <input type="text" 
                           name="username" 
                           class="form-control mb-4" 
                           placeholder="Tên đăng nhập" 
                           required>

                    <input type="password" 
                           name="password" 
                           class="form-control mb-4" 
                           placeholder="Mật khẩu" 
                           required>

                    <button type="submit" name="login" class="btn btn-pink w-100 mb-4">
                        Đăng nhập
                    </button>
                </form>

                <div class="text-center mt-4">
                    <a href="?page=register">Chưa có tài khoản? Đăng ký ngay</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'layout/footer.php'; ?>
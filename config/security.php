<?php
/**
 * Security Helpers for Shop Mỹ Phẩm
 */

// CSRF Protection
function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validate_csrf_token($token) {
    if (!isset($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
        return false;
    }
    return true;
}

function csrf_input() {
    $token = generate_csrf_token();
    return '<input type="hidden" name="csrf_token" value="' . $token . '">';
}

// Logic bảo vệ CSRF chung cho mọi POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Cho phép bỏ qua CSRF cho một số trường hợp cụ thể nếu cần (hiện tại áp dụng toàn bộ)
    if (!isset($_POST['csrf_token']) || !validate_csrf_token($_POST['csrf_token'])) {
        die("Lỗi: Xác thực CSRF thất bại. Vui lòng thử lại.");
    }
}

/**
 * Hashing Password mới
 */
function hash_password($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

/**
 * Kiểm tra Password (hỗ trợ nâng cấp từ MD5)
 */
function verify_user_password($password, $hashed_password, $username, $pdo) {
    // 1. Kiểm tra chuẩn password_hash
    if (password_verify($password, $hashed_password)) {
        return true;
    }
    
    // 2. Fallback: Kiểm tra MD5 cũ
    if ($hashed_password === md5($password)) {
        // Nâng cấp lên hash mới ngay lập tức
        $new_hash = hash_password($password);
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE username = ?");
        $stmt->execute([$new_hash, $username]);
        return true;
    }
    
    return false;
}
?>

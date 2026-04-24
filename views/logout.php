<?php
// ==================== LOGOUT ĐÃ SỬA ====================
session_start();

// Xóa toàn bộ session
$_SESSION = array();
session_destroy();

// Xóa cookie session (để sạch sẽ)
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, 
              $params["path"], 
              $params["domain"], 
              $params["secure"], 
              $params["httponly"]);
}

// Chuyển hướng về trang chủ (quan trọng nhất)
header("Location: index.php");
exit();
?>
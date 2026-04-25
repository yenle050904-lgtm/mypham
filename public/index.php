<?php
require '../config/database.php';

$page = $_GET['page'] ?? 'home';
$page = trim($page, '/');

switch($page){
    case 'login': 
        include '../views/login.php'; 
        break;
    case 'register': 
        include '../views/register.php'; 
        break;
    case 'forgot_password':
        include '../views/forgot_password.php';
        break;
    case 'reset_password':
        include '../views/reset_password.php';
        break;
    case 'cart': 
        include '../views/cart.php'; 
        break;
    case 'admin': 
        include '../views/admin.php'; 
        break;
    case 'admin_orders': 
        include '../views/admin_orders.php'; 
        break;
    case 'order_detail': 
        include '../views/order_detail.php'; 
        break;
    case 'product_detail': 
        include '../views/product_detail.php'; 
        break;
    case 'admin_report': 
        include '../views/admin_report.php'; 
        break;
    case 'admin_categories': 
        include '../views/admin_categories.php'; 
        break;
    case 'admin_users': 
        include '../views/admin_users.php'; 
        break;
    case 'admin_coupons': 
        include '../views/admin_coupons.php'; 
        break;
    case 'export_orders': 
        include '../views/export_orders.php'; 
        exit(); 
        break;
    case 'profile': 
        include '../views/profile.php'; 
        break;
    case 'gioi_thieu': 
        include '../views/gioi_thieu.php';     // ← MỚI THÊM
        break;
    case 'contact':
        include '../views/contact.php';
        break;
    case 'chinh_sach':
        include '../views/chinh_sach.php';
        break;
    case 'wishlist':
        include '../views/wishlist.php';
        break;
    case 'logout':
        include '../views/logout.php'; 
        break;
    case 'checkout': 
        include '../views/checkout.php'; 
        break;
    case 'order_success': 
        include '../views/order_success.php'; 
        break;
    case 'my_orders': 
        include '../views/my_orders.php'; 
        break;
    case '': 
    case 'home': 
        include '../views/home.php'; 
        break;
    default: 
        http_response_code(404);
        include '../views/404.php'; 
        break;
}
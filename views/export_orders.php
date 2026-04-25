<?php
// Kiểm tra quyền admin
if (($_SESSION['role'] ?? '') !== 'admin') {
    die('Bạn không có quyền truy cập!');
}

// Lấy danh sách đơn hàng
$stmt = $conn->query("SELECT * FROM orders ORDER BY id DESC");
$orders = $stmt->fetchAll();

// Tên file
$filename = "don_hang_" . date('YmdHis') . ".csv";

// Header để trình duyệt tải file
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

// Ghi file ra output
$output = fopen('php://output', 'w');

// Thêm BOM cho UTF-8 để Excel hiển thị đúng tiếng Việt
fwrite($output, "\xEF\xBB\xBF");

// Tiêu đề cột
fputcsv($output, [
    'ID', 
    'Mã đơn hàng', 
    'Họ tên khách hàng', 
    'Số điện thoại', 
    'Địa chỉ', 
    'Tổng tiền', 
    'Trạng thái', 
    'Ngày đặt', 
    'Phương thức thanh toán'
]);

// Dữ liệu
foreach ($orders as $o) {
    fputcsv($output, [
        $o['id'],
        $o['order_code'],
        $o['fullname'],
        $o['phone'],
        $o['address'],
        $o['total'], // Để số nguyên thuần cho CSV
        $o['status'],
        $o['created_at'],
        ($o['payment_method'] ?? 'cod') == 'cod' ? 'Tiền mặt' : 'Chuyển khoản'
    ]);
}

fclose($output);
exit();
?>

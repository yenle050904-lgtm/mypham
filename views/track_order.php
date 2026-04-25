<?php
$order = null;
$items = [];
$error = '';
$order_code_search = trim($_GET['order_code'] ?? '');

if ($order_code_search) {
    // 1. Query thông tin đơn hàng
    $stmt = $conn->prepare("SELECT * FROM orders WHERE order_code = ?");
    $stmt->execute([$order_code_search]);
    $order = $stmt->fetch();

    if ($order) {
        // 2. Query danh sách sản phẩm (Lấy từ order_items để giữ giá lịch sử)
        $stmt_items = $conn->prepare("SELECT * FROM order_items WHERE order_id = ?");
        $stmt_items->execute([$order['id']]);
        $items = $stmt_items->fetchAll();
    } else {
        $error = "Không tìm thấy đơn hàng với mã: <strong>" . htmlspecialchars($order_code_search) . "</strong>";
    }
}

include 'layout/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="text-center mb-5">
                <h1 class="fw-bold font-elegant text-pink">🔍 Tra cứu đơn hàng</h1>
                <p class="text-muted">Nhập mã đơn hàng của bạn để theo dõi hành trình món quà tâm hồn</p>
            </div>

            <!-- Form Tra Cứu -->
            <div class="card border-0 shadow-sm rounded-4 mb-5">
                <div class="card-body p-4">
                    <form method="GET" class="row g-3 align-items-center">
                        <input type="hidden" name="page" value="track_order">
                        <div class="col-md-9">
                            <div class="input-group input-group-lg">
                                <span class="input-group-text bg-white border-end-0 rounded-start-pill ps-4 text-pink">
                                    <i class="fa-solid fa-barcode"></i>
                                </span>
                                <input type="text" name="order_code" class="form-control border-start-0 rounded-end-pill py-3 px-4 shadow-none" 
                                       placeholder="Ví dụ: ORD12345678" value="<?= htmlspecialchars($order_code_search) ?>" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-pink w-100 py-3 rounded-pill fs-5 shadow">Tra cứu ngay</button>
                        </div>
                    </form>
                </div>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-warning text-center rounded-4 shadow-sm py-4">
                    <i class="fa-solid fa-circle-exclamation fs-3 mb-3 d-block"></i>
                    <?= $error ?>
                </div>
            <?php endif; ?>

            <?php if ($order): ?>
                <!-- Kết quả: Timeline -->
                <?php 
                $statuses = ['Đang xử lý', 'Đã duyệt', 'Đang giao', 'Hoàn thành'];
                $current_idx = array_search($order['status'], $statuses);
                if ($order['status'] === 'Đã hủy') {
                    $statuses = ['Đang xử lý', 'Đã hủy'];
                    $current_idx = 1;
                }
                ?>
                <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden">
                    <div class="card-header bg-pink text-white py-3">
                        <h5 class="mb-0 fw-bold"><i class="fa-solid fa-truck-fast me-2"></i>Hành trình đơn hàng: <?= $order['order_code'] ?></h5>
                    </div>
                    <div class="card-body py-5">
                        <div class="tracking-timeline d-flex justify-content-between position-relative">
                            <div class="timeline-line position-absolute w-100 bg-light" style="height: 4px; top: 20px; z-index: 1;"></div>
                            <div class="timeline-line-active position-absolute bg-pink" style="height: 4px; top: 20px; z-index: 2; width: <?= ($current_idx / (count($statuses)-1)) * 100 ?>%; transition: width 1s ease;"></div>
                            
                            <?php foreach($statuses as $index => $status): ?>
                                <div class="timeline-item text-center position-relative" style="z-index: 3; width: 80px;">
                                    <div class="timeline-icon rounded-circle mx-auto d-flex align-items-center justify-content-center shadow-sm <?= $index <= $current_idx ? 'bg-pink text-white' : 'bg-white text-muted border' ?>" style="width: 45px; height: 45px;">
                                        <?php if ($status == 'Đang xử lý') echo '<i class="fa-solid fa-receipt"></i>';
                                              elseif ($status == 'Đã duyệt') echo '<i class="fa-solid fa-check-double"></i>';
                                              elseif ($status == 'Đang giao') echo '<i class="fa-solid fa-shipping-fast"></i>';
                                              elseif ($status == 'Hoàn thành') echo '<i class="fa-solid fa-box-open"></i>';
                                              elseif ($status == 'Đã hủy') echo '<i class="fa-solid fa-xmark"></i>';
                                        ?>
                                    </div>
                                    <div class="mt-2 small fw-bold <?= $index <= $current_idx ? 'text-pink' : 'text-muted' ?>"><?= $status ?></div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <div class="row g-4">
                    <div class="col-md-5">
                        <div class="card border-0 shadow-sm rounded-4 h-100">
                            <div class="card-body">
                                <h6 class="fw-bold mb-4 border-start border-pink border-4 ps-2">Thông tin người nhận</h6>
                                <p class="mb-2"><strong>Người nhận:</strong> <?= htmlspecialchars($order['fullname']) ?></p>
                                <p class="mb-2"><strong>Địa chỉ:</strong> <?= htmlspecialchars($order['address']) ?></p>
                                <p class="mb-2"><strong>Ngày đặt:</strong> <?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></p>
                                <p class="mb-0"><strong>Thanh toán:</strong> <?= $order['payment_method'] == 'cod' ? 'Tiền mặt (COD)' : 'Chuyển khoản' ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-7">
                        <div class="card border-0 shadow-sm rounded-4 h-100">
                            <div class="card-body">
                                <h6 class="fw-bold mb-4 border-start border-pink border-4 ps-2">Chi tiết sản phẩm</h6>
                                <div class="table-responsive">
                                    <table class="table table-borderless align-middle mb-0">
                                        <tbody>
                                            <?php foreach($items as $item): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($item['product_name']) ?></td>
                                                    <td class="text-muted small">x<?= $item['quantity'] ?></td>
                                                    <td class="text-end fw-bold"><?= number_format($item['price'] * $item['quantity']) ?> đ</td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                        <tfoot class="border-top">
                                            <tr>
                                                <td colspan="2" class="fw-bold pt-3">TỔNG CỘNG</td>
                                                <td class="text-end fw-bold fs-5 text-pink pt-3"><?= number_format($order['total']) ?> đ</td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'layout/footer.php'; ?>

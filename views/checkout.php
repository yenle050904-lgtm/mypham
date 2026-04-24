<?php
// ==================== XỬ LÝ ĐẶT HÀNG TRƯỚC KHI XUẤT HTML ====================
if (isset($_POST['confirm_order'])) {
    // CSRF tự động kiểm tra
    if (empty($_SESSION['cart'])) {
        header("Location: ?page=cart");
        exit();
    }

    $fullname = trim($_POST['fullname']);
    $phone    = trim($_POST['phone']);
    $address  = trim($_POST['address']);

    if (empty($fullname) || empty($phone) || empty($address)) {
        $error = "Vui lòng điền đầy đủ thông tin!";
    } else {
        // Kiểm tra lại toàn bộ giỏ hàng trước khi cho phép đặt
        $total = 0;
        $cart_items = array_count_values($_SESSION['cart'] ?? []);
        $invalid_found = false;
        
        foreach ($cart_items as $id => $qty) {
            $stmt = $conn->prepare("SELECT price, status FROM products WHERE id = ?");
            $stmt->execute([$id]);
            $row = $stmt->fetch();
            
            if (!$row || $row['status'] != 'active') {
                $invalid_found = true;
                break;
            }
            $total += $row['price'] * $qty;
        }

        if ($invalid_found) {
            $error = "Có sản phẩm trong giỏ hàng đã hết hàng hoặc không hợp lệ. Vui lòng quay lại giỏ hàng!";
        } else {
            $_SESSION['last_order'] = [
                'fullname' => $fullname,
                'phone'    => $phone,
                'address'  => $address,
                'total'    => $total,
                'payment_method' => $_POST['payment_method'] ?? 'cod',
                'cart'     => $cart_items // Lưu giỏ vào orders (logic checkout.php của bạn cần cái này)
            ];

            header("Location: ?page=order_success");
            exit();
        }
    }
}

// Yêu cầu đăng nhập để thanh toán (Đảm bảo đồng bộ với các trang cá nhân)
if (!isset($_SESSION['user'])) {
    header("Location: ?page=login&redirect=checkout");
    exit();
}

// Kiểm tra giỏ hàng hợp lệ lúc vào trang
if (empty($_SESSION['cart'])) {
    header("Location: ?page=cart");
    exit();
}

// Lấy thông tin profile để tự động điền (Pre-fill)
$stmt_u = $conn->prepare("SELECT fullname, phone, address, username FROM users WHERE username = ?");
$stmt_u->execute([$_SESSION['user']]);
$u_info = $stmt_u->fetch();

$def_fullname = ($u_info['fullname'] && trim($u_info['fullname']) != '') ? $u_info['fullname'] : $u_info['username'];
$def_phone    = $u_info['phone'] ?? '';
$def_address  = $u_info['address'] ?? '';
?>

<?php include 'layout/header.php'; ?>

<style>
    .cursor-pointer { cursor: pointer; }
    .payment-option { transition: all 0.2s; border: 2px solid #eee !important; position: relative; }
    .payment-option:hover { border-color: #ffb8d1 !important; }
    .payment-option.active { border-color: #ff69b4 !important; background-color: #fff9fb; }
    .payment-option input:checked + .d-flex { color: #ff69b4; }
    .icon-circle { width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; }
</style>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <h2 class="fw-bold mb-5 text-center">💳 Hoàn tất đặt hàng</h2>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-danger text-center shadow-sm border-0 mb-4"><?= $error ?></div>
            <?php endif; ?>

            <div class="row g-4">
                <!-- Thông tin người nhận -->
                <div class="col-lg-7">
                    <div class="card shadow-sm border-0 p-4">
                        <h5 class="fw-bold mb-4"><i class="fa-solid fa-map-location-dot me-2 text-pink"></i>Thông tin vận chuyển</h5>
                        <form method="POST">
                            <?= csrf_input() ?>
                            <div class="mb-4">
                                <label class="form-label fw-medium">Họ và tên người nhận</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0"><i class="fa-solid fa-user text-muted"></i></span>
                                    <input type="text" name="fullname" class="form-control border-start-0" 
                                           value="<?= htmlspecialchars($def_fullname) ?>" required>
                                </div>
                            </div>
                            <div class="mb-4">
                                <label class="form-label fw-medium">Số điện thoại liên lạc</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0"><i class="fa-solid fa-phone text-muted"></i></span>
                                    <input type="tel" name="phone" class="form-control border-start-0" 
                                           placeholder="Số điện thoại" value="<?= htmlspecialchars($def_phone) ?>" required>
                                </div>
                            </div>
                            <div class="mb-4">
                                <label class="form-label fw-medium">Địa chỉ giao hàng</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0 align-items-start pt-2"><i class="fa-solid fa-location-arrow text-muted"></i></span>
                                    <textarea name="address" class="form-control border-start-0" rows="3" 
                                              placeholder="Số nhà, đường, phường, quận, tỉnh..." required><?= htmlspecialchars($def_address) ?></textarea>
                                </div>
                                <div class="form-text small text-muted">Vui lòng nhập chính xác để chúng tôi giao hàng nhanh nhất.</div>
                            </div>

                            <h5 class="fw-bold mb-4 mt-5"><i class="fa-solid fa-credit-card me-2 text-pink"></i>Phương thức thanh toán</h5>
                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label class="payment-option active w-100 p-3 border rounded-3 cursor-pointer d-block" id="label-cod">
                                        <input type="radio" name="payment_method" value="cod" class="d-none" checked onclick="toggleBank(false, this)">
                                        <div class="d-flex align-items-center">
                                            <div class="icon-circle bg-success text-white me-3"><i class="fa-solid fa-money-bill-wave"></i></div>
                                            <div>
                                                <div class="fw-bold">Thanh toán khi nhận hàng</div>
                                                <div class="small text-muted">Trả tiền mặt khi nhận hàng (COD)</div>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                                <div class="col-md-6">
                                    <label class="payment-option w-100 p-3 border rounded-3 cursor-pointer d-block" id="label-bank">
                                        <input type="radio" name="payment_method" value="bank" class="d-none" onclick="toggleBank(true, this)">
                                        <div class="d-flex align-items-center">
                                            <div class="icon-circle bg-primary text-white me-3"><i class="fa-solid fa-building-columns"></i></div>
                                            <div>
                                                <div class="fw-bold">Chuyển khoản ngân hàng</div>
                                                <div class="small text-muted">Chuyển khoản qua số tài khoản</div>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <div id="bank-info" class="alert alert-info border-0 shadow-sm mb-4 d-none">
                                <h6 class="fw-bold"><i class="fa-solid fa-info-circle me-2"></i>Thông tin chuyển khoản:</h6>
                                <p class="mb-1 small">Ngân hàng: <strong>Vietcombank</strong></p>
                                <p class="mb-1 small">Số TK: <strong>1234567890</strong></p>
                                <p class="mb-1 small">Chủ TK: <strong>SHOP MY PHAM XINH</strong></p>
                                <p class="mb-0 small">Nội dung CK: <span class="text-danger fw-bold">[Mã đơn hàng]</span> (Sẽ hiển thị sau khi đặt xong)</p>
                            </div>

                            <button type="submit" name="confirm_order" class="btn btn-pink w-100 py-3 fs-5 mt-2 shadow-sm">
                                <i class="fa-solid fa-check-circle me-2"></i> Xác nhận & Đặt hàng
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Tóm tắt đơn hàng -->
                <div class="col-lg-5">
                    <div class="card shadow-sm border-0 bg-light p-4 sticky-top" style="top:100px;">
                        <h5 class="fw-bold mb-4"><i class="fa-solid fa-bag-shopping me-2 text-pink"></i>Tóm tắt đơn hàng</h5>
                        <div class="checkout-items mb-4 custom-scrollbar" style="max-height: 300px; overflow-y: auto;">
                            <?php 
                            $total = 0;
                            $cart_items = array_count_values($_SESSION['cart'] ?? []);
                            foreach($cart_items as $id => $qty): 
                                $stmt = $conn->prepare("SELECT * FROM products WHERE id=?");
                                $stmt->execute([$id]);
                                $p = $stmt->fetch();
                                if(!$p) continue;
                                $subtotal = $p['price'] * $qty;
                                $total += $subtotal;
                            ?>
                            <div class="d-flex align-items-center mb-3">
                                <div class="position-relative">
                                    <?php 
                                    $img_src = (file_exists('uploads/' . $p['image']) && !empty($p['image'])) ? 'uploads/' . $p['image'] : 'images/' . $p['image'];
                                    ?>
                                    <img src="<?= $img_src ?>" class="rounded border" style="width:50px;height:50px;object-fit:cover;">
                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-secondary" style="font-size: 10px;">
                                        <?= $qty ?>
                                    </span>
                                </div>
                                <div class="ms-3 flex-grow-1">
                                    <div class="small fw-bold text-truncate" style="max-width: 150px;"><?= htmlspecialchars($p['name']) ?></div>
                                    <div class="small text-muted"><?= number_format($p['price']) ?> đ</div>
                                </div>
                                <div class="ms-2 fw-bold small"><?= number_format($subtotal) ?> đ</div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <div class="border-top pt-3 mt-3">
                            <div class="d-flex justify-content-between mb-2 text-muted">
                                <span>Tạm tính</span>
                                <span><?= number_format($total) ?> đ</span>
                            </div>
                            <div class="d-flex justify-content-between mb-3 text-muted">
                                <span>Phí giao hàng</span>
                                <span class="text-success small">Miễn phí</span>
                            </div>
                            <div class="d-flex justify-content-between fs-4 pt-3 border-top">
                                <span class="fw-bold">Tổng tiền</span>
                                <span class="fw-bold text-pink"><?= number_format($total) ?> đ</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'layout/footer.php'; ?>

<script>
function toggleBank(show, input) {
    const bankInfo = document.getElementById('bank-info');
    if (show) {
        bankInfo.classList.remove('d-none');
    } else {
        bankInfo.classList.add('d-none');
    }
    
    // Toggle class active cho label
    document.querySelectorAll('.payment-option').forEach(el => el.classList.remove('active'));
    input.closest('.payment-option').classList.add('active');
}
</script>
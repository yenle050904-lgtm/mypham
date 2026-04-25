<?php 
// ==================== XỬ LÝ TRƯỚC KHI XUẤT HTML ====================
// Xử lý thêm, cập nhật số lượng, xóa
if (isset($_GET['add'])) {
    $id = (int)$_GET['add'];
    
    // Kiểm tra hàng còn không và stock > 0 mới cho thêm
    $stmt = $conn->prepare("SELECT status, stock FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $p = $stmt->fetch();
    
    // Đếm số lượng sản phẩm này hiện có trong giỏ
    $current_qty = 0;
    if (isset($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $item_id) {
            if ($item_id == $id) $current_qty++;
        }
    }

    if ($p && $p['status'] == 'active' && $p['stock'] > $current_qty) {
        if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
        $_SESSION['cart'][] = $id;
        $_SESSION['flash_msg'] = "Đã thêm sản phẩm vào giỏ hàng!";
        header("Location: ?page=cart");
        exit();
    } else {
        $msg = ($p && $p['stock'] <= $current_qty) ? 'Sản phẩm này đã đạt giới hạn tồn kho!' : 'Sản phẩm này đã hết hàng hoặc không còn kinh doanh!';
        echo "<script>alert('$msg'); window.location='?page=home';</script>";
        exit();
    }
}

if (isset($_GET['remove'])) {
    $id = (int)$_GET['remove'];
    $_SESSION['cart'] = array_values(array_diff($_SESSION['cart'] ?? [], [$id]));
    $_SESSION['flash_msg'] = "Đã xóa sản phẩm khỏi giỏ hàng!";
}

if (isset($_POST['update_cart'])) {
    // CSRF tự động kiểm tra
    $_SESSION['cart'] = [];
    foreach ($_POST['qty'] as $id => $qty) {
        $qty = (int)$qty;
        if ($qty <= 0) continue; // Ngăn chặn số lượng <= 0

        // Kiểm tra lại trạng thái sản phẩm và tồn kho
        $stmt = $conn->prepare("SELECT status, stock FROM products WHERE id = ?");
        $stmt->execute([$id]);
        $p = $stmt->fetch();
        
        if ($p && $p['status'] == 'active') {
            $final_qty = min($qty, $p['stock']);
            for ($i = 0; $i < $final_qty; $i++) {
                $_SESSION['cart'][] = (int)$id;
            }
        }
    }
    $_SESSION['flash_msg'] = "Cập nhật giỏ hàng thành công!";
}

$cart_items = array_count_values($_SESSION['cart'] ?? []);

// --- XỬ LÝ MÃ GIẢM GIÁ (COUPON) ---
$coupon_error = '';
$coupon_success = '';

// Tính tạm tính trước để kiểm tra min_order
$subtotal_before_coupon = 0;
foreach ($cart_items as $id => $qty) {
    $stmt_p = $conn->prepare("SELECT price, status, stock FROM products WHERE id = ?");
    $stmt_p->execute([$id]);
    $p = $stmt_p->fetch();
    if ($p && $p['status'] == 'active' && $p['stock'] >= $qty) {
        $subtotal_before_coupon += $p['price'] * $qty;
    }
}

if (isset($_POST['apply_coupon'])) {
    $code = trim($_POST['coupon_code']);
    $stmt = $conn->prepare("SELECT * FROM coupons WHERE code = ? AND is_active = 1");
    $stmt->execute([$code]);
    $coupon = $stmt->fetch();

    if (!$coupon) {
        $coupon_error = "Mã giảm giá không hợp lệ hoặc đã hết hạn!";
        unset($_SESSION['coupon']);
    } elseif ($coupon['used_count'] >= $coupon['max_uses'] && $coupon['max_uses'] > 0) {
        $coupon_error = "Mã giảm giá này đã được sử dụng hết lượt!";
        unset($_SESSION['coupon']);
    } elseif ($subtotal_before_coupon < $coupon['min_order']) {
        $coupon_error = "Đơn hàng tối thiểu " . number_format($coupon['min_order']) . " đ để áp dụng mã này!";
        unset($_SESSION['coupon']);
    } else {
        $_SESSION['coupon'] = $coupon;
        $coupon_success = "Áp dụng mã giảm giá thành công!";
    }
}

if (isset($_POST['remove_coupon'])) {
    unset($_SESSION['coupon']);
    $coupon_success = "Đã xóa mã giảm giá.";
}

// Kiểm tra lại điều kiện min_order nếu giỏ hàng thay đổi
if (isset($_SESSION['coupon'])) {
    if ($subtotal_before_coupon < $_SESSION['coupon']['min_order']) {
        unset($_SESSION['coupon']);
        $coupon_error = "Giỏ hàng thay đổi, mã giảm giá không còn hiệu lực!";
    }
}

include 'layout/header.php'; 
?>

<div class="container py-4">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="?page=home" class="text-pink text-decoration-none">Trang chủ</a></li>
            <li class="breadcrumb-item active" aria-current="page">Giỏ hàng</li>
        </ol>
    </nav>

    <div class="d-flex align-items-center justify-content-between mb-5">
        <h2 class="fw-bold mb-0">🛒 Giỏ hàng của bạn</h2>
        <span class="text-muted"><?= count($cart_items) ?> Sản phẩm</span>
    </div>

    <?php if (empty($_SESSION['cart'])): ?>
        <div class="text-center py-5">
            <div class="mb-4"><i class="fa-solid fa-cart-shopping fs-1 text-muted opacity-25"></i></div>
            <h4 class="text-muted">Giỏ hàng của bạn đang trống</h4>
            <p class="mb-4">Hãy tiếp tục khám phá và thêm những sản phẩm yêu thích vào giỏ hàng nhé!</p>
            <a href="index.php" class="btn btn-pink px-5 py-3">Tiếp tục mua sắm</a>
        </div>
    <?php else: ?>
        <form method="POST">
            <?= csrf_input() ?>
            <div class="row g-4">
                <div class="col-lg-8">
                    <?php 
                    $total = 0;
                    $has_invalid_product = false;
                    foreach ($cart_items as $id => $qty):
                        $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
                        $stmt->execute([$id]);
                        $p = $stmt->fetch();
                        if (!$p) continue;
                        
                        $is_out_of_stock = ($p['status'] != 'active' || $p['stock'] <= 0);
                        $is_over_stock = ($qty > $p['stock']);
                        if($is_out_of_stock || $is_over_stock) $has_invalid_product = true;

                        $effective_price = $p['sale_price'] ?? $p['price'];
                        $subtotal = $effective_price * $qty;
                        $total += $subtotal;
                    ?>
                    <div class="card mb-3 shadow-sm border-0 <?= $is_out_of_stock ? 'opacity-50' : '' ?>">
                        <div class="card-body p-3">
                            <div class="row align-items-center">
                                <div class="col-auto">
                                    <?php 
                                    $img_src = (file_exists('uploads/' . $p['image']) && !empty($p['image'])) ? 'uploads/' . $p['image'] : 'images/' . $p['image'];
                                    ?>
                                    <img src="<?= $img_src ?>" style="width:100px;height:100px;object-fit:cover;border-radius:16px;">
                                </div>
                                <div class="col">
                                    <h5 class="fw-bold mb-1"><?= htmlspecialchars($p['name']) ?></h5>
                                    <?php if($p['sale_price']): ?>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="text-pink fw-bold"><?= number_format($p['sale_price']) ?> đ</span>
                                            <span class="text-muted text-decoration-line-through small"><?= number_format($p['price']) ?> đ</span>
                                        </div>
                                    <?php else: ?>
                                        <p class="text-pink fw-bold mb-0"><?= number_format($p['price']) ?> đ</p>
                                    <?php endif; ?>
                                    <?php if($is_out_of_stock): ?>
                                        <div class="text-danger small fw-bold"><i class="fa-solid fa-triangle-exclamation me-1"></i>Sản phẩm tạm hết hàng - Vui lòng xóa</div>
                                    <?php elseif($is_over_stock): ?>
                                        <div class="text-warning small fw-bold"><i class="fa-solid fa-circle-exclamation me-1"></i>Vượt quá tồn kho (Hiện có: <?= $p['stock'] ?>)</div>
                                    <?php endif; ?>
                                </div>
                                <div class="col-auto d-flex align-items-center gap-2">
                                    <div class="input-group input-group-sm" style="width:120px;">
                                        <span class="input-group-text py-2 bg-light border-0">Sl:</span>
                                        <input type="number" name="qty[<?= $id ?>]" value="<?= $qty ?>" min="1" class="form-control text-center border-0 bg-light">
                                    </div>
                                    <a href="?page=cart&remove=<?= $id ?>" class="btn btn-light btn-sm text-danger hover-delete" title="Xóa">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </a>
                                </div>
                                <div class="col-auto text-end" style="min-width: 120px;">
                                    <div class="small text-muted mb-1">Thành tiền</div>
                                    <div class="fw-bold fs-5 <?= $is_out_of_stock ? 'text-muted' : 'text-dark' ?>"><?= number_format($subtotal) ?> đ</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    
                    <div class="d-flex justify-content-between mt-4">
                        <a href="index.php" class="btn btn-light"><i class="fa-solid fa-arrow-left me-2"></i>Tiếp tục mua hàng</a>
                        <button type="submit" name="update_cart" class="btn btn-outline-secondary">
                             <i class="fa-solid fa-rotate me-2"></i>Cập nhật giỏ hàng
                        </button>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm p-4 sticky-top" style="top:100px;">
                        <h4 class="fw-bold mb-4">Tóm tắt đơn hàng</h4>
                        
                        <div class="d-flex justify-content-between mb-3 text-muted">
                            <span>Tạm tính</span>
                            <span><?= number_format($total) ?> đ</span>
                        </div>

                        <?php 
                        $discount = 0;
                        if (isset($_SESSION['coupon'])): 
                            $cp = $_SESSION['coupon'];
                            if ($cp['discount_type'] == 'percent') {
                                $discount = ($total * $cp['discount_value']) / 100;
                            } else {
                                $discount = $cp['discount_value'];
                            }
                        ?>
                        <div class="d-flex justify-content-between mb-3 text-success fw-bold">
                            <span>Giảm giá (<?= htmlspecialchars($cp['code']) ?>)</span>
                            <span>-<?= number_format($discount) ?> đ</span>
                        </div>
                        <?php endif; ?>

                        <div class="d-flex justify-content-between mb-3 text-muted">
                            <span>Phí vận chuyển</span>
                            <span class="text-success">Miễn phí</span>
                        </div>
                        <hr class="my-4">
                        <div class="d-flex justify-content-between mb-4">
                            <span class="fw-bold">Tổng cộng</span>
                            <span class="fw-bold fs-3 text-pink"><?= number_format($total - $discount) ?> đ</span>
                        </div>

                        <!-- Mã giảm giá UI -->
                        <div class="mb-4">
                            <label class="form-label small fw-bold text-muted">Mã giảm giá</label>
                            <?php if (isset($_SESSION['coupon'])): ?>
                                <div class="d-flex align-items-center justify-content-between bg-light p-2 rounded-3 border">
                                    <span class="text-pink fw-bold ms-2"><?= htmlspecialchars($_SESSION['coupon']['code']) ?></span>
                                    <button type="submit" name="remove_coupon" class="btn btn-sm btn-light text-danger border-0">
                                        <i class="fa-solid fa-xmark"></i> Xóa
                                    </button>
                                </div>
                            <?php else: ?>
                                <div class="input-group">
                                    <input type="text" name="coupon_code" class="form-control border-0 bg-light" placeholder="Nhập mã...">
                                    <button type="submit" name="apply_coupon" class="btn btn-dark btn-sm px-3">Áp dụng</button>
                                </div>
                            <?php endif; ?>
                            <?php if ($coupon_error): ?><div class="text-danger small mt-2 fs-7"><i class="fa-solid fa-circle-exclamation me-1"></i><?= $coupon_error ?></div><?php endif; ?>
                            <?php if ($coupon_success): ?><div class="text-success small mt-2 fs-7"><i class="fa-solid fa-circle-check me-1"></i><?= $coupon_success ?></div><?php endif; ?>
                        </div>
                        
                        <?php if($has_invalid_product): ?>
                            <div class="alert alert-warning small mb-3">
                                <i class="fa-solid fa-circle-info me-2"></i>Có sản phẩm không hợp lệ trong giỏ hàng. Vui lòng kiểm tra lại.
                            </div>
                            <button class="btn btn-secondary w-100 py-3 mb-3 fs-5" disabled>
                                Chưa thể thanh toán
                            </button>
                        <?php else: ?>
                            <a href="?page=checkout" class="btn btn-pink w-100 py-3 mb-3 fs-5 shadow-sm">
                                Tiến hành thanh toán <i class="fa-solid fa-arrow-right ms-2"></i>
                            </a>
                        <?php endif; ?>
                        
                        <div class="text-center">
                            <small class="text-muted"><i class="fa-solid fa-shield-halved me-1"></i> Thanh toán an toàn & bảo mật</small>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    <?php endif; ?>
</div>

<?php include 'layout/footer.php'; ?>
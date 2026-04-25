<?php
// ==================== XỬ LÝ TRƯỚC KHI XUẤT HTML ====================
if (!isset($_SESSION['user'])) {
    header("Location: ?page=login");
    exit();
}

$username = $_SESSION['user'];
$success = '';
$error = '';

// Lấy user_id
$stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
$stmt->execute([$username]);
$user_id = $stmt->fetch()['id'];

// Xử lý Thêm vào Wishlist
if (isset($_GET['add'])) {
    $product_id = (int)$_GET['add'];
    try {
        $stmt = $conn->prepare("INSERT INTO wishlist (user_id, product_id) VALUES (?, ?)");
        $stmt->execute([$user_id, $product_id]);
        $_SESSION['flash_msg'] = "Đã thêm vào danh sách yêu thích!";
        unset($_SESSION['wishlist_count']); // Để header query lại
    } catch (PDOException $e) {
        $_SESSION['flash_msg'] = "Sản phẩm đã có trong danh sách yêu thích.";
    }
}

// Xử lý Xóa
if (isset($_GET['remove'])) {
    $product_id = (int)$_GET['remove'];
    $stmt = $conn->prepare("DELETE FROM wishlist WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$user_id, $product_id]);
    $_SESSION['flash_msg'] = "Đã xóa khỏi danh sách yêu thích.";
    unset($_SESSION['wishlist_count']); // Để header query lại
}

// Lấy danh sách Wishlist
$stmt = $conn->prepare("SELECT p.* FROM products p 
                        JOIN wishlist w ON p.id = w.product_id 
                        WHERE w.user_id = ? AND p.status != 'hidden'");
$stmt->execute([$user_id]);
$wishlist = $stmt->fetchAll();
?>

<?php include 'layout/header.php'; ?>

<div class="container py-5">
    <h2 class="text-center mb-5 text-pink fw-bold"><i class="fa-solid fa-heart me-2"></i>Sản phẩm yêu thích</h2>

    <?php if (empty($wishlist)): ?>
        <div class="text-center py-5">
            <div class="mb-4 text-muted"><i class="fa-regular fa-heart fs-1 opacity-25"></i></div>
            <h4 class="text-muted">Danh sách yêu thích của bạn trống.</h4>
            <p>Hãy thả tim cho những sản phẩm bạn thích để theo dõi dễ dàng hơn nhé!</p>
            <a href="?page=home" class="btn btn-pink mt-3 px-4 shadow-sm">Khám phá sản phẩm</a>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($wishlist as $p): ?>
            <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                <div class="card product-card h-100">
                    <div class="position-relative overflow-hidden">
                        <a href="?page=product_detail&id=<?= $p['id'] ?>">
                            <?php 
                            $img_src = (file_exists('uploads/' . $p['image']) && !empty($p['image'])) ? 'uploads/' . $p['image'] : 'images/' . $p['image'];
                            ?>
                            <img src="<?= $img_src ?>" class="card-img-top" alt="<?= htmlspecialchars($p['name']) ?>">
                        </a>
                    </div>
                    <div class="card-body text-center d-flex flex-column">
                        <h6 class="text-truncate mb-2"><?= htmlspecialchars($p['name']) ?></h6>
                        <div class="mb-3">
                            <?php if($p['sale_price']): ?>
                                <span class="text-pink fw-bold d-block"><?= number_format($p['sale_price']) ?> đ</span>
                                <span class="text-muted text-decoration-line-through small"><?= number_format($p['price']) ?> đ</span>
                            <?php else: ?>
                                <span class="text-pink fw-bold d-block"><?= number_format($p['price']) ?> đ</span>
                                <span class="small opacity-0">&nbsp;</span>
                            <?php endif; ?>
                        </div>
                        <div class="mt-auto">
                            <?php if ($p['status'] == 'active' && $p['stock'] > 0): ?>
                                <a href="?page=cart&add=<?= $p['id'] ?>" class="btn btn-pink btn-sm w-100 mb-2">Thêm vào giỏ</a>
                            <?php else: ?>
                                <button class="btn btn-secondary btn-sm w-100 mb-2" disabled>Hết hàng</button>
                            <?php endif; ?>

                            <div class="d-flex gap-1">
                                <a href="?page=product_detail&id=<?= $p['id'] ?>" class="btn btn-outline-pink btn-sm flex-grow-1">Xem chi tiết</a>
                                <a href="?page=wishlist&remove=<?= $p['id'] ?>" class="btn btn-light btn-sm text-danger" title="Xóa" onclick="return confirm('Xóa khỏi yêu thích?')">
                                    <i class="fa-solid fa-trash-can"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php include 'layout/footer.php'; ?>

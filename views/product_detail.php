<?php 
include 'layout/header.php'; 

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die('<div class="container py-5"><div class="alert alert-danger">Không tìm thấy sản phẩm!</div></div>');
}

$id = (int)$_GET['id'];
$success = '';
$error = '';

// 1. LẤY THÔNG TIN SẢN PHẨM
$stmt = $conn->prepare("SELECT p.*, 
                           COALESCE(c.name, 'Chưa phân loại') as category_name 
                      FROM products p 
                      LEFT JOIN categories c ON p.category_id = c.id 
                      WHERE p.id = ? AND p.status != 'hidden'");
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    die('<div class="container py-5"><div class="alert alert-danger">Sản phẩm không tồn tại hoặc đã bị ẩn!</div></div>');
}

// Lấy Gallery Ảnh phụ
$stmt_gallery = $conn->prepare("SELECT * FROM product_images WHERE product_id = ? ORDER BY sort_order ASC, id ASC");
$stmt_gallery->execute([$id]);
$product_gallery = $stmt_gallery->fetchAll();

// Xử lý Sản phẩm đã xem gần đây (Lưu vào session)
if (!isset($_SESSION['recently_viewed'])) $_SESSION['recently_viewed'] = [];
$current_history = $_SESSION['recently_viewed'];

// Thêm sản phẩm vừa xem vào đầu danh sách (Xóa trùng và giới hạn 6)
$current_history = array_values(array_unique(array_merge([$id], $current_history)));
$_SESSION['recently_viewed'] = array_slice($current_history, 0, 7); // Giữ 7 để khi hiển thị trừ ra 1 vẫn được 6

// 2. XỬ LÝ GỬI ĐÁNH GIÁ (REVIEW)
$user_review = null;
if (isset($_SESSION['user'])) {
    // Lấy user_id
    $u_stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $u_stmt->execute([$_SESSION['user']]);
    $u_id = $u_stmt->fetch()['id'];

    // Kiểm tra xem user đã đánh giá chưa
    $check_rev = $conn->prepare("SELECT * FROM reviews WHERE user_id = ? AND product_id = ?");
    $check_rev->execute([$u_id, $id]);
    $user_review = $check_rev->fetch();

    if (isset($_POST['submit_review'])) {
        if ($user_review) {
            $error = "Bạn đã đánh giá sản phẩm này rồi!";
        } else {
            $rating = (int)$_POST['rating'];
            $comment = trim($_POST['comment']);
            
            if ($rating >= 1 && $rating <= 5) {
                $r_stmt = $conn->prepare("INSERT INTO reviews (user_id, product_id, rating, comment) VALUES (?, ?, ?, ?)");
                if ($r_stmt->execute([$u_id, $id, $rating, $comment])) {
                    $success = "Cảm ơn bạn đã đánh giá sản phẩm!";
                    // Reload user_review sau khi insert thành công
                    $check_rev->execute([$u_id, $id]);
                    $user_review = $check_rev->fetch();
                }
            }
        }
    }
}

// 3. LẤY DANH SÁCH ĐÁNH GIÁ
$stmt_rev = $conn->prepare("SELECT r.*, u.username FROM reviews r JOIN users u ON r.user_id = u.id WHERE r.product_id = ? ORDER BY r.created_at DESC");
$stmt_rev->execute([$id]);
$reviews = $stmt_rev->fetchAll();

// Tính trung bình sao
$avg_rating = 0;
if (count($reviews) > 0) {
    $sum = 0;
    foreach($reviews as $r) $sum += $r['rating'];
    $avg_rating = round($sum / count($reviews), 1);
}

// 4. LẤY SẢN PHẨM LIÊN QUAN (Cùng danh mục)
$stmt_rel = $conn->prepare("SELECT * FROM products WHERE category_id = ? AND id != ? AND status != 'hidden' LIMIT 4");
$stmt_rel->execute([$product['category_id'], $id]);
$related_products = $stmt_rel->fetchAll();
?>

<div class="container py-4">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="?page=home" class="text-pink text-decoration-none">Trang chủ</a></li>
            <?php if ($product['category_id']): ?>
            <li class="breadcrumb-item">
                <a href="?page=home&category_id=<?= $product['category_id'] ?>" class="text-pink text-decoration-none">
                    <?= htmlspecialchars($product['category_name']) ?>
                </a>
            </li>
            <?php endif; ?>
            <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($product['name']) ?></li>
        </ol>
    </nav>

    <div class="row">
        <!-- Hình ảnh sản phẩm -->
        <div class="col-lg-6">
            <div class="product-gallery">
                <!-- Ảnh lớn chính -->
                <div class="position-relative mb-3">
                    <?php if($product['status'] == 'out_of_stock'): ?>
                        <div class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center rounded-4" style="background: rgba(0,0,0,0.3); z-index: 2;">
                            <span class="badge bg-danger fs-4 px-4 py-2 shadow-lg">TẠM HẾT HÀNG</span>
                        </div>
                    <?php endif; ?>

                    <?php 
                    $main_img = (file_exists('uploads/' . $product['image']) && !empty($product['image'])) ? 'uploads/' . $product['image'] : 'images/' . $product['image'];
                    ?>
                    <img id="mainImage" src="<?= $main_img ?>" 
                         class="img-fluid rounded-4 shadow-sm w-100 transition-all" 
                         style="aspect-ratio: 1/1; object-fit: cover;"
                         alt="<?= htmlspecialchars($product['name']) ?>">
                </div>

                <!-- Dãy ảnh phụ (thumbnails) -->
                <?php if (!empty($product_gallery)): ?>
                <div class="row g-2">
                    <div class="col-3">
                        <img src="<?= $main_img ?>" 
                             class="img-fluid rounded-3 border-pink cursor-pointer thumb-gallery active-thumb" 
                             onclick="changeImage(this)"
                             data-src="<?= $main_img ?>"
                             style="aspect-ratio: 1/1; object-fit: cover;">
                    </div>
                    <?php foreach($product_gallery as $g): ?>
                    <div class="col-3">
                        <?php $g_src = 'uploads/' . $g['image_name']; ?>
                        <img src="<?= $g_src ?>" 
                             class="img-fluid rounded-3 cursor-pointer thumb-gallery" 
                             onclick="changeImage(this)"
                             data-src="<?= $g_src ?>"
                             style="aspect-ratio: 1/1; object-fit: cover;">
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <script>
        function changeImage(el) {
            // Thay đổi ảnh lớn
            const mainImg = document.getElementById('mainImage');
            mainImg.src = el.getAttribute('data-src');
            
            // Cập nhật class active cho thumbnail
            document.querySelectorAll('.thumb-gallery').forEach(t => t.classList.remove('active-thumb', 'border-pink'));
            el.classList.add('active-thumb', 'border-pink');
        }
        </script>
        <style>
        .thumb-gallery { cursor: pointer; transition: all 0.3s; border: 2px solid transparent; opacity: 0.7; }
        .thumb-gallery:hover { opacity: 1; border-color: #fce4ec; }
        .active-thumb { opacity: 1; border-color: #e91e63 !important; }
        </style>

        <!-- Thông tin chi tiết -->
        <div class="col-lg-6">

            <h1 class="display-6 fw-bold"><?= htmlspecialchars($product['name']) ?></h1>
            
            <div class="d-flex align-items-center mb-3">
                <div class="text-warning me-2">
                    <?php for($i=1; $i<=5; $i++): ?>
                        <i class="fa-<?= $i <= $avg_rating ? 'solid' : 'regular' ?> fa-star"></i>
                    <?php endfor; ?>
                </div>
                <span class="text-muted">(<?= count($reviews) ?> đánh giá)</span>
                <span class="mx-2 text-muted">|</span>
                <span class="text-success small"><i class="fa-solid fa-check-circle me-1"></i>Chính hãng</span>
            </div>

            <div class="mb-4">
                <?php if($product['sale_price']): ?>
                    <span class="text-pink fs-3 fw-bold me-2"><?= number_format($product['sale_price']) ?> đ</span>
                    <span class="text-muted text-decoration-line-through fs-5"><?= number_format($product['price']) ?> đ</span>
                    <span class="badge bg-danger ms-2">-<?= round(($product['price'] - $product['sale_price']) / $product['price'] * 100) ?>%</span>
                <?php else: ?>
                    <span class="text-pink fs-3 fw-bold"><?= number_format($product['price']) ?> đ</span>
                <?php endif; ?>
            </div>

            <div class="mb-4">
                <?php if($product['status'] == 'active' && $product['stock'] > 0): ?>
                    <span class="badge bg-success fs-6"><i class="fa-solid fa-check me-1"></i> Sẵn sàng giao hàng</span>
                    <?php if($product['stock'] <= 5): ?>
                        <span class="badge bg-warning text-dark fs-6 ms-2"><i class="fa-solid fa-fire me-1"></i> Chỉ còn <?= $product['stock'] ?> sản phẩm cuối cùng!</span>
                    <?php endif; ?>
                <?php else: ?>
                    <span class="badge bg-danger fs-6"><i class="fa-solid fa-xmark me-1"></i> Tạm hết hàng</span>
                <?php endif; ?>
            </div>

            <div class="product-description mb-4">
                <p class="text-muted"><?= nl2br(htmlspecialchars($product['description'] ?: 'Thông tin sản phẩm đang được cập nhật...')) ?></p>
            </div>

            <div class="d-flex gap-3">
                <div class="flex-grow-1">
                    <?php if ($product['status'] == 'active' && $product['stock'] > 0): ?>
                        <a href="?page=cart&add=<?= $product['id'] ?>" class="btn btn-pink btn-lg w-100 py-3 shadow-sm">
                            <i class="fa-solid fa-cart-plus me-2"></i>Thêm vào giỏ hàng
                        </a>
                    <?php else: ?>
                        <button class="btn btn-secondary btn-lg w-100 py-3" disabled>Tạm hết hàng</button>
                    <?php endif; ?>
                </div>
                <?php if (isset($_SESSION['user'])): ?>
                    <a href="?page=wishlist&add=<?= $product['id'] ?>" class="btn btn-outline-pink btn-lg px-4" title="Thêm vào yêu thích">
                        <i class="fa-regular fa-heart"></i>
                    </a>
                <?php else: ?>
                    <a href="?page=login" class="btn btn-outline-pink btn-lg px-4" title="Đăng nhập để lưu yêu thích">
                        <i class="fa-regular fa-heart"></i>
                    </a>
                <?php endif; ?>
            </div>
            
            <hr class="my-5">
            <div class="row g-3">
                <div class="col-6"><i class="fa-solid fa-truck-fast text-pink me-2"></i>Giao nhanh 2h</div>
                <div class="col-6"><i class="fa-solid fa-shield-heart text-pink me-2"></i>Bảo mật 100%</div>
                <div class="col-6"><i class="fa-solid fa-rotate-left text-pink me-2"></i>Đổi trả 7 ngày</div>
                <div class="col-6"><i class="fa-solid fa-gift text-pink me-2"></i>Quà tặng hấp dẫn</div>
            </div>
        </div>
    </div>

    <!-- ĐÁNH GIÁ SẢN PHẨM -->
    <div class="row mt-5 pt-5">
        <div class="col-lg-8 shadow-sm p-4 rounded-4 bg-white">
            <h4 class="fw-bold mb-4">Đánh giá từ khách hàng</h4>
            
            <?php if ($success): ?><div class="alert alert-success shadow-sm"><?= $success ?></div><?php endif; ?>
            <?php if ($error): ?><div class="alert alert-danger shadow-sm"><?= $error ?></div><?php endif; ?>

            <?php if (isset($_SESSION['user'])): ?>
                <?php if ($user_review): ?>
                    <div class="alert alert-info border-0 rounded-4 p-4 mb-5 shadow-sm">
                        <div class="d-flex align-items-center mb-3">
                            <div class="bg-pink text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                <i class="fa-solid fa-check"></i>
                            </div>
                            <h6 class="fw-bold mb-0">Bạn đã đánh giá sản phẩm này</h6>
                        </div>
                        <div class="text-warning mb-2" style="margin-left: 56px;">
                            <?php for($i=1; $i<=5; $i++) echo $i <= $user_review['rating'] ? '<i class="fa-solid fa-star me-1"></i>' : '<i class="fa-regular fa-star me-1"></i>'; ?>
                        </div>
                        <p class="mb-0 text-muted italic small" style="margin-left: 56px;">"<?= htmlspecialchars($user_review['comment']) ?>"</p>
                    </div>
                <?php else: ?>
                    <form method="POST" class="mb-5 p-3 border rounded-4 bg-light">
                        <?= csrf_input() ?>
                        <h6 class="fw-bold mb-3">Gửi đánh giá của bạn</h6>
                        <div class="mb-3">
                            <label class="form-label small">Xếp hạng:</label>
                            <select name="rating" class="form-select d-inline-block ms-2" style="width: 220px;" required>
                                <option value="5">⭐⭐⭐⭐⭐ 5 sao</option>
                                <option value="4">⭐⭐⭐⭐ 4 sao</option>
                                <option value="3">⭐⭐⭐ 3 sao</option>
                                <option value="2">⭐⭐ 2 sao</option>
                                <option value="1">⭐ 1 sao</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <textarea name="comment" class="form-control" rows="3" placeholder="Chia sẻ cảm nhận của bạn về sản phẩm..." required></textarea>
                        </div>
                        <button type="submit" name="submit_review" class="btn btn-pink px-4">Gửi đánh giá</button>
                    </form>
                <?php endif; ?>
            <?php else: ?>
                <div class="alert alert-light border small text-center mb-5">
                    Vui lòng <a href="?page=login" class="text-pink fw-bold">Đăng nhập</a> để gửi đánh giá.
                </div>
            <?php endif; ?>

            <?php if (empty($reviews)): ?>
                <p class="text-muted text-center pt-3">Chưa có đánh giá nào cho sản phẩm này.</p>
            <?php else: ?>
                <?php foreach($reviews as $r): ?>
                <div class="d-flex mb-4 border-bottom pb-3">
                    <div class="flex-shrink-0">
                        <div class="bg-pink text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 45px; height: 45px;">
                            <?= strtoupper(substr($r['username'], 0, 1)) ?>
                        </div>
                    </div>
                    <div class="ms-3 flex-grow-1">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <h6 class="fw-bold mb-0"><?= htmlspecialchars($r['username']) ?></h6>
                            <small class="text-muted"><?= date('d/m/Y', strtotime($r['created_at'])) ?></small>
                        </div>
                        <div class="text-warning small mb-2">
                            <?php for($i=1; $i<=5; $i++) echo $i <= $r['rating'] ? '★' : '☆'; ?>
                        </div>
                        <p class="mb-0 text-dark small"><?= nl2br(htmlspecialchars($r['comment'])) ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- SẢN PHẨM LIÊN QUAN -->
        <div class="col-lg-4 ps-lg-5">
            <h4 class="fw-bold mb-4">Sản phẩm liên quan</h4>
            <?php if(empty($related_products)): ?>
                <p class="small text-muted">Không có sản phẩm liên quan.</p>
            <?php else: ?>
                <?php foreach($related_products as $rel): ?>
                <a href="?page=product_detail&id=<?= $rel['id'] ?>" class="text-decoration-none text-dark">
                    <div class="d-flex align-items-center mb-3 p-2 rounded-3 hover-shadow-sm transition-all" style="background: #fff; border: 1px solid #f0f0f0;">
                        <?php 
                        $rel_img = (file_exists('uploads/' . $rel['image']) && !empty($rel['image'])) ? 'uploads/' . $rel['image'] : 'images/' . $rel['image'];
                        ?>
                        <img src="<?= $rel_img ?>" class="rounded border" style="width: 70px; height: 70px; object-fit: cover;">
                        <div class="ms-3 overflow-hidden">
                            <div class="small fw-bold text-truncate"><?= htmlspecialchars($rel['name']) ?></div>
                            <div class="text-pink small"><?= number_format($rel['price']) ?> đ</div>
                        </div>
                    </div>
                </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    <!-- SẢN PHẨM ĐÃ XEM GẦN ĐÂY -->
    <?php 
    $recent_ids = array_diff($_SESSION['recently_viewed'] ?? [], [$id]); // Trừ sản phẩm đang xem
    if (!empty($recent_ids)):
        $placeholders = implode(',', array_fill(0, count($recent_ids), '?'));
        $stmt_recent = $conn->prepare("SELECT * FROM products WHERE id IN ($placeholders) AND status != 'hidden'");
        $stmt_recent->execute(array_values($recent_ids));
        $recent_products = $stmt_recent->fetchAll();
        
        if ($recent_products):
    ?>
    <div class="mt-5 pt-5 pb-4">
        <h4 class="fw-bold mb-4 font-elegant text-pink border-bottom pb-2">Bạn đã xem <span class="text-dark">gần đây</span></h4>
        <div class="row g-3">
            <?php foreach($recent_products as $rp): ?>
            <div class="col-lg-2 col-md-3 col-6">
                <a href="?page=product_detail&id=<?= $rp['id'] ?>" class="text-decoration-none text-dark card h-100 border-0 shadow-sm transition-all hover-translate-y">
                    <img src="<?= (file_exists('uploads/'.$rp['image']) && !empty($rp['image'])) ? 'uploads/'.$rp['image'] : 'images/'.$rp['image'] ?>" 
                         class="card-img-top" style="aspect-ratio: 1/1; object-fit: cover;">
                    <div class="card-body p-2 text-center">
                        <div class="small fw-bold text-truncate mb-1"><?= htmlspecialchars($rp['name']) ?></div>
                        <div class="text-pink small fw-bold"><?= number_format($rp['sale_price'] ?? $rp['price']) ?> đ</div>
                    </div>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php 
        endif;
    endif; 
    ?>
</div>

<?php include 'layout/footer.php'; ?>
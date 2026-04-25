<?php include 'layout/header.php'; ?>

<!-- Hero Banner -->
<?php if (!isset($_GET['search']) && !isset($_GET['category_id']) && !isset($_GET['min_price'])): ?>
<div class="hero-banner">
    <div class="container">
        <div class="hero-content mx-auto text-center">
            <span class="badge bg-pink mb-3 px-4 py-2 fs-6">Mỹ Phẩm Cao Cấp</span>
            <h1 class="font-elegant">Khám Phá Vẻ Đẹp Của Bạn</h1>
            <p>Khám phá bộ sưu tập mỹ phẩm chính hãng giúp chăm sóc và nâng tầm vẻ đẹp tự nhiên của bạn mỗi ngày.</p>
            
            <div class="d-flex gap-3 justify-content-center">
                <a href="#products" class="btn btn-pink btn-lg px-5">
                    <i class="fa-solid fa-bag-shopping me-2"></i>Mua sắm ngay
                </a>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="container py-5" id="products">
    <?php
    $limit = 8; 
    $page = isset($_GET['p']) && is_numeric($_GET['p']) ? (int)$_GET['p'] : 1;
    if ($page < 1) $page = 1;
    $offset = ($page - 1) * $limit;

    $where_clauses = ["status != 'hidden'"];
    $params = [];
    $title = "Sản phẩm của chúng tôi";

    if (isset($_GET['search']) && $_GET['search'] != '') {
        $search = trim($_GET['search']);
        $where_clauses[] = "name LIKE ?";
        $params[] = "%$search%";
        $title = "Kết quả tìm kiếm: <span class='text-pink'>" . htmlspecialchars($search) . "</span>";
    }

    if (isset($_GET['category_id']) && is_numeric($_GET['category_id'])) {
        $cat_id = (int)$_GET['category_id'];
        $where_clauses[] = "category_id = ?";
        $params[] = $cat_id;
        
        $stmt_cat = $conn->prepare("SELECT name FROM categories WHERE id = ?");
        $stmt_cat->execute([$cat_id]);
        $cat_info = $stmt_cat->fetch();
        if ($cat_info) $title = "Danh mục: " . htmlspecialchars($cat_info['name']);
    }

    if (isset($_GET['min_price']) && is_numeric($_GET['min_price'])) {
        $where_clauses[] = "price >= ?";
        $params[] = (int)$_GET['min_price'];
    }
    if (isset($_GET['max_price']) && is_numeric($_GET['max_price'])) {
        $where_clauses[] = "price <= ?";
        $params[] = (int)$_GET['max_price'];
    }

    $where_sql = " WHERE " . implode(" AND ", $where_clauses);

    $sort = $_GET['sort'] ?? 'newest';
    $order_sql = " ORDER BY p.id DESC"; 
    if ($sort === 'price_asc') $order_sql = " ORDER BY p.price ASC";
    elseif ($sort === 'price_desc') $order_sql = " ORDER BY p.price DESC";
    elseif ($sort === 'oldest') $order_sql = " ORDER BY p.id ASC";
    elseif ($sort === 'top_rated') $order_sql = " ORDER BY avg_rating DESC, review_count DESC";

    $stmt_count = $conn->prepare("SELECT COUNT(*) as total FROM products p $where_sql");
    $stmt_count->execute($params);
    $total_products = $stmt_count->fetch()['total'] ?? 0;
    $total_pages = ceil($total_products / $limit);

    $query = "SELECT p.*, AVG(r.rating) as avg_rating, COUNT(r.id) as review_count 
              FROM products p 
              LEFT JOIN reviews r ON p.id = r.product_id 
              $where_sql 
              GROUP BY p.id 
              $order_sql 
              LIMIT $limit OFFSET $offset";
    $stmt = $conn->prepare($query);
    $stmt->execute($params);
    $products = $stmt->fetchAll();
    ?>

    <!-- Bộ lọc UI -->
    <?php 
    // Chỉ hiện section nổi bật khi không tìm kiếm/lọc
    if (empty($_GET['search']) && empty($_GET['category_id']) && $page == 1): 
        $stmt_featured = $conn->query("SELECT p.*, AVG(r.rating) as avg_rating, COUNT(r.id) as review_count 
                                        FROM products p 
                                        LEFT JOIN reviews r ON p.id = r.product_id 
                                        WHERE p.is_featured = 1 AND p.status = 'active'
                                        GROUP BY p.id 
                                        LIMIT 4");
        $featured_products = $stmt_featured->fetchAll();
        
        if ($featured_products):
    ?>
    <section class="featured-section mb-5 py-4 px-4 bg-light rounded-4">
        <div class="d-flex justify-content-between align-items-end mb-4">
            <div>
                <h2 class="font-elegant text-pink fw-bold mb-0">Sản phẩm <span class="text-dark">nổi bật</span></h2>
                <p class="text-muted small mb-0">Lựa chọn tinh tế nhất dành riêng cho bạn</p>
            </div>
        </div>
        <div class="row g-4">
            <?php foreach($featured_products as $f): ?>
            <div class="col-lg-6 col-xl-3">
                <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden overflow-hidden hover-shadow transition-all bg-white">
                    <div class="row g-0 h-100">
                        <div class="col-5">
                            <div class="h-100" style="background: url('<?= (file_exists('uploads/'.$f['image']) && !empty($f['image'])) ? 'uploads/'.$f['image'] : 'images/'.$f['image'] ?>') center/cover no-repeat;"></div>
                        </div>
                        <div class="col-7">
                            <div class="card-body d-flex flex-column justify-content-center p-3">
                                <span class="badge bg-pink-light text-pink align-self-start mb-2" style="font-size: 0.65rem;">NỔI BẬT</span>
                                <h6 class="fw-bold text-truncate mb-1"><?= htmlspecialchars($f['name']) ?></h6>
                                <div class="text-warning mb-2" style="font-size: 0.7rem;">
                                    <?php 
                                    $rating = round($f['avg_rating']); 
                                    for($i=1; $i<=5; $i++) echo ($i <= $rating) ? '<i class="fa-solid fa-star"></i>' : '<i class="fa-regular fa-star"></i>';
                                    ?>
                                </div>
                                <div class="mb-3">
                                    <?php if($f['sale_price']): ?>
                                        <span class="text-pink fw-bold d-block small"><?= number_format($f['sale_price']) ?> đ</span>
                                        <span class="text-muted text-decoration-line-through" style="font-size: 0.7rem;"><?= number_format($f['price']) ?> đ</span>
                                    <?php else: ?>
                                        <span class="text-dark fw-bold d-block small"><?= number_format($f['price']) ?> đ</span>
                                    <?php endif; ?>
                                </div>
                                <a href="?page=product_detail&id=<?= $f['id'] ?>" class="btn btn-sm btn-pink rounded-pill opacity-75">Xem ngay</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php 
        endif;
    endif; 
    ?>

    <div class="filter-section mb-5 p-4 bg-white rounded-4 shadow-sm">
        <form action="index.php" method="GET" class="row g-3 align-items-end">
            <input type="hidden" name="page" value="home">
            <div class="col-md-3">
                <label class="form-label small fw-bold text-muted">Danh mục</label>
                <select name="category_id" class="form-select border-0 bg-light">
                    <option value="">Tất cả danh mục</option>
                    <?php
                    $cats = $conn->query("SELECT * FROM categories ORDER BY name")->fetchAll();
                    foreach($cats as $c):
                        $sel = (isset($_GET['category_id']) && $_GET['category_id'] == $c['id']) ? 'selected' : '';
                        echo "<option value='{$c['id']}' $sel>{$c['name']}</option>";
                    endforeach;
                    ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-bold text-muted">Khoảng giá</label>
                <div class="input-group">
                    <input type="number" name="min_price" class="form-control border-0 bg-light" placeholder="Từ" value="<?= htmlspecialchars($_GET['min_price'] ?? '') ?>">
                    <input type="number" name="max_price" class="form-control border-0 bg-light" placeholder="Đến" value="<?= htmlspecialchars($_GET['max_price'] ?? '') ?>">
                </div>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-bold text-muted">Sắp xếp</label>
                <select name="sort" class="form-select border-0 bg-light">
                    <option value="newest" <?= $sort == 'newest' ? 'selected' : '' ?>>Mới nhất</option>
                    <option value="price_asc" <?= $sort == 'price_asc' ? 'selected' : '' ?>>Giá tăng dần</option>
                    <option value="price_desc" <?= $sort == 'price_desc' ? 'selected' : '' ?>>Giá giảm dần</option>
                    <option value="top_rated" <?= $sort == 'top_rated' ? 'selected' : '' ?>>Đánh giá cao nhất</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-pink w-100 py-2 rounded-3 shadow-sm">Lọc sản phẩm</button>
            </div>
        </form>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-4 px-2">
        <h3 class="fw-bold mb-0"><?= $title ?></h3>
        <span class="text-muted small">Hiển thị <?= count($products) ?> / <?= $total_products ?> món</span>
    </div>

    <?php if (empty($products)): ?>
        <div class="text-center py-5"><h4 class="text-muted">Không có sản phẩm nào.</h4></div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach($products as $p): ?>
            <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                <div class="card product-card h-100 border-0 shadow-sm transition-all hover-translate-y">
                    <div class="position-relative overflow-hidden rounded-top-4">
                        <?php if($p['status'] == 'out_of_stock'): ?>
                            <div class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center bg-black bg-opacity-25" style="z-index:3">
                                <span class="badge bg-danger">HẾT HÀNG</span>
                            </div>
                        <?php elseif($p['sale_price']): ?>
                            <div class="position-absolute top-0 start-0 m-2" style="z-index:2">
                                <span class="badge bg-danger shadow-sm">-<?= round(($p['price'] - $p['sale_price']) / $p['price'] * 100) ?>%</span>
                            </div>
                        <?php elseif($p['stock'] <= 5 && $p['stock'] > 0): ?>
                            <div class="position-absolute top-0 start-0 m-2" style="z-index:2">
                                <span class="badge bg-warning text-dark shadow-sm">Chỉ còn <?= $p['stock'] ?> sp</span>
                            </div>
                        <?php endif; ?>
                        
                        <a href="?page=product_detail&id=<?= $p['id'] ?>">
                            <?php $src = (file_exists('uploads/'.$p['image']) && $p['image']) ? 'uploads/'.$p['image'] : 'images/'.$p['image']; ?>
                            <img src="<?= $src ?>" class="card-img-top" alt="<?= htmlspecialchars($p['name']) ?>">
                        </a>
                        
                        <!-- Wishlist Button -->
                        <?php if (isset($_SESSION['user'])): ?>
                            <a href="?page=wishlist&add=<?= $p['id'] ?>" class="position-absolute top-0 end-0 m-2 btn btn-sm btn-white rounded-circle shadow-sm text-pink wishlist-float">
                                <i class="fa-regular fa-heart"></i>
                            </a>
                        <?php else: ?>
                            <a href="?page=login" class="position-absolute top-0 end-0 m-2 btn btn-sm btn-white rounded-circle shadow-sm text-pink wishlist-float" title="Đăng nhập để lưu yêu thích">
                                <i class="fa-regular fa-heart"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                    <div class="card-body text-center d-flex flex-column p-4">
                        <h6 class="text-truncate fw-bold mb-1"><?= htmlspecialchars($p['name']) ?></h6>
                        
                        <div class="mb-2">
                            <?php if ($p['review_count'] > 0): ?>
                                <div class="text-warning small">
                                    <?php 
                                    $rating = round($p['avg_rating']); 
                                    for($i=1; $i<=5; $i++) echo ($i <= $rating) ? '<i class="fa-solid fa-star"></i>' : '<i class="fa-regular fa-star"></i>';
                                    ?>
                                    <span class="text-muted ms-1" style="font-size: 0.75rem;">(<?= $p['review_count'] ?>)</span>
                                </div>
                            <?php else: ?>
                                <div class="text-muted small" style="font-size: 0.75rem;">Chưa có đánh giá</div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <?php if($p['sale_price']): ?>
                                <span class="text-pink fw-bold d-block"><?= number_format($p['sale_price']) ?> đ</span>
                                <span class="text-muted text-decoration-line-through small"><?= number_format($p['price']) ?> đ</span>
                            <?php else: ?>
                                <span class="text-pink fw-bold d-block"><?= number_format($p['price']) ?> đ</span>
                                <span class="small text-white">&nbsp;</span> <!-- Giữ layout -->
                            <?php endif; ?>
                        </div>
                        
                        <div class="mt-auto">
                            <?php if ($p['status'] == 'active'): ?>
                                <a href="?page=cart&add=<?= $p['id'] ?>" class="btn btn-pink btn-sm w-100 rounded-pill">Thêm vào giỏ</a>
                            <?php else: ?>
                                <button class="btn btn-secondary btn-sm w-100 rounded-pill" disabled>Hết hàng</button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <?php if ($total_pages > 1): ?>
        <nav class="mt-5"><ul class="pagination justify-content-center">
            <?php $bq = $_GET; unset($bq['p']); $bu = "index.php?".http_build_query($bq); ?>
            <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                <a class="page-link border-0 rounded-circle mx-1" href="<?= $bu ?>&p=<?= $page - 1 ?>"><i class="fa-solid fa-chevron-left"></i></a>
            </li>
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <li class="page-item <?= ($page == $i) ? 'active' : '' ?>">
                    <a class="page-link border-0 rounded-circle mx-1 <?= ($page == $i) ? 'bg-pink text-white' : 'text-dark' ?>" href="<?= $bu ?>&p=<?= $i ?>"><?= $i ?></a>
                </li>
            <?php endfor; ?>
            <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : '' ?>">
                <a class="page-link border-0 rounded-circle mx-1" href="<?= $bu ?>&p=<?= $page + 1 ?>"><i class="fa-solid fa-chevron-right"></i></a>
            </li>
        </ul></nav>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php include 'layout/footer.php'; ?>
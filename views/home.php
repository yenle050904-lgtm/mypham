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
    $order_sql = " ORDER BY id DESC"; 
    if ($sort === 'price_asc') $order_sql = " ORDER BY price ASC";
    elseif ($sort === 'price_desc') $order_sql = " ORDER BY price DESC";
    elseif ($sort === 'oldest') $order_sql = " ORDER BY id ASC";

    $stmt_count = $conn->prepare("SELECT COUNT(*) as total FROM products $where_sql");
    $stmt_count->execute($params);
    $total_products = $stmt_count->fetch()['total'] ?? 0;
    $total_pages = ceil($total_products / $limit);

    $query = "SELECT * FROM products $where_sql $order_sql LIMIT $limit OFFSET $offset";
    $stmt = $conn->prepare($query);
    $stmt->execute($params);
    $products = $stmt->fetchAll();
    ?>

    <!-- Bộ lọc UI -->
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
                            <div class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center bg-black bg-opacity-25" style="z-index:1 px-5">
                                <span class="badge bg-danger">HẾT HÀNG</span>
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
                        <h6 class="text-truncate fw-bold mb-2"><?= htmlspecialchars($p['name']) ?></h6>
                        <p class="text-pink fw-bold mb-3"><?= number_format($p['price']) ?> đ</p>
                        
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
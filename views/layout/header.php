<?php
// Header hiện đại - Mỹ Phẩm Xinh
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mỹ Phẩm Xinh | Vẻ Đẹp Tự Nhiên</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700;800&family=Dancing+Script:wght@700&display=swap" rel="stylesheet">
    <link href="assets/style.css" rel="stylesheet">
    <style>
        body { font-family: 'Nunito', sans-serif; }
        .font-elegant { font-family: 'Dancing Script', cursive; }
    </style>
</head>
<body class="d-flex flex-column min-vh-100">

<nav class="navbar navbar-expand-lg sticky-top shadow-sm py-3">
    <div class="container">
        <a class="navbar-brand fw-bold text-pink fs-3 font-elegant" href="?page=home">
            <i class="fa-solid fa-wand-magic-sparkles me-1"></i>Mỹ Phẩm Xinh
        </a>

        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto mb-0 align-items-center">
                <li class="nav-item">
                    <a class="nav-link" href="?page=home">Trang chủ</a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">Danh mục</a>
                    <ul class="dropdown-menu border-0 shadow-lg p-2 rounded-3">
                        <?php 
                        $cats = $conn->query("SELECT * FROM categories ORDER BY name")->fetchAll();
                        foreach($cats as $c):
                        ?>
                        <li><a class="dropdown-item rounded-2" href="?page=home&category_id=<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="?page=gioi_thieu">Về chúng tôi</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="?page=contact">Liên hệ</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="?page=track_order"><i class="fa-solid fa-truck-fast me-1"></i>Tra cứu</a>
                </li>
            </ul>
            
            <form class="d-flex align-items-center mx-lg-4 my-2 my-lg-0" action="index.php" method="GET">
                <input type="hidden" name="page" value="home">
                <div class="input-group search-group">
                    <span class="input-group-text border-0 bg-light rounded-start-pill ps-3"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                    <input type="text" name="search" class="form-control border-0 bg-light rounded-end-pill py-2" 
                           placeholder="Tìm sản phẩm..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                </div>
            </form>

            <ul class="navbar-nav ms-auto align-items-center gap-2">
                <?php if (isset($_SESSION['user'])): ?>
                    <!-- Wishlist -->
                    <li class="nav-item">
                        <a href="?page=wishlist" class="nav-link position-relative px-2 text-pink" title="Yêu thích">
                            <i class="fa-solid fa-heart fs-5"></i>
                            <?php 
                            // Fallback if user_id is not in session
                            if (isset($_SESSION['user']) && !isset($_SESSION['user_id'])) {
                                $stmt_uid = $conn->prepare("SELECT id FROM users WHERE username = ?");
                                $stmt_uid->execute([$_SESSION['user']]);
                                $_SESSION['user_id'] = $stmt_uid->fetchColumn();
                            }

                            // Query count and cache in session
                            if (isset($_SESSION['user_id']) && !isset($_SESSION['wishlist_count'])) {
                                $stmt_w = $conn->prepare("SELECT COUNT(*) FROM wishlist WHERE user_id = ?");
                                $stmt_w->execute([$_SESSION['user_id']]);
                                $_SESSION['wishlist_count'] = $stmt_w->fetchColumn();
                            }
                            if (isset($_SESSION['wishlist_count']) && $_SESSION['wishlist_count'] > 0): 
                            ?>
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger border border-light" style="font-size: 10px;">
                                    <?= $_SESSION['wishlist_count'] ?>
                                </span>
                            <?php endif; ?>
                        </a>
                    </li>
                    
                    <li class="nav-item dropdown ms-2">
                        <a class="nav-link dropdown-toggle fw-bold text-dark d-flex align-items-center gap-2" href="#" role="button" data-bs-toggle="dropdown">
                            <div class="bg-pink text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; font-size: 14px;">
                                <?= strtoupper(substr($_SESSION['user'], 0, 1)) ?>
                            </div>
                            <span><?= htmlspecialchars($_SESSION['user']) ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end border-0 shadow-lg p-2 rounded-3 mt-2">
                            <?php if (($_SESSION['role'] ?? '') === 'admin'): ?>
                                <li><a class="dropdown-item py-2" href="?page=admin_report"><i class="fa-solid fa-chart-line me-2"></i>Thống kê</a></li>
                                <li><a class="dropdown-item py-2" href="?page=admin"><i class="fa-solid fa-box me-2"></i>Sản phẩm</a></li>
                                <li><a class="dropdown-item py-2" href="?page=admin_categories"><i class="fa-solid fa-tags me-2"></i>Danh mục</a></li>
                                <li><a class="dropdown-item py-2" href="?page=admin_coupons"><i class="fa-solid fa-ticket me-2"></i>Mã giảm giá</a></li>
                                <li><a class="dropdown-item py-2" href="?page=admin_orders"><i class="fa-solid fa-clipboard-list me-2"></i>Đơn hàng</a></li>
                                <li>
                                    <a class="dropdown-item py-2 d-flex justify-content-between align-items-center" href="?page=admin_contacts">
                                        <span><i class="fa-solid fa-envelope me-2"></i>Liên hệ</span>
                                        <?php 
                                        $unread_mt = $conn->query("SELECT COUNT(*) FROM contacts WHERE is_read = 0")->fetchColumn();
                                        if ($unread_mt > 0): 
                                        ?>
                                            <span class="badge rounded-pill bg-danger shadow-sm"><?= $unread_mt ?></span>
                                        <?php endif; ?>
                                    </a>
                                </li>
                                <li><a class="dropdown-item py-2" href="?page=admin_users"><i class="fa-solid fa-users me-2"></i>Người dùng</a></li>
                            <?php else: ?>
                                <li><a class="dropdown-item py-2" href="?page=profile"><i class="fa-solid fa-user-pen me-2"></i>Hồ sơ cá nhân</a></li>
                                <li><a class="dropdown-item py-2" href="?page=my_orders"><i class="fa-solid fa-bag-shopping me-2"></i>Đơn hàng của tôi</a></li>
                                <li><a class="dropdown-item py-2" href="?page=wishlist"><i class="fa-solid fa-heart me-2"></i>Danh sách yêu thích</a></li>
                            <?php endif; ?>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item py-2 text-danger" href="?page=logout"><i class="fa-solid fa-right-from-bracket me-2"></i>Đăng xuất</a></li>
                        </ul>
                    </li>
                    
                    <?php if (($_SESSION['role'] ?? '') !== 'admin'): ?>
                    <li class="nav-item ms-lg-2">
                        <a href="?page=cart" class="btn btn-cart-red position-relative rounded-pill px-3">
                            <i class="fa-solid fa-cart-shopping"></i>
                            <?php 
                            $cart_count = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;
                            if ($cart_count > 0): 
                            ?>
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger border border-light">
                                    <?= $cart_count ?>
                                </span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <?php endif; ?>
                <?php else: ?>
                    <li class="nav-item">
                        <a href="?page=login" class="btn btn-pink px-4 py-2 rounded-pill shadow-sm">
                            <i class="fa-solid fa-right-to-bracket me-2"></i>Đăng nhập
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<!-- Global Flash Message -->
<?php if (isset($_SESSION['flash_msg'])): ?>
<div class="container mt-3" id="flash-container">
    <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 rounded-3" role="alert">
        <i class="fa-solid fa-circle-check me-2"></i>
        <?= $_SESSION['flash_msg']; unset($_SESSION['flash_msg']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>
</div>
<script>
    setTimeout(function() {
        const flash = document.getElementById('flash-container');
        if (flash) {
            flash.style.transition = "opacity 0.5s ease";
            flash.style.opacity = "0";
            setTimeout(() => flash.remove(), 500);
        }
    }, 3000);
</script>
<?php endif; ?>

<main class="flex-grow-1">
<?php
// ==================== XỬ LÝ TRƯỚC KHI XUẤT HTML ====================
$success = '';
$error = '';

// Kiểm tra quyền admin
if (($_SESSION['role'] ?? '') !== 'admin') {
    die('<div class="container py-5"><div class="alert alert-danger text-center">Bạn không có quyền truy cập trang Admin!</div></div>');
}

// Xử lý Thêm / Sửa sản phẩm
if (isset($_POST['add']) || isset($_POST['update'])) {
    // CSRF tự động kiểm tra
    
    $name   = trim($_POST['name']);
    $price  = (int)$_POST['price'];
    $sale_price = ($_POST['sale_price'] !== '') ? (int)$_POST['sale_price'] : null;
    $cat    = (int)$_POST['category_id'];
    $stock  = (int)$_POST['stock'];
    $desc   = trim($_POST['description']);
    $status = $_POST['status'] ?? 'active';
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;

    if ($sale_price !== null && $sale_price >= $price) {
        $error = "Giá khuyến mãi phải nhỏ hơn giá gốc!";
    }
    
    $image_name = $_POST['current_image'] ?? '';

    // Xử lý Upload Ảnh
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        $filename = $_FILES['image']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if (in_array($ext, $allowed)) {
            $new_name = time() . '_' . uniqid() . '.' . $ext;
            $upload_path = 'uploads/' . $new_name; // Đường dẫn tương đối từ public/
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                $image_name = $new_name;
            } else {
                $error = "Không thể tải ảnh lên thư mục uploads!";
            }
        } else {
            $error = "Chỉ chấp nhận các định dạng ảnh: " . implode(', ', $allowed);
        }
    }

    if (empty($error)) {
        if (isset($_POST['add'])) {
            $stmt = $conn->prepare("INSERT INTO products (name, price, sale_price, is_featured, stock, image, category_id, description, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            if ($stmt->execute([$name, $price, $sale_price, $is_featured, $stock, $image_name, $cat, $desc, $status])) {
                $success = "Thêm sản phẩm thành công!";
            } else {
                $error = "Có lỗi xảy ra khi thêm sản phẩm.";
            }
        } else {
            $id = (int)$_POST['id'];
            $stmt = $conn->prepare("UPDATE products SET name = ?, price = ?, sale_price = ?, is_featured = ?, stock = ?, image = ?, category_id = ?, description = ?, status = ? WHERE id = ?");
            if ($stmt->execute([$name, $price, $sale_price, $is_featured, $stock, $image_name, $cat, $desc, $status, $id])) {
                $success = "Cập nhật sản phẩm thành công!";
            } else {
                $error = "Có lỗi xảy ra khi cập nhật sản phẩm.";
            }
        }

        // Xử lý upload nhiều ảnh phụ (Gallery)
        if (empty($error) && isset($_FILES['extra_images'])) {
            $pid = isset($_POST['add']) ? $conn->lastInsertId() : (int)$_POST['id'];
            foreach ($_FILES['extra_images']['tmp_name'] as $key => $tmp_name) {
                if ($_FILES['extra_images']['error'][$key] == 0) {
                    $ext = strtolower(pathinfo($_FILES['extra_images']['name'][$key], PATHINFO_EXTENSION));
                    if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                        $new_extra_name = 'gallery_' . time() . '_' . uniqid() . '.' . $ext;
                        if (move_uploaded_file($tmp_name, 'uploads/' . $new_extra_name)) {
                            $stmt_extra = $conn->prepare("INSERT INTO product_images (product_id, image_name) VALUES (?, ?)");
                            $stmt_extra->execute([$pid, $new_extra_name]);
                        }
                    }
                }
            }
        }
    }
}

// Xử lý xóa ảnh phụ
if (isset($_GET['delete_img'])) {
    $img_id = (int)$_GET['delete_img'];
    $stmt_img = $conn->prepare("SELECT image_name FROM product_images WHERE id = ?");
    $stmt_img->execute([$img_id]);
    $img = $stmt_img->fetch();
    if ($img) {
        if (file_exists('uploads/' . $img['image_name'])) unlink('uploads/' . $img['image_name']);
        $conn->prepare("DELETE FROM product_images WHERE id = ?")->execute([$img_id]);
        $success = "Đã xóa ảnh phụ.";
    }
}

// Xử lý Xóa
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    if ($stmt->execute([$id])) {
        $success = "Xóa sản phẩm thành công!";
    } else {
        $error = "Có lỗi xảy ra khi xóa sản phẩm.";
    }
}

// Lấy dữ liệu để sửa
$edit = null;
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $edit = $stmt->fetch();
}

// --- XỬ LÝ PHÂN TRANG ---
$per_page = 10;
$current_page = isset($_GET['p']) ? max(1, (int)$_GET['p']) : 1;
$offset = ($current_page - 1) * $per_page;

$total_stmt = $conn->query("SELECT COUNT(*) FROM products");
$total_items = $total_stmt->fetchColumn();
$total_pages = ceil($total_items / $per_page);
?>

<?php include 'layout/header.php'; ?>

<div class="container py-5">
    <h2 class="text-center mb-4 text-pink fw-bold font-elegant">🔧 Quản trị Admin - Quản lý Sản phẩm</h2>

    <?php if ($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>

    <!-- Form Thêm / Sửa -->
    <div class="card mb-5 shadow-sm border-0">
        <div class="card-body p-4">
            <h4 class="fw-bold mb-4"><?= $edit ? '<i class="fa-solid fa-pen-to-square me-2 text-warning"></i>Sửa sản phẩm' : '<i class="fa-solid fa-plus me-2 text-pink"></i>Thêm sản phẩm mới' ?></h4>
            <form method="POST" enctype="multipart/form-data" class="row g-3">
                <?= csrf_input() ?>
                <?php if ($edit): ?>
                    <input type="hidden" name="id" value="<?= $edit['id'] ?>">
                    <input type="hidden" name="current_image" value="<?= $edit['image'] ?>">
                <?php endif; ?>

                <div class="col-md-6">
                    <label class="form-label fw-medium">Tên sản phẩm</label>
                    <input name="name" class="form-control" value="<?= htmlspecialchars($edit['name'] ?? '') ?>" placeholder="VD: Son Dior" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-medium">Giá (VNĐ)</label>
                    <input name="price" type="number" class="form-control" value="<?= $edit['price'] ?? '' ?>" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-medium">Giá khuyến mãi</label>
                    <input name="sale_price" type="number" class="form-control" value="<?= $edit['sale_price'] ?? '' ?>" placeholder="Để trống nếu không sale">
                    <div class="form-text small">Phải < giá gốc.</div>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-medium">Số lượng tồn</label>
                    <input name="stock" type="number" class="form-control" value="<?= $edit['stock'] ?? '0' ?>" required min="0">
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-medium">Trạng thái</label>
                    <select name="status" class="form-select">
                        <option value="active" <?= (isset($edit['status']) && $edit['status'] == 'active') ? 'selected' : '' ?>>Đang bán</option>
                        <option value="out_of_stock" <?= (isset($edit['status']) && $edit['status'] == 'out_of_stock') ? 'selected' : '' ?>>Hết hàng</option>
                        <option value="hidden" <?= (isset($edit['status']) && $edit['status'] == 'hidden') ? 'selected' : '' ?>>Ẩn</option>
                    </select>
                </div>

                <div class="col-md-2 d-flex align-items-center">
                    <div class="form-check form-switch mt-3">
                        <input class="form-check-input" type="checkbox" name="is_featured" id="is_featured" <?= (isset($edit['is_featured']) && $edit['is_featured']) ? 'checked' : '' ?>>
                        <label class="form-check-label fw-medium" for="is_featured">Nổi bật</label>
                    </div>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-medium">Danh mục</label>
                    <select name="category_id" class="form-select" required>
                        <option value="">-- Chọn danh mục --</option>
                        <?php
                        $cat_stmt = $conn->query("SELECT * FROM categories ORDER BY name");
                        while ($cat = $cat_stmt->fetch()):
                            $selected = (isset($edit['category_id']) && $edit['category_id'] == $cat['id']) ? 'selected' : '';
                        ?>
                            <option value="<?= $cat['id'] ?>" <?= $selected ?>><?= htmlspecialchars($cat['name']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-medium">Ảnh chính (Tải lên file)</label>
                    <input type="file" name="image" class="form-control" accept="image/png, image/jpeg, image/webp">
                    <?php if ($edit && $edit['image']): ?>
                        <div class="mt-2 small text-muted">Ảnh hiện tại: <?= htmlspecialchars($edit['image'] ?? '') ?></div>
                    <?php endif; ?>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-medium">Ảnh phụ (Gallery - Chọn nhiều file)</label>
                    <input type="file" name="extra_images[]" class="form-control" accept="image/png, image/jpeg, image/webp" multiple>
                    <?php if ($edit): ?>
                        <div class="mt-2 d-flex flex-wrap gap-2">
                            <?php 
                            $gallery = $conn->prepare("SELECT * FROM product_images WHERE product_id = ?");
                            $gallery->execute([$edit['id']]);
                            while($img = $gallery->fetch()):
                            ?>
                                <div class="position-relative">
                                    <img src="uploads/<?= $img['image_name'] ?>" style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px;">
                                    <a href="?page=admin&edit=<?= $edit['id'] ?>&delete_img=<?= $img['id'] ?>" 
                                       class="position-absolute top-0 end-0 bg-danger text-white rounded-circle d-flex align-items-center justify-content-center" 
                                       style="width: 18px; height: 18px; font-size: 10px; text-decoration: none;"
                                       onclick="return confirm('Xóa ảnh này?')">×</a>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="col-12">
                    <label class="form-label fw-medium">Mô tả sản phẩm</label>
                    <textarea name="description" class="form-control" rows="3" placeholder="Nhập mô tả chi tiết sản phẩm..."><?= htmlspecialchars($edit['description'] ?? '') ?></textarea>
                </div>

                <div class="col-12 mt-4">
                    <button type="submit" name="<?= $edit ? 'update' : 'add' ?>" class="btn btn-pink px-5">
                         <?= $edit ? 'Cập nhật sản phẩm' : 'Thêm sản phẩm' ?>
                    </button>
                    <?php if ($edit): ?>
                        <a href="?page=admin" class="btn btn-light ms-2">Hủy bỏ</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <!-- Danh sách sản phẩm -->
    <h4 class="fw-bold mb-4 px-2 d-flex justify-content-between align-items-center">
        <span><i class="fa-solid fa-boxes-stacked me-2 text-pink"></i>Danh sách sản phẩm</span>
        <span class="small text-muted fw-normal">Tổng cộng: <?= $total_items ?> sản phẩm</span>
    </h4>
    <?php
    $res = $conn->prepare("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.id DESC LIMIT ? OFFSET ?");
    $res->bindValue(1, $per_page, PDO::PARAM_INT);
    $res->bindValue(2, $offset, PDO::PARAM_INT);
    $res->execute();
    ?>
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Sản phẩm</th>
                            <th>Giá</th>
                            <th>Kho</th>
                            <th>Danh mục</th>
                            <th>Trạng thái</th>
                            <th class="text-center">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($p = $res->fetch()): ?>
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center gap-3">
                                    <?php 
                                    $img_src = (file_exists('uploads/' . $p['image']) && !empty($p['image'])) ? 'uploads/' . $p['image'] : 'images/' . $p['image'];
                                    ?>
                                    <img src="<?= $img_src ?>" style="width:60px;height:60px;object-fit:cover;border-radius:12px;" alt="">
                                    <div>
                                        <div class="fw-bold"><?= htmlspecialchars($p['name']) ?></div>
                                        <div class="d-flex gap-2 align-items-center">
                                            <span class="text-muted small">ID: #<?= $p['id'] ?></span>
                                            <?php if($p['is_featured']): ?>
                                                <span class="badge bg-danger rounded-pill" style="font-size: 0.6rem;">NỔI BẬT</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="fw-bold text-pink">
                                <?php if($p['sale_price']): ?>
                                    <div class="text-muted text-decoration-line-through small" style="font-size: 0.8rem;"><?= number_format($p['price']) ?> đ</div>
                                    <div><?= number_format($p['sale_price']) ?> đ</div>
                                <?php else: ?>
                                    <?= number_format($p['price']) ?> đ
                                <?php endif; ?>
                            </td>
                            <td class="fw-bold"><?= $p['stock'] ?></td>
                            <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($p['category_name'] ?? 'Chưa có') ?></span></td>
                            <td>
                                <?php if($p['status'] == 'active'): ?>
                                    <span class="badge bg-success">Đang bán</span>
                                <?php elseif($p['status'] == 'out_of_stock'): ?>
                                    <span class="badge bg-warning text-dark">Hết hàng</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Ẩn</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center pe-4">
                                <a href="?page=admin&edit=<?= $p['id'] ?>" class="btn btn-sm btn-light text-warning" title="Sửa">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </a>
                                <a href="?page=admin&delete=<?= $p['id'] ?>" class="btn btn-sm btn-light text-danger" onclick="return confirm('Xóa sản phẩm này?')" title="Xóa">
                                    <i class="fa-solid fa-trash-can"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Phân trang UI -->
    <?php if ($total_pages > 1): ?>
    <nav class="mt-4">
        <ul class="pagination justify-content-center">
            <li class="page-item <?= ($current_page <= 1) ? 'disabled' : '' ?>">
                <a class="page-link" href="?page=admin&p=<?= $current_page - 1 ?>">Trước</a>
            </li>
            
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <li class="page-item <?= ($i == $current_page) ? 'active' : '' ?>">
                <a class="page-link <?= ($i == $current_page) ? 'bg-pink border-pink' : '' ?>" href="?page=admin&p=<?= $i ?>"><?= $i ?></a>
            </li>
            <?php endfor; ?>

            <li class="page-item <?= ($current_page >= $total_pages) ? 'disabled' : '' ?>">
                <a class="page-link" href="?page=admin&p=<?= $current_page + 1 ?>">Sau</a>
            </li>
        </ul>
    </nav>
    <?php endif; ?>
</div>

<?php include 'layout/footer.php'; ?>
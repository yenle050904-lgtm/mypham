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
    $cat    = (int)$_POST['category_id'];
    $desc   = trim($_POST['description']);
    $status = $_POST['status'] ?? 'active';
    
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
            $stmt = $conn->prepare("INSERT INTO products (name, price, image, category_id, description, status) VALUES (?, ?, ?, ?, ?, ?)");
            if ($stmt->execute([$name, $price, $image_name, $cat, $desc, $status])) {
                $success = "Thêm sản phẩm thành công!";
            } else {
                $error = "Có lỗi xảy ra khi thêm sản phẩm.";
            }
        } else {
            $id = (int)$_POST['id'];
            $stmt = $conn->prepare("UPDATE products SET name = ?, price = ?, image = ?, category_id = ?, description = ?, status = ? WHERE id = ?");
            if ($stmt->execute([$name, $price, $image_name, $cat, $desc, $status, $id])) {
                $success = "Cập nhật sản phẩm thành công!";
            } else {
                $error = "Có lỗi xảy ra khi cập nhật sản phẩm.";
            }
        }
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
                <div class="col-md-3">
                    <label class="form-label fw-medium">Giá (VNĐ)</label>
                    <input name="price" type="number" class="form-control" value="<?= $edit['price'] ?? '' ?>" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-medium">Trạng thái</label>
                    <select name="status" class="form-select">
                        <option value="active" <?= (isset($edit['status']) && $edit['status'] == 'active') ? 'selected' : '' ?>>Đang bán (Active)</option>
                        <option value="out_of_stock" <?= (isset($edit['status']) && $edit['status'] == 'out_of_stock') ? 'selected' : '' ?>>Hết hàng (Out of Stock)</option>
                        <option value="hidden" <?= (isset($edit['status']) && $edit['status'] == 'hidden') ? 'selected' : '' ?>>Ẩn (Hidden)</option>
                    </select>
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
                    <label class="form-label fw-medium">Ảnh sản phẩm (Tải lên file)</label>
                    <input type="file" name="image" class="form-control" accept="image/png, image/jpeg, image/webp">
                    <?php if ($edit && $edit['image']): ?>
                        <div class="mt-2 small text-muted">Ảnh hiện tại: <?= htmlspecialchars($edit['image'] ?? '') ?></div>
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
    <h4 class="fw-bold mb-4 px-2"><i class="fa-solid fa-boxes-stacked me-2 text-pink"></i>Danh sách sản phẩm hiện tại</h4>
    <?php
    $res = $conn->query("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.id DESC");
    ?>
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Sản phẩm</th>
                            <th>Giá</th>
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
                                        <div class="text-muted small">ID: #<?= $p['id'] ?></div>
                                    </div>
                                </div>
                            </td>
                            <td class="fw-bold text-pink"><?= number_format($p['price']) ?> đ</td>
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
</div>

<?php include 'layout/footer.php'; ?>
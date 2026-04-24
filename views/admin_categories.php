<?php
// ==================== XỬ LÝ TRƯỚC KHI XUẤT HTML ====================
$success = '';
$error = '';

// Kiểm tra quyền admin
if (!isset($_SESSION['user']) || $_SESSION['user'] !== 'admin') {
    die('<div class="container py-5"><div class="alert alert-danger text-center">Bạn không có quyền truy cập trang Admin!</div></div>');
}

// Xử lý Thêm / Sửa danh mục
if (isset($_POST['add']) || isset($_POST['update'])) {
    // CSRF tự động kiểm tra
    
    $name = trim($_POST['name']);

    if (isset($_POST['add'])) {
        $stmt = $conn->prepare("INSERT INTO categories (name) VALUES (?)");
        if ($stmt->execute([$name])) {
            $success = "Thêm danh mục thành công!";
        } else {
            $error = "Có lỗi xảy ra khi thêm danh mục.";
        }
    } else {
        $id = (int)$_POST['id'];
        $stmt = $conn->prepare("UPDATE categories SET name = ? WHERE id = ?");
        if ($stmt->execute([$name, $id])) {
            $success = "Cập nhật danh mục thành công!";
        } else {
            $error = "Có lỗi xảy ra khi cập nhật danh mục.";
        }
    }
}

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    
    // Kiểm tra xem có sản phẩm nào thuộc danh mục này không
    $stmt = $conn->prepare("SELECT id FROM products WHERE category_id = ? LIMIT 1");
    $stmt->execute([$id]);
    if ($stmt->fetch()) {
        $error = "Không thể xóa danh mục này vì vẫn còn sản phẩm thuộc danh mục!";
    } else {
        $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
        if ($stmt->execute([$id])) {
            $success = "Xóa danh mục thành công!";
        } else {
            $error = "Có lỗi xảy ra khi xóa danh mục.";
        }
    }
}

// Lấy dữ liệu để sửa
$edit = null;
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM categories WHERE id = ?");
    $stmt->execute([$id]);
    $edit = $stmt->fetch();
}
?>

<?php include 'layout/header.php'; ?>

<div class="container py-5">
    <h2 class="text-center mb-4 text-pink fw-bold font-elegant">🏷️ Quản trị Admin - Quản lý Danh mục</h2>

    <?php if ($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>

    <div class="row">
        <!-- Danh mục Form -->
        <div class="col-md-4">
            <div class="card mb-4 shadow-sm border-0">
                <div class="card-body p-4">
                    <h4 class="fw-bold mb-4"><?= $edit ? '<i class="fa-solid fa-pen-to-square me-2 text-warning"></i>Sửa danh mục' : '<i class="fa-solid fa-plus me-2 text-pink"></i>Thêm mới' ?></h4>
                    <form method="POST" class="row g-3">
                        <?= csrf_input() ?>
                        <?php if ($edit): ?>
                            <input type="hidden" name="id" value="<?= $edit['id'] ?>">
                        <?php endif; ?>

                        <div class="col-12">
                            <label class="form-label fw-medium">Tên danh mục</label>
                            <input name="name" class="form-control" value="<?= htmlspecialchars($edit['name'] ?? '') ?>" placeholder="VD: Son môi" required>
                        </div>

                        <div class="col-12 mt-4">
                            <button type="submit" name="<?= $edit ? 'update' : 'add' ?>" class="btn btn-pink w-100 py-2">
                                <?= $edit ? 'Cập nhật danh mục' : 'Thêm danh mục' ?>
                            </button>
                            <?php if ($edit): ?>
                                <a href="?page=admin_categories" class="btn btn-light w-100 mt-2">Hủy bỏ</a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Bảng danh mục -->
        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <h4 class="fw-bold mb-4"><i class="fa-solid fa-list me-2 text-pink"></i>Danh sách danh mục</h4>
                    <?php
                    $res = $conn->query("SELECT * FROM categories ORDER BY id DESC");
                    ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="bg-light">
                                <tr>
                                    <th width="80">ID</th>
                                    <th>Tên danh mục</th>
                                    <th class="text-center" width="150">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($c = $res->fetch()): ?>
                                <tr>
                                    <td class="text-muted">#<?= $c['id'] ?></td>
                                    <td class="fw-bold"><?= htmlspecialchars($c['name']) ?></td>
                                    <td class="text-center">
                                        <a href="?page=admin_categories&edit=<?= $c['id'] ?>" class="btn btn-sm btn-light text-warning" title="Sửa">
                                            <i class="fa-solid fa-pen"></i>
                                        </a>
                                        <a href="?page=admin_categories&delete=<?= $c['id'] ?>" class="btn btn-sm btn-light text-danger" onclick="return confirm('Xóa danh mục này?')" title="Xóa">
                                            <i class="fa-solid fa-trash"></i>
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
    </div>
</div>

<?php include 'layout/footer.php'; ?>

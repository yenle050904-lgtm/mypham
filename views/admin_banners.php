<?php
$success = '';
$error = '';

if (($_SESSION['role'] ?? '') !== 'admin') {
    die('<div class="container py-5"><div class="alert alert-danger text-center">Bạn không có quyền truy cập trang Quản lý Banner!</div></div>');
}

// Xử lý Thêm / Sửa Banner
if (isset($_POST['add']) || isset($_POST['update'])) {
    $title       = trim($_POST['title']);
    $subtitle    = trim($_POST['subtitle']);
    $btn_text    = trim($_POST['button_text']);
    $btn_link    = trim($_POST['button_link']);
    $is_active   = isset($_POST['is_active']) ? 1 : 0;
    $sort_order  = (int)$_POST['sort_order'];
    $image_bg    = $_POST['current_image'] ?? '';

    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, $allowed)) {
            $new_name = 'banner_' . time() . '_' . uniqid() . '.' . $ext;
            if (move_uploaded_file($_FILES['image']['tmp_name'], 'uploads/' . $new_name)) {
                $image_bg = $new_name;
            }
        } else {
            $error = "Chỉ chấp nhận ảnh định dạng JPG, PNG, WEBP.";
        }
    }

    if (empty($error)) {
        if (isset($_POST['add'])) {
            $stmt = $conn->prepare("INSERT INTO banners (title, subtitle, button_text, button_link, image_bg, is_active, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$title, $subtitle, $btn_text, $btn_link, $image_bg, $is_active, $sort_order]);
            $success = "Thêm banner thành công!";
        } else {
            $id = (int)$_POST['id'];
            $stmt = $conn->prepare("UPDATE banners SET title=?, subtitle=?, button_text=?, button_link=?, image_bg=?, is_active=?, sort_order=? WHERE id=?");
            $stmt->execute([$title, $subtitle, $btn_text, $btn_link, $image_bg, $is_active, $sort_order, $id]);
            $success = "Cập nhật banner thành công!";
        }
    }
}

// Xử lý Xóa
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $conn->prepare("SELECT image_bg FROM banners WHERE id = ?");
    $stmt->execute([$id]);
    $b = $stmt->fetch();
    if ($b && $b['image_bg'] && file_exists('uploads/' . $b['image_bg'])) unlink('uploads/' . $b['image_bg']);
    
    $conn->prepare("DELETE FROM banners WHERE id = ?")->execute([$id]);
    $success = "Đã xóa banner.";
}

$edit = null;
if (isset($_GET['edit'])) {
    $stmt = $conn->prepare("SELECT * FROM banners WHERE id = ?");
    $stmt->execute([(int)$_GET['edit']]);
    $edit = $stmt->fetch();
}

$banners = $conn->query("SELECT * FROM banners ORDER BY sort_order ASC, id DESC")->fetchAll();

include 'layout/header.php';
?>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-pink mb-0"><i class="fa-solid fa-panorama me-2"></i>Quản lý Banners</h2>
        <a href="?page=admin" class="btn btn-outline-pink rounded-pill px-4"><i class="fa-solid fa-arrow-left me-2"></i>Quay lại Admin</a>
    </div>

    <?php if ($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>

    <div class="card border-0 shadow-sm rounded-4 mb-5">
        <div class="card-body p-4">
            <h5 class="fw-bold mb-4"><?= $edit ? 'Cập nhật Banner' : 'Thêm Banner Mới' ?></h5>
            <form method="POST" enctype="multipart/form-data" class="row g-3">
                <?= csrf_input() ?>
                <?php if ($edit): ?>
                    <input type="hidden" name="id" value="<?= $edit['id'] ?>">
                    <input type="hidden" name="current_image" value="<?= $edit['image_bg'] ?>">
                <?php endif; ?>

                <div class="col-md-6">
                    <label class="form-label fw-bold">Tiêu đề lớn</label>
                    <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($edit['title'] ?? '') ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Nội dung phụ</label>
                    <input type="text" name="subtitle" class="form-control" value="<?= htmlspecialchars($edit['subtitle'] ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Text trên nút</label>
                    <input type="text" name="button_text" class="form-control" value="<?= htmlspecialchars($edit['button_text'] ?? 'Mua sắm ngay') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Link khi nhấn nút</label>
                    <input type="text" name="button_link" class="form-control" value="<?= htmlspecialchars($edit['button_link'] ?? '#products') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Ảnh nền</label>
                    <input type="file" name="image" class="form-control">
                </div>
                <div class="col-md-1">
                    <label class="form-label fw-bold">Thứ tự</label>
                    <input type="number" name="sort_order" class="form-control" value="<?= $edit['sort_order'] ?? 0 ?>">
                </div>
                <div class="col-md-2 d-flex align-items-end mb-1 ps-lg-4">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="is_active" id="is_active" <?= (isset($edit['is_active']) && !$edit['is_active']) ? '' : 'checked' ?>>
                        <label class="form-check-label fw-bold" for="is_active">Hoạt động</label>
                    </div>
                </div>

                <div class="col-12 mt-4 text-center">
                    <button type="submit" name="<?= $edit ? 'update' : 'add' ?>" class="btn btn-pink px-5 py-2 rounded-pill shadow-sm">
                        <?= $edit ? 'Lưu thay đổi' : 'Tạo Banner' ?>
                    </button>
                    <?php if ($edit): ?>
                        <a href="?page=admin_banners" class="btn btn-light ms-2 px-5 py-2 rounded-pill">Hủy</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <div class="table-responsive rounded-4 shadow-sm">
        <table class="table table-hover align-middle bg-white mb-0">
            <thead class="bg-light">
                <tr>
                    <th class="ps-4">Preview</th>
                    <th>Nội dung</th>
                    <th>Thứ tự</th>
                    <th>Trạng thái</th>
                    <th class="text-center pe-4">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($banners as $b): ?>
                <tr>
                    <td class="ps-4">
                        <?php if($b['image_bg']): ?>
                            <img src="uploads/<?= $b['image_bg'] ?>" style="width: 120px; height: 60px; object-fit: cover;" class="rounded shadow-sm">
                        <?php else: ?>
                            <div class="bg-light rounded d-flex align-items-center justify-content-center text-muted" style="width: 120px; height: 60px; font-size: 10px;">Không có nền</div>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="fw-bold"><?= htmlspecialchars($b['title']) ?></div>
                        <div class="small text-muted"><?= htmlspecialchars($b['subtitle']) ?></div>
                    </td>
                    <td><?= $b['sort_order'] ?></td>
                    <td>
                        <span class="badge rounded-pill <?= $b['is_active'] ? 'bg-success' : 'bg-secondary' ?>">
                            <?= $b['is_active'] ? 'Active' : 'Hidden' ?>
                        </span>
                    </td>
                    <td class="text-center pe-4">
                        <a href="?page=admin_banners&edit=<?= $b['id'] ?>" class="btn btn-sm btn-light text-warning me-2 shadow-sm"><i class="fa-solid fa-pen"></i></a>
                        <a href="?page=admin_banners&delete=<?= $b['id'] ?>" class="btn btn-sm btn-light text-danger shadow-sm" onclick="return confirm('Xóa banner này?')"><i class="fa-solid fa-trash"></i></a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if(empty($banners)): ?>
                    <tr><td colspan="5" class="py-5 text-center text-muted italic">Chưa có banner nào. Banner mặc định sẽ được sử dụng.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'layout/footer.php'; ?>

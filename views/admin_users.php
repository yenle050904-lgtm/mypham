<?php
// ==================== XỬ LÝ TRƯỚC KHI XUẤT HTML ====================
$success = '';
$error = '';

// Kiểm tra quyền admin
if (!isset($_SESSION['user']) || $_SESSION['user'] !== 'admin') {
    die('<div class="container py-5"><div class="alert alert-danger text-center">Bạn không có quyền truy cập trang Admin!</div></div>');
}

// Xóa người dùng
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    
    // Lấy thông tin user trước (PDO)
    $stmt = $conn->prepare("SELECT username FROM users WHERE id = ?");
    $stmt->execute([$id]);
    $u = $stmt->fetch();

    if ($u) {
        if ($u['username'] === 'admin') {
            $error = "Không thể xóa tài khoản Admin hệ thống!";
        } else {
            $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
            if ($stmt->execute([$id])) {
                $success = "Xóa người dùng thành công!";
            } else {
                $error = "Có lỗi xảy ra khi xóa người dùng.";
            }
        }
    }
}
?>

<?php include 'layout/header.php'; ?>

<div class="container py-5">
    <h2 class="text-center mb-4 text-pink">👥 Quản trị Admin - Quản lý Người dùng</h2>

    <?php if ($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>

    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <h4 class="fw-bold mb-4"><i class="fa-solid fa-users me-2 text-pink"></i>Danh sách thành viên</h4>
            <?php
            $res = $conn->query("SELECT * FROM users ORDER BY id DESC");
            ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="bg-light">
                        <tr>
                            <th width="80">ID</th>
                            <th>Tên đăng nhập</th>
                            <th>Vai trò</th>
                            <th class="text-center" width="150">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($u = $res->fetch()): ?>
                        <tr>
                            <td class="text-muted">#<?= $u['id'] ?></td>
                            <td class="fw-bold"><?= htmlspecialchars($u['username']) ?></td>
                            <td>
                                <span class="badge bg-<?= ($u['username'] === 'admin') ? 'danger' : 'primary' ?>">
                                    <?= ($u['username'] === 'admin') ? 'ADMIN' : 'USER' ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <?php if ($u['username'] !== 'admin'): ?>
                                    <a href="?page=admin_users&delete=<?= $u['id'] ?>" class="btn btn-sm btn-light text-danger" onclick="return confirm('⚠️ Xóa tài khoản này?')" title="Xóa">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted small"><i class="fa-solid fa-lock me-1"></i>Hệ thống</span>
                                <?php endif; ?>
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

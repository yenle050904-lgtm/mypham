<?php
// Kiểm tra quyền admin
if (($_SESSION['role'] ?? '') !== 'admin') {
    die('<div class="container py-5"><div class="alert alert-danger text-center">Bạn không có quyền truy cập!</div></div>');
}

$success = '';
$error = '';

// Xử lý đánh dấu đã đọc
if (isset($_GET['read'])) {
    $id = (int)$_GET['read'];
    $stmt = $conn->prepare("UPDATE contacts SET is_read = 1 WHERE id = ?");
    if ($stmt->execute([$id])) {
        $success = "Đã đánh dấu tin nhắn là đã đọc.";
    }
}

// Xử lý Xóa
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM contacts WHERE id = ?");
    if ($stmt->execute([$id])) {
        $success = "Đã xóa tin nhắn thành công.";
    }
}

// Lấy danh sách liên hệ
$stmt = $conn->query("SELECT * FROM contacts ORDER BY is_read ASC, created_at DESC");
$contacts = $stmt->fetchAll();
?>

<?php include 'layout/header.php'; ?>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-pink font-elegant mb-0"><i class="fa-solid fa-envelope-open-text me-2"></i>Quản lý liên hệ</h2>
        <span class="badge bg-light text-dark border">Tổng cộng: <?= count($contacts) ?> tin nhắn</span>
    </div>

    <?php if ($success): ?><div class="alert alert-success shadow-sm"><?= $success ?></div><?php endif; ?>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-dark">
                        <tr>
                            <th class="ps-4">Họ tên</th>
                            <th>Email/SĐT</th>
                            <th>Chủ đề</th>
                            <th>Ngày gửi</th>
                            <th class="text-center">Trạng thái</th>
                            <th class="text-center pe-4">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($contacts)): ?>
                            <tr><td colspan="6" class="text-center py-5 text-muted">Chưa có tin nhắn liên hệ nào.</td></tr>
                        <?php else: ?>
                            <?php foreach ($contacts as $c): ?>
                            <tr class="<?= $c['is_read'] == 0 ? 'fw-bold bg-light-pink' : '' ?>">
                                <td class="ps-4">
                                    <div class="d-flex align-items-center">
                                        <div class="bg-pink text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 35px; height: 35px;">
                                            <?= strtoupper(substr($c['fullname'], 0, 1)) ?>
                                        </div>
                                        <?= htmlspecialchars($c['fullname']) ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="small"><?= htmlspecialchars($c['email']) ?></div>
                                    <div class="small text-muted"><?= htmlspecialchars($c['phone'] ?: '---') ?></div>
                                </td>
                                <td><?= htmlspecialchars($c['subject']) ?></td>
                                <td class="small text-muted"><?= date('d/m/Y H:i', strtotime($c['created_at'])) ?></td>
                                <td class="text-center">
                                    <?php if ($c['is_read'] == 0): ?>
                                        <span class="badge bg-warning text-dark">Chưa đọc</span>
                                    <?php else: ?>
                                        <span class="badge bg-light text-dark border">Đã đọc</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center pe-4">
                                    <div class="d-flex justify-content-center gap-2">
                                        <button type="button" class="btn btn-sm btn-info text-white" 
                                                data-bs-toggle="modal" data-bs-target="#msgModal<?= $c['id'] ?>" title="Xem nội dung">
                                            <i class="fa-solid fa-eye"></i>
                                        </button>
                                        <?php if ($c['is_read'] == 0): ?>
                                            <a href="?page=admin_contacts&read=<?= $c['id'] ?>" class="btn btn-sm btn-success" title="Đánh dấu đã đọc">
                                                <i class="fa-solid fa-check"></i>
                                            </a>
                                        <?php endif; ?>
                                        <a href="?page=admin_contacts&delete=<?= $c['id'] ?>" class="btn btn-sm btn-light text-danger" 
                                           onclick="return confirm('Bạn có chắc chắn muốn xóa tin nhắn này?')" title="Xóa">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </a>
                                    </div>

                                    <!-- Modal Xem Nội Dung -->
                                    <div class="modal fade" id="msgModal<?= $c['id'] ?>" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content border-0 rounded-4 shadow">
                                                <div class="modal-header border-0 pb-0">
                                                    <h5 class="modal-title fw-bold">Nội dung liên hệ</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body text-start p-4">
                                                    <div class="mb-3">
                                                        <label class="small fw-bold text-muted text-uppercase d-block mb-1">Chủ đề</label>
                                                        <div class="p-2 bg-light rounded-3"><?= htmlspecialchars($c['subject']) ?></div>
                                                    </div>
                                                    <div class="mb-0">
                                                        <label class="small fw-bold text-muted text-uppercase d-block mb-1">Tin nhắn</label>
                                                        <div class="p-3 border rounded-3 bg-white" style="min-height: 100px; white-space: pre-wrap;"><?= htmlspecialchars($c['message']) ?></div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer border-0 pt-0">
                                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Đóng</button>
                                                    <?php if ($c['is_read'] == 0): ?>
                                                        <a href="?page=admin_contacts&read=<?= $c['id'] ?>" class="btn btn-pink">Đánh dấu đã đọc</a>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
.bg-light-pink { background-color: rgba(233, 30, 99, 0.02); }
</style>

<?php include 'layout/footer.php'; ?>

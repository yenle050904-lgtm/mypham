<?php
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = trim($_POST['fullname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if (empty($fullname) || empty($email) || empty($message)) {
        $error = "Vui lòng điền đầy đủ các trường bắt buộc!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Email không hợp lệ!";
    } else {
        $stmt = $conn->prepare("INSERT INTO contacts (fullname, email, phone, subject, message) VALUES (?, ?, ?, ?, ?)");
        if ($stmt->execute([$fullname, $email, $phone, $subject, $message])) {
            $success = "Cảm ơn bạn! Tin nhắn của bạn đã được gửi thành công.";
        } else {
            $error = "Có lỗi xảy ra, vui lòng thử lại sau.";
        }
    }
}

include 'layout/header.php'; 
?>

<div class="container py-5">
    <div class="text-center mb-5">
        <h1 class="fw-bold">Liên hệ với <span class="text-pink font-elegant">chúng tôi</span></h1>
        <p class="text-secondary">Bạn có câu hỏi? Đội ngũ của chúng tôi luôn sẵn sàng hỗ trợ.</p>
    </div>

    <div class="row g-4 justify-content-center">
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 p-4 h-100">
                <h5 class="fw-bold mb-4">Thông tin liên lạc</h5>
                
                <div class="d-flex align-items-center mb-4">
                    <div class="bg-pink-light p-3 rounded-3 me-3 text-pink">
                        <i class="fa-solid fa-location-dot fs-4"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-0">Địa chỉ</h6>
                        <p class="mb-0 small text-muted">123 Đường ABC, Quận 1, Tp.HCM</p>
                    </div>
                </div>

                <div class="d-flex align-items-center mb-4">
                    <div class="bg-pink-light p-3 rounded-3 me-3 text-pink">
                        <i class="fa-solid fa-phone fs-4"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-0">Điện thoại</h6>
                        <p class="mb-0 small text-muted">0123 456 789 (Hotline 24/7)</p>
                    </div>
                </div>

                <div class="d-flex align-items-center mb-4">
                    <div class="bg-pink-light p-3 rounded-3 me-3 text-pink">
                        <i class="fa-solid fa-envelope fs-4"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-0">Email</h6>
                        <p class="mb-0 small text-muted">shop@myphamxinh.vn</p>
                    </div>
                </div>
                
                <hr class="my-4">
                <h6 class="fw-bold mb-3">Thời gian làm việc</h6>
                <p class="small text-muted mb-1">Thứ 2 - Thứ 6: 8:00 - 21:00</p>
                <p class="small text-muted">Thứ 7 - CN: 9:00 - 18:00</p>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-4 p-4">
                <h5 class="fw-bold mb-4">Gửi tin nhắn cho chúng tôi</h5>
                
                <?php if ($success): ?><div class="alert alert-success shadow-sm"><?= $success ?></div><?php endif; ?>
                <?php if ($error): ?><div class="alert alert-danger shadow-sm"><?= $error ?></div><?php endif; ?>

                <form method="POST">
                    <?= csrf_input() ?>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Họ và tên</label>
                            <input type="text" name="fullname" class="form-control" placeholder="Nguyễn Văn A" required value="<?= htmlspecialchars($_POST['fullname'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Email</label>
                            <input type="email" name="email" class="form-control" placeholder="email@example.com" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold">Số điện thoại (tùy chọn)</label>
                            <input type="text" name="phone" class="form-control" placeholder="0123 456 789" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold">Tiêu đề</label>
                            <input type="text" name="subject" class="form-control" placeholder="Tôi cần tư vấn về..." required value="<?= htmlspecialchars($_POST['subject'] ?? '') ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold">Nội dung tin nhắn</label>
                            <textarea name="message" class="form-control" rows="5" placeholder="Viết tin nhắn của bạn tại đây..." required><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
                        </div>
                        <div class="col-12 mt-4">
                            <button type="submit" class="btn btn-pink px-5 py-2 w-100 fs-5">Gửi ngay <i class="fa-solid fa-paper-plane ms-2"></i></button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
.bg-pink-light { background-color: rgba(233, 30, 99, 0.05); }
</style>

<?php include 'layout/footer.php'; ?>

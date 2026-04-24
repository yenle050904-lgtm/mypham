<?php include 'layout/header.php'; ?>

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
                <form action="?page=home&msg=Cảm ơn bạn! Chúng tôi sẽ phản hồi sớm nhất." method="POST">
                    <?= csrf_input() ?>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Họ và tên</label>
                            <input type="text" class="form-control" placeholder="Nguyễn Văn A" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Email</label>
                            <input type="email" class="form-control" placeholder="email@example.com" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold">Tiêu đề</label>
                            <input type="text" class="form-control" placeholder="Tôi cần tư vấn về..." required>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold">Nội dung tin nhắn</label>
                            <textarea class="form-control" rows="5" placeholder="Viết tin nhắn của bạn tại đây..." required></textarea>
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

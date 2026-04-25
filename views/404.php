<?php 
// Trang lỗi 404 custom cho Mỹ Phẩm Xinh
include 'layout/header.php'; 
?>

<div class="container py-5 mt-5">
    <div class="row justify-content-center text-center py-5">
        <div class="col-lg-6">
            <div class="mb-4">
                <h1 class="display-1 fw-bold text-pink font-elegant animate-bounce" style="font-size: 8rem; opacity: 0.8;">404</h1>
            </div>
            <h2 class="fw-bold mb-3">Oops! Trang không tìm thấy!</h2>
            <p class="text-muted mb-5 fs-5">
                Rất tiếc, trang bạn đang tìm kiếm không tồn tại hoặc đã được chuyển sang một địa chỉ khác. 
                Hãy quay lại trang chủ để tiếp tục khám phá những sản phẩm làm đẹp tuyệt vời nhé!
            </p>
            <div class="d-flex flex-column flex-md-row gap-3 justify-content-center">
                <a href="?page=home" class="btn btn-pink px-5 py-3 rounded-pill shadow-lg">
                    <i class="fa-solid fa-house me-2"></i>Quay lại Trang chủ
                </a>
                <a href="?page=contact" class="btn btn-outline-secondary px-5 py-3 rounded-pill">
                    Liên hệ hỗ trợ
                </a>
            </div>
        </div>
    </div>
</div>

<style>
    @keyframes bounce {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-20px); }
    }
    .animate-bounce {
        animation: bounce 2s infinite ease-in-out;
        display: inline-block;
    }
</style>

<?php include 'layout/footer.php'; ?>

         </main> <!-- Đóng thẻ main từ header -->

        <!-- Toast Container -->
        <div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1100">
            <div id="liveToast" class="toast align-items-center text-white bg-pink border-0" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="4000">
                <div class="d-flex">
                    <div class="toast-body" id="toastMessage">
                        Hành động thành công!
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        </div>

        <footer class="footer mt-auto py-5">
            <div class="container">
                <div class="row g-4 mb-5 text-center text-md-start">
                    <div class="col-lg-4 col-md-6 mb-4 mb-lg-0">
                        <h5 class="fw-bold mb-4 fs-4"><i class="fa-solid fa-wand-magic-sparkles me-2 text-pink"></i>Mỹ Phẩm Xinh</h5>
                        <p class="mb-4 text-secondary">Chúng tôi cung cấp các giải pháp làm đẹp hàng đầu, giúp bạn tự tin và tỏa sáng hơn mỗi ngày với các sản phẩm chính hãng 100%.</p>
                        <div class="d-flex gap-3 justify-content-center justify-content-md-start">
                            <a href="#" class="btn btn-outline-pink btn-sm rounded-circle"><i class="fa-brands fa-facebook-f p-1"></i></a>
                            <a href="#" class="btn btn-outline-pink btn-sm rounded-circle"><i class="fa-brands fa-instagram p-1"></i></a>
                            <a href="#" class="btn btn-outline-pink btn-sm rounded-circle"><i class="fa-brands fa-tiktok p-1"></i></a>
                        </div>
                    </div>
                    <div class="col-lg-2 col-md-6 footer-links">
                        <h6 class="fw-bold text-uppercase small mb-4">Liên kết</h6>
                        <ul class="list-unstyled">
                            <li class="mb-2"><a href="?page=home" class="text-decoration-none text-secondary">Trang chủ</a></li>
                            <li class="mb-2"><a href="?page=gioi_thieu" class="text-decoration-none text-secondary">Giới thiệu</a></li>
                            <li class="mb-2"><a href="?page=contact" class="text-decoration-none text-secondary">Liên hệ</a></li>
                        </ul>
                    </div>
                    <div class="col-lg-3 col-md-6 footer-links">
                        <h6 class="fw-bold text-uppercase small mb-4">Hỗ trợ</h6>
                        <ul class="list-unstyled">
                            <li class="mb-2"><a href="?page=chinh_sach" class="text-decoration-none text-secondary">Chính sách đổi trả</a></li>
                            <li class="mb-2"><a href="#" class="text-decoration-none text-secondary">Thanh toán bảo mật</a></li>
                            <li class="mb-2"><a href="#" class="text-decoration-none text-secondary">Câu hỏi thường gặp</a></li>
                        </ul>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <h6 class="fw-bold text-uppercase small mb-4">Liên hệ</h6>
                        <p class="small text-secondary"><i class="fa-solid fa-location-dot me-2 text-pink"></i> 123 Đường ABC, Quận 1, Tp.HCM</p>
                        <p class="small text-secondary"><i class="fa-solid fa-phone me-2 text-pink"></i> 0123 456 789</p>
                        <p class="small text-secondary"><i class="fa-solid fa-envelope me-2 text-pink"></i> shop@myphamxinh.vn</p>
                    </div>
                </div>
                <hr class="border-secondary opacity-25 mb-4">
                <div class="text-center">
                    <p class="mb-0 small text-secondary">© 2026 Mỹ Phẩm Xinh. All rights reserved. <i class="fa-solid fa-heart text-pink mx-1"></i> Beauty is yours.</p>
                </div>
            </div>
        </footer>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        
        <script>
            // Hàm hiển thị Toast thông báo
            function showToast(message, colorClass = 'bg-pink') {
                const toastEl = document.getElementById('liveToast');
                const toastMsg = document.getElementById('toastMessage');
                toastEl.className = `toast align-items-center text-white ${colorClass} border-0`;
                toastMsg.innerText = message;
                const toast = new bootstrap.Toast(toastEl);
                toast.show();
            }

            // Kiểm tra thông báo từ Session (Flash message)
            <?php if (isset($_SESSION['flash_msg'])): ?>
                showToast("<?= $_SESSION['flash_msg'] ?>");
                <?php unset($_SESSION['flash_msg']); ?>
            <?php endif; ?>

            // Kiểm tra tham số URL (Nếu có)
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('msg')) {
                showToast(urlParams.get('msg'));
            }
        </script>
    </body>
</html>
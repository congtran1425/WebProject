</main>

<footer class="site-footer mt-5 border-top">
    <div class="footer-menu py-4">
        <div class="container">
            <div class="row gy-3">
                <?php
                if (!isset($categories)) {
                    require_once __DIR__ . "/../config/Database.php";
                    require_once __DIR__ . "/../models/Category.php";
                    $db = new Database();
                    $conn = $db->connect();
                    $category_model = new Category($conn);
                    $category_result = $category_model->getAllCategories();
                    $categories = [];
                    if ($category_result) {
                        while ($row = $category_result->fetch_assoc()) {
                            $categories[] = $row;
                        }
                    }
                }

                $column_count = 4;
                $total_categories = count($categories);
                if ($total_categories > 0) {
                    $column_count = min($column_count, $total_categories);
                    $per_column = (int)ceil($total_categories / $column_count);
                    $chunks = array_chunk($categories, $per_column);
                } else {
                    $chunks = [];
                }
                ?>

                <?php foreach ($chunks as $chunk) { ?>
                    <div class="col-6 col-md-2">
                        <ul class="list-unstyled footer-list">
                            <?php foreach ($chunk as $category) { ?>
                                <li>
                                    <a href="index.php?category_id=<?php echo (int)$category["category_id"]; ?>">
                                        <?php echo htmlspecialchars($category["category_name"], ENT_QUOTES, "UTF-8"); ?>
                                    </a>
                                </li>
                            <?php } ?>
                        </ul>
                    </div>
                <?php } ?>

                <div class="col-12 col-md-4">
                    <div class="footer-apps">
                        <div class="text-uppercase small text-muted mb-2">Tải ứng dụng</div>
                        <div class="d-flex gap-2 mb-2">
                            <a class="app-badge" href="#"><span class="brand-e small">N</span> VnNews</a>
                            <a class="app-badge" href="#"><span class="brand-e small">N</span> International</a>
                        </div>
                        <div class="footer-contact">
                            <div class="text-muted small">Liên hệ</div>
                            <div class="d-flex align-items-center gap-2"><i class="bi bi-envelope"></i><a href="#">Tòa soạn</a></div>
                            <div class="d-flex align-items-center gap-2"><i class="bi bi-badge-ad"></i><a href="#">Quảng cáo</a></div>
                            <div class="text-muted small mt-2">Đường dây nóng</div>
                            <div class="fw-semibold">0399.798.259 <span class="text-muted small">(Hà Nội)</span></div>
                            <div class="fw-semibold">0399.798.259 <span class="text-muted small">(TP Hồ Chí Minh)</span></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="footer-legal border-top">
        <div class="container py-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div class="d-flex align-items-center gap-3">
                <span class="text-muted">Báo điện tử</span>
                <span class="brand mark">VN<span class="brand-e">N</span>EWS</span>
            </div>
            <div class="d-flex flex-wrap gap-3">
                <a href="#">Điều khoản sử dụng</a>
                <a href="#">Chính sách bảo mật</a>
                <a href="#">Cookies</a>
                <a href="#">RSS</a>
                <a href="#">Theo dõi</a>
            </div>
            <div class="d-flex gap-3 text-muted">
                <i class="bi bi-facebook"></i>
                <i class="bi bi-youtube"></i>
                <i class="bi bi-tiktok"></i>
            </div>
        </div>
        <div class="container border-top py-3">
            <div class="row gy-3 small text-muted">
                <div class="col-md-4">
                    <div class="fw-semibold text-dark">Báo tiếng Việt nhiều người xem nhất</div>
                    <div>Thuộc Khoa Công nghệ Thông tin Kinh doanh UEH</div>
                    <div>Số giấy phép: 548/GP-BTTTT do Bộ Thông tin và Truyền thông cấp ngày 24/08/2021</div>
                </div>
                <div class="col-md-4">
                    <div class="fw-semibold text-dark">Tổng biên tập: Trần Chí Công</div>
                    <div>Địa chỉ: 279 Nguyễn Tri Phương, Phường 5, Quận 10, Hồ Chí Minh, Việt Nam</div>
                    <div>Điện thoại: 0399 798 259</div>
                    <div>Email: ccongtran.31231027506@ueh.edu.vn</div>
                </div>
                <div class="col-md-4 text-md-end">
                    <div>© 2005-2026. Toàn bộ bản quyền thuộc UEH</div>
                </div>
            </div>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>
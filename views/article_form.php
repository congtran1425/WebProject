<?php include "includes/header.php"; ?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <h2 class="h4 mb-3">Đăng bài viết</h2>
                <p class="text-muted mb-4">Điền đầy đủ thông tin để xuất bản bài viết mới.</p>

                <form method="POST" enctype="multipart/form-data" class="row g-3">
                    <div class="col-12">
                        <label class="form-label">Tiêu đề</label>
                        <input type="text" name="title" class="form-control" required>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Tóm tắt</label>
                        <textarea name="summary" class="form-control" rows="3"></textarea>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Nội dung</label>
                        <textarea name="content" class="form-control" rows="8" required></textarea>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Danh mục</label>
                        <select name="category" class="form-select" required>
                            <option value="">Chọn danh mục</option>
                            <?php foreach ($categories as $category) { ?>
                                <option value="<?php echo (int)$category["category_id"]; ?>">
                                    <?php echo htmlspecialchars($category["category_name"], ENT_QUOTES, "UTF-8"); ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Ảnh đại diện (Thumbnail)</label>
                        <input type="file" name="thumbnail" class="form-control" accept="image/jpeg,image/png,image/webp">
                        <div class="form-text">Định dạng: JPG/PNG/WEBP. Dung lượng tối đa 2MB.</div>
                    </div>

                    <div class="col-12">
                        <div class="thumb-preview border rounded p-3 text-center text-muted">
                            Chưa có ảnh xem trước
                        </div>
                    </div>

                    <div class="col-12 d-flex gap-2">
                        <button type="submit" class="btn btn-danger">Xuất bản</button>
                        <a href="index.php" class="btn btn-outline-secondary">Hủy</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
const fileInput = document.querySelector('input[name="thumbnail"]');
const preview = document.querySelector('.thumb-preview');

if (fileInput && preview) {
    fileInput.addEventListener('change', (event) => {
        const file = event.target.files && event.target.files[0];
        if (!file) {
            preview.textContent = 'Chưa có ảnh xem trước';
            preview.style.backgroundImage = '';
            return;
        }

        if (!file.type.startsWith('image/')) {
            preview.textContent = 'File không hợp lệ';
            preview.style.backgroundImage = '';
            return;
        }

        const reader = new FileReader();
        reader.onload = (e) => {
            preview.textContent = '';
            preview.style.backgroundImage = `url(${e.target.result})`;
            preview.style.backgroundSize = 'cover';
            preview.style.backgroundPosition = 'center';
            preview.style.height = '220px';
        };
        reader.readAsDataURL(file);
    });
}
</script>

<?php include "includes/footer.php"; ?>

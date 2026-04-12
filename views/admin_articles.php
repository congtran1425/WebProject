<?php $basePath = "../"; ?>
<?php include "../includes/header.php"; ?>

<?php $activeTab = "articles"; ?>
<?php include "../views/admin_nav.php"; ?>
<?php include "../views/admin_flash.php"; ?>

<section class="card border-0 shadow-sm admin-section">
    <div class="card-body">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h2 class="h5 mb-0">Bài viết</h2>
            <span class="badge text-bg-warning">Duyệt đăng</span>
        </div>
        <div class="table-responsive">
            <table class="table align-middle admin-table">
                <thead>
                    <tr>
                        <th>Bài viết</th>
                        <th>Tác giả</th>
                        <th>Danh mục</th>
                        <th>Trạng thái</th>
                        <th>Lượt xem</th>
                        <th class="text-end">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($article = $adminArticles->fetch_assoc()) { ?>
                        <tr>
                            <td>
                                <div class="fw-semibold"><?php echo htmlspecialchars($article["title"], ENT_QUOTES, "UTF-8"); ?></div>
                                <div class="small text-muted"><?php echo !empty($article["created_at"]) ? date("d/m/Y H:i", strtotime($article["created_at"])) : ""; ?></div>
                            </td>
                            <td><?php echo htmlspecialchars($article["username"], ENT_QUOTES, "UTF-8"); ?></td>
                            <td><?php echo htmlspecialchars($article["category_name"], ENT_QUOTES, "UTF-8"); ?></td>
                            <td><span class="badge text-bg-light js-status-badge"><?php echo htmlspecialchars($article["status"], ENT_QUOTES, "UTF-8"); ?></span></td>
                            <td><?php echo (int)$article["view_count"]; ?></td>
                            <td>
                                <div class="d-flex gap-2 justify-content-end flex-wrap">
                                    <form method="POST" class="d-flex gap-2" data-admin-form="1">
                                        <input type="hidden" name="action" value="update_article_status">
                                        <input type="hidden" name="article_id" value="<?php echo (int)$article["article_id"]; ?>">
                                        <input type="hidden" name="redirect" value="articles.php">
                                        <select name="status" class="form-select form-select-sm">
                                            <?php foreach (["pending", "published", "archived", "draft"] as $status) { ?>
                                                <option value="<?php echo $status; ?>" <?php echo $article["status"] === $status ? "selected" : ""; ?>>
                                                    <?php echo ucfirst($status); ?>
                                                </option>
                                            <?php } ?>
                                        </select>
                                        <button type="submit" class="btn btn-sm btn-dark">Cập nhật</button>
                                    </form>
                                    <form method="POST" data-admin-form="1">
                                        <input type="hidden" name="action" value="delete_article">
                                        <input type="hidden" name="article_id" value="<?php echo (int)$article["article_id"]; ?>">
                                        <input type="hidden" name="redirect" value="articles.php">
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Xóa</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<?php include "../includes/footer.php"; ?>

<script>
    (function () {
        var forms = document.querySelectorAll("form[data-admin-form=\"1\"]");
        if (!forms.length) {
            return;
        }

        var showAlert = function (message, isSuccess) {
            if (!window.appToast) {
                return;
            }
            window.appToast.show(String(message || "Có lỗi xảy ra."), isSuccess ? "success" : "error");
        };

        forms.forEach(function (form) {
            form.addEventListener("submit", function (event) {
                event.preventDefault();

                var actionField = form.querySelector("input[name=\"action\"]");
                var action = actionField ? actionField.value : "";
                if (action === "delete_article" && !window.confirm("Xóa bài viết này?")) {
                    return;
                }

                var submitBtn = form.querySelector("button[type=\"submit\"]");
                if (submitBtn) {
                    submitBtn.disabled = true;
                }

                var formData = new FormData(form);
                fetch("../api/admin.php", {
                    method: "POST",
                    body: formData
                })
                    .then(function (res) { return res.json(); })
                    .then(function (data) {
                        if (!data || !data.success) {
                            showAlert(data && data.message ? data.message : "Không thể cập nhật.");
                            return;
                        }

                        var row = form.closest("tr");
                        if (action === "delete_article") {
                            if (row) {
                                row.remove();
                            }
                            showAlert(data.message || "Đã xóa bài viết.", true);
                            return;
                        }

                        var statusField = form.querySelector("select[name=\"status\"]");
                        var badge = row ? row.querySelector(".js-status-badge") : null;
                        if (statusField && badge) {
                            badge.textContent = statusField.value;
                        }
                        showAlert(data.message || "Đã cập nhật.", true);
                    })
                    .catch(function () {
                        showAlert("Không thể kết nối máy chủ.");
                    })
                    .finally(function () {
                        if (submitBtn) {
                            submitBtn.disabled = false;
                        }
                    });
            });
        });
    })();
</script>

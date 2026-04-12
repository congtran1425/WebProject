<?php $basePath = "../"; ?>
<?php include "../includes/header.php"; ?>

<?php $activeTab = "categories"; ?>
<?php include "../views/admin_nav.php"; ?>
<?php include "../views/admin_flash.php"; ?>

<section class="card border-0 shadow-sm admin-section">
    <div class="card-body">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h2 class="h5 mb-0">Danh mục</h2>
            <span class="badge text-bg-danger">CRUD</span>
        </div>

        <form method="POST" class="row g-2 admin-inline-form mb-4" data-admin-form="1">
            <input type="hidden" name="action" value="create_category">
            <input type="hidden" name="redirect" value="categories.php">
            <div class="col-md-4">
                <input type="text" name="category_name" class="form-control" placeholder="Tên danh mục" required>
            </div>
            <div class="col-md-6">
                <input type="text" name="description" class="form-control" placeholder="Mô tả ngắn">
            </div>
            <div class="col-md-2 d-grid">
                <button type="submit" class="btn btn-danger">Thêm mới</button>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table align-middle admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tên danh mục</th>
                        <th>Mô tả</th>
                        <th class="text-end">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($category = $adminCategories->fetch_assoc()) { ?>
                        <?php $formId = "category-form-" . (int)$category["category_id"]; ?>
                        <tr>
                            <td><?php echo (int)$category["category_id"]; ?></td>
                            <td>
                                <input type="text" name="category_name" class="form-control form-control-sm" value="<?php echo htmlspecialchars($category["category_name"], ENT_QUOTES, "UTF-8"); ?>" form="<?php echo $formId; ?>" required>
                            </td>
                            <td>
                                <input type="text" name="description" class="form-control form-control-sm" value="<?php echo htmlspecialchars($category["description"] ?? "", ENT_QUOTES, "UTF-8"); ?>" form="<?php echo $formId; ?>">
                            </td>
                            <td class="text-end">
                                <form method="POST" id="<?php echo $formId; ?>" class="d-inline" data-admin-form="1">
                                    <input type="hidden" name="category_id" value="<?php echo (int)$category["category_id"]; ?>">
                                    <input type="hidden" name="redirect" value="categories.php">
                                    <div class="d-inline-flex gap-2">
                                        <button type="submit" name="action" value="update_category" class="btn btn-sm btn-outline-dark">Sửa</button>
                                        <button type="submit" name="action" value="delete_category" class="btn btn-sm btn-outline-danger">Xóa</button>
                                    </div>
                                </form>
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

                var submitter = event.submitter || form.querySelector("button[type=\"submit\"]");
                var action = "";
                if (submitter && submitter.name === "action" && submitter.value) {
                    action = submitter.value;
                } else {
                    var actionField = form.querySelector("input[name=\"action\"]");
                    action = actionField ? actionField.value : "";
                }

                if (action === "delete_category" && !window.confirm("Xóa danh mục này?")) {
                    return;
                }

                var formData = new FormData(form);
                if (submitter && submitter.name === "action" && submitter.value) {
                    formData.set("action", submitter.value);
                }

                if (submitter) {
                    submitter.disabled = true;
                }

                fetch("../api/admin.php", {
                    method: "POST",
                    body: formData
                })
                    .then(function (res) { return res.json(); })
                    .then(function (data) {
                        if (!data || !data.success) {
                            showAlert(data && data.message ? data.message : "Không thể cập nhật danh mục.");
                            return;
                        }

                        if (action === "create_category") {
                            showAlert(data.message || "Đã thêm danh mục.", true);
                            window.setTimeout(function () {
                                window.location.reload();
                            }, 900);
                            return;
                        }

                        if (action === "delete_category") {
                            var row = form.closest("tr");
                            if (row) {
                                row.remove();
                            }
                            showAlert(data.message || "Đã xóa danh mục.", true);
                            return;
                        }

                        showAlert(data.message || "Đã cập nhật danh mục.", true);
                    })
                    .catch(function () {
                        showAlert("Không thể kết nối máy chủ.");
                    })
                    .finally(function () {
                        if (submitter) {
                            submitter.disabled = false;
                        }
                    });
            });
        });
    })();
</script>

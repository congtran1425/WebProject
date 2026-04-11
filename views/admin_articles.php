<?php
$basePath = "../";
include "../includes/header.php";

$statusLabels = [
    "pending" => "Chờ duyệt",
    "published" => "Đã duyệt",
    "archived" => "Lưu trữ",
    "draft" => "Nháp",
];

$statusBadgeClasses = [
    "pending" => "text-bg-warning",
    "published" => "text-bg-success",
    "archived" => "text-bg-secondary",
    "draft" => "text-bg-light",
];
?>

<?php $activeTab = "articles"; ?>
<?php include "../views/admin_nav.php"; ?>
<?php include "../views/admin_flash.php"; ?>
<div id="admin-alert" class="mb-3"></div>

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
                <tbody id="admin-articles-body">
                    <?php while ($article = $adminArticles->fetch_assoc()) { ?>
                        <?php
                        $currentStatus = (string)($article["status"] ?? "draft");
                        $badgeClass = $statusBadgeClasses[$currentStatus] ?? "text-bg-light";
                        ?>
                        <tr data-article-row="<?php echo (int)$article["article_id"]; ?>">
                            <td>
                                <div class="fw-semibold"><?php echo htmlspecialchars($article["title"], ENT_QUOTES, "UTF-8"); ?></div>
                                <div class="small text-muted"><?php echo !empty($article["created_at"]) ? date("d/m/Y H:i", strtotime($article["created_at"])) : ""; ?></div>
                            </td>
                            <td><?php echo htmlspecialchars($article["username"], ENT_QUOTES, "UTF-8"); ?></td>
                            <td><?php echo htmlspecialchars($article["category_name"], ENT_QUOTES, "UTF-8"); ?></td>
                            <td>
                                <span class="badge js-status-badge <?php echo $badgeClass; ?>" data-status="<?php echo htmlspecialchars($currentStatus, ENT_QUOTES, "UTF-8"); ?>">
                                    <?php echo htmlspecialchars($statusLabels[$currentStatus] ?? $currentStatus, ENT_QUOTES, "UTF-8"); ?>
                                </span>
                            </td>
                            <td><?php echo (int)$article["view_count"]; ?></td>
                            <td>
                                <div class="d-flex gap-2 justify-content-end flex-wrap">
                                    <form method="POST" class="d-flex gap-2" data-admin-form="1">
                                        <input type="hidden" name="action" value="update_article_status">
                                        <input type="hidden" name="article_id" value="<?php echo (int)$article["article_id"]; ?>">
                                        <input type="hidden" name="redirect" value="articles.php">
                                        <select name="status" class="form-select form-select-sm">
                                            <?php foreach (["pending", "published", "archived", "draft"] as $status) { ?>
                                                <option value="<?php echo $status; ?>" <?php echo $currentStatus === $status ? "selected" : ""; ?>>
                                                    <?php echo htmlspecialchars($statusLabels[$status] ?? ucfirst($status), ENT_QUOTES, "UTF-8"); ?>
                                                </option>
                                            <?php } ?>
                                        </select>
                                        <button type="submit" class="btn btn-sm btn-dark">Cập nhật</button>
                                    </form>
                                    <?php if ($currentStatus === "pending") { ?>
                                        <button
                                            type="button"
                                            class="btn btn-sm btn-success"
                                            data-approve-article="1"
                                            data-article-id="<?php echo (int)$article["article_id"]; ?>"
                                        >
                                            Duyệt nhanh
                                        </button>
                                    <?php } ?>
                                    <form method="POST" data-admin-delete-form="1">
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
        var deleteForms = document.querySelectorAll("form[data-admin-delete-form=\"1\"]");
        var quickApproveButtons = document.querySelectorAll("[data-approve-article=\"1\"]");
        var alertBox = document.getElementById("admin-alert");
        var bodyEl = document.getElementById("admin-articles-body");

        var statusLabelMap = {
            pending: "Chờ duyệt",
            published: "Đã duyệt",
            archived: "Lưu trữ",
            draft: "Nháp"
        };

        var statusClassMap = {
            pending: "text-bg-warning",
            published: "text-bg-success",
            archived: "text-bg-secondary",
            draft: "text-bg-light"
        };

        var showAlert = function (message, isSuccess) {
            if (!alertBox) {
                return;
            }
            alertBox.innerHTML = "<div class=\"alert " + (isSuccess ? "alert-success" : "alert-danger") + "\" role=\"alert\">" +
                String(message || "Có lỗi xảy ra.") +
                "</div>";
        };

        var sendAdminRequest = function (formData, onSuccess, fallbackErrorMessage) {
            fetch("../api/admin.php", {
                method: "POST",
                body: formData
            })
                .then(function (res) { return res.json(); })
                .then(function (data) {
                    if (!data || !data.success) {
                        showAlert(data && data.message ? data.message : fallbackErrorMessage, false);
                        return;
                    }

                    onSuccess(data);
                })
                .catch(function () {
                    showAlert("Không thể kết nối máy chủ.", false);
                });
        };

        var updateBadge = function (row, status) {
            if (!row) {
                return;
            }

            var badge = row.querySelector(".js-status-badge");
            if (!badge) {
                return;
            }

            badge.textContent = statusLabelMap[status] || status;
            badge.setAttribute("data-status", status);
            badge.className = "badge js-status-badge " + (statusClassMap[status] || "text-bg-light");
        };

        var attachQuickApprove = function (button) {
            button.addEventListener("click", function () {
                var articleId = button.getAttribute("data-article-id");
                if (!articleId) {
                    return;
                }

                button.disabled = true;

                var formData = new FormData();
                formData.append("action", "update_article_status");
                formData.append("article_id", articleId);
                formData.append("status", "published");
                formData.append("redirect", "articles.php");

                sendAdminRequest(formData, function (data) {
                    var row = button.closest("tr");
                    updateBadge(row, "published");

                    var select = row ? row.querySelector("select[name=\"status\"]") : null;
                    if (select) {
                        select.value = "published";
                    }

                    button.remove();
                    showAlert(data.message || "Đã duyệt bài viết.", true);
                }, "Không thể duyệt bài viết.");
            });
        };

        forms.forEach(function (form) {
            form.addEventListener("submit", function (event) {
                event.preventDefault();

                var submitBtn = form.querySelector("button[type=\"submit\"]");
                if (submitBtn) {
                    submitBtn.disabled = true;
                }

                var formData = new FormData(form);
                sendAdminRequest(formData, function (data) {
                    var row = form.closest("tr");
                    var statusField = form.querySelector("select[name=\"status\"]");
                    var nextStatus = statusField ? statusField.value : ((data.payload && data.payload.status) || "");

                    updateBadge(row, nextStatus);

                    var quickApproveBtn = row ? row.querySelector("[data-approve-article=\"1\"]") : null;
                    if (nextStatus === "pending") {
                        if (!quickApproveBtn) {
                            var deleteForm = row ? row.querySelector("form[data-admin-delete-form=\"1\"]") : null;
                            if (deleteForm && deleteForm.parentNode) {
                                var btn = document.createElement("button");
                                btn.type = "button";
                                btn.className = "btn btn-sm btn-success";
                                btn.textContent = "Duyệt nhanh";
                                btn.setAttribute("data-approve-article", "1");
                                btn.setAttribute("data-article-id", (data.payload && data.payload.article_id) || "");
                                deleteForm.parentNode.insertBefore(btn, deleteForm);
                                attachQuickApprove(btn);
                            }
                        }
                    } else if (quickApproveBtn) {
                        quickApproveBtn.remove();
                    }

                    showAlert(data.message || "Đã cập nhật.", true);
                }, "Không thể cập nhật trạng thái bài viết.");
            });
        });

        deleteForms.forEach(function (form) {
            form.addEventListener("submit", function (event) {
                event.preventDefault();

                if (!window.confirm("Xóa bài viết này?")) {
                    return;
                }

                var submitBtn = form.querySelector("button[type=\"submit\"]");
                if (submitBtn) {
                    submitBtn.disabled = true;
                }

                var formData = new FormData(form);
                sendAdminRequest(formData, function (data) {
                    var row = form.closest("tr");
                    if (row) {
                        row.remove();
                    }

                    if (bodyEl && !bodyEl.querySelector("tr")) {
                        bodyEl.innerHTML = "<tr><td colspan=\"6\" class=\"text-center text-muted py-4\">Chưa có bài viết nào.</td></tr>";
                    }

                    showAlert(data.message || "Đã xóa bài viết.", true);
                }, "Không thể xóa bài viết.");
            });
        });

        quickApproveButtons.forEach(attachQuickApprove);
    })();
</script>

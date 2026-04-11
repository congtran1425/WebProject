<?php
$basePath = "../";
include "../includes/header.php";
?>

<?php $activeTab = "comments"; ?>
<?php include "../views/admin_nav.php"; ?>
<?php include "../views/admin_flash.php"; ?>
<div id="admin-alert" class="mb-3"></div>

<section class="card border-0 shadow-sm admin-section">
    <div class="card-body">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h2 class="h5 mb-0">Bình luận</h2>
            <span class="badge text-bg-secondary">Kiểm duyệt</span>
        </div>
        <div class="table-responsive">
            <table class="table align-middle admin-table">
                <thead>
                    <tr>
                        <th>Nội dung</th>
                        <th>Người viết</th>
                        <th>Bài viết</th>
                        <th>Trạng thái</th>
                        <th class="text-end">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($comment = $adminComments->fetch_assoc()) { ?>
                        <tr>
                            <td>
                                <div class="admin-comment-text"><?php echo htmlspecialchars($comment["content"], ENT_QUOTES, "UTF-8"); ?></div>
                                <div class="small text-muted"><?php echo !empty($comment["created_at"]) ? date("d/m/Y H:i", strtotime($comment["created_at"])) : ""; ?></div>
                            </td>
                            <td><?php echo htmlspecialchars($comment["username"], ENT_QUOTES, "UTF-8"); ?></td>
                            <td><?php echo htmlspecialchars($comment["article_title"], ENT_QUOTES, "UTF-8"); ?></td>
                            <td><span class="badge text-bg-light js-status-badge"><?php echo htmlspecialchars($comment["status"], ENT_QUOTES, "UTF-8"); ?></span></td>
                            <td>
                                <form method="POST" class="d-flex gap-2 justify-content-end" data-admin-form="1">
                                    <input type="hidden" name="action" value="update_comment_status">
                                    <input type="hidden" name="comment_id" value="<?php echo (int)$comment["comment_id"]; ?>">
                                    <input type="hidden" name="redirect" value="comments.php">
                                    <select name="status" class="form-select form-select-sm">
                                        <?php foreach (["visible", "hidden", "deleted"] as $status) { ?>
                                            <option value="<?php echo $status; ?>" <?php echo $comment["status"] === $status ? "selected" : ""; ?>>
                                                <?php echo ucfirst($status); ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                    <button type="submit" class="btn btn-sm btn-dark">Lưu</button>
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
        var alertBox = document.getElementById("admin-alert");
        if (!forms.length) {
            return;
        }

        var showAlert = function (message, isSuccess) {
            if (!alertBox) {
                return;
            }
            alertBox.innerHTML = "<div class=\"alert " + (isSuccess ? "alert-success" : "alert-danger") + "\" role=\"alert\">" +
                String(message || "Có lỗi xảy ra.") +
                "</div>";
        };

        forms.forEach(function (form) {
            form.addEventListener("submit", function (event) {
                event.preventDefault();

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

                        var statusField = form.querySelector("select[name=\"status\"]");
                        var row = form.closest("tr");
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




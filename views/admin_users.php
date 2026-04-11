<?php
$basePath = "../";
include "../includes/header.php";

$statusLabels = [
    "active" => "Hoạt động",
    "inactive" => "Tạm khóa",
    "banned" => "Cấm",
];

$statusBadgeClasses = [
    "active" => "text-bg-success",
    "inactive" => "text-bg-warning",
    "banned" => "text-bg-danger",
];

$roleLabels = [
    "admin" => "ADMIN",
    "editor" => "EDITOR",
    "author" => "AUTHOR",
    "reader" => "READER",
];

$roleBadgeClasses = [
    "admin" => "text-bg-dark",
    "editor" => "text-bg-primary",
    "author" => "text-bg-info",
    "reader" => "text-bg-secondary",
];
?>

<?php $activeTab = "users"; ?>
<?php include "../views/admin_nav.php"; ?>
<?php include "../views/admin_flash.php"; ?>
<div id="admin-alert" class="mb-3"></div>

<section class="card border-0 shadow-sm admin-section">
    <div class="card-body">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h2 class="h5 mb-0">Người dùng</h2>
            <span class="badge text-bg-dark">Role</span>
        </div>
        <div class="table-responsive">
            <table class="table align-middle admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tài khoản</th>
                        <th>Email</th>
                        <th>Trạng thái</th>
                        <th>Quyền</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($user = $adminUsers->fetch_assoc()) { ?>
                        <?php
                        $currentStatus = (string)($user["status"] ?? "active");
                        $currentRole = (string)($user["role"] ?? "reader");
                        ?>
                        <tr data-user-row="<?php echo (int)$user["user_id"]; ?>">
                            <td><?php echo (int)$user["user_id"]; ?></td>
                            <td>
                                <div class="fw-semibold"><?php echo htmlspecialchars($user["username"], ENT_QUOTES, "UTF-8"); ?></div>
                                <div class="small text-muted"><?php echo !empty($user["created_at"]) ? date("d/m/Y", strtotime($user["created_at"])) : ""; ?></div>
                            </td>
                            <td><?php echo htmlspecialchars($user["email"], ENT_QUOTES, "UTF-8"); ?></td>
                            <td>
                                <div class="d-flex gap-2 align-items-center">
                                    <span class="badge js-status-badge <?php echo $statusBadgeClasses[$currentStatus] ?? "text-bg-light"; ?>">
                                        <?php echo htmlspecialchars($statusLabels[$currentStatus] ?? $currentStatus, ENT_QUOTES, "UTF-8"); ?>
                                    </span>
                                    <form method="POST" class="d-flex gap-2" data-admin-form="1">
                                        <input type="hidden" name="action" value="update_user_status">
                                        <input type="hidden" name="user_id" value="<?php echo (int)$user["user_id"]; ?>">
                                        <input type="hidden" name="redirect" value="users.php">
                                        <select name="status" class="form-select form-select-sm">
                                            <?php foreach (["active", "inactive", "banned"] as $status) { ?>
                                                <option value="<?php echo $status; ?>" <?php echo $currentStatus === $status ? "selected" : ""; ?>>
                                                    <?php echo htmlspecialchars($statusLabels[$status] ?? ucfirst($status), ENT_QUOTES, "UTF-8"); ?>
                                                </option>
                                            <?php } ?>
                                        </select>
                                        <button type="submit" class="btn btn-sm btn-outline-dark">Lưu</button>
                                    </form>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex gap-2 align-items-center">
                                    <span class="badge js-role-badge <?php echo $roleBadgeClasses[$currentRole] ?? "text-bg-secondary"; ?>">
                                        <?php echo htmlspecialchars($roleLabels[$currentRole] ?? strtoupper($currentRole), ENT_QUOTES, "UTF-8"); ?>
                                    </span>
                                    <form method="POST" class="d-flex gap-2" data-admin-form="1">
                                        <input type="hidden" name="action" value="update_user_role">
                                        <input type="hidden" name="user_id" value="<?php echo (int)$user["user_id"]; ?>">
                                        <input type="hidden" name="redirect" value="users.php">
                                        <select name="role" class="form-select form-select-sm">
                                            <?php foreach (["admin", "editor", "author", "reader"] as $role) { ?>
                                                <option value="<?php echo $role; ?>" <?php echo $currentRole === $role ? "selected" : ""; ?>>
                                                    <?php echo htmlspecialchars($roleLabels[$role] ?? strtoupper($role), ENT_QUOTES, "UTF-8"); ?>
                                                </option>
                                            <?php } ?>
                                        </select>
                                        <button type="submit" class="btn btn-sm btn-dark">Lưu</button>
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
        var alertBox = document.getElementById("admin-alert");
        if (!forms.length) {
            return;
        }

        var statusLabelMap = {
            active: "Hoạt động",
            inactive: "Tạm khóa",
            banned: "Cấm"
        };

        var statusClassMap = {
            active: "text-bg-success",
            inactive: "text-bg-warning",
            banned: "text-bg-danger"
        };

        var roleLabelMap = {
            admin: "ADMIN",
            editor: "EDITOR",
            author: "AUTHOR",
            reader: "READER"
        };

        var roleClassMap = {
            admin: "text-bg-dark",
            editor: "text-bg-primary",
            author: "text-bg-info",
            reader: "text-bg-secondary"
        };

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
                            showAlert(data && data.message ? data.message : "Không thể cập nhật người dùng.", false);
                            return;
                        }

                        var row = form.closest("tr");
                        if (!row) {
                            showAlert(data.message || "Đã cập nhật.", true);
                            return;
                        }

                        if ((form.querySelector("input[name=\"action\"]") || {}).value === "update_user_status") {
                            var statusField = form.querySelector("select[name=\"status\"]");
                            var statusBadge = row.querySelector(".js-status-badge");
                            var nextStatus = statusField ? statusField.value : ((data.payload && data.payload.status) || "");
                            if (statusBadge && nextStatus) {
                                statusBadge.textContent = statusLabelMap[nextStatus] || nextStatus;
                                statusBadge.className = "badge js-status-badge " + (statusClassMap[nextStatus] || "text-bg-light");
                            }
                        }

                        if ((form.querySelector("input[name=\"action\"]") || {}).value === "update_user_role") {
                            var roleField = form.querySelector("select[name=\"role\"]");
                            var roleBadge = row.querySelector(".js-role-badge");
                            var nextRole = roleField ? roleField.value : ((data.payload && data.payload.role) || "");
                            if (roleBadge && nextRole) {
                                roleBadge.textContent = roleLabelMap[nextRole] || nextRole.toUpperCase();
                                roleBadge.className = "badge js-role-badge " + (roleClassMap[nextRole] || "text-bg-secondary");
                            }
                        }

                        showAlert(data.message || "Đã cập nhật.", true);
                    })
                    .catch(function () {
                        showAlert("Không thể kết nối máy chủ.", false);
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

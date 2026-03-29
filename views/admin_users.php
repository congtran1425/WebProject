<?php $basePath = "../"; ?>
<?php include "../includes/header.php"; ?>

<?php $activeTab = "users"; ?>
<?php include "../views/admin_nav.php"; ?>
<?php include "../views/admin_flash.php"; ?>

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
                        <tr>
                            <td><?php echo (int)$user["user_id"]; ?></td>
                            <td>
                                <div class="fw-semibold"><?php echo htmlspecialchars($user["username"], ENT_QUOTES, "UTF-8"); ?></div>
                                <div class="small text-muted"><?php echo !empty($user["created_at"]) ? date("d/m/Y", strtotime($user["created_at"])) : ""; ?></div>
                            </td>
                            <td><?php echo htmlspecialchars($user["email"], ENT_QUOTES, "UTF-8"); ?></td>
                            <td>
                                <form method="POST" class="d-flex gap-2">
                                    <input type="hidden" name="action" value="update_user_status">
                                    <input type="hidden" name="user_id" value="<?php echo (int)$user["user_id"]; ?>">
                                    <input type="hidden" name="redirect" value="users.php">
                                    <select name="status" class="form-select form-select-sm">
                                        <?php foreach (["active", "inactive", "banned"] as $status) { ?>
                                            <option value="<?php echo $status; ?>" <?php echo $user["status"] === $status ? "selected" : ""; ?>>
                                                <?php echo ucfirst($status); ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                    <button type="submit" class="btn btn-sm btn-outline-dark">Lưu</button>
                                </form>
                            </td>
                            <td>
                                <form method="POST" class="d-flex gap-2">
                                    <input type="hidden" name="action" value="update_user_role">
                                    <input type="hidden" name="user_id" value="<?php echo (int)$user["user_id"]; ?>">
                                    <input type="hidden" name="redirect" value="users.php">
                                    <select name="role" class="form-select form-select-sm">
                                        <?php foreach (["admin", "editor", "author", "reader"] as $role) { ?>
                                            <option value="<?php echo $role; ?>" <?php echo $user["role"] === $role ? "selected" : ""; ?>>
                                                <?php echo strtoupper($role); ?>
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




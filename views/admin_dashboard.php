<?php include "includes/header.php"; ?>

<?php
$flashStatus = $_GET["status"] ?? "";
$flashMessage = $_GET["message"] ?? "";
?>

<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <h1 class="h3 mb-1">Trang quản trị</h1>
        <p class="text-muted mb-0">Quản lý người dùng, danh mục, bài viết và bình luận từ một dashboard.</p>
    </div>
    <a class="btn btn-outline-dark" href="index.php">Về trang người dùng</a>
</div>

<?php if ($flashMessage !== "") { ?>
    <div class="alert <?php echo $flashStatus === "success" ? "alert-success" : "alert-danger"; ?> border-0 shadow-sm">
        <?php echo htmlspecialchars($flashMessage, ENT_QUOTES, "UTF-8"); ?>
    </div>
<?php } ?>

<div class="admin-grid">
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

    <section class="card border-0 shadow-sm admin-section">
        <div class="card-body">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h2 class="h5 mb-0">Danh mục</h2>
                <span class="badge text-bg-danger">CRUD</span>
            </div>

            <form method="POST" class="row g-2 admin-inline-form mb-4">
                <input type="hidden" name="action" value="create_category">
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
                            <tr>
                                <td><?php echo (int)$category["category_id"]; ?></td>
                                <td colspan="3">
                                    <div class="admin-category-row">
                                        <form method="POST" class="row g-2 flex-grow-1">
                                            <input type="hidden" name="action" value="update_category">
                                            <input type="hidden" name="category_id" value="<?php echo (int)$category["category_id"]; ?>">
                                            <div class="col-md-4">
                                                <input type="text" name="category_name" class="form-control form-control-sm" value="<?php echo htmlspecialchars($category["category_name"], ENT_QUOTES, "UTF-8"); ?>" required>
                                            </div>
                                            <div class="col-md-5">
                                                <input type="text" name="description" class="form-control form-control-sm" value="<?php echo htmlspecialchars($category["description"] ?? "", ENT_QUOTES, "UTF-8"); ?>">
                                            </div>
                                            <div class="col-md-3 d-flex gap-2 justify-content-end">
                                                <button type="submit" class="btn btn-sm btn-outline-dark">Sửa</button>
                                            </div>
                                        </form>
                                        <form method="POST" onsubmit="return confirm('Xóa danh mục này?');">
                                            <input type="hidden" name="action" value="delete_category">
                                            <input type="hidden" name="category_id" value="<?php echo (int)$category["category_id"]; ?>">
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
                                <td><span class="badge text-bg-light"><?php echo htmlspecialchars($article["status"], ENT_QUOTES, "UTF-8"); ?></span></td>
                                <td><?php echo (int)$article["view_count"]; ?></td>
                                <td>
                                    <div class="d-flex gap-2 justify-content-end flex-wrap">
                                        <form method="POST" class="d-flex gap-2">
                                            <input type="hidden" name="action" value="update_article_status">
                                            <input type="hidden" name="article_id" value="<?php echo (int)$article["article_id"]; ?>">
                                            <select name="status" class="form-select form-select-sm">
                                                <?php foreach (["pending", "published", "archived", "draft"] as $status) { ?>
                                                    <option value="<?php echo $status; ?>" <?php echo $article["status"] === $status ? "selected" : ""; ?>>
                                                        <?php echo ucfirst($status); ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                            <button type="submit" class="btn btn-sm btn-dark">Cập nhật</button>
                                        </form>
                                        <form method="POST" onsubmit="return confirm('Xóa bài viết này?');">
                                            <input type="hidden" name="action" value="delete_article">
                                            <input type="hidden" name="article_id" value="<?php echo (int)$article["article_id"]; ?>">
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
                                <td><span class="badge text-bg-light"><?php echo htmlspecialchars($comment["status"], ENT_QUOTES, "UTF-8"); ?></span></td>
                                <td>
                                    <form method="POST" class="d-flex gap-2 justify-content-end">
                                        <input type="hidden" name="action" value="update_comment_status">
                                        <input type="hidden" name="comment_id" value="<?php echo (int)$comment["comment_id"]; ?>">
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
</div>

<?php include "includes/footer.php"; ?>

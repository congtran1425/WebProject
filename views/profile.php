<?php
function profile_display_name(array $profile)
{
    if (!empty($profile["full_name"])) {
        return $profile["full_name"];
    }

    return $profile["username"] ?? "Người dùng";
}

function profile_initial($name)
{
    if (function_exists("mb_substr")) {
        return mb_strtoupper(mb_substr($name, 0, 1, "UTF-8"), "UTF-8");
    }

    return strtoupper(substr($name, 0, 1));
}

function profile_avatar_src($path)
{
    $normalized = trim((string)$path);
    if ($normalized === "") {
        return "";
    }

    $normalized = str_replace("\\", "/", $normalized);
    if (preg_match("#^(https?:|data:)#i", $normalized)) {
        return $normalized;
    }

    $pos = strpos($normalized, "assets/avatars/");
    if ($pos !== false) {
        $normalized = substr($normalized, $pos);
    }

    $pos = strpos($normalized, "avatars/");
    if ($pos !== false) {
        $normalized = "assets/avatars/" . basename($normalized);
    }

    $normalized = ltrim($normalized, "/");
    $base = rtrim(dirname($_SERVER["SCRIPT_NAME"] ?? ""), "/");
    if ($base === "" || $base === ".") {
        return "/" . $normalized;
    }

    return $base . "/" . $normalized;
}

$displayName = profile_display_name($profile);
$avatarPath = profile_avatar_src($profile["avatar"] ?? "");
$genderMap = [
    "male" => "Nam",
    "female" => "Nữ",
    "other" => "Khác",
];
?>

<?php include "includes/header.php"; ?>

<div class="profile-page">
    <?php if ($profileFeedback) { ?>
        <div class="alert <?php echo !empty($profileFeedback["success"]) ? "alert-success" : "alert-danger"; ?>" role="alert">
            <?php echo htmlspecialchars($profileFeedback["message"], ENT_QUOTES, "UTF-8"); ?>
        </div>
    <?php } ?>

    <?php if ($usesFallbackUser && $canEdit) { ?>
        <div class="alert alert-warning" role="alert">
            Dự án hiện chưa có luồng đăng nhập hoàn chỉnh, nên trang cá nhân đang hiển thị người dùng đầu tiên trong hệ thống để bạn có thể thử tính năng cập nhật hồ sơ.
        </div>
    <?php } ?>

    <section class="card border-0 shadow-sm profile-hero mb-4">
        <div class="card-body p-4 p-lg-5">
            <div class="row align-items-center g-4">
                <div class="col-lg-8">
                    <div class="d-flex flex-column flex-md-row align-items-md-center gap-4">
                        <div class="profile-avatar-lg">
                            <?php if ($avatarPath !== "") { ?>
                                <img src="<?php echo htmlspecialchars($avatarPath, ENT_QUOTES, "UTF-8"); ?>" alt="Ảnh đại diện">
                            <?php } else { ?>
                                <span><?php echo htmlspecialchars(profile_initial($displayName), ENT_QUOTES, "UTF-8"); ?></span>
                            <?php } ?>
                        </div>
                        <div>
                            <div class="profile-overline">Trang cá nhân</div>
                            <h1 class="profile-name mb-2"><?php echo htmlspecialchars($profile["full_name"] ?? "", ENT_QUOTES, "UTF-8"); ?></h1>
                            <div class="d-flex flex-wrap gap-2 mb-3">
                                <?php if (!empty($profile["role"])) { ?>
                                    <span class="badge rounded-pill text-bg-dark"><?php echo htmlspecialchars($profile["role"], ENT_QUOTES, "UTF-8"); ?></span>
                                <?php } ?>
                                <?php if (!empty($profile["status"])) { ?>
                                    <span class="badge rounded-pill text-bg-light border"><?php echo htmlspecialchars($profile["status"], ENT_QUOTES, "UTF-8"); ?></span>
                                <?php } ?>
                            </div>
                            <p class="profile-bio mb-3">
                                <?php echo !empty($profile["bio"]) ? nl2br(htmlspecialchars($profile["bio"], ENT_QUOTES, "UTF-8")) : "Chưa có mô tả cá nhân."; ?>
                            </p>
                            <div class="profile-meta-grid">
                                <div><i class="bi bi-person"></i> @<?php echo htmlspecialchars($profile["username"] ?? "", ENT_QUOTES, "UTF-8"); ?></div>
                                <div><i class="bi bi-envelope"></i> <?php echo htmlspecialchars($profile["email"] ?? "", ENT_QUOTES, "UTF-8"); ?></div>
                                <?php if (!empty($profile["phone"])) { ?>
                                    <div><i class="bi bi-telephone"></i> <?php echo htmlspecialchars($profile["phone"], ENT_QUOTES, "UTF-8"); ?></div>
                                <?php } ?>
                                <?php if (!empty($profile["birth_date"])) { ?>
                                    <div><i class="bi bi-calendar-event"></i> <?php echo date("d/m/Y", strtotime($profile["birth_date"])); ?></div>
                                <?php } ?>
                                <?php if (!empty($profile["gender"])) { ?>
                                    <div><i class="bi bi-gender-ambiguous"></i> <?php echo htmlspecialchars($genderMap[$profile["gender"]] ?? $profile["gender"], ENT_QUOTES, "UTF-8"); ?></div>
                                <?php } ?>
                                <?php if (!empty($profile["address"])) { ?>
                                    <div><i class="bi bi-geo-alt"></i> <?php echo htmlspecialchars($profile["address"], ENT_QUOTES, "UTF-8"); ?></div>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="profile-stats">
                        <div class="profile-stat-card">
                            <div class="profile-stat-value"><?php echo (int)$stats["article_count"]; ?></div>
                            <div class="profile-stat-label">Bài viết</div>
                        </div>
                        <div class="profile-stat-card">
                            <div class="profile-stat-value"><?php echo (int)$stats["comment_count"]; ?></div>
                            <div class="profile-stat-label">Bình luận</div>
                        </div>
                        <div class="profile-stat-card">
                            <div class="profile-stat-value"><?php echo (int)$stats["total_views"]; ?></div>
                            <div class="profile-stat-label">Lượt xem</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="row g-4">
        <div class="col-xl-7">
            <section class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h2 class="h5 mb-0">Thông tin hồ sơ</h2>
                        <?php if (!$canEdit) { ?>
                            <span class="small text-muted">Đang xem hồ sơ công khai</span>
                        <?php } ?>
                    </div>

                    <?php if ($canEdit) { ?>
                        <form method="post" enctype="multipart/form-data" class="row g-3">
                            <input type="hidden" name="action" value="update_profile">

                            <div class="col-md-6">
                                <label class="form-label" for="profile-username">Tên đăng nhập</label>
                                <input class="form-control" id="profile-username" type="text" name="username" value="<?php echo htmlspecialchars($profile["username"] ?? "", ENT_QUOTES, "UTF-8"); ?>" readonly>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="profile-email">Email</label>
                                <input class="form-control" id="profile-email" type="email" name="email" value="<?php echo htmlspecialchars($profile["email"] ?? "", ENT_QUOTES, "UTF-8"); ?>" readonly>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="profile-full-name">Họ và tên</label>
                                <input class="form-control" id="profile-full-name" type="text" name="full_name" value="<?php echo htmlspecialchars($profile["full_name"] ?? "", ENT_QUOTES, "UTF-8"); ?>">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="profile-phone">Số điện thoại</label>
                                <input class="form-control" id="profile-phone" type="text" name="phone" value="<?php echo htmlspecialchars($profile["phone"] ?? "", ENT_QUOTES, "UTF-8"); ?>">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="profile-gender">Giới tính</label>
                                <select class="form-select" id="profile-gender" name="gender">
                                    <option value="">Chọn giới tính</option>
                                    <option value="male" <?php echo ($profile["gender"] ?? "") === "male" ? "selected" : ""; ?>>Nam</option>
                                    <option value="female" <?php echo ($profile["gender"] ?? "") === "female" ? "selected" : ""; ?>>Nữ</option>
                                    <option value="other" <?php echo ($profile["gender"] ?? "") === "other" ? "selected" : ""; ?>>Khác</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="profile-birth-date">Ngày sinh</label>
                                <input class="form-control" id="profile-birth-date" type="date" name="birth_date" value="<?php echo htmlspecialchars($profile["birth_date"] ?? "", ENT_QUOTES, "UTF-8"); ?>">
                            </div>

                            <div class="col-12">
                                <label class="form-label" for="profile-address">Địa chỉ</label>
                                <input class="form-control" id="profile-address" type="text" name="address" value="<?php echo htmlspecialchars($profile["address"] ?? "", ENT_QUOTES, "UTF-8"); ?>">
                            </div>

                            <div class="col-12">
                                <label class="form-label" for="profile-bio">Giới thiệu</label>
                                <textarea class="form-control" id="profile-bio" name="bio" rows="4"><?php echo htmlspecialchars($profile["bio"] ?? "", ENT_QUOTES, "UTF-8"); ?></textarea>
                            </div>

                            <div class="col-md-7">
                                <label class="form-label" for="profile-avatar">Ảnh đại diện</label>
                                <input class="form-control" id="profile-avatar" type="file" name="avatar" accept="image/jpeg,image/png,image/webp">
                                <div class="form-text">Hỗ trợ JPG, PNG, WEBP. Tối đa 2MB.</div>
                            </div>

                            <div class="col-md-5 d-flex align-items-end">
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" id="remove-avatar" name="remove_avatar" value="1">
                                    <label class="form-check-label" for="remove-avatar">Xóa ảnh đại diện hiện tại</label>
                                </div>
                            </div>

                            <div class="col-12 d-flex gap-2">
                                <button class="btn btn-danger" type="submit">Lưu thay đổi</button>
                                <a class="btn btn-outline-secondary" href="profile.php<?php echo isset($_GET["id"]) ? ("?id=" . (int)$_GET["id"]) : ""; ?>">Làm mới</a>
                            </div>
                        </form>
                    <?php } else { ?>
                        <div class="profile-readonly-grid">
                            <div>
                                <div class="profile-field-label">Họ và tên</div>
                                <div class="profile-field-value"><?php echo htmlspecialchars($profile["full_name"] ?? "Chưa cập nhật", ENT_QUOTES, "UTF-8"); ?></div>
                            </div>
                            <div>
                                <div class="profile-field-label">Số điện thoại</div>
                                <div class="profile-field-value"><?php echo htmlspecialchars($profile["phone"] ?? "Chưa cập nhật", ENT_QUOTES, "UTF-8"); ?></div>
                            </div>
                            <div>
                                <div class="profile-field-label">Giới tính</div>
                                <div class="profile-field-value"><?php echo htmlspecialchars($genderMap[$profile["gender"] ?? ""] ?? "Chưa cập nhật", ENT_QUOTES, "UTF-8"); ?></div>
                            </div>
                            <div>
                                <div class="profile-field-label">Ngày sinh</div>
                                <div class="profile-field-value">
                                    <?php echo !empty($profile["birth_date"]) ? date("d/m/Y", strtotime($profile["birth_date"])) : "Chưa cập nhật"; ?>
                                </div>
                            </div>
                            <div class="w-100">
                                <div class="profile-field-label">Địa chỉ</div>
                                <div class="profile-field-value"><?php echo htmlspecialchars($profile["address"] ?? "Chưa cập nhật", ENT_QUOTES, "UTF-8"); ?></div>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            </section>
        </div>

        <div class="col-xl-5">
            <section class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h2 class="h5 mb-0">Bài viết gần đây</h2>
                        <span class="small text-muted"><?php echo (int)$stats["article_count"]; ?> bài</span>
                    </div>

                    <?php if ($articles && $articles->num_rows > 0) { ?>
                        <div class="profile-activity-list">
                            <?php while ($articleItem = $articles->fetch_assoc()) { ?>
                                <a class="profile-activity-item" href="article_detail.php?id=<?php echo (int)$articleItem["article_id"]; ?>">
                                    <div class="profile-activity-title"><?php echo htmlspecialchars($articleItem["title"], ENT_QUOTES, "UTF-8"); ?></div>
                                    <div class="profile-activity-meta">
                                        <?php if (!empty($articleItem["category_name"])) { ?>
                                            <span><?php echo htmlspecialchars($articleItem["category_name"], ENT_QUOTES, "UTF-8"); ?></span>
                                        <?php } ?>
                                        <?php if (!empty($articleItem["created_at"])) { ?>
                                            <span><?php echo date("d/m/Y", strtotime($articleItem["created_at"])); ?></span>
                                        <?php } ?>
                                        <span><?php echo (int)($articleItem["view_count"] ?? 0); ?> lượt xem</span>
                                    </div>
                                </a>
                            <?php } ?>
                        </div>
                    <?php } else { ?>
                        <div class="empty-sidebar-state">Người dùng này chưa có bài viết nào.</div>
                    <?php } ?>
                </div>
            </section>

            <section class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h2 class="h5 mb-0">Bình luận gần đây</h2>
                        <span class="small text-muted"><?php echo (int)$stats["comment_count"]; ?> bình luận</span>
                    </div>

                    <?php if ($comments && $comments->num_rows > 0) { ?>
                        <div class="profile-activity-list">
                            <?php while ($commentItem = $comments->fetch_assoc()) { ?>
                                <a class="profile-activity-item" href="article_detail.php?id=<?php echo (int)$commentItem["article_id"]; ?>#comment-<?php echo (int)$commentItem["comment_id"]; ?>">
                                    <div class="profile-activity-title"><?php echo htmlspecialchars($commentItem["article_title"], ENT_QUOTES, "UTF-8"); ?></div>
                                    <div class="profile-comment-snippet">
                                        <?php echo htmlspecialchars($commentItem["content"], ENT_QUOTES, "UTF-8"); ?>
                                    </div>
                                    <div class="profile-activity-meta">
                                        <?php if (!empty($commentItem["created_at"])) { ?>
                                            <span><?php echo date("d/m/Y H:i", strtotime($commentItem["created_at"])); ?></span>
                                        <?php } ?>
                                        <?php if (!empty($commentItem["status"])) { ?>
                                            <span><?php echo htmlspecialchars($commentItem["status"], ENT_QUOTES, "UTF-8"); ?></span>
                                        <?php } ?>
                                    </div>
                                </a>
                            <?php } ?>
                        </div>
                    <?php } else { ?>
                        <div class="empty-sidebar-state">Người dùng này chưa có bình luận nào.</div>
                    <?php } ?>
                </div>
            </section>
        </div>
    </div>
</div>

<?php include "includes/footer.php"; ?>

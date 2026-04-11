<?php
$basePath = "../";
include "../includes/header.php";
?>

<?php $activeTab = "dashboard"; ?>
<?php include "../views/admin_nav.php"; ?>
<?php include "../views/admin_flash.php"; ?>

<div class="row g-3">
    <div class="col-md-6 col-lg-3">
        <a class="card border-0 shadow-sm admin-card-link" href="users.php">
            <div class="card-body">
                <div class="text-muted small">Người dùng</div>
                <div class="display-6 fw-bold mb-2"><?php echo (int)$stats["users"]; ?></div>
                <div class="small admin-card-action">Quản lý người dùng</div>
            </div>
        </a>
    </div>
    <div class="col-md-6 col-lg-3">
        <a class="card border-0 shadow-sm admin-card-link" href="categories.php">
            <div class="card-body">
                <div class="text-muted small">Danh mục</div>
                <div class="display-6 fw-bold mb-2"><?php echo (int)$stats["categories"]; ?></div>
                <div class="small admin-card-action">Quản lý danh mục</div>
            </div>
        </a>
    </div>
    <div class="col-md-6 col-lg-3">
        <a class="card border-0 shadow-sm admin-card-link" href="articles.php">
            <div class="card-body">
                <div class="text-muted small">Bài viết</div>
                <div class="display-6 fw-bold mb-2"><?php echo (int)$stats["articles"]; ?></div>
                <div class="small admin-card-action">Quản lý bài viết</div>
            </div>
        </a>
    </div>
    <div class="col-md-6 col-lg-3">
        <a class="card border-0 shadow-sm admin-card-link" href="comments.php">
            <div class="card-body">
                <div class="text-muted small">Bình luận</div>
                <div class="display-6 fw-bold mb-2"><?php echo (int)$stats["comments"]; ?></div>
                <div class="small admin-card-action">Quản lý bình luận</div>
            </div>
        </a>
    </div>
</div>

<?php include "../includes/footer.php"; ?>




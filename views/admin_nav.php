<?php
$activeTab = $activeTab ?? "dashboard";
$tabs = [
    "dashboard" => ["label" => "Tổng quan", "href" => "index.php"],
    "users" => ["label" => "Người dùng", "href" => "users.php"],
    "categories" => ["label" => "Danh mục", "href" => "categories.php"],
    "articles" => ["label" => "Bài viết", "href" => "articles.php"],
    "comments" => ["label" => "Bình luận", "href" => "comments.php"],
];
?>

<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <h1 class="h3 mb-1">Trang quản trị</h1>
        <p class="text-muted mb-0">Quản lý hệ thống theo từng module.</p>
    </div>
    <a class="btn btn-outline-dark" href="../index.php">Về trang người dùng</a>
</div>

<ul class="nav nav-tabs mb-4">
    <?php foreach ($tabs as $key => $tab) { ?>
        <li class="nav-item">
            <a class="nav-link <?php echo $activeTab === $key ? "active" : ""; ?>" href="<?php echo $tab["href"]; ?>">
                <?php echo htmlspecialchars($tab["label"], ENT_QUOTES, "UTF-8"); ?>
            </a>
        </li>
    <?php } ?>
</ul>

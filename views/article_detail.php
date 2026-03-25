<?php include "includes/header.php"; ?>

<?php if (!$article) { ?>
    <div class="alert alert-danger border-0 shadow-sm">
        Bài viết không tồn tại hoặc chưa được duyệt hiển thị.
    </div>
<?php } else { ?>
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <?php if (!empty($article["thumbnail"])) { ?>
                <img class="article-thumb mb-3" src="<?php echo htmlspecialchars($article["thumbnail"], ENT_QUOTES, "UTF-8"); ?>" alt="Thumbnail">
            <?php } ?>

            <h2 class="h4 mb-2"><?php echo htmlspecialchars($article["title"], ENT_QUOTES, "UTF-8"); ?></h2>

            <p class="text-muted">
                Danh mục: <?php echo htmlspecialchars($article["category_name"], ENT_QUOTES, "UTF-8"); ?>
            </p>

            <hr>

            <div class="article-content">
                <?php echo $article["content"]; ?>
            </div>
        </div>
    </div>
<?php } ?>

<?php include "includes/footer.php"; ?>

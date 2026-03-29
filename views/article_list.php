<?php include "includes/header.php"; ?>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="d-flex align-items-center justify-content-between mb-2">
            <h2 class="h6 mb-0 text-uppercase">Mới nhất</h2>
            <?php if (isset($_GET["category_id"])) { ?>
                <a class="small text-decoration-none" href="index.php">Xem tất cả</a>
            <?php } ?>
        </div>

        <div class="row g-2">
            <?php while ($row = $articles->fetch_assoc()) { ?>
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="card compact-card border-0 shadow-sm h-100 position-relative">
                        <div class="card-body">
                            <div class="thumb-frame compact mb-2">
                                <?php if (!empty($row["thumbnail"])) { ?>
                                    <img class="article-thumb" src="<?php echo htmlspecialchars($row["thumbnail"], ENT_QUOTES, "UTF-8"); ?>" alt="Thumbnail">
                                <?php } else { ?>
                                    <div class="thumb-placeholder">Chưa có ảnh</div>
                                <?php } ?>
                            </div>
                            <h3 class="h6 mb-1">
                                <a class="text-decoration-none text-dark stretched-link" href="article_detail.php?id=<?php echo (int)$row["article_id"]; ?>">
                                    <?php echo htmlspecialchars($row["title"], ENT_QUOTES, "UTF-8"); ?>
                                </a>
                            </h3>
                            <p class="text-muted small mb-2 clamp-2">
                                <?php echo htmlspecialchars($row["summary"], ENT_QUOTES, "UTF-8"); ?>
                            </p>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
                <h3 class="h6">Tin nổi bật</h3>
                <div class="list-group list-group-flush">
                    <?php while ($top = $top_articles->fetch_assoc()) { ?>
                        <a class="list-group-item list-group-item-action px-0" href="article_detail.php?id=<?php echo (int)$top["article_id"]; ?>">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="d-flex gap-2">
                                    <div class="mini-thumb">
                                        <?php if (!empty($top["thumbnail"])) { ?>
                                            <img src="<?php echo htmlspecialchars($top["thumbnail"], ENT_QUOTES, "UTF-8"); ?>" alt="Thumbnail">
                                        <?php } else { ?>
                                            <div class="thumb-placeholder small">Không có ảnh</div>
                                        <?php } ?>
                                    </div>
                                    <div>
                                        <div class="fw-semibold small">
                                            <?php echo htmlspecialchars($top["title"], ENT_QUOTES, "UTF-8"); ?>
                                        </div>
                                        <div class="small text-muted">
                                            <?php echo htmlspecialchars($top["category_name"], ENT_QUOTES, "UTF-8"); ?>
                                            <?php if (!empty($top["view_count"])) { ?>
                                                · <?php echo (int)$top["view_count"]; ?> lượt xem
                                            <?php } ?>
                                        </div>
                                    </div>
                                </div>
                                <span class="badge bg-danger-subtle text-danger">Hot</span>
                            </div>
                        </a>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include "includes/footer.php"; ?>

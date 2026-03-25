<?php include "includes/header.php"; ?>

<div class="row g-4">
    <div class="col-lg-8">
        <article class="card border-0 shadow-sm article-detail-card">
            <div class="card-body p-4 p-lg-5">
                <div class="d-flex flex-wrap align-items-center gap-2 mb-3 article-meta-top">
                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle">
                        <?php echo htmlspecialchars($article["category_name"], ENT_QUOTES, "UTF-8"); ?>
                    </span>
                    <?php if (!empty($article["created_at"])) { ?>
                        <span class="text-muted small"><?php echo date("d/m/Y H:i", strtotime($article["created_at"])); ?></span>
                    <?php } ?>
                    <span class="text-muted small"><?php echo (int)$article["view_count"] + 1; ?> lượt xem</span>
                </div>

                <h1 class="article-detail-title mb-3"><?php echo htmlspecialchars($article["title"], ENT_QUOTES, "UTF-8"); ?></h1>

                <?php if (!empty($article["summary"])) { ?>
                    <p class="article-detail-summary mb-4">
                        <?php echo htmlspecialchars($article["summary"], ENT_QUOTES, "UTF-8"); ?>
                    </p>
                <?php } ?>

                <?php if (!empty($article["thumbnail"])) { ?>
                    <div class="article-detail-hero mb-4">
                        <img src="<?php echo htmlspecialchars($article["thumbnail"], ENT_QUOTES, "UTF-8"); ?>" alt="Thumbnail bài viết">
                    </div>
                <?php } ?>

                <div class="article-content">
                    <?php echo $article["content"]; ?>
                </div>
            </div>
        </article>
    </div>

    <aside class="col-lg-4">
        <div class="card border-0 shadow-sm sidebar-card mb-4">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h2 class="h6 mb-0 text-uppercase">Bình luận</h2>
                    <span class="comment-count"><?php echo $comments->num_rows; ?></span>
                </div>

                <?php if ($comments->num_rows > 0) { ?>
                    <div class="comment-list">
                        <?php while ($comment = $comments->fetch_assoc()) { ?>
                            <?php $display_name = !empty($comment["full_name"]) ? $comment["full_name"] : $comment["username"]; ?>
                            <div class="comment-item">
                                <div class="d-flex align-items-start gap-3">
                                    <div class="comment-avatar">
                                        <?php echo strtoupper(substr($display_name, 0, 1)); ?>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                                            <span class="fw-semibold"><?php echo htmlspecialchars($display_name, ENT_QUOTES, "UTF-8"); ?></span>
                                            <?php if (!empty($comment["created_at"])) { ?>
                                                <span class="text-muted small"><?php echo date("d/m/Y H:i", strtotime($comment["created_at"])); ?></span>
                                            <?php } ?>
                                        </div>
                                        <p class="mb-0 text-muted"><?php echo nl2br(htmlspecialchars($comment["content"], ENT_QUOTES, "UTF-8")); ?></p>
                                    </div>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                <?php } else { ?>
                    <div class="empty-sidebar-state">
                        Chưa có bình luận nào cho bài viết này.
                    </div>
                <?php } ?>
            </div>
        </div>

        <div class="card border-0 shadow-sm sidebar-card">
            <div class="card-body">
                <h2 class="h6 mb-3 text-uppercase">Cùng thể loại</h2>

                <?php if ($related_articles->num_rows > 0) { ?>
                    <div class="related-list">
                        <?php while ($related = $related_articles->fetch_assoc()) { ?>
                            <a class="related-item" href="article_detail.php?id=<?php echo (int)$related["article_id"]; ?>">
                                <div class="related-thumb">
                                    <?php if (!empty($related["thumbnail"])) { ?>
                                        <img src="<?php echo htmlspecialchars($related["thumbnail"], ENT_QUOTES, "UTF-8"); ?>" alt="Thumbnail bài viết liên quan">
                                    <?php } else { ?>
                                        <div class="thumb-placeholder small">Không có ảnh</div>
                                    <?php } ?>
                                </div>
                                <div>
                                    <div class="related-title"><?php echo htmlspecialchars($related["title"], ENT_QUOTES, "UTF-8"); ?></div>
                                    <?php if (!empty($related["created_at"])) { ?>
                                        <div class="small text-muted mt-1"><?php echo date("d/m/Y", strtotime($related["created_at"])); ?></div>
                                    <?php } ?>
                                </div>
                            </a>
                        <?php } ?>
                    </div>
                <?php } else { ?>
                    <div class="empty-sidebar-state">
                        Chưa có bài viết nào khác trong cùng thể loại.
                    </div>
                <?php } ?>
            </div>
        </div>
    </aside>
</div>

<?php include "includes/footer.php"; ?>

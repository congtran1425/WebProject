<?php
function comment_display_name(array $comment)
{
    if (!empty($comment["full_name"])) {
        return $comment["full_name"];
    }

    return $comment["username"] ?? "Người dùng";
}

function comment_initial($name)
{
    if (function_exists("mb_substr")) {
        return mb_strtoupper(mb_substr($name, 0, 1, "UTF-8"), "UTF-8");
    }

    return strtoupper(substr($name, 0, 1));
}

function render_comment_nodes(array $comments, $articleId, $supportsReplies, $level = 0)
{
    if (empty($comments)) {
        return;
    }
    ?>
    <div class="<?php echo $level === 0 ? "comment-thread" : "comment-replies"; ?>">
        <?php foreach ($comments as $comment) { ?>
            <?php $displayName = comment_display_name($comment); ?>
            <article class="comment-card<?php echo $level > 0 ? " is-reply" : ""; ?>" id="comment-<?php echo (int)$comment["comment_id"]; ?>">
                <div class="d-flex align-items-start gap-3">
                    <div class="comment-avatar">
                        <?php echo htmlspecialchars(comment_initial($displayName), ENT_QUOTES, "UTF-8"); ?>
                    </div>
                    <div class="flex-grow-1">
                        <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                            <a class="fw-semibold text-decoration-none author-link" href="profile.php?id=<?php echo (int)$comment["user_id"]; ?>">
                                <?php echo htmlspecialchars($displayName, ENT_QUOTES, "UTF-8"); ?>
                            </a>
                            <?php if (!empty($comment["created_at"])) { ?>
                                <span class="text-muted small"><?php echo date("d/m/Y H:i", strtotime($comment["created_at"])); ?></span>
                            <?php } ?>
                            <?php if ($level > 0) { ?>
                                <span class="comment-level-badge">Phản hồi</span>
                            <?php } ?>
                        </div>

                        <div class="comment-body">
                            <?php echo nl2br(htmlspecialchars($comment["content"], ENT_QUOTES, "UTF-8")); ?>
                        </div>

                        <?php if ($supportsReplies) { ?>
                            <details class="comment-reply-box mt-3">
                                <summary>Trả lời</summary>
                                <form method="post" action="article_detail.php?id=<?php echo (int)$articleId; ?>#comment-<?php echo (int)$comment["comment_id"]; ?>" class="reply-form mt-3">
                                    <input type="hidden" name="comment_action" value="create">
                                    <input type="hidden" name="parent_comment_id" value="<?php echo (int)$comment["comment_id"]; ?>">
                                    <label class="form-label" for="reply-<?php echo (int)$comment["comment_id"]; ?>">Nội dung trả lời</label>
                                    <textarea class="form-control" id="reply-<?php echo (int)$comment["comment_id"]; ?>" name="content" rows="3" required></textarea>
                                    <div class="reply-form-actions">
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Gửi trả lời</button>
                                    </div>
                                </form>
                            </details>
                        <?php } ?>

                        <?php render_comment_nodes($comment["replies"], $articleId, $supportsReplies, $level + 1); ?>
                    </div>
                </div>
            </article>
        <?php } ?>
    </div>
    <?php
}
?>

<?php include "includes/header.php"; ?>

<div class="article-layout">
    <div class="article-layout-main">
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

                <?php if (!empty($article["user_id"])) { ?>
                    <div class="article-author-box mb-4">
                        <div class="article-author-avatar">
                            <?php if (!empty($article["avatar"])) { ?>
                                <img src="<?php echo htmlspecialchars($article["avatar"], ENT_QUOTES, "UTF-8"); ?>" alt="Tác giả">
                            <?php } else { ?>
                                <?php echo htmlspecialchars(comment_initial(comment_display_name($article)), ENT_QUOTES, "UTF-8"); ?>
                            <?php } ?>
                        </div>
                        <div>
                            <div class="small text-uppercase text-muted mb-1">Tác giả</div>
                            <a class="article-author-name" href="profile.php?id=<?php echo (int)$article["user_id"]; ?>">
                                <?php echo htmlspecialchars(comment_display_name($article), ENT_QUOTES, "UTF-8"); ?>
                            </a>
                        </div>
                    </div>
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

    <aside class="article-layout-side">
        <section class="card border-0 shadow-sm sidebar-card comment-section" id="comments">
            <div class="card-body">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
                    <div>
                        <h2 class="h6 mb-1 text-uppercase">Bình luận</h2>
                    </div>
                    <span class="comment-count"><?php echo (int)$comments["count"]; ?></span>
                </div>

                <?php if (!empty($comment_feedback)) { ?>
                    <div class="alert <?php echo !empty($comment_feedback["success"]) ? "alert-success" : "alert-danger"; ?> mb-4" role="alert">
                        <?php echo htmlspecialchars($comment_feedback["message"], ENT_QUOTES, "UTF-8"); ?>
                    </div>
                <?php } ?>

                <form method="post" action="article_detail.php?id=<?php echo (int)$article["article_id"]; ?>#comments" class="comment-form">
                    <input type="hidden" name="comment_action" value="create">
                    <label class="form-label" for="comment-content">Nội dung bình luận</label>
                    <textarea class="form-control" id="comment-content" name="content" rows="4" placeholder="Chia sẻ suy nghĩ của bạn..." required></textarea>
                    <div class="comment-form-actions">
                        <button type="submit" class="btn btn-sm btn-danger comment-submit-btn">Gửi bình luận</button>
                    </div>
                </form>

                <div class="comment-divider"></div>

                <?php if ($comments["count"] > 0) { ?>
                    <?php render_comment_nodes($comments["items"], (int)$article["article_id"], $comments["supports_replies"]); ?>
                <?php } else { ?>
                    <div class="empty-sidebar-state">
                        Chưa có bình luận nào cho bài viết này.
                    </div>
                <?php } ?>
            </div>
        </section>

        <div class="card border-0 shadow-sm sidebar-card mt-4">
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

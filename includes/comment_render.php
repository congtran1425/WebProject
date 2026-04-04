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
                                <form method="post" action="article_detail.php?id=<?php echo (int)$articleId; ?>#comment-<?php echo (int)$comment["comment_id"]; ?>" class="reply-form mt-3" data-comment-form="1" data-auth="<?php echo !empty($_SESSION["user_id"]) ? "1" : "0"; ?>">
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

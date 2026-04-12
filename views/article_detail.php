<?php
if (!empty($comment_feedback["message"])) {
    $toastMessages = $toastMessages ?? [];
    $toastMessages[] = [
        "message" => (string)$comment_feedback["message"],
        "type" => !empty($comment_feedback["success"]) ? "success" : "error",
    ];
}
include __DIR__ . "/../includes/comment_render.php";
include "includes/header.php";
?>

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
                    <span class="comment-count" id="comment-count"><?php echo (int)$comments["count"]; ?></span>
                </div>

                <form method="post" action="article_detail.php?id=<?php echo (int)$article["article_id"]; ?>#comments" class="comment-form" data-comment-form="1" data-auth="<?php echo !empty($_SESSION["user_id"]) ? "1" : "0"; ?>">
                    <input type="hidden" name="comment_action" value="create">
                    <label class="form-label" for="comment-content">Nội dung bình luận</label>
                    <textarea class="form-control" id="comment-content" name="content" rows="4" placeholder="Chia sẻ suy nghĩ của bạn..." required></textarea>
                    <div class="comment-form-actions">
                        <button type="submit" class="btn btn-sm btn-danger comment-submit-btn">Gửi bình luận</button>
                    </div>
                </form>

                <div class="comment-divider"></div>

                <div id="comment-thread">
<?php if ($comments["count"] > 0) { ?>
                    <?php render_comment_nodes($comments["items"], (int)$article["article_id"], $comments["supports_replies"]); ?>
                <?php } else { ?>
                    <div class="empty-sidebar-state">
                        Chưa có bình luận nào cho bài viết này.
                    </div>
                <?php } ?>
</div>
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

<script>
    (function () {
        var commentSection = document.getElementById("comments");
        if (!commentSection) {
            return;
        }

        var textarea = document.getElementById("comment-content");
        var articleId = <?php echo (int)$article["article_id"]; ?>;
        var storageKey = "pending_comment_" + articleId;

        if (textarea) {
            var saved = localStorage.getItem(storageKey);
            if (saved && textarea.value.trim() === "") {
                textarea.value = saved;
            }
        }

        var countEl = document.getElementById("comment-count");
        var threadEl = document.getElementById("comment-thread");

        var showAlert = function (message, isSuccess) {
            if (!window.appToast) {
                return;
            }
            window.appToast.show(String(message || "Có lỗi xảy ra."), isSuccess ? "success" : "error");
        };

        commentSection.addEventListener("submit", function (event) {
            var form = event.target.closest("form[data-comment-form=\"1\"]");
            if (!form) {
                return;
            }

            var isAuthed = form.getAttribute("data-auth") === "1";
            if (!isAuthed) {
                event.preventDefault();

                if (textarea && form.classList.contains("comment-form")) {
                    localStorage.setItem(storageKey, textarea.value);
                }

                if (window.bootstrap) {
                    var modalEl = document.getElementById("loginModal");
                    if (modalEl) {
                        var modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                        modal.show();
                        return;
                    }
                }

                window.location.href = "#loginModal";
                return;
            }

            event.preventDefault();

            var submitBtn = form.querySelector("button[type=\"submit\"]");
            if (submitBtn) {
                submitBtn.disabled = true;
            }

            var formData = new FormData(form);
            formData.append("article_id", String(articleId));

            fetch("api/comments.php", {
                method: "POST",
                body: formData
            })
                .then(function (res) { return res.json(); })
                .then(function (data) {
                    if (!data || !data.success) {
                        showAlert(data && data.message ? data.message : "Không thể gửi bình luận.");
                        return;
                    }

                    if (countEl && typeof data.count === "number") {
                        countEl.textContent = String(data.count);
                    }

                    if (threadEl && typeof data.html === "string") {
                        threadEl.innerHTML = data.html;
                    }

                    var contentField = form.querySelector("textarea[name=\"content\"]");
                    if (contentField) {
                        contentField.value = "";
                    }

                    if (form.classList.contains("reply-form")) {
                        var details = form.closest("details");
                        if (details) {
                            details.removeAttribute("open");
                        }
                    }

                    showAlert(data.message || "Đã gửi bình luận.", true);
                })
                .catch(function () {
                    showAlert("Không thể kết nối máy chủ.");
                })
                .finally(function () {
                    if (submitBtn) {
                        submitBtn.disabled = false;
                    }
                });
        });

        var mainForm = document.querySelector(".comment-form");
        var mainAuthed = mainForm ? (mainForm.getAttribute("data-auth") === "1") : false;
        if (mainAuthed && textarea) {
            localStorage.removeItem(storageKey);
        }
    })();
</script>

<?php include "includes/footer.php"; ?>

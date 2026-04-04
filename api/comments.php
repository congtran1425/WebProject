<?php
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    header("Content-Type: application/json; charset=UTF-8");
    echo json_encode([
        "success" => false,
        "message" => "Phương thức không hợp lệ.",
    ]);
    exit;
}

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . "/../controllers/ArticleController.php";
require_once __DIR__ . "/../includes/comment_render.php";

$articleId = (int)($_POST["article_id"] ?? 0);
if ($articleId <= 0) {
    http_response_code(400);
    header("Content-Type: application/json; charset=UTF-8");
    echo json_encode([
        "success" => false,
        "message" => "Thiếu bài viết.",
    ]);
    exit;
}

$controller = new ArticleController();
$result = $controller->submitComment($articleId, $_POST);
if (empty($result["success"])) {
    header("Content-Type: application/json; charset=UTF-8");
    echo json_encode($result);
    exit;
}

$comments = $controller->getComments($articleId);

ob_start();
if (!empty($comments["count"])) {
    render_comment_nodes($comments["items"], $articleId, $comments["supports_replies"]);
} else {
    ?>
    <div class="empty-sidebar-state">
        Chưa có bình luận nào cho bài viết này.
    </div>
    <?php
}
$html = ob_get_clean();

header("Content-Type: application/json; charset=UTF-8");
echo json_encode([
    "success" => true,
    "message" => $result["message"] ?? "Đã gửi bình luận.",
    "count" => (int)($comments["count"] ?? 0),
    "html" => $html,
]);

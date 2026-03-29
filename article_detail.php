<?php

require_once "controllers/ArticleController.php";

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$controller = new ArticleController();

$id = isset($_GET["id"]) ? (int)$_GET["id"] : 0;

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["comment_action"])) {
    $result = $controller->submitComment($id, $_POST);

    $_SESSION["comment_feedback"] = $result;
    header("Location: article_detail.php?id=" . $id . "#comments");
    exit;
}

$article = $controller->show($id);

if (!$article) {
    http_response_code(404);
    exit("Bài viết không tồn tại.");
}

$controller->increaseView($id);
$comments = $controller->getComments($id);
$related_articles = $controller->relatedArticles((int)$article["category_id"], $id, 3);
$comment_feedback = $_SESSION["comment_feedback"] ?? null;
unset($_SESSION["comment_feedback"]);

include "views/article_detail.php";

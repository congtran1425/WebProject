<?php

require_once "controllers/ArticleController.php";

$controller = new ArticleController();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$article = $controller->show($id);

if (!$article) {
    http_response_code(404);
    exit("Bài viết không tồn tại.");
}

$controller->increaseView($id);
$comments = $controller->getComments($id);
$related_articles = $controller->relatedArticles((int)$article["category_id"], $id, 4);

include "views/article_detail.php";

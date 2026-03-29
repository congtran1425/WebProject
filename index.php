<?php

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once "controllers/ArticleController.php";

$controller = new ArticleController();
$articles = $controller->index();
$top_articles = $controller->topViewed(6);

include "views/article_list.php";

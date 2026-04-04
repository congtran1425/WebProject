<?php

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once "controllers/ArticleController.php";

$controller = new ArticleController();
$createFeedback = $controller->create();

include "views/article_form.php";

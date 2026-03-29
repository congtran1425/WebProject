<?php

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once "controllers/ArticleController.php";

$controller = new ArticleController();
$controller->create();

include "views/article_form.php";

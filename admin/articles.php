<?php

require_once "../includes/admin_guard.php";

require_once "../controllers/AdminController.php";

$controller = new AdminController();
$controller->handleRequest();
$adminArticles = $controller->articlesData();

include "../views/admin_articles.php";


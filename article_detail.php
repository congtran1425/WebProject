<?php

require_once "controllers/ArticleController.php";

$controller = new ArticleController();

$id = $_GET['id'];

$article = $controller->show($id);

include "views/article_detail.php";
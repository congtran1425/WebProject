<?php

require_once "controllers/ArticleController.php";

$controller = new ArticleController();
$controller->create();

include "views/article_form.php";
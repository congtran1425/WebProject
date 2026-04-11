<?php

require_once "../includes/admin_guard.php";

require_once "../controllers/AdminController.php";

$controller = new AdminController();
$controller->handleRequest();
$adminComments = $controller->commentsData();

include "../views/admin_comments.php";


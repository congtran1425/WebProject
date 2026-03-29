<?php

require_once "../includes/admin_guard.php";

require_once "../controllers/AdminController.php";

$controller = new AdminController();
$controller->handleRequest();
$adminCategories = $controller->categoriesData();

include "../views/admin_categories.php";


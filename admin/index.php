<?php

require_once "../includes/admin_guard.php";

require_once "../controllers/AdminController.php";

$controller = new AdminController();
$controller->handleRequest();
$stats = $controller->dashboardStats();

include "../views/admin_dashboard.php";


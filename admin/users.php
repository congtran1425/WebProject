<?php

require_once "../includes/admin_guard.php";

require_once "../controllers/AdminController.php";

$controller = new AdminController();
$controller->handleRequest();
$adminUsers = $controller->usersData();

include "../views/admin_users.php";


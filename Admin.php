<?php

require_once "controllers/AdminController.php";

$controller = new AdminController();
$controller->handleRequest();
$data = $controller->dashboardData();

$adminUsers = $data["users"];
$adminCategories = $data["categories"];
$adminArticles = $data["articles"];
$adminComments = $data["comments"];

include "views/admin_dashboard.php";

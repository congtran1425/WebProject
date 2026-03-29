<?php
require_once __DIR__ . "/controllers/CategoryController.php";

$controller = new CategoryController();
$categoryId = isset($_GET["category_id"]) ? (int)$_GET["category_id"] : 0;
$controller->show($categoryId);

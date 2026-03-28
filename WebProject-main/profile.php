<?php

require_once "controllers/ProfileController.php";

$requestedUserId = isset($_GET["id"]) ? (int)$_GET["id"] : 0;

$controller = new ProfileController();
$controller->handleRequest($requestedUserId);
$pageData = $controller->getPageData($requestedUserId);

if (!$pageData) {
    http_response_code(404);
    exit("Không tìm thấy trang cá nhân.");
}

$profile = $pageData["profile"];
$stats = $pageData["stats"];
$articles = $pageData["articles"];
$comments = $pageData["comments"];
$canEdit = $pageData["can_edit"];
$usesFallbackUser = $pageData["uses_fallback_user"];
$profileFeedback = $pageData["flash"];

include "views/profile.php";

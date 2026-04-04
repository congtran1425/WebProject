<?php
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    header("Content-Type: application/json; charset=UTF-8");
    echo json_encode([
        "success" => false,
        "message" => "Phuong th?c không h?p l?.",
    ]);
    exit;
}

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$role = $_SESSION["role"] ?? "";
$userId = (int)($_SESSION["user_id"] ?? 0);
if ($userId <= 0 || !in_array($role, ["author", "editor", "admin"], true)) {
    http_response_code(403);
    header("Content-Type: application/json; charset=UTF-8");
    echo json_encode([
        "success" => false,
        "message" => "B?n không có quy?n dang bài.",
    ]);
    exit;
}

require_once __DIR__ . "/../controllers/ArticleController.php";

$controller = new ArticleController();
$result = $controller->createFromRequest($_POST, $_FILES, $userId);

header("Content-Type: application/json; charset=UTF-8");
echo json_encode([
    "success" => !empty($result["success"]),
    "message" => $result["message"] ?? "Không th? t?o bài vi?t.",
    "redirect" => !empty($result["success"]) ? "index.php" : null,
]);
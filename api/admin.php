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

if (($_SESSION["role"] ?? "") !== "admin") {
    http_response_code(403);
    header("Content-Type: application/json; charset=UTF-8");
    echo json_encode([
        "success" => false,
        "message" => "Không có quy?n truy c?p.",
    ]);
    exit;
}

require_once __DIR__ . "/../controllers/AdminController.php";

$controller = new AdminController();
$result = $controller->handleApi($_POST);

if ($result === null) {
    http_response_code(400);
    header("Content-Type: application/json; charset=UTF-8");
    echo json_encode([
        "success" => false,
        "message" => "Hành d?ng không h?p l?.",
    ]);
    exit;
}

header("Content-Type: application/json; charset=UTF-8");
echo json_encode([
    "success" => (bool)$result["success"],
    "message" => $result["success"] ? $result["success_message"] : $result["error_message"],
]);
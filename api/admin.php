<?php
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    header("Content-Type: application/json; charset=UTF-8");
    echo json_encode([
        "success" => false,
        "message" => "Phương thức không hợp lệ.",
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
        "message" => "Không có quyền truy cập.",
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
        "message" => "Hành động không hợp lệ.",
    ]);
    exit;
}

header("Content-Type: application/json; charset=UTF-8");
echo json_encode([
    "success" => (bool)$result["success"],
    "message" => $result["success"] ? $result["success_message"] : $result["error_message"],
    "payload" => $result["payload"] ?? new stdClass(),
]);

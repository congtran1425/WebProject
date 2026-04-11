<?php
if ($_SERVER["REQUEST_METHOD"] !== "GET") {
    http_response_code(405);
    header("Content-Type: application/json; charset=UTF-8");
    echo json_encode([
        "success" => false,
        "message" => "Phương thức không hợp lệ.",
    ]);
    exit;
}

require_once __DIR__ . "/../controllers/SearchController.php";

$controller = new SearchController();
$data = $controller->getSearchData();
extract($data);

ob_start();
include __DIR__ . "/../views/partials/search_results.php";
$html = ob_get_clean();

header("Content-Type: application/json; charset=UTF-8");
echo json_encode([
    "success" => true,
    "html" => $html,
]);

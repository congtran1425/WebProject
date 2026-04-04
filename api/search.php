<?php
if ($_SERVER["REQUEST_METHOD"] !== "GET") {
    http_response_code(405);
    header("Content-Type: application/json; charset=UTF-8");
    echo json_encode([
        "success" => false,
        "message" => "Phuong th?c không h?p l?.",
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
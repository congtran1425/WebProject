<?php
require_once __DIR__ . "/../config/Database.php";
require_once __DIR__ . "/../models/Category.php";
require_once __DIR__ . "/../services/SearchService.php";

class SearchController
{
    private $searchService;
    private $categoryModel;

    public function __construct()
    {
        $database = new Database();
        $db = $database->connect();

        $this->searchService = new SearchService($db);
        $this->categoryModel = new Category($db);
    }

    public function index()
    {
        $data = $this->getSearchData();
        extract($data);
        include __DIR__ . "/../views/search_result.php";
    }

    public function getSearchData()
    {
        $categoryResult = $this->categoryModel->getAllCategories();
        $categories = [];
        if ($categoryResult) {
            while ($row = $categoryResult->fetch_assoc()) {
                $categories[] = $row;
            }
        }

        $rawKeyword = $_GET["q"] ?? "";
        $keyword = trim(preg_replace("/\s+/", " ", (string)$rawKeyword));
        if (mb_strlen($keyword, "UTF-8") > 100) {
            $keyword = mb_substr($keyword, 0, 100, "UTF-8");
        }

        $categoryId = (int)($_GET["category_id"] ?? 0);
        $sort = $_GET["sort"] ?? "";
        $range = $_GET["range"] ?? "all";
        $page = max(1, (int)($_GET["page"] ?? 1));
        $perPage = 9;

        if (!in_array($sort, ["relevance", "newest", "oldest"], true)) {
            $sort = $keyword !== "" ? "relevance" : "newest";
        }

        $rangeOptions = ["all", "7d", "30d", "365d"];
        if (!in_array($range, $rangeOptions, true)) {
            $range = "all";
        }

        $dateFrom = null;
        $dateTo = null;
        if ($range !== "all") {
            $dateTo = date("Y-m-d H:i:s");
            if ($range === "7d") {
                $dateFrom = date("Y-m-d H:i:s", strtotime("-7 days"));
            } elseif ($range === "30d") {
                $dateFrom = date("Y-m-d H:i:s", strtotime("-30 days"));
            } elseif ($range === "365d") {
                $dateFrom = date("Y-m-d H:i:s", strtotime("-365 days"));
            }
        }

        $minKeywordLen = 2;
        $validationError = null;
        if ($keyword !== "" && mb_strlen($keyword, "UTF-8") < $minKeywordLen) {
            $validationError = "Tu khoa can it nhat " . $minKeywordLen . " ky tu.";
        }

        $total = 0;
        $articles = null;
        $shouldSearch = $validationError === null && ($keyword !== "" || $categoryId > 0 || $range !== "all");

        if ($shouldSearch) {
            $total = $this->searchService->countSearch($keyword, $categoryId, $dateFrom, $dateTo);
            $totalPages = max(1, (int)ceil($total / $perPage));
            if ($page > $totalPages) {
                $page = 1;
            }
            $offset = ($page - 1) * $perPage;
            $articles = $this->searchService->searchByKeyword($keyword, $categoryId, $dateFrom, $dateTo, $sort, $perPage, $offset);
        } else {
            $totalPages = 1;
        }

        return compact(
            "categories",
            "keyword",
            "categoryId",
            "sort",
            "range",
            "page",
            "perPage",
            "total",
            "totalPages",
            "articles",
            "shouldSearch",
            "validationError"
        );
    }
}

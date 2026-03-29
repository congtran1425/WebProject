<?php
require_once __DIR__ . "/../config/Database.php";
require_once __DIR__ . "/../models/Article.php";
require_once __DIR__ . "/../models/Category.php";

class CategoryController
{
    private $article;
    private $category;

    public function __construct()
    {
        $database = new Database();
        $db = $database->connect();

        $this->article = new Article($db);
        $this->category = new Category($db);
    }

    public function show($categoryId)
    {
        $categoryId = (int)$categoryId;
        if ($categoryId <= 0) {
            header("Location: index.php");
            exit;
        }

        $categoryInfo = $this->category->getCategoryById($categoryId);
        if (!$categoryInfo) {
            http_response_code(404);
            exit("Danh mục không tồn tại.");
        }

        $articles = $this->article->getArticlesByCategory($categoryId);
        $top_articles = $this->article->getTopViewed(6);

        include __DIR__ . "/../views/category_result.php";
    }
}

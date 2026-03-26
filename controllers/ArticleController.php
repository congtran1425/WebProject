<?php

require_once __DIR__ . "/../config/Database.php";
require_once __DIR__ . "/../models/Article.php";

class ArticleController
{

    private $article;

    public function __construct()
    {

        $database = new Database();
        $db = $database->connect();

        $this->article = new Article($db);
    }

    public function create()
    {

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $title = $_POST['title'];
            $summary = $_POST['summary'];
            $content = $_POST['content'];
            $category = $_POST['category'];
            $thumbnail_path = null;

            if (isset($_FILES["thumbnail"]) && $_FILES["thumbnail"]["error"] === UPLOAD_ERR_OK) {
                $thumbnail_path = $this->handleThumbnailUpload($_FILES["thumbnail"]);
            }

            $this->article->createArticle($title, $summary, $content, $category, $thumbnail_path);

            header("Location: index.php?submission=pending");
            exit;
        }
    }

    public function index()
    {
        if (isset($_GET["category_id"])) {
            $category_id = (int)$_GET["category_id"];
            return $this->article->getArticlesByCategory($category_id);
        }

        return $this->article->getAllArticles();
    }

    public function topViewed($limit = 5)
    {
        return $this->article->getTopViewed($limit);
    }
    public function show($id)
    {
        $article = $this->article->getArticleById($id);
        if ($article) {
            $this->article->increaseView($id);
            $article["view_count"] = (int)$article["view_count"] + 1;
        }

        return $article;
    }

    private function handleThumbnailUpload($file)
    {
        $allowed = ["image/jpeg", "image/png", "image/webp"];
        if (!in_array($file["type"], $allowed, true)) {
            return null;
        }

        if ($file["size"] > 2 * 1024 * 1024) {
            return null;
        }

        $upload_dir = __DIR__ . "/../assets/uploads/";
        $ext = pathinfo($file["name"], PATHINFO_EXTENSION);
        $basename = uniqid("thumb_", true);
        $original_path = $upload_dir . $basename . "." . $ext;

        if (!move_uploaded_file($file["tmp_name"], $original_path)) {
            return null;
        }

        if (!function_exists("imagecreatefromjpeg")) {
            return "assets/uploads/" . $basename . "." . $ext;
        }

        $thumb_path = $upload_dir . $basename . "_small." . $ext;
        $this->createThumbnail($original_path, $thumb_path, 600);

        return "assets/uploads/" . $basename . "_small." . $ext;
    }

    private function createThumbnail($source, $dest, $targetWidth)
    {
        $info = getimagesize($source);
        if ($info === false) {
            return false;
        }

        [$width, $height] = $info;
        if ($width <= 0 || $height <= 0) {
            return false;
        }

        $mime = $info["mime"];
        if ($mime === "image/jpeg") {
            $src_img = imagecreatefromjpeg($source);
        } elseif ($mime === "image/png") {
            $src_img = imagecreatefrompng($source);
        } elseif ($mime === "image/webp") {
            $src_img = imagecreatefromwebp($source);
        } else {
            return false;
        }

        $ratio = $targetWidth / $width;
        $targetHeight = (int)($height * $ratio);
        $dst_img = imagecreatetruecolor($targetWidth, $targetHeight);

        if ($mime === "image/png") {
            imagealphablending($dst_img, false);
            imagesavealpha($dst_img, true);
        }

        imagecopyresampled($dst_img, $src_img, 0, 0, 0, 0, $targetWidth, $targetHeight, $width, $height);

        if ($mime === "image/jpeg") {
            imagejpeg($dst_img, $dest, 85);
        } elseif ($mime === "image/png") {
            imagepng($dst_img, $dest);
        } elseif ($mime === "image/webp") {
            imagewebp($dst_img, $dest, 85);
        }

        imagedestroy($src_img);
        imagedestroy($dst_img);

        return true;
    }
}

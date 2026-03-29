<?php

require_once __DIR__ . "/../config/Database.php";
require_once __DIR__ . "/../models/Article.php";
require_once __DIR__ . "/../models/Comment.php";

class ArticleController
{

    private $article;
    private $comment;

    public function __construct()
    {

        $database = new Database();
        $db = $database->connect();

        $this->article = new Article($db);
        $this->comment = new Comment($db);
    }

    public function create()
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $role = $_SESSION["role"] ?? "";
        $userId = (int)($_SESSION["user_id"] ?? 0);
        if ($userId <= 0 || !in_array($role, ["author", "editor", "admin"], true)) {
            header("Location: index.php");
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $title = $_POST['title'];
            $summary = $_POST['summary'];
            $content = $_POST['content'];
            $category = $_POST['category'];
            $thumbnail_path = null;

            if (isset($_FILES["thumbnail"]) && $_FILES["thumbnail"]["error"] === UPLOAD_ERR_OK) {
                $thumbnail_path = $this->handleThumbnailUpload($_FILES["thumbnail"]);
            }

            $this->article->createArticle($title, $summary, $content, $category, $userId, $thumbnail_path);

            header("Location: index.php");
        }
    }

    public function index()
    {
        return $this->article->getAllArticles();
    }

    public function topViewed($limit = 5)
    {
        return $this->article->getTopViewed($limit);
    }
    public function show($id)
    {
        return $this->article->getArticleById($id);
    }

    public function getComments($articleId)
    {
        $result = $this->comment->getCommentsByArticle($articleId);
        $comments = [];

        while ($row = $result->fetch_assoc()) {
            $row["replies"] = [];
            $comments[(int)$row["comment_id"]] = $row;
        }

        $tree = [];
        foreach ($comments as $commentId => &$comment) {
            $parentId = isset($comment["parent_comment_id"]) ? (int)$comment["parent_comment_id"] : 0;

            if ($parentId > 0 && isset($comments[$parentId])) {
                $comments[$parentId]["replies"][] = &$comment;
                continue;
            }

            $tree[] = &$comment;
        }
        unset($comment);

        return [
            "items" => $tree,
            "count" => count($comments),
            "supports_replies" => $this->comment->supportsReplies(),
        ];
    }

    public function relatedArticles($categoryId, $articleId, $limit = 4)
    {
        return $this->article->getRelatedArticles($categoryId, $articleId, $limit);
    }

    public function increaseView($articleId)
    {
        return $this->article->increaseView($articleId);
    }

    public function submitComment($articleId, array $data)
    {
        $content = trim($data["content"] ?? "");
        $parentCommentId = isset($data["parent_comment_id"]) ? (int)$data["parent_comment_id"] : 0;

        if ($content === "") {
            return [
                "success" => false,
                "message" => "Vui lÃ²ng nháº­p ná»™i dung bÃ¬nh luáº­n.",
            ];
        }

        if ($parentCommentId > 0 && !$this->comment->supportsReplies()) {
            return [
                "success" => false,
                "message" => "CÆ¡ sá»Ÿ dá»¯ liá»‡u chÆ°a há»— trá»£ tráº£ lá»i bÃ¬nh luáº­n.",
            ];
        }

        $userId = $this->resolveCommentUserId();
        if ($userId <= 0) {
            return [
                "success" => false,
                "message" => "KhÃ´ng tÃ¬m tháº¥y tÃ i khoáº£n Ä‘á»ƒ gá»­i bÃ¬nh luáº­n.",
            ];
        }

        $saved = $this->comment->createComment($articleId, $userId, $content, $parentCommentId);
        if (!$saved) {
            return [
                "success" => false,
                "message" => "KhÃ´ng thá»ƒ lÆ°u bÃ¬nh luáº­n. Vui lÃ²ng thá»­ láº¡i.",
            ];
        }

        return [
            "success" => true,
            "message" => $parentCommentId > 0 ? "ÄÃ£ gá»­i tráº£ lá»i bÃ¬nh luáº­n." : "ÄÃ£ gá»­i bÃ¬nh luáº­n.",
        ];
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

    private function resolveCommentUserId()
    {
        if (session_status() === PHP_SESSION_ACTIVE && isset($_SESSION["user_id"])) {
            return (int)$_SESSION["user_id"];
        }

        return 0;
    }
}


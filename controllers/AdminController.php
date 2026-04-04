<?php

require_once __DIR__ . "/../config/Database.php";
require_once __DIR__ . "/../models/Admin.php";

class AdminController
{
    private $admin;

    public function __construct()
    {
        $database = new Database();
        $db = $database->connect();

        $this->admin = new Admin($db);
    }

    public function handleRequest()
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        if (($_SESSION["role"] ?? "") !== "admin") {
            header("Location: index.php");
            exit;
        }

        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            return;
        }

        $action = $_POST["action"] ?? "";
        $redirect = $this->normalizeRedirect($_POST["redirect"] ?? "index.php");
        $result = $this->processAction($action, $_POST);
        if ($result !== null) {
            $this->redirectWithMessage($result["success"], $result["success_message"], $result["error_message"], $redirect);
        }
    }

    public function handleApi(array $data)
    {
        $action = $data["action"] ?? "";
        return $this->processAction($action, $data);
    }

    public function dashboardStats()
    {
        return [
            "users" => $this->admin->countUsers(),
            "categories" => $this->admin->countCategories(),
            "articles" => $this->admin->countArticles(),
            "comments" => $this->admin->countComments(),
        ];
    }

    public function usersData()
    {
        return $this->admin->manageUsers();
    }

    public function categoriesData()
    {
        return $this->admin->manageCategory();
    }

    public function articlesData()
    {
        return $this->admin->getAllArticles();
    }

    public function commentsData()
    {
        return $this->admin->getAllComments();
    }

    private function redirectWithMessage($success, $successMessage, $errorMessage, $redirect)
    {
        $query = $success
            ? "?status=success&message=" . urlencode($successMessage)
            : "?status=error&message=" . urlencode($errorMessage);

        header("Location: " . $redirect . $query);
        exit;
    }

    private function normalizeRedirect($redirect)
    {
        $allowed = [
            "index.php",
            "users.php",
            "categories.php",
            "articles.php",
            "comments.php",
        ];

        $redirect = basename(trim((string)$redirect));
        if (!in_array($redirect, $allowed, true)) {
            return "index.php";
        }

        return $redirect;
    }

    private function processAction($action, array $data)
    {
        switch ($action) {
            case "update_user_role":
                $success = $this->admin->updateUserRole((int)($data["user_id"] ?? 0), trim($data["role"] ?? ""));
                return [
                    "success" => $success,
                    "success_message" => "Đã cập nhật quyền người dùng.",
                    "error_message" => "Không thể cập nhật quyền người dùng.",
                ];

            case "update_user_status":
                $success = $this->admin->updateUserStatus((int)($data["user_id"] ?? 0), trim($data["status"] ?? ""));
                return [
                    "success" => $success,
                    "success_message" => "Đã cập nhật trạng thái người dùng.",
                    "error_message" => "Không thể cập nhật trạng thái người dùng.",
                ];

            case "create_category":
                $success = $this->admin->createCategory(
                    trim($data["category_name"] ?? ""),
                    trim($data["description"] ?? "")
                );
                return [
                    "success" => $success,
                    "success_message" => "Đã thêm danh mục.",
                    "error_message" => "Không thể thêm danh mục.",
                ];

            case "update_category":
                $success = $this->admin->updateCategory(
                    (int)($data["category_id"] ?? 0),
                    trim($data["category_name"] ?? ""),
                    trim($data["description"] ?? "")
                );
                return [
                    "success" => $success,
                    "success_message" => "Đã cập nhật danh mục.",
                    "error_message" => "Không thể cập nhật danh mục.",
                ];

            case "delete_category":
                $success = $this->admin->deleteCategory((int)($data["category_id"] ?? 0));
                return [
                    "success" => $success,
                    "success_message" => "Đã xóa danh mục.",
                    "error_message" => "Không thể xóa danh mục. Có thể danh mục đang được bài viết sử dụng.",
                ];

            case "update_article_status":
                $success = $this->admin->updateArticleStatus(
                    (int)($data["article_id"] ?? 0),
                    trim($data["status"] ?? "")
                );
                return [
                    "success" => $success,
                    "success_message" => "Đã cập nhật trạng thái bài viết.",
                    "error_message" => "Không thể cập nhật trạng thái bài viết.",
                ];

            case "delete_article":
                $success = $this->admin->deleteArticle((int)($data["article_id"] ?? 0));
                return [
                    "success" => $success,
                    "success_message" => "Đã xóa bài viết.",
                    "error_message" => "Không thể xóa bài viết.",
                ];

            case "update_comment_status":
                $success = $this->admin->updateCommentStatus(
                    (int)($data["comment_id"] ?? 0),
                    trim($data["status"] ?? "")
                );
                return [
                    "success" => $success,
                    "success_message" => "Đã cập nhật bình luận.",
                    "error_message" => "Không thể cập nhật bình luận.",
                ];
        }

        return null;
    }
}

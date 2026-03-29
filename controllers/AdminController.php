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

        switch ($action) {
            case "update_user_role":
                $success = $this->admin->updateUserRole((int)($_POST["user_id"] ?? 0), trim($_POST["role"] ?? ""));
                $this->redirectWithMessage($success, "Đã cập nhật quyền người dùng.", "Không thể cập nhật quyền người dùng.", $redirect);
                break;

            case "update_user_status":
                $success = $this->admin->updateUserStatus((int)($_POST["user_id"] ?? 0), trim($_POST["status"] ?? ""));
                $this->redirectWithMessage($success, "Đã cập nhật trạng thái người dùng.", "Không thể cập nhật trạng thái người dùng.", $redirect);
                break;

            case "create_category":
                $success = $this->admin->createCategory(
                    trim($_POST["category_name"] ?? ""),
                    trim($_POST["description"] ?? "")
                );
                $this->redirectWithMessage($success, "Đã thêm danh mục.", "Không thể thêm danh mục.", $redirect);
                break;

            case "update_category":
                $success = $this->admin->updateCategory(
                    (int)($_POST["category_id"] ?? 0),
                    trim($_POST["category_name"] ?? ""),
                    trim($_POST["description"] ?? "")
                );
                $this->redirectWithMessage($success, "Đã cập nhật danh mục.", "Không thể cập nhật danh mục.", $redirect);
                break;

            case "delete_category":
                $success = $this->admin->deleteCategory((int)($_POST["category_id"] ?? 0));
                $this->redirectWithMessage($success, "Đã xóa danh mục.", "Không thể xóa danh mục. Có thể danh mục đang được bài viết sử dụng.", $redirect);
                break;

            case "update_article_status":
                $success = $this->admin->updateArticleStatus(
                    (int)($_POST["article_id"] ?? 0),
                    trim($_POST["status"] ?? "")
                );
                $this->redirectWithMessage($success, "Đã cập nhật trạng thái bài viết.", "Không thể cập nhật trạng thái bài viết.", $redirect);
                break;

            case "delete_article":
                $success = $this->admin->deleteArticle((int)($_POST["article_id"] ?? 0));
                $this->redirectWithMessage($success, "Đã xóa bài viết.", "Không thể xóa bài viết.", $redirect);
                break;

            case "update_comment_status":
                $success = $this->admin->updateCommentStatus(
                    (int)($_POST["comment_id"] ?? 0),
                    trim($_POST["status"] ?? "")
                );
                $this->redirectWithMessage($success, "Đã cập nhật bình luận.", "Không thể cập nhật bình luận.", $redirect);
                break;
        }
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
}

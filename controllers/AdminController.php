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

    private function buildResult($success, $successMessage, $errorMessage, array $payload = [])
    {
        return [
            "success" => (bool)$success,
            "success_message" => $successMessage,
            "error_message" => $errorMessage,
            "payload" => $payload,
        ];
    }

    private function processAction($action, array $data)
    {
        switch ($action) {
            case "update_user_role":
                $userId = (int)($data["user_id"] ?? 0);
                $role = trim((string)($data["role"] ?? ""));
                $success = $this->admin->updateUserRole($userId, $role);
                return $this->buildResult(
                    $success,
                    "Đã cập nhật quyền người dùng.",
                    "Không thể cập nhật quyền người dùng.",
                    [
                        "user_id" => $userId,
                        "role" => $role,
                    ]
                );

            case "update_user_status":
                $userId = (int)($data["user_id"] ?? 0);
                $status = trim((string)($data["status"] ?? ""));
                $success = $this->admin->updateUserStatus($userId, $status);
                return $this->buildResult(
                    $success,
                    "Đã cập nhật trạng thái người dùng.",
                    "Không thể cập nhật trạng thái người dùng.",
                    [
                        "user_id" => $userId,
                        "status" => $status,
                    ]
                );

            case "create_category":
                $success = $this->admin->createCategory(
                    trim((string)($data["category_name"] ?? "")),
                    trim((string)($data["description"] ?? ""))
                );
                return $this->buildResult(
                    $success,
                    "Đã thêm danh mục.",
                    "Không thể thêm danh mục."
                );

            case "update_category":
                $categoryId = (int)($data["category_id"] ?? 0);
                $success = $this->admin->updateCategory(
                    $categoryId,
                    trim((string)($data["category_name"] ?? "")),
                    trim((string)($data["description"] ?? ""))
                );
                return $this->buildResult(
                    $success,
                    "Đã cập nhật danh mục.",
                    "Không thể cập nhật danh mục.",
                    [
                        "category_id" => $categoryId,
                    ]
                );

            case "delete_category":
                $categoryId = (int)($data["category_id"] ?? 0);
                $success = $this->admin->deleteCategory($categoryId);
                return $this->buildResult(
                    $success,
                    "Đã xóa danh mục.",
                    "Không thể xóa danh mục. Có thể danh mục đang được bài viết sử dụng.",
                    [
                        "category_id" => $categoryId,
                        "deleted" => (bool)$success,
                    ]
                );

            case "update_article_status":
                $articleId = (int)($data["article_id"] ?? 0);
                $status = trim((string)($data["status"] ?? ""));
                $success = $this->admin->updateArticleStatus($articleId, $status);
                return $this->buildResult(
                    $success,
                    "Đã cập nhật trạng thái bài viết.",
                    "Không thể cập nhật trạng thái bài viết.",
                    [
                        "article_id" => $articleId,
                        "status" => $status,
                    ]
                );

            case "delete_article":
                $articleId = (int)($data["article_id"] ?? 0);
                $success = $this->admin->deleteArticle($articleId);
                return $this->buildResult(
                    $success,
                    "Đã xóa bài viết.",
                    "Không thể xóa bài viết.",
                    [
                        "article_id" => $articleId,
                        "deleted" => (bool)$success,
                    ]
                );

            case "update_comment_status":
                $commentId = (int)($data["comment_id"] ?? 0);
                $status = trim((string)($data["status"] ?? ""));
                $success = $this->admin->updateCommentStatus($commentId, $status);
                return $this->buildResult(
                    $success,
                    "Đã cập nhật bình luận.",
                    "Không thể cập nhật bình luận.",
                    [
                        "comment_id" => $commentId,
                        "status" => $status,
                    ]
                );
        }

        return null;
    }
}

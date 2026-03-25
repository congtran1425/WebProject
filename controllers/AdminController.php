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
        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            return;
        }

        $action = $_POST["action"] ?? "";

        switch ($action) {
            case "update_user_role":
                $success = $this->admin->updateUserRole((int)($_POST["user_id"] ?? 0), trim($_POST["role"] ?? ""));
                $this->redirectWithMessage($success, "Đã cập nhật quyền người dùng.", "Không thể cập nhật quyền người dùng.");
                break;

            case "create_category":
                $success = $this->admin->createCategory(
                    trim($_POST["category_name"] ?? ""),
                    trim($_POST["description"] ?? "")
                );
                $this->redirectWithMessage($success, "Đã thêm danh mục.", "Không thể thêm danh mục.");
                break;

            case "update_category":
                $success = $this->admin->updateCategory(
                    (int)($_POST["category_id"] ?? 0),
                    trim($_POST["category_name"] ?? ""),
                    trim($_POST["description"] ?? "")
                );
                $this->redirectWithMessage($success, "Đã cập nhật danh mục.", "Không thể cập nhật danh mục.");
                break;

            case "delete_category":
                $success = $this->admin->deleteCategory((int)($_POST["category_id"] ?? 0));
                $this->redirectWithMessage($success, "Đã xóa danh mục.", "Không thể xóa danh mục. Có thể danh mục đang được bài viết sử dụng.");
                break;

            case "update_article_status":
                $success = $this->admin->updateArticleStatus(
                    (int)($_POST["article_id"] ?? 0),
                    trim($_POST["status"] ?? "")
                );
                $this->redirectWithMessage($success, "Đã cập nhật trạng thái bài viết.", "Không thể cập nhật trạng thái bài viết.");
                break;

            case "delete_article":
                $success = $this->admin->deleteArticle((int)($_POST["article_id"] ?? 0));
                $this->redirectWithMessage($success, "Đã xóa bài viết.", "Không thể xóa bài viết.");
                break;

            case "update_comment_status":
                $success = $this->admin->updateCommentStatus(
                    (int)($_POST["comment_id"] ?? 0),
                    trim($_POST["status"] ?? "")
                );
                $this->redirectWithMessage($success, "Đã cập nhật bình luận.", "Không thể cập nhật bình luận.");
                break;
        }
    }

    public function dashboardData()
    {
        return [
            "users" => $this->admin->manageUsers(),
            "categories" => $this->admin->manageCategory(),
            "articles" => $this->admin->getAllArticles(),
            "comments" => $this->admin->getAllComments(),
        ];
    }

    private function redirectWithMessage($success, $successMessage, $errorMessage)
    {
        $query = $success
            ? "?status=success&message=" . urlencode($successMessage)
            : "?status=error&message=" . urlencode($errorMessage);

        header("Location: admin.php" . $query);
        exit;
    }
}

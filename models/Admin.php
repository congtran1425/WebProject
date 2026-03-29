<?php
require_once "User.php";

class Admin extends User {

    public function manageUsers() {
        $sql = "SELECT user_id, username, email, role, status, created_at
                FROM user
                ORDER BY created_at DESC";
        return $this->conn->query($sql);
    }

    public function updateUserRole($userId, $role) {
        $allowedRoles = ['admin', 'editor', 'author', 'reader'];
        if (!in_array($role, $allowedRoles, true)) {
            return false;
        }

        $sql = "UPDATE user SET role=? WHERE user_id=?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("si", $role, $userId);

        return $stmt->execute();
    }

    public function updateUserStatus($userId, $status) {
        $allowedStatuses = ['active', 'inactive', 'banned'];
        if (!in_array($status, $allowedStatuses, true)) {
            return false;
        }

        $sql = "UPDATE user SET status=? WHERE user_id=?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("si", $status, $userId);

        return $stmt->execute();
    }

    public function approveArticle($articleId) {

        $sql = "UPDATE article SET status='published' WHERE article_id=?";
        $stmt = $this->conn->prepare($sql);

        $stmt->bind_param("i", $articleId);

        return $stmt->execute();
    }

    public function deleteArticle($articleId) {
        $thumbnailPath = $this->getArticleThumbnailPath($articleId);

        $sql = "DELETE FROM article WHERE article_id=?";
        $stmt = $this->conn->prepare($sql);

        $stmt->bind_param("i", $articleId);

        if (!$stmt->execute()) {
            return false;
        }

        return $this->deleteThumbnailFiles($thumbnailPath);
    }

    public function manageCategory() {
        $sql = "SELECT *
                FROM category
                ORDER BY category_name ASC";
        return $this->conn->query($sql);
    }

    public function createCategory($name, $description) {
        $sql = "INSERT INTO category (category_name, description) VALUES (?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ss", $name, $description);

        return $stmt->execute();
    }

    public function updateCategory($categoryId, $name, $description) {
        $sql = "UPDATE category SET category_name=?, description=? WHERE category_id=?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ssi", $name, $description, $categoryId);

        return $stmt->execute();
    }

    public function deleteCategory($categoryId) {
        $sql = "DELETE FROM category WHERE category_id=?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $categoryId);

        return $stmt->execute();
    }

    public function getAllArticles() {
        $sql = "SELECT a.article_id, a.title, a.status, a.view_count, a.created_at,
                       c.category_name, u.username
                FROM article a
                JOIN category c ON a.category_id = c.category_id
                JOIN user u ON a.user_id = u.user_id
                ORDER BY FIELD(a.status, 'pending', 'draft', 'published', 'archived'), a.created_at DESC";
        return $this->conn->query($sql);
    }

    public function updateArticleStatus($articleId, $status) {
        $allowedStatuses = ['draft', 'pending', 'published', 'archived'];
        if (!in_array($status, $allowedStatuses, true)) {
            return false;
        }

        $sql = "UPDATE article SET status=? WHERE article_id=?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("si", $status, $articleId);

        return $stmt->execute();
    }

    public function getAllComments() {
        $sql = "SELECT cm.comment_id, cm.content, cm.status, cm.created_at,
                       a.article_id, a.title AS article_title,
                       u.user_id, u.username
                FROM comment cm
                JOIN article a ON cm.article_id = a.article_id
                JOIN user u ON cm.user_id = u.user_id
                ORDER BY cm.created_at DESC";
        return $this->conn->query($sql);
    }

    public function updateCommentStatus($commentId, $status) {
        $allowedStatuses = ['visible', 'hidden', 'deleted'];
        if (!in_array($status, $allowedStatuses, true)) {
            return false;
        }

        $sql = "UPDATE comment SET status=? WHERE comment_id=?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("si", $status, $commentId);

        return $stmt->execute();
    }

    public function countUsers() {
        return $this->countTable("user");
    }

    public function countCategories() {
        return $this->countTable("category");
    }

    public function countArticles() {
        return $this->countTable("article");
    }

    public function countComments() {
        return $this->countTable("comment");
    }

    private function countTable($table) {
        $sql = "SELECT COUNT(*) AS total FROM " . $table;
        $result = $this->conn->query($sql);
        if (!$result) {
            return 0;
        }
        $row = $result->fetch_assoc();
        return (int)($row["total"] ?? 0);
    }

    private function getArticleThumbnailPath($articleId) {
        $sql = "SELECT thumbnail FROM article WHERE article_id=?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $articleId);
        $stmt->execute();

        $article = $stmt->get_result()->fetch_assoc();

        return $article["thumbnail"] ?? null;
    }

    private function deleteThumbnailFiles($thumbnailPath) {
        if (empty($thumbnailPath)) {
            return true;
        }

        $uploadDir = realpath(__DIR__ . "/../assets/uploads");
        if ($uploadDir === false) {
            return false;
        }

        $targets = $this->buildThumbnailTargets($thumbnailPath);

        foreach ($targets as $target) {
            $fullPath = $uploadDir . DIRECTORY_SEPARATOR . $target;

            if (!is_file($fullPath)) {
                continue;
            }

            if (!@unlink($fullPath)) {
                return false;
            }
        }

        return true;
    }

    private function buildThumbnailTargets($thumbnailPath) {
        $relativePath = str_replace("\\", "/", trim($thumbnailPath));
        $prefix = "assets/uploads/";

        if (strncmp($relativePath, $prefix, strlen($prefix)) !== 0) {
            return [];
        }

        $filename = basename(substr($relativePath, strlen($prefix)));
        if ($filename === "" || $filename === "." || $filename === "..") {
            return [];
        }

        $targets = [$filename];
        $pathInfo = pathinfo($filename);
        $name = $pathInfo["filename"] ?? "";
        $extension = isset($pathInfo["extension"]) ? "." . $pathInfo["extension"] : "";

        if ($name !== "" && str_ends_with($name, "_small")) {
            $targets[] = substr($name, 0, -6) . $extension;
        } elseif ($name !== "") {
            $targets[] = $name . "_small" . $extension;
        }

        return array_values(array_unique($targets));
    }
}
?>

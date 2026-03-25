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

    public function approveArticle($articleId) {

        $sql = "UPDATE article SET status='published' WHERE article_id=?";
        $stmt = $this->conn->prepare($sql);

        $stmt->bind_param("i", $articleId);

        return $stmt->execute();
    }

    public function deleteArticle($articleId) {

        $sql = "DELETE FROM article WHERE article_id=?";
        $stmt = $this->conn->prepare($sql);

        $stmt->bind_param("i", $articleId);

        return $stmt->execute();
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
}
?>

<?php
require_once "User.php";

class Admin extends User {

    public function manageUsers() {
        $sql = "SELECT * FROM users";
        return $this->conn->query($sql);
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
        $sql = "SELECT * FROM category";
        return $this->conn->query($sql);
    }
}
?>
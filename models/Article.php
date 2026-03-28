<?php
class Article
{

    private $conn;
    private $table = "article";

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function publish($articleId)
    {

        $sql = "UPDATE article SET status='published'
                WHERE article_id=?";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $articleId);

        return $stmt->execute();
    }

    public function updateContent($articleId, $content)
    {

        $sql = "UPDATE article SET content=? WHERE article_id=?";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("si", $content, $articleId);

        return $stmt->execute();
    }

    public function increaseView($articleId)
    {

        $sql = "UPDATE article SET view_count=view_count+1
                WHERE article_id=?";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $articleId);

        return $stmt->execute();
    }

    public function createArticle($title, $summary, $content, $categoryId, $thumbnailPath = null)
    {
        $sql = "INSERT INTO article (title, summary, content, thumbnail, category_id, user_id, status)
            VALUES (?, ?, ?, ?, ?, 1, 'published')";

        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            die("Prepare failed: " . $this->conn->error);
        }

        $stmt->bind_param("ssssi", $title, $summary, $content, $thumbnailPath, $categoryId);

        if (!$stmt->execute()) {
            die("Execute failed: " . $stmt->error);
        }

        return true;
    }
    public function getAllArticles()
    {
        $sql = "SELECT a.*, c.category_name, u.username, u.full_name, u.avatar
            FROM article a
            JOIN category c ON a.category_id = c.category_id
            JOIN user u ON a.user_id = u.user_id
            ORDER BY created_at DESC";

        return $this->conn->query($sql);
    }

    public function getArticlesByCategory($categoryId)
    {
        $sql = "SELECT a.*, c.category_name, u.username, u.full_name, u.avatar
            FROM article a
            JOIN category c ON a.category_id = c.category_id
            JOIN user u ON a.user_id = u.user_id
            WHERE a.category_id=?
            ORDER BY created_at DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $categoryId);
        $stmt->execute();

        return $stmt->get_result();
    }

    public function getTopViewed($limit = 5)
    {
        $limit = (int)$limit;
        if ($limit < 1) {
            $limit = 5;
        }

        $sql = "SELECT a.*, c.category_name, u.username, u.full_name, u.avatar
            FROM article a
            JOIN category c ON a.category_id = c.category_id
            JOIN user u ON a.user_id = u.user_id
            ORDER BY view_count DESC, created_at DESC
            LIMIT " . $limit;

        return $this->conn->query($sql);
    }
    public function getArticleById($id)
    {
        $sql = "SELECT a.*, c.category_name, u.username, u.full_name, u.avatar
            FROM article a
            JOIN category c ON a.category_id = c.category_id
            JOIN user u ON a.user_id = u.user_id
            WHERE article_id=?";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();

        return $stmt->get_result()->fetch_assoc();
    }

    public function getRelatedArticles($categoryId, $articleId, $limit = 4)
    {
        $limit = (int)$limit;
        if ($limit < 1) {
            $limit = 4;
        }

        $sql = "SELECT a.*, c.category_name, u.username, u.full_name, u.avatar
            FROM article a
            JOIN category c ON a.category_id = c.category_id
            JOIN user u ON a.user_id = u.user_id
            WHERE a.category_id = ? AND a.article_id <> ?
            ORDER BY a.created_at DESC
            LIMIT " . $limit;

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $categoryId, $articleId);
        $stmt->execute();

        return $stmt->get_result();
    }
}

<?php
class Comment
{
    private $conn;
    private $table = "comment";
    private $supportsRepliesCache = null;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function getCommentsByArticle($articleId)
    {
        if ($this->supportsReplies()) {
            $sql = "SELECT c.*, u.username, u.full_name, u.avatar
                    FROM comment c
                    JOIN user u ON c.user_id = u.user_id
                    WHERE c.article_id=? AND c.status='visible'
                    ORDER BY c.created_at ASC, c.comment_id ASC";
        } else {
            $sql = "SELECT c.*, NULL AS parent_comment_id, u.username, u.full_name, u.avatar
                    FROM comment c
                    JOIN user u ON c.user_id = u.user_id
                    WHERE c.article_id=? AND c.status='visible'
                    ORDER BY c.created_at ASC, c.comment_id ASC";
        }

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $articleId);
        $stmt->execute();

        return $stmt->get_result();
    }

    public function createComment($articleId, $userId, $content, $parentCommentId = 0)
    {
        $content = trim($content);
        if ($content === "") {
            return false;
        }

        if ($parentCommentId > 0) {
            if (!$this->supportsReplies() || !$this->parentBelongsToArticle($parentCommentId, $articleId)) {
                return false;
            }

            $sql = "INSERT INTO comment (content, user_id, article_id, parent_comment_id)
                    VALUES (?, ?, ?, ?)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("siii", $content, $userId, $articleId, $parentCommentId);

            return $stmt->execute();
        }

        $sql = "INSERT INTO comment (content, user_id, article_id)
                VALUES (?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sii", $content, $userId, $articleId);

        return $stmt->execute();
    }

    public function supportsReplies()
    {
        if ($this->supportsRepliesCache !== null) {
            return $this->supportsRepliesCache;
        }

        $result = $this->conn->query("SHOW COLUMNS FROM comment LIKE 'parent_comment_id'");
        $this->supportsRepliesCache = $result && $result->num_rows > 0;

        return $this->supportsRepliesCache;
    }

    public function getFallbackUserId()
    {
        $sql = "SELECT user_id
                FROM user
                WHERE status = 'active'
                ORDER BY user_id ASC
                LIMIT 1";

        $result = $this->conn->query($sql);
        if (!$result || $result->num_rows === 0) {
            return 0;
        }

        $user = $result->fetch_assoc();

        return (int)$user["user_id"];
    }

    private function parentBelongsToArticle($parentCommentId, $articleId)
    {
        $sql = "SELECT comment_id
                FROM comment
                WHERE comment_id = ? AND article_id = ? AND status = 'visible'
                LIMIT 1";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $parentCommentId, $articleId);
        $stmt->execute();

        $result = $stmt->get_result();

        return $result->num_rows > 0;
    }
}
?>
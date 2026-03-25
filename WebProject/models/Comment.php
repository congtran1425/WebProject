<?php
class Comment {

    private $conn;
    private $table = "comment";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getCommentsByArticle($articleId) {

        $sql = "SELECT c.*, u.username, u.full_name, u.avatar
                FROM comment c
                JOIN user u ON c.user_id = u.user_id
                WHERE c.article_id=? AND c.status='visible'
                ORDER BY c.created_at DESC";

        $stmt = $this->conn->prepare($sql);

        $stmt->bind_param("i",$articleId);
        $stmt->execute();

        return $stmt->get_result();
    }
}
?>

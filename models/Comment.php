<?php
class Comment {

    private $conn;
    private $table = "comment";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getCommentsByArticle($articleId) {

        $sql = "SELECT * FROM comment
                WHERE article_id=? AND status='visible'";

        $stmt = $this->conn->prepare($sql);

        $stmt->bind_param("i",$articleId);
        $stmt->execute();

        return $stmt->get_result();
    }
}
?>
<?php
class Article {

    private $conn;
    private $table = "article";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function publish($articleId) {

        $sql = "UPDATE article SET status='published'
                WHERE article_id=?";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i",$articleId);

        return $stmt->execute();
    }

    public function updateContent($articleId,$content) {

        $sql = "UPDATE article SET content=? WHERE article_id=?";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("si",$content,$articleId);

        return $stmt->execute();
    }

    public function increaseView($articleId) {

        $sql = "UPDATE article SET view_count=view_count+1
                WHERE article_id=?";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i",$articleId);

        return $stmt->execute();
    }

    public function getAllArticles() {

        $sql = "SELECT * FROM article WHERE status='published'";
        return $this->conn->query($sql);
    }
}
?>
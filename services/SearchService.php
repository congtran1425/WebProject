<?php
class SearchService {

    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function searchByKeyword($keyword) {

        $sql = "SELECT * FROM article
                WHERE title LIKE ? OR content LIKE ?";

        $keyword = "%".$keyword."%";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ss",$keyword,$keyword);

        $stmt->execute();

        return $stmt->get_result();
    }

    public function searchByCategory($categoryId) {

        $sql = "SELECT * FROM article WHERE category_id=?";
        $stmt = $this->conn->prepare($sql);

        $stmt->bind_param("i",$categoryId);
        $stmt->execute();

        return $stmt->get_result();
    }
}
?>
<?php
class Category {

    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getAllCategories() {

        $sql = "SELECT * FROM category";
        return $this->conn->query($sql);
    }

    public function getCategoryById($categoryId) {
        $sql = "SELECT * FROM category WHERE category_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $categoryId);
        $stmt->execute();

        return $stmt->get_result()->fetch_assoc();
    }
}
?>

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
}
?>
<?php
class Database {

    private $host = "localhost";
    private $db_name = "webproject";
    private $username = "root";
    private $password = "";

    public $conn;

    public function connect() {

        $this->conn = new mysqli(
            $this->host,
            $this->username,
            $this->password,
            $this->db_name
        );

        if ($this->conn->connect_error) {
            die("Connection failed: " . $this->conn->connect_error);
        }

        $this->conn->set_charset("utf8mb4");

        return $this->conn;
    }
}
?>
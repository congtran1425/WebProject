<?php
require_once __DIR__ . "/env.php";

class Database {

    private $host;
    private $db_name;
    private $username;
    private $password;
    private $port;

    public $conn;

    public function __construct()
    {
        $parsedUrl = $this->parseDatabaseUrl(env_value(["DATABASE_URL", "MYSQL_URL"]));

        $this->host = $parsedUrl["host"] ?? env_value(["DB_HOST", "MYSQLHOST"], "localhost");
        $this->db_name = $parsedUrl["database"] ?? env_value(["DB_NAME", "MYSQLDATABASE"], "webproject");
        $this->username = $parsedUrl["username"] ?? env_value(["DB_USER", "MYSQLUSER"], "root");
        $this->password = $parsedUrl["password"] ?? env_value(["DB_PASS", "MYSQLPASSWORD"], "");
        $this->port = (int)($parsedUrl["port"] ?? env_value(["DB_PORT", "MYSQLPORT"], 3306));
    }

    public function connect() {

        $this->conn = new mysqli(
            $this->host,
            $this->username,
            $this->password,
            $this->db_name,
            $this->port
        );

        if ($this->conn->connect_error) {
            die("Connection failed: " . $this->conn->connect_error);
        }

        $this->conn->set_charset("utf8mb4");

        return $this->conn;
    }

    private function parseDatabaseUrl($url)
    {
        if (!is_string($url) || trim($url) === "") {
            return null;
        }

        $parts = parse_url($url);
        if ($parts === false) {
            return null;
        }

        return [
            "host" => $parts["host"] ?? null,
            "port" => $parts["port"] ?? null,
            "username" => $parts["user"] ?? null,
            "password" => $parts["pass"] ?? null,
            "database" => isset($parts["path"]) ? ltrim($parts["path"], "/") : null,
        ];
    }
}
?>

<?php
class Database {

    private $host;
    private $db_name;
    private $username;
    private $password;
    private $port;
    private $sslCa;
    private $sslCert;
    private $sslKey;
    private $sslCaContent;

    public $conn;

    public function __construct()
    {
        $this->host = getenv("DB_HOST") ?: "localhost";
        $this->db_name = getenv("DB_NAME") ?: "webproject";
        $this->username = getenv("DB_USER") ?: "root";
        $this->password = getenv("DB_PASS") ?: "";
        $this->port = (int)(getenv("DB_PORT") ?: 3306);
        $this->sslCa = getenv("DB_SSL_CA") ?: "";
        $this->sslCaContent = getenv("DB_SSL_CA_CONTENT") ?: "";
        if ($this->sslCaContent !== "" && strpos($this->sslCaContent, "\\n") !== false) {
            $this->sslCaContent = str_replace("\\n", "\n", $this->sslCaContent);
        }
        $this->sslCaContent = trim($this->sslCaContent);
        $this->sslCert = getenv("DB_SSL_CERT") ?: "";
        $this->sslKey = getenv("DB_SSL_KEY") ?: "";
    }

    public function connect() {
        $useSsl = $this->sslCa !== "" || $this->sslCaContent !== "" || getenv("DB_SSL") === "1";

        $caPath = $this->sslCa;
        if ($caPath === "" && $this->sslCaContent !== "") {
            $tempPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . "db_ca_" . md5($this->sslCaContent) . ".pem";
            if (!file_exists($tempPath)) {
                file_put_contents($tempPath, $this->sslCaContent);
            }
            $caPath = $tempPath;
        }

        if ($useSsl) {
            $this->conn = mysqli_init();
            if ($this->conn === false) {
                die("Failed to initialize MySQL connection.");
            }

            $this->conn->ssl_set(
                $this->sslKey !== "" ? $this->sslKey : null,
                $this->sslCert !== "" ? $this->sslCert : null,
                $caPath !== "" ? $caPath : null,
                null,
                null
            );

            $connected = $this->conn->real_connect(
                $this->host,
                $this->username,
                $this->password,
                $this->db_name,
                $this->port
            );

            if (!$connected) {
                die("Connection failed: " . $this->conn->connect_error);
            }
        } else {
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
        }

        $this->conn->set_charset("utf8mb4");

        return $this->conn;
    }
}
?>

<?php
class AuthService {

    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function register($username,$email,$password) {

        $hash = password_hash($password,PASSWORD_BCRYPT);

        $sql = "INSERT INTO users(username,email,password)
                VALUES(?,?,?)";

        $stmt = $this->conn->prepare($sql);

        $stmt->bind_param("sss",$username,$email,$hash);

        return $stmt->execute();
    }

    public function authenticate($email,$password) {

        $sql = "SELECT * FROM users WHERE email=?";
        $stmt = $this->conn->prepare($sql);

        $stmt->bind_param("s",$email);
        $stmt->execute();

        $user = $stmt->get_result()->fetch_assoc();

        if($user && password_verify($password,$user['password'])) {
            return $user;
        }

        return false;
    }

    public function authorize($roleRequired,$userRole) {

        return $roleRequired === $userRole;
    }
}
?>
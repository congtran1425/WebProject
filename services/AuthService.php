<?php
class AuthService {

    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function authenticate($email,$password) {

        $sql = "SELECT * FROM user WHERE email=?";
        $stmt = $this->conn->prepare($sql);

        $stmt->bind_param("s",$email);
        $stmt->execute();

        $user = $stmt->get_result()->fetch_assoc();

        if($user && password_verify($password,$user['password_hash'])) {
            return $user;
        }

        return false;
    }

    public function recordLogin($userId) {
        $sql = "UPDATE user SET last_login = NOW() WHERE user_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $userId);

        return $stmt->execute();
    }

    public function register($username,$email,$password,$role = "reader") {

        $hash = password_hash($password,PASSWORD_BCRYPT);

        $sql = "INSERT INTO user(username,email,password_hash,role)
                VALUES(?,?,?,?)";

        $stmt = $this->conn->prepare($sql);

        $stmt->bind_param("ssss",$username,$email,$hash,$role);

        return $stmt->execute();
    }

    public function authorize($roleRequired,$userRole) {

        return $roleRequired === $userRole;
    }
}
?>

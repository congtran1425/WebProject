<?php
require_once "config/Database.php";
abstract class User {

    protected $conn;
    protected $table = "user";

    protected $userId;
    protected $username;
    protected $email;
    protected $passwordHash;
    protected $role;
    protected $status;
    protected $createdAt;
    protected $lastLogin;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function login($email, $password) {

        $sql = "SELECT * FROM user WHERE email = ?";
        $stmt = $this->conn->prepare($sql);

        $stmt->bind_param("s", $email);
        $stmt->execute();

        $result = $stmt->get_result()->fetch_assoc();

        if ($result && password_verify($password, $result['password_hash'])) {
            return $result;
        }

        return false;
    }

    public function logout() {
        session_destroy();
    }

    public function updateProfile($userId, $username, $email) {

        $sql = "UPDATE user SET username=?, email=? WHERE user_id=?";
        $stmt = $this->conn->prepare($sql);

        $stmt->bind_param("ssi", $username, $email, $userId);

        return $stmt->execute();
    }

    public function changePassword($userId, $newPassword) {

        $hash = password_hash($newPassword, PASSWORD_BCRYPT);

        $sql = "UPDATE user SET password_hash=? WHERE user_id=?";
        $stmt = $this->conn->prepare($sql);

        $stmt->bind_param("si", $hash, $userId);

        return $stmt->execute();
    }
}
?>

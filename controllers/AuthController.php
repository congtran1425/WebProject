<?php

require_once __DIR__ . "/../config/Database.php";
require_once __DIR__ . "/../services/AuthService.php";
require_once __DIR__ . "/../includes/auth_cookie.php";

class AuthController
{
    private $auth;

    public function __construct()
    {
        $database = new Database();
        $db = $database->connect();

        $this->auth = new AuthService($db);
    }

    public function handleLogin()
    {
        $this->ensureSession();

        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            return [
                "success" => null,
                "message" => null,
                "email" => "",
            ];
        }

        $email = trim($_POST["email"] ?? "");
        $password = (string)($_POST["password"] ?? "");

        if ($email === "" || $password === "") {
            return $this->handleLoginFailure("Vui lòng nhập email và mật khẩu.", $email);
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->handleLoginFailure("Email không hợp lệ.", $email);
        }

        $user = $this->auth->authenticate($email, $password);
        if (!$user) {
            return $this->handleLoginFailure("Email hoặc mật khẩu không đúng.", $email);
        }

        if (!empty($user["status"]) && $user["status"] !== "active") {
            return $this->handleLoginFailure("Tài khoản hiện không khả dụng.", $email);
        }

        $_SESSION["user_id"] = (int)$user["user_id"];
        $_SESSION["username"] = $user["username"] ?? "";
        $_SESSION["email"] = $user["email"] ?? "";
        $_SESSION["role"] = $user["role"] ?? "reader";
        $_SESSION["full_name"] = $user["full_name"] ?? "";
        $_SESSION["avatar"] = $user["avatar"] ?? "";

        $this->auth->recordLogin((int)$user["user_id"]);
        set_auth_cookie($user);

        $redirect = $_POST["redirect"] ?? $_GET["redirect"] ?? "index.php";
        $safeRedirect = $this->sanitizeRedirect($redirect);

        header("Location: " . $safeRedirect);
        exit;
    }

    public function handleRegister()
    {
        $this->ensureSession();

        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            return [
                "success" => null,
                "message" => null,
                "email" => "",
                "username" => "",
            ];
        }

        $username = trim($_POST["username"] ?? "");
        $email = trim($_POST["email"] ?? "");
        $password = (string)($_POST["password"] ?? "");
        $confirm = (string)($_POST["confirm_password"] ?? "");

        if ($username === "" || $email === "" || $password === "") {
            return $this->handleRegisterFailure("Vui lòng nhập đầy đủ thông tin.", $email, $username);
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->handleRegisterFailure("Email không hợp lệ.", $email, $username);
        }

        if (strlen($password) < 6) {
            return $this->handleRegisterFailure("Mật khẩu cần tối thiểu 6 ký tự.", $email, $username);
        }

        if ($confirm !== "" && $password !== $confirm) {
            return $this->handleRegisterFailure("Mật khẩu xác nhận không khớp.", $email, $username);
        }

        $saved = $this->auth->register($username, $email, $password, "reader");
        if (!$saved) {
            return $this->handleRegisterFailure("Không thể tạo tài khoản. Email hoặc tên đăng nhập đã tồn tại.", $email, $username);
        }

        $user = $this->auth->authenticate($email, $password);
        if ($user) {
            $_SESSION["user_id"] = (int)$user["user_id"];
            $_SESSION["username"] = $user["username"] ?? "";
            $_SESSION["email"] = $user["email"] ?? "";
            $_SESSION["role"] = $user["role"] ?? "reader";
            $_SESSION["full_name"] = $user["full_name"] ?? "";
            $_SESSION["avatar"] = $user["avatar"] ?? "";
            $this->auth->recordLogin((int)$user["user_id"]);
            set_auth_cookie($user);
        }

        $redirect = $_POST["redirect"] ?? $_GET["redirect"] ?? "index.php";
        $safeRedirect = $this->sanitizeRedirect($redirect);

        header("Location: " . $safeRedirect);
        exit;
    }

    private function ensureSession()
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
    }

    private function sanitizeRedirect($redirect)
    {
        $redirect = trim((string)$redirect);
        if ($redirect === "") {
            return "index.php";
        }

        if (preg_match("/^[a-z][a-z0-9+.-]*:/i", $redirect) || str_starts_with($redirect, "//")) {
            return "index.php";
        }

        return ltrim($redirect, "/");
    }

    private function handleLoginFailure($message, $email)
    {
        $redirect = $_POST["redirect"] ?? $_GET["redirect"] ?? "";
        $isModal = !empty($_POST["modal"]) || !empty($_GET["modal"]);

        if ($isModal && $redirect !== "") {
            $safeRedirect = $this->sanitizeRedirect($redirect);
            $_SESSION["login_feedback"] = [
                "success" => false,
                "message" => $message,
                "email" => $email,
            ];

            $returnUrl = $this->appendQueryFlag($safeRedirect, "login", "1");
            header("Location: " . $returnUrl);
            exit;
        }

        return [
            "success" => false,
            "message" => $message,
            "email" => $email,
        ];
    }

    private function appendQueryFlag($url, $key, $value)
    {
        $parts = parse_url($url);
        $path = $parts["path"] ?? $url;
        $query = [];
        if (!empty($parts["query"])) {
            parse_str($parts["query"], $query);
        }
        $query[$key] = $value;
        $queryString = http_build_query($query);
        $fragment = isset($parts["fragment"]) ? ("#" . $parts["fragment"]) : "";

        return $path . ($queryString ? ("?" . $queryString) : "") . $fragment;
    }

    private function handleRegisterFailure($message, $email, $username)
    {
        $redirect = $_POST["redirect"] ?? $_GET["redirect"] ?? "";
        $isModal = !empty($_POST["modal"]) || !empty($_GET["modal"]);

        if ($isModal && $redirect !== "") {
            $safeRedirect = $this->sanitizeRedirect($redirect);
            $_SESSION["register_feedback"] = [
                "success" => false,
                "message" => $message,
                "email" => $email,
                "username" => $username,
            ];

            $returnUrl = $this->appendQueryFlag($safeRedirect, "register", "1");
            header("Location: " . $returnUrl);
            exit;
        }

        return [
            "success" => false,
            "message" => $message,
            "email" => $email,
            "username" => $username,
        ];
    }

}



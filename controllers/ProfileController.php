<?php

require_once __DIR__ . "/../config/Database.php";
require_once __DIR__ . "/../config/env.php";
require_once __DIR__ . "/../models/Profile.php";

class ProfileController
{
    private $profile;

    public function __construct()
    {
        $database = new Database();
        $db = $database->connect();

        $this->profile = new Profile($db);
    }

    public function handleRequest($requestedUserId = 0)
    {
        $this->ensureSession();

        if ($_SERVER["REQUEST_METHOD"] !== "POST" || ($_POST["action"] ?? "") !== "update_profile") {
            return;
        }

        $userId = $this->resolveProfileUserId($requestedUserId);
        if ($userId <= 0) {
            $this->setFlash(false, "Không tìm thấy người dùng để cập nhật.");
            $this->redirectToProfile($requestedUserId);
        }

        if (!$this->canEdit($userId, $requestedUserId)) {
            $this->setFlash(false, "Bạn không có quyền chỉnh sửa hồ sơ này.");
            $this->redirectToProfile($requestedUserId);
        }

        $data = [
            "username" => trim($_POST["username"] ?? ""),
            "email" => trim($_POST["email"] ?? ""),
            "full_name" => trim($_POST["full_name"] ?? ""),
            "bio" => trim($_POST["bio"] ?? ""),
            "phone" => trim($_POST["phone"] ?? ""),
            "address" => trim($_POST["address"] ?? ""),
            "gender" => trim($_POST["gender"] ?? ""),
            "birth_date" => trim($_POST["birth_date"] ?? ""),
        ];

        $validationMessage = $this->validateProfileData($data);
        if ($validationMessage !== null) {
            $this->setFlash(false, $validationMessage);
            $this->redirectToProfile($requestedUserId);
        }

        $existingProfile = $this->profile->getProfileById($userId);
        if (!$existingProfile) {
            $this->setFlash(false, "Không tìm thấy hồ sơ người dùng.");
            $this->redirectToProfile($requestedUserId);
        }

        $data["avatar"] = $existingProfile["avatar"] ?? null;

        if (!empty($_POST["remove_avatar"])) {
            $this->deleteAvatarIfManaged($existingProfile["avatar"] ?? null);
            $data["avatar"] = null;
        }

        $uploadResult = $this->handleAvatarUpload($_FILES["avatar"] ?? null, $existingProfile["avatar"] ?? null);
        if (!$uploadResult["success"]) {
            $this->setFlash(false, $uploadResult["message"]);
            $this->redirectToProfile($requestedUserId);
        }

        if (array_key_exists("path", $uploadResult)) {
            $data["avatar"] = $uploadResult["path"];
        }

        $saved = $this->profile->updateProfile($userId, $data);
        if ($saved) {
            $_SESSION["avatar"] = $data["avatar"] ?? null;
            $_SESSION["full_name"] = $data["full_name"] ?? ($_SESSION["full_name"] ?? null);
            $_SESSION["username"] = $data["username"] ?? ($_SESSION["username"] ?? null);
        }
        $this->setFlash($saved, $saved ? "Đã cập nhật trang cá nhân." : "Không thể cập nhật trang cá nhân.");

        $this->redirectToProfile($requestedUserId);
    }

    public function getPageData($requestedUserId = 0)
    {
        $this->ensureSession();

        $userId = $this->resolveProfileUserId($requestedUserId);
        if ($userId <= 0) {
            return null;
        }

        $profile = $this->profile->getProfileById($userId);
        if (!$profile) {
            return null;
        }

        return [
            "profile" => $profile,
            "stats" => $this->profile->getProfileStats($userId),
            "articles" => $this->profile->getRecentArticlesByUser($userId, 6),
            "comments" => $this->profile->getRecentCommentsByUser($userId, 6),
            "can_edit" => $this->canEdit($userId, $requestedUserId),
            "uses_fallback_user" => $requestedUserId <= 0 && empty($_SESSION["user_id"]),
            "flash" => $this->consumeFlash(),
        ];
    }

    private function ensureSession()
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
    }

    private function resolveProfileUserId($requestedUserId = 0)
    {
        $requestedUserId = (int)$requestedUserId;
        if ($requestedUserId > 0) {
            return $requestedUserId;
        }

        if (!empty($_SESSION["user_id"])) {
            return (int)$_SESSION["user_id"];
        }

        return $this->profile->resolveDefaultUserId();
    }

    private function canEdit($userId, $requestedUserId = 0)
    {
        if ($requestedUserId > 0) {
            return !empty($_SESSION["user_id"]) && (int)$_SESSION["user_id"] === (int)$userId;
        }

        if (!empty($_SESSION["user_id"])) {
            return (int)$_SESSION["user_id"] === (int)$userId;
        }

        return true;
    }

    private function validateProfileData(array $data)
    {
        if ($data["username"] === "") {
            return "Vui lòng nhập tên đăng nhập.";
        }

        if ($data["email"] === "" || !filter_var($data["email"], FILTER_VALIDATE_EMAIL)) {
            return "Vui lòng nhập email hợp lệ.";
        }

        if ($data["gender"] !== "" && !in_array($data["gender"], ["male", "female", "other"], true)) {
            return "Giới tính không hợp lệ.";
        }

        if ($data["birth_date"] !== "" && !$this->isValidDate($data["birth_date"])) {
            return "Ngày sinh không hợp lệ.";
        }

        return null;
    }

    private function isValidDate($date)
    {
        $parsed = date_create_from_format("Y-m-d", $date);

        return $parsed !== false && $parsed->format("Y-m-d") === $date;
    }

    private function handleAvatarUpload($file, $existingAvatar = null)
    {
        if (!$file || !isset($file["error"]) || $file["error"] === UPLOAD_ERR_NO_FILE) {
            return ["success" => true];
        }

        if (!$this->uploadsEnabled()) {
            return [
                "success" => false,
                "message" => "Bản deploy hiện tại đang tạm tắt tải ảnh đại diện.",
            ];
        }

        if ($file["error"] !== UPLOAD_ERR_OK) {
            return [
                "success" => false,
                "message" => "Không thể tải ảnh đại diện lên. Vui lòng thử lại.",
            ];
        }

        if (($file["size"] ?? 0) > 2 * 1024 * 1024) {
            return [
                "success" => false,
                "message" => "Ảnh đại diện vượt quá 2MB.",
            ];
        }

        $mime = $this->detectMimeType($file);
        $allowed = [
            "image/jpeg" => "jpg",
            "image/png" => "png",
            "image/webp" => "webp",
        ];

        if (!isset($allowed[$mime])) {
            return [
                "success" => false,
                "message" => "Ảnh đại diện chỉ hỗ trợ JPG, PNG hoặc WEBP.",
            ];
        }

        $uploadDir = __DIR__ . "/../assets/avatars";
        if (!is_dir($uploadDir) && !mkdir($uploadDir, 0777, true) && !is_dir($uploadDir)) {
            return [
                "success" => false,
                "message" => "Không thể tạo thư mục lưu ảnh đại diện.",
            ];
        }

        $basename = uniqid("avatar_", true);
        $relativePath = "assets/avatars/" . $basename . "." . $allowed[$mime];
        $fullPath = __DIR__ . "/../" . $relativePath;

        if (!move_uploaded_file($file["tmp_name"], $fullPath)) {
            return [
                "success" => false,
                "message" => "Không thể lưu ảnh đại diện.",
            ];
        }

        $this->deleteAvatarIfManaged($existingAvatar);

        return [
            "success" => true,
            "path" => $relativePath,
        ];
    }

    private function detectMimeType($file)
    {
        if (function_exists("finfo_open")) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            if ($finfo) {
                $mime = finfo_file($finfo, $file["tmp_name"]);
                finfo_close($finfo);
                if (is_string($mime) && $mime !== "") {
                    return $mime;
                }
            }
        }

        return $file["type"] ?? "";
    }

    private function deleteAvatarIfManaged($avatarPath)
    {
        if (empty($avatarPath)) {
            return;
        }

        $normalizedPath = str_replace("\\", "/", $avatarPath);
        $prefix = "assets/avatars/";
        if (strncmp($normalizedPath, $prefix, strlen($prefix)) !== 0) {
            return;
        }

        $fullPath = realpath(__DIR__ . "/../assets/avatars");
        if ($fullPath === false) {
            return;
        }

        $filename = basename($normalizedPath);
        if ($filename === "" || $filename === "." || $filename === "..") {
            return;
        }

        $target = $fullPath . DIRECTORY_SEPARATOR . $filename;
        if (is_file($target)) {
            @unlink($target);
        }
    }

    private function setFlash($success, $message)
    {
        $_SESSION["profile_feedback"] = [
            "success" => (bool)$success,
            "message" => $message,
        ];
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }
    }

    private function consumeFlash()
    {
        $flash = $_SESSION["profile_feedback"] ?? null;
        unset($_SESSION["profile_feedback"]);

        return $flash;
    }

    private function redirectToProfile($requestedUserId = 0)
    {
        $location = "profile.php";
        if ((int)$requestedUserId > 0) {
            $location .= "?id=" . (int)$requestedUserId;
        }

        header("Location: " . $location);
        exit;
    }

    private function uploadsEnabled()
    {
        return env_flag(["APP_ENABLE_UPLOADS"], false);
    }
}

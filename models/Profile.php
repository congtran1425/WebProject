<?php

class Profile
{
    private $conn;
    private $userColumns = null;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function resolveDefaultUserId()
    {
        $sql = "SELECT user_id FROM user";
        if ($this->hasUserColumn("status")) {
            $sql .= " WHERE status = 'active'";
        }
        $sql .= " ORDER BY user_id ASC LIMIT 1";

        $result = $this->conn->query($sql);
        if (!$result || $result->num_rows === 0) {
            return 0;
        }

        $row = $result->fetch_assoc();

        return (int)($row["user_id"] ?? 0);
    }

    public function getProfileById($userId)
    {
        $selectColumns = ["user_id", "username", "email"];
        $optionalColumns = [
            "role",
            "status",
            "full_name",
            "avatar",
            "bio",
            "phone",
            "address",
            "gender",
            "birth_date",
            "created_at",
            "last_login",
        ];

        foreach ($optionalColumns as $column) {
            if ($this->hasUserColumn($column)) {
                $selectColumns[] = $column;
            }
        }

        $sql = "SELECT " . implode(", ", $selectColumns) . " FROM user WHERE user_id = ? LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();

        return $stmt->get_result()->fetch_assoc();
    }

    public function updateProfile($userId, array $data)
    {
        $fieldMap = [
            "username" => $this->normalizeString($data["username"] ?? ""),
            "email" => $this->normalizeString($data["email"] ?? ""),
            "full_name" => $this->normalizeNullableString($data["full_name"] ?? null),
            "avatar" => $this->normalizeNullableString($data["avatar"] ?? null),
            "bio" => $this->normalizeNullableString($data["bio"] ?? null),
            "phone" => $this->normalizeNullableString($data["phone"] ?? null),
            "address" => $this->normalizeNullableString($data["address"] ?? null),
            "gender" => $this->normalizeNullableString($data["gender"] ?? null),
            "birth_date" => $this->normalizeNullableString($data["birth_date"] ?? null),
        ];

        $setParts = [];
        $types = "";
        $values = [];

        foreach ($fieldMap as $column => $value) {
            if (!$this->hasUserColumn($column)) {
                continue;
            }

            $setParts[] = $column . " = ?";
            $types .= "s";
            $values[] = $value;
        }

        if (empty($setParts)) {
            return false;
        }

        $sql = "UPDATE user SET " . implode(", ", $setParts) . " WHERE user_id = ?";
        $stmt = $this->conn->prepare($sql);
        $types .= "i";
        $values[] = $userId;
        $stmt->bind_param($types, ...$values);

        return $stmt->execute();
    }

    public function getProfileStats($userId)
    {
        $articleCount = $this->runCountQuery("SELECT COUNT(*) AS total FROM article WHERE user_id = ?", $userId);
        $commentCount = $this->runCountQuery("SELECT COUNT(*) AS total FROM comment WHERE user_id = ?", $userId);

        $viewSql = "SELECT COALESCE(SUM(view_count), 0) AS total FROM article WHERE user_id = ?";
        $stmt = $this->conn->prepare($viewSql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();

        return [
            "article_count" => $articleCount,
            "comment_count" => $commentCount,
            "total_views" => (int)($row["total"] ?? 0),
        ];
    }

    public function getRecentArticlesByUser($userId, $limit = 5)
    {
        $limit = max(1, (int)$limit);

        $sql = "SELECT a.article_id, a.title, a.summary, a.thumbnail, a.status, a.view_count, a.created_at, c.category_name
                FROM article a
                LEFT JOIN category c ON a.category_id = c.category_id
                WHERE a.user_id = ?
                ORDER BY a.created_at DESC
                LIMIT " . $limit;

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();

        return $stmt->get_result();
    }

    public function getRecentCommentsByUser($userId, $limit = 5)
    {
        $limit = max(1, (int)$limit);

        $sql = "SELECT c.comment_id, c.content, c.status, c.created_at, a.article_id, a.title AS article_title
                FROM comment c
                JOIN article a ON c.article_id = a.article_id
                WHERE c.user_id = ?
                ORDER BY c.created_at DESC
                LIMIT " . $limit;

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();

        return $stmt->get_result();
    }

    private function runCountQuery($sql, $userId)
    {
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            return 0;
        }

        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();

        return (int)($row["total"] ?? 0);
    }

    private function normalizeString($value)
    {
        return trim((string)$value);
    }

    private function normalizeNullableString($value)
    {
        $value = trim((string)($value ?? ""));

        return $value === "" ? null : $value;
    }

    private function hasUserColumn($column)
    {
        $columns = $this->getUserColumns();

        return isset($columns[$column]);
    }

    private function getUserColumns()
    {
        if ($this->userColumns !== null) {
            return $this->userColumns;
        }

        $this->userColumns = [];
        $result = $this->conn->query("SHOW COLUMNS FROM user");
        if (!$result) {
            return $this->userColumns;
        }

        while ($row = $result->fetch_assoc()) {
            if (!empty($row["Field"])) {
                $this->userColumns[$row["Field"]] = true;
            }
        }

        return $this->userColumns;
    }
}

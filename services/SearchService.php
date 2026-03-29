<?php
class SearchService {

    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    private function bindParams($stmt, $types, array $params) {
        if ($types === "" || empty($params)) {
            return;
        }

        $bind = [$types];
        foreach ($params as $key => $value) {
            $bind[] = &$params[$key];
        }

        call_user_func_array([$stmt, "bind_param"], $bind);
    }

    public function searchByKeyword($keyword, $categoryId = 0, $dateFrom = null, $dateTo = null, $sort = "relevance", $limit = 12, $offset = 0) {
        $conditions = ["a.status = 'published'"];
        $params = [];
        $types = "";
        $dateColumn = "(CASE WHEN a.created_at LIKE '%/%/%' THEN STR_TO_DATE(a.created_at, '%Y/%m/%d %H:%i:%s') ELSE a.created_at END)";

        $keyword = trim((string)$keyword);
        $keywordLike = null;

        if ($keyword !== "") {
            $keywordLike = "%" . $keyword . "%";
            $conditions[] = "(a.title LIKE ? OR a.summary LIKE ? OR a.content LIKE ?)";
            $types .= "sss";
            $params[] = $keywordLike;
            $params[] = $keywordLike;
            $params[] = $keywordLike;
        }

        $categoryId = (int)$categoryId;
        if ($categoryId > 0) {
            $conditions[] = "a.category_id = ?";
            $types .= "i";
            $params[] = $categoryId;
        }

        if (!empty($dateFrom)) {
            $conditions[] = $dateColumn . " >= ?";
            $types .= "s";
            $params[] = $dateFrom;
        }

        if (!empty($dateTo)) {
            $conditions[] = $dateColumn . " <= ?";
            $types .= "s";
            $params[] = $dateTo;
        }

        $where = "WHERE " . implode(" AND ", $conditions);
        $orderBy = " ORDER BY " . $dateColumn . " DESC";

        if ($sort === "oldest") {
            $orderBy = " ORDER BY " . $dateColumn . " ASC";
        } elseif ($sort === "relevance" && $keywordLike !== null) {
            $orderBy = " ORDER BY (CASE
                WHEN a.title LIKE ? THEN 3
                WHEN a.summary LIKE ? THEN 2
                WHEN a.content LIKE ? THEN 1
                ELSE 0 END) DESC, " . $dateColumn . " DESC";
            $types .= "sss";
            $params[] = $keywordLike;
            $params[] = $keywordLike;
            $params[] = $keywordLike;
        }

        $limit = max(1, (int)$limit);
        $offset = max(0, (int)$offset);

        $sql = "SELECT a.*, c.category_name
                FROM article a
                JOIN category c ON a.category_id = c.category_id
                " . $where . $orderBy . " LIMIT ? OFFSET ?";

        $types .= "ii";
        $params[] = $limit;
        $params[] = $offset;

        $stmt = $this->conn->prepare($sql);
        $this->bindParams($stmt, $types, $params);
        $stmt->execute();

        return $stmt->get_result();
    }

    public function countSearch($keyword, $categoryId = 0, $dateFrom = null, $dateTo = null) {
        $conditions = ["a.status = 'published'"];
        $params = [];
        $types = "";
        $dateColumn = "(CASE WHEN a.created_at LIKE '%/%/%' THEN STR_TO_DATE(a.created_at, '%Y/%m/%d %H:%i:%s') ELSE a.created_at END)";

        $keyword = trim((string)$keyword);
        if ($keyword !== "") {
            $keywordLike = "%" . $keyword . "%";
            $conditions[] = "(a.title LIKE ? OR a.summary LIKE ? OR a.content LIKE ?)";
            $types .= "sss";
            $params[] = $keywordLike;
            $params[] = $keywordLike;
            $params[] = $keywordLike;
        }

        $categoryId = (int)$categoryId;
        if ($categoryId > 0) {
            $conditions[] = "a.category_id = ?";
            $types .= "i";
            $params[] = $categoryId;
        }

        if (!empty($dateFrom)) {
            $conditions[] = $dateColumn . " >= ?";
            $types .= "s";
            $params[] = $dateFrom;
        }

        if (!empty($dateTo)) {
            $conditions[] = $dateColumn . " <= ?";
            $types .= "s";
            $params[] = $dateTo;
        }

        $where = "WHERE " . implode(" AND ", $conditions);
        $sql = "SELECT COUNT(*) AS total
                FROM article a
                " . $where;

        $stmt = $this->conn->prepare($sql);
        $this->bindParams($stmt, $types, $params);
        $stmt->execute();

        $result = $stmt->get_result()->fetch_assoc();
        return (int)($result["total"] ?? 0);
    }
}
?>

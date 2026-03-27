<?php
require_once __DIR__ . "/../config/Database.php";
require_once __DIR__ . "/../models/Category.php";
require_once __DIR__ . "/weather.php";

$db = new Database();
$conn = $db->connect();
$category_model = new Category($conn);
$category_result = $category_model->getAllCategories();
$categories = [];
if ($category_result) {
    while ($row = $category_result->fetch_assoc()) {
        $categories[] = $row;
    }
}

$day_map = [
    "Monday" => "Thứ hai",
    "Tuesday" => "Thứ ba",
    "Wednesday" => "Thứ tư",
    "Thursday" => "Thứ năm",
    "Friday" => "Thứ sáu",
    "Saturday" => "Thứ bảy",
    "Sunday" => "Chủ nhật",
];
$day_en = date("l");
$day_vi = $day_map[$day_en] ?? $day_en;
$today_text = $day_vi . ", " . date("d/m/Y");

$weather = get_current_weather(10.8231, 106.6297);
$weather_temp = null;
$weather_icon = "bi-cloud";
if (is_array($weather) && isset($weather["temperature"])) {
    $weather_temp = round((float)$weather["temperature"]);
    if (isset($weather["weathercode"])) {
        $weather_icon = map_weather_icon((int)$weather["weathercode"]);
    }
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>News Website</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="assets/css/site.css" rel="stylesheet">
</head>

<body>
    <header class="site-header">
        <div class="topbar border-bottom">
            <div class="container d-flex flex-wrap align-items-center justify-content-between gap-3 py-2">
                <div class="d-flex align-items-center gap-3">
                    <a class="brand" href="index.php" aria-label="Trang chủ">
                        <span class="brand-vn">VN</span><span class="brand-e">N</span><span class="brand-rest">EWS</span>
                    </a>
                    <span class="brand-sub">Báo tiếng Việt nhiều người xem nhất</span>
                </div>
                <div class="d-none d-md-flex align-items-center gap-3 text-muted small">
                    <span class="divider">
                        TP HCM
                        <i class="bi <?php echo $weather_icon; ?> ms-1"></i>
                        <?php echo $weather_temp !== null ? ($weather_temp . "°") : "--°"; ?>
                    </span>
                    <span class="divider"><?php echo htmlspecialchars($today_text, ENT_QUOTES, "UTF-8"); ?></span>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <!-- <a class="top-link" href="#">Mới nhất</a>
                    <a class="top-link" href="#">Tin theo khu vực</a>
                    <a class="top-link top-link-brand" href="#"><span class="brand-e small">N</span> International</a> -->
                    <button class="icon-btn" type="button" aria-label="Tìm kiếm"><i class="bi bi-search"></i></button>
                    <button class="icon-btn" type="button" aria-label="Tài khoản"><i class="bi bi-person-circle"></i></button>
                    <a class="btn btn-sm btn-danger" href="create_article.php">Tạo bài viết</a>
                </div>
            </div>
        </div>
        <div class="category-bar border-bottom">
            <div class="container">
                <nav class="category-nav" aria-label="Danh mục">
                    <a class="category-home" href="index.php" aria-label="Trang chủ"><i class="bi bi-house-door"></i></a>
                    <?php foreach ($categories as $category) { ?>
                        <a href="index.php?category_id=<?php echo (int)$category["category_id"]; ?>">
                            <?php echo htmlspecialchars($category["category_name"], ENT_QUOTES, "UTF-8"); ?>
                        </a>
                    <?php } ?>
                </nav>
            </div>
        </div>
    </header>

    <main class="container mt-4">

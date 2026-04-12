<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$basePath = $basePath ?? "";
$basePath = $basePath === "" ? "" : rtrim($basePath, "/") . "/";
if (!isset($toastMessages) || !is_array($toastMessages)) {
    $toastMessages = [];
}

if (!function_exists("enqueue_toast")) {
    function enqueue_toast(&$toastMessages, $message, $type = "info")
    {
        $message = trim((string)$message);
        if ($message === "") {
            return;
        }

        $allowedTypes = ["success", "error", "warning", "info"];
        if (!in_array($type, $allowedTypes, true)) {
            $type = "info";
        }

        $toastMessages[] = [
            "message" => $message,
            "type" => $type,
        ];
    }
}

function resolve_asset_path($path, $basePath) {
    $path = trim((string)$path);
    if ($path === "") {
        return "";
    }
    if (preg_match("#^(https?:|data:)#i", $path)) {
        return $path;
    }
    if (str_starts_with($path, "/")) {
        return $path;
    }
    return $basePath . $path;
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && ($_POST["auth_action"] ?? "") === "login") {
    require_once __DIR__ . "/../controllers/AuthController.php";
    $authController = new AuthController();
    $authController->handleLogin();
}
if ($_SERVER["REQUEST_METHOD"] === "POST" && ($_POST["auth_action"] ?? "") === "register") {
    require_once __DIR__ . "/../controllers/AuthController.php";
    $authController = new AuthController();
    $authController->handleRegister();
}

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

$loginFeedback = $_SESSION["login_feedback"] ?? null;
unset($_SESSION["login_feedback"]);
$registerFeedback = $_SESSION["register_feedback"] ?? null;
unset($_SESSION["register_feedback"]);
if (!empty($loginFeedback["message"])) {
    enqueue_toast($toastMessages, $loginFeedback["message"], !empty($loginFeedback["success"]) ? "success" : "error");
}
if (!empty($registerFeedback["message"])) {
    enqueue_toast($toastMessages, $registerFeedback["message"], !empty($registerFeedback["success"]) ? "success" : "error");
}
$currentRequest = $_SERVER["REQUEST_URI"] ?? "index.php";
$shouldOpenLoginModal = !empty($loginFeedback) || (isset($_GET["login"]) && $_GET["login"] === "1");
$shouldOpenRegisterModal = !empty($registerFeedback) || (isset($_GET["register"]) && $_GET["register"] === "1");
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
    <link href="<?php echo $basePath; ?>assets/css/site.css" rel="stylesheet">
</head>

<body>
    <header class="site-header">
        <div class="topbar border-bottom">
            <div class="container d-flex flex-wrap align-items-center justify-content-between gap-3 py-1">
                <?php
                $currentRole = $_SESSION["role"] ?? "";
                $isLoggedIn = !empty($_SESSION["user_id"]);
                $canCreateArticle = in_array($currentRole, ["author", "editor", "admin"], true);
                $isAdmin = $currentRole === "admin";
                ?>
                <div class="d-flex align-items-center gap-3">
                    <a class="brand" href="<?php echo $basePath; ?>index.php" aria-label="Trang chủ">
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
                    <div class="search-wrapper" data-search>
                        <button class="icon-btn search-toggle" type="button" aria-label="Tìm kiếm" aria-expanded="false" aria-controls="header-search-form">
                            <i class="bi bi-search"></i>
                        </button>
                        <form class="search-form" id="header-search-form" method="get" action="<?php echo $basePath; ?>search.php" role="search" aria-hidden="true">
                            <input class="search-input" type="search" name="q" placeholder="Nhập từ khóa cần tìm..." autocomplete="off" />
                            <button class="search-submit" type="submit">Tìm</button>
                        </form>
                    </div>
                    <?php if (!$isLoggedIn) { ?>
                        <a class="btn btn-sm btn-outline-dark" href="#loginModal" data-bs-toggle="modal" data-bs-target="#loginModal">Đăng nhập</a>
                    <?php } else { ?>
                        <?php
                        $avatarPath = trim((string)($_SESSION["avatar"] ?? ""));
                        $displayName = trim((string)($_SESSION["full_name"] ?? ""));
                        if ($displayName === "") {
                            $displayName = trim((string)($_SESSION["username"] ?? ""));
                        }
                        $initial = $displayName !== "" ? mb_strtoupper(mb_substr($displayName, 0, 1, "UTF-8"), "UTF-8") : "U";
                        ?>
                        <div class="dropdown">
                            <button class="user-avatar-btn dropdown-toggle" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false" aria-label="Tài khoản">
                                <?php if ($avatarPath !== "") { ?>
                                <img src="<?php echo htmlspecialchars(resolve_asset_path($avatarPath, $basePath), ENT_QUOTES, "UTF-8"); ?>" alt="Avatar">
                                <?php } else { ?>
                                    <span class="avatar-initial"><?php echo htmlspecialchars($initial, ENT_QUOTES, "UTF-8"); ?></span>
                                <?php } ?>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                <li class="dropdown-header"><?php echo htmlspecialchars($displayName !== "" ? $displayName : "Tài khoản", ENT_QUOTES, "UTF-8"); ?></li>
                                <li><a class="dropdown-item" href="<?php echo $basePath; ?>profile.php">Thông tin cá nhân</a></li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-item text-danger" href="<?php echo $basePath; ?>logout.php">Đăng xuất</a></li>
                            </ul>
                        </div>
                        <?php if ($canCreateArticle) { ?>
                            <a class="btn btn-sm btn-danger" href="<?php echo $basePath; ?>create_article.php">Tạo bài viết</a>
                        <?php } ?>
                        <?php if ($isAdmin) { ?>
                            <a class="btn btn-sm btn-outline-dark" href="<?php echo $basePath; ?>admin/index.php">Admin</a>
                        <?php } ?>
                    <?php } ?>
                </div>
            </div>
        </div>
        <div class="category-bar border-bottom">
            <div class="container">
                <nav class="category-nav" aria-label="Danh mục">
                    <a class="category-home" href="<?php echo $basePath; ?>index.php" aria-label="Trang chủ"><i class="bi bi-house-door"></i></a>
                    <?php foreach ($categories as $category) { ?>
                        <a href="<?php echo $basePath; ?>category.php?category_id=<?php echo (int)$category["category_id"]; ?>">
                            <?php echo htmlspecialchars($category["category_name"], ENT_QUOTES, "UTF-8"); ?>
                        </a>
                    <?php } ?>
                </nav>
            </div>
        </div>
    </header>

    <div class="modal fade" id="loginModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-body p-4">
                    <div class="login-header mb-4">
                        <div class="login-subtitle text-uppercase">Đăng nhập</div>
                        <h1 class="login-title mb-2">Chào mừng quay lại</h1>
                        <p class="text-muted mb-0">Đăng nhập để quản lý bài viết và hồ sơ cá nhân.</p>
                    </div>

                    <form method="post" action="<?php echo htmlspecialchars($currentRequest, ENT_QUOTES, "UTF-8"); ?>">
                        <input type="hidden" name="auth_action" value="login">
                        <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($currentRequest, ENT_QUOTES, "UTF-8"); ?>">
                        <input type="hidden" name="modal" value="1">
                        <div class="mb-3">
                            <label class="form-label" for="login-email-modal">Email</label>
                            <input class="form-control" id="login-email-modal" type="email" name="email" placeholder="name@example.com" value="<?php echo htmlspecialchars($loginFeedback["email"] ?? "", ENT_QUOTES, "UTF-8"); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="login-password-modal">Mật khẩu</label>
                            <input class="form-control" id="login-password-modal" type="password" name="password" placeholder="••••••••" required>
                        </div>
                        <div class="d-grid">
                            <button class="btn btn-danger" type="submit">Đăng nhập</button>
                        </div>
                        <div class="text-muted small mt-3 text-center">
                            Bạn chưa có tài khoản? <a href="#registerModal" data-bs-toggle="modal" data-bs-target="#registerModal" data-bs-dismiss="modal">Đăng ký ngay</a>.
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="registerModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-body p-4">
                    <div class="login-header mb-4">
                        <div class="login-subtitle text-uppercase">Đăng ký</div>
                        <h1 class="login-title mb-2">Tạo tài khoản</h1>
                        <p class="text-muted mb-0">Tài khoản mới sẽ có quyền Reader mặc định.</p>
                    </div>

                    <form method="post" action="<?php echo htmlspecialchars($currentRequest, ENT_QUOTES, "UTF-8"); ?>">
                        <input type="hidden" name="auth_action" value="register">
                        <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($currentRequest, ENT_QUOTES, "UTF-8"); ?>">
                        <input type="hidden" name="modal" value="1">
                        <div class="mb-3">
                            <label class="form-label" for="register-username-modal">Tên đăng nhập</label>
                            <input class="form-control" id="register-username-modal" type="text" name="username" placeholder="tennguoiddung" value="<?php echo htmlspecialchars($registerFeedback["username"] ?? "", ENT_QUOTES, "UTF-8"); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="register-email-modal">Email</label>
                            <input class="form-control" id="register-email-modal" type="email" name="email" placeholder="name@example.com" value="<?php echo htmlspecialchars($registerFeedback["email"] ?? "", ENT_QUOTES, "UTF-8"); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="register-password-modal">Mật khẩu</label>
                            <input class="form-control" id="register-password-modal" type="password" name="password" placeholder="Tối thiểu 6 ký tự" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="register-confirm-modal">Xác nhận mật khẩu</label>
                            <input class="form-control" id="register-confirm-modal" type="password" name="confirm_password" placeholder="Nhập lại mật khẩu">
                        </div>
                        <div class="d-grid">
                            <button class="btn btn-danger" type="submit">Đăng ký</button>
                        </div>
                        <div class="text-muted small mt-3 text-center">
                            Bạn đã có tài khoản? <a href="#loginModal" data-bs-toggle="modal" data-bs-target="#loginModal" data-bs-dismiss="modal">Đăng nhập ngay</a>.
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script>
        window.addEventListener("load", function () {
            var shouldOpen = <?php echo $shouldOpenLoginModal ? "true" : "false"; ?>;
            if (!window.bootstrap) {
                return;
            }
            if (shouldOpen) {
                var modalEl = document.getElementById("loginModal");
                if (modalEl) {
                    var modal = new bootstrap.Modal(modalEl);
                    modal.show();
                }
            }
            var shouldOpenRegister = <?php echo $shouldOpenRegisterModal ? "true" : "false"; ?>;
            if (shouldOpenRegister) {
                var registerEl = document.getElementById("registerModal");
                if (registerEl) {
                    var registerModal = new bootstrap.Modal(registerEl);
                    registerModal.show();
                }
            }
        });
    </script>
    <script>
        (function () {
            var searchWrapper = document.querySelector("[data-search]");
            if (!searchWrapper) {
                return;
            }

            var toggleBtn = searchWrapper.querySelector(".search-toggle");
            var form = searchWrapper.querySelector(".search-form");
            var input = searchWrapper.querySelector(".search-input");
            var isOpen = false;

            var closeSearch = function () {
                if (!isOpen) {
                    return;
                }
                isOpen = false;
                searchWrapper.classList.remove("is-open");
                if (toggleBtn) {
                    toggleBtn.setAttribute("aria-expanded", "false");
                }
                if (form) {
                    form.setAttribute("aria-hidden", "true");
                }
            };

            var openSearch = function () {
                if (isOpen) {
                    return;
                }
                isOpen = true;
                searchWrapper.classList.add("is-open");
                if (toggleBtn) {
                    toggleBtn.setAttribute("aria-expanded", "true");
                }
                if (form) {
                    form.setAttribute("aria-hidden", "false");
                }
                if (input) {
                    input.focus();
                }
            };

            if (toggleBtn) {
                toggleBtn.addEventListener("click", function (event) {
                    event.stopPropagation();
                    openSearch();
                });
            }

            if (form) {
                form.addEventListener("click", function (event) {
                    event.stopPropagation();
                });
            }

            document.addEventListener("click", function (event) {
                if (!searchWrapper.contains(event.target)) {
                    closeSearch();
                }
            });

            document.addEventListener("keydown", function (event) {
                if (event.key === "Escape") {
                    closeSearch();
                }
            });
        })();
    </script>

    <main class="container mt-4">

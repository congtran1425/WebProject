<?php
function auth_cookie_name()
{
    return "auth_token";
}

function auth_secret()
{
    $secret = getenv("APP_AUTH_SECRET") ?: (getenv("APP_SECRET") ?: "");
    return trim((string)$secret);
}

function base64url_encode($data)
{
    return rtrim(strtr(base64_encode($data), "+/", "-_"), "=");
}

function base64url_decode($data)
{
    $data = strtr($data, "-_", "+/");
    $pad = strlen($data) % 4;
    if ($pad > 0) {
        $data .= str_repeat("=", 4 - $pad);
    }
    return base64_decode($data);
}

function sign_token($token, $secret)
{
    return base64url_encode(hash_hmac("sha256", $token, $secret, true));
}

function set_auth_cookie(array $user, $ttlSeconds = 604800)
{
    $secret = auth_secret();
    if ($secret === "") {
        return false;
    }

    $payload = [
        "user_id" => (int)($user["user_id"] ?? 0),
        "username" => (string)($user["username"] ?? ""),
        "email" => (string)($user["email"] ?? ""),
        "role" => (string)($user["role"] ?? "reader"),
        "full_name" => (string)($user["full_name"] ?? ""),
        "avatar" => (string)($user["avatar"] ?? ""),
        "exp" => time() + (int)$ttlSeconds,
    ];

    $json = json_encode($payload);
    if ($json === false) {
        return false;
    }

    $token = base64url_encode($json);
    $sig = sign_token($token, $secret);
    $value = $token . "." . $sig;

    $secure = !empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off";
    setcookie(auth_cookie_name(), $value, [
        "expires" => $payload["exp"],
        "path" => "/",
        "secure" => $secure,
        "httponly" => true,
        "samesite" => "Lax",
    ]);

    return true;
}

function clear_auth_cookie()
{
    setcookie(auth_cookie_name(), "", [
        "expires" => time() - 3600,
        "path" => "/",
        "secure" => !empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off",
        "httponly" => true,
        "samesite" => "Lax",
    ]);
}

function hydrate_session_from_cookie()
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    if (!empty($_SESSION["user_id"])) {
        return false;
    }

    $secret = auth_secret();
    if ($secret === "") {
        return false;
    }

    $cookie = $_COOKIE[auth_cookie_name()] ?? "";
    if ($cookie === "") {
        return false;
    }

    $parts = explode(".", $cookie);
    if (count($parts) !== 2) {
        return false;
    }

    [$token, $sig] = $parts;
    $expected = sign_token($token, $secret);
    if (!hash_equals($expected, $sig)) {
        return false;
    }

    $json = base64url_decode($token);
    if ($json === false) {
        return false;
    }

    $data = json_decode($json, true);
    if (!is_array($data)) {
        return false;
    }

    if (!empty($data["exp"]) && time() > (int)$data["exp"]) {
        return false;
    }

    $_SESSION["user_id"] = (int)($data["user_id"] ?? 0);
    $_SESSION["username"] = $data["username"] ?? "";
    $_SESSION["email"] = $data["email"] ?? "";
    $_SESSION["role"] = $data["role"] ?? "reader";
    $_SESSION["full_name"] = $data["full_name"] ?? "";
    $_SESSION["avatar"] = $data["avatar"] ?? "";

    return true;
}

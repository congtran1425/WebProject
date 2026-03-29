<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$currentRole = $_SESSION["role"] ?? "";
if ($currentRole !== "admin") {
    header("Location: ../index.php");
    exit;
}

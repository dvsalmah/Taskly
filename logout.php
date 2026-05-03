<?php
session_start();
require_once __DIR__ . '/includes/connect.php';

if (isset($_COOKIE['taskly_remember'])) {
    $cookieVal = $_COOKIE['taskly_remember'];
    if (strpos($cookieVal, ':') !== false) {
        [$selector] = explode(':', $cookieVal, 2);
        $stmt = $conn->prepare("DELETE FROM remember_tokens WHERE selector = ?");
        $stmt->bind_param("s", $selector);
        $stmt->execute();
        $stmt->close();
    }
    setcookie('taskly_remember', '', time() - 86400, '/', '', false, true);
}

$_SESSION = [];
session_unset();

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

session_destroy();
header("Location: pages/login.php");
exit;
<?php
session_start();
require_once __DIR__ . '/../includes/connect.php';

function getUserByUsername(mysqli $conn, string $username): ?array {
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? LIMIT 1");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $row ?: null;
}

function buildSessionUser(array $u): array {
    return [
        'id'         => $u['id'],
        'first_name' => $u['first_name'],
        'last_name'  => $u['last_name'],
        'username'   => $u['username'],
        'email'      => $u['email'],
        'contact'    => $u['contact']  ?? '',
        'position'   => $u['position'] ?? '',
        'photo'      => $u['photo']    ?? '',
    ];
}

function issueRememberToken(mysqli $conn, int $userId): string {
    $selector  = bin2hex(random_bytes(7));         
    $validator = bin2hex(random_bytes(32));         
    $hash      = password_hash($validator, PASSWORD_DEFAULT);
    $expiry    = date('Y-m-d H:i:s', strtotime('+30 days'));

    $del = $conn->prepare("DELETE FROM remember_tokens WHERE user_id = ?");
    $del->bind_param("i", $userId);
    $del->execute();
    $del->close();
    $ins = $conn->prepare(
        "INSERT INTO remember_tokens (user_id, selector, validator_hash, esxpires_at, created_ar)
         VALUES (?, ?, ?, ?, NOW())"
    );
    $ins->bind_param("isss", $userId, $selector, $hash, $expiry);
    $ins->execute();
    $ins->close();

    return $selector . ':' . $validator;
}


function setRememberCookie(string $cookieVal): void {
    setcookie(
        'taskly_remember',
        $cookieVal,
        time() + 60 * 60 * 24 * 30,   
        '/',
        '',
        false,   
        true    
    );
}

function clearRememberCookie(): void {
    setcookie('taskly_remember', '', time() - 86400, '/', '', false, true);
}

if (!isset($_SESSION['user']) && isset($_COOKIE['taskly_remember'])) {
    $cookieVal = $_COOKIE['taskly_remember'];

    if (strpos($cookieVal, ':') !== false) {
        [$selector, $validator] = explode(':', $cookieVal, 2);

        $stmt = $conn->prepare(
            "SELECT rt.id AS token_id, rt.validator_hash, rt.esxpires_at, rt.user_id,
                    u.*
             FROM   remember_tokens rt
             JOIN   users u ON rt.user_id = u.id
             WHERE  rt.selector = ?
             LIMIT  1"
        );
        $stmt->bind_param("s", $selector);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        $tokenValid = false;

        if ($row) {
            if (strtotime($row['esxpires_at']) > time()) {
                if (password_verify($validator, $row['validator_hash'])) {
                    $tokenValid = true;
                }
            }
        }

        if ($tokenValid) {
            $_SESSION['user'] = buildSessionUser($row);

            $delOld = $conn->prepare("DELETE FROM remember_tokens WHERE selector = ?");
            $delOld->bind_param("s", $selector);
            $delOld->execute();
            $delOld->close();

            $userId       = (int)$row['user_id'];
            $newCookieVal = issueRememberToken($conn, $userId);

            setRememberCookie($newCookieVal);

            header('Location: homepage.php');
            exit;

        } else {
            clearRememberCookie();
            if ($row) {
                $delStale = $conn->prepare("DELETE FROM remember_tokens WHERE selector = ?");
                $delStale->bind_param("s", $selector);
                $delStale->execute();
                $delStale->close();
            }
        }
    }
}

if (isset($_SESSION['user'])) {
    header('Location: homepage.php');
    exit;
}
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username   = trim($_POST['username'] ?? '');
    $password   = $_POST['password'] ?? '';
    $rememberMe = isset($_POST['remember']);

    $user = getUserByUsername($conn, $username);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user'] = buildSessionUser($user);

        if ($rememberMe) {
            $userId       = (int)$user['id'];
            $newCookieVal = issueRememberToken($conn, $userId);
            setRememberCookie($newCookieVal);
        }

        header('Location: homepage.php');
        exit;

    } else {
        $error = 'Incorrect username or password. Please try again.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<?php $pageTitle = 'Sign In'; include '../includes/head.php'; ?>
    <link rel="stylesheet" href="../css/auth.css">
</head>
<body>

<div class="auth-bg">
    <div class="auth-card">

        <div class="auth-form-side">
            <h1 class="auth-title">Welcome Back!</h1>
            <p class="auth-subtitle">Continue where you left off</p>

            <?php if ($error): ?>
                <div class="alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form action="login.php" method="POST" class="auth-form">
                <div class="input-group">
                    <img class="input-icon" src="../assets/user.svg" alt="">
                    <input type="text" name="username" placeholder="Enter Username" required autocomplete="username">
                </div>

                <div class="input-group">
                    <img class="input-icon" src="../assets/password.svg" alt="">
                    <input type="password" name="password" placeholder="Enter Password" required autocomplete="current-password">
                </div>

                <label class="checkbox-label">
                    <input type="checkbox" name="remember"> Keep me signed in
                </label>

                <button type="submit" class="btn-auth">Sign In</button>

                <p class="auth-switch">
                    Don't have an account? <a href="register.php">Register here</a>
                </p>
            </form>
        </div>

        <div class="auth-logo-side">
            <div class="logo-wrap">
                <img src="../assets/taskly.svg" alt="Taskly Logo" class="logo-svg">
            </div>
        </div>

    </div>
</div>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="../js/auth.js"></script>
</body>
</html>
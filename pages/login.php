<?php
session_start();
$users_file = 'data/users.json';

//cek cookie remember
if (!isset($_SESSION['user']) && isset($_COOKIE['taskly_remember'])) {
    $cookie_user = $_COOKIE['taskly_remember'];
    
    if (file_exists($users_file)) {
        $users = json_decode(file_get_contents($users_file), true) ?? [];
        foreach ($users as $u) {
            if ($u['username'] === $cookie_user) {
                $_SESSION['user'] = [
                    'first_name' => $u['first_name'],
                    'last_name'  => $u['last_name'],
                    'username'   => $u['username'],
                    'email'      => $u['email'],
                    'contact'    => $u['contact']  ?? '',
                    'position'   => $u['position'] ?? '',
                    'photo'      => $u['photo']    ?? 'https://i.pravatar.cc/150?img=8',
                ];
                header('Location: homepage.php');
                exit;
            }
        }
    }
}

//cek login
if (isset($_SESSION['user'])) {
    header('Location: homepage.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    $users_file = 'data/users.json';
    $users = [];

    if (file_exists($users_file)) {
        $users = json_decode(file_get_contents($users_file), true) ?? [];
    }

    $found = false;
    foreach ($users as $user) {
        if ($user['username'] === $username && password_verify($password, $user['password'])) {
            $_SESSION['user'] = [
                'first_name' => $user['first_name'],
                'last_name'  => $user['last_name'],
                'username'   => $user['username'],
                'email'      => $user['email'],
                'contact'    => $user['contact']  ?? '',
                'position'   => $user['position'] ?? '',
                'photo'      => $user['photo']    ?? 'https://i.pravatar.cc/150?img=8',
            ];
            $found = true;
            break;
        }
    }

    if ($found) {
        header('Location: homepage.php');
        exit;
    } else {
        $error = 'Username atau password salah.';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In — Taskly</title>
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
                    <img class="input-icon" viewBox="0 0 24 24" src="../assets/user.svg">
                    <input type="text" name="username" placeholder="Enter Username" required autocomplete="username">
                </div>

                <div class="input-group">
                <img class="input-icon" viewBox="0 0 24 24" src="../assets/password.svg">
                <input type="password" name="password" placeholder="Enter Password" required autocomplete="current-password">
                </div>

                <label class="checkbox-label">
                    <input type="checkbox" name="remember"> Remember me
                </label>

                <button type="submit" class="btn-auth">Login</button>

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
<script src="../js/login.js"></script>
</body>
</html>
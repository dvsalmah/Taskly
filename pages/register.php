<?php
session_start();

if (isset($_SESSION['user'])) {
    header('Location: pages/dashboard.php');
    exit;
}

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = htmlspecialchars(trim($_POST['first_name'] ?? ''));
    $last_name  = htmlspecialchars(trim($_POST['last_name']  ?? ''));
    $username   = htmlspecialchars(trim($_POST['username']   ?? ''));
    $email      = htmlspecialchars(trim($_POST['email']      ?? ''));
    $password   = $_POST['password']         ?? '';
    $confirm_pw = $_POST['confirm_password'] ?? '';
    $agree      = $_POST['agree']            ?? '';

    if (!$first_name || !$last_name || !$username || !$email || !$password) {
        $error = 'Semua field wajib diisi.';
    } elseif ($password !== $confirm_pw) {
        $error = 'Password dan konfirmasi password tidak cocok.';
    } elseif (strlen($password) < 6) {
        $error = 'Password minimal 6 karakter.';
    } elseif (!$agree) {
        $error = 'Kamu harus menyetujui Terms of Service.';
    } else {
        $users_file = 'data/users.json';
        if (!is_dir('data')) mkdir('data', 0755, true);

        $users = [];
        if (file_exists($users_file)) {
            $users = json_decode(file_get_contents($users_file), true) ?? [];
        }

        foreach ($users as $u) {
            if ($u['username'] === $username) { $error = 'Username sudah digunakan.'; break; }
            if ($u['email']    === $email)    { $error = 'Email sudah digunakan.';    break; }
        }

        if (!$error) {
            $users[] = [
                'first_name' => $first_name,
                'last_name'  => $last_name,
                'username'   => $username,
                'email'      => $email,
                'password'   => password_hash($password, PASSWORD_DEFAULT),
                'contact'    => '',
                'position'   => '',
                'photo'      => 'https://i.pravatar.cc/150?img=8',
            ];

            file_put_contents($users_file, json_encode($users, JSON_PRETTY_PRINT));
            $success = 'Akun berhasil dibuat! Silakan sign in.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up — Taskly</title>
    <link rel="stylesheet" href="../css/auth.css">
</head>
<body>

<div class="auth-bg">
    <div class="auth-card auth-card--register">
    <div class="auth-logo-side">
            <div class="logo-wrap">
                <img src="../assets/taskly.svg" alt="Taskly Logo" class="logo-svg">
            </div>
        </div>

        <div class="auth-form-side">
            <h1 class="auth-title">Sign Up</h1>

            <?php if ($error): ?>
                <div class="alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert-success">
                    <?= htmlspecialchars($success) ?>
                    <a href="login.php">Sign In sekarang &rarr;</a>
                </div>
            <?php endif; ?>

            <form action="register.php" method="POST" class="auth-form">
                <div class="input-group">
                <img class="input-icon" viewBox="0 0 24 24" src="../assets/username.svg">
                <input type="text" name="first_name" placeholder="Enter First Name" required value="<?= htmlspecialchars($_POST['first_name'] ?? '') ?>">
                </div>

                <div class="input-group">
                <img class="input-icon" viewBox="0 0 24 24" src="../assets/username.svg">
                <input type="text" name="last_name" placeholder="Enter Last Name" required value="<?= htmlspecialchars($_POST['last_name'] ?? '') ?>">
                </div>

                <div class="input-group">
                <img class="input-icon" viewBox="0 0 24 24" src="../assets/user.svg">
                <input type="text" name="username" placeholder="Enter Username" required value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
                </div>

                <div class="input-group">
                <img class="input-icon" viewBox="0 0 24 24" src="../assets/mail.svg">
                    <input type="email" name="email" placeholder="Enter Email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                </div>

                <div class="input-group">
                <img class="input-icon" viewBox="0 0 24 24" src="../assets/password.svg">
                    <input type="password" name="password" placeholder="Enter Password" required>
                </div>

                <div class="input-group">
                <img class="input-icon" viewBox="0 0 24 24" src="../assets/password.svg">
                    <input type="password" name="confirm_password" placeholder="Confirm Password" required>
                </div>

                <label class="checkbox-label">
                    <input type="checkbox" name="agree" <?= isset($_POST['agree']) ? 'checked' : '' ?>>
                    I agree to Terms of Service and Privacy Policy
                </label>

                <button type="submit" class="btn-auth">Register</button>

                <p class="auth-switch">
                    Already have an account? <a href="login.php">Sign In</a>
                </p>
            </form>
        </div>

    </div>
</div>

</body>
</html>
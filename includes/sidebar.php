<?php
$user      = $_SESSION['user'] ?? [];
$full_name = trim(($user['first_name'] ?? 'User') . ' ' . ($user['last_name'] ?? ''));
$email     = $user['email'] ?? '';
$current   = basename($_SERVER['PHP_SELF']);

$photo_raw = $user['photo'] ?? '';

if (empty($photo_raw)) {
    $foto = 'https://i.pravatar.cc/150?img=8';
} elseif (strpos($photo_raw, 'http') === 0) {
    $foto = $photo_raw;
} else {
    $photo_path = __DIR__ . '/../' . $photo_raw;
    if (file_exists($photo_path)) {
        $foto = '../' . $photo_raw;
    } else {
        $foto = 'https://i.pravatar.cc/150?img=8'; 
    }
}
?>

<aside class="sidebar">
    <div class="sidebar-profile">
        <div class="sidebar-avatar-wrap">
            <img src="<?= htmlspecialchars($foto) ?>" alt="Avatar" class="sidebar-avatar" onerror="this.src='https://i.pravatar.cc/150?img=8'">
        </div>
        <p class="sidebar-name"><?= htmlspecialchars($full_name) ?></p>
        <p class="sidebar-email"><?= htmlspecialchars($email) ?></p>
    </div>

    <nav class="sidebar-nav">
        <a href="dashboard.php" class="nav-item <?= $current === 'dashboard.php' ? 'active' : '' ?>">
            <span class="nav-icon">&#⊞;</span> Dashboard
        </a>
        <a href="vital-task.php" class="nav-item <?= $current === 'vital-task.php' ? 'active' : '' ?>">
            <span class="nav-icon">&#9888;</span> Vital Task
        </a>
        <a href="my-task.php" class="nav-item <?= $current === 'my-task.php' ? 'active' : '' ?>">
            <span class="nav-icon">&#10003;</span> My Task
        </a>
        <a href="task-categories.php" class="nav-item <?= $current === 'task-categories.php' ? 'active' : '' ?>">
            <span class="nav-icon">&#9776;</span> Task Categories
        </a>
        <a href="profile.php" class="nav-item <?= $current === 'profile.php' ? 'active' : '' ?>">
            <span class="nav-icon">&#9881;</span> Settings
        </a>
        <a href="#" class="nav-item">
            <span class="nav-icon">&#63;</span> Help
        </a>
    </nav>

    <a href="../logout.php" class="logout-btn">
        &#8594; Logout
    </a>
</aside>
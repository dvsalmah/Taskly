<?php
$user    = $_SESSION['user'] ?? [];
$current = basename($_SERVER['PHP_SELF']);
?>

<aside class="sidebar">
    <nav class="sidebar-nav" style="margin-top: 16px;">
        <a href="homepage.php" class="nav-item <?= $current === 'homepage.php' ? 'active' : '' ?>">
            <span class="side-icon">
                <img src="../assets/homepage.svg" alt="">
            </span> Homepage
        </a>
        <a href="vital-task.php" class="nav-item <?= $current === 'vital-task.php' ? 'active' : '' ?>">
            <span class="side-icon">
                <img src="../assets/vital-task.svg" alt="">
            </span> Vital Task
        </a>
        <a href="my-task.php" class="nav-item <?= $current === 'my-task.php' ? 'active' : '' ?>">
            <span class="side-icon">
                <img src="../assets/my-task.svg" alt="">
            </span> My Task
        </a>
        <a href="task-category.php" class="nav-item <?= $current === 'task-category.php' ? 'active' : '' ?>">
            <span class="side-icon">
                <img src="../assets/category.svg" alt="">
            </span> Task Categories
        </a>
        <a href="help.php" class="nav-item <?= $current === 'help.php' ? 'active' : '' ?>">
            <span class="side-icon">
                <img src="../assets/help.svg" alt="">
            </span> Help
        </a>
    </nav>

    <a href="../logout.php" class="logout-btn" onclick="return confirm('Are you sure you want to logout?')">
        <span class="logout-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                 fill="none" stroke="currentColor" stroke-width="2"
                 stroke-linecap="round" stroke-linejoin="round">
                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                <polyline points="16 17 21 12 16 7"/>
                <line x1="21" y1="12" x2="9" y2="12"/>
            </svg>
        </span>
        Logout
    </a>
</aside>
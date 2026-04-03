<?php
    // Logika backend bisa ditaruh di atas sini
    $appName = "Taskly";
    $pageTitle = "Welcome to " . $appName;
    $heroSubtitle = "Kickstart your productivity journey. Keep track of your tasks, plan your schedule, and get things done—easier than ever.";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/svg+xml" href="assets/icon.svg" />
    <title><?= $pageTitle; ?></title> <link rel="stylesheet" href="css/style.css">
</head>
<body>

    <div class="hero-section">
        <img src="assets/taskly.svg" alt="<?= $appName; ?> Logo" class="hero-logo">
        
        <h1 class="hero-title">Welcome to <?= $appName; ?></h1>
        <p class="hero-subtitle"><?= $heroSubtitle; ?></p>
        
        <div class="action-buttons">
            <a href="pages/login.php" class="btn-outline">Sign In</a>
            <a href="pages/register.php" class="btn-primary">Get Started</a>
        </div>
    </div>

</body>
</html>
<?php
    $appName = "Taskly";
    $pageTitle = $appName;
    $heroSubtitle = "Kickstart your productivity journey. Keep track of your tasks, plan your schedule, and get things done—easier than ever.";
?>
<!DOCTYPE html>
<html lang="en">
<head>
<?php $pageTitle = ''; include 'includes/head.php'; ?>
    <link rel="stylesheet" href="css/style.css">
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
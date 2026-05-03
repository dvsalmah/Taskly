<?php
session_start();
require_once __DIR__ . '/../includes/connect.php';

if (!isset($_SESSION['user'])) {
    header('Location: ../login.php');
    exit;
}

$message  = '';
$msg_type = '';

// ─── Cancel photo preview ────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'cancel_photo') {
    unset($_SESSION['temp_photo']);
    $msg_type = 'error';
}

// ─── Keep photo preview ──────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'keep_photo') {
    if (isset($_SESSION['temp_photo'])) {
        $_SESSION['temp_photo']['confirmed'] = true;
        $msg_type = 'success';
    }
}

// ─── Photo preview ───────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'preview') {
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === 0) {
        $file        = $_FILES['foto'];
        $allowed_ext = ['jpg', 'jpeg', 'png'];
        $ext         = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $max_size    = 2 * 1024 * 1024;

        if (!in_array($ext, $allowed_ext)) {
            $message  = 'Only .jpg, .jpeg and .png files are allowed.';
            $msg_type = 'error';
        } elseif ($file['size'] > $max_size) {
            $message  = 'Maximum file size is 2MB.';
            $msg_type = 'error';
        } else {
            $_SESSION['temp_photo'] = [
                'content'   => base64_encode(file_get_contents($file['tmp_name'])),
                'mime'      => $file['type'],
                'name'      => $file['name'],
                'ext'       => $ext,
                'confirmed' => false,
            ];
            $message  = 'Confirm your photo in the preview box below.';
            $msg_type = 'success';
        }
    } else {
        $message  = 'No photo/files to preview yet.';
        $msg_type = 'error';
    }
}

// ─── Update Info ─────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update_info') {

    $first_name = htmlspecialchars(trim($_POST['first_name'] ?? ''));
    $last_name  = htmlspecialchars(trim($_POST['last_name']  ?? ''));
    $email      = htmlspecialchars(trim($_POST['email']      ?? ''));
    $contact    = htmlspecialchars(trim($_POST['contact']    ?? ''));
    $position   = htmlspecialchars(trim($_POST['position']   ?? ''));
    $username   = $_SESSION['user']['username'];

    // Update session
    $_SESSION['user']['first_name'] = $first_name;
    $_SESSION['user']['last_name']  = $last_name;
    $_SESSION['user']['email']      = $email;
    $_SESSION['user']['contact']    = $contact;
    $_SESSION['user']['position']   = $position;

    // ─── Handle photo upload ──────────────────────────────
    $uploadsDir   = realpath(__DIR__ . '/../uploads');
    if (!$uploadsDir) {
        $uploadsDir = __DIR__ . '/../uploads';
        @mkdir($uploadsDir, 0755, true);
        $uploadsDir = realpath($uploadsDir);
    }

    $safeUsername = preg_replace('/[^a-z0-9_-]/i', '', $username);
    $photoSaved   = false;
    $newPhotoPath = $_SESSION['user']['photo'] ?? '';

    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === 0) {
        $file        = $_FILES['foto'];
        $ext         = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $newFilename = 'photo_' . $safeUsername . '_' . time() . '.' . $ext;
        $destination = $uploadsDir . '/' . $newFilename;

        if (move_uploaded_file($file['tmp_name'], $destination)) {
            $photoSaved   = true;
            $newPhotoPath = 'uploads/' . $newFilename;
        }
    } elseif (isset($_SESSION['temp_photo']) && !empty($_SESSION['temp_photo']['confirmed'])) {
        $content     = base64_decode($_SESSION['temp_photo']['content']);
        $ext         = $_SESSION['temp_photo']['ext'];
        $newFilename = 'photo_' . $safeUsername . '_' . time() . '.' . $ext;
        $destination = $uploadsDir . '/' . $newFilename;

        if (file_put_contents($destination, $content)) {
            $photoSaved   = true;
            $newPhotoPath = 'uploads/' . $newFilename;
        }
    }

    if ($photoSaved) {
        // Remove the old photo file if stored locally in uploads/
        $oldPhoto = $_SESSION['user']['photo'] ?? '';
        if (!empty($oldPhoto) && strpos($oldPhoto, 'http') === false && strpos($oldPhoto, 'uploads/') === 0) {
            $oldPath = __DIR__ . '/../' . $oldPhoto;
            if (file_exists($oldPath)) @unlink($oldPath);
        }
        $_SESSION['user']['photo'] = $newPhotoPath;
        unset($_SESSION['temp_photo']);
    }

    // ─── Persist to database ──────────────────────────────
    $stmt = $conn->prepare(
        "UPDATE users SET first_name=?, last_name=?, email=?, contact=?, position=?, photo=?
         WHERE username=?"
    );
    $stmt->bind_param(
        "sssssss",
        $_SESSION['user']['first_name'],
        $_SESSION['user']['last_name'],
        $_SESSION['user']['email'],
        $_SESSION['user']['contact'],
        $_SESSION['user']['position'],
        $_SESSION['user']['photo'],
        $username
    );
    $stmt->execute();
    $stmt->close();

    $message  = 'Your profile has been updated successfully.';
    $msg_type = 'success';
}

// ─── Prepare display values ──────────────────────────────
$user = $_SESSION['user'];

$val_first_name = $_POST['first_name'] ?? $user['first_name'] ?? '';
$val_last_name  = $_POST['last_name']  ?? $user['last_name']  ?? '';
$val_email      = $_POST['email']      ?? $user['email']      ?? '';
$val_contact    = $_POST['contact']    ?? $user['contact']    ?? '';
$val_position   = $_POST['position']   ?? $user['position']   ?? '';

if (isset($_SESSION['temp_photo']) && !empty($_SESSION['temp_photo']['confirmed'])) {
    $photo_url = 'data:' . $_SESSION['temp_photo']['mime'] . ';base64,' . $_SESSION['temp_photo']['content'];
} else {
    $photo_raw = $user['photo'] ?? '';
    if (empty($photo_raw)) {
        $photo_url = 'https://i.pravatar.cc/150?img=8';
    } elseif (strpos($photo_raw, 'http') === 0) {
        $photo_url = $photo_raw;
    } else {
        $photo_path = __DIR__ . '/../' . $photo_raw;
        $photo_url  = file_exists($photo_path) ? '../' . $photo_raw : 'https://i.pravatar.cc/150?img=8';
    }
}

$full_name_display = trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));
?>
<!DOCTYPE html>
<html lang="en">
<head>
<?php $pageTitle = 'Profile'; include '../includes/head.php'; ?>
    <link rel="stylesheet" href="../css/main.css?v=<?= time(); ?>">
    <link rel="stylesheet" href="../css/profile.css?v=<?= time(); ?>">
</head>
<body>

<?php include '../includes/sidebar.php'; ?>

<div class="main-wrap">

    <?php include '../includes/navbar.php'; ?>

    <main class="content">
        <div class="card">
            <div class="card-header">
                <h2>Account Information</h2>
                <a href="homepage.php" class="go-back">Go Back</a>
            </div>

            <div class="profile-preview">
                <div class="profile-photo-wrap">
                    <img
                        src="<?= htmlspecialchars($photo_url) ?>"
                        alt="Foto Profil"
                        class="profile-photo"
                        onerror="this.src='https://i.pravatar.cc/150?img=8'"
                    >
                </div>
                <div class="profile-info">
                    <p class="profile-name"><?= htmlspecialchars($full_name_display) ?></p>
                    <p class="profile-email"><?= htmlspecialchars($user['email']) ?></p>
                </div>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-<?= $msg_type ?>">
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>

            <form action="profile.php" method="POST" enctype="multipart/form-data" class="profile-form">

                <div class="upload-wrap">
                    <label for="foto" class="upload-label">
                        Update your profile
                        <span class="upload-hint">(Choose File)</span>
                    </label>
                    <input type="file" name="foto" id="foto" accept="image/jpeg,image/png,image/jpg" class="upload-input">
                </div>

                <?php if (isset($_SESSION['temp_photo']) && empty($_SESSION['temp_photo']['confirmed'])): ?>
                    <div class="photo-preview-box">
                        <p class="preview-label">are you sure to use this photo?</p>
                        <div class="preview-photo-wrap">
                            <img src="data:<?= $_SESSION['temp_photo']['mime'] ?>;base64,<?= $_SESSION['temp_photo']['content'] ?>" alt="Preview" class="preview-photo">
                        </div>
                        <div class="preview-actions">
                            <button type="submit" name="action" value="keep_photo" class="btn btn-outline" style="border: 1.5px solid #2E7D32; color: #2E7D32;">Yes, I'm sure</button>
                            <button type="submit" name="action" value="cancel_photo" class="btn btn-outline" style="border: 1.5px solid #C62828; color: #C62828;">No, Cancel this</button>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="form-grid">
                    <div class="form-group">
                        <label for="first_name">First Name</label>
                        <input type="text" id="first_name" name="first_name" value="<?= htmlspecialchars($val_first_name) ?>">
                    </div>
                    <div class="form-group">
                        <label for="last_name">Last Name</label>
                        <input type="text" id="last_name" name="last_name" value="<?= htmlspecialchars($val_last_name) ?>">
                    </div>
                    <div class="form-group full-width">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" value="<?= htmlspecialchars($val_email) ?>">
                    </div>
                    <div class="form-group">
                        <label for="contact">Contact Number</label>
                        <input type="text" id="contact" name="contact" value="<?= htmlspecialchars($val_contact) ?>">
                    </div>
                    <div class="form-group">
                        <label for="position">Position</label>
                        <input type="text" id="position" name="position" value="<?= htmlspecialchars($val_position) ?>">
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" name="action" value="preview" class="btn btn-outline">
                        <img class="btn-icon" viewBox="0 0 24 24" src="../assets/camera.svg">
                    Preview
                    </button>
                    <button type="submit" name="action" value="update_info" class="btn btn-primary">Update Info</button>
                    <a href="change-password.php" class="btn btn-outline" style="border;">Change Password</a>
                </div>
            </form>
        </div>
    </main>

</div>

</body>
</html>
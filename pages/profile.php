<!-- ini aku vibecoding in , mintol adjust aja, aku blum liat preview nya jugaa-->

<?php
session_start();

if (!isset($_SESSION['user'])) {
    header('Location: ../login.php');
    exit;
}

$message  = '';
$msg_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update_info') {

    $_SESSION['user']['first_name'] = htmlspecialchars(trim($_POST['first_name'] ?? ''));
    $_SESSION['user']['last_name']  = htmlspecialchars(trim($_POST['last_name']  ?? ''));
    $_SESSION['user']['email']      = htmlspecialchars(trim($_POST['email']      ?? ''));
    $_SESSION['user']['contact']    = htmlspecialchars(trim($_POST['contact']    ?? ''));
    $_SESSION['user']['position']   = htmlspecialchars(trim($_POST['position']   ?? ''));

    // Handle upload foto
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === 0) {
        $file        = $_FILES['foto'];
        $allowed_ext = ['jpg', 'jpeg', 'png'];
        $ext         = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        // Operasi string: cek ekstensi
        if (!in_array($ext, $allowed_ext)) {
            $message  = 'Hanya file .jpg dan .png yang diizinkan.';
            $msg_type = 'error';
        } else {
            $uploads_dir = '../uploads/';
            if (!is_dir($uploads_dir)) mkdir($uploads_dir, 0755, true);

            // Operasi string: buat nama file unik
            $username     = $_SESSION['user']['username'] ?? 'user';
            $new_filename = 'foto_' . $username . '_' . time() . '.' . $ext;
            $destination  = $uploads_dir . $new_filename;

            if (move_uploaded_file($file['tmp_name'], $destination)) {
                // Hapus foto lama kalau bukan placeholder
                $old = $_SESSION['user']['photo'] ?? '';
                if (!empty($old) && strpos($old, 'http') !== 0 && file_exists('../' . $old)) {
                    unlink('../' . $old);
                }
                $_SESSION['user']['photo'] = 'uploads/' . $new_filename;
            } else {
                $message  = 'Gagal menyimpan foto.';
                $msg_type = 'error';
            }
        }
    }

    if (!$message) {
        $message  = 'Profil berhasil diperbarui!';
        $msg_type = 'success';
    }
}

$user      = $_SESSION['user'];
$full_name = trim($user['first_name'] . ' ' . $user['last_name']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile — Taskly</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600&family=DM+Serif+Display&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/main.css">
    <link rel="stylesheet" href="../assets/css/profile.css">
</head>
<body>

<?php include '../includes/sidebar.php'; ?>

<div class="main-wrap">

    <?php include '../includes/navbar.php'; ?>

    <main class="content">
        <div class="card">
            <div class="card-header">
                <h2>Account Information</h2>
                <a href="dashboard.php" class="go-back">Go Back</a>
            </div>

            <!-- Foto profil: tampil hasil upload setelah submit -->
            <div class="profile-preview">
                <div class="profile-photo-wrap">
                    <img
                        src="<?= htmlspecialchars($user['photo']) ?>"
                        alt="Foto Profil"
                        class="profile-photo"
                    >
                </div>
                <div class="profile-info">
                    <p class="profile-name"><?= htmlspecialchars($full_name) ?></p>
                    <p class="profile-email"><?= htmlspecialchars($user['email']) ?></p>
                </div>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-<?= $msg_type ?>">
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>

            <form action="profile.php" method="POST" enctype="multipart/form-data" class="profile-form">
                <input type="hidden" name="action" value="update_info">

                <!-- Upload foto via label — klik label = buka file picker, tanpa JS -->
                <div class="upload-wrap">
                    <label for="foto" class="upload-label">
                        &#128247; Ganti Foto Profil
                        <span class="upload-hint">(jpg / png)</span>
                    </label>
                    <input
                        type="file"
                        name="foto"
                        id="foto"
                        accept=".jpg,.jpeg,.png"
                        class="upload-input"
                    >
                    <?php if (!empty($_FILES['foto']['name'])): ?>
                        <span class="upload-filename"><?= htmlspecialchars($_FILES['foto']['name']) ?></span>
                    <?php endif; ?>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label for="first_name">First Name</label>
                        <input type="text" id="first_name" name="first_name" value="<?= htmlspecialchars($user['first_name']) ?>">
                    </div>
                    <div class="form-group">
                        <label for="last_name">Last Name</label>
                        <input type="text" id="last_name" name="last_name" value="<?= htmlspecialchars($user['last_name']) ?>">
                    </div>
                    <div class="form-group full-width">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>">
                    </div>
                    <div class="form-group">
                        <label for="contact">Contact Number</label>
                        <input type="text" id="contact" name="contact" value="<?= htmlspecialchars($user['contact']) ?>">
                    </div>
                    <div class="form-group">
                        <label for="position">Position</label>
                        <input type="text" id="position" name="position" value="<?= htmlspecialchars($user['position']) ?>">
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Update Info</button>
                    <a href="change-password.php" class="btn btn-outline">Change Password</a>
                </div>
            </form>
        </div>
    </main>

</div>

</body>
</html>
<?php
session_start();
if (!isset($_SESSION['user'])) { header('Location: ../login.php'); exit; }

require_once '../includes/connect.php';
require_once '../includes/task-helper.php';

$username = $_SESSION['user']['username'];
$message  = '';
$msg_type = '';

$action = $_POST['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if ($action === 'add') {
        $name  = trim($_POST['name']  ?? '');
        $color = $_POST['color'] ?? '#EC003F';
        if ($name === '') {
            $message  = 'Category name is required.';
            $msg_type = 'error';
        } else {
            addCategory($username, $name, $color);
            $message  = 'Category added!';
            $msg_type = 'success';
        }
    }

    elseif ($action === 'delete') {
        $id = (int)($_POST['cat_id'] ?? 0);
        if ($id) deleteCategory($id, $username);
        header('Location: task-category.php'); exit;
    }
}

$categories = loadCategories($username);
$allTasks   = loadTasks($username);

// Count tasks per category
$catCounts = [];
foreach ($allTasks as $t) {
    $cid = $t['category_id'] ? (int)$t['category_id'] : null;
    if ($cid) $catCounts[$cid] = ($catCounts[$cid] ?? 0) + 1;
}

// Preset colors
$presets = ['#EC003F','#FF6F00','#F9A825','#2E7D32','#1565C0','#6A1B9A','#00838F','#4E342E','#546E7A'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<?php $pageTitle = 'Task Categories'; include '../includes/head.php'; ?>
    <link rel="stylesheet" href="../css/main.css?v=<?= time() ?>">
    <link rel="stylesheet" href="../css/tasks.css?v=<?= time() ?>">
    <style>
        .color-presets { display:flex; gap:8px; flex-wrap:wrap; margin-top:8px; }
        .color-preset {
            width:28px; height:28px; border-radius:50%; cursor:pointer;
            border:2px solid transparent; transition:border-color 0.15s, transform 0.15s;
        }
        .color-preset:hover, .color-preset.selected { border-color: var(--text); transform:scale(1.15); }
    </style>
</head>
<body>

<?php include '../includes/sidebar.php'; ?>

<div class="main-wrap">
    <?php include '../includes/navbar.php'; ?>
    <main class="content">

        <div class="page-header">
            <div>
                <h1>Task Categories</h1>
                <p><?= count($categories) ?> categor<?= count($categories) !== 1 ? 'ies' : 'y' ?></p>
            </div>
            <button class="btn btn-primary" id="openCatModal">
                <img src="../assets/add.svg" alt="Add Category" class="icon-small">
                Add Category
            </button>
        </div>

        <?php if ($message): ?>
            <div style="padding:10px 16px; border-radius:8px; margin-bottom:16px; font-size:13px;
                        background:<?= $msg_type==='success'?'#E8F5E9':'#FFEBEE' ?>;
                        color:<?= $msg_type==='success'?'#2E7D32':'#C62828' ?>;
                        border:1px solid <?= $msg_type==='success'?'#A5D6A7':'#EF9A9A' ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <?php if (empty($categories)): ?>
            <div class="empty-state">
                <h3>No categories yet</h3>
                <p>Create categories to organise your tasks by topic, project, or priority.</p>
            </div>
        <?php else: ?>
            <div class="cat-grid">
                <?php foreach ($categories as $cat):
                    $count = $catCounts[$cat['id']] ?? 0;
                ?>
                <div class="cat-card" style="border-top-color:<?= htmlspecialchars($cat['color']) ?>">
                    <div class="cat-swatch" style="background:<?= htmlspecialchars($cat['color']) ?>"></div>
                    <div class="cat-info">
                        <div class="cat-name"><?= htmlspecialchars($cat['name']) ?></div>
                        <div class="cat-count"><?= $count ?> task<?= $count !== 1 ? 's' : '' ?></div>
                    </div>
                    <form method="POST" action="task-category.php" style="margin-left:auto">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="cat_id" value="<?= htmlspecialchars($cat['id']) ?>">
                        <button type="submit" class="btn btn-danger btn-sm btn-delete" title="Delete category">🗑</button>
                    </form>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </main>
</div>

<div class="modal-overlay" id="catModal">
    <div class="modal" style="max-width:400px">
        <div class="modal-header">
            <h2>New Category</h2>
            <button class="modal-close" id="closeCatModal">✕</button>
        </div>
        <form method="POST" action="task-category.php">
            <input type="hidden" name="action" value="add">
            <input type="hidden" name="color"  id="colorInput" value="#EC003F">

            <div class="form-group">
                <label for="cat_name">Category Name *</label>
                <input type="text" id="cat_name" name="name" placeholder="e.g. Work, Personal, Study..." required>
            </div>

            <div class="form-group">
                <label>Pick a Color</label>
                <div class="color-presets" id="colorPresets">
                    <?php foreach ($presets as $i => $hex): ?>
                        <div class="color-preset <?= $i===0?'selected':'' ?>"
                             style="background:<?= $hex ?>"
                             data-color="<?= $hex ?>"
                             title="<?= $hex ?>"></div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="form-actions">
                <button type="button" class="btn btn-ghost" id="closeCatModalBtn"
                        onclick="document.getElementById('catModal').classList.remove('open'); document.body.style.overflow=''">
                    Cancel
                </button>
                <button type="submit" class="btn btn-primary">Create</button>
            </div>
        </form>
    </div>
</div>

<script src="../js/tasks.js"></script>
<script>
var openCatBtn  = document.getElementById('openCatModal');
var catModal    = document.getElementById('catModal');
var closeCatBtn = document.getElementById('closeCatModal');

if (openCatBtn && catModal) {
    openCatBtn.addEventListener('click', function () {
        catModal.classList.add('open');
        document.body.style.overflow = 'hidden';
    });
}
if (closeCatBtn && catModal) {
    closeCatBtn.addEventListener('click', function () {
        catModal.classList.remove('open');
        document.body.style.overflow = '';
    });
}
if (catModal) {
    catModal.addEventListener('click', function (e) {
        if (e.target === catModal) {
            catModal.classList.remove('open');
            document.body.style.overflow = '';
        }
    });
}

document.querySelectorAll('.color-preset').forEach(function(el) {
    el.addEventListener('click', function() {
        document.querySelectorAll('.color-preset').forEach(function(p){ p.classList.remove('selected'); });
        el.classList.add('selected');
        document.getElementById('colorInput').value = el.dataset.color;
    });
});
</script>
</body>
</html>

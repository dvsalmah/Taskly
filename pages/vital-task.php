<?php
session_start();
if (!isset($_SESSION['user'])) { header('Location: ../login.php'); exit; }

require_once '../includes/task-helper.php';

$username   = $_SESSION['user']['username'];
$categories = loadCategories($username);
$tasks      = loadTasks($username);

$vital_tasks = array_values(array_filter($tasks, fn($t) => isVital($t)));

usort($vital_tasks, function ($a, $b) {
    $dlA = $a['deadline'] ?? '';
    $dlB = $b['deadline'] ?? '';
    if ($dlA && $dlB) return strcmp($dlA, $dlB);
    if ($dlA) return -1;
    if ($dlB) return  1;
    $order = ['in_progress' => 0, 'not_started' => 1, 'completed' => 2];
    return ($order[$a['status']] ?? 9) <=> ($order[$b['status']] ?? 9);
});
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vital Task — Taskly</title>
    <link rel="stylesheet" href="../css/main.css?v=<?= time() ?>">
    <link rel="stylesheet" href="../css/tasks.css?v=<?= time() ?>">
</head>
<body>

<?php include '../includes/sidebar.php'; ?>

<div class="main-wrap">
    <?php include '../includes/navbar.php'; ?>
    <main class="content">

        <div class="page-header">
            <div>
                <h1>Vital Task</h1>
                <p><?= count($vital_tasks) ?> vital task<?= count($vital_tasks) !== 1 ? 's' : '' ?></p>
            </div>
            <a href="my-task.php" class="btn btn-ghost">
                <img src="../assets/left.svg" alt="back" class="vital-icon">Back to My Task</a>
        </div>

        <div class="vital-banner">
            <div>
                <strong>High-priority tasks with upcoming deadlines.</strong><br>
                <span style="font-size:13px;opacity:0.9">Tasks are automatically shown here when they have <strong>High</strong> priority and a deadline within the next 48 hours.</span>
            </div>
        </div>

        <div class="filter-bar">
            <label>Filter:</label>
            <span class="chip active" data-filter="all">All</span>
            <span class="chip" data-filter="not_started">Not Started</span>
            <span class="chip" data-filter="in_progress">In Progress</span>
            <span class="chip" data-filter="completed">Completed</span>
        </div>

        <div class="task-list" id="taskList">
            <?php if (empty($vital_tasks)): ?>
                <div class="empty-state">
                    <div class="empty-icon"></div>
                    <h3>No vital tasks right now</h3>
                    <p>Tasks with <strong>High</strong> priority and a deadline within 48 hours will appear here automatically.</p>
                </div>
            <?php else: ?>
                <div id="emptyState" class="empty-state" style="display:none">
                    <div class="empty-icon">
                        <img src="../assets/search.svg" alt="">
                    </div>
                    <h3>No tasks match this filter</h3>
                </div>
                <?php foreach ($vital_tasks as $task):
                    $cat      = $task['category_id'] ? getCategoryById($task['category_id'], $categories) : null;
                    $priority = $task['priority'] ?? 'high';
                    $dl       = $task['deadline'] ?? '';
                    $updatedAt = $task['updated_at'] ?? '';
                ?>
                <div class="task-card vital <?= htmlspecialchars($task['status']) ?>"
                     data-status="<?= htmlspecialchars($task['status']) ?>">
                    <div class="task-card-body">
                        <div class="task-card-title"><?= htmlspecialchars($task['title']) ?></div>
                        <?php if ($task['description']): ?>
                            <div class="task-card-desc"><?= htmlspecialchars($task['description']) ?></div>
                        <?php endif; ?>
                        <div class="task-card-meta">
                            <span class="badge <?= statusClass($task['status']) ?>"><?= statusLabel($task['status']) ?></span>
                            <span class="badge <?= priorityClass($priority) ?>"><?= priorityLabel($priority) ?></span>
                            <?php if ($cat): ?>
                                <span class="category-dot" style="color:<?= htmlspecialchars($cat['color']) ?>">
                                    <?= htmlspecialchars($cat['name']) ?>
                                </span>
                            <?php endif; ?>
                        </div>
                        <?php if ($dl): ?>
                            <div class="task-card-footer">
                                <span class="task-deadline urgent">🗓 <?= htmlspecialchars(deadlineLabel($dl)) ?></span>
                                <span class="task-date">
                                    <?php if (!empty($updatedAt)): ?>
                                        edited <?= timeAgo($updatedAt) ?>
                                    <?php else: ?>
                                        <?= timeAgo($task['created_at']) ?>
                                    <?php endif; ?>
                                </span>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="task-card-actions">
                        <!-- inline status update -->
                        <form method="POST" action="my-task.php">
                            <input type="hidden" name="action"  value="update_status">
                            <input type="hidden" name="task_id" value="<?= htmlspecialchars($task['id']) ?>">
                            <select name="status" class="status-select">
                                <option value="not_started" <?= $task['status']==='not_started'?'selected':'' ?>>Not Started</option>
                                <option value="in_progress" <?= $task['status']==='in_progress'?'selected':'' ?>>In Progress</option>
                                <option value="completed"   <?= $task['status']==='completed'?'selected':'' ?>>Completed</option>
                            </select>
                        </form>
                        <a href="my-task.php" class="btn btn-ghost btn-sm" title="Go to My Task">
                        <img src="../assets/icons/edit.svg" alt="">
                        Edit</a>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

    </main>
</div>

<script src="../js/tasks.js"></script>
</body>
</html>

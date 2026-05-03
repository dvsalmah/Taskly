<?php
session_start();
if (!isset($_SESSION['user'])) { header('Location: ../login.php'); exit; }

require_once '../includes/connect.php';
require_once '../includes/task-helper.php';

$user     = $_SESSION['user'];
$username = $user['username'];

$tasks      = loadTasks($username);
$categories = loadCategories($username);

$total      = count($tasks);
$completed  = count(array_filter($tasks, fn($t) => $t['status'] === 'completed'));
$in_prog    = count(array_filter($tasks, fn($t) => $t['status'] === 'in_progress'));
$not_start  = count(array_filter($tasks, fn($t) => $t['status'] === 'not_started'));

$pct_completed = $total > 0 ? round($completed  / $total * 100) : 0;
$pct_progress  = $total > 0 ? round($in_prog    / $total * 100) : 0;
$pct_notstart  = $total > 0 ? round($not_start  / $total * 100) : 0;

$todo_tasks = array_filter($tasks, fn($t) => $t['status'] !== 'completed');
usort($todo_tasks, fn($a,$b) => strcmp($b['created_at'], $a['created_at']));
$todo_tasks = array_slice(array_values($todo_tasks), 0, 5);

$done_tasks = array_filter($tasks, fn($t) => $t['status'] === 'completed');
usort($done_tasks, fn($a,$b) => strcmp($b['created_at'], $a['created_at']));
$done_tasks = array_slice(array_values($done_tasks), 0, 5);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<?php $pageTitle = 'Homepage'; include '../includes/head.php'; ?>
    <link rel="stylesheet" href="../css/main.css?v=<?= time() ?>">
    <link rel="stylesheet" href="../css/homepage.css?v=<?= time() ?>">
    <link rel="stylesheet" href="../css/tasks.css?v=<?= time() ?>">
</head>
<body>

    <?php include '../includes/sidebar.php'; ?>
    <div class="main-wrap">
        <?php include '../includes/navbar.php'; ?>
        <main class="content">

            <section class="content">
                <h1>Welcome back, <?= htmlspecialchars($user['first_name'] ?? 'User') ?>!</h1>

                <div class="grid">

                    <div class="card">
                        <h3>To-Do
                            <span style="font-size:12px;font-weight:400;color:var(--text-muted);margin-left:8px">
                                <?= count($todo_tasks) ?> pending
                            </span>
                        </h3>

                        <?php if (empty($todo_tasks)): ?>
                            <div style="text-align:center;padding:28px 0;color:var(--text-muted);font-size:13px">
                                Congratulations! You have completed all your tasks! <a href="my-task.php" style="color:var(--pink-dark)">Add more</a>
                            </div>
                        <?php else: ?>
                            <?php foreach ($todo_tasks as $t):
                                $cat = $t['category_id'] ? getCategoryById((int)$t['category_id'], $categories) : null;
                                $isVital     = isVital($t);
                                $borderColor = $isVital ? '#FF6F00' : ($cat ? $cat['color'] : '#f48b95');
                            ?>
                            <div class="task" style="border-left-color:<?= $borderColor ?>">
                                <h4>
                                    <?php if ($isVital): ?><span style="color:#FF6F00">🔥 </span><?php endif; ?>
                                    <?= htmlspecialchars($t['title']) ?>
                                </h4>
                                <?php if ($t['description']): ?>
                                    <p><?= htmlspecialchars(mb_strimwidth($t['description'], 0, 90, '…')) ?></p>
                                <?php endif; ?>
                                <small>
                                    Status: <?= statusLabel($t['status']) ?>
                                    <?php if ($cat): ?> | <?= htmlspecialchars($cat['name']) ?><?php endif; ?>
                                    | <?= date('d M Y', strtotime($t['created_at'])) ?>
                                </small>
                            </div>
                            <?php endforeach; ?>

                            <?php if (count(array_filter($tasks, fn($t)=>$t['status']!=='completed')) > 5): ?>
                                <p style="text-align:center;margin-top:12px;font-size:12px;color:var(--text-muted)">
                                    <a href="my-task.php" style="color:var(--pink-dark)">View all tasks →</a>
                                </p>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>

                    <div class="card">
                        <h3>Task Status</h3>
                        <?php if ($total === 0): ?>
                            <div style="text-align:center;padding:28px 0;color:var(--text-muted);font-size:13px">
                                No tasks yet. <a href="my-task.php" style="color:var(--pink-dark)">Add one!</a>
                            </div>
                        <?php else: ?>
                        <div class="stat-circles">
                            <div class="stat-circle">
                                <div class="stat-ring green">
                                    <?= $pct_completed ?>%
                                    <small><?= $completed ?> tasks</small>
                                </div>
                                <div class="stat-label">Completed</div>
                            </div>
                            <div class="stat-circle">
                                <div class="stat-ring blue">
                                    <?= $pct_progress ?>%
                                    <small><?= $in_prog ?> tasks</small>
                                </div>
                                <div class="stat-label">In Progress</div>
                            </div>
                            <div class="stat-circle">
                                <div class="stat-ring gray">
                                    <?= $pct_notstart ?>%
                                    <small><?= $not_start ?> tasks</small>
                                </div>
                                <div class="stat-label">Not Started</div>
                            </div>
                        </div>
                        <p style="text-align:center;font-size:12px;color:var(--text-muted);margin-top:12px">
                            <?= $total ?> total task<?= $total!==1?'s':'' ?>
                        </p>
                        <?php endif; ?>
                    </div>

                    <div class="card full">
                        <h3>Recently Completed</h3>
                        <?php if (empty($done_tasks)): ?>
                            <div style="text-align:center;padding:28px 0;color:var(--text-muted);font-size:13px">
                                No completed tasks yet. Keep going!
                            </div>
                        <?php else: ?>
                        <div class="completed-grid">
                            <?php foreach ($done_tasks as $t):
                                $cat = $t['category_id'] ? getCategoryById((int)$t['category_id'], $categories) : null;
                            ?>
                            <div class="completed-task">
                                <h4><?= htmlspecialchars($t['title']) ?></h4>
                                <?php if ($t['description']): ?>
                                    <p><?= htmlspecialchars(mb_strimwidth($t['description'], 0, 100, '…')) ?></p>
                                <?php endif; ?>
                                <small>
                                    Status: Completed
                                    <?php if ($cat): ?> | <?= htmlspecialchars($cat['name']) ?><?php endif; ?>
                                    | <?= timeAgo($t['created_at']) ?>
                                </small>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>

                </div>
            </section>
        </main>
    </div>

</body>
</html>
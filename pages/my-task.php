<?php
session_start();
if (!isset($_SESSION['user'])) { header('Location: ../login.php'); exit; }

require_once '../includes/connect.php';
require_once '../includes/task-helper.php';

$username   = $_SESSION['user']['username'];
$categories = loadCategories($username);
$message    = '';
$msg_type   = '';

$action = $_POST['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if ($action === 'add') {
        $title    = trim($_POST['title']    ?? '');
        $desc     = trim($_POST['desc']     ?? '');
        $cat_id   = $_POST['category_id'] !== '' ? (int)$_POST['category_id'] : null;
        $priority = $_POST['priority']     ?? 'medium';
        $status   = $_POST['status']       ?? 'not_started';
        $deadline = trim($_POST['deadline'] ?? '');

        if (!in_array($priority, ['low','medium','high'])) $priority = 'medium';

        if ($title === '') {
            $message  = 'Task title is required.';
            $msg_type = 'error';
        } else {
            addTask($username, $title, $desc, $cat_id, $priority, $status, $deadline);
            $message  = 'Task added successfully!';
            $msg_type = 'success';
        }
    }

    elseif ($action === 'update_status') {
        $id     = (int)($_POST['task_id'] ?? 0);
        $status = $_POST['status']  ?? '';
        if ($id && in_array($status, ['not_started','in_progress','completed'])) {
            updateTask($id, ['status' => $status]);
        }
        header('Location: my-task.php'); exit;
    }

    elseif ($action === 'edit') {
        $id       = (int)($_POST['task_id']    ?? 0);
        $title    = trim($_POST['title']  ?? '');
        $desc     = trim($_POST['desc']   ?? '');
        $cat_id   = $_POST['category_id'] !== '' ? (int)$_POST['category_id'] : null;
        $priority = $_POST['priority']    ?? 'medium';
        $status   = $_POST['status']      ?? 'not_started';
        $deadline = trim($_POST['deadline'] ?? '');

        if (!in_array($priority, ['low','medium','high'])) $priority = 'medium';

        if ($id && $title) {
            updateTask($id, [
                'title'       => $title,
                'description' => $desc,
                'category_id' => $cat_id,
                'priority'    => $priority,
                'status'      => $status,
                'deadline'    => $deadline,
            ]);
            $message  = 'Task updated.';
            $msg_type = 'success';
        }
    }

    elseif ($action === 'delete') {
        $id = (int)($_POST['task_id'] ?? 0);
        if ($id) deleteTask($id, $username);
        header('Location: my-task.php'); exit;
    }
}

$tasks      = loadTasks($username);
$categories = loadCategories($username);

$minDeadline = date('Y-m-d\TH:i');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<?php $pageTitle = 'My Task'; include '../includes/head.php'; ?>
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
                <h1>My Task</h1>
                <p><?= count($tasks) ?> task<?= count($tasks) !== 1 ? 's' : '' ?> total</p>
            </div>
            <button class="btn btn-primary" id="openTaskModal">
                <img src="../assets/add.svg" alt="Add Task" class="icon-small">
                Add Task
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

        <div class="filter-bar">
            <label>Filter:</label>
            <span class="chip active" data-filter="all">All</span>
            <span class="chip" data-filter="not_started">Not Started</span>
            <span class="chip" data-filter="in_progress">In Progress</span>
            <span class="chip" data-filter="completed">Completed</span>
            <select id="priorityFilter" class="filter-select">
                <option value="all">All Priority</option>
                <option value="high">High</option>
                <option value="medium">Medium</option>
                <option value="low">Low</option>
            </select>
        </div>

        <div class="task-grid" id="taskList">
            <?php if (empty($tasks)): ?>
                <div class="empty-state full-width" id="emptyState">
                    <img src="../assets/clipboard.svg" alt="" class="empty-icon">
                    <h3>No tasks yet</h3>
                    <p>Click "Add Task" to create your first task.</p>
                </div>
            <?php else: ?>
                <div id="emptyState" style="display:none" class="empty-state full-width">
                    <img src="../assets/search.svg" alt="" class="empty-icon">
                    <h3>No tasks match this filter</h3>
                </div>
                <?php foreach ($tasks as $task):
                    $catId    = $task['category_id'] ? (int)$task['category_id'] : null;
                    $cat      = $catId ? getCategoryById($catId, $categories) : null;
                    $priority = $task['priority'] ?? 'low';
                    $vital    = isVital($task);
                    $dl       = $task['deadline'] ?? '';
                    $updatedAt = $task['updated_at'] ?? '';
                    $dataDeadline = htmlspecialchars($dl);
                    $dataCatName  = $cat ? htmlspecialchars($cat['name']) : '';
                    $dataCatColor = $cat ? htmlspecialchars($cat['color']) : '';
                ?>
                <div class="task-card <?= $vital ? 'vital' : '' ?> <?= htmlspecialchars($task['status']) ?>"
                     data-status="<?= htmlspecialchars($task['status']) ?>"
                     data-priority="<?= htmlspecialchars($priority) ?>"
                     data-id="<?= htmlspecialchars($task['id']) ?>"
                     data-title="<?= htmlspecialchars($task['title']) ?>"
                     data-desc="<?= htmlspecialchars($task['description']) ?>"
                     data-category="<?= htmlspecialchars($task['category_id']) ?>"
                     data-deadline="<?= $dataDeadline ?>"
                     data-cat-name="<?= $dataCatName ?>"
                     data-cat-color="<?= $dataCatColor ?>"
                     data-created="<?= htmlspecialchars($task['created_at']) ?>"
                     data-updated="<?= htmlspecialchars($updatedAt) ?>">

                    <div class="task-card-top">
                        <?php if ($vital): ?><span class="badge badge-vital">Vital</span><?php endif; ?>
                        <span class="badge <?= priorityClass($priority) ?>"><?= priorityLabel($priority) ?></span>
                    </div>

                    <div class="task-card-body">
                        <div class="task-card-title"><?= htmlspecialchars($task['title']) ?></div>
                        <?php if ($task['description']): ?>
                            <div class="task-card-desc"><?= htmlspecialchars($task['description']) ?></div>
                        <?php endif; ?>

                        <div class="task-card-meta">
                            <span class="badge <?= statusClass($task['status']) ?>"><?= statusLabel($task['status']) ?></span>
                            <?php if ($cat): ?>
                                <span class="category-dot" style="color:<?= htmlspecialchars($cat['color']) ?>">
                                    <?= htmlspecialchars($cat['name']) ?>
                                </span>
                            <?php endif; ?>
                        </div>

                        <div class="task-card-footer">
                            <?php if (!empty($dl)): ?>
                                <div class="task-deadline <?= isVital($task) ? 'urgent' : '' ?>">
                                    <img src="../assets/clock.svg" alt="clock" class="vital-icon"><?= htmlspecialchars(deadlineLabel($dl)) ?>
                                </div>
                            <?php endif; ?>
                            <span class="task-date">
                                <?php if (!empty($updatedAt)): ?>
                                    edited <?= timeAgo($updatedAt) ?>
                                <?php else: ?>
                                    <?= timeAgo($task['created_at']) ?>
                                <?php endif; ?>
                            </span>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

    </main>
</div>

<div class="modal-overlay" id="previewModal">
    <div class="modal modal-preview" onclick="event.stopPropagation()">
        <div class="modal-header">
            <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
                <span id="pv_vital_badge" style="display:none" class="badge badge-vital"><img src="../assets/fire.svg" alt="vital" class="vital-icon"> Vital</span>
                <span id="pv_priority_badge" class="badge"></span>
                <h2 id="pv_title" style="font-size:17px;"></h2>
            </div>
            <button class="modal-close" id="closePreviewModal">✕</button>
        </div>

        <div id="pv_desc" class="pv-desc" style="display:none;"></div>

        <div class="pv-meta-grid">
            <div class="pv-meta-item">
                <span class="pv-label">Status</span>
                <span id="pv_status" class="badge"></span>
            </div>
            <div class="pv-meta-item">
                <span class="pv-label">Priority</span>
                <span id="pv_priority_text"></span>
            </div>
            <div class="pv-meta-item" id="pv_cat_row" style="display:none;">
                <span class="pv-label">Category</span>
                <span id="pv_category" class="category-dot"></span>
            </div>
            <div class="pv-meta-item" id="pv_dl_row" style="display:none;">
                <span class="pv-label">Deadline</span>
                <span id="pv_deadline"></span>
            </div>
            <div class="pv-meta-item">
                <span class="pv-label">Created</span>
                <span id="pv_created"></span>
            </div>
            <div class="pv-meta-item" id="pv_updated_row" style="display:none;">
                <span class="pv-label">Last edited</span>
                <span id="pv_updated"></span>
            </div>
        </div>

        <form method="POST" action="my-task.php" id="pv_status_form" style="margin-top:16px;">
            <input type="hidden" name="action"  value="update_status">
            <input type="hidden" name="task_id" id="pv_status_task_id">
            <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
                <label style="font-size:13px;font-weight:600;color:var(--text-muted);">Update Status:</label>
                <select name="status" id="pv_status_select" class="status-select" onchange="this.form.submit()">
                    <option value="not_started">Not Started</option>
                    <option value="in_progress">In Progress</option>
                    <option value="completed">Completed</option>
                </select>
            </div>
        </form>

        <div class="form-actions" style="margin-top:20px;">
            <form method="POST" action="my-task.php" id="pv_delete_form" style="display:inline">
                <input type="hidden" name="action"  value="delete">
                <input type="hidden" name="task_id" id="pv_delete_id">
                <button type="button" class="btn btn-danger" id="pv_delete_btn">
                    <img src="../assets/trash.svg" width="16" height="16" alt=""> Delete
                </button>
            </form>
            <button class="btn btn-ghost" id="pv_edit_btn">
                <img src="../assets/edit.svg" width="16" height="16" alt=""> Edit
            </button>
        </div>
    </div>
</div>

<div class="modal-overlay" id="taskModal">
    <div class="modal">
        <div class="modal-header">
            <h2>Add New Task</h2>
        </div>
        <form method="POST" action="my-task.php">
            <input type="hidden" name="action" value="add">

            <div class="form-group">
                <label for="add_title">Task Title *</label>
                <input type="text" id="add_title" name="title" placeholder="Enter task title..." required>
            </div>

            <div class="form-group">
                <label for="add_desc">Description</label>
                <textarea id="add_desc" name="desc" placeholder="Optional description..."></textarea>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="add_priority">Priority</label>
                    <select id="add_priority" name="priority">
                        <option value="low">Low</option>
                        <option value="medium" selected>Medium</option>
                        <option value="high">High</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="add_status">Status</label>
                    <select id="add_status" name="status">
                        <option value="not_started">Not Started</option>
                        <option value="in_progress">In Progress</option>
                        <option value="completed">Completed</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="add_category">Category</label>
                <select id="add_category" name="category_id">
                    <option value="">— No category —</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= htmlspecialchars($cat['id']) ?>">
                            <?= htmlspecialchars($cat['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="add_deadline">Deadline (optional)</label>
                <input type="datetime-local" id="add_deadline" name="deadline" min="<?= $minDeadline ?>">
            </div>

            <div class="form-actions">
                <button type="button" class="btn btn-ghost" id="cancelAddTask">Cancel</button>
                <button type="submit" class="btn btn-primary">Add Task</button>
            </div>
        </form>
    </div>
</div>

<div class="modal-overlay" id="editModal">
    <div class="modal">
        <div class="modal-header">
            <h2>Edit Task</h2>
            <button class="modal-close" id="closeEditModal">✕</button>
        </div>
        <form method="POST" action="my-task.php">
            <input type="hidden" name="action"  value="edit">
            <input type="hidden" name="task_id" id="edit_id">

            <div class="form-group">
                <label for="edit_title">Task Title *</label>
                <input type="text" id="edit_title" name="title" required>
            </div>

            <div class="form-group">
                <label for="edit_desc">Description</label>
                <textarea id="edit_desc" name="desc"></textarea>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="edit_priority">Priority</label>
                    <select id="edit_priority" name="priority">
                        <option value="low">Low</option>
                        <option value="medium">Medium</option>
                        <option value="high">High</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="edit_status">Status</label>
                    <select id="edit_status" name="status">
                        <option value="not_started">Not Started</option>
                        <option value="in_progress">In Progress</option>
                        <option value="completed">Completed</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="edit_category">Category</label>
                <select id="edit_category" name="category_id">
                    <option value="">— No category —</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= htmlspecialchars($cat['id']) ?>">
                            <?= htmlspecialchars($cat['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="edit_deadline">Deadline (optional)</label>
                <input type="datetime-local" id="edit_deadline" name="deadline" min="<?= $minDeadline ?>">
            </div>

            <div class="form-actions">
                <button type="button" class="btn btn-ghost" id="cancelEditTask">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script src="../js/tasks.js"></script>
</body>
</html>

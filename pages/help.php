<?php
session_start();
if (!isset($_SESSION['user'])) { header('Location: ../login.php'); exit; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<?php $pageTitle = 'Help'; include '../includes/head.php'; ?>
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
                <h1>Help Center</h1>
                <p>Learn how to use Taskly and get answers to common questions.</p>
            </div>
        </div>

        <div class="help-grid">

            <div class="help-card">
                <img src="../assets/my-task.svg" alt="" class="help-icon-svg">
                <h3>My Task</h3>
                <p>Add, edit, and manage all your tasks in one place. Set a title, description, category, and status. Use the status dropdown on each card to quickly update progress.</p>
            </div>

            <div class="help-card">
                <img src="../assets/vital-task.svg" alt="" class="help-icon-svg">
                <h3>Vital Task</h3>
                <p>Tasks with <strong>High</strong> priority and a deadline within 48 hours are automatically shown here. Keep an eye on them so nothing important slips through.</p>
            </div>

            <div class="help-card">
                <img src="../assets/category.svg" alt="" class="help-icon-svg">
                <h3>Task Categories</h3>
                <p>Create custom categories (e.g. Work, Personal, Study) with your own colour. When adding a task, pick a category to keep things organised and visually distinct.</p>
            </div>

            <div class="help-card">
                <img src="../assets/homepage.svg" alt="" class="help-icon-svg">
                <h3>Dashboard</h3>
                <p>The homepage shows your upcoming to-dos, real-time task status percentages, and recently completed tasks — all pulled live from your task data.</p>
            </div>

            <div class="help-card">
                <img src="../assets/profile.svg" alt="" class="help-icon-svg">
                <h3>Profile &amp; Settings</h3>
                <p>Update your name, email, contact, and position anytime. You can also upload a profile photo (JPG/PNG, max 2 MB) which will show in the navbar.</p>
            </div>

            <div class="help-card">
                <img src="../assets/password.svg" alt="" class="help-icon-svg">
                <h3>Security</h3>
                <p>Change your password from the Profile page. Your session is protected — you'll be redirected to login if not authenticated. Logout from the sidebar at any time.</p>
            </div>

        </div>

        <h2 style="margin-top:36px; margin-bottom:4px; font-size:18px;">Frequently Asked Questions</h2>
        <p style="font-size:13px; color:var(--text-muted); margin-bottom:16px;">Click a question to expand the answer.</p>

        <div class="faq-list">

            <div class="faq-item">
                <button class="faq-question">
                    How do I add a new task?
                    <i class="faq-chevron">▾</i>
                </button>
                <div class="faq-answer">
                    Go to <strong>My Task</strong> from the sidebar and click <strong>+ Add Task</strong>.
                    Fill in the title (required), description, category, priority level, status, and optionally a deadline date &amp; time.
                    Click <em>Add Task</em> — the card will appear in the grid immediately.
                </div>
            </div>

            <div class="faq-item">
                <button class="faq-question">
                    How do I view, edit, or delete a task?
                    <i class="faq-chevron">▾</i>
                </button>
                <div class="faq-answer">
                    Click any task card to open a <strong>Preview</strong> panel showing full details.
                    Inside the preview you can: change the status via the dropdown, click <strong>Edit</strong> to modify the task, or click <strong>Delete</strong> to remove it permanently.
                    Close the preview with the <strong>✕</strong> button or by clicking outside the panel.
                </div>
            </div>

            <div class="faq-item">
                <button class="faq-question">
                    What is a Vital Task and how does it work?
                    <i class="faq-chevron">▾</i>
                </button>
                <div class="faq-answer">
                    A task is automatically flagged as <strong>Vital</strong> when it meets <em>both</em> conditions:
                    <ul style="margin:8px 0 0 18px; line-height:1.8; font-size:13px;">
                        <li>Priority is set to <strong>High</strong></li>
                        <li>Deadline is within the next <strong>48 hours</strong></li>
                    </ul>
                    You don't need to do anything manually — vital tasks appear on the <strong>Vital Task</strong> page automatically and are marked with a 🔥 badge on the card.
                </div>
            </div>

            <div class="faq-item">
                <button class="faq-question">
                    How do I set a deadline for a task?
                    <i class="faq-chevron">▾</i>
                </button>
                <div class="faq-answer">
                    When adding or editing a task, use the <strong>Deadline</strong> date &amp; time picker at the bottom of the form.
                    Once set, the card will show a countdown such as <em>"Due in 3 hr"</em>, <em>"Due tomorrow"</em>, or <em>"Overdue"</em>.
                    The countdown updates automatically in real-time — no page refresh needed.
                </div>
            </div>

            <div class="faq-item">
                <button class="faq-question">
                    How do I filter or search my tasks?
                    <i class="faq-chevron">▾</i>
                </button>
                <div class="faq-answer">
                    Use the <strong>filter bar</strong> on the My Task page to narrow tasks by status (All / Not Started / In Progress / Completed) and by <strong>priority</strong> via the dropdown.
                    You can also type in the <strong>search bar</strong> at the top of the page — it filters cards live by title, description, or category name. All filters work together simultaneously.
                </div>
            </div>

            <div class="faq-item">
                <button class="faq-question">
                    Can I delete a category that has tasks?
                    <i class="faq-chevron">▾</i>
                </button>
                <div class="faq-answer">
                    Yes. Deleting a category only removes the label — your tasks are <strong>not deleted</strong>.
                    Tasks that belonged to the deleted category will simply show no category until you assign a new one via the Edit modal.
                </div>
            </div>

            <div class="faq-item">
                <button class="faq-question">
                    Is my data saved between sessions?
                    <i class="faq-chevron">▾</i>
                </button>
                <div class="faq-answer">
                    Yes. Tasks, categories, and profile information are stored in JSON files on the server and persist between logins.
                    If you enable <em>Remember Me</em> on the login page, you will be logged in automatically on your next visit.
                </div>
            </div>

        </div>

    </main>
</div>

<script src="../js/tasks.js"></script>
<script>
document.querySelectorAll('.faq-question').forEach(function (btn) {
    btn.addEventListener('click', function () {
        var answer = btn.nextElementSibling;
        var isOpen = btn.classList.contains('open');
        document.querySelectorAll('.faq-question').forEach(function (b) {
            b.classList.remove('open');
            var a = b.nextElementSibling;
            if (a && a.classList.contains('faq-answer')) a.classList.remove('open');
        });

        if (!isOpen && answer) {
            btn.classList.add('open');
            answer.classList.add('open');
        }
    });
});
</script>
</body>
</html>

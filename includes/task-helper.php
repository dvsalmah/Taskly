<?php
function loadTasks(string $username): array {
    global $conn;
    $stmt = $conn->prepare(
        "SELECT t.*, c.name AS cat_name, c.color AS cat_color
         FROM tasks t
         LEFT JOIN categories c ON t.category_id = c.id
         WHERE t.username = ?
         ORDER BY t.created_at DESC"
    );
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $tasks  = [];
    while ($row = $result->fetch_assoc()) $tasks[] = $row;
    $stmt->close();
    return $tasks;
}

function addTask(string $username, string $title, string $description,
                 ?int $category_id, string $priority, string $status,
                 string $deadline = ''): int {
    global $conn;
    $dl    = parseDeadline($deadline);
    $now   = date('Y-m-d H:i:s');
    $catId = $category_id ?: null;

    $stmt = $conn->prepare(
        "INSERT INTO tasks (username, title, description, category_id, priority, status, deadline, created_at, updated_at)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );
    $stmt->bind_param("sssisssss", $username, $title, $description, $catId, $priority, $status, $dl, $now, $now);
    $stmt->execute();
    $id = $conn->insert_id;
    $stmt->close();
    return $id;
}

function updateTask(int $id, array $fields): bool {
    global $conn;
    if (empty($fields)) return false;

    if (isset($fields['deadline'])) {
        $fields['deadline'] = parseDeadline($fields['deadline']);
    }

    $setClauses = [];
    $types      = '';
    $values     = [];

    foreach ($fields as $col => $val) {
        $allowed = ['title','description','category_id','priority','status','deadline'];
        if (!in_array($col, $allowed)) continue;
        $setClauses[] = "`$col` = ?";
        if ($col === 'category_id') {
            $types .= 'i';
            $values[] = $val ?: null;
        } else {
            $types .= 's';
            $values[] = $val;
        }
    }
    if (empty($setClauses)) return false;

    $setClauses[] = "updated_at = NOW()";
    $types       .= 'i';
    $values[]     = $id;

    $sql  = "UPDATE tasks SET " . implode(', ', $setClauses) . " WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$values);
    $ok = $stmt->execute();
    $stmt->close();
    return $ok;
}

function deleteTask(int $id, string $username): bool {
    global $conn;
    $stmt = $conn->prepare("DELETE FROM tasks WHERE id = ? AND username = ?");
    $stmt->bind_param("is", $id, $username);
    $stmt->execute();
    $ok = $stmt->affected_rows > 0;
    $stmt->close();
    return $ok;
}

function loadCategories(string $username): array {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM categories WHERE username = ? ORDER BY name ASC");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $cats   = [];
    while ($row = $result->fetch_assoc()) $cats[] = $row;
    $stmt->close();
    return $cats;
}

function addCategory(string $username, string $name, string $color): array {
    global $conn;
    $stmt = $conn->prepare("INSERT INTO categories (username, name, color) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $username, $name, $color);
    $stmt->execute();
    $id = $conn->insert_id;
    $stmt->close();
    return ['id' => $id, 'username' => $username, 'name' => $name, 'color' => $color];
}

function deleteCategory(int $id, string $username): bool {
    global $conn;
    $nullStmt = $conn->prepare("UPDATE tasks SET category_id = NULL WHERE category_id = ? AND username = ?");
    $nullStmt->bind_param("is", $id, $username);
    $nullStmt->execute();
    $nullStmt->close();

    $stmt = $conn->prepare("DELETE FROM categories WHERE id = ? AND username = ?");
    $stmt->bind_param("is", $id, $username);
    $stmt->execute();
    $ok = $stmt->affected_rows > 0;
    $stmt->close();
    return $ok;
}

function getCategoryById(?int $id, array $categories): ?array {
    if (!$id) return null;
    foreach ($categories as $c) {
        if ((int)$c['id'] === $id) return $c;
    }
    return null;
}

function parseDeadline(string $raw): ?string {
    $raw = trim($raw);
    if ($raw === '') return null;
    $ts = strtotime(str_replace('T', ' ', $raw));
    return ($ts !== false && $ts !== -1) ? date('Y-m-d H:i:s', $ts) : null;
}

function isVital(array $task): bool {
    if (($task['priority'] ?? '') !== 'high') return false;
    $raw = trim($task['deadline'] ?? '');
    if ($raw === '') return false;
    $ts   = strtotime($raw);
    if (!$ts || $ts === -1) return false;
    $diff = $ts - time();
    return $diff <= 172800 && $diff > -86400;
}

function statusLabel(string $status): string {
    return match($status) {
        'completed'   => 'Completed',
        'in_progress' => 'In Progress',
        default       => 'Not Started',
    };
}

function statusClass(string $status): string {
    return match($status) {
        'completed'   => 'badge-completed',
        'in_progress' => 'badge-progress',
        default       => 'badge-notstarted',
    };
}

function priorityLabel(string $p): string {
    return match($p) {
        'high'   => 'High',
        'medium' => 'Medium',
        default  => 'Low',
    };
}

function priorityClass(string $p): string {
    return match($p) {
        'high'   => 'badge-priority-high',
        'medium' => 'badge-priority-medium',
        default  => 'badge-priority-low',
    };
}

function timeAgo(string $datetime): string {
    if (empty($datetime)) return '';
    $diff = time() - strtotime($datetime);
    if ($diff < 60)     return 'just now';
    if ($diff < 3600)   return floor($diff / 60) . ' min ago';
    if ($diff < 86400)  return floor($diff / 3600) . ' hr ago';
    if ($diff < 604800) return floor($diff / 86400) . ' days ago';
    return date('d M Y', strtotime($datetime));
}

function deadlineLabel(string $deadline): string {
    if (empty($deadline)) return '';
    $ts = strtotime($deadline);
    if (!$ts || $ts === -1) return '';
    $diff = $ts - time();
    if ($diff < -86400)  return 'Overdue';
    if ($diff < 0)       return 'Due today (overdue)';
    if ($diff < 3600)    return 'Due in ' . ceil($diff / 60) . ' min';
    if ($diff < 86400)   return 'Due in ' . ceil($diff / 3600) . ' hr';
    if ($diff < 172800)  return 'Due tomorrow';
    return 'Due ' . date('d M Y, H:i', $ts);
}

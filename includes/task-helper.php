<?php

function getDataDir() {
    return __DIR__ . '/../data/';
}

function generateId() {
    return bin2hex(random_bytes(8));
}

function loadAllTasks() {
    $file = getDataDir() . 'tasks.json';
    if (!file_exists($file)) return [];
    return json_decode(file_get_contents($file), true) ?? [];
}

function loadTasks(string $username): array {
    $all = loadAllTasks();
    return array_values(array_filter($all, fn($t) => ($t['username'] ?? '') === $username));
}

function saveAllTasks(array $tasks): void {
    $dir = getDataDir();
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    file_put_contents($dir . 'tasks.json', json_encode(array_values($tasks), JSON_PRETTY_PRINT));
}

function addTask(string $username, string $title, string $description,
                 string $category_id, string $priority, string $status,
                 string $deadline = ''): array {
    $all  = loadAllTasks();
    $task = [
        'id'          => generateId(),
        'username'    => $username,
        'title'       => $title,
        'description' => $description,
        'category_id' => $category_id,
        'priority'    => $priority,   
        'vital'       => false,       
        'status'      => $status,
        'deadline'    => $deadline,  
        'created_at'  => date('Y-m-d H:i:s'),
        'updated_at'  => '',
    ];
    $all[] = $task;
    saveAllTasks($all);
    return $task;
}

function updateTask(string $id, array $fields): bool {
    $all = loadAllTasks();
    foreach ($all as &$t) {
        if ($t['id'] === $id) {
            foreach ($fields as $k => $v) $t[$k] = $v;
            $t['updated_at'] = date('Y-m-d H:i:s');
            saveAllTasks($all);
            return true;
        }
    }
    return false;
}

function deleteTask(string $id, string $username): bool {
    $all = loadAllTasks();
    $new = array_filter($all, fn($t) => !($t['id'] === $id && $t['username'] === $username));
    if (count($new) === count($all)) return false;
    saveAllTasks($new);
    return true;
}

function loadAllCategories(): array {
    $file = getDataDir() . 'categories.json';
    if (!file_exists($file)) return [];
    return json_decode(file_get_contents($file), true) ?? [];
}

function loadCategories(string $username): array {
    $all = loadAllCategories();
    return array_values(array_filter($all, fn($c) => ($c['username'] ?? '') === $username));
}

function saveAllCategories(array $cats): void {
    $dir = getDataDir();
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    file_put_contents($dir . 'categories.json', json_encode(array_values($cats), JSON_PRETTY_PRINT));
}

function addCategory(string $username, string $name, string $color): array {
    $all = loadAllCategories();
    $cat = [
        'id'       => generateId(),
        'username' => $username,
        'name'     => $name,
        'color'    => $color,
    ];
    $all[] = $cat;
    saveAllCategories($all);
    return $cat;
}

function deleteCategory(string $id, string $username): bool {
    $all = loadAllCategories();
    $new = array_filter($all, fn($c) => !($c['id'] === $id && $c['username'] === $username));
    if (count($new) === count($all)) return false;
    saveAllCategories($new);
    return true;
}

function getCategoryById(string $id, array $categories): ?array {
    foreach ($categories as $c) {
        if ($c['id'] === $id) return $c;
    }
    return null;
}

function isVital(array $task): bool {
    if (($task['priority'] ?? '') !== 'high') return false;
    $raw = trim($task['deadline'] ?? '');
    if ($raw === '') return false;
    $dl   = str_replace('T', ' ', $raw);
    $dlTs = strtotime($dl);
    if ($dlTs === false || $dlTs === -1) return false;
    $diff = $dlTs - time();         
    return $diff <= 172800;         
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
    $ts   = strtotime(str_replace('T', ' ', $deadline));
    if (!$ts || $ts === -1) return '';
    $diff = $ts - time();
    if ($diff < 0)       return 'Overdue';
    if ($diff < 3600)    return 'Due in ' . ceil($diff / 60) . ' min';
    if ($diff < 86400)   return 'Due in ' . ceil($diff / 3600) . ' hr';
    if ($diff < 172800)  return 'Due tomorrow';
    return 'Due ' . date('d M Y, H:i', $ts);
}

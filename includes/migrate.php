<?php
/**
 * migrate.php — Jalankan SEKALI untuk migrasi data JSON ke MySQL.
 * Setelah selesai, hapus file ini.
 */
require_once __DIR__ . '/connect.php';

$dataDir = __DIR__ . '/../pages/data/';

echo "<pre>";

// ───────────────────────── USERS ─────────────────────────
$usersFile = $dataDir . 'users.json';
if (file_exists($usersFile)) {
    $users = json_decode(file_get_contents($usersFile), true) ?? [];
    $inserted = 0; $skipped = 0;
    foreach ($users as $u) {
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->bind_param("s", $u['username']);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) { $stmt->close(); $skipped++; continue; }
        $stmt->close();

        $fn   = $u['first_name'] ?? '';
        $ln   = $u['last_name']  ?? '';
        $un   = $u['username']   ?? '';
        $em   = $u['email']      ?? '';
        $pw   = $u['password']   ?? '';
        $ct   = $u['contact']    ?? '';
        $pos  = $u['position']   ?? '';
        $ph   = $u['photo']      ?? '';

        $ins = $conn->prepare(
            "INSERT INTO users (first_name, last_name, username, email, password, contact, position, photo)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $ins->bind_param("ssssssss", $fn, $ln, $un, $em, $pw, $ct, $pos, $ph);
        $ins->execute();
        $ins->close();
        $inserted++;
    }
    echo "USERS    — inserted: $inserted, skipped (exists): $skipped\n";
} else {
    echo "USERS    — file tidak ditemukan, skip.\n";
}

// ─────────────────────── CATEGORIES ──────────────────────
// JSON id adalah hex string; di DB id adalah INT AUTO_INCREMENT.
// Kita simpan mapping hex→int untuk dipakai saat migrasi tasks.
$catMap = []; // hex_id => new int id

$catsFile = $dataDir . 'categories.json';
if (file_exists($catsFile)) {
    $cats = json_decode(file_get_contents($catsFile), true) ?? [];
    $inserted = 0; $skipped = 0;
    foreach ($cats as $c) {
        $un   = $c['username'] ?? '';
        $name = $c['name']     ?? '';
        $col  = $c['color']    ?? '#EC003F';
        $hexId = $c['id']      ?? '';

        // Cek duplikat berdasarkan username+name
        $chk = $conn->prepare("SELECT id FROM categories WHERE username = ? AND name = ?");
        $chk->bind_param("ss", $un, $name);
        $chk->execute();
        $chk->bind_result($existId);
        if ($chk->fetch()) { $chk->close(); $catMap[$hexId] = $existId; $skipped++; continue; }
        $chk->close();

        $ins = $conn->prepare("INSERT INTO categories (username, name, color) VALUES (?, ?, ?)");
        $ins->bind_param("sss", $un, $name, $col);
        $ins->execute();
        $newId = $conn->insert_id;
        $ins->close();
        $catMap[$hexId] = $newId;
        $inserted++;
    }
    echo "CATEGORIES — inserted: $inserted, skipped (exists): $skipped\n";
} else {
    echo "CATEGORIES — file tidak ditemukan, skip.\n";
}

// ────────────────────────── TASKS ────────────────────────
$tasksFile = $dataDir . 'tasks.json';
if (file_exists($tasksFile)) {
    $tasks = json_decode(file_get_contents($tasksFile), true) ?? [];
    $inserted = 0; $skipped = 0;
    foreach ($tasks as $t) {
        $un    = $t['username']    ?? '';
        $title = $t['title']       ?? '';

        // category_id: petakan hex → int; NULL jika kosong
        $hexCat = $t['category_id'] ?? '';
        $catId  = ($hexCat !== '' && isset($catMap[$hexCat])) ? $catMap[$hexCat] : null;

        $prio   = $t['priority']    ?? 'medium';
        $status = $t['status']      ?? 'not_started';
        $desc   = $t['description'] ?? '';

        // deadline: konversi dari "2026-05-02T15:59" → datetime atau NULL
        $dlRaw = trim($t['deadline'] ?? '');
        $dl    = '';
        if ($dlRaw !== '') {
            $ts = strtotime(str_replace('T', ' ', $dlRaw));
            $dl = ($ts !== false && $ts !== -1) ? date('Y-m-d H:i:s', $ts) : '';
        }
        $dlParam = $dl !== '' ? $dl : null;

        $createdAt  = $t['created_at'] ?? date('Y-m-d H:i:s');
        $updatedAtR = trim($t['updated_at'] ?? '');
        // Jika updated_at kosong, gunakan created_at sebagai fallback (kolom NOT NULL)
        $updatedAt  = $updatedAtR !== '' ? $updatedAtR : $createdAt;

        // Hindari duplikat: cek username+title+created_at
        $chk = $conn->prepare("SELECT id FROM tasks WHERE username = ? AND title = ? AND created_at = ?");
        $chk->bind_param("sss", $un, $title, $createdAt);
        $chk->execute();
        $chk->store_result();
        if ($chk->num_rows > 0) { $chk->close(); $skipped++; continue; }
        $chk->close();

        $ins = $conn->prepare(
            "INSERT INTO tasks (username, title, description, category_id, priority, status, deadline, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $ins->bind_param("sssisssss", $un, $title, $desc, $catId, $prio, $status, $dlParam, $createdAt, $updatedAt);
        $ins->execute();
        $ins->close();
        $inserted++;
    }
    echo "TASKS    — inserted: $inserted, skipped (exists): $skipped\n";
} else {
    echo "TASKS    — file tidak ditemukan, skip.\n";
}

echo "\nMigrasi selesai! Hapus file ini setelah selesai.\n";
echo "</pre>";

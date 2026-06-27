<?php
require_once __DIR__ . '/config.php';

$method = $_SERVER['REQUEST_METHOD'];
$id     = isset($_GET['id']) ? (int)$_GET['id'] : null;

// ── GET /api/ties.php — fetch all ties (public, used by gallery too)
if ($method === 'GET' && !$id) {
    $stmt = db()->query('SELECT * FROM ties ORDER BY date_added DESC, id DESC');
    json_out($stmt->fetchAll());
}

// ── GET /api/ties.php?id=N — single tie
if ($method === 'GET' && $id) {
    $stmt = db()->prepare('SELECT * FROM ties WHERE id = ?');
    $stmt->execute([$id]);
    $tie = $stmt->fetch();
    if (!$tie) json_out(['error' => 'Not found'], 404);
    json_out($tie);
}

// ── POST /api/ties.php — create tie (owner only)
if ($method === 'POST') {
    auth_required();
    $b = body();
    $stmt = db()->prepare('
        INSERT INTO ties (color, color2, pattern, size, occasion, condition_, notes, loan_status, date_added)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ');
    $stmt->execute([
        $b['color']       ?? '',
        $b['color2']      ?? '',
        $b['pattern']     ?? '',
        $b['size']        ?? '',
        $b['occasion']    ?? '',
        $b['condition_']  ?? '',
        $b['notes']       ?? '',
        $b['loan_status'] ?? 'in',
        $b['date_added']  ?? date('Y-m-d'),
    ]);
    $newId = db()->lastInsertId();
    $stmt2 = db()->prepare('SELECT * FROM ties WHERE id = ?');
    $stmt2->execute([$newId]);
    json_out($stmt2->fetch(), 201);
}

// ── PUT /api/ties.php?id=N — update tie (owner only)
if ($method === 'PUT' && $id) {
    auth_required();
    $b = body();
    $stmt = db()->prepare('
        UPDATE ties SET
            color       = ?,
            color2      = ?,
            pattern     = ?,
            size        = ?,
            occasion    = ?,
            condition_  = ?,
            notes       = ?,
            loan_status = ?
        WHERE id = ?
    ');
    $stmt->execute([
        $b['color']       ?? '',
        $b['color2']      ?? '',
        $b['pattern']     ?? '',
        $b['size']        ?? '',
        $b['occasion']    ?? '',
        $b['condition_']  ?? '',
        $b['notes']       ?? '',
        $b['loan_status'] ?? 'in',
        $id,
    ]);
    $stmt2 = db()->prepare('SELECT * FROM ties WHERE id = ?');
    $stmt2->execute([$id]);
    json_out($stmt2->fetch());
}

// ── DELETE /api/ties.php?id=N — delete tie (owner only)
if ($method === 'DELETE' && $id) {
    auth_required();
    // Delete photo file if exists
    $stmt = db()->prepare('SELECT photo FROM ties WHERE id = ?');
    $stmt->execute([$id]);
    $tie = $stmt->fetch();
    if ($tie && $tie['photo']) {
        $photoPath = __DIR__ . '/../uploads/' . basename($tie['photo']);
        if (file_exists($photoPath)) unlink($photoPath);
    }
    db()->prepare('DELETE FROM ties WHERE id = ?')->execute([$id]);
    json_out(['ok' => true]);
}

json_out(['error' => 'Not found'], 404);

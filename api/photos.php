<?php
require_once __DIR__ . '/config.php';

$method = $_SERVER['REQUEST_METHOD'];
$tieId  = isset($_GET['tie_id']) ? (int)$_GET['tie_id'] : null;

// ── POST /api/photos.php?tie_id=N — upload photo for a tie
if ($method === 'POST' && $tieId) {
    auth_required();

    if (empty($_FILES['photo'])) {
        json_out(['error' => 'No file uploaded'], 400);
    }

    $file    = $_FILES['photo'];
    $allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
    $mime    = mime_content_type($file['tmp_name']);

    if (!in_array($mime, $allowed)) {
        json_out(['error' => 'Invalid file type'], 400);
    }

    // Max 5MB
    if ($file['size'] > 5 * 1024 * 1024) {
        json_out(['error' => 'File too large (max 5MB)'], 400);
    }

    $ext      = match($mime) {
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
        'image/gif'  => 'gif',
        default      => 'jpg'
    };
    $filename = 'tie_' . $tieId . '_' . time() . '.' . $ext;
    $uploadDir = __DIR__ . '/../uploads/';

    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

    // Delete old photo if exists
    $stmt = db()->prepare('SELECT photo FROM ties WHERE id = ?');
    $stmt->execute([$tieId]);
    $tie = $stmt->fetch();
    if ($tie && $tie['photo']) {
        $old = $uploadDir . basename($tie['photo']);
        if (file_exists($old)) unlink($old);
    }

    if (!move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) {
        json_out(['error' => 'Failed to save file'], 500);
    }

    // Save path to DB
    $photoUrl = APP_URL . '/uploads/' . $filename;
    db()->prepare('UPDATE ties SET photo = ? WHERE id = ?')->execute([$photoUrl, $tieId]);

    json_out(['ok' => true, 'photo' => $photoUrl]);
}

// ── DELETE /api/photos.php?tie_id=N — remove photo
if ($method === 'DELETE' && $tieId) {
    auth_required();
    $stmt = db()->prepare('SELECT photo FROM ties WHERE id = ?');
    $stmt->execute([$tieId]);
    $tie = $stmt->fetch();
    if ($tie && $tie['photo']) {
        $path = __DIR__ . '/../uploads/' . basename($tie['photo']);
        if (file_exists($path)) unlink($path);
        db()->prepare('UPDATE ties SET photo = "" WHERE id = ?')->execute([$tieId]);
    }
    json_out(['ok' => true]);
}

json_out(['error' => 'Not found'], 404);

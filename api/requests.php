<?php
require_once __DIR__ . '/config.php';

$method = $_SERVER['REQUEST_METHOD'];
$id     = isset($_GET['id']) ? (int)$_GET['id'] : null;

// ── GET /api/requests.php — all pending requests (owner only)
if ($method === 'GET' && !$id) {
    auth_required();
    $status = $_GET['status'] ?? 'pending';
    $stmt = db()->prepare('
        SELECT r.*, t.color, t.color2, t.pattern, t.size, t.occasion, t.photo
        FROM requests r
        JOIN ties t ON t.id = r.tie_id
        WHERE r.status = ?
        ORDER BY r.created_at DESC
    ');
    $stmt->execute([$status]);
    json_out($stmt->fetchAll());
}

// ── GET /api/requests.php?action=count — unread count for badge
if ($method === 'GET' && isset($_GET['action']) && $_GET['action'] === 'count') {
    auth_required();
    $stmt = db()->query('SELECT COUNT(*) as count FROM requests WHERE status = "pending"');
    json_out($stmt->fetch());
}

// ── POST /api/requests.php — friend submits a borrow request (public)
if ($method === 'POST' && !$id) {
    $b = body();
    $tieId = (int)($b['tie_id'] ?? 0);
    if (!$tieId) json_out(['error' => 'tie_id required'], 400);
    if (empty($b['requester_name']))  json_out(['error' => 'Name required'], 400);
    if (empty($b['requester_phone'])) json_out(['error' => 'Phone required'], 400);

    // Check tie is available
    $stmt = db()->prepare('SELECT loan_status FROM ties WHERE id = ?');
    $stmt->execute([$tieId]);
    $tie = $stmt->fetch();
    if (!$tie) json_out(['error' => 'Tie not found'], 404);
    if ($tie['loan_status'] === 'out') json_out(['error' => 'Tie is currently on loan'], 409);

    // Check no active pending request for this tie
    $stmt2 = db()->prepare('SELECT id FROM requests WHERE tie_id = ? AND status = "pending"');
    $stmt2->execute([$tieId]);
    if ($stmt2->fetch()) json_out(['error' => 'A request is already pending for this tie'], 409);

    db()->prepare('
        INSERT INTO requests (tie_id, requester_name, requester_phone, note)
        VALUES (?, ?, ?, ?)
    ')->execute([
        $tieId,
        $b['requester_name'],
        $b['requester_phone'],
        $b['note'] ?? '',
    ]);

    json_out(['ok' => true, 'message' => 'Request sent! Jeremy will be in touch soon.'], 201);
}

// ── PUT /api/requests.php?id=N — approve or decline (owner only)
if ($method === 'PUT' && $id) {
    auth_required();
    $b      = body();
    $action = $b['action'] ?? ''; // 'approve' or 'decline'

    if (!in_array($action, ['approve', 'decline'])) {
        json_out(['error' => 'action must be approve or decline'], 400);
    }

    $stmt = db()->prepare('SELECT * FROM requests WHERE id = ?');
    $stmt->execute([$id]);
    $req = $stmt->fetch();
    if (!$req) json_out(['error' => 'Request not found'], 404);

    if ($action === 'approve') {
        // Create a loan record
        db()->prepare('
            INSERT INTO loans (tie_id, borrower_name, borrower_phone, date_borrowed, status)
            VALUES (?, ?, ?, ?, "active")
        ')->execute([
            $req['tie_id'],
            $req['requester_name'],
            $req['requester_phone'],
            date('Y-m-d'),
        ]);
        // Mark tie as out
        db()->prepare('UPDATE ties SET loan_status = "out" WHERE id = ?')->execute([$req['tie_id']]);
        // Update request status
        db()->prepare('UPDATE requests SET status = "approved" WHERE id = ?')->execute([$id]);
        // Decline any other pending requests for the same tie
        db()->prepare('UPDATE requests SET status = "declined" WHERE tie_id = ? AND id != ? AND status = "pending"')
            ->execute([$req['tie_id'], $id]);
    } else {
        db()->prepare('UPDATE requests SET status = "declined" WHERE id = ?')->execute([$id]);
    }

    json_out(['ok' => true]);
}

json_out(['error' => 'Not found'], 404);

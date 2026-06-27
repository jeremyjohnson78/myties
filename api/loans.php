<?php
require_once __DIR__ . '/config.php';

$method = $_SERVER['REQUEST_METHOD'];
$tieId  = isset($_GET['tie_id']) ? (int)$_GET['tie_id'] : null;
$loanId = isset($_GET['id'])     ? (int)$_GET['id']     : null;

// ── GET /api/loans.php — all active loans
if ($method === 'GET' && !$tieId && !$loanId) {
    auth_required();
    $stmt = db()->query('
        SELECT l.*, t.color, t.color2, t.pattern, t.size
        FROM loans l
        JOIN ties t ON t.id = l.tie_id
        ORDER BY l.date_borrowed DESC
    ');
    json_out($stmt->fetchAll());
}

// ── POST /api/loans.php — lend a tie out
if ($method === 'POST') {
    auth_required();
    $b = body();
    $tId = (int)($b['tie_id'] ?? 0);
    if (!$tId) json_out(['error' => 'tie_id required'], 400);
    if (empty($b['borrower_name'])) json_out(['error' => 'Name required'], 400);

    // Create loan record
    $stmt = db()->prepare('
        INSERT INTO loans (tie_id, borrower_name, borrower_phone, date_borrowed, status)
        VALUES (?, ?, ?, ?, "active")
    ');
    $stmt->execute([
        $tId,
        $b['borrower_name'],
        $b['borrower_phone'] ?? '',
        $b['date_borrowed']  ?? date('Y-m-d'),
    ]);

    // Update tie status
    db()->prepare('UPDATE ties SET loan_status = "out" WHERE id = ?')->execute([$tId]);

    json_out(['ok' => true, 'loan_id' => db()->lastInsertId()], 201);
}

// ── PUT /api/loans.php?id=N — mark returned
if ($method === 'PUT' && $loanId) {
    auth_required();
    $stmt = db()->prepare('SELECT tie_id FROM loans WHERE id = ?');
    $stmt->execute([$loanId]);
    $loan = $stmt->fetch();
    if (!$loan) json_out(['error' => 'Loan not found'], 404);

    db()->prepare('UPDATE loans SET status = "returned", date_returned = ? WHERE id = ?')
        ->execute([date('Y-m-d'), $loanId]);
    db()->prepare('UPDATE ties SET loan_status = "in" WHERE id = ?')
        ->execute([$loan['tie_id']]);

    json_out(['ok' => true]);
}

json_out(['error' => 'Not found'], 404);

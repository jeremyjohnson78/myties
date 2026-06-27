<?php
require_once __DIR__ . '/config.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

// ── POST /api/auth.php?action=request_link ──
// Owner submits their email → we send a magic link
if ($method === 'POST' && $action === 'request_link') {
    $body  = body();
    $email = trim($body['email'] ?? '');

    if (strtolower($email) !== strtolower(OWNER_EMAIL)) {
        json_out(['error' => 'Email not recognised'], 403);
    }

    // Generate a secure token
    $token   = bin2hex(random_bytes(32));
    $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

    db()->prepare('INSERT INTO sessions (token, expires_at) VALUES (?, ?)')->execute([$token, $expires]);

    $link    = APP_URL . '/index.html?login=' . $token;
    $subject = 'Your MyTies login link';
    $message = "Hi Jeremy,\n\nClick this link to log in to MyTies (valid for 1 hour):\n\n$link\n\nIf you didn't request this, ignore this email.\n";
    $headers = 'From: noreply@tektwv.my';

    mail(OWNER_EMAIL, $subject, $message, $headers);

    json_out(['ok' => true, 'message' => 'Login link sent to your email']);
}

// ── POST /api/auth.php?action=verify ──
// App exchanges token for a 30-day session
if ($method === 'POST' && $action === 'verify') {
    $body  = body();
    $token = trim($body['token'] ?? '');
    if (!$token) json_out(['error' => 'No token'], 400);

    $stmt = db()->prepare('SELECT token FROM sessions WHERE token = ? AND expires_at > NOW()');
    $stmt->execute([$token]);
    if (!$stmt->fetch()) json_out(['error' => 'Invalid or expired link'], 401);

    // Extend session to 30 days
    $expires = date('Y-m-d H:i:s', strtotime('+' . SESSION_DAYS . ' days'));
    db()->prepare('UPDATE sessions SET expires_at = ? WHERE token = ?')->execute([$expires, $token]);

    json_out(['ok' => true, 'token' => $token]);
}

// ── POST /api/auth.php?action=logout ──
if ($method === 'POST' && $action === 'logout') {
    auth_required();
    $token = str_replace('Bearer ', '', $_SERVER['HTTP_AUTHORIZATION'] ?? '');
    db()->prepare('DELETE FROM sessions WHERE token = ?')->execute([$token]);
    json_out(['ok' => true]);
}

// ── GET /api/auth.php?action=check ──
if ($method === 'GET' && $action === 'check') {
    auth_required();
    json_out(['ok' => true, 'authenticated' => true]);
}

json_out(['error' => 'Not found'], 404);

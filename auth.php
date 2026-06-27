<?php
require_once __DIR__ . '/config.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

// ── POST /api/auth.php?action=login ──
if ($method === 'POST' && $action === 'login') {
    $body = body();
    $user = trim($body['username'] ?? '');
    $pass = trim($body['password'] ?? '');

    if ($user !== OWNER_USERNAME || $pass !== OWNER_PASSWORD) {
        json_out(['error' => 'Wrong username or password'], 401);
    }

    $token   = bin2hex(random_bytes(32));
    $expires = date('Y-m-d H:i:s', strtotime('+' . SESSION_DAYS . ' days'));
    db()->prepare('INSERT INTO sessions (token, expires_at) VALUES (?, ?)')->execute([$token, $expires]);

    // Set cookie as fallback
    setcookie('myties_token', $token, [
        'expires'  => time() + (SESSION_DAYS * 24 * 60 * 60),
        'path'     => '/',
        'secure'   => true,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    json_out(['ok' => true, 'token' => $token]);
}

// ── POST /api/auth.php?action=logout ──
if ($method === 'POST' && $action === 'logout') {
    $token = get_token();
    if ($token) {
        db()->prepare('DELETE FROM sessions WHERE token = ?')->execute([$token]);
    }
    setcookie('myties_token', '', ['expires' => time() - 3600, 'path' => '/', 'secure' => true, 'httponly' => true, 'samesite' => 'Lax']);
    json_out(['ok' => true]);
}

// ── GET /api/auth.php?action=check ──
if ($method === 'GET' && $action === 'check') {
    auth_required();
    json_out(['ok' => true, 'authenticated' => true]);
}

json_out(['error' => 'Unknown action'], 404);

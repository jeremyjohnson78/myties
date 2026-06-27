<?php
// ============================================================
//  MyTies — Configuration
//  Edit the values below then upload to api/config.php
// ============================================================

// ── Login credentials (change these!) ──
define('OWNER_USERNAME', 'jeremy');
define('OWNER_PASSWORD', 'MyTies2026!');

// ── Database ──
define('DB_HOST', 'localhost');
define('DB_NAME', 'tektwvmy_myties');      // e.g. tektwvmy_myties
define('DB_USER', 'tektwvmy_tieuser');      // e.g. tektwvmy_tieuser
define('DB_PASS', 'Palat3Caf3!');  // your DB password

// ── App settings ──
define('SESSION_DAYS', 30);

// ============================================================
//  DO NOT EDIT BELOW THIS LINE
// ============================================================

function db(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $pdo = new PDO(
                'mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8mb4',
                DB_USER, DB_PASS,
                [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]
            );
        } catch (PDOException $e) {
            http_response_code(500);
            die(json_encode(['error' => 'Database connection failed', 'detail' => $e->getMessage()]));
        }
    }
    return $pdo;
}

function json_out(mixed $data, int $code = 200): void {
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    echo json_encode($data);
    exit;
}

function get_token(): string {
    if (!empty($_SERVER['HTTP_AUTHORIZATION'])) {
        return trim(str_replace('Bearer ', '', $_SERVER['HTTP_AUTHORIZATION']));
    }
    if (!empty($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
        return trim(str_replace('Bearer ', '', $_SERVER['REDIRECT_HTTP_AUTHORIZATION']));
    }
    if (function_exists('apache_request_headers')) {
        foreach (apache_request_headers() as $key => $val) {
            if (strtolower($key) === 'authorization') {
                return trim(str_replace('Bearer ', '', $val));
            }
        }
    }
    return $_COOKIE['myties_token'] ?? '';
}

function auth_required(): void {
    $token = get_token();
    if (!$token) json_out(['error' => 'Unauthorized', 'reason' => 'no_token'], 401);
    $stmt = db()->prepare('SELECT token FROM sessions WHERE token = ? AND expires_at > NOW()');
    $stmt->execute([$token]);
    if (!$stmt->fetch()) json_out(['error' => 'Unauthorized', 'reason' => 'invalid_token'], 401);
}

function body(): array {
    return json_decode(file_get_contents('php://input'), true) ?? [];
}

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    http_response_code(204);
    exit;
}

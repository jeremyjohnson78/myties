<?php
// ============================================================
//  MyTies — Database Configuration
//  EDIT THESE 4 VALUES after creating your cPanel database
// ============================================================

define('DB_HOST', 'localhost');
define('DB_NAME', 'YOUR_DB_NAME');       // e.g. tektwv_myties
define('DB_USER', 'YOUR_DB_USER');       // e.g. tektwv_tieuser
define('DB_PASS', 'YOUR_DB_PASSWORD');   // your chosen password

// Owner email — magic link login sent here
define('OWNER_EMAIL', 'jeremyjohnson78@gmail.com');

// App URL
define('APP_URL', 'https://myties.tektwv.my');

// Session duration (days)
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
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]
            );
        } catch (PDOException $e) {
            http_response_code(500);
            die(json_encode(['error' => 'Database connection failed']));
        }
    }
    return $pdo;
}

function json_out(mixed $data, int $code = 200): void {
    http_response_code($code);
    header('Content-Type: application/json');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    echo json_encode($data);
    exit;
}

function auth_required(): void {
    $token = $_COOKIE['myties_token'] ?? ($_SERVER['HTTP_AUTHORIZATION'] ?? '');
    $token = str_replace('Bearer ', '', $token);
    if (!$token) {
        json_out(['error' => 'Unauthorized'], 401);
    }
    $stmt = db()->prepare('SELECT token FROM sessions WHERE token = ? AND expires_at > NOW()');
    $stmt->execute([$token]);
    if (!$stmt->fetch()) {
        json_out(['error' => 'Unauthorized'], 401);
    }
}

function body(): array {
    return json_decode(file_get_contents('php://input'), true) ?? [];
}

// Handle CORS preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    http_response_code(204);
    exit;
}

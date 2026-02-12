<?php
declare(strict_types=1);


error_reporting(E_ALL);
ini_set('display_errors', '0');

require_once __DIR__ . '/../config/csrf.php';
require_csrf();
require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/session.php';

function json_out(int $code, array $payload): void {
    http_response_code($code);
    echo json_encode($payload);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_out(405, ['status' => 'error', 'message' => 'Method not allowed']);
}

try {
    $rawToken = $_COOKIE['deenlink_session'] ?? '';

    if ($rawToken !== '') {
        $tokenHash = hash('sha256', $rawToken);
        try {
            $pdo = DB::conn();
            $pdo->prepare("UPDATE user_sessions SET is_active = 0 WHERE session_token_hash = ?")
                ->execute([$tokenHash]);
        } catch (Throwable $e) {
            error_log("Logout DB error: " . $e->getMessage());
        }
    }

    // Clear PHP session
    $_SESSION = [];
    if (session_id()) {
        session_destroy();
    }

    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');

    // Expire cookies
    setcookie('PHPSESSID', '', [
        'expires'  => time() - 3600,
        'path'     => '/',
        'secure'   => $secure,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);

    setcookie('deenlink_session', '', [
        'expires'  => time() - 3600,
        'path'     => '/',
        'secure'   => $secure,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);

    json_out(200, [
        'status' => 'success',
        'message' => 'Logged out successfully',
        'redirect' => '../index.html'
    ]);

} catch (Throwable $e) {
    error_log("Logout error: " . $e->getMessage());
    json_out(200, [
        'status' => 'success',
        'message' => 'Logged out',
        'redirect' => '../index.html'
    ]);
}
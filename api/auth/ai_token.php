<?php
declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '0');

require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/csrf.php';
require_once __DIR__ . '/../config/jwt.php';

function json_out(int $code, array $payload): void {
    http_response_code($code);
    echo json_encode($payload);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_out(405, ['status' => 'error', 'message' => 'Method not allowed']);
}

// CSRF protects this endpoint from being abused from other sites
require_csrf();

if (empty($_SESSION['logged_in']) || empty($_SESSION['user_id'])) {
    json_out(401, ['status' => 'error', 'message' => 'Not logged in']);
}

$userId = (int)$_SESSION['user_id'];

try {
    $pdo = DB::conn();
    $stmt = $pdo->prepare("SELECT id, username, user_type, is_active, is_email_verified FROM users WHERE id = ? LIMIT 1");
    $stmt->execute([$userId]);
    $u = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$u) json_out(401, ['status' => 'error', 'message' => 'Not logged in']);
    if ((int)$u['is_active'] !== 1) json_out(403, ['status' => 'error', 'message' => 'Account disabled']);
    if ((int)$u['is_email_verified'] !== 1) json_out(403, ['status' => 'error', 'message' => 'Email not verified']);

    $secret = getenv('AI_JWT_SECRET');
    if (!$secret) {
        // Put this in Apache env vars or .env loader, never hardcode in git
        json_out(500, ['status' => 'error', 'message' => 'AI JWT secret not configured']);
    }

    $now = time();
    $ttl = 10 * 60; // 10 minutes

    $payload = [
        'iss' => 'deenlink',
        'aud' => 'deenlink-ai',
        'sub' => (int)$u['id'],              // DeenLink user_id
        'username' => (string)$u['username'],
        'user_type' => (string)($u['user_type'] ?? 'user'),
        'iat' => $now,
        'exp' => $now + $ttl,
    ];

    $jwt = jwt_sign_hs256($payload, $secret);

    json_out(200, [
        'status' => 'success',
        'token_type' => 'Bearer',
        'expires_in' => $ttl,
        'ai_jwt' => $jwt
    ]);

} catch (Throwable $e) {
    error_log("ai_token error: " . $e->getMessage());
    json_out(500, ['status' => 'error', 'message' => 'Server error']);
}
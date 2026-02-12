<?php
declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '0');

require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/mailer.php';
require_once __DIR__ . '/../config/app.php';

function json_out(int $code, array $payload): void {
    http_response_code($code);
    echo json_encode($payload);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_out(405, ['status' => 'error', 'message' => 'Method not allowed']);
}

$input = file_get_contents('php://input') ?: '';
$data = json_decode($input, true);

$email = trim((string)($data['email'] ?? ''));
if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    json_out(400, ['status' => 'error', 'message' => 'Valid email is required']);
}

try {
    $pdo = DB::conn();

    // Always return success (prevents email enumeration)
    $genericOk = ['status' => 'success', 'message' => 'If the email exists, a reset link has been sent.'];

    $stmt = $pdo->prepare("SELECT id, email, is_active FROM users WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        json_out(200, $genericOk);
    }

    // Optional: block disabled users
    if ((int)$user['is_active'] !== 1) {
        json_out(200, $genericOk);
    }

    // Create token (store hash, email raw)
    $rawToken = bin2hex(random_bytes(32));
    $tokenHash = hash('sha256', $rawToken);
    $expiresAt = date('Y-m-d H:i:s', time() + 60 * 30); // 30 minutes

    $pdo->prepare("
        INSERT INTO password_reset_tokens (user_id, token_hash, expires_at, created_at)
        VALUES (?, ?, ?, NOW())
    ")->execute([(int)$user['id'], $tokenHash, $expiresAt]);

    $resetLink = app_url() . '/profile/reset_password.html?token=' . urlencode($rawToken);

    send_email(
        $user['email'],
        'Reset your password - DeenLink',
        "Assalamu alaikum,<br><br>
        Click this link to reset your password:<br>
        <a href='{$resetLink}'>{$resetLink}</a><br><br>
        This link expires in 30 minutes. If you didn’t request this, ignore this email."
    );

    json_out(200, $genericOk);

} catch (Throwable $e) {
    error_log("request_password_reset error: " . $e->getMessage());
    json_out(500, ['status' => 'error', 'message' => 'Server error']);
}
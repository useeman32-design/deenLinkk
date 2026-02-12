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

    $stmt = $pdo->prepare("SELECT id, email, is_email_verified FROM users WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    // Do not reveal whether email exists (avoid enumeration)
    if (!$user) {
        json_out(200, ['status' => 'success', 'message' => 'If the email exists, a verification link has been sent.']);
    }

    if ((int)$user['is_email_verified'] === 1) {
        json_out(200, ['status' => 'success', 'message' => 'Email is already verified. You can login.']);
    }

    $rawVerifyToken  = bin2hex(random_bytes(32));
    $verifyTokenHash = hash('sha256', $rawVerifyToken);
    $verifyExpiresAt = date('Y-m-d H:i:s', time() + 24 * 60 * 60);

    $pdo->prepare("
        UPDATE users
        SET email_verification_token_hash = ?,
            email_verification_expires_at = ?
        WHERE id = ?
    ")->execute([$verifyTokenHash, $verifyExpiresAt, (int)$user['id']]);

    $verifyLink = app_url() . '/api/auth/verify_email.php?token=' . urlencode($rawVerifyToken);

    send_email(
        $email,
        'Verify your email - DeenLink (Resend)',
        "Assalamu alaikum,<br><br>
        Here is your verification link:<br>
        <a href='{$verifyLink}'>{$verifyLink}</a><br><br>
        This link expires in 24 hours."
    );

    json_out(200, ['status' => 'success', 'message' => 'If the email exists, a verification link has been sent.']);

} catch (Throwable $e) {
    error_log("resend_verification error: " . $e->getMessage());
    json_out(500, ['status' => 'error', 'message' => 'Server error']);
}
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

$oldEmail = trim((string)($data['old_email'] ?? ''));
$newEmail = trim((string)($data['new_email'] ?? ''));

if (!filter_var($oldEmail, FILTER_VALIDATE_EMAIL) || !filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
    json_out(400, ['status' => 'error', 'message' => 'Valid old and new email are required']);
}

try {
    $pdo = DB::conn();

    // Find user by old email
    $stmt = $pdo->prepare("SELECT id, is_email_verified FROM users WHERE email = ? LIMIT 1");
    $stmt->execute([$oldEmail]);
    $user = $stmt->fetch();

    // Avoid enumeration
    if (!$user) {
        json_out(200, ['status' => 'success', 'message' => 'If the account exists, the email has been updated and a verification link sent.']);
    }

    if ((int)$user['is_email_verified'] === 1) {
        json_out(400, ['status' => 'error', 'message' => 'Email is already verified. Please login.']);
    }

    // Ensure new email isn't already used
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
    $stmt->execute([$newEmail]);
    if ($stmt->fetch()) {
        json_out(400, ['status' => 'error', 'message' => 'That new email is already in use.']);
    }

    // New verification token
    $rawVerifyToken  = bin2hex(random_bytes(32));
    $verifyTokenHash = hash('sha256', $rawVerifyToken);
    $verifyExpiresAt = date('Y-m-d H:i:s', time() + 24 * 60 * 60);

    // Update email + token
    $pdo->prepare("
        UPDATE users
        SET email = ?,
            email_verification_token_hash = ?,
            email_verification_expires_at = ?
        WHERE id = ?
    ")->execute([$newEmail, $verifyTokenHash, $verifyExpiresAt, (int)$user['id']]);

    $verifyLink = app_url() . '/api/auth/verify_email.php?token=' . urlencode($rawVerifyToken);

    send_email(
        $newEmail,
        'Verify your email - DeenLink',
        "Assalamu alaikum,<br><br>
        Please verify your email by clicking this link:<br>
        <a href='{$verifyLink}'>{$verifyLink}</a><br><br>
        This link expires in 24 hours."
    );

    json_out(200, [
        'status' => 'success',
        'message' => 'Email updated. Verification link sent.',
        'email' => $newEmail
    ]);

} catch (Throwable $e) {
    error_log("change_email error: " . $e->getMessage());
    json_out(500, ['status' => 'error', 'message' => 'Server error']);
}
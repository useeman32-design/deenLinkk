<?php
declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '0');

require_once __DIR__ . '/../config/db.php';

$token = (string)($_GET['token'] ?? '');
if ($token === '') {
    http_response_code(400);
    echo "Invalid token.";
    exit;
}

try {
    $pdo = DB::conn();
    $tokenHash = hash('sha256', $token);

    $stmt = $pdo->prepare("
        SELECT id, is_email_verified
        FROM users
        WHERE email_verification_token_hash = ?
          AND email_verification_expires_at > NOW()
        LIMIT 1
    ");
    $stmt->execute([$tokenHash]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo "Verification link invalid or expired.";
        exit;
    }

    if ((int)$user['is_email_verified'] === 1) {
        echo "Email already verified. You can login.";
        exit;
    }

    $pdo->prepare("
        UPDATE users
        SET is_email_verified = 1,
            is_active = 1,
            email_verification_token_hash = NULL,
            email_verification_expires_at = NULL
        WHERE id = ?
    ")->execute([(int)$user['id']]);

    echo "Email verified successfully. You can now login.";

} catch (Throwable $e) {
    error_log("verify_email error: " . $e->getMessage());
    http_response_code(500);
    echo "Server error.";
}
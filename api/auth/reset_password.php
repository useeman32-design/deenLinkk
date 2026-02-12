<?php
declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '0');

require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../config/db.php';

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

$token = (string)($data['token'] ?? '');
$new_password = (string)($data['password'] ?? '');
$confirm = (string)($data['confirm_password'] ?? '');

if ($token === '' || $new_password === '' || $confirm === '') {
    json_out(400, ['status' => 'error', 'message' => 'Token and passwords are required']);
}

if ($new_password !== $confirm) {
    json_out(400, ['status' => 'error', 'message' => 'Passwords do not match']);
}

// Reuse your password rules
if (strlen($new_password) < 8) {
    json_out(400, ['status' => 'error', 'message' => 'Password must be at least 8 characters']);
}

$hasUpper = preg_match('/[A-Z]/', $new_password);
$hasLower = preg_match('/[a-z]/', $new_password);
$hasDigit = preg_match('/\d/', $new_password);
$hasSpecial = preg_match('/[!@#$%^&*()\-_=+{};:,<.>]/', $new_password);

if (!($hasUpper && $hasLower && $hasDigit && $hasSpecial)) {
    json_out(400, ['status' => 'error', 'message' => 'Password must contain uppercase, lowercase, number and special character']);
}

try {
    $pdo = DB::conn();
    $tokenHash = hash('sha256', $token);

    // Find valid unused token
    $stmt = $pdo->prepare("
        SELECT id, user_id
        FROM password_reset_tokens
        WHERE token_hash = ?
          AND used_at IS NULL
          AND expires_at > NOW()
        LIMIT 1
    ");
    $stmt->execute([$tokenHash]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        json_out(400, ['status' => 'error', 'message' => 'Reset link is invalid or expired']);
    }

    // Prevent re-using the same password
$stmt = $pdo->prepare("SELECT password_hash FROM users WHERE id = ? LIMIT 1");
$stmt->execute([(int)$row['user_id']]);
$currentHash = (string)$stmt->fetchColumn();

if ($currentHash && password_verify($new_password, $currentHash)) {
    json_out(400, [
        'status' => 'error',
        'message' => 'New password cannot be the same as the old password'
    ]);
}

    $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
    if (!$password_hash) throw new RuntimeException('Hashing failed');

    // Update password
    $pdo->prepare("UPDATE users SET password_hash = ?, last_password_change = NOW() WHERE id = ?")
        ->execute([$password_hash, (int)$row['user_id']]);

    // Mark token used
    $pdo->prepare("UPDATE password_reset_tokens SET used_at = NOW() WHERE id = ?")
        ->execute([(int)$row['id']]);

    // Optional: invalidate all sessions for that user (recommended)
    $pdo->prepare("UPDATE user_sessions SET is_active = 0 WHERE user_id = ?")
        ->execute([(int)$row['user_id']]);

    json_out(200, ['status' => 'success', 'message' => 'Password reset successful. Please login.']);

} catch (Throwable $e) {
    error_log("reset_password error: " . $e->getMessage());
    json_out(500, ['status' => 'error', 'message' => 'Server error']);
}
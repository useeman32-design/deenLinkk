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
if (!is_array($data)) {
    json_out(400, ['status' => 'error', 'message' => 'Invalid JSON data']);
}

$full_name = trim((string)($data['full_name'] ?? ''));
$email = trim((string)($data['email'] ?? ''));
$username = trim((string)($data['username'] ?? ''));
$password = (string)($data['password'] ?? '');
$confirm_password = (string)($data['confirm_password'] ?? '');

$gender = (string)($data['gender'] ?? '');
if (!in_array($gender, ['male', 'female'], true)) {
    $gender = null;
}

$country = trim((string)($data['country'] ?? ''));
$agree_terms = !empty($data['agree_terms']);

$errors = [];

if ($full_name === '' || mb_strlen($full_name) < 2 || mb_strlen($full_name) > 100) {
    $errors['full_name'] = 'Full name must be 2-100 characters';
}
if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 100) {
    $errors['email'] = 'Invalid email';
}
if ($username === '' || mb_strlen($username) < 3 || mb_strlen($username) > 20 || !preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
    $errors['username'] = 'Username must be 3-20 chars and contain only letters, numbers, underscores';
}
$reserved = ['admin','root','system','test','user','moderator','staff','support'];
if (in_array(strtolower($username), $reserved, true)) {
    $errors['username'] = 'This username is not available';
}

if ($password === '' || strlen($password) < 8) {
    $errors['password'] = 'Password must be at least 8 characters';
} else {
    $hasUpper = preg_match('/[A-Z]/', $password);
    $hasLower = preg_match('/[a-z]/', $password);
    $hasDigit = preg_match('/\d/', $password);
    $hasSpecial = preg_match('/[!@#$%^&*()\-_=+{};:,<.>]/', $password);
    if (!($hasUpper && $hasLower && $hasDigit && $hasSpecial)) {
        $errors['password'] = 'Password must contain uppercase, lowercase, number and special character';
    }
}

if ($password !== $confirm_password) {
    $errors['confirm_password'] = 'Passwords do not match';
}

if (!$agree_terms) {
    $errors['terms'] = 'You must agree to the terms and conditions';
}

if ($errors) {
    json_out(400, [
        'status' => 'error',
        'message' => 'Please fix the errors below',
        'errors' => $errors
    ]);
}

try {
    $pdo = DB::conn();

    // Unique email/username
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        json_out(400, ['status' => 'error', 'message' => 'Email already registered', 'errors' => ['email' => 'Email already registered']]);
    }

    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? LIMIT 1");
    $stmt->execute([$username]);
    if ($stmt->fetch()) {
        json_out(400, ['status' => 'error', 'message' => 'Username already taken', 'errors' => ['username' => 'Username already taken']]);
    }

    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    if (!$password_hash) {
        throw new RuntimeException('Password hashing failed');
    }

    // Create user as UNVERIFIED + INACTIVE
    $pdo->prepare("
        INSERT INTO users (username, email, password_hash, full_name, gender, country, deenpoints_balance, is_email_verified, is_active, created_at)
        VALUES (?, ?, ?, ?, ?, ?, 100, 0, 0, NOW())
    ")->execute([$username, $email, $password_hash, $full_name, $gender, $country ?: null]);

    $user_id = (int)$pdo->lastInsertId();

    // Create verification token (store hash)
    $rawVerifyToken  = bin2hex(random_bytes(32));
    $verifyTokenHash = hash('sha256', $rawVerifyToken);
    $verifyExpiresAt = date('Y-m-d H:i:s', time() + 24 * 60 * 60);

    $pdo->prepare("
        UPDATE users
        SET email_verification_token_hash = ?,
            email_verification_expires_at = ?
        WHERE id = ?
    ")->execute([$verifyTokenHash, $verifyExpiresAt, $user_id]);

    $verifyLink = app_url() . '/api/auth/verify_email.php?token=' . urlencode($rawVerifyToken);

    send_email(
        $email,
        'Verify your email - DeenLink',
        "Assalamu alaikum,<br><br>
        Please verify your email by clicking this link:<br>
        <a href='{$verifyLink}'>{$verifyLink}</a><br><br>
        This link expires in 24 hours."
    );

    json_out(201, [
        'status' => 'success',
        'needs_verification' => true,
        'message' => 'Account created. Please verify your email to continue.',
        'email' => $email
    ]);

} catch (Throwable $e) {
    error_log("Register error: " . $e->getMessage());
    json_out(500, ['status' => 'error', 'message' => 'Server error']);
}
<?php
declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '0');

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

$input = file_get_contents('php://input') ?: '';
$data = json_decode($input, true);
if (!is_array($data)) {
    json_out(400, ['status' => 'error', 'message' => 'Invalid JSON']);
}

$identifier = trim((string)($data['identifier'] ?? ''));
$password   = (string)($data['password'] ?? '');
$remember   = !empty($data['remember_me']);

if ($identifier === '' || $password === '') {
    json_out(400, ['status' => 'error', 'message' => 'Identifier and password are required']);
}

try {
    $pdo = DB::conn();

    $stmt = $pdo->prepare("
        SELECT id, username, email, password_hash, full_name,
        user_type,
        deenpoints_balance, profile_image, bio,
        gender, country, created_at,
        is_active, is_email_verified,
        failed_login_attempts, account_locked_until
        FROM users
        WHERE email = ? OR username = ?
        LIMIT 1
    ");
    $stmt->execute([$identifier, $identifier]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Generic fail (avoid user enumeration)
    $genericFail = function() : void {
        usleep(random_int(250000, 800000));
        json_out(401, ['status' => 'error', 'message' => 'Invalid username/email or password']);
    };

    if (!$user) {
        $genericFail();
    }

    

    if (!empty($user['account_locked_until']) && strtotime((string)$user['account_locked_until']) > time()) {
        json_out(429, ['status' => 'error', 'message' => 'Account temporarily locked. Try again later.']);
    }

    if (!password_verify($password, (string)$user['password_hash'])) {
        // Increment failed attempts
        $pdo->prepare("UPDATE users SET failed_login_attempts = failed_login_attempts + 1 WHERE id = ?")
            ->execute([(int)$user['id']]);

        // Lock after N attempts
        $attempts = (int)$pdo->prepare("SELECT failed_login_attempts FROM users WHERE id = ?")
            ->execute([(int)$user['id']]) ?: 0;

        // The above execute() doesn't fetch; do it properly:
        $st2 = $pdo->prepare("SELECT failed_login_attempts FROM users WHERE id = ?");
        $st2->execute([(int)$user['id']]);
        $attempts = (int)$st2->fetchColumn();

        if ($attempts >= 8) {
            $pdo->prepare("UPDATE users SET account_locked_until = DATE_ADD(NOW(), INTERVAL 15 MINUTE) WHERE id = ?")
                ->execute([(int)$user['id']]);
        }

        $genericFail();
    }

    // Require email verification
    if ((int)$user['is_email_verified'] !== 1) {
        json_out(403, [
            'status' => 'error',
            'needs_verification' => true,
            'message' => 'Please verify your email to login.',
            'email' => $user['email']
        ]);
    }
    if ((int)$user['is_active'] !== 1) {
        json_out(403, ['status' => 'error', 'message' => 'Account disabled']);
    }
    // Success: reset failed attempts + update last login
    $pdo->prepare("
        UPDATE users
        SET failed_login_attempts = 0,
            account_locked_until = NULL,
            last_login = NOW(),
            last_login_ip = ?
        WHERE id = ?
    ")->execute([$_SERVER['REMOTE_ADDR'] ?? null, (int)$user['id']]);

    // Regenerate PHP session id to prevent fixation
    session_regenerate_id(true);

    $_SESSION['user_id'] = (int)$user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['logged_in'] = true;

    // Create remember cookie session
    $rawToken  = bin2hex(random_bytes(32));
    $tokenHash = hash('sha256', $rawToken);

    $days = $remember ? 30 : 2;
    $expiresAt = date('Y-m-d H:i:s', time() + ($days * 86400));

    $pdo->prepare("
        INSERT INTO user_sessions (user_id, session_token_hash, ip_address, user_agent, expires_at, is_active)
        VALUES (?, ?, ?, ?, ?, 1)
    ")->execute([
        (int)$user['id'],
        $tokenHash,
        $_SERVER['REMOTE_ADDR'] ?? null,
        $_SERVER['HTTP_USER_AGENT'] ?? null,
        $expiresAt
    ]);

    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    setcookie('deenlink_session', $rawToken, [
        'expires'  => time() + ($days * 86400),
        'path'     => '/',
        'secure'   => $secure,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);

    // Return user info (no password)
    unset($user['password_hash']);

    json_out(200, [
        'status' => 'success',
        'message' => 'Login successful',
        'user' => [
            'id' => (int)$user['id'],
            'username' => $user['username'],
            'email' => $user['email'],
            'full_name' => $user['full_name'],
            'user_type' => $user['user_type'] ?? 'user',
            'deenpoints_balance' => (int)$user['deenpoints_balance'],
            'profile_image' => $user['profile_image'],
            'bio' => $user['bio'],
            'gender' => $user['gender'],
            'country' => $user['country'],
            'created_at' => $user['created_at']
        ],
        'redirect' => (($user['user_type'] ?? 'user') === 'scholar') ? '../profile/scholar.html' : '../profile/'
    ]);

} catch (Throwable $e) {
    error_log("Login error: " . $e->getMessage());
    json_out(500, ['status' => 'error', 'message' => 'Server error']);
}
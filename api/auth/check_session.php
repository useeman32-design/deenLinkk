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

try {
    // 1) PHP session check
    if (!empty($_SESSION['logged_in']) && !empty($_SESSION['user_id'])) {
        $pdo = DB::conn();
        $stmt = $pdo->prepare("
            SELECT id, username, email, full_name,
                   user_type,
                   deenpoints_balance, profile_image, bio,
                   gender, country, created_at,
                   hide_charity_balance,
                   last_username_change,
                   last_full_name_change
            FROM users
            WHERE id = ?
            LIMIT 1
        ");
        $stmt->execute([(int)$_SESSION['user_id']]);
        $user = $stmt->fetch();

        if ($user) {
            json_out(200, [
                'status' => 'success',
                'logged_in' => true,
                'user' => $user,
                'message' => 'Session valid'
            ]);
        }
    }

    // 2) Cookie token check
    $rawToken = $_COOKIE['deenlink_session'] ?? '';
    if ($rawToken !== '') {
        $pdo = DB::conn();
        $tokenHash = hash('sha256', $rawToken);

        $stmt = $pdo->prepare("
            SELECT us.user_id,
                   u.id, u.username, u.email, u.full_name,
                   u.user_type,
                   u.deenpoints_balance, u.profile_image, u.bio,
                   u.gender, u.country, u.created_at,
                   u.hide_charity_balance,
                   u.last_username_change,
                   u.last_full_name_change
            FROM user_sessions us
            JOIN users u ON u.id = us.user_id
            WHERE us.session_token_hash = ?
              AND us.expires_at > NOW()
              AND us.is_active = 1
            LIMIT 1
        ");
        $stmt->execute([$tokenHash]);
        $user = $stmt->fetch();

        if ($user) {
            // Update last activity
            $pdo->prepare("UPDATE user_sessions SET last_activity = NOW() WHERE session_token_hash = ?")
                ->execute([$tokenHash]);

            // Restore PHP session
            session_regenerate_id(true);
            $_SESSION['user_id'] = (int)$user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['user_type'] = $user['user_type'] ?? 'user'; // optional but useful
            $_SESSION['logged_in'] = true;

            json_out(200, [
                'status' => 'success',
                'logged_in' => true,
                'user' => $user,
                'message' => 'Cookie session valid'
            ]);
        }
    }

    json_out(200, [
        'status' => 'success',
        'logged_in' => false,
        'message' => 'No valid session'
    ]);

} catch (Throwable $e) {
    error_log("check_session error: " . $e->getMessage());
    json_out(500, [
        'status' => 'error',
        'logged_in' => false,
        'message' => 'Server error'
    ]);
}
<?php
declare(strict_types=1);

/**
 * Secure PHP session settings.
 * In production, use HTTPS and set cookie_secure=true.
 */

$secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');

ini_set('session.use_strict_mode', '1');
ini_set('session.use_only_cookies', '1');
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_samesite', 'Lax');
if ($secure) {
    ini_set('session.cookie_secure', '1');
}

// Start session if needed
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * ------------------------------------------------------------
 * Auto-login bridge using remember-me cookie (deenlink_session)
 * ------------------------------------------------------------
 * If PHP session expired but cookie token is still valid in user_sessions,
 * restore $_SESSION so session-based endpoints keep working.
 *
 * IMPORTANT: This requires DB class. We include it conditionally to avoid
 * errors if DB isn't available in some scripts.
 */
if (
    (empty($_SESSION['logged_in']) || empty($_SESSION['user_id'])) &&
    !empty($_COOKIE['deenlink_session'])
) {
    // Avoid recursion if this file is included from db.php, etc.
    if (!class_exists('DB') && file_exists(__DIR__ . '/db.php')) {
        require_once __DIR__ . '/db.php';
    }

    if (class_exists('DB')) {
        try {
            $rawToken  = (string)$_COOKIE['deenlink_session'];
            $tokenHash = hash('sha256', $rawToken);

            $pdo = DB::conn();
            $stmt = $pdo->prepare("
                SELECT us.user_id, u.username, u.email
                FROM user_sessions us
                JOIN users u ON u.id = us.user_id
                WHERE us.session_token_hash = ?
                  AND us.is_active = 1
                  AND us.expires_at > NOW()
                  AND u.is_active = 1
                LIMIT 1
            ");
            $stmt->execute([$tokenHash]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($row) {
                $_SESSION['user_id'] = (int)$row['user_id'];
                $_SESSION['username'] = (string)$row['username'];
                $_SESSION['email'] = (string)$row['email'];
                $_SESSION['logged_in'] = true;
            }
        } catch (Throwable $e) {
            // Silent fail: user just appears logged out
            // error_log("Auto-login bridge error: " . $e->getMessage());
        }
    }
}

/**<?php
declare(strict_types=1);

/**
 * Secure PHP session settings.
 * In production, use HTTPS and set cookie_secure=true.
 

$secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');

ini_set('session.use_strict_mode', '1');
ini_set('session.use_only_cookies', '1');
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_samesite', 'Lax');
if ($secure) {
    ini_set('session.cookie_secure', '1');
}

// Start session if needed
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}*/
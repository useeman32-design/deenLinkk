<?php
declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '0');

require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/csrf.php';

function json_out(int $code, array $payload): void {
    http_response_code($code);
    echo json_encode($payload);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_out(405, ['status' => 'error', 'message' => 'Method not allowed']);
}

require_csrf();

if (empty($_SESSION['logged_in']) || empty($_SESSION['user_id'])) {
    json_out(401, ['status' => 'error', 'message' => 'Not logged in']);
}

$input = file_get_contents('php://input') ?: '';
$data = json_decode($input, true);
if (!is_array($data)) {
    json_out(400, ['status' => 'error', 'message' => 'Invalid JSON']);
}

$full_name = trim((string)($data['full_name'] ?? ''));
$bio = trim((string)($data['bio'] ?? ''));
$username = trim((string)($data['username'] ?? ''));
$hide_charity_balance = !empty($data['hide_charity_balance']);

$errors = [];
if ($full_name !== '' && (mb_strlen($full_name) < 2 || mb_strlen($full_name) > 100)) {
    $errors['full_name'] = 'Full name must be 2-100 characters';
}
if ($bio !== '' && mb_strlen($bio) > 500) {
    $errors['bio'] = 'Bio must be 500 characters or less';
}
if ($username !== '') {
    if (mb_strlen($username) < 3 || mb_strlen($username) > 20 || !preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $errors['username'] = 'Username must be 3-20 chars and contain only letters, numbers, underscores';
    }
    $reserved = ['admin','root','system','test','user','moderator','staff','support'];
    if (in_array(strtolower($username), $reserved, true)) {
        $errors['username'] = 'This username is not available';
    }
}
if ($errors) {
    json_out(400, ['status' => 'error', 'message' => 'Validation failed', 'errors' => $errors]);
}

$userId = (int)$_SESSION['user_id'];

try {
    $pdo = DB::conn();

    // Current user values
    $stmt = $pdo->prepare("
        SELECT username, full_name, bio, hide_charity_balance,
               last_username_change, last_full_name_change
        FROM users
        WHERE id = ?
        LIMIT 1
    ");
    $stmt->execute([$userId]);
    $current = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$current) {
        json_out(404, ['status' => 'error', 'message' => 'User not found']);
    }

    $newUsername = $username !== '' ? $username : (string)$current['username'];
    $newFullName = $full_name !== '' ? $full_name : (string)$current['full_name'];
    $newBio = $bio; // allow empty bio
    $newHide = $hide_charity_balance ? 1 : 0;

    $changingUsername = ($newUsername !== (string)$current['username']);
    $changingFullName = ($newFullName !== (string)$current['full_name']);

    // Enforce username change once per 30 days
    if ($changingUsername) {
        $last = $current['last_username_change'];
        if ($last && strtotime((string)$last) > (time() - 30 * 86400)) {
            json_out(429, [
                'status' => 'error',
                'message' => 'You can change your username only once every 30 days.'
            ]);
        }

        // Check availability
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? AND id <> ? LIMIT 1");
        $stmt->execute([$newUsername, $userId]);
        if ($stmt->fetch()) {
            json_out(400, ['status' => 'error', 'message' => 'Username already taken', 'errors' => ['username' => 'Username already taken']]);
        }
    }

    // Enforce full name change once per 30 days
    if ($changingFullName) {
        $last = $current['last_full_name_change'];
        if ($last && strtotime((string)$last) > (time() - 30 * 86400)) {
            json_out(429, [
                'status' => 'error',
                'message' => 'You can change your full name only once every 30 days.'
            ]);
        }
    }

    // Build update query dynamically
    $fields = [
        'bio' => $newBio,
        'hide_charity_balance' => $newHide,
        'updated_at' => date('Y-m-d H:i:s')
    ];

    if ($changingUsername) {
        $fields['username'] = $newUsername;
        $fields['last_username_change'] = date('Y-m-d H:i:s');
    }

    if ($changingFullName) {
        $fields['full_name'] = $newFullName;
        $fields['last_full_name_change'] = date('Y-m-d H:i:s');
    }

    $setParts = [];
    $values = [];
    foreach ($fields as $k => $v) {
        $setParts[] = "{$k} = ?";
        $values[] = $v;
    }
    $values[] = $userId;

    $sql = "UPDATE users SET " . implode(', ', $setParts) . " WHERE id = ?";
    $pdo->prepare($sql)->execute($values);

    // Return updated data
    $stmt = $pdo->prepare("
        SELECT id, username, email, full_name, bio, hide_charity_balance, profile_image,
               last_username_change, last_full_name_change, updated_at
        FROM users WHERE id = ?
    ");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    json_out(200, [
        'status' => 'success',
        'message' => 'Profile updated',
        'user' => $user
    ]);

} catch (Throwable $e) {
    error_log("update_profile error: " . $e->getMessage());
    json_out(500, ['status' => 'error', 'message' => 'Server error']);
}

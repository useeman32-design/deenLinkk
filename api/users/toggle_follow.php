<?php
declare(strict_types=1);

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
    json_out(401, ['status' => 'error', 'message' => 'Not logged in', 'requires_login' => true]);
}

$input = file_get_contents('php://input') ?: '';
$data = json_decode($input, true);

$targetId = (int)($data['user_id'] ?? 0);
$me = (int)$_SESSION['user_id'];

if ($targetId <= 0) json_out(400, ['status' => 'error', 'message' => 'Invalid user_id']);
if ($targetId === $me) json_out(400, ['status' => 'error', 'message' => 'You cannot follow yourself']);

try {
    $pdo = DB::conn();

    $stmt = $pdo->prepare("SELECT 1 FROM user_follows WHERE follower_id = ? AND following_id = ? LIMIT 1");
    $stmt->execute([$me, $targetId]);
    $exists = (bool)$stmt->fetchColumn();

    if ($exists) {
        $pdo->prepare("DELETE FROM user_follows WHERE follower_id = ? AND following_id = ?")->execute([$me, $targetId]);
        $following = false;
    } else {
        $pdo->prepare("INSERT INTO user_follows (follower_id, following_id, created_at) VALUES (?, ?, NOW())")->execute([$me, $targetId]);
        $following = true;
    }

    // counts
    $st1 = $pdo->prepare("SELECT COUNT(*) FROM user_follows WHERE following_id = ?");
    $st1->execute([$targetId]);
    $followersCount = (int)$st1->fetchColumn();

    $st2 = $pdo->prepare("SELECT COUNT(*) FROM user_follows WHERE follower_id = ?");
    $st2->execute([$targetId]);
    $followingCount = (int)$st2->fetchColumn();

    json_out(200, [
        'status' => 'success',
        'following' => $following,
        'followers_count' => $followersCount,
        'following_count' => $followingCount
    ]);

} catch (Throwable $e) {
    error_log("toggle_follow error: " . $e->getMessage());
    json_out(500, ['status' => 'error', 'message' => 'Server error']);
}
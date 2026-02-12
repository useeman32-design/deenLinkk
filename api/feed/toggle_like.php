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
$postId = (int)($data['post_id'] ?? 0);
if ($postId <= 0) json_out(400, ['status' => 'error', 'message' => 'Invalid post_id']);

$userId = (int)$_SESSION['user_id'];

try {
    $pdo = DB::conn();

    // check existing
    $stmt = $pdo->prepare("SELECT 1 FROM post_likes WHERE post_id = ? AND user_id = ? LIMIT 1");
    $stmt->execute([$postId, $userId]);
    $exists = (bool)$stmt->fetchColumn();

    if ($exists) {
        $pdo->prepare("DELETE FROM post_likes WHERE post_id = ? AND user_id = ?")->execute([$postId, $userId]);
        $liked = false;
    } else {
        $pdo->prepare("INSERT INTO post_likes (post_id, user_id, created_at) VALUES (?, ?, NOW())")->execute([$postId, $userId]);
        $liked = true;
    }

    $count = (int)$pdo->prepare("SELECT COUNT(*) FROM post_likes WHERE post_id = ?")
        ->execute([$postId]) ?: 0;
    // proper fetch:
    $st2 = $pdo->prepare("SELECT COUNT(*) FROM post_likes WHERE post_id = ?");
    $st2->execute([$postId]);
    $count = (int)$st2->fetchColumn();

    json_out(200, ['status' => 'success', 'liked' => $liked, 'like_count' => $count]);

} catch (Throwable $e) {
    error_log("toggle_like error: " . $e->getMessage());
    json_out(500, ['status' => 'error', 'message' => 'Server error']);
}
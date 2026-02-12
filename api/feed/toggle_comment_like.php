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

$commentId = (int)($data['comment_id'] ?? 0);
if ($commentId <= 0) json_out(400, ['status' => 'error', 'message' => 'Invalid comment_id']);

$userId = (int)$_SESSION['user_id'];

try {
    $pdo = DB::conn();

    $stmt = $pdo->prepare("SELECT 1 FROM comment_likes WHERE comment_id = ? AND user_id = ? LIMIT 1");
    $stmt->execute([$commentId, $userId]);
    $exists = (bool)$stmt->fetchColumn();

    if ($exists) {
        $pdo->prepare("DELETE FROM comment_likes WHERE comment_id = ? AND user_id = ?")->execute([$commentId, $userId]);
        $liked = false;
    } else {
        $pdo->prepare("INSERT INTO comment_likes (comment_id, user_id, created_at) VALUES (?, ?, NOW())")->execute([$commentId, $userId]);
        $liked = true;
    }

    $st2 = $pdo->prepare("SELECT COUNT(*) FROM comment_likes WHERE comment_id = ?");
    $st2->execute([$commentId]);
    $count = (int)$st2->fetchColumn();

    json_out(200, ['status' => 'success', 'liked' => $liked, 'like_count' => $count]);

} catch (Throwable $e) {
    error_log("toggle_comment_like error: " . $e->getMessage());
    json_out(500, ['status' => 'error', 'message' => 'Server error']);
}
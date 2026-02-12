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
$text = trim((string)($data['text'] ?? ''));

if ($postId <= 0) json_out(400, ['status' => 'error', 'message' => 'Invalid post_id']);
if ($text === '' || mb_strlen($text) > 500) json_out(400, ['status' => 'error', 'message' => 'Comment must be 1-500 characters']);

$userId = (int)$_SESSION['user_id'];

try {
    $pdo = DB::conn();
    $pdo->prepare("INSERT INTO post_comments (post_id, user_id, comment_text, created_at) VALUES (?, ?, ?, NOW())")
        ->execute([$postId, $userId, $text]);

    $commentId = (int)$pdo->lastInsertId();

    // return updated count
    $st = $pdo->prepare("SELECT COUNT(*) FROM post_comments WHERE post_id = ? AND is_deleted = 0");
    $st->execute([$postId]);
    $count = (int)$st->fetchColumn();

    json_out(200, ['status' => 'success', 'comment_id' => $commentId, 'comment_count' => $count]);

} catch (Throwable $e) {
    error_log("add_comment error: " . $e->getMessage());
    json_out(500, ['status' => 'error', 'message' => 'Server error']);
}
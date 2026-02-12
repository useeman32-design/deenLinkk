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
    json_out(401, ['status' => 'error', 'message' => 'Not logged in']);
}

$input = file_get_contents('php://input') ?: '';
$data = json_decode($input, true);
$postId = (int)($data['post_id'] ?? 0);
if ($postId <= 0) json_out(400, ['status' => 'error', 'message' => 'Invalid post_id']);

$userId = (int)$_SESSION['user_id'];

try {
    $pdo = DB::conn();

    // Ensure ownership
    $stmt = $pdo->prepare("SELECT user_id FROM posts WHERE id = ? AND is_deleted = 0 LIMIT 1");
    $stmt->execute([$postId]);
    $owner = $stmt->fetchColumn();

    if (!$owner) json_out(404, ['status' => 'error', 'message' => 'Post not found']);
    if ((int)$owner !== $userId) json_out(403, ['status' => 'error', 'message' => 'Forbidden']);

    // soft delete
    $pdo->prepare("UPDATE posts SET is_deleted = 1 WHERE id = ?")->execute([$postId]);

    json_out(200, ['status' => 'success', 'message' => 'Post deleted']);
} catch (Throwable $e) {
    error_log("delete_post error: " . $e->getMessage());
    json_out(500, ['status' => 'error', 'message' => 'Server error']);
}
<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../config/db.php';

function json_out(int $code, array $payload): void {
    http_response_code($code);
    echo json_encode($payload);
    exit;
}

$userId = (int)($_GET['user_id'] ?? 0);
if ($userId <= 0) json_out(400, ['status' => 'error', 'message' => 'Invalid user_id']);

try {
    $pdo = DB::conn();

    $st1 = $pdo->prepare("SELECT COUNT(*) FROM user_follows WHERE following_id = ?");
    $st1->execute([$userId]);
    $followers = (int)$st1->fetchColumn();

    $st2 = $pdo->prepare("SELECT COUNT(*) FROM user_follows WHERE follower_id = ?");
    $st2->execute([$userId]);
    $following = (int)$st2->fetchColumn();

    json_out(200, ['status' => 'success', 'followers' => $followers, 'following' => $following]);

} catch (Throwable $e) {
    error_log("get_follow_counts error: " . $e->getMessage());
    json_out(500, ['status' => 'error', 'message' => 'Server error']);
}
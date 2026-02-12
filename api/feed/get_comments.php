<?php
declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '0');

require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/session.php';


function time_ago(string $dt): string {
    $t = strtotime($dt);
    if (!$t) return $dt;

    $diff = time() - $t;
    if ($diff < 60) return $diff . "s ago";
    if ($diff < 3600) return floor($diff / 60) . "m ago";
    if ($diff < 86400) return floor($diff / 3600) . "h ago";
    if ($diff < 2592000) return floor($diff / 86400) . " days ago";
    return date('F j, Y', $t);
}

function json_out(int $code, array $payload): void {
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload);
    exit;
}

function base_path(): string {
    $script = $_SERVER['SCRIPT_NAME'] ?? '';
    $parts = explode('/', trim($script, '/'));
    $base = '';
    if (count($parts) > 0 && strtolower($parts[0]) !== 'api') $base = '/' . $parts[0];
    return $base;
}

$postId = (int)($_GET['post_id'] ?? 0);
if ($postId <= 0) {
    json_out(400, ['status' => 'error', 'message' => 'Invalid post_id']);
}

$isLoggedIn = !empty($_SESSION['logged_in']) && !empty($_SESSION['user_id']);
$me = $isLoggedIn ? (int)$_SESSION['user_id'] : 0;

try {
    $pdo = DB::conn();

    $stmt = $pdo->prepare("
      SELECT c.id, c.comment_text, c.created_at,
             u.id AS user_id, u.username, u.full_name, u.profile_image,
             (SELECT COUNT(*) FROM comment_likes cl WHERE cl.comment_id = c.id) AS like_count,
             EXISTS(SELECT 1 FROM comment_likes cl2 WHERE cl2.comment_id = c.id AND cl2.user_id = ?) AS liked_by_me
      FROM post_comments c
      JOIN users u ON u.id = c.user_id
      WHERE c.post_id = ? AND c.is_deleted = 0
      ORDER BY c.id ASC
      LIMIT 200
    ");
    $stmt->execute([$me, $postId]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $base = base_path();

    $comments = array_map(function($r) use ($base) {
        $p = $r['profile_image'] ?? 'default_profile.jpg';
        $profileUrl = ($p === 'default_profile.jpg')
            ? ($base . '/img/default_profile.jpg')
            : ($base . '/uploads/profile/' . $p);

        return [
            'id' => (int)$r['id'],
            'text' => (string)$r['comment_text'],
            'created_at' => (string)$r['created_at'],
            'time_ago' => time_ago((string)$r['created_at']),
            'like_count' => (int)$r['like_count'],
            'liked_by_me' => ((int)$r['liked_by_me'] === 1),
            'user' => [
                'id' => (int)$r['user_id'],
                'name' => (string)$r['full_name'],
                'username' => (string)$r['username'],
                'profile_image_url' => $profileUrl
            ]
        ];
    }, $rows);

    json_out(200, ['status' => 'success', 'comments' => $comments]);

} catch (Throwable $e) {
    error_log("get_comments error: " . $e->getMessage());
    json_out(500, ['status' => 'error', 'message' => 'Server error']);
}
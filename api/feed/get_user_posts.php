<?php
declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '0');

require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/session.php';

error_log("get_user_posts session user_id=" . ($_SESSION['user_id'] ?? 'NULL') . " requested=" . ($_GET['user_id'] ?? 'NULL'));
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

if (empty($_SESSION['logged_in']) || empty($_SESSION['user_id'])) {
    json_out(401, ['status' => 'error', 'message' => 'Not logged in']);
}

$me = (int)$_SESSION['user_id'];

// Default to "me" if not provided
$requestedUserId = isset($_GET['user_id']) ? (int)$_GET['user_id'] : $me;

// Only allow viewing your own posts via this endpoint (for now)
if ($requestedUserId !== $me) {
    json_out(403, ['status' => 'error', 'message' => 'Forbidden']);
}
$limit = (int)($_GET['limit'] ?? 20);
$limit = max(1, min($limit, 50));
$cursor = (int)($_GET['cursor'] ?? 0);

try {
    $pdo = DB::conn();

    $whereCursor = $cursor > 0 ? "AND p.id < :cursor" : "";

    $sql = "
      SELECT
        p.id,
        p.user_id,
        p.content_text,
        p.created_at,

        u.username,
        u.full_name,
        u.profile_image,

        (SELECT COUNT(*) FROM post_likes pl WHERE pl.post_id = p.id) AS like_count,
        (SELECT COUNT(*) FROM post_comments pc WHERE pc.post_id = p.id AND pc.is_deleted = 0) AS comment_count,
        EXISTS(SELECT 1 FROM post_likes pl2 WHERE pl2.post_id = p.id AND pl2.user_id = :me) AS liked_by_me

      FROM posts p
      JOIN users u ON u.id = p.user_id
      WHERE p.is_deleted = 0
        AND p.user_id = :uid
      $whereCursor
      ORDER BY p.id DESC
      LIMIT :limit
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':me', $me, PDO::PARAM_INT);
    $stmt->bindValue(':uid', $requestedUserId, PDO::PARAM_INT);
    if ($cursor > 0) $stmt->bindValue(':cursor', $cursor, PDO::PARAM_INT);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();

    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Media lookup
    $postIds = array_map(fn($r) => (int)$r['id'], $rows);
    $mediaByPost = [];
    if ($postIds) {
        $in = implode(',', array_fill(0, count($postIds), '?'));
        $m = $pdo->prepare("SELECT post_id, file_1080, file_360 FROM post_media WHERE post_id IN ($in) ORDER BY sort_order ASC, id ASC");
        $m->execute($postIds);
        while ($mr = $m->fetch(PDO::FETCH_ASSOC)) {
            $pid = (int)$mr['post_id'];
            $mediaByPost[$pid] ??= [];
            $base = base_path();
            $mediaByPost[$pid][] = [
                'image_url_1080' => $base . '/uploads/posts/' . $mr['file_1080'],
                'image_url_360'  => $base . '/uploads/posts/' . $mr['file_360'],
            ];
        }
    }

    $base = base_path();

    $posts = array_map(function($r) use ($mediaByPost, $base) {
        $pid = (int)$r['id'];
        $profileImage = $r['profile_image'] ?? 'default_profile.jpg';
        $profileUrl = ($profileImage === 'default_profile.jpg')
            ? ($base . '/img/default_profile.jpg')
            : ($base . '/uploads/profile/' . $profileImage);

        return [
            'id' => $pid,
            'content_text' => $r['content_text'] ?? '',
            'created_at' => $r['created_at'],
            'time_ago' => time_ago((string)$r['created_at']),
            'like_count' => (int)$r['like_count'],
            'comment_count' => (int)$r['comment_count'],
            'liked_by_me' => (bool)$r['liked_by_me'],
            'user' => [
                'id' => (int)$r['user_id'],
                'name' => $r['full_name'],
                'username' => $r['username'],
                'profile_image_url' => $profileUrl
            ],
            'media' => $mediaByPost[$pid] ?? []
        ];
    }, $rows);

    $nextCursor = count($posts) ? end($posts)['id'] : null;

    json_out(200, ['status' => 'success', 'posts' => $posts, 'next_cursor' => $nextCursor]);

} catch (Throwable $e) {
    error_log("get_user_posts error: " . $e->getMessage());
    json_out(500, ['status' => 'error', 'message' => 'Server error']);
}
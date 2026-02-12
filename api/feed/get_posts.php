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
    if ($diff < 2592000) return floor($diff / 86400) . " days ago"; // < 30 days

    return date('F j, Y', $t);
}

function json_out(int $code, array $payload): void {
    http_response_code($code);
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

$tab = $_GET['tab'] ?? 'for-you';          // for-you | scholars | following
$limit = (int)($_GET['limit'] ?? 10);
$cursor = (int)($_GET['cursor'] ?? 0);     // cursor = last post id (simple)
$limit = max(1, min($limit, 20));

$isLoggedIn = !empty($_SESSION['logged_in']) && !empty($_SESSION['user_id']);
$userId = $isLoggedIn ? (int)$_SESSION['user_id'] : 0;

// Guests: only allow up to 10 posts total (you can tune)
if (!$isLoggedIn && $limit > 10) $limit = 10;

try {
    $pdo = DB::conn();

    // following tab requires login (for now)
    if ($tab === 'following' && !$isLoggedIn) {
        json_out(200, [
            'status' => 'success',
            'requires_login' => true,
            'message' => 'Login to view Following feed.'
        ]);
    }

    // Very simple query for now: newest first
    // Cursor: if cursor provided, fetch posts with id < cursor
    $whereCursor = $cursor > 0 ? "AND p.id < :cursor" : "";

    $sql = "
  SELECT
    p.id,
    p.user_id,
    p.content_text,
    p.created_at,

    EXISTS(
      SELECT 1 FROM user_follows uf
      WHERE uf.follower_id = :me1 AND uf.following_id = p.user_id
    ) AS following_by_me,

    u.username,
    u.full_name,
    u.profile_image,

    (SELECT COUNT(*) FROM post_likes pl WHERE pl.post_id = p.id) AS like_count,
    (SELECT COUNT(*) FROM post_comments pc WHERE pc.post_id = p.id AND pc.is_deleted = 0) AS comment_count,

    EXISTS(
      SELECT 1 FROM post_likes pl2
      WHERE pl2.post_id = p.id AND pl2.user_id = :me2
    ) AS liked_by_me

  FROM posts p
  JOIN users u ON u.id = p.user_id
  WHERE p.is_deleted = 0
  $whereCursor
  ORDER BY p.id DESC
  LIMIT :limit
";
    error_log("TAB=$tab cursor=$cursor limit=$limit SQL=" . $sql);
    $stmt = $pdo->prepare($sql);
$stmt->bindValue(':me1', $userId, PDO::PARAM_INT);
$stmt->bindValue(':me2', $userId, PDO::PARAM_INT);
if ($cursor > 0) $stmt->bindValue(':cursor', $cursor, PDO::PARAM_INT);
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->execute();


    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Attach media for each post (1 query)
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

        // profile images live in uploads/profile/ (filename stored)
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
            'following_by_me' => ((int)$r['following_by_me'] === 1),
            'user' => [
                'id' => (int)$r['user_id'],
                'name' => $r['full_name'],
                'username' => $r['username'],
                'profile_image_url' => $profileUrl
            ],
            'media' => $mediaByPost[$pid] ?? []
        ];
    }, $rows);

    // next cursor (simple): last post id in this batch
    $nextCursor = count($posts) ? end($posts)['id'] : null;

    // Guest preview gate: if guest and they request more (cursor != 0), enforce stop after first page
    if (!$isLoggedIn && $cursor > 0) {
        json_out(200, [
            'status' => 'success',
            'requires_login' => true,
            'message' => 'Login to see more posts.',
            'posts' => []
        ]);
    }

    json_out(200, [
        'status' => 'success',
        'posts' => $posts,
        'next_cursor' => $nextCursor
    ]);

} catch (Throwable $e) {
    error_log("get_posts error: " . $e->getMessage());
    json_out(500, ['status' => 'error', 'message' => 'Server error']);
}
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

function base_path(): string {
    $script = $_SERVER['SCRIPT_NAME'] ?? '';
    $parts = explode('/', trim($script, '/'));
    $base = '';
    if (count($parts) > 0 && strtolower($parts[0]) !== 'api') $base = '/' . $parts[0];
    return $base;
}

function load_image_from_tmp(string $tmp, string $mime) {
    return match ($mime) {
        'image/jpeg' => @imagecreatefromjpeg($tmp),
        'image/png'  => @imagecreatefrompng($tmp),
        default      => false
    };
}

function resize_to_jpg($src, int $maxW, int $quality, string $savePath): array {
    $srcW = imagesx($src);
    $srcH = imagesy($src);

    $scale = min($maxW / $srcW, 1);
    $dstW = (int)floor($srcW * $scale);
    $dstH = (int)floor($srcH * $scale);

    $resized = imagecreatetruecolor($dstW, $dstH);
    imagealphablending($resized, false);
    imagesavealpha($resized, true);
    $transparent = imagecolorallocatealpha($resized, 0, 0, 0, 127);
    imagefilledrectangle($resized, 0, 0, $dstW, $dstH, $transparent);

    imagecopyresampled($resized, $src, 0, 0, 0, 0, $dstW, $dstH, $srcW, $srcH);

    // White background for JPG
    $bg = imagecreatetruecolor($dstW, $dstH);
    $white = imagecolorallocate($bg, 255, 255, 255);
    imagefilledrectangle($bg, 0, 0, $dstW, $dstH, $white);
    imagecopy($bg, $resized, 0, 0, 0, 0, $dstW, $dstH);

    $ok = imagejpeg($bg, $savePath, $quality);

    imagedestroy($resized);
    imagedestroy($bg);

    if (!$ok) return [false, 0, 0];
    return [true, $dstW, $dstH];
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_out(405, ['status' => 'error', 'message' => 'Method not allowed']);
}

require_csrf();

if (empty($_SESSION['logged_in']) || empty($_SESSION['user_id'])) {
    json_out(401, ['status' => 'error', 'message' => 'Not logged in', 'requires_login' => true]);
}

$content = trim((string)($_POST['content_text'] ?? ''));
if ($content === '' && empty($_FILES['images'])) {
    json_out(400, ['status' => 'error', 'message' => 'Post text or image is required']);
}

$userId = (int)$_SESSION['user_id'];

try {
    $pdo = DB::conn();
    $pdo->beginTransaction();

    // create post
    $pdo->prepare("INSERT INTO posts (user_id, content_text, visibility, created_at) VALUES (?, ?, 'public', NOW())")
        ->execute([$userId, $content]);

    $postId = (int)$pdo->lastInsertId();

    // handle images (multi-ready)
    if (!empty($_FILES['images'])) {
        // Normalize files array for images[]
        $files = $_FILES['images'];
        $count = is_array($files['name']) ? count($files['name']) : 0;

        $maxFiles = 5;
        $maxBytes = 10 * 1024 * 1024;

        $year = date('Y');
        $month = date('m');

        $uploadDir = __DIR__ . "/../../uploads/posts/{$year}/{$month}";
        if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true)) {
            throw new RuntimeException('Failed to create upload directory');
        }
        $uploadDirReal = realpath($uploadDir);
        if ($uploadDirReal === false) throw new RuntimeException('Upload directory not accessible');

        for ($i = 0; $i < $count && $i < $maxFiles; $i++) {
            if ($files['error'][$i] !== UPLOAD_ERR_OK) continue;
            if ($files['size'][$i] > $maxBytes) continue;

            $tmp = $files['tmp_name'][$i];

            $info = @getimagesize($tmp);
            if ($info === false) continue;
            $mime = $info['mime'] ?? '';
            if (!in_array($mime, ['image/jpeg','image/png'], true)) continue;

            $src = load_image_from_tmp($tmp, $mime);
            if (!$src) continue;

            $rand = bin2hex(random_bytes(6));
            $file1080 = "{$year}/{$month}/post_{$postId}_{$rand}_1080.jpg";
            $file360  = "{$year}/{$month}/post_{$postId}_{$rand}_360.jpg";

            $path1080 = $uploadDirReal . DIRECTORY_SEPARATOR . basename($file1080);
            $path360  = $uploadDirReal . DIRECTORY_SEPARATOR . basename($file360);

            [$ok1, $w1, $h1] = resize_to_jpg($src, 1080, 85, $path1080);
            [$ok2, $w2, $h2] = resize_to_jpg($src, 360, 75, $path360);

            imagedestroy($src);

            if (!$ok1 || !$ok2) continue;

            $sizeBytes = (is_file($path1080) ? filesize($path1080) : null);

            $pdo->prepare("
              INSERT INTO post_media (post_id, media_type, file_1080, file_360, width, height, size_bytes, sort_order, created_at)
              VALUES (?, 'image', ?, ?, ?, ?, ?, ?, NOW())
            ")->execute([$postId, $file1080, $file360, $w1, $h1, $sizeBytes, $i]);
        }
    }

    $pdo->commit();

    $base = base_path();

    json_out(200, [
        'status' => 'success',
        'message' => 'Post created',
        'post_id' => $postId,
        'post_url' => $base . '/posts/' . $postId
    ]);

} catch (Throwable $e) {
    if ($pdo && $pdo->inTransaction()) $pdo->rollBack();
    error_log("create_post error: " . $e->getMessage());
    json_out(500, ['status' => 'error', 'message' => 'Server error']);
}
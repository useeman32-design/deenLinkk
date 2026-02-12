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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_out(405, ['status' => 'error', 'message' => 'Method not allowed']);
}

require_csrf();

if (empty($_SESSION['logged_in']) || empty($_SESSION['user_id'])) {
    json_out(401, ['status' => 'error', 'message' => 'Not logged in']);
}

if (empty($_FILES['profile_image']) || $_FILES['profile_image']['error'] !== UPLOAD_ERR_OK) {
    json_out(400, ['status' => 'error', 'message' => 'No image uploaded']);
}

$file = $_FILES['profile_image'];

// Allow larger uploads but compress server-side
$maxBytes = 10 * 1024 * 1024; // 10MB
if ($file['size'] > $maxBytes) {
    json_out(400, ['status' => 'error', 'message' => 'Image too large (max 10MB)']);
}

// Verify it is a real image
$tmp = $file['tmp_name'];
$info = @getimagesize($tmp);
if ($info === false) {
    json_out(400, ['status' => 'error', 'message' => 'Invalid image file']);
}

$mime = $info['mime'] ?? '';
$allowed = ['image/jpeg', 'image/png']; // JPG-only output, but allow PNG input too
if (!in_array($mime, $allowed, true)) {
    json_out(400, ['status' => 'error', 'message' => 'Only JPG or PNG images are allowed']);
}

// Load image
switch ($mime) {
    case 'image/jpeg':
        $src = @imagecreatefromjpeg($tmp);
        break;
    case 'image/png':
        $src = @imagecreatefrompng($tmp);
        break;
    default:
        $src = false;
}
if (!$src) {
    json_out(400, ['status' => 'error', 'message' => 'Failed to process image']);
}

$srcW = imagesx($src);
$srcH = imagesy($src);

// Resize keep aspect ratio to max 384
$maxDim = 384;
$scale = min($maxDim / $srcW, $maxDim / $srcH, 1); // never upscale
$dstW = (int)floor($srcW * $scale);
$dstH = (int)floor($srcH * $scale);

// First resize into a transparent canvas
$resized = imagecreatetruecolor($dstW, $dstH);
imagealphablending($resized, false);
imagesavealpha($resized, true);
$transparent = imagecolorallocatealpha($resized, 0, 0, 0, 127);
imagefilledrectangle($resized, 0, 0, $dstW, $dstH, $transparent);

imagecopyresampled($resized, $src, 0, 0, 0, 0, $dstW, $dstH, $srcW, $srcH);

// Convert to JPG: draw onto white background (handles PNG transparency)
$bg = imagecreatetruecolor($dstW, $dstH);
$white = imagecolorallocate($bg, 255, 255, 255);
imagefilledrectangle($bg, 0, 0, $dstW, $dstH, $white);
imagecopy($bg, $resized, 0, 0, 0, 0, $dstW, $dstH);

imagedestroy($src);
imagedestroy($resized);

$userId = (int)$_SESSION['user_id'];
$filename = 'profile_' . $userId . '_' . time() . '.jpg';

// Ensure upload dir exists
$uploadDir = __DIR__ . '/../../uploads/profile';
if (!is_dir($uploadDir)) {
    if (!mkdir($uploadDir, 0755, true)) {
        imagedestroy($bg);
        json_out(500, ['status' => 'error', 'message' => 'Failed to create upload directory']);
    }
}
$uploadDirReal = realpath($uploadDir);
if ($uploadDirReal === false) {
    imagedestroy($bg);
    json_out(500, ['status' => 'error', 'message' => 'Upload directory not accessible']);
}

$savePath = $uploadDirReal . DIRECTORY_SEPARATOR . $filename;

$quality = 82; // good balance
if (!imagejpeg($bg, $savePath, $quality)) {
    imagedestroy($bg);
    json_out(500, ['status' => 'error', 'message' => 'Failed to save image']);
}
imagedestroy($bg);

try {
    $pdo = DB::conn();

    // delete old file (optional)
    $stmt = $pdo->prepare("SELECT profile_image FROM users WHERE id = ? LIMIT 1");
    $stmt->execute([$userId]);
    $old = (string)$stmt->fetchColumn();

    $pdo->prepare("UPDATE users SET profile_image = ?, updated_at = NOW() WHERE id = ?")
        ->execute([$filename, $userId]);

    if ($old && $old !== 'default_profile.jpg' && $old !== $filename) {
        $oldPath = $uploadDirReal . DIRECTORY_SEPARATOR . basename($old);
        if (is_file($oldPath)) @unlink($oldPath);
    }

    // IMPORTANT: adjust casing to your folder name: deenLink vs deenlink
    $script = $_SERVER['SCRIPT_NAME'] ?? ''; 
// e.g. /deenLink/api/users/upload_profile_image.php  OR /api/users/upload_profile_image.php

$parts = explode('/', trim($script, '/')); 
// ['deenLink','api','users','upload_profile_image.php'] OR ['api','users','upload_profile_image.php']

$base = '';
if (count($parts) > 0 && strtolower($parts[0]) !== 'api') {
    // first folder is the project folder in dev
    $base = '/' . $parts[0];   // '/deenLink'
}
// else production root => base = ''

$publicUrl = $base . '/uploads/profile/' . $filename;

    json_out(200, [
        'status' => 'success',
        'message' => 'Profile image updated',
        'profile_image' => $filename,
        'profile_image_url' => $publicUrl
    ]);

} catch (Throwable $e) {
    error_log("upload_profile_image DB error: " . $e->getMessage());
    json_out(500, ['status' => 'error', 'message' => 'Server error']);
}
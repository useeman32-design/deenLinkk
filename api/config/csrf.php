<?php
declare(strict_types=1);

require_once __DIR__ . '/session.php';

function require_csrf(): void {
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    if (in_array($method, ['GET', 'HEAD', 'OPTIONS'], true)) {
        return;
    }

    $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    $sessionToken = $_SESSION['csrf_token'] ?? '';

    if ($token === '' || $sessionToken === '' || !hash_equals($sessionToken, $token)) {
        http_response_code(403);
        echo json_encode(['status' => 'error', 'message' => 'CSRF validation failed']);
        exit;
    }
}
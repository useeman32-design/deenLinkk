<?php
declare(strict_types=1);

/**
 * CORS configuration:
 * - If frontend and API are same-origin (http://localhost), CORS is not needed,
 *   but keeping it doesn't hurt as long as it's correct.
 * - With cookies, DO NOT use "*" as Access-Control-Allow-Origin.
 */

$allowedOrigins = [
    'http://localhost',
    'http://127.0.0.1',
    // Production (edit to your real domain):
    // 'https://deenlink.com',
    // 'https://www.deenlink.com',
];

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if ($origin && in_array($origin, $allowedOrigins, true)) {
    header("Access-Control-Allow-Origin: {$origin}");
    header('Access-Control-Allow-Credentials: true');
    header('Vary: Origin');
}

// If your frontend is same-origin, these headers usually won’t be used.
// They matter when you call API from a different origin (port/domain).
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-CSRF-Token');

header('Content-Type: application/json; charset=utf-8');

// Preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}
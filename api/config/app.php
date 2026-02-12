<?php
declare(strict_types=1);

function app_url(): string {
    $url = getenv('APP_URL');
    if ($url && $url !== '') return rtrim($url, '/');

    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    $scheme = $https ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';

    // Auto-detect folder (e.g. /deenLink) from current request
    $script = $_SERVER['SCRIPT_NAME'] ?? '';
    $parts = explode('/', trim($script, '/')); // e.g. ['deenLink','api','auth','login.php']
    $base = '';
    if (count($parts) > 0) {
        // assume first folder is project folder in dev
        $base = '/' . $parts[0];
    }

    return $scheme . '://' . $host . $base;
}
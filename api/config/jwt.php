<?php
declare(strict_types=1);

function b64url_encode(string $data): string {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

function jwt_sign_hs256(array $payload, string $secret): string {
    $header = ['alg' => 'HS256', 'typ' => 'JWT'];

    $h = b64url_encode(json_encode($header, JSON_UNESCAPED_SLASHES));
    $p = b64url_encode(json_encode($payload, JSON_UNESCAPED_SLASHES));

    $sig = hash_hmac('sha256', "{$h}.{$p}", $secret, true);
    $s = b64url_encode($sig);

    return "{$h}.{$p}.{$s}";
}
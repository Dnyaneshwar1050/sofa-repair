<?php
declare(strict_types=1);

// Minimal HS256 JWT implementation (no Composer required).

function jwt_base64url_encode(string $data): string
{
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

function jwt_base64url_decode(string $data): string
{
    $remainder = strlen($data) % 4;
    if ($remainder) {
        $data .= str_repeat('=', 4 - $remainder);
    }
    return base64_decode(strtr($data, '-_', '+/')) ?: '';
}

function jwt_sign(array $payload, string $secret, int $ttlSeconds = 86400): string
{
    $header = ['alg' => 'HS256', 'typ' => 'JWT'];
    $now = time();
    $payload = array_merge($payload, [
        'iat' => $now,
        'exp' => $now + $ttlSeconds,
    ]);

    $segments = [
        jwt_base64url_encode(json_encode($header, JSON_UNESCAPED_UNICODE)),
        jwt_base64url_encode(json_encode($payload, JSON_UNESCAPED_UNICODE)),
    ];
    $signingInput = implode('.', $segments);
    $signature = hash_hmac('sha256', $signingInput, $secret, true);
    $segments[] = jwt_base64url_encode($signature);
    return implode('.', $segments);
}

function jwt_verify(string $token, string $secret): ?array
{
    $parts = explode('.', $token);
    if (count($parts) !== 3) return null;

    [$h64, $p64, $s64] = $parts;
    $headerJson = jwt_base64url_decode($h64);
    $payloadJson = jwt_base64url_decode($p64);
    $sig = jwt_base64url_decode($s64);

    $header = json_decode($headerJson, true);
    $payload = json_decode($payloadJson, true);
    if (!is_array($header) || !is_array($payload)) return null;
    if (($header['alg'] ?? '') !== 'HS256') return null;

    $expected = hash_hmac('sha256', $h64 . '.' . $p64, $secret, true);
    if (!hash_equals($expected, $sig)) return null;

    if (isset($payload['exp']) && time() > (int)$payload['exp']) return null;
    return $payload;
}


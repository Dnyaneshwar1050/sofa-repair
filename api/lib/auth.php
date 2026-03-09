<?php
declare(strict_types=1);

require_once __DIR__ . '/jwt.php';
require_once __DIR__ . '/response.php';

// Keep in sync with InfinityFree env by editing this file (no .env required).
const JWT_SECRET = 'CHANGE_ME_TO_A_LONG_RANDOM_SECRET';

function require_user(PDO $pdo): array
{
    $token = bearer_token();
    if (!$token) json_response(['error' => 'Not authorized'], 401);

    $payload = jwt_verify($token, JWT_SECRET);
    if (!$payload) json_response(['error' => 'Invalid token'], 401);

    $userId = (int)($payload['uid'] ?? 0);
    if ($userId <= 0) json_response(['error' => 'Invalid token'], 401);

    $stmt = $pdo->prepare('SELECT id, name, email, phone, role, is_super_admin FROM users WHERE id = ? LIMIT 1');
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$user) json_response(['error' => 'User not found'], 401);
    return $user;
}

function require_role(PDO $pdo, array $roles): array
{
    $user = require_user($pdo);
    if (!in_array($user['role'] ?? '', $roles, true)) {
        json_response(['error' => 'Forbidden'], 403);
    }
    return $user;
}


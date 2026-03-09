<?php
declare(strict_types=1);

require_once __DIR__ . '/../db.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $stmt = db()->prepare('SELECT id, name, short_description, base_price, image_url FROM services WHERE is_disabled = 0 ORDER BY id DESC');
    $stmt->execute();
    echo json_encode($stmt->fetchAll(), JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['message' => 'Server error'], JSON_UNESCAPED_UNICODE);
}


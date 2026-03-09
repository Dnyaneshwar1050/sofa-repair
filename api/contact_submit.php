<?php
declare(strict_types=1);

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../includes/helpers.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['message' => 'Method not allowed']);
    exit;
}

csrf_verify();

$name = trim((string)($_POST['name'] ?? ''));
$email = trim((string)($_POST['email'] ?? ''));
$phone = trim((string)($_POST['phone'] ?? ''));
$message = trim((string)($_POST['message'] ?? ''));

if ($name === '' || $message === '') {
    http_response_code(400);
    echo json_encode(['message' => 'Name and message are required.']);
    exit;
}

try {
    $stmt = db()->prepare('INSERT INTO contact_messages (name, email, phone, message) VALUES (:name, :email, :phone, :message)');
    $stmt->execute([
        ':name' => $name,
        ':email' => ($email !== '' ? $email : null),
        ':phone' => ($phone !== '' ? $phone : null),
        ':message' => $message,
    ]);
    echo json_encode(['message' => 'Message sent.'], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['message' => 'Error saving message.'], JSON_UNESCAPED_UNICODE);
}


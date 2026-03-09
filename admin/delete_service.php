<?php
declare(strict_types=1);
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../includes/helpers.php';

require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/admin/dashboard.php');
}

csrf_verify();

$id = (int)($_POST['id'] ?? 0);
if ($id > 0) {
    $stmt = db()->prepare('DELETE FROM services WHERE id = ?');
    $stmt->execute([$id]);
}

redirect('/admin/dashboard.php');


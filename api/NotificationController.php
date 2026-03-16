<?php
require_once __DIR__ . '/../includes/db.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Fetch notifications for the user
    $userId = $_SESSION['user_id'];
    $stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id = ? AND tenant_id = ? ORDER BY created_at DESC LIMIT 20");
    $stmt->execute([$userId, CURRENT_TENANT_ID]);
    $notifications = $stmt->fetchAll();

    // Count unread
    $unreadStmt = $pdo->prepare("SELECT COUNT(*) as unread_count FROM notifications WHERE user_id = ? AND tenant_id = ? AND is_read = FALSE");
    $unreadStmt->execute([$userId, CURRENT_TENANT_ID]);
    $unreadCount = $unreadStmt->fetch()->unread_count;

    sendJson([
        'notifications' => $notifications,
        'unread_count' => $unreadCount
    ]);
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Mark as read
    $data = json_decode(file_get_contents('php://input'), true);
    $notificationId = $data['id'] ?? null;

    if ($notificationId) {
        $stmt = $pdo->prepare("UPDATE notifications SET is_read = TRUE WHERE id = ? AND user_id = ? AND tenant_id = ?");
        $stmt->execute([$notificationId, $_SESSION['user_id'], CURRENT_TENANT_ID]);
        sendJson(['success' => true]);
    } else {
        // Mark all as read
        $stmt = $pdo->prepare("UPDATE notifications SET is_read = TRUE WHERE user_id = ? AND tenant_id = ? AND is_read = FALSE");
        $stmt->execute([$_SESSION['user_id'], CURRENT_TENANT_ID]);
        sendJson(['success' => true]);
    }
} else {
    sendJson(['error' => 'Method not allowed'], 405);
}
?>

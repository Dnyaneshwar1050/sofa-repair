<?php
require_once __DIR__ . '/../includes/db.php';

requireRole(['admin', 'superadmin']);

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Fetch all settings for tenant
    $stmt = $pdo->prepare("SELECT setting_key, setting_value FROM app_settings WHERE tenant_id = ?");
    $stmt->execute([CURRENT_TENANT_ID]);
    $settingsRaw = $stmt->fetchAll();
    
    $settings = [];
    foreach ($settingsRaw as $row) {
        $settings[$row->setting_key] = $row->setting_value;
    }
    
    sendJson(['settings' => $settings]);
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Update settings
    $data = json_decode(file_get_contents('php://input'), true);
    $settingsToUpdate = $data['settings'] ?? [];

    if (!is_array($settingsToUpdate)) {
        sendJson(['error' => 'Invalid settings format'], 400);
    }

    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare("
            INSERT INTO app_settings (tenant_id, setting_key, setting_value) 
            VALUES (?, ?, ?) 
            ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)
        ");

        foreach ($settingsToUpdate as $key => $value) {
            // Basic validation on keys could be added here
            $stmt->execute([CURRENT_TENANT_ID, $key, (string)$value]);
        }
        $pdo->commit();
        
        // Log to notification
        // Fixed syntax error from missing escape on SQL below
        $notifyStmt = $pdo->prepare("INSERT INTO notifications (tenant_id, user_id, title, message, type, priority) VALUES (?, ?, 'Settings Updated', 'Application settings have been modified.', 'system', 'low')");
        $notifyStmt->execute([CURRENT_TENANT_ID, $_SESSION['user_id']]);

        sendJson(['success' => true, 'message' => 'Settings updated successfully']);
    } catch (Exception $e) {
        $pdo->rollBack();
        sendJson(['error' => 'Failed to update settings', 'details' => $e->getMessage()], 500);
    }
} else {
    sendJson(['error' => 'Method not allowed'], 405);
}
?>

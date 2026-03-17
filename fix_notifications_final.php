<?php
require_once __DIR__ . '/includes/db.php';

try {
    // Ensure all required columns exist
    $pdo->exec("ALTER TABLE notifications ADD COLUMN IF NOT EXISTS type ENUM('booking', 'contact', 'system') DEFAULT 'system'");
    $pdo->exec("ALTER TABLE notifications ADD COLUMN IF NOT EXISTS priority ENUM('low', 'normal', 'high') DEFAULT 'normal'");
    $pdo->exec("ALTER TABLE notifications ADD COLUMN IF NOT EXISTS status ENUM('read', 'unread', 'archived') DEFAULT 'unread'");
    $pdo->exec("ALTER TABLE notifications ADD COLUMN IF NOT EXISTS meta_data JSON NULL");
    
    echo "Notifications table structure verified.\n";

    // Clear and re-seed with data matching screenshots
    $pdo->exec("DELETE FROM notifications");
    
    $notifications = [
        [
            'title' => 'New Service Booking',
            'message' => 'New booking received from Tushar for Dominic Maxwell',
            'type' => 'booking',
            'priority' => 'high',
            'status' => 'unread',
            'created_at' => '2026-02-19 16:20:00',
            'meta_data' => json_encode([
                'service' => 'Dominic Maxwell',
                'customer' => 'Tushar',
                'amount' => '0',
                'phone' => '9146294066'
            ])
        ],
        [
            'title' => 'New Service Booking',
            'message' => 'New booking received from wyqepaza@mailinator.com for Scarlett Frank',
            'type' => 'booking',
            'priority' => 'high',
            'status' => 'unread',
            'created_at' => '2026-02-19 14:30:00',
            'meta_data' => json_encode([
                'service' => 'Scarlett Frank',
                'customer' => 'wyqepaza@mailinator.com',
                'amount' => '0',
                'phone' => '+1 (371) 953-2015'
            ])
        ],
        [
            'title' => 'New Service Booking',
            'message' => 'New booking received from bhunatarbalvant51@gmail.com for Sofa Foam Mattress',
            'type' => 'booking',
            'priority' => 'high',
            'status' => 'unread',
            'created_at' => '2026-01-25 10:00:00',
            'meta_data' => json_encode([
                'service' => 'Sofa Foam Mattress',
                'customer' => 'bhunatarbalvant51@gmail.com',
                'amount' => '0',
                'phone' => '8780231871'
            ])
        ]
    ];

    $stmt = $pdo->prepare("INSERT INTO notifications (title, message, type, priority, status, created_at, meta_data) VALUES (?, ?, ?, ?, ?, ?, ?)");
    foreach ($notifications as $n) {
        $stmt->execute([$n['title'], $n['message'], $n['type'], $n['priority'], $n['status'], $n['created_at'], $n['meta_data']]);
    }
    echo "Detailed test notifications seeded successfully.\n";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>

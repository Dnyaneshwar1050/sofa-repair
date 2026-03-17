<?php
require_once __DIR__ . '/includes/db.php';

try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS notifications (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NULL,
        title VARCHAR(255) NOT NULL,
        message TEXT NOT NULL,
        type ENUM('booking', 'contact', 'system') DEFAULT 'system',
        priority ENUM('low', 'normal', 'high') DEFAULT 'normal',
        status ENUM('read', 'unread', 'archived') DEFAULT 'unread',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX (user_id),
        INDEX (status)
    )");
    echo "Notifications table created successfully.\n";

    // Seed some test notifications if empty
    $count = $pdo->query("SELECT COUNT(*) FROM notifications")->fetchColumn();
    if ($count == 0) {
        $pdo->exec("INSERT INTO notifications (title, message, type, priority, status, created_at) VALUES 
            ('New Service Booking', 'New booking received from bhunatarbalvant51@gmail.com for Sofa Foam Mattress', 'booking', 'high', 'unread', '2026-01-25 10:00:00'),
            ('New Service Booking', 'New booking received from wyqepaza@mailinator.com for Scarlett Frank', 'booking', 'high', 'unread', '2026-02-19 14:30:00'),
            ('New Service Booking', 'New booking received from Tushar for Dominic Maxwell', 'booking', 'high', 'unread', '2026-02-19 16:20:00')
        ");
        echo "Test notifications seeded.\n";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>

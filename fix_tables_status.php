<?php
require_once __DIR__ . '/includes/db.php';

try {
    // Add is_disabled to categories if it doesn't exist
    $pdo->exec("ALTER TABLE categories ADD COLUMN IF NOT EXISTS is_disabled TINYINT(1) DEFAULT 0");
    echo "Categories table updated successfully.\n";
    
    // Also ensure services has it (just in case)
    $pdo->exec("ALTER TABLE services ADD COLUMN IF NOT EXISTS is_disabled TINYINT(1) DEFAULT 0");
    echo "Services table verified.\n";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>

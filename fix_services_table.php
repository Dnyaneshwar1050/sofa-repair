<?php
require_once __DIR__ . '/includes/db.php';

$alterStatements = [
    "ALTER TABLE services ADD COLUMN max_expected_price DECIMAL(10,2) DEFAULT 0",
    "ALTER TABLE services ADD COLUMN service_options JSON DEFAULT NULL",
    "ALTER TABLE services ADD COLUMN gallery_images JSON DEFAULT NULL",
];

foreach ($alterStatements as $sql) {
    try {
        $pdo->exec($sql);
        echo "OK: $sql\n";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate column') !== false) {
            echo "SKIP (already exists): $sql\n";
        } else {
            echo "ERROR: " . $e->getMessage() . "\n";
        }
    }
}

echo "\nDone. Current columns:\n";
$cols = $pdo->query("SHOW COLUMNS FROM services")->fetchAll(PDO::FETCH_ASSOC);
foreach ($cols as $col) {
    echo "  - " . $col['Field'] . " (" . $col['Type'] . ")\n";
}

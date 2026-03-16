<?php
// run_sql.php
require_once __DIR__ . '/includes/db.php';
try {
    $sql = file_get_contents(__DIR__ . '/alter_blogs.sql');
    $pdo->exec($sql);
    echo "Success\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

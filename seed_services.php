<?php
require_once __DIR__ . '/includes/db.php';

$data = [
    'Electricity' => [
        'Circuit Breaker Repair',
        'New Wiring Installation'
    ],
    'Painting & Wall Care' => [
        'Safety Net Installation',
        'False Ceiling Setup',
        'Floor Tiling',
        'Interior Wall Painting',
        'Exterior Painting'
    ],
    'Sofa Repairing' => [
        'Sofa Upholstery & Repair',
        'Sofa Mechanism Service',
        'Sofa Upholstery',
        'Sofa Frame Repair',
        'Sofa Cushion Replacement'
    ],
    'Furniture/Carpenter' => [
        'Custom Furniture Design',
        'Wardrobe Assembly/Repair'
    ],
    'Plumbing' => [
        'Blocked Drain Cleaning',
        'Toilet/Faucet Repair'
    ]
];

foreach ($data as $catName => $serviceNames) {
    // Check if category exists
    $stmt = $pdo->prepare("SELECT id FROM categories WHERE name = ?");
    $stmt->execute([$catName]);
    $catId = $stmt->fetchColumn();

    if (!$catId) {
        $stmt = $pdo->prepare("INSERT INTO categories (tenant_id, name, description) VALUES (1, ?, ?)");
        $stmt->execute([$catName, "Professional $catName services"]);
        $catId = $pdo->lastInsertId();
        echo "Added category $catName (ID $catId)\n";
    }

    foreach ($serviceNames as $serviceName) {
        // Check if service exists
        $stmt = $pdo->prepare("SELECT id FROM services WHERE name = ? AND category_id = ?");
        $stmt->execute([$serviceName, $catId]);
        $serviceId = $stmt->fetchColumn();

        if (!$serviceId) {
            $stmt = $pdo->prepare("INSERT INTO services (tenant_id, category_id, name, description, base_price, image) VALUES (1, ?, ?, ?, 999, 'default-service.png')");
            $stmt->execute([$catId, $serviceName, "Expert $serviceName service."]);
            echo "Added service $serviceName to category $catName\n";
        }
    }
}

echo "Done.\n";

<?php
require_once __DIR__ . '/../includes/db.php';

requireLogin();

// Dummy Mocking for Cloudinary Image Upload
function mockCloudinaryUpload($tmpFile, $filename) {
    // In a real scenario, this would POST to Cloudinary API and return URL
    // Here we just pretend it was uploaded somewhere securely
    $ext = pathinfo($filename, PATHINFO_EXTENSION);
    return "https://res.cloudinary.com/demo/image/upload/v1234567/mock_" . uniqid() . ".$ext";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bookingId = $_POST['booking_id'] ?? null;
    $serviceId = $_POST['service_id'] ?? null;
    $rating = (int)($_POST['rating'] ?? 5);
    $qualityRating = (int)($_POST['quality_rating'] ?? 5);
    $communicationRating = (int)($_POST['communication_rating'] ?? 5);
    $valueRating = (int)($_POST['value_rating'] ?? 5);
    $comment = $_POST['comment'] ?? '';

    if (!$bookingId || !$serviceId) {
        sendJson(['error' => 'Missing booking_id or service_id.'], 400);
    }

    // Verify booking belongs to user and is completed
    $stmt = $pdo->prepare("SELECT status FROM bookings WHERE id = ? AND user_id = ? AND tenant_id = ? AND status = 'completed'");
    $stmt->execute([$bookingId, $_SESSION['user_id'], CURRENT_TENANT_ID]);
    if (!$stmt->fetch()) {
        sendJson(['error' => 'Invalid booking or booking not completed.'], 403);
    }

    // Process Images
    $imageUrls = [];
    if (!empty($_FILES['images']['name'][0])) {
        // Max 5 images
        $fileCount = min(count($_FILES['images']['name']), 5);
        for ($i = 0; $i < $fileCount; $i++) {
            $tmpName = $_FILES['images']['tmp_name'][$i];
            $fileName = $_FILES['images']['name'][$i];
            if (is_uploaded_file($tmpName)) {
                $imageUrls[] = mockCloudinaryUpload($tmpName, $fileName);
            }
        }
    }
    
    $imagesJson = json_encode($imageUrls);

    // Insert Review
    $insertStmt = $pdo->prepare("
        INSERT INTO reviews (tenant_id, booking_id, user_id, service_id, rating, quality_rating, communication_rating, value_rating, comment, images) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $insertStmt->execute([
        CURRENT_TENANT_ID, $bookingId, $_SESSION['user_id'], $serviceId, $rating, $qualityRating, $communicationRating, $valueRating, $comment, $imagesJson
    ]);

    // Create a notification for Admin
    $notifyStmt = $pdo->prepare("INSERT INTO notifications (tenant_id, user_id, title, message, type, priority) VALUES (?, ?, 'New Review Submitted', 'A new review was submitted for a service.', 'system', 'low')");
    // Get an admin ID roughly
    $adminStmt = $pdo->prepare("SELECT id FROM users WHERE tenant_id = ? AND role IN ('admin', 'superadmin') LIMIT 1");
    $adminStmt->execute([CURRENT_TENANT_ID]);
    $admin = $adminStmt->fetch();
    if ($admin) {
         $notifyStmt->execute([CURRENT_TENANT_ID, $admin->id]);
    }

    sendJson(['success' => true, 'message' => 'Review submitted successfully.']);
} elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    // Vote Helpfulness
    $data = json_decode(file_get_contents('php://input'), true);
    $reviewId = $data['review_id'] ?? null;
    
    if ($reviewId) {
        $stmt = $pdo->prepare("UPDATE reviews SET helpfulness_votes = helpfulness_votes + 1 WHERE id = ? AND tenant_id = ?");
        $stmt->execute([$reviewId, CURRENT_TENANT_ID]);
        sendJson(['success' => true]);
    } else {
        sendJson(['error' => 'No review ID provided.'], 400);
    }
} else {
    sendJson(['error' => 'Method not allowed'], 405);
}
?>

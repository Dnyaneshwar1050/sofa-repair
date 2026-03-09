<?php
declare(strict_types=1);

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../includes/helpers.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['message' => 'Method not allowed']);
    exit;
}

csrf_verify();

$serviceId = isset($_POST['service_id']) && $_POST['service_id'] !== '' ? (int)$_POST['service_id'] : null;
$serviceNameCustom = trim((string)($_POST['service_name_custom'] ?? ''));
$customerName = trim((string)($_POST['customer_name'] ?? ''));
$email = trim((string)($_POST['email'] ?? ''));
$phone = trim((string)($_POST['phone'] ?? ''));
$budget = trim((string)($_POST['budget'] ?? ''));

$houseNo = trim((string)($_POST['address_house_no'] ?? ''));
$area = trim((string)($_POST['address_area'] ?? ''));
$city = trim((string)($_POST['address_city'] ?? ''));
$pincode = trim((string)($_POST['address_pincode'] ?? ''));
$notes = trim((string)($_POST['notes'] ?? ''));

if ($customerName === '' || $phone === '' || $houseNo === '' || $area === '' || $city === '' || $pincode === '') {
    http_response_code(400);
    echo json_encode(['message' => 'Please fill all required fields.']);
    exit;
}

try {
    $serviceName = $serviceNameCustom;
    if ($serviceId) {
        $s = db()->prepare('SELECT name FROM services WHERE id = ? AND is_disabled = 0');
        $s->execute([$serviceId]);
        $row = $s->fetch();
        if (!$row) {
            http_response_code(400);
            echo json_encode(['message' => 'Selected service not found.']);
            exit;
        }
        $serviceName = (string)$row['name'];
    }

    if ($serviceName === '') {
        $serviceName = 'General request';
    }

    $stmt = db()->prepare('
        INSERT INTO bookings
          (service_id, service_name, customer_name, email, phone, budget,
           address_house_no, address_area, address_city, address_pincode, notes)
        VALUES
          (:service_id, :service_name, :customer_name, :email, :phone, :budget,
           :house_no, :area, :city, :pincode, :notes)
    ');

    $stmt->execute([
        ':service_id' => $serviceId,
        ':service_name' => $serviceName,
        ':customer_name' => $customerName,
        ':email' => ($email !== '' ? $email : null),
        ':phone' => $phone,
        ':budget' => ($budget !== '' ? (int)$budget : null),
        ':house_no' => $houseNo,
        ':area' => $area,
        ':city' => $city,
        ':pincode' => $pincode,
        ':notes' => ($notes !== '' ? $notes : null),
    ]);

    echo json_encode(['message' => 'Booking submitted successfully.'], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['message' => 'Error saving booking.'], JSON_UNESCAPED_UNICODE);
}


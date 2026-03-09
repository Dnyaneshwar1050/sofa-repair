<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/lib/response.php';
require_once __DIR__ . '/lib/auth.php';

$pdo = db();

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$uri = $_SERVER['REQUEST_URI'] ?? '/api';
$path = parse_url($uri, PHP_URL_PATH) ?: '/api';

// Remove leading "/api"
$path = preg_replace('#^/api#', '', $path) ?: '/';
$path = $path === '' ? '/' : $path;

// Convenience
function now_iso(): string { return gmdate('c'); }

// ---- Routing ----

// Health
if ($method === 'GET' && $path === '/health') {
    json_response(['status' => 'OK', 'timestamp' => now_iso()]);
}

// ---------------------------
// AUTH: /auth/register, /auth/login, /auth/complete-registration
// ---------------------------
if (preg_match('#^/auth/register$#', $path) && $method === 'POST') {
    $body = get_json_body();
    $phone = trim((string)($body['phone'] ?? ''));
    $email = trim((string)($body['email'] ?? ''));
    $name = trim((string)($body['name'] ?? ''));
    $password = (string)($body['password'] ?? '');

    if ($phone === '' || $password === '') {
        json_response(['message' => 'Phone and password are required'], 400);
    }

    // Create customer user
    try {
        $stmt = $pdo->prepare('
          INSERT INTO users (name, email, phone, password_hash, role)
          VALUES (:name, :email, :phone, :ph, :role)
        ');
        $stmt->execute([
            ':name' => ($name !== '' ? $name : null),
            ':email' => ($email !== '' ? $email : null),
            ':phone' => $phone,
            ':ph' => password_hash($password, PASSWORD_DEFAULT),
            ':role' => 'customer',
        ]);
        json_response(['message' => 'Registered'], 201);
    } catch (Throwable $e) {
        json_response(['message' => 'User already exists or invalid data'], 400);
    }
}

if (preg_match('#^/auth/login$#', $path) && $method === 'POST') {
    $body = get_json_body();
    $phoneOrEmail = trim((string)($body['phone'] ?? $body['email'] ?? ''));
    $password = (string)($body['password'] ?? '');

    if ($phoneOrEmail === '' || $password === '') {
        json_response(['message' => 'Missing credentials'], 400);
    }

    $stmt = $pdo->prepare('
      SELECT id, name, email, phone, role, is_super_admin, password_hash
      FROM users
      WHERE phone = :v OR email = :v
      LIMIT 1
    ');
    $stmt->execute([':v' => $phoneOrEmail]);
    $u = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$u || empty($u['password_hash']) || !password_verify($password, (string)$u['password_hash'])) {
        json_response(['message' => 'Invalid credentials'], 401);
    }

    $token = jwt_sign(['uid' => (int)$u['id'], 'role' => (string)$u['role'], 'super' => (int)($u['is_super_admin'] ?? 0)], JWT_SECRET, 60 * 60 * 24 * 14);
    json_response([
        'token' => $token,
        'user' => [
            'id' => (int)$u['id'],
            'name' => $u['name'],
            'email' => $u['email'],
            'phone' => $u['phone'],
            'role' => $u['role'],
            'isSuperAdmin' => (bool)($u['is_super_admin'] ?? 0),
        ],
    ]);
}

if (preg_match('#^/auth/complete-registration$#', $path) && $method === 'PUT') {
    $body = get_json_body();
    $phone = trim((string)($body['phone'] ?? ''));
    $password = (string)($body['password'] ?? '');
    if ($phone === '' || $password === '') json_response(['message' => 'Phone and password required'], 400);

    $stmt = $pdo->prepare('UPDATE users SET password_hash = :ph WHERE phone = :phone');
    $stmt->execute([':ph' => password_hash($password, PASSWORD_DEFAULT), ':phone' => $phone]);
    json_response(['message' => 'Registration completed']);
}

// ---------------------------
// SERVICES: /services, /services/:id, /services/categories
// ---------------------------
if (preg_match('#^/services/categories$#', $path) && $method === 'GET') {
    $stmt = $pdo->prepare('SELECT id, name, is_disabled FROM categories WHERE is_disabled = 0 ORDER BY name');
    $stmt->execute();
    json_response($stmt->fetchAll(PDO::FETCH_ASSOC));
}

if (preg_match('#^/services$#', $path) && $method === 'GET') {
    $categoryId = isset($_GET['categoryId']) ? (int)$_GET['categoryId'] : 0;
    $search = trim((string)($_GET['search'] ?? ''));

    $sql = '
      SELECT s.*
      FROM services s
      JOIN categories c ON c.id = s.category_id
      WHERE s.is_disabled = 0 AND c.is_disabled = 0
    ';
    $params = [];
    if ($categoryId > 0) {
        $sql .= ' AND s.category_id = :cid';
        $params[':cid'] = $categoryId;
    }
    if ($search !== '') {
        $sql .= ' AND (s.name LIKE :q OR s.short_description LIKE :q)';
        $params[':q'] = '%' . $search . '%';
    }
    $sql .= ' ORDER BY s.id DESC';

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Decode JSON fields to match MERN shape
    foreach ($rows as &$r) {
        $r['images'] = json_decode((string)($r['images_json'] ?? '[]'), true) ?: [];
        $r['options'] = json_decode((string)($r['options_json'] ?? '[]'), true) ?: [];
        $r['averageRating'] = (float)($r['average_rating'] ?? 0);
        $r['reviewCount'] = (int)($r['review_count'] ?? 0);
        $r['basePrice'] = (int)($r['base_price'] ?? 0);
        $r['priceUpperRange'] = $r['price_upper_range'] !== null ? (int)$r['price_upper_range'] : null;
        $r['shortDescription'] = $r['short_description'];
        $r['isDisabled'] = (bool)($r['is_disabled'] ?? 0);
        $r['_id'] = (string)$r['id'];
        unset($r['images_json'], $r['options_json'], $r['average_rating'], $r['review_count'], $r['base_price'], $r['price_upper_range'], $r['short_description'], $r['is_disabled']);
    }

    json_response($rows);
}

if (preg_match('#^/services/(\d+)$#', $path, $m) && $method === 'GET') {
    $id = (int)$m[1];
    $stmt = $pdo->prepare('
      SELECT s.*, c.name AS category_name, c.is_disabled AS category_disabled
      FROM services s
      JOIN categories c ON c.id = s.category_id
      WHERE s.id = ?
      LIMIT 1
    ');
    $stmt->execute([$id]);
    $r = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$r || (int)$r['is_disabled'] === 1 || (int)$r['category_disabled'] === 1) {
        json_response(['message' => 'Service not found'], 404);
    }

    $r['images'] = json_decode((string)($r['images_json'] ?? '[]'), true) ?: [];
    $r['options'] = json_decode((string)($r['options_json'] ?? '[]'), true) ?: [];
    $r['averageRating'] = (float)($r['average_rating'] ?? 0);
    $r['reviewCount'] = (int)($r['review_count'] ?? 0);
    $r['basePrice'] = (int)($r['base_price'] ?? 0);
    $r['priceUpperRange'] = $r['price_upper_range'] !== null ? (int)$r['price_upper_range'] : null;
    $r['shortDescription'] = $r['short_description'];
    $r['isDisabled'] = (bool)($r['is_disabled'] ?? 0);
    $r['_id'] = (string)$r['id'];
    $r['category'] = ['_id' => (string)$r['category_id'], 'name' => $r['category_name'], 'isDisabled' => (bool)$r['category_disabled']];

    unset($r['images_json'], $r['options_json'], $r['average_rating'], $r['review_count'], $r['base_price'], $r['price_upper_range'], $r['short_description'], $r['is_disabled'], $r['category_name'], $r['category_disabled']);
    json_response($r);
}

// ---------------------------
// BOOKINGS: /bookings (POST), /bookings/my (GET)
// ---------------------------
if ($path === '/bookings' && $method === 'POST') {
    // Supports both JSON and multipart/form-data (FormData)
    $isMultipart = str_contains($_SERVER['CONTENT_TYPE'] ?? '', 'multipart/form-data');
    $body = $isMultipart ? $_POST : get_json_body();

    $serviceId = $body['serviceId'] ?? null;
    $customServiceName = trim((string)($body['customServiceName'] ?? ''));
    $name = trim((string)($body['name'] ?? ''));
    $email = trim((string)($body['email'] ?? ''));
    $phone = trim((string)($body['phone'] ?? ''));
    $budget = isset($body['budget']) && $body['budget'] !== '' ? (int)$body['budget'] : null;
    $notes = trim((string)($body['notes'] ?? ''));
    $address = $body['address'] ?? null;

    // Attempt to read address from flat fields if needed
    if (!is_array($address)) {
        $address = [
            'houseNo' => $body['address']['houseNo'] ?? $body['address_house_no'] ?? '',
            'area' => $body['address']['area'] ?? $body['address_area'] ?? '',
            'city' => $body['address']['city'] ?? $body['address_city'] ?? '',
            'pincode' => $body['address']['pincode'] ?? $body['address_pincode'] ?? '',
            'street' => $body['address']['street'] ?? '',
            'landmark' => $body['address']['landmark'] ?? '',
        ];
    }

    if ($phone === '') json_response(['message' => 'Mobile number is required for booking.'], 400);
    if (empty($address['houseNo']) || empty($address['area']) || empty($address['city']) || empty($address['pincode'])) {
        json_response(['message' => 'Missing required address details (houseNo, area, city, pincode).'], 400);
    }

    // Logged-in user if token valid; otherwise lazy-create guest user by phone
    $userId = null;
    $isGuestBookingPrompt = false;
    $token = bearer_token();
    if ($token) {
        $payload = jwt_verify($token, JWT_SECRET);
        if ($payload && !empty($payload['uid'])) $userId = (int)$payload['uid'];
    }

    $userRow = null;
    if (!$userId) {
        // Find or create customer by phone/email
        $stmt = $pdo->prepare('SELECT id, name, email, phone, role, password_hash FROM users WHERE phone = :p OR (:e IS NOT NULL AND email = :e) LIMIT 1');
        $stmt->execute([':p' => $phone, ':e' => ($email !== '' ? $email : null)]);
        $userRow = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$userRow) {
            $stmt = $pdo->prepare('INSERT INTO users (name, email, phone, password_hash, role) VALUES (:n, :e, :p, NULL, :r)');
            $stmt->execute([
                ':n' => ($name !== '' ? $name : ($email !== '' ? $email : ('Guest-' . $phone))),
                ':e' => ($email !== '' ? $email : null),
                ':p' => $phone,
                ':r' => 'customer',
            ]);
            $userId = (int)$pdo->lastInsertId();
            $isGuestBookingPrompt = true;
        } else {
            $userId = (int)$userRow['id'];
            if (empty($userRow['password_hash'])) $isGuestBookingPrompt = true;
        }
    }

    // Resolve service
    $serviceName = '';
    $serviceDbId = null;
    $providerId = null;
    $basePrice = 0;

    if ($serviceId) {
        $sid = (int)$serviceId;
        $stmt = $pdo->prepare('SELECT id, name, provider_id, base_price FROM services WHERE id = ? LIMIT 1');
        $stmt->execute([$sid]);
        $srv = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$srv) json_response(['message' => 'Service not found'], 404);
        $serviceDbId = (int)$srv['id'];
        $serviceName = (string)$srv['name'];
        $providerId = (int)($srv['provider_id'] ?? 0);
        $basePrice = (int)($srv['base_price'] ?? 0);
    } elseif ($customServiceName !== '') {
        $serviceName = $customServiceName;
    } else {
        json_response(['message' => 'Either serviceId or customServiceName is required.'], 400);
    }

    // Option parsing (frontend sends JSON string sometimes)
    $option = $body['option'] ?? null;
    if (is_string($option)) {
        $decoded = json_decode($option, true);
        $option = is_array($decoded) ? $decoded : null;
    }
    $optionPrice = is_array($option) && isset($option['price']) ? (int)$option['price'] : 0;
    $totalAmount = $basePrice + $optionPrice;

    // Items array matches MERN-ish format
    $items = [[
        'service' => $serviceDbId ? (string)$serviceDbId : null,
        'serviceName' => $serviceName,
        'selectedOption' => is_array($option) ? ['name' => ($option['name'] ?? null), 'price' => $optionPrice] : null,
        'quantity' => 1,
        'images' => [], // keep empty if you upload directly to Cloudinary from React
    ]];

    $stmt = $pdo->prepare('
      INSERT INTO bookings
        (user_id, provider_id, items_json, total_amount, budget, address_json, phone, notes, status)
      VALUES
        (:uid, :pid, :items, :total, :budget, :addr, :phone, :notes, "Pending")
    ');
    $stmt->execute([
        ':uid' => $userId,
        ':pid' => ($providerId ?: null),
        ':items' => json_encode($items, JSON_UNESCAPED_UNICODE),
        ':total' => $totalAmount,
        ':budget' => $budget,
        ':addr' => json_encode($address, JSON_UNESCAPED_UNICODE),
        ':phone' => $phone,
        ':notes' => ($notes !== '' ? $notes : null),
    ]);

    $id = (int)$pdo->lastInsertId();

    // Create admin notifications for new booking (used by React admin notifications page)
    try {
        $adminStmt = $pdo->prepare('SELECT id FROM users WHERE role = "admin"');
        $adminStmt->execute();
        $admins = $adminStmt->fetchAll(PDO::FETCH_ASSOC);
        $meta = json_encode(['bookingId' => (string)$id, 'userId' => (string)$userId], JSON_UNESCAPED_UNICODE);
        $ins = $pdo->prepare('INSERT INTO notifications (user_id, booking_id, title, message, type, meta_json, is_read) VALUES (?,?,?,?,?,?,0)');
        foreach ($admins as $a) {
            $ins->execute([
                (int)$a['id'],
                $id,
                'New booking request',
                'A new booking request was created.',
                'booking',
                $meta
            ]);
        }
    } catch (Throwable $e) {
        // Don't fail booking if notifications fail
    }

    json_response([
        '_id' => (string)$id,
        'user' => (string)$userId,
        'provider' => $providerId ? (string)$providerId : null,
        'items' => $items,
        'totalAmount' => $totalAmount,
        'budget' => $budget,
        'address' => $address,
        'phone' => $phone,
        'notes' => $notes,
        'status' => 'Pending',
        'isGuestBookingPrompt' => $isGuestBookingPrompt,
        'createdAt' => now_iso(),
    ], 201);
}

if ($path === '/bookings/my' && $method === 'GET') {
    $user = require_role($pdo, ['customer', 'provider', 'admin']);
    $stmt = $pdo->prepare('SELECT * FROM bookings WHERE user_id = ? ORDER BY id DESC');
    $stmt->execute([(int)$user['id']]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as &$r) {
        $r['_id'] = (string)$r['id'];
        $r['items'] = json_decode((string)($r['items_json'] ?? '[]'), true) ?: [];
        $r['address'] = json_decode((string)($r['address_json'] ?? '{}'), true) ?: [];
        $r['totalAmount'] = (int)$r['total_amount'];
        unset($r['items_json'], $r['address_json'], $r['total_amount']);
    }
    json_response($rows);
}

// ---------------------------
// ADMIN: users, services, bookings, categories (JWT protected)
// ---------------------------
if ($path === '/admin/users' && $method === 'GET') {
    require_role($pdo, ['admin']);
    $stmt = $pdo->prepare('SELECT id, name, email, phone, role, is_super_admin, created_at FROM users ORDER BY id DESC');
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as &$u) {
        $u['_id'] = (string)$u['id'];
        $u['isSuperAdmin'] = (bool)($u['is_super_admin'] ?? 0);
        unset($u['is_super_admin']);
    }
    json_response($rows);
}

if (preg_match('#^/admin/users/(\d+)$#', $path, $m) && $method === 'PUT') {
    require_role($pdo, ['admin']);
    $id = (int)$m[1];
    $body = get_json_body();
    $role = (string)($body['role'] ?? '');
    if (!in_array($role, ['customer','provider','admin'], true)) json_response(['message' => 'Invalid role'], 400);
    $stmt = $pdo->prepare('UPDATE users SET role = ? WHERE id = ?');
    $stmt->execute([$role, $id]);
    json_response(['message' => 'Updated']);
}

if (preg_match('#^/admin/users/(\d+)$#', $path, $m) && $method === 'DELETE') {
    require_role($pdo, ['admin']);
    $id = (int)$m[1];
    $stmt = $pdo->prepare('DELETE FROM users WHERE id = ?');
    $stmt->execute([$id]);
    json_response(['message' => 'Deleted']);
}

if ($path === '/admin/services' && $method === 'GET') {
    $user = require_role($pdo, ['admin','provider']);
    $sql = 'SELECT * FROM services';
    $params = [];
    if (($user['role'] ?? '') === 'provider') {
        $sql .= ' WHERE provider_id = :pid';
        $params[':pid'] = (int)$user['id'];
    }
    $sql .= ' ORDER BY id DESC';
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as &$r) {
        $r['_id'] = (string)$r['id'];
        $r['images'] = json_decode((string)($r['images_json'] ?? '[]'), true) ?: [];
        $r['options'] = json_decode((string)($r['options_json'] ?? '[]'), true) ?: [];
        $r['shortDescription'] = $r['short_description'];
        $r['basePrice'] = (int)$r['base_price'];
        $r['priceUpperRange'] = $r['price_upper_range'] !== null ? (int)$r['price_upper_range'] : null;
        $r['averageRating'] = (float)$r['average_rating'];
        $r['reviewCount'] = (int)$r['review_count'];
        $r['isDisabled'] = (bool)$r['is_disabled'];
        unset($r['images_json'],$r['options_json'],$r['short_description'],$r['base_price'],$r['price_upper_range'],$r['average_rating'],$r['review_count'],$r['is_disabled']);
    }
    json_response($rows);
}

if ($path === '/admin/services' && $method === 'POST') {
    $user = require_role($pdo, ['admin','provider']);
    // FormData supported
    $name = trim((string)($_POST['name'] ?? ''));
    $short = trim((string)($_POST['shortDescription'] ?? $_POST['short_description'] ?? ''));
    $base = isset($_POST['basePrice']) ? (int)$_POST['basePrice'] : (isset($_POST['base_price']) ? (int)$_POST['base_price'] : 0);
    $upper = isset($_POST['priceUpperRange']) && $_POST['priceUpperRange'] !== '' ? (int)$_POST['priceUpperRange'] : null;
    $categoryId = isset($_POST['category']) ? (int)$_POST['category'] : (isset($_POST['categoryId']) ? (int)$_POST['categoryId'] : 0);
    $images = $_POST['images'] ?? null; // frontend might send JSON string
    $options = $_POST['options'] ?? null;

    if ($name === '' || $short === '' || $categoryId <= 0) {
        json_response(['message' => 'Missing required fields'], 400);
    }
    if (is_string($images)) $images = json_decode($images, true);
    if (is_string($options)) $options = json_decode($options, true);
    if (!is_array($images)) $images = [];
    if (!is_array($options)) $options = [];

    $providerId = (($user['role'] ?? '') === 'provider') ? (int)$user['id'] : (isset($_POST['provider']) ? (int)$_POST['provider'] : (int)$user['id']);

    $stmt = $pdo->prepare('
      INSERT INTO services
        (name, category_id, provider_id, short_description, base_price, price_upper_range, images_json, options_json, is_disabled)
      VALUES
        (:n,:cid,:pid,:sd,:bp,:ur,:img,:opt,0)
    ');
    $stmt->execute([
        ':n' => $name,
        ':cid' => $categoryId,
        ':pid' => $providerId,
        ':sd' => $short,
        ':bp' => $base,
        ':ur' => $upper,
        ':img' => json_encode($images, JSON_UNESCAPED_UNICODE),
        ':opt' => json_encode($options, JSON_UNESCAPED_UNICODE),
    ]);
    json_response(['message' => 'Created'], 201);
}

if (preg_match('#^/admin/services/(\d+)$#', $path, $m) && $method === 'PUT') {
    $user = require_role($pdo, ['admin','provider']);
    $id = (int)$m[1];

    // Support both JSON and FormData
    $isMultipart = str_contains($_SERVER['CONTENT_TYPE'] ?? '', 'multipart/form-data');
    $body = $isMultipart ? $_POST : get_json_body();

    $name = trim((string)($body['name'] ?? ''));
    $short = trim((string)($body['shortDescription'] ?? $body['short_description'] ?? ''));
    $base = isset($body['basePrice']) ? (int)$body['basePrice'] : (isset($body['base_price']) ? (int)$body['base_price'] : null);
    $upper = isset($body['priceUpperRange']) ? ($body['priceUpperRange'] !== '' ? (int)$body['priceUpperRange'] : null) : null;
    $categoryId = isset($body['category']) ? (int)$body['category'] : (isset($body['categoryId']) ? (int)$body['categoryId'] : 0);

    $images = $body['images'] ?? null;
    $options = $body['options'] ?? null;
    if (is_string($images)) $images = json_decode($images, true);
    if (is_string($options)) $options = json_decode($options, true);
    if (!is_array($images)) $images = null;
    if (!is_array($options)) $options = null;

    // Provider restriction
    if (($user['role'] ?? '') === 'provider') {
        $stmt = $pdo->prepare('SELECT provider_id FROM services WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row || (int)$row['provider_id'] !== (int)$user['id']) json_response(['message' => 'Forbidden'], 403);
    }

    $stmt = $pdo->prepare('
      UPDATE services SET
        name = COALESCE(:n, name),
        short_description = COALESCE(:sd, short_description),
        base_price = COALESCE(:bp, base_price),
        price_upper_range = :ur,
        category_id = COALESCE(:cid, category_id),
        images_json = COALESCE(:img, images_json),
        options_json = COALESCE(:opt, options_json)
      WHERE id = :id
    ');
    $stmt->execute([
        ':n' => ($name !== '' ? $name : null),
        ':sd' => ($short !== '' ? $short : null),
        ':bp' => ($base !== null ? $base : null),
        ':ur' => $upper,
        ':cid' => ($categoryId > 0 ? $categoryId : null),
        ':img' => ($images !== null ? json_encode($images, JSON_UNESCAPED_UNICODE) : null),
        ':opt' => ($options !== null ? json_encode($options, JSON_UNESCAPED_UNICODE) : null),
        ':id' => $id,
    ]);
    json_response(['message' => 'Updated']);
}

if (preg_match('#^/admin/services/(\d+)$#', $path, $m) && $method === 'DELETE') {
    $user = require_role($pdo, ['admin','provider']);
    $id = (int)$m[1];
    if (($user['role'] ?? '') === 'provider') {
        $stmt = $pdo->prepare('SELECT provider_id FROM services WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row || (int)$row['provider_id'] !== (int)$user['id']) json_response(['message' => 'Forbidden'], 403);
    }
    $stmt = $pdo->prepare('DELETE FROM services WHERE id = ?');
    $stmt->execute([$id]);
    json_response(['message' => 'Deleted']);
}

if (preg_match('#^/admin/services/(\d+)/status$#', $path, $m) && $method === 'PUT') {
    $user = require_role($pdo, ['admin','provider']);
    $id = (int)$m[1];
    $body = get_json_body();
    $isDisabled = !empty($body['isDisabled']) ? 1 : 0;

    if (($user['role'] ?? '') === 'provider') {
        $stmt = $pdo->prepare('SELECT provider_id FROM services WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row || (int)$row['provider_id'] !== (int)$user['id']) json_response(['message' => 'Forbidden'], 403);
    }

    $stmt = $pdo->prepare('UPDATE services SET is_disabled = ? WHERE id = ?');
    $stmt->execute([$isDisabled, $id]);
    json_response(['message' => 'Updated']);
}

if ($path === '/admin/categories' && $method === 'GET') {
    require_role($pdo, ['admin','provider']);
    $stmt = $pdo->prepare('SELECT id, name, is_disabled FROM categories ORDER BY name');
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as &$c) {
        $c['_id'] = (string)$c['id'];
        $c['isDisabled'] = (bool)$c['is_disabled'];
        unset($c['is_disabled']);
    }
    json_response($rows);
}

if (preg_match('#^/admin/categories/(\d+)/status$#', $path, $m) && $method === 'PUT') {
    require_role($pdo, ['admin','provider']);
    $id = (int)$m[1];
    $body = get_json_body();
    $isDisabled = !empty($body['isDisabled']) ? 1 : 0;
    $stmt = $pdo->prepare('UPDATE categories SET is_disabled = ? WHERE id = ?');
    $stmt->execute([$isDisabled, $id]);
    json_response(['message' => 'Updated']);
}

if ($path === '/admin/bookings' && $method === 'GET') {
    require_role($pdo, ['admin']);
    $stmt = $pdo->prepare('SELECT * FROM bookings ORDER BY id DESC');
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as &$r) {
        $r['_id'] = (string)$r['id'];
        $r['items'] = json_decode((string)($r['items_json'] ?? '[]'), true) ?: [];
        $r['address'] = json_decode((string)($r['address_json'] ?? '{}'), true) ?: [];
        $r['totalAmount'] = (int)$r['total_amount'];
        unset($r['items_json'], $r['address_json'], $r['total_amount']);
    }
    json_response($rows);
}

if (preg_match('#^/admin/bookings/(\d+)$#', $path, $m) && $method === 'PUT') {
    require_role($pdo, ['admin']);
    $id = (int)$m[1];
    $body = get_json_body();
    $status = (string)($body['status'] ?? '');
    if (!in_array($status, ['Pending','Confirmed','Completed','Cancelled'], true)) json_response(['message' => 'Invalid status'], 400);
    $stmt = $pdo->prepare('UPDATE bookings SET status = ? WHERE id = ?');
    $stmt->execute([$status, $id]);
    json_response(['message' => 'Updated']);
}

if (preg_match('#^/admin/bookings/(\d+)$#', $path, $m) && $method === 'DELETE') {
    require_role($pdo, ['admin']);
    $id = (int)$m[1];
    $stmt = $pdo->prepare('DELETE FROM bookings WHERE id = ?');
    $stmt->execute([$id]);
    json_response(['message' => 'Deleted']);
}

// Provider bookings
if ($path === '/provider/bookings' && $method === 'GET') {
    $user = require_role($pdo, ['provider']);
    $stmt = $pdo->prepare('SELECT * FROM bookings WHERE provider_id = ? ORDER BY id DESC');
    $stmt->execute([(int)$user['id']]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as &$r) {
        $r['_id'] = (string)$r['id'];
        $r['items'] = json_decode((string)($r['items_json'] ?? '[]'), true) ?: [];
        $r['address'] = json_decode((string)($r['address_json'] ?? '{}'), true) ?: [];
        $r['totalAmount'] = (int)$r['total_amount'];
        unset($r['items_json'], $r['address_json'], $r['total_amount']);
    }
    json_response($rows);
}

if (preg_match('#^/provider/bookings/(\d+)$#', $path, $m) && $method === 'PUT') {
    $user = require_role($pdo, ['provider']);
    $id = (int)$m[1];
    $body = get_json_body();
    $status = (string)($body['status'] ?? '');
    if (!in_array($status, ['Pending','Confirmed','Completed','Cancelled'], true)) json_response(['message' => 'Invalid status'], 400);

    // Ensure booking belongs to provider
    $stmt = $pdo->prepare('SELECT provider_id FROM bookings WHERE id = ?');
    $stmt->execute([$id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row || (int)$row['provider_id'] !== (int)$user['id']) json_response(['message' => 'Forbidden'], 403);

    $stmt = $pdo->prepare('UPDATE bookings SET status = ? WHERE id = ?');
    $stmt->execute([$status, $id]);
    json_response(['message' => 'Updated']);
}

// ---------------------------
// CONTACT: /contact/submit (POST), /contact/messages (GET), /contact/stats (GET), status patch, delete
// ---------------------------
if ($path === '/contact/submit' && $method === 'POST') {
    $body = get_json_body();
    $name = trim((string)($body['name'] ?? ''));
    $email = trim((string)($body['email'] ?? ''));
    $phone = trim((string)($body['phone'] ?? ''));
    $message = trim((string)($body['message'] ?? ''));
    if ($name === '' || $message === '') json_response(['message' => 'Name and message required'], 400);

    $stmt = $pdo->prepare('INSERT INTO contact_messages (name, email, phone, message, status) VALUES (:n,:e,:p,:m,"new")');
    $stmt->execute([
        ':n' => $name,
        ':e' => ($email !== '' ? $email : null),
        ':p' => ($phone !== '' ? $phone : null),
        ':m' => $message,
    ]);
    json_response(['message' => 'Submitted'], 201);
}

if ($path === '/contact/messages' && $method === 'GET') {
    require_role($pdo, ['admin']);
    $stmt = $pdo->prepare('SELECT * FROM contact_messages ORDER BY id DESC');
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as &$r) $r['_id'] = (string)$r['id'];
    json_response($rows);
}

if ($path === '/contact/stats' && $method === 'GET') {
    require_role($pdo, ['admin']);
    $stmt = $pdo->query('SELECT status, COUNT(*) AS count FROM contact_messages GROUP BY status');
    $stats = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $stats[$row['status']] = (int)$row['count'];
    }
    json_response($stats);
}

if (preg_match('#^/contact/messages/(\d+)/status$#', $path, $m) && ($method === 'PATCH' || $method === 'PUT')) {
    require_role($pdo, ['admin']);
    $id = (int)$m[1];
    $body = get_json_body();
    $status = (string)($body['status'] ?? '');
    if (!in_array($status, ['new','read','closed'], true)) json_response(['message' => 'Invalid status'], 400);
    $stmt = $pdo->prepare('UPDATE contact_messages SET status = ? WHERE id = ?');
    $stmt->execute([$status, $id]);
    json_response(['message' => 'Updated']);
}

if (preg_match('#^/contact/messages/(\d+)$#', $path, $m) && $method === 'DELETE') {
    require_role($pdo, ['admin']);
    $id = (int)$m[1];
    $stmt = $pdo->prepare('DELETE FROM contact_messages WHERE id = ?');
    $stmt->execute([$id]);
    json_response(['message' => 'Deleted']);
}

// ---------------------------
// SETTINGS: /settings (GET), /settings/:key (PUT)  (React AdminSettings)
// ---------------------------
if ($path === '/settings' && $method === 'GET') {
    require_role($pdo, ['admin']);
    $stmt = $pdo->prepare('SELECT setting_key, setting_value, setting_type, description, category, created_at, updated_at FROM app_settings ORDER BY setting_key');
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $out = [];
    foreach ($rows as $r) {
        $out[] = [
            '_id' => $r['setting_key'],
            'settingKey' => $r['setting_key'],
            'settingValue' => $r['setting_value'],
            'settingType' => $r['setting_type'],
            'description' => $r['description'],
            'category' => $r['category'],
            'createdAt' => $r['created_at'],
            'updatedAt' => $r['updated_at'],
        ];
    }
    json_response(['success' => true, 'data' => $out]);
}

if (preg_match('#^/settings/([^/]+)$#', $path, $m) && $method === 'PUT') {
    require_role($pdo, ['admin']);
    $key = urldecode($m[1]);
    $body = get_json_body();
    $value = isset($body['value']) ? (string)$body['value'] : null;
    $description = isset($body['description']) ? (string)$body['description'] : null;
    $category = isset($body['category']) ? (string)$body['category'] : null;

    $stmt = $pdo->prepare('
      INSERT INTO app_settings (setting_key, setting_value, setting_type, description, category)
      VALUES (:k, :v, "string", :d, :c)
      ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), description = COALESCE(VALUES(description), description), category = COALESCE(VALUES(category), category)
    ');
    $stmt->execute([':k' => $key, ':v' => $value, ':d' => $description, ':c' => $category]);

    $stmt = $pdo->prepare('SELECT setting_key, setting_value, setting_type, description, category, created_at, updated_at FROM app_settings WHERE setting_key = ?');
    $stmt->execute([$key]);
    $r = $stmt->fetch(PDO::FETCH_ASSOC);
    json_response(['success' => true, 'data' => [
        '_id' => $r['setting_key'],
        'settingKey' => $r['setting_key'],
        'settingValue' => $r['setting_value'],
        'settingType' => $r['setting_type'],
        'description' => $r['description'],
        'category' => $r['category'],
        'createdAt' => $r['created_at'],
        'updatedAt' => $r['updated_at'],
    ]]);
}

// ---------------------------
// NOTIFICATIONS: /notifications/user, /notifications/admin, read, respond
// ---------------------------
if ($path === '/notifications/user' && $method === 'GET') {
    $user = require_role($pdo, ['customer','provider','admin']);
    $stmt = $pdo->prepare('SELECT * FROM notifications WHERE user_id = ? ORDER BY id DESC');
    $stmt->execute([(int)$user['id']]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $out = [];
    foreach ($rows as $r) {
        $meta = json_decode((string)($r['meta_json'] ?? '{}'), true) ?: [];
        $out[] = [
            '_id' => (string)$r['id'],
            'title' => $r['title'],
            'message' => $r['message'],
            'type' => $r['type'],
            'isRead' => (bool)$r['is_read'],
            'createdAt' => $r['created_at'],
            'booking' => $meta['bookingId'] ?? ($r['booking_id'] ? (string)$r['booking_id'] : null),
            'user' => $meta['userId'] ?? null,
        ];
    }
    json_response(['success' => true, 'data' => $out]);
}

if ($path === '/notifications/admin' && $method === 'GET') {
    $admin = require_role($pdo, ['admin']);
    $stmt = $pdo->prepare('SELECT * FROM notifications WHERE user_id = ? ORDER BY id DESC');
    $stmt->execute([(int)$admin['id']]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $out = [];
    foreach ($rows as $r) {
        $meta = json_decode((string)($r['meta_json'] ?? '{}'), true) ?: [];
        $out[] = [
            '_id' => (string)$r['id'],
            'title' => $r['title'],
            'message' => $r['message'],
            'type' => $r['type'],
            'isRead' => (bool)$r['is_read'],
            'createdAt' => $r['created_at'],
            'booking' => $meta['bookingId'] ?? ($r['booking_id'] ? (string)$r['booking_id'] : null),
            'user' => $meta['userId'] ?? null,
        ];
    }
    json_response(['success' => true, 'data' => $out]);
}

if (preg_match('#^/notifications/(user|admin)/(\d+)/read$#', $path, $m) && $method === 'PUT') {
    $scope = $m[1];
    $id = (int)$m[2];
    $u = ($scope === 'admin') ? require_role($pdo, ['admin']) : require_role($pdo, ['customer','provider','admin']);
    $stmt = $pdo->prepare('UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?');
    $stmt->execute([$id, (int)$u['id']]);
    json_response(['success' => true, 'message' => 'Marked as read']);
}

if ($path === '/notifications/admin/respond' && $method === 'POST') {
    require_role($pdo, ['admin']);
    $body = get_json_body();
    $bookingId = isset($body['bookingId']) ? (int)$body['bookingId'] : 0;
    $userId = isset($body['userId']) ? (int)$body['userId'] : 0;
    $text = trim((string)($body['responseText'] ?? ''));
    if ($userId <= 0 || $text === '') json_response(['message' => 'Missing data'], 400);

    $meta = json_encode(['bookingId' => $bookingId ? (string)$bookingId : null, 'userId' => (string)$userId], JSON_UNESCAPED_UNICODE);
    $stmt = $pdo->prepare('INSERT INTO notifications (user_id, booking_id, title, message, type, meta_json, is_read) VALUES (?,?,?,?,?,?,0)');
    $stmt->execute([$userId, ($bookingId ?: null), 'Update from admin', $text, 'admin_response', $meta]);
    json_response(['success' => true, 'message' => 'Response sent']);
}

// ---------------------------
// BLOGS: /blogs, /blogs/:slug, /blogs/admin/*
// ---------------------------
if ($path === '/blogs' && $method === 'GET') {
    $limit = isset($_GET['limit']) ? max(1, min(50, (int)$_GET['limit'])) : 20;
    $stmt = $pdo->prepare('SELECT id, slug, title, excerpt, cover_image_url, created_at FROM blogs WHERE is_published = 1 ORDER BY id DESC LIMIT ' . $limit);
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as &$r) $r['_id'] = (string)$r['id'];
    json_response($rows);
}

if (preg_match('#^/blogs/([^/]+)$#', $path, $m) && $method === 'GET') {
    $slug = urldecode($m[1]);
    $stmt = $pdo->prepare('SELECT * FROM blogs WHERE slug = ? AND is_published = 1 LIMIT 1');
    $stmt->execute([$slug]);
    $r = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$r) json_response(['message' => 'Not found'], 404);
    $r['_id'] = (string)$r['id'];
    $r['tags'] = json_decode((string)($r['tags_json'] ?? '[]'), true) ?: [];
    unset($r['tags_json']);
    json_response($r);
}

if ($path === '/blogs/admin/all' && $method === 'GET') {
    require_role($pdo, ['admin']);
    $stmt = $pdo->prepare('SELECT * FROM blogs ORDER BY id DESC');
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as &$r) {
        $r['_id'] = (string)$r['id'];
        $r['tags'] = json_decode((string)($r['tags_json'] ?? '[]'), true) ?: [];
        unset($r['tags_json']);
    }
    json_response($rows);
}

if ($path === '/blogs/admin/create' && $method === 'POST') {
    require_role($pdo, ['admin']);
    $title = trim((string)($_POST['title'] ?? ''));
    $slug = trim((string)($_POST['slug'] ?? ''));
    $content = (string)($_POST['content'] ?? '');
    $excerpt = (string)($_POST['excerpt'] ?? '');
    $cover = trim((string)($_POST['coverImage'] ?? $_POST['cover_image_url'] ?? ''));
    $tags = $_POST['tags'] ?? null;
    if (is_string($tags)) $tags = json_decode($tags, true);
    if (!is_array($tags)) $tags = [];
    if ($title === '' || $slug === '' || $content === '') json_response(['message' => 'Missing fields'], 400);
    $stmt = $pdo->prepare('INSERT INTO blogs (slug,title,excerpt,content,cover_image_url,tags_json,is_published) VALUES (?,?,?,?,?,?,1)');
    $stmt->execute([$slug,$title,$excerpt,$content,($cover !== '' ? $cover : null),json_encode($tags, JSON_UNESCAPED_UNICODE)]);
    json_response(['message' => 'Created'], 201);
}

if (preg_match('#^/blogs/admin/(\d+)$#', $path, $m) && $method === 'PUT') {
    require_role($pdo, ['admin']);
    $id = (int)$m[1];
    $isMultipart = str_contains($_SERVER['CONTENT_TYPE'] ?? '', 'multipart/form-data');
    $body = $isMultipart ? $_POST : get_json_body();
    $title = isset($body['title']) ? trim((string)$body['title']) : null;
    $slug = isset($body['slug']) ? trim((string)$body['slug']) : null;
    $content = isset($body['content']) ? (string)$body['content'] : null;
    $excerpt = isset($body['excerpt']) ? (string)$body['excerpt'] : null;
    $cover = isset($body['coverImage']) ? trim((string)$body['coverImage']) : (isset($body['cover_image_url']) ? trim((string)$body['cover_image_url']) : null);
    $tags = $body['tags'] ?? null;
    if (is_string($tags)) $tags = json_decode($tags, true);
    $tagsJson = is_array($tags) ? json_encode($tags, JSON_UNESCAPED_UNICODE) : null;
    $stmt = $pdo->prepare('
      UPDATE blogs SET
        title = COALESCE(:t,title),
        slug = COALESCE(:s,slug),
        content = COALESCE(:c,content),
        excerpt = COALESCE(:e,excerpt),
        cover_image_url = COALESCE(:img,cover_image_url),
        tags_json = COALESCE(:tags,tags_json)
      WHERE id = :id
    ');
    $stmt->execute([':t'=>$title,':s'=>$slug,':c'=>$content,':e'=>$excerpt,':img'=>$cover,':tags'=>$tagsJson,':id'=>$id]);
    json_response(['message' => 'Updated']);
}

if (preg_match('#^/blogs/admin/(\d+)$#', $path, $m) && $method === 'DELETE') {
    require_role($pdo, ['admin']);
    $id = (int)$m[1];
    $stmt = $pdo->prepare('DELETE FROM blogs WHERE id = ?');
    $stmt->execute([$id]);
    json_response(['message' => 'Deleted']);
}

// ---------------------------
// SEO: /seo/current, /seo/settings (admin), /seo/festivals, manual override
// ---------------------------
if ($path === '/seo/current' && $method === 'GET') {
    $stmt = $pdo->prepare('SELECT * FROM seo_settings ORDER BY id DESC LIMIT 1');
    $stmt->execute();
    $r = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    json_response($r);
}

if ($path === '/seo/settings' && $method === 'GET') {
    require_role($pdo, ['admin']);
    $stmt = $pdo->prepare('SELECT * FROM seo_settings ORDER BY id DESC LIMIT 1');
    $stmt->execute();
    json_response(['success' => true, 'data' => $stmt->fetch(PDO::FETCH_ASSOC) ?: []]);
}

if ($path === '/seo/settings' && $method === 'PUT') {
    require_role($pdo, ['admin']);
    $body = get_json_body();
    $stmt = $pdo->prepare('INSERT INTO seo_settings (title,description,keywords,og_image_url,is_manual_override) VALUES (?,?,?,?,0)');
    $stmt->execute([
        $body['title'] ?? null,
        $body['description'] ?? null,
        $body['keywords'] ?? null,
        $body['og_image_url'] ?? ($body['ogImage'] ?? null),
    ]);
    json_response(['success' => true, 'message' => 'Saved']);
}

if ($path === '/seo/festivals' && $method === 'GET') {
    require_role($pdo, ['admin']);
    json_response([]); // optional feature; can be expanded later
}

if ($path === '/seo/manual-override' && $method === 'POST') {
    require_role($pdo, ['admin']);
    $body = get_json_body();
    $stmt = $pdo->prepare('INSERT INTO seo_settings (title,description,keywords,og_image_url,is_manual_override) VALUES (?,?,?,?,1)');
    $stmt->execute([
        $body['title'] ?? null,
        $body['description'] ?? null,
        $body['keywords'] ?? null,
        $body['og_image_url'] ?? ($body['ogImage'] ?? null),
    ]);
    json_response(['success' => true, 'message' => 'Manual override enabled']);
}

if ($path === '/seo/disable-override' && $method === 'POST') {
    require_role($pdo, ['admin']);
    // Create a new row marking override off (keeps history simple)
    $stmt = $pdo->prepare('INSERT INTO seo_settings (is_manual_override) VALUES (0)');
    $stmt->execute();
    json_response(['success' => true, 'message' => 'Manual override disabled']);
}

// ---------------------------
// CHATBOT: /chatbot/message
// ---------------------------
if ($path === '/chatbot/message' && $method === 'POST') {
    $body = get_json_body();
    $message = trim((string)($body['message'] ?? ''));
    $sessionId = trim((string)($body['sessionId'] ?? ''));
    if ($sessionId === '') $sessionId = bin2hex(random_bytes(16));

    $lower = strtolower($message);
    $response = "Thanks! Please share your phone number and location, and we'll help you with sofa repair.";
    $suggestions = ['Pricing', 'Book a visit', 'Working hours', 'Service areas'];
    $intent = 'general_help';
    $confidence = 0.6;
    if (str_contains($lower, 'price') || str_contains($lower, 'cost')) {
        $response = "Pricing depends on the sofa condition and work required. You can book a visit and we’ll share an exact quote.";
        $intent = 'pricing';
        $confidence = 0.8;
        $suggestions = ['Book a visit', 'What services do you offer?'];
    } elseif (str_contains($lower, 'book')) {
        $response = "You can book directly from the website. Go to Services → select a service → submit booking.";
        $intent = 'booking';
        $confidence = 0.8;
        $suggestions = ['Open Services page', 'What details are needed?'];
    }

    json_response([
        'sessionId' => $sessionId,
        'response' => $response,
        'suggestions' => $suggestions,
        'intent' => $intent,
        'confidence' => $confidence,
    ]);
}

// ---------------------------
// REVIEWS: POST /reviews (multipart), GET /reviews?serviceId=...
// ---------------------------
if ($path === '/reviews' && $method === 'POST') {
    $user = require_role($pdo, ['customer','provider','admin']);
    // FormData expected
    $serviceId = isset($_POST['serviceId']) ? (int)$_POST['serviceId'] : (isset($_POST['service']) ? (int)$_POST['service'] : 0);
    $rating = isset($_POST['rating']) ? (int)$_POST['rating'] : 0;
    $comment = trim((string)($_POST['comment'] ?? ''));
    $images = $_POST['images'] ?? null;
    if (is_string($images)) $images = json_decode($images, true);
    if (!is_array($images)) $images = [];
    if ($serviceId <= 0 || $rating < 1 || $rating > 5) json_response(['message' => 'Invalid review'], 400);

    $stmt = $pdo->prepare('INSERT INTO reviews (service_id, user_id, rating, comment, images_json) VALUES (?,?,?,?,?)');
    $stmt->execute([$serviceId, (int)$user['id'], $rating, ($comment !== '' ? $comment : null), json_encode($images, JSON_UNESCAPED_UNICODE)]);

    // Update service aggregates (simple recalculation)
    $agg = $pdo->prepare('SELECT COUNT(*) AS cnt, AVG(rating) AS avg_rating FROM reviews WHERE service_id = ?');
    $agg->execute([$serviceId]);
    $a = $agg->fetch(PDO::FETCH_ASSOC) ?: ['cnt'=>0,'avg_rating'=>0];
    $upd = $pdo->prepare('UPDATE services SET review_count = ?, average_rating = ? WHERE id = ?');
    $upd->execute([(int)$a['cnt'], (float)$a['avg_rating'], $serviceId]);

    json_response(['message' => 'Review created'], 201);
}

if ($path === '/reviews' && $method === 'GET') {
    $serviceId = isset($_GET['serviceId']) ? (int)$_GET['serviceId'] : 0;
    if ($serviceId <= 0) json_response([]);
    $stmt = $pdo->prepare('SELECT r.*, u.name AS user_name FROM reviews r JOIN users u ON u.id = r.user_id WHERE r.service_id = ? ORDER BY r.id DESC');
    $stmt->execute([$serviceId]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as &$r) {
        $r['_id'] = (string)$r['id'];
        $r['images'] = json_decode((string)($r['images_json'] ?? '[]'), true) ?: [];
        unset($r['images_json']);
    }
    json_response($rows);
}

// If we reach here, endpoint not found
json_response(['error' => 'API endpoint not found', 'path' => $path], 404);


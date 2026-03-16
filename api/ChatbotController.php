<?php
require_once __DIR__ . '/../includes/db.php';

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJson(['error' => 'Method not allowed'], 405);
}

// Get JSON input
$data = json_decode(file_get_contents('php://input'), true);
$message = $data['message'] ?? '';
$sessionId = $data['session_id'] ?? session_id();

if (empty($message)) {
    sendJson(['error' => 'Message cannot be empty'], 400);
}

// Simple rule-based NLP intent recognition for demonstration
$messageLower = strtolower($message);
$intent = 'unknown';
$response = "I'm not sure I understand. Would you like to speak to a human agent?";

if (strpos($messageLower, 'price') !== false || strpos($messageLower, 'cost') !== false || strpos($messageLower, 'how much') !== false) {
    $intent = 'pricing';
    $response = "Our sofa repair prices start from ₹500 depending on the type of repair and material. Please check our services section for detailed pricing.";
} elseif (strpos($messageLower, 'book') !== false || strpos($messageLower, 'schedule') !== false) {
    $intent = 'booking';
    $response = "You can easily book a service by navigating to the 'Services' tab, picking your desired service, and selecting a time slot.";
} elseif (strpos($messageLower, 'contact') !== false || strpos($messageLower, 'phone') !== false || strpos($messageLower, 'call') !== false) {
    $intent = 'contact';
    $response = "You can reach us directly at +919689861811 or via email at info@silvafurniture.com.";
} elseif (strpos($messageLower, 'hi') !== false || strpos($messageLower, 'hello') !== false) {
    $intent = 'greeting';
    $response = "Hello! Welcome to Silva Furniture. How can I assist you today?";
} elseif (strpos($messageLower, 'clean') !== false || strpos($messageLower, 'wash') !== false) {
    $intent = 'service_inquiry';
    $response = "We offer professional sofa cleaning services! Our deep cleaning removes stains and odor thoroughly.";
}

// Log the conversation in db
$stmt = $pdo->prepare("INSERT INTO chatbot_conversations (tenant_id, user_id, session_id, message, response, intent) VALUES (?, ?, ?, ?, ?, ?)");
$userId = $_SESSION['user_id'] ?? null;
$stmt->execute([CURRENT_TENANT_ID, $userId, $sessionId, $message, $response, $intent]);

sendJson([
    'intent' => $intent,
    'response' => $response,
    'session_id' => $sessionId
]);
?>

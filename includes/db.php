<?php
session_start();

// Database configuration
$host = 'localhost'; // Or InfinityFree database host (e.g., sql123.epizy.com)
$dbname = 'sofa_repair'; // Your database name
$username = 'root'; // Your database username (e.g., epiz_12345678)
$password = ''; // Your database password

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    // Set PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Set default fetch mode to object
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// --- Multi-Tenant Resolution ---
$domain = $_SERVER['HTTP_HOST'] ?? 'localhost';
$subdomain = 'www'; // default

// Basic extraction of subdomain from domain
// Matches something like tenant.example.com
$parts = explode('.', $domain);
if (count($parts) >= 3 && $parts[0] !== 'www') {
    $subdomain = $parts[0];
}

// If testing locally (e.g. localhost:8000), you might pass tenant via query param for debugging
if (isset($_GET['tenant'])) {
    $subdomain = preg_replace('/[^a-zA-Z0-9-]/', '', $_GET['tenant']);
}

// Fetch the tenant ID
$stmt = $pdo->prepare("SELECT * FROM tenants WHERE subdomain = ? LIMIT 1");
$stmt->execute([$subdomain]);
$currentTenant = $stmt->fetch();

if (!$currentTenant) {
    // Fallback to default shop if tenant not found
    $stmt = $pdo->prepare("SELECT * FROM tenants WHERE subdomain = 'www' LIMIT 1");
    $stmt->execute();
    $currentTenant = $stmt->fetch();
    
    if (!$currentTenant) {
        die("System error: No default tenant found.");
    }
}

// Store globally for easy access
define('CURRENT_TENANT_ID', $currentTenant->id);
$GLOBALS['current_tenant'] = $currentTenant;

// Helper function for sending JSON response
function sendJson($data, $status = 200)
{
    header('Content-Type: application/json');
    http_response_code($status);
    echo json_encode($data);
    exit;
}

// Authentication Helpers
function isLoggedIn()
{
    return isset($_SESSION['user_id']);
}

function requireLogin()
{
    if (!isLoggedIn()) {
        header("Location: /login.php");
        exit;
    }
}

function hasRole($roles)
{
    if (!isLoggedIn())
        return false;
    $userRole = $_SESSION['user_role'] ?? 'user';
    if (is_array($roles)) {
        return in_array($userRole, $roles);
    }
    return $userRole === $roles;
}

function requireRole($roles)
{
    if (!hasRole($roles)) {
        header("Location: /index.php"); // or a 'not authorized' page
        exit;
    }
}
?>
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
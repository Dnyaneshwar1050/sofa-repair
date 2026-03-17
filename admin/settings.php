<?php
require_once __DIR__ . '/../includes/db.php';
requireLogin();
requireRole(['admin', 'superadmin']);

$success = '';
$error = '';

// Fetch current settings
function getSetting($pdo, $key, $default = '') {
    $stmt = $pdo->prepare("SELECT setting_value FROM app_settings WHERE setting_key = ? LIMIT 1");
    $stmt->execute([$key]);
    $row = $stmt->fetch();
    return $row ? $row->setting_value : $default;
}

// Handle settings update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_settings'])) {
    $settings = [
        'show_service_prices' => isset($_POST['show_service_prices']) ? 'true' : 'false',
        'notification_retention_days' => (int)($_POST['notification_retention_days'] ?? 30),
        'email_notifications' => isset($_POST['email_notifications']) ? 'true' : 'false',
        'recent_bookings_window' => (int)($_POST['recent_bookings_window'] ?? 24),
        'auto_confirm_bookings' => isset($_POST['auto_confirm_bookings']) ? 'true' : 'false',
        'max_bookings_per_user_per_day' => (int)($_POST['max_bookings_per_user_per_day'] ?? 5),
    ];

    try {
        foreach ($settings as $key => $value) {
            $stmt = $pdo->prepare("INSERT INTO app_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?, updated_at = NOW()");
            $stmt->execute([$key, $value, $value]);
        }
        $success = 'Settings saved successfully!';
    } catch (PDOException $e) {
        $error = 'Failed to save settings: ' . $e->getMessage();
    }
}

// Load current values
$showPrices = getSetting($pdo, 'show_service_prices', 'true');
$notifRetention = getSetting($pdo, 'notification_retention_days', '30');
$emailNotif = getSetting($pdo, 'email_notifications', 'true');
$recentWindow = getSetting($pdo, 'recent_bookings_window', '24');
$autoConfirm = getSetting($pdo, 'auto_confirm_bookings', 'false');
$maxBookings = getSetting($pdo, 'max_bookings_per_user_per_day', '5');

// Get last updated timestamps
function getUpdatedAt($pdo, $key) {
    $stmt = $pdo->prepare("SELECT updated_at FROM app_settings WHERE setting_key = ? LIMIT 1");
    $stmt->execute([$key]);
    $row = $stmt->fetch();
    return $row && isset($row->updated_at) ? date('n/j/Y', strtotime($row->updated_at)) : '';
}

require_once __DIR__ . '/includes/header.php';
?>

<!-- Header -->
<div class="mb-8 bg-gradient-to-r from-purple-600 to-purple-700 rounded-2xl p-8 text-white shadow-lg">
    <h1 class="text-3xl font-bold mb-1">System Settings</h1>
    <p class="text-purple-200 text-sm">Configure system behavior and feature toggles</p>
</div>

<?php if ($success): ?>
    <div class="bg-green-50 text-green-700 p-4 rounded-lg mb-6 flex items-center shadow-sm border border-green-200">
        <i class="fa-solid fa-circle-check mr-3 text-xl"></i>
        <?= htmlspecialchars($success) ?>
    </div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="bg-red-50 text-red-700 p-4 rounded-lg mb-6 flex items-center shadow-sm border border-red-200">
        <i class="fa-solid fa-circle-exclamation mr-3 text-xl"></i>
        <?= htmlspecialchars($error) ?>
    </div>
<?php endif; ?>

<form method="POST" class="space-y-8">
    <input type="hidden" name="save_settings" value="1">

    <!-- Website Display Section -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-center gap-3 mb-6">
            <span class="text-2xl">👆</span>
            <h2 class="text-xl font-bold text-gray-900">Website Display</h2>
        </div>

        <div class="flex items-center justify-between py-4 border-b border-gray-100">
            <div class="flex items-start gap-3">
                <span class="text-lg mt-0.5">👆</span>
                <div>
                    <h3 class="font-semibold text-gray-900">Show Service Prices</h3>
                    <p class="text-gray-500 text-sm">Display service prices to customers on the website</p>
                    <p class="text-gray-400 text-xs mt-1">Current: <?= $showPrices === 'true' ? 'Enabled' : 'Disabled' ?>. (Updated: <?= getUpdatedAt($pdo, 'show_service_prices') ?>)</p>
                </div>
            </div>
            <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" name="show_service_prices" value="1" class="sr-only peer" <?= $showPrices === 'true' ? 'checked' : '' ?>>
                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                <span class="ml-2 text-sm font-medium text-gray-600"><?= $showPrices === 'true' ? 'Enabled' : 'Disabled' ?></span>
            </label>
        </div>
    </div>

    <!-- Notifications Section -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-center gap-3 mb-6">
            <span class="text-2xl">🔔</span>
            <h2 class="text-xl font-bold text-gray-900">Notifications</h2>
        </div>

        <!-- Notification Retention -->
        <div class="flex items-center justify-between py-4 border-b border-gray-100">
            <div class="flex items-start gap-3">
                <span class="text-lg mt-0.5">🔔</span>
                <div>
                    <h3 class="font-semibold text-gray-900">Notification Retention (Days)</h3>
                    <p class="text-gray-500 text-sm">How many days to keep notifications before automatic cleanup</p>
                    <p class="text-gray-400 text-xs mt-1">Current: <?= $notifRetention ?>. (Updated: <?= getUpdatedAt($pdo, 'notification_retention_days') ?>)</p>
                </div>
            </div>
            <input type="number" name="notification_retention_days" value="<?= htmlspecialchars($notifRetention) ?>" 
                   min="1" max="365"
                   class="w-20 border border-gray-300 rounded-lg px-3 py-2 text-sm text-center focus:ring-2 focus:ring-orange-500 outline-none">
        </div>

        <!-- Email Notifications -->
        <div class="flex items-center justify-between py-4">
            <div class="flex items-start gap-3">
                <span class="text-lg mt-0.5">🔵</span>
                <div>
                    <h3 class="font-semibold text-gray-900">Email Notifications</h3>
                    <p class="text-gray-500 text-sm">Send email notifications for booking updates</p>
                    <p class="text-gray-400 text-xs mt-1">Current: <?= $emailNotif === 'true' ? 'Enabled' : 'Disabled' ?>. (Updated: <?= getUpdatedAt($pdo, 'email_notifications') ?>)</p>
                </div>
            </div>
            <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" name="email_notifications" value="1" class="sr-only peer" <?= $emailNotif === 'true' ? 'checked' : '' ?>>
                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                <span class="ml-2 text-sm font-medium text-gray-600"><?= $emailNotif === 'true' ? 'Enabled' : 'Disabled' ?></span>
            </label>
        </div>
    </div>

    <!-- Dashboard Section -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-center gap-3 mb-6">
            <span class="text-2xl">🔴</span>
            <h2 class="text-xl font-bold text-gray-900">Dashboard</h2>
        </div>

        <div class="flex items-center justify-between py-4">
            <div class="flex items-start gap-3">
                <span class="text-lg mt-0.5">🔴</span>
                <div>
                    <h3 class="font-semibold text-gray-900">Recent Bookings Window (Hours)</h3>
                    <p class="text-gray-500 text-sm">Time window for considering bookings as "recent" in the dashboard</p>
                    <p class="text-gray-400 text-xs mt-1">Current: <?= $recentWindow ?>. (Updated: <?= getUpdatedAt($pdo, 'recent_bookings_window') ?>)</p>
                </div>
            </div>
            <input type="number" name="recent_bookings_window" value="<?= htmlspecialchars($recentWindow) ?>"
                   min="1" max="168"
                   class="w-20 border border-gray-300 rounded-lg px-3 py-2 text-sm text-center focus:ring-2 focus:ring-orange-500 outline-none">
        </div>
    </div>

    <!-- Booking Management Section -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-center gap-3 mb-6">
            <span class="text-2xl">✅</span>
            <h2 class="text-xl font-bold text-gray-900">Booking Management</h2>
        </div>

        <!-- Auto-Confirm Bookings -->
        <div class="flex items-center justify-between py-4 border-b border-gray-100">
            <div class="flex items-start gap-3">
                <span class="text-lg mt-0.5">✅</span>
                <div>
                    <h3 class="font-semibold text-gray-900">Auto-Confirm Bookings</h3>
                    <p class="text-gray-500 text-sm">Automatically confirm bookings without admin approval</p>
                    <p class="text-gray-400 text-xs mt-1">Current: <?= $autoConfirm === 'true' ? 'Enabled' : 'Disabled' ?></p>
                </div>
            </div>
            <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" name="auto_confirm_bookings" value="1" class="sr-only peer" <?= $autoConfirm === 'true' ? 'checked' : '' ?>>
                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                <span class="ml-2 text-sm font-medium text-gray-600"><?= $autoConfirm === 'true' ? 'Enabled' : 'Disabled' ?></span>
            </label>
        </div>

        <!-- Max Bookings Per User Per Day -->
        <div class="flex items-center justify-between py-4">
            <div class="flex items-start gap-3">
                <span class="text-lg mt-0.5">📋</span>
                <div>
                    <h3 class="font-semibold text-gray-900">Max Bookings Per User Per Day</h3>
                    <p class="text-gray-500 text-sm">Maximum number of bookings a user can make in a single day</p>
                </div>
            </div>
            <input type="number" name="max_bookings_per_user_per_day" value="<?= htmlspecialchars($maxBookings) ?>"
                   min="1" max="50"
                   class="w-20 border border-gray-300 rounded-lg px-3 py-2 text-sm text-center focus:ring-2 focus:ring-orange-500 outline-none">
        </div>
    </div>

    <!-- Save Button -->
    <div class="flex justify-end">
        <button type="submit"
                class="bg-orange-600 text-white px-8 py-3 rounded-xl font-bold hover:bg-orange-700 transition shadow-md flex items-center gap-2">
            <i class="fa-solid fa-floppy-disk"></i> Save Settings
        </button>
    </div>
</form>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

<?php
require_once __DIR__ . '/../includes/db.php';
requireLogin();
requireRole(['admin', 'superadmin']);

// Fetch stats
$stats = [
    'users' => $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn(),
    'services' => $pdo->query("SELECT COUNT(*) FROM services")->fetchColumn(),
    'bookings' => $pdo->query("SELECT COUNT(*) FROM bookings")->fetchColumn(),
];

// Fetch recent bookings
$stmt = $pdo->query("
    SELECT b.id, b.status, b.created_at, u.name as user_name, s.name as service_name 
    FROM bookings b 
    JOIN users u ON b.user_id = u.id 
    JOIN services s ON b.service_id = s.id 
    ORDER BY b.created_at DESC LIMIT 5
");
$recent_bookings = $stmt->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<div class="mb-8">
    <h1 class="text-3xl font-bold text-[#1e3264] font-serif-custom">Admin Dashboard</h1>
</div>

<!-- Stats Grid -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-12">
    <!-- Card 1: Bookings -->
    <div class="bg-white rounded-xl shadow-[0_4px_20px_-4px_rgba(0,0,0,0.1)] border-t-4 border-[#3b82f6] p-6 flex flex-col justify-between hover:-translate-y-1 transition-transform duration-300">
        <h3 class="text-lg font-bold text-gray-900 font-serif-custom mb-3">Total Bookings</h3>
        <p class="text-4xl font-bold text-[#3b82f6] mb-4"><?= number_format($stats['bookings']) ?></p>
        <a href="/admin/bookings.php" class="text-sm font-medium text-[#3b82f6] hover:underline">Manage Bookings</a>
    </div>

    <!-- Card 2: Services -->
    <div class="bg-white rounded-xl shadow-[0_4px_20px_-4px_rgba(0,0,0,0.1)] border-t-4 border-[#f59e0b] p-6 flex flex-col justify-between hover:-translate-y-1 transition-transform duration-300">
        <h3 class="text-lg font-bold text-gray-900 font-serif-custom mb-3">Active Services</h3>
        <p class="text-4xl font-bold text-[#f59e0b] mb-4"><?= number_format($stats['services']) ?></p>
        <a href="/admin/services.php" class="text-sm font-medium text-[#3b82f6] hover:underline">Manage Services</a>
    </div>

    <!-- Card 3: Users -->
    <div class="bg-white rounded-xl shadow-[0_4px_20px_-4px_rgba(0,0,0,0.1)] border-t-4 border-[#22c55e] p-6 flex flex-col justify-between hover:-translate-y-1 transition-transform duration-300">
        <h3 class="text-lg font-bold text-gray-900 font-serif-custom mb-3">Total Users</h3>
        <p class="text-4xl font-bold text-[#22c55e] mb-4"><?= number_format($stats['users']) ?></p>
        <a href="/admin/users.php" class="text-sm font-medium text-[#3b82f6] hover:underline">Manage Users</a>
    </div>
</div>

<!-- Recent Activity -->
<div class="mb-4">
    <h2 class="text-2xl font-bold text-[#1e3264] font-serif-custom">Recent Bookings</h2>
</div>

<div class="bg-white shadow-[0_4px_20px_-4px_rgba(0,0,0,0.05)] rounded-xl overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-[#f8f9fa] border-b border-gray-100">
                    <th class="p-5 text-xs font-bold text-gray-800 uppercase tracking-widest">Booking ID</th>
                    <th class="p-5 text-xs font-bold text-gray-800 uppercase tracking-widest">Customer</th>
                    <th class="p-5 text-xs font-bold text-gray-800 uppercase tracking-widest">Service</th>
                    <th class="p-5 text-xs font-bold text-gray-800 uppercase tracking-widest">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 bg-white">
                <?php if (count($recent_bookings) > 0): ?>
                    <?php foreach ($recent_bookings as $booking):
                        // Generating a pseudo-random looking hex ID to match the screenshot style if needed, 
                        // or just padding the database ID. Let's use database ID but make it text-gray-500.
                        $hexId = '#' . substr(md5($booking->id), 0, 6);
                        
                        $statusClass = match ($booking->status) {
                            'pending' => 'bg-[#fef3c7] text-[#92400e]',
                            'confirmed' => 'bg-blue-100 text-blue-800',
                            'in_progress' => 'bg-purple-100 text-purple-800',
                            'completed' => 'bg-green-100 text-green-800',
                            'cancelled' => 'bg-red-100 text-red-800',
                            default => 'bg-gray-100 text-gray-800'
                        };
                        ?>
                        <tr class="hover:bg-gray-50 transition duration-150">
                            <td class="p-5 text-sm text-gray-500 font-mono">
                                <?= $hexId ?>
                            </td>
                            <td class="p-5 text-sm font-medium text-gray-800">
                                <?= htmlspecialchars($booking->user_name) ?>
                            </td>
                            <td class="p-5 text-sm text-gray-600">
                                <?= htmlspecialchars($booking->service_name) ?>
                            </td>
                            <td class="p-5">
                                <span class="px-4 py-1.5 text-xs font-bold rounded-full capitalize <?= $statusClass ?>">
                                    <?= htmlspecialchars($booking->status) ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" class="p-8 text-center text-gray-500 text-sm">No recent bookings found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- App Settings -->
<div class="mb-4 mt-8">
    <h2 class="text-2xl font-bold text-[#1e3264] font-serif-custom">Application Settings</h2>
</div>

<div class="bg-white shadow-[0_4px_20px_-4px_rgba(0,0,0,0.05)] rounded-xl p-6 mb-8">
    <form id="app-settings-form" class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Setting: Show Prices -->
            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg border border-gray-100">
                <div>
                    <h4 class="font-bold text-gray-800">Show Service Prices</h4>
                    <p class="text-xs text-gray-500">Display base prices to public users</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" id="setting_show_prices" class="sr-only peer">
                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#ea580c]"></div>
                </label>
            </div>

            <!-- Setting: Global Notifications -->
            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg border border-gray-100">
                <div>
                    <h4 class="font-bold text-gray-800">System Notifications</h4>
                    <p class="text-xs text-gray-500">Enable site-wide alerts</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" id="setting_enable_notifications" class="sr-only peer">
                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#ea580c]"></div>
                </label>
            </div>

            <!-- Setting: Festival SEO Mode -->
            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg border border-gray-100">
                <div>
                    <h4 class="font-bold text-gray-800">Festival SEO Mode</h4>
                    <p class="text-xs text-gray-500">Auto-adjust meta tags for Indian Festivals</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" id="setting_seo_festival_mode" class="sr-only peer">
                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#ea580c]"></div>
                </label>
            </div>
        </div>

        <div class="flex justify-end pt-4 border-t">
            <button type="submit" id="save-settings-btn" class="bg-[#3b82f6] hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-lg transition shadow-md">
                Save Settings
            </button>
        </div>
        <p id="settings-status" class="text-right text-sm font-semibold hidden mt-2"></p>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', async () => {
    const showPricesParams = document.getElementById('setting_show_prices');
    const notifParams = document.getElementById('setting_enable_notifications');
    const seoParams = document.getElementById('setting_seo_festival_mode');
    const form = document.getElementById('app-settings-form');
    const statusText = document.getElementById('settings-status');

    // Fetch initial settings
    try {
        const res = await fetch('/api/AppSettingsController.php');
        const data = await res.json();
        if(data.settings) {
            showPricesParams.checked = data.settings.show_service_prices === 'true';
            notifParams.checked = data.settings.enable_notifications === 'true';
            seoParams.checked = data.settings.seo_festival_mode === 'true';
        }
    } catch(e) {
        console.error("Failed to fetch settings.");
    }

    // Save Settings
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const saveBtn = document.getElementById('save-settings-btn');
        saveBtn.disabled = true;
        saveBtn.innerText = 'Saving...';
        
        const settingsPayload = {
            show_service_prices: showPricesParams.checked ? 'true' : 'false',
            enable_notifications: notifParams.checked ? 'true' : 'false',
            seo_festival_mode: seoParams.checked ? 'true' : 'false'
        };

        try {
            const res = await fetch('/api/AppSettingsController.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ settings: settingsPayload })
            });
            const data = await res.json();
            
            statusText.classList.remove('hidden', 'text-red-600');
            statusText.classList.add('text-green-600');
            statusText.innerText = data.message || 'Saved successfully!';
        } catch(e) {
            statusText.classList.remove('hidden', 'text-green-600');
            statusText.classList.add('text-red-600');
            statusText.innerText = 'Failed to save settings.';
        } finally {
            saveBtn.disabled = false;
            saveBtn.innerText = 'Save Settings';
            setTimeout(() => { statusText.classList.add('hidden'); }, 3000);
        }
    });
});
</script>

<!-- Floating Action Button (Optional based on screenshot) -->
<div class="fixed bottom-8 right-8 z-50">
    <button class="bg-[#ea580c] hover:bg-[#c2410c] text-white rounded-full w-14 h-14 flex items-center justify-center shadow-lg transition-transform hover:scale-105">
        <i class="fa-regular fa-message text-xl"></i>
    </button>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
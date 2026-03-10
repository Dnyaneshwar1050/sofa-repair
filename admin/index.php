<?php
require_once __DIR__ . '/../includes/db.php';
requireLogin();
requireRole(['admin', 'superadmin']);

// Fetch stats
$stats = [
    'users' => $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn(),
    'services' => $pdo->query("SELECT COUNT(*) FROM services")->fetchColumn(),
    'bookings' => $pdo->query("SELECT COUNT(*) FROM bookings")->fetchColumn(),
    'pending_bookings' => $pdo->query("SELECT COUNT(*) FROM bookings WHERE status = 'pending'")->fetchColumn(),
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

<div class="mb-6">
    <h1 class="text-2xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-gray-900 to-gray-600">Overview
        Stats</h1>
    <p class="text-gray-500 text-sm">Welcome back to your dashboard.</p>
</div>

<!-- Stats Grid -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <div
        class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 flex items-center justify-between hover:shadow-md transition">
        <div>
            <p class="text-sm font-bold text-gray-500 uppercase tracking-wider mb-1">Total Users</p>
            <h3 class="text-3xl font-black text-gray-800">
                <?= number_format($stats['users']) ?>
            </h3>
        </div>
        <div
            class="w-14 h-14 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center text-2xl shadow-inner">
            <i class="fa-solid fa-users"></i>
        </div>
    </div>

    <div
        class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 flex items-center justify-between hover:shadow-md transition">
        <div>
            <p class="text-sm font-bold text-gray-500 uppercase tracking-wider mb-1">Total Bookings</p>
            <h3 class="text-3xl font-black text-gray-800">
                <?= number_format($stats['bookings']) ?>
            </h3>
        </div>
        <div
            class="w-14 h-14 bg-green-50 text-green-600 rounded-2xl flex items-center justify-center text-2xl shadow-inner">
            <i class="fa-solid fa-calendar-check"></i>
        </div>
    </div>

    <div
        class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 flex items-center justify-between hover:shadow-md transition">
        <div>
            <p class="text-sm font-bold text-gray-500 uppercase tracking-wider mb-1">Total Services</p>
            <h3 class="text-3xl font-black text-gray-800">
                <?= number_format($stats['services']) ?>
            </h3>
        </div>
        <div
            class="w-14 h-14 bg-purple-50 text-purple-600 rounded-2xl flex items-center justify-center text-2xl shadow-inner">
            <i class="fa-solid fa-couch"></i>
        </div>
    </div>

    <div
        class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 flex items-center justify-between hover:shadow-md transition">
        <div>
            <p class="text-sm font-bold text-gray-500 uppercase tracking-wider mb-1">Pending Orders</p>
            <h3 class="text-3xl font-black text-orange-600">
                <?= number_format($stats['pending_bookings']) ?>
            </h3>
        </div>
        <div
            class="w-14 h-14 bg-orange-50 text-orange-600 rounded-2xl flex items-center justify-center text-2xl shadow-inner">
            <i class="fa-solid fa-clock"></i>
        </div>
    </div>
</div>

<!-- Recent Activity -->
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
        <h2 class="text-lg font-bold text-gray-800">Recent Bookings</h2>
        <a href="/admin/bookings.php"
            class="text-orange-600 hover:text-orange-700 font-semibold text-sm flex items-center gap-1">
            View All <i class="fa-solid fa-arrow-right"></i>
        </a>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider border-b border-gray-100">
                    <th class="p-4 font-bold">Booking ID</th>
                    <th class="p-4 font-bold">Customer</th>
                    <th class="p-4 font-bold">Service</th>
                    <th class="p-4 font-bold">Date</th>
                    <th class="p-4 font-bold">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php if (count($recent_bookings) > 0): ?>
                    <?php foreach ($recent_bookings as $booking):
                        $statusClass = match ($booking->status) {
                            'pending' => 'bg-yellow-50 text-yellow-700 border border-yellow-200',
                            'confirmed' => 'bg-blue-50 text-blue-700 border border-blue-200',
                            'in_progress' => 'bg-purple-50 text-purple-700 border border-purple-200',
                            'completed' => 'bg-green-50 text-green-700 border border-green-200',
                            'cancelled' => 'bg-red-50 text-red-700 border border-red-200',
                            default => 'bg-gray-50 text-gray-700 border border-gray-200'
                        };
                        ?>
                        <tr class="hover:bg-gray-50 transition">
                            <td class="p-4 font-bold text-gray-700">#
                                <?= str_pad($booking->id, 5, '0', STR_PAD_LEFT) ?>
                            </td>
                            <td class="p-4 font-medium text-gray-800">
                                <?= htmlspecialchars($booking->user_name) ?>
                            </td>
                            <td class="p-4 text-gray-600">
                                <?= htmlspecialchars($booking->service_name) ?>
                            </td>
                            <td class="p-4 text-gray-500 text-sm">
                                <?= date('M d, Y h:i A', strtotime($booking->created_at)) ?>
                            </td>
                            <td class="p-4">
                                <span class="px-3 py-1 text-xs font-bold rounded-full uppercase <?= $statusClass ?>">
                                    <?= htmlspecialchars($booking->status) ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="p-8 text-center text-gray-500 font-medium">No recent bookings found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
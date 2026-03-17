<?php
require_once __DIR__ . '/includes/db.php';

requireLogin();

$userId = $_SESSION['user_id'];

// Fetch user bookings with service details
$stmt = $pdo->prepare("
    SELECT b.*, s.name as service_name, s.image as service_image 
    FROM bookings b 
    JOIN services s ON b.service_id = s.id 
    WHERE b.user_id = ? 
    ORDER BY b.created_at DESC
");
$stmt->execute([$userId]);
$bookings = $stmt->fetchAll();

require_once __DIR__ . '/includes/header.php';

// Helper function for status colors
function getStatusClass($status)
{
    return match ($status) {
        'pending' => 'bg-yellow-100 text-yellow-800',
        'confirmed' => 'bg-blue-100 text-blue-800',
        'in_progress' => 'bg-purple-100 text-purple-800',
        'completed' => 'bg-green-100 text-green-800',
        'cancelled' => 'bg-red-100 text-red-800',
        default => 'bg-gray-100 text-gray-800'
    };
}
?>

<div class="bg-gray-50 min-h-screen py-10">
    <div class="max-w-6xl mx-auto px-4">
        <h1 class="text-3xl font-black text-gray-900 mb-8"><i class="fa-solid fa-list-check text-orange-600"></i> My
            Requests</h1>

        <?php if (count($bookings) > 0): ?>
            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                <?php foreach ($bookings as $booking): ?>
                    <div
                        class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden flex flex-col hover:shadow-md transition">
                        <div class="p-5 border-b flex justify-between items-center">
                            <span class="text-xs font-bold text-gray-500">Order #
                                <?= str_pad($booking->id, 5, '0', STR_PAD_LEFT) ?>
                            </span>
                            <span
                                class="<?= getStatusClass($booking->status) ?> text-xs font-bold px-3 py-1 rounded-full uppercase">
                                <?= htmlspecialchars($booking->status) ?>
                            </span>
                        </div>
                        <div class="p-5 flex-grow flex items-start gap-4">
                            <img src="/frontend/public/<?= htmlspecialchars($booking->service_image) ?>"
                                class="w-20 h-20 rounded-lg object-cover bg-gray-100 shrink-0"
                                onerror="this.onerror=null;this.src='/frontend/public/default-service.png'">
                            <div>
                                <h3 class="font-bold text-lg mb-1 leading-tight">
                                    <?= htmlspecialchars($booking->service_name) ?>
                                </h3>
                                <p class="text-orange-600 font-bold mb-2">₹
                                    <?= number_format($booking->total_amount, 2) ?>
                                </p>
                                <p class="text-xs text-gray-500"><i class="fa-regular fa-calendar-alt"></i>
                                    <?= date('d M Y, h:i A', strtotime($booking->scheduled_date)) ?>
                                </p>
                            </div>
                        </div>
                        <div class="bg-gray-50 p-4 text-sm text-gray-600 border-t">
                            <p class="truncate"><i class="fa-solid fa-location-dot mr-1"></i>
                                <?= htmlspecialchars($booking->address) ?>
                            </p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-12 text-center">
                <i class="fa-solid fa-box-open text-6xl text-gray-300 mb-4 block"></i>
                <h3 class="text-2xl font-bold text-gray-700 mb-2">No active requests</h3>
                <p class="text-gray-500 mb-6">You haven't booked any services yet.</p>
                <a href="/index.php"
                    class="inline-block bg-orange-600 text-white font-bold py-3 px-8 rounded-lg hover:bg-orange-700 transition">
                    Explore Services
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
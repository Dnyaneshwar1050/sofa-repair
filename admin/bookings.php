<?php
require_once __DIR__ . '/../includes/db.php';
requireLogin();
requireRole(['admin', 'superadmin']);

$success = '';
$error = '';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id = $_POST['id'] ?? null;

    if ($action === 'update_status' && $id) {
        $status = $_POST['status'] ?? '';
        $stmt = $pdo->prepare("UPDATE bookings SET status = ? WHERE id = ?");
        if ($stmt->execute([$status, $id])) {
            $success = 'Booking status updated successfully.';
        } else {
            $error = 'Failed to update booking.';
        }
    } elseif ($action === 'delete') {
        $stmt = $pdo->prepare("DELETE FROM bookings WHERE id = ?");
        if ($stmt->execute([$id])) {
            $success = 'Booking deleted successfully.';
        } else {
            $error = 'Failed to delete booking.';
        }
    }
}

// Fetch all bookings
$stmt = $pdo->query("
    SELECT b.*, u.name as customer_name, u.phone as customer_phone, u.email as customer_email, s.name as service_name
    FROM bookings b 
    JOIN users u ON b.user_id = u.id 
    JOIN services s ON b.service_id = s.id 
    ORDER BY b.created_at DESC
");
$bookings = $stmt->fetchAll();

require_once __DIR__ . '/includes/header.php';

function getStatusBadge($status)
{
    return match ($status) {
        'pending' => '<span class="bg-yellow-50 text-yellow-600 text-[10px] font-bold px-3 py-1 rounded-full uppercase">Pending</span>',
        'confirmed' => '<span class="bg-blue-50 text-blue-600 text-[10px] font-bold px-3 py-1 rounded-full uppercase">Confirmed</span>',
        'in_progress' => '<span class="bg-purple-50 text-purple-600 text-[10px] font-bold px-3 py-1 rounded-full uppercase">In Progress</span>',
        'completed' => '<span class="bg-green-50 text-green-600 text-[10px] font-bold px-3 py-1 rounded-full uppercase">Completed</span>',
        'cancelled' => '<span class="bg-red-50 text-red-600 text-[10px] font-bold px-3 py-1 rounded-full uppercase">Cancelled</span>',
        default => '<span class="bg-gray-50 text-gray-600 text-[10px] font-bold px-3 py-1 rounded-full uppercase">' . $status . '</span>'
    };
}
?>

<div class="mb-8">
    <h1 class="text-3xl font-bold text-gray-900 mb-1">Booking Requests Management</h1>
</div>

<?php if ($success): ?>
    <div class="bg-green-50 text-green-700 p-4 rounded-xl mb-6 flex items-center shadow-sm border border-green-200">
        <i class="fa-solid fa-circle-check mr-2 text-xl block"></i>
        <?= htmlspecialchars($success) ?>
    </div>
<?php endif; ?>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse min-w-max">
            <thead>
                <tr class="bg-gray-50 text-gray-400 text-[11px] uppercase tracking-wider border-b border-gray-100 text-center">
                    <th class="p-6 font-bold text-left">Booking ID</th>
                    <th class="p-6 font-bold">Date & Time</th>
                    <th class="p-6 font-bold">Customer</th>
                    <th class="p-6 font-bold">Phone</th>
                    <th class="p-6 font-bold text-left">Address</th>
                    <th class="p-6 font-bold">Service Name</th>
                    <th class="p-6 font-bold">Price</th>
                    <th class="p-6 font-bold">Current Status</th>
                    <th class="p-6 font-bold">Change Status To</th>
                    <th class="p-6 font-bold">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50 text-sm">
                <?php foreach ($bookings as $booking): ?>
                    <tr class="hover:bg-gray-50 transition align-top">
                        <td class="p-6 font-bold text-gray-900">
                            #<?= substr(md5($booking->id), 0, 5) ?>
                        </td>
                        <td class="p-6 text-gray-500 whitespace-nowrap">
                            <?= date('n/j/Y, g:i:s A', strtotime($booking->created_at)) ?>
                        </td>
                        <td class="p-6">
                            <span class="font-medium text-gray-800 block"><?= htmlspecialchars($booking->customer_name ?: $booking->customer_email) ?></span>
                        </td>
                        <td class="p-6 text-center">
                            <a href="tel:<?= htmlspecialchars($booking->customer_phone) ?>" 
                               class="bg-[#10b981] text-white px-4 py-2 rounded-lg text-xs font-bold flex items-center justify-center gap-2 hover:bg-green-600 transition shadow-sm">
                                <i class="fa-solid fa-phone"></i>
                                <?= htmlspecialchars($booking->customer_phone) ?>
                            </a>
                        </td>
                        <td class="p-6 max-w-[200px]">
                            <p class="text-gray-500 text-xs leading-relaxed mb-1"><?= htmlspecialchars($booking->address) ?></p>
                            <a href="https://www.google.com/maps/search/?api=1&query=<?= urlencode($booking->address) ?>" 
                               target="_blank" class="text-blue-600 text-[10px] font-bold hover:underline flex items-center gap-1 uppercase">
                                <i class="fa-solid fa-location-dot"></i> Directions
                            </a>
                        </td>
                        <td class="p-6 font-medium text-gray-800">
                            <?= htmlspecialchars($booking->service_name) ?>
                        </td>
                        <td class="p-6 text-center font-bold text-green-600">
                            ₹<?= number_format($booking->total_amount, 0) ?>
                        </td>
                        <td class="p-6 text-center whitespace-nowrap">
                            <?= getStatusBadge($booking->status) ?>
                        </td>
                        <td class="p-6">
                            <form method="POST" action="bookings.php" class="inline-block">
                                <input type="hidden" name="action" value="update_status">
                                <input type="hidden" name="id" value="<?= $booking->id ?>">
                                <select name="status" onchange="this.form.submit()" 
                                        class="p-2 border border-gray-200 rounded-lg text-xs font-bold bg-gray-50 outline-none focus:ring-1 focus:ring-blue-500">
                                    <option value="pending" <?= $booking->status == 'pending' ? 'selected' : '' ?>>Pending</option>
                                    <option value="confirmed" <?= $booking->status == 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                                    <option value="in_progress" <?= $booking->status == 'in_progress' ? 'selected' : '' ?>>In Progress</option>
                                    <option value="completed" <?= $booking->status == 'completed' ? 'selected' : '' ?>>Completed</option>
                                    <option value="cancelled" <?= $booking->status == 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                </select>
                            </form>
                        </td>
                        <td class="p-6 text-center">
                            <form method="POST" action="bookings.php" onsubmit="return confirm('Delete this booking?');">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= $booking->id ?>">
                                <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded-lg text-xs font-bold hover:bg-red-700 transition shadow-sm">
                                    Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (count($bookings) === 0): ?>
                    <tr>
                        <td colspan="10" class="p-12 text-center text-gray-400 font-medium">No bookings found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
?>
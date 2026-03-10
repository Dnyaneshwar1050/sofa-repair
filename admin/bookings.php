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
        $provider_id = (isset($_POST['provider_id']) && !empty($_POST['provider_id'])) ? $_POST['provider_id'] : null;

        $stmt = $pdo->prepare("UPDATE bookings SET status = ?, provider_id = ? WHERE id = ?");
        if ($stmt->execute([$status, $provider_id, $id])) {
            $success = 'Booking status updated successfully.';
        } else {
            $error = 'Failed to update booking.';
        }
    }
}

// Fetch all bookings
$stmt = $pdo->query("
    SELECT b.*, u.name as customer_name, u.phone as customer_phone, s.name as service_name, p.name as provider_name 
    FROM bookings b 
    JOIN users u ON b.user_id = u.id 
    JOIN services s ON b.service_id = s.id 
    LEFT JOIN users p ON b.provider_id = p.id
    ORDER BY b.created_at DESC
");
$bookings = $stmt->fetchAll();

// Fetch providers
$providers = $pdo->query("SELECT id, name FROM users WHERE role IN ('provider', 'superadmin')")->fetchAll();

require_once __DIR__ . '/includes/header.php';

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

<div class="mb-6">
    <h1 class="text-2xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-gray-900 to-gray-600">Booking
        Management</h1>
    <p class="text-gray-500 text-sm">View all customer requests and assign providers.</p>
</div>

<?php if ($success): ?>
    <div class="bg-green-50 text-green-700 p-4 rounded-lg mb-6 flex items-center shadow-sm border border-green-200">
        <i class="fa-solid fa-circle-check mr-2 text-xl block"></i>
        <?= htmlspecialchars($success) ?>
    </div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="bg-red-50 text-red-700 p-4 rounded-lg mb-6 flex items-center shadow-sm border border-red-200">
        <i class="fa-solid fa-circle-exclamation mr-2 text-xl block"></i>
        <?= htmlspecialchars($error) ?>
    </div>
<?php endif; ?>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse min-w-max">
            <thead>
                <tr class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider border-b border-gray-100">
                    <th class="p-4 font-bold">Booking Details</th>
                    <th class="p-4 font-bold">Customer Info</th>
                    <th class="p-4 font-bold">Scheduled Info</th>
                    <th class="p-4 font-bold text-center">Status / Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php foreach ($bookings as $booking): ?>
                    <tr class="hover:bg-gray-50 transition p-2">
                        <td class="p-4 align-top">
                            <span class="text-xs font-bold text-gray-400 block mb-1">#
                                <?= str_pad($booking->id, 5, '0', STR_PAD_LEFT) ?>
                            </span>
                            <p class="font-bold text-gray-800 text-base">
                                <?= htmlspecialchars($booking->service_name) ?>
                            </p>
                            <p class="text-orange-600 font-bold mt-1">₹
                                <?= number_format($booking->total_amount, 2) ?>
                            </p>
                        </td>
                        <td class="p-4 align-top">
                            <p class="font-semibold text-gray-700"><i
                                    class="fa-solid fa-user text-gray-400 mr-2 text-xs"></i>
                                <?= htmlspecialchars($booking->customer_name) ?>
                            </p>
                            <p class="text-sm text-gray-600 mt-1"><i
                                    class="fa-solid fa-phone text-gray-400 mr-2 text-xs"></i>
                                <?= htmlspecialchars($booking->customer_phone) ?>
                            </p>
                            <?php if ($booking->notes): ?>
                                <p class="text-xs text-gray-500 mt-2 italic border-l-2 border-orange-300 pl-2">"
                                    <?= htmlspecialchars($booking->notes) ?>"
                                </p>
                            <?php endif; ?>
                        </td>
                        <td class="p-4 align-top max-w-xs">
                            <p class="font-medium text-gray-800"><i
                                    class="fa-regular fa-calendar-check text-orange-500 mr-2"></i>
                                <?= date('M d, Y', strtotime($booking->scheduled_date)) ?>
                            </p>
                            <p class="text-xs text-gray-500 mt-1 pl-6">
                                <?= date('h:i A', strtotime($booking->scheduled_date)) ?>
                            </p>
                            <p class="text-xs text-gray-600 mt-3 flex items-start truncate whitespace-normal"><i
                                    class="fa-solid fa-location-dot text-gray-400 mt-1 mr-2 shrink-0"></i>
                                <?= htmlspecialchars($booking->address) ?>
                            </p>
                        </td>
                        <td class="p-4 align-top text-center w-64">
                            <div class="mb-3">
                                <span
                                    class="<?= getStatusClass($booking->status) ?> text-xs font-bold px-3 py-1 rounded-full uppercase block text-center border <?= str_replace('bg-', 'border-', explode(' ', getStatusClass($booking->status))[0]) ?>">
                                    <?= htmlspecialchars($booking->status) ?>
                                </span>
                            </div>

                            <button onclick='openStatusModal(<?= json_encode([
                                "id" => $booking->id,
                                "status" => $booking->status,
                                "provider_id" => $booking->provider_id
                            ]) ?>)'
                                class="w-full bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold py-1.5 px-3 rounded transition flex justify-center items-center gap-2 border border-gray-300">
                                <i class="fa-solid fa-pen-to-square"></i> Update
                            </button>

                            <?php if ($booking->provider_name): ?>
                                <p class="text-xs text-gray-500 mt-2 bg-gray-50 rounded p-1">Provider: <strong>
                                        <?= htmlspecialchars($booking->provider_name) ?>
                                    </strong></p>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (count($bookings) === 0): ?>
                    <tr>
                        <td colspan="4" class="p-8 text-center text-gray-500 font-medium">No bookings found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Update Status Modal -->
<div id="status-modal"
    class="hidden fixed inset-0 bg-gray-900 bg-opacity-50 z-50 flex items-center justify-center px-4 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm overflow-hidden transform transition-all">
        <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
            <h3 class="text-lg font-bold text-gray-800">Update Booking</h3>
            <button onclick="closeStatusModal()" class="text-gray-400 hover:text-gray-600 transition">
                <i class="fa-solid fa-times text-xl"></i>
            </button>
        </div>
        <form method="POST" action="bookings.php" class="p-6">
            <input type="hidden" name="action" value="update_status">
            <input type="hidden" name="id" id="modal-booking-id" value="">

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select name="status" id="modal-status" required
                        class="w-full p-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 outline-none transition bg-white">
                        <option value="pending">Pending</option>
                        <option value="confirmed">Confirmed</option>
                        <option value="in_progress">In Progress</option>
                        <option value="completed">Completed</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Assign Provider</label>
                    <select name="provider_id" id="modal-provider"
                        class="w-full p-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 outline-none transition bg-white">
                        <option value="">-- Unassigned --</option>
                        <?php foreach ($providers as $p): ?>
                            <option value="<?= $p->id ?>">
                                <?= htmlspecialchars($p->name) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-3 pt-4 border-t border-gray-100">
                <button type="button" onclick="closeStatusModal()"
                    class="px-4 py-2 text-gray-600 font-medium hover:bg-gray-100 rounded-lg transition">Cancel</button>
                <button type="submit"
                    class="bg-orange-600 text-white px-6 py-2 rounded-lg font-bold hover:bg-orange-700 transition shadow-md">Update</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openStatusModal(data) {
        document.getElementById('modal-booking-id').value = data.id;
        document.getElementById('modal-status').value = data.status;
        document.getElementById('modal-provider').value = data.provider_id || '';
        document.getElementById('status-modal').classList.remove('hidden');
    }

    function closeStatusModal() {
        document.getElementById('status-modal').classList.add('hidden');
    }
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
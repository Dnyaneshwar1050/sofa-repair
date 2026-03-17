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

    if ($action === 'mark_read' && $id) {
        $stmt = $pdo->prepare("UPDATE notifications SET status = 'read' WHERE id = ?");
        if ($stmt->execute([$id])) {
            $success = 'Notification marked as read.';
        }
    } elseif ($action === 'delete' && $id) {
        $stmt = $pdo->prepare("DELETE FROM notifications WHERE id = ?");
        if ($stmt->execute([$id])) {
            $success = 'Notification deleted.';
        }
    }
}

// Fetch counts
$counts = $pdo->query("
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'unread' THEN 1 ELSE 0 END) as unread,
        SUM(CASE WHEN status = 'read' THEN 1 ELSE 0 END) as read_count
    FROM notifications
")->fetch();

// Fetch notifications
$status_filter = $_GET['status'] ?? 'all';
$query = "SELECT * FROM notifications";
if ($status_filter !== 'all') {
    $query .= " WHERE status = '$status_filter'";
}
$query .= " ORDER BY created_at DESC";
$notifications = $pdo->query($query)->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<!-- Header Section -->
<div class="bg-gradient-to-r from-blue-600 to-purple-600 rounded-2xl p-8 mb-8 text-white shadow-lg relative overflow-hidden">
    <div class="relative z-10 flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-black mb-2 tracking-tight">Admin Notifications</h1>
            <p class="text-blue-100/80 text-sm">Manage booking notifications and customer communications</p>
        </div>
        <div class="bg-white/10 backdrop-blur-md rounded-2xl px-8 py-4 border border-white/20 text-center min-w-[140px]">
            <p class="text-3xl font-black mb-1"><?= $counts->unread ?? 0 ?></p>
            <p class="text-[10px] uppercase font-bold tracking-widest text-blue-100">Unread</p>
        </div>
    </div>
    <!-- Decor -->
    <div class="absolute -right-20 -top-20 w-64 h-64 bg-white/5 rounded-full"></div>
</div>

<!-- Tabs -->
<div class="flex items-center gap-2 mb-8 bg-gray-100 p-1.5 rounded-2xl w-max">
    <a href="?status=all" class="px-6 py-2.5 rounded-xl text-sm font-bold transition <?= $status_filter == 'all' ? 'bg-blue-600 text-white shadow-md' : 'text-gray-500 hover:text-gray-900' ?>">
        All Notifications (<?= $counts->total ?? 0 ?>)
    </a>
    <a href="?status=unread" class="px-6 py-2.5 rounded-xl text-sm font-bold transition <?= $status_filter == 'unread' ? 'bg-blue-600 text-white shadow-md' : 'text-gray-500 hover:text-gray-900' ?>">
        Unread (<?= $counts->unread ?? 0 ?>)
    </a>
    <a href="?status=read" class="px-6 py-2.5 rounded-xl text-sm font-bold transition <?= $status_filter == 'read' ? 'bg-blue-600 text-white shadow-md' : 'text-gray-500 hover:text-gray-900' ?>">
        Read (<?= $counts->read_count ?? 0 ?>)
    </a>
</div>

<?php if ($success): ?>
    <div class="bg-green-50 text-green-700 p-4 rounded-2xl mb-6 flex items-center shadow-sm border border-green-200 animate-fade-in">
        <i class="fa-solid fa-circle-check mr-3 text-xl"></i>
        <span class="font-bold text-sm"><?= htmlspecialchars($success) ?></span>
    </div>
<?php endif; ?>

<!-- Notifications List -->
<div class="space-y-6">
    <?php foreach ($notifications as $n): 
        $meta = json_decode($n->meta_data);
    ?>
    <div class="bg-white rounded-3xl border border-blue-100 shadow-sm overflow-hidden relative group hover:shadow-xl transition-all duration-300">
        <!-- Colored Strip based on type -->
        <div class="absolute left-0 top-0 bottom-0 w-1.5 <?= $n->priority == 'high' ? 'bg-red-500' : 'bg-blue-500' ?>"></div>
        
        <div class="p-8">
            <div class="flex flex-col md:flex-row gap-6">
                <!-- Icon -->
                <div class="w-16 h-16 bg-gray-50 rounded-2xl flex items-center justify-center shrink-0 border border-gray-100">
                    <i class="fa-regular fa-clipboard text-2xl text-orange-400"></i>
                </div>
                
                <div class="flex-1">
                    <div class="flex flex-wrap items-center gap-3 mb-2">
                        <h3 class="text-xl font-bold text-gray-900"><?= htmlspecialchars($n->title) ?></h3>
                        <?php if ($n->status == 'unread'): ?>
                            <span class="bg-blue-600 text-white text-[10px] px-2.5 py-1 rounded-lg font-black uppercase tracking-widest shadow-sm">New</span>
                        <?php endif; ?>
                        <?php if ($n->priority == 'high'): ?>
                            <span class="bg-red-50 text-red-500 text-[10px] px-2.5 py-1 rounded-lg font-black uppercase tracking-widest border border-red-100">High Priority</span>
                        <?php endif; ?>
                    </div>
                    
                    <p class="text-gray-500 text-sm mb-6 leading-relaxed"><?= htmlspecialchars($n->message) ?></p>
                    
                    <!-- Details Sub-card -->
                    <?php if ($meta): ?>
                    <div class="bg-gray-50/50 rounded-2xl p-6 border border-gray-100 grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <p class="text-[10px] uppercase font-bold text-gray-400 mb-2 tracking-widest">Booking Details:</p>
                            <div class="space-y-2">
                                <div class="flex justify-between items-center bg-white p-2.5 rounded-xl border border-gray-50">
                                    <span class="text-xs text-gray-400">Service:</span>
                                    <span class="text-xs font-black text-gray-900"><?= htmlspecialchars($meta->service ?? 'N/A') ?></span>
                                </div>
                                <div class="flex justify-between items-center bg-white p-2.5 rounded-xl border border-gray-50">
                                    <span class="text-xs text-gray-400">Customer:</span>
                                    <span class="text-xs font-black text-gray-900"><?= htmlspecialchars($meta->customer ?? 'N/A') ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="md:pt-6">
                            <div class="space-y-2">
                                <div class="flex justify-between items-center bg-white p-2.5 rounded-xl border border-gray-50">
                                    <span class="text-xs text-gray-400">Amount:</span>
                                    <span class="text-xs font-black text-gray-900">₹<?= htmlspecialchars($meta->amount ?? '0') ?></span>
                                </div>
                                <div class="flex justify-between items-center bg-white p-2.5 rounded-xl border border-gray-50">
                                    <span class="text-xs text-gray-400">Phone:</span>
                                    <span class="text-xs font-black text-gray-900"><?= htmlspecialchars($meta->phone ?? 'N/A') ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <div class="mt-6 flex flex-wrap justify-between items-center gap-4">
                        <span class="text-xs font-bold text-gray-400 tracking-tighter">
                            <?= date('n/j/Y', strtotime($n->created_at)) ?>
                        </span>
                        
                        <div class="flex items-center gap-3">
                            <?php if ($meta && isset($meta->phone)): ?>
                            <a href="tel:<?= $meta->phone ?>" class="bg-emerald-500 text-white px-6 py-2 rounded-xl text-xs font-black hover:bg-emerald-600 transition shadow-md flex items-center gap-2">
                                <i class="fa-solid fa-phone"></i> Call
                            </a>
                            <?php endif; ?>
                            
                            <?php if ($n->status == 'unread'): ?>
                            <form method="POST" class="inline">
                                <input type="hidden" name="action" value="mark_read">
                                <input type="hidden" name="id" value="<?= $n->id ?>">
                                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-xl text-xs font-black hover:bg-blue-700 transition shadow-md">
                                    Mark Read
                                </button>
                            </form>
                            <?php endif; ?>
                            
                            <a href="/admin/bookings.php" class="bg-purple-600 text-white px-6 py-2 rounded-xl text-xs font-black hover:bg-purple-700 transition shadow-md flex items-center gap-2">
                                <i class="fa-solid fa-reply"></i> Respond
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    
    <?php if (count($notifications) === 0): ?>
    <div class="flex flex-col items-center justify-center py-20 bg-white rounded-3xl border border-gray-100 opacity-60">
        <i class="fa-regular fa-bell-slash text-5xl mb-4 text-gray-200"></i>
        <h3 class="text-lg font-bold text-gray-400">No Notifications</h3>
    </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

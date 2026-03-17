<?php
require_once __DIR__ . '/../includes/db.php';
requireLogin();
requireRole(['admin', 'superadmin']);

// Fetch counts
$totalCount = $pdo->query("SELECT COUNT(*) FROM contact_messages")->fetchColumn();
$newCount = $pdo->query("SELECT COUNT(*) FROM contact_messages WHERE status = 'unread'")->fetchColumn();
$readCount = $pdo->query("SELECT COUNT(*) FROM contact_messages WHERE status = 'read'")->fetchColumn();
$repliedCount = $pdo->query("SELECT COUNT(*) FROM contact_messages WHERE status = 'replied'")->fetchColumn();

require_once __DIR__ . '/includes/header.php';
?>

<!-- Management Card Header -->
<div class="bg-gradient-to-r from-[#2563eb] to-[#1e40af] rounded-2xl p-8 text-white shadow-lg mb-8 relative overflow-hidden">
    <div class="relative z-10 flex justify-between items-center">
        <div>
            <div class="flex items-center gap-3 mb-2">
                <div class="bg-white/20 p-2 rounded-lg backdrop-blur-sm">
                    <i class="fa-solid fa-envelope text-xl"></i>
                </div>
                <h1 class="text-3xl font-bold">Contact Messages Management</h1>
            </div>
            <p class="text-blue-100 text-sm">Manage and respond to customer inquiries</p>
        </div>
        <div class="flex gap-4">
            <div class="bg-white/10 backdrop-blur-md px-6 py-4 rounded-xl border border-white/10 text-center min-w-[120px]">
                <p class="text-[10px] uppercase font-bold text-blue-200 mb-1 tracking-wider">Total Messages</p>
                <p class="text-3xl font-black"><?= $totalCount ?></p>
            </div>
            <div class="bg-white/10 backdrop-blur-md px-6 py-4 rounded-xl border border-white/10 text-center min-w-[120px]">
                <p class="text-[10px] uppercase font-bold text-yellow-300 mb-1 tracking-wider">New Messages</p>
                <p class="text-3xl font-black text-yellow-300"><?= $newCount ?></p>
            </div>
        </div>
    </div>
    <!-- Decorative Circle -->
    <div class="absolute -right-20 -top-20 w-64 h-64 bg-white/5 rounded-full"></div>
</div>

<!-- Tabs -->
<div class="flex items-center gap-6 mb-8 border-b border-gray-100 px-2 pb-1 overflow-x-auto whitespace-nowrap scrollbar-hide">
    <button class="flex items-center gap-2 px-4 py-2 bg-[#1d4ed8] text-white rounded-lg text-sm font-bold shadow-md transition">
        All <span class="bg-white/20 px-2 py-0.5 rounded-full text-[10px]"><?= $totalCount ?></span>
    </button>
    <button class="flex items-center gap-2 px-4 py-2 text-gray-500 hover:text-gray-900 text-sm font-bold transition">
        New <span class="bg-gray-100 px-2 py-0.5 rounded-full text-[10px] text-gray-400"><?= $newCount ?></span>
    </button>
    <button class="flex items-center gap-2 px-4 py-2 text-gray-500 hover:text-gray-900 text-sm font-bold transition">
        Read <span class="bg-gray-100 px-2 py-0.5 rounded-full text-[10px] text-gray-400"><?= $readCount ?></span>
    </button>
    <button class="flex items-center gap-2 px-4 py-2 text-gray-500 hover:text-gray-900 text-sm font-bold transition">
        Responded <span class="bg-gray-100 px-2 py-0.5 rounded-full text-[10px] text-gray-400"><?= $repliedCount ?></span>
    </button>
    <button class="flex items-center gap-2 px-4 py-2 text-gray-500 hover:text-gray-900 text-sm font-bold transition">
        Archived <span class="bg-gray-100 px-2 py-0.5 rounded-full text-[10px] text-gray-400">0</span>
    </button>
</div>

<!-- Empty State -->
<div class="flex flex-col items-center justify-center py-32 bg-white rounded-3xl border border-gray-100 shadow-sm">
    <div class="w-24 h-24 bg-gray-50 rounded-full flex items-center justify-center mb-6 border border-gray-100">
        <i class="fa-regular fa-comment-dots text-4xl text-gray-200"></i>
    </div>
    <h3 class="text-xl font-bold text-gray-900 mb-2">No messages found.</h3>
    <p class="text-gray-400 text-sm">When customers contact you, their messages will appear here.</p>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

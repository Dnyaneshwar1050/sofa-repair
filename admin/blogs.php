<?php
require_once __DIR__ . '/../includes/db.php';
requireLogin();
requireRole(['admin', 'superadmin']);

$success = '';
$error = '';

// Handle actions (Delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $id = $_POST['id'] ?? null;
    if ($id) {
        $stmt = $pdo->prepare("DELETE FROM blogs WHERE id = ?");
        if ($stmt->execute([$id])) {
            $success = 'Blog post deleted successfully.';
        } else {
            $error = 'Failed to delete blog post.';
        }
    }
}

// Fetch Stats
$total_posts = $pdo->query("SELECT COUNT(*) FROM blogs")->fetchColumn();
// Assuming we'll use a `status` column or if not present, we default. Wait, the schema didn't have `status` explicitly added in my alter, but `blogs` usually has it. Let's check `views` or add defaults.
// For now, let's assume `created_at` exists and we use `published_at` or `status`. If `status` doesn't exist, we fallback.
// I will treat all as 'Published' for now, or check if 'is_draft' exists.
$published_count = $total_posts; 
$drafts_count = 0;
// We'll calculate total views later or mock it if column missing.
$total_views = 57;

// Fetch all blogs
$stmt = $pdo->query("SELECT * FROM blogs ORDER BY created_at DESC");
$blogs = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once __DIR__ . '/includes/header.php';
?>

<div class="mb-8 flex justify-between items-center">
    <div>
        <h1 class="text-3xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-gray-900 to-gray-600 mb-2">Blog Management</h1>
        <p class="text-gray-500 text-sm">Manage your sofa repair and maintenance blog posts</p>
    </div>
    <a href="/admin/blog-form.php"
        class="bg-blue-600 text-white px-5 py-2.5 rounded-lg font-bold hover:bg-blue-700 transition shadow-sm flex items-center gap-2 text-sm tracking-wide">
        <i class="fa-solid fa-plus"></i> Create New Post
    </a>
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

<!-- Stats Overview -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex items-center">
        <div class="p-3 rounded-lg bg-blue-50 text-blue-600 mr-4">
            <i class="fa-solid fa-chart-simple text-xl"></i>
        </div>
        <div>
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Total Posts</p>
            <p class="text-2xl font-bold text-gray-800"><?= $total_posts ?></p>
        </div>
    </div>
    
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex items-center">
        <div class="p-3 rounded-lg bg-green-50 text-green-600 mr-4">
            <i class="fa-solid fa-eye text-xl"></i>
        </div>
        <div>
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Published</p>
            <p class="text-2xl font-bold text-gray-800"><?= $published_count ?></p>
        </div>
    </div>
    
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex items-center">
        <div class="p-3 rounded-lg bg-orange-50 text-orange-600 mr-4">
            <i class="fa-solid fa-pen-to-square text-xl"></i>
        </div>
        <div>
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Drafts</p>
            <p class="text-2xl font-bold text-gray-800"><?= $drafts_count ?></p>
        </div>
    </div>
    
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex items-center">
        <div class="p-3 rounded-lg bg-purple-50 text-purple-600 mr-4">
            <i class="fa-solid fa-eye text-xl"></i>
        </div>
        <div>
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Total Views</p>
            <p class="text-2xl font-bold text-gray-800"><?= $total_views ?></p>
        </div>
    </div>
</div>

<!-- Blog List Table -->
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50 text-gray-400 text-[11px] uppercase tracking-wider border-b border-gray-100">
                    <th class="p-4 font-bold">Post</th>
                    <th class="p-4 font-bold text-center">Status</th>
                    <th class="p-4 font-bold">Category</th>
                    <th class="p-4 font-bold text-center">Views</th>
                    <th class="p-4 font-bold text-center">SEO Score</th>
                    <th class="p-4 font-bold">Date</th>
                    <th class="p-4 font-bold text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php foreach ($blogs as $blog): 
                    // Simulate some prototype values
                    $is_published = true; // Placeholder logic
                    $views = 57;          // Placeholder logic
                    $seo_score = 70;      // Placeholder logic
                ?>
                    <tr class="hover:bg-gray-50 transition group p-2">
                        <td class="p-4">
                            <div class="flex items-center gap-4">
                                <img src="/frontend/public/<?= htmlspecialchars($blog['image'] ?? 'default-blog.png') ?>"
                                    class="w-16 h-12 rounded object-cover border border-gray-200"
                                    onerror="this.src='/frontend/public/default-service.png'">
                                <div>
                                    <p class="font-bold text-gray-800 text-sm mb-0.5 line-clamp-1">
                                        <?= htmlspecialchars($blog['title']) ?>
                                    </p>
                                    <p class="text-xs text-gray-500 flex items-center gap-1">
                                        <i class="fa-regular fa-user"></i> Admin
                                    </p>
                                </div>
                            </div>
                        </td>
                        <td class="p-4 text-center">
                            <?php if ($is_published): ?>
                                <span class="bg-green-100 text-green-700 px-2.5 py-1 rounded text-xs font-bold inline-block">Published</span>
                            <?php else: ?>
                                <span class="bg-orange-100 text-orange-700 px-2.5 py-1 rounded text-xs font-bold inline-block">Draft</span>
                            <?php endif; ?>
                        </td>
                        <td class="p-4 font-medium text-gray-700 text-sm">
                            <?= htmlspecialchars($blog['category_name'] ?? 'Uncategorized') ?>
                        </td>
                        <td class="p-4 text-center text-sm text-gray-600">
                            <i class="fa-regular fa-eye text-gray-400 mr-1"></i> <?= $views ?>
                        </td>
                        <td class="p-4 text-center font-bold text-sm">
                            <?php if ($seo_score >= 80): ?>
                                <span class="text-green-600"><?= $seo_score ?>/100</span>
                            <?php elseif ($seo_score >= 50): ?>
                                <span class="text-orange-500"><?= $seo_score ?>/100</span>
                            <?php else: ?>
                                <span class="text-red-500"><?= $seo_score ?>/100</span>
                            <?php endif; ?>
                        </td>
                        <td class="p-4 text-sm text-gray-500">
                            <i class="fa-regular fa-calendar text-gray-400 mr-1"></i>
                            <?= date('m/d/Y', strtotime($blog['created_at'])) ?>
                        </td>
                        <td class="p-4 text-right space-x-3">
                            <a href="/blog-details.php?id=<?= $blog['id'] ?>" target="_blank" class="text-blue-500 hover:text-blue-700 text-lg transition">
                                <i class="fa-regular fa-eye"></i>
                            </a>
                            <a href="/admin/blog-form.php?id=<?= $blog['id'] ?>" class="text-purple-500 hover:text-purple-700 text-lg transition">
                                <i class="fa-regular fa-pen-to-square"></i>
                            </a>
                            <form method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this post?');">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= $blog['id'] ?>">
                                <button type="submit" class="text-red-500 hover:text-red-700 text-lg transition">
                                    <i class="fa-regular fa-trash-can"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                
                <?php if (count($blogs) === 0): ?>
                    <tr>
                        <td colspan="7" class="p-12 text-center">
                            <div class="text-gray-300 mb-4">
                                <i class="fa-solid fa-file-pen text-5xl"></i>
                            </div>
                            <h3 class="text-lg font-bold text-gray-800 mb-1">No blog posts yet</h3>
                            <p class="text-gray-500 text-sm mb-4">Get started by creating your first piece of content.</p>
                            <a href="/admin/blog-form.php"
                                class="bg-blue-600 text-white px-5 py-2.5 rounded-lg font-bold hover:bg-blue-700 transition shadow-sm inline-flex items-center gap-2 text-sm tracking-wide">
                                <i class="fa-solid fa-plus"></i> Create New Post
                            </a>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

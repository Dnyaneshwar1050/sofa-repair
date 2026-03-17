<?php
require_once __DIR__ . '/includes/db.php';

$id = $_GET['id'] ?? null;

if (!$id) {
    header("Location: /blog.php");
    exit;
}

// Fetch blog details
$stmt = $pdo->prepare("SELECT * FROM blogs WHERE id = ?");
$stmt->execute([$id]);
$blog = $stmt->fetch();

if (!$blog) {
    header("Location: /blog.php");
    exit;
}

// Fetch related blogs (excluding current)
$stmtRelated = $pdo->prepare("SELECT id, title, image, created_at FROM blogs WHERE id != ? ORDER BY created_at DESC LIMIT 3");
$stmtRelated->execute([$id]);
$relatedBlogs = $stmtRelated->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<div class="bg-gray-50 min-h-screen py-10">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Breadcrumbs -->
        <nav class="flex text-sm text-gray-500 mb-8 font-medium">
            <a href="/" class="hover:text-orange-600 transition"><i class="fa-solid fa-home mr-1"></i> Home</a>
            <span class="mx-2">/</span>
            <a href="/blog.php" class="hover:text-orange-600 transition">Blog</a>
            <span class="mx-2">/</span>
            <span class="text-gray-800 truncate border-b border-gray-800 pb-0.5">
                <?= htmlspecialchars($blog->title) ?>
            </span>
        </nav>

        <article class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden mb-12">
            <div class="relative h-64 md:h-96 w-full">
                <img src="/frontend/public/<?= htmlspecialchars($blog->image) ?>"
                    alt="<?= htmlspecialchars($blog->title) ?>" class="w-full h-full object-cover"
                    onerror="this.onerror=null;this.src='/frontend/public/default-blog.jpg'">
            </div>

            <div class="p-8 md:p-12">
                <div
                    class="flex items-center text-sm font-bold text-gray-500 mb-6 space-x-6 border-b border-gray-100 pb-6">
                    <span class="flex items-center"><i
                            class="fa-regular fa-calendar-alt text-orange-500 mr-2 text-lg"></i> Published on
                        <?= date('F d, Y', strtotime($blog->created_at)) ?>
                    </span>
                    <span class="flex items-center"><i class="fa-solid fa-user-circle text-orange-500 mr-2 text-lg"></i>
                        Silva Admin</span>
                </div>

                <h1 class="text-3xl md:text-5xl font-black text-gray-900 mb-8 leading-tight">
                    <?= htmlspecialchars($blog->title) ?>
                </h1>

                <div
                    class="prose prose-lg max-w-none text-gray-600 prose-headings:text-gray-900 prose-p:leading-relaxed prose-a:text-orange-600 hover:prose-a:text-orange-700">
                    <!-- Since content might contain HTML from a rich text editor, we output it. 
                         If it's just plain text, nl2br is needed. Adjust based on your admin panel input type. -->
                    <?= nl2br(htmlspecialchars($blog->content)) ?>
                </div>
            </div>
        </article>

        <!-- Related Articles -->
        <?php if (count($relatedBlogs) > 0): ?>
            <div class="pt-10 border-t border-gray-200">
                <div class="flex items-center justify-between mb-8">
                    <h2 class="text-2xl font-black text-gray-900 border-l-4 border-orange-500 pl-4">Read Next</h2>
                    <a href="/blog.php"
                        class="text-orange-600 font-bold text-sm hover:text-orange-700 transition-colors flex items-center">
                        View All <i class="fa-solid fa-arrow-right ml-1"></i>
                    </a>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <?php foreach ($relatedBlogs as $rb): ?>
                        <a href="blog-details.php?id=<?= $rb->id ?>"
                            class="group bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-all">
                            <div class="h-40 overflow-hidden relative">
                                <img src="/frontend/public/<?= htmlspecialchars($rb->image) ?>"
                                    alt="<?= htmlspecialchars($rb->title) ?>"
                                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                                    onerror="this.onerror=null;this.src='/frontend/public/default-blog.jpg'">
                            </div>
                            <div class="p-5">
                                <span class="text-xs font-bold text-orange-500 block mb-2">
                                    <?= date('M d, Y', strtotime($rb->created_at)) ?>
                                </span>
                                <h3 class="font-bold text-gray-900 line-clamp-2 group-hover:text-orange-600 transition-colors">
                                    <?= htmlspecialchars($rb->title) ?>
                                </h3>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
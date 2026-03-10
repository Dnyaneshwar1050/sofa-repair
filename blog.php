<?php
require_once __DIR__ . '/includes/db.php';

// Fetch all blogs
$stmt = $pdo->query("SELECT * FROM blogs ORDER BY created_at DESC");
$blogs = $stmt->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<!-- Header Banner -->
<div class="relative bg-gray-900 py-24 sm:py-32 overflow-hidden">
    <div class="absolute inset-0">
        <img src="https://images.unsplash.com/photo-1540574163026-643ea20affe2?ixlib=rb-4.0.3&auto=format&fit=crop&w=2070&q=80"
            alt="Blog Header" class="w-full h-full object-cover opacity-20" />
        <div class="absolute inset-0 bg-gradient-to-t from-gray-900 via-gray-900/60 to-transparent"></div>
    </div>
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h1 class="text-4xl sm:text-5xl lg:text-6xl font-black text-white tracking-tight mb-6">
            Our <span class="text-orange-500">Blog</span>
        </h1>
        <p class="mt-4 text-xl text-gray-300 max-w-2xl mx-auto font-medium">
            Tips, tricks, and insights on furniture care, upholstery trends, and home decor from our experts.
        </p>
    </div>
</div>

<div class="bg-gray-50 min-h-screen py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <?php if (count($blogs) > 0): ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php foreach ($blogs as $blog): ?>
                    <article
                        class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-xl transition-all duration-300 group flex flex-col h-full">
                        <div class="relative h-64 overflow-hidden">
                            <img src="/frontend/public/<?= htmlspecialchars($blog->image) ?>"
                                alt="<?= htmlspecialchars($blog->title) ?>"
                                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                                onerror="this.src='/frontend/public/default-blog.jpg'">
                            <div
                                class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                            </div>
                        </div>
                        <div class="p-6 flex-grow flex flex-col">
                            <div class="flex items-center text-xs font-bold text-gray-500 mb-3 space-x-4">
                                <span><i class="fa-regular fa-calendar text-orange-500 mr-1"></i>
                                    <?= date('M d, Y', strtotime($blog->created_at)) ?>
                                </span>
                            </div>
                            <h3
                                class="text-xl font-bold text-gray-900 mb-3 line-clamp-2 group-hover:text-orange-600 transition-colors">
                                <?= htmlspecialchars($blog->title) ?>
                            </h3>
                            <p class="text-gray-600 text-sm line-clamp-3 mb-6 flex-grow">
                                <?= htmlspecialchars(strip_tags($blog->content)) ?>
                            </p>
                            <div class="mt-auto">
                                <a href="blog-details.php?id=<?= $blog->id ?>"
                                    class="inline-flex items-center text-orange-600 font-bold text-sm hover:text-orange-700 transition-colors group/link">
                                    Read Article <i
                                        class="fa-solid fa-arrow-right ml-2 transform group-hover/link:translate-x-1 transition-transform"></i>
                                </a>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-20 bg-white rounded-3xl shadow-sm border border-gray-100">
                <div class="w-24 h-24 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-6">
                    <i class="fa-solid fa-newspaper text-4xl text-gray-300"></i>
                </div>
                <h3 class="text-2xl font-bold text-gray-800 mb-2">No Articles Yet</h3>
                <p class="text-gray-500 max-w-md mx-auto">We're working on some great content. Check back soon for tips on
                    maintaining your furniture.</p>
            </div>
        <?php endif; ?>

    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
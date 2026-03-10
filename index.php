<?php
require_once __DIR__ . '/includes/db.php';

// Fetch categories
$stmtCat = $pdo->query("SELECT * FROM categories ORDER BY name ASC LIMIT 9");
$categories = $stmtCat->fetchAll();

// Fetch latest blogs
$stmtBlog = $pdo->query("SELECT * FROM blogs WHERE status = 'published' ORDER BY created_at DESC LIMIT 3");
$blogs = $stmtBlog->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<div class="bg-gray-50 min-h-screen text-gray-900">
    <!-- Hero Search -->
    <section class="bg-gradient-to-br from-orange-50 to-white px-1 pt-10 pb-16 lg:px-3 text-center">
        <h1 class="text-4xl md:text-5xl font-black text-gray-900 mb-3">Your Home Service Hub</h1>
        <p class="text-lg text-gray-600 mb-8">Find trusted local professionals near you</p>

        <form action="/services.php" method="GET"
            class="flex flex-col md:flex-row items-center justify-center max-w-2xl mx-auto bg-white border border-gray-200 rounded-xl shadow-lg overflow-hidden">
            <input type="text" name="q" placeholder="Search for services..."
                class="grow p-3 text-gray-800 focus:outline-none w-full" />
            <button type="submit"
                class="bg-orange-600 hover:bg-orange-700 text-white px-6 py-3 transition-colors font-semibold w-full md:w-auto">
                <i class="fa-solid fa-search"></i>
            </button>
        </form>

        <div class="mt-10">
            <h3 class="text-xl font-bold mb-4 text-gray-900">Browse Categories</h3>
            <!-- Categories Scroll List -->
            <div class="flex overflow-x-auto gap-4 py-4 px-2 hide-scrollbar justify-start md:justify-center">
                <?php if (count($categories) > 0): ?>
                    <?php foreach ($categories as $category): ?>
                        <a href="/services.php?category_id=<?= $category->id ?>"
                            class="flex-shrink-0 w-32 md:w-40 flex flex-col items-center gap-3 group">
                            <div
                                class="w-20 h-20 md:w-24 md:h-24 rounded-full border-4 border-white shadow-md overflow-hidden bg-white transition-transform group-hover:scale-105">
                                <img src="/frontend/public/<?= htmlspecialchars($category->image) ?>"
                                    alt="<?= htmlspecialchars($category->name) ?>" class="w-full h-full object-cover"
                                    onerror="this.src='/frontend/public/default-category.png'">
                            </div>
                            <span
                                class="text-sm md:text-base font-semibold text-gray-800 text-center group-hover:text-orange-600 transition-colors">
                                <?= htmlspecialchars($category->name) ?>
                            </span>
                        </a>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-gray-500 w-full">No categories available.</p>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Categories Section with Services -->
    <section class="max-w-6xl mx-auto px-4 py-12">
        <div class="flex justify-between items-center mb-8">
            <h2 class="text-3xl font-black">Featured Services</h2>
            <div class="h-1 w-24 bg-orange-500 rounded-full"></div>
        </div>

        <div class="space-y-12">
            <?php
            // Fetch services for each category
            foreach ($categories as $category):
                $stmtSvc = $pdo->prepare("SELECT * FROM services WHERE category_id = ? LIMIT 6");
                $stmtSvc->execute([$category->id]);
                $services = $stmtSvc->fetchAll();

                if (count($services) > 0):
                    ?>
                    <div>
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-2xl font-bold text-gray-800">
                                <?= htmlspecialchars($category->name) ?>
                            </h3>
                            <a href="/services.php?category_id=<?= $category->id ?>"
                                class="text-orange-600 hover:underline font-semibold text-sm flex items-center gap-1">
                                View All <i class="fa-solid fa-arrow-right"></i>
                            </a>
                        </div>

                        <div class="flex overflow-x-auto gap-6 pb-4 hide-scrollbar snap-x">
                            <?php foreach ($services as $service): ?>
                                <div
                                    class="snap-start flex-shrink-0 w-64 md:w-72 bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-lg transition-all group">
                                    <div class="relative h-48 overflow-hidden">
                                        <img src="/frontend/public/<?= htmlspecialchars($service->image) ?>"
                                            alt="<?= htmlspecialchars($service->name) ?>"
                                            class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110"
                                            onerror="this.src='/frontend/public/default-service.png'">
                                        <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
                                        <div class="absolute bottom-4 left-4 text-white">
                                            <h4 class="font-bold text-lg mb-1">
                                                <?= htmlspecialchars($service->name) ?>
                                            </h4>
                                            <p class="text-sm text-gray-200">Starting at ₹
                                                <?= number_format($service->base_price, 2) ?>
                                            </p>
                                        </div>
                                    </div>
                                    <div class="p-4">
                                        <p class="text-gray-600 text-sm line-clamp-2 mb-4">
                                            <?= htmlspecialchars($service->description) ?>
                                        </p>
                                        <a href="/service-details.php?id=<?= $service->id ?>"
                                            class="w-full block text-center bg-orange-50 text-orange-600 font-semibold py-2 rounded-xl group-hover:bg-orange-600 group-hover:text-white transition-colors">
                                            Book Now
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php
                endif;
            endforeach;
            ?>
        </div>
    </section>

    <!-- Blogs Section -->
    <section class="bg-white py-16 border-t border-gray-200">
        <div class="max-w-6xl mx-auto px-4">
            <div class="text-center mb-10">
                <h2 class="text-3xl font-black flex justify-center items-center gap-2">
                    <i class="fa-solid fa-book-open text-orange-600"></i> Latest Articles
                </h2>
                <p class="text-gray-600 text-lg">Insights and tips from our experts</p>
            </div>

            <?php if (count($blogs) > 0): ?>
                <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
                    <?php foreach ($blogs as $blog): ?>
                        <article
                            class="bg-gray-50 border border-gray-200 rounded-2xl overflow-hidden shadow-sm hover:shadow-md transition-all">
                            <?php if ($blog->image): ?>
                                <a href="/blog.php?slug=<?= htmlspecialchars($blog->slug) ?>">
                                    <img src="<?= htmlspecialchars($blog->image) ?>" alt="<?= htmlspecialchars($blog->title) ?>"
                                        class="w-full h-48 object-cover" />
                                </a>
                            <?php endif; ?>
                            <div class="p-6">
                                <h3 class="text-lg font-bold mb-2 hover:text-orange-600 transition-colors">
                                    <a href="/blog.php?slug=<?= htmlspecialchars($blog->slug) ?>">
                                        <?= htmlspecialchars($blog->title) ?>
                                    </a>
                                </h3>
                                <p class="text-gray-600 text-sm line-clamp-3 mb-4">
                                    <?= htmlspecialchars(strip_tags($blog->content)) ?>
                                </p>
                                <a href="/blog.php?slug=<?= htmlspecialchars($blog->slug) ?>"
                                    class="inline-flex items-center text-orange-600 font-semibold text-sm">
                                    Read More <i class="fa-solid fa-arrow-right ml-1"></i>
                                </a>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="text-center text-gray-600">No blogs found.</p>
            <?php endif; ?>
        </div>
    </section>

    <!-- Contact Section -->
    <section class="bg-gradient-to-r from-orange-600 to-orange-500 text-white py-12">
        <div class="max-w-5xl mx-auto px-4 text-center">
            <h2 class="text-3xl font-black mb-3">Need Assistance?</h2>
            <p class="text-lg mb-6">Our experts are always ready to help.</p>
            <div class="space-y-4">
                <a href="tel:+919689861811" class="block text-lg hover:text-orange-100">
                    📞 +919689861811
                </a>
                <a href="mailto:info@khushihomesofarepairing.com" class="block text-lg hover:text-orange-100">
                    📧 info@khushihomesofarepairing.com
                </a>
            </div>
            <a href="/contact.php"
                class="inline-block mt-6 bg-white text-orange-600 font-semibold px-6 py-2 rounded-lg hover:bg-orange-50 transition-colors">
                Contact Us
            </a>
        </div>
    </section>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
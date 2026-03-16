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
    <!-- Premium Hero Section -->
    <section class="relative bg-white pt-20 pb-24 overflow-hidden">
        <!-- Abstract gradient mesh backgrounds -->
        <div class="absolute -top-40 -left-40 w-96 h-96 bg-brand-100 rounded-full mix-blend-multiply filter blur-3xl opacity-70 animate-blob"></div>
        <div class="absolute top-0 -right-40 w-96 h-96 bg-orange-100 rounded-full mix-blend-multiply filter blur-3xl opacity-70 animate-blob animation-delay-2000"></div>
        <div class="absolute -bottom-40 left-20 w-96 h-96 bg-yellow-50 rounded-full mix-blend-multiply filter blur-3xl opacity-70 animate-blob animation-delay-4000"></div>

        <div class="relative max-w-5xl mx-auto px-4 text-center z-10">
            <h1 class="text-5xl md:text-7xl font-heading font-extrabold text-gray-900 mb-6 tracking-tight leading-tight">
                Your Premium <br/><span class="text-transparent bg-clip-text bg-gradient-to-r from-brand-600 to-orange-400">Home Service Hub</span>
            </h1>
            <p class="text-xl text-gray-500 mb-10 max-w-2xl mx-auto font-light">Find trusted, top-rated local professionals near you in seconds.</p>

            <form action="/services.php" method="GET"
                class="flex flex-col md:flex-row items-center justify-center max-w-3xl mx-auto bg-white/80 backdrop-blur-xl border border-white/50 rounded-full shadow-premium p-2 transition-transform hover:scale-[1.02] duration-300">
                <div class="flex-grow flex items-center pl-6 w-full md:w-auto">
                    <i class="fa-solid fa-search text-gray-400 text-lg"></i>
                    <input type="text" name="q" placeholder="What service do you need today?"
                        class="grow p-4 text-gray-800 bg-transparent focus:outline-none w-full placeholder-gray-400 font-medium" />
                </div>
                <button type="submit"
                    class="bg-brand-600 hover:bg-brand-700 text-white px-8 py-4 rounded-full font-semibold w-full md:w-auto shadow-md transition-all active:scale-95 whitespace-nowrap">
                    Search Now
                </button>
            </form>

            <div class="mt-16">
                <!-- Categories Scroll List -->
                <div class="flex overflow-x-auto gap-6 py-6 px-4 hide-scrollbar justify-start md:justify-center">
                    <?php if (count($categories) > 0): ?>
                        <?php foreach ($categories as $category): ?>
                            <a href="/services.php?category_id=<?= $category->id ?>"
                                class="flex-shrink-0 w-32 flex flex-col items-center gap-4 group cursor-pointer">
                                <div
                                    class="w-20 h-20 rounded-2xl shadow-soft group-hover:shadow-premium bg-white transition-all duration-300 group-hover:-translate-y-2 flex items-center justify-center overflow-hidden border border-gray-50">
                                    <img src="/frontend/public/<?= htmlspecialchars($category->image) ?>"
                                        alt="<?= htmlspecialchars($category->name) ?>" class="w-12 h-12 object-contain group-hover:scale-110 transition-transform duration-300"
                                        onerror="this.src='/frontend/public/default-category.png'">
                                </div>
                                <span
                                    class="text-sm font-semibold text-gray-600 text-center group-hover:text-brand-600 transition-colors">
                                    <?= htmlspecialchars($category->name) ?>
                                </span>
                            </a>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-gray-500 w-full">No categories available.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- Categories Section with Services -->
    <section class="max-w-6xl mx-auto px-4 py-20">
        <div class="flex flex-col items-center mb-12 text-center">
            <h2 class="text-4xl font-heading font-bold text-gray-900 mb-4">Featured Services</h2>
            <p class="text-gray-500 max-w-2xl">Discover our most popular repair and cleaning solutions tailored for your premium furniture.</p>
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
                                    class="snap-start flex-shrink-0 w-72 md:w-80 bg-white rounded-2xl shadow-soft border border-gray-100 overflow-hidden hover:shadow-premium transition-all duration-300 group">
                                    <div class="relative h-56 overflow-hidden">
                                        <img src="/frontend/public/<?= htmlspecialchars($service->image) ?>"
                                            alt="<?= htmlspecialchars($service->name) ?>"
                                            class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110"
                                            onerror="this.src='/frontend/public/default-service.png'">
                                        <div class="absolute inset-0 bg-gradient-to-t from-gray-900/80 via-gray-900/20 to-transparent"></div>
                                        <div class="absolute bottom-5 left-5 right-5 text-white">
                                            <h4 class="font-heading font-bold text-xl mb-1 leading-tight">
                                                <?= htmlspecialchars($service->name) ?>
                                            </h4>
                                            <p class="text-sm font-medium text-brand-100">Starting at ₹<?= number_format($service->base_price, 2) ?></p>
                                        </div>
                                    </div>
                                    <div class="p-6">
                                        <p class="text-gray-500 text-sm line-clamp-2 mb-6 leading-relaxed">
                                            <?= htmlspecialchars($service->description) ?>
                                        </p>
                                        <a href="/service-details.php?id=<?= $service->id ?>"
                                            class="w-full flex items-center justify-center bg-gray-50 text-gray-700 font-semibold py-3 rounded-xl group-hover:bg-brand-600 group-hover:text-white transition-all duration-300">
                                            Book Now <i class="fa-solid fa-arrow-right ml-2 opacity-0 group-hover:opacity-100 transition-opacity -translate-x-2 group-hover:translate-x-0 duration-300"></i>
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
                            class="bg-white border border-gray-100 shadow-soft hover:shadow-premium rounded-2xl overflow-hidden transition-all duration-300 group">
                            <?php if ($blog->image): ?>
                                <a href="/blog.php?slug=<?= htmlspecialchars($blog->slug) ?>" class="block relative h-52 overflow-hidden">
                                    <img src="<?= htmlspecialchars($blog->image) ?>" alt="<?= htmlspecialchars($blog->title) ?>"
                                        class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105" />
                                </a>
                            <?php endif; ?>
                            <div class="p-8">
                                <h3 class="text-xl font-heading font-bold mb-3 text-gray-900 group-hover:text-brand-600 transition-colors leading-snug">
                                    <a href="/blog.php?slug=<?= htmlspecialchars($blog->slug) ?>">
                                        <?= htmlspecialchars($blog->title) ?>
                                    </a>
                                </h3>
                                <p class="text-gray-500 text-sm line-clamp-3 mb-6 leading-relaxed">
                                    <?= htmlspecialchars(strip_tags($blog->content)) ?>
                                </p>
                                <a href="/blog.php?slug=<?= htmlspecialchars($blog->slug) ?>"
                                    class="inline-flex items-center text-brand-600 font-semibold text-sm hover:text-brand-700 transition-colors">
                                    Read Article <i class="fa-solid fa-arrow-right ml-2 group-hover:translate-x-1 transition-transform"></i>
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
    <section class="bg-gradient-to-tr from-brand-900 to-brand-700 text-white py-20 relative overflow-hidden">
        <!-- Abstract pattern -->
        <div class="absolute right-0 top-0 opacity-10">
            <svg width="404" height="384" fill="none" viewBox="0 0 404 384"><defs><pattern id="d3eb07ae-5182-43e6-857d-35c643af9034" x="0" y="0" width="20" height="20" patternUnits="userSpaceOnUse"><rect x="0" y="0" width="4" height="4" fill="currentColor"></rect></pattern></defs><rect width="404" height="384" fill="url(#d3eb07ae-5182-43e6-857d-35c643af9034)"></rect></svg>
        </div>
        <div class="max-w-4xl mx-auto px-4 text-center relative z-10">
            <h2 class="text-4xl font-heading font-extrabold mb-4">Ready for a Premium Makeover?</h2>
            <p class="text-xl text-brand-100 mb-10 max-w-2xl mx-auto font-light">Join thousands of happy customers who trust Silva Furniture for their restoration needs.</p>
            
            <div class="flex flex-col sm:flex-row justify-center items-center gap-4">
                <a href="/contact.php"
                    class="bg-white text-brand-700 font-bold px-8 py-4 rounded-full hover:bg-brand-50 shadow-lg active:scale-95 transition-all text-lg w-full sm:w-auto">
                    Contact Us Today
                </a>
                <a href="tel:+919689861811"
                    class="bg-transparent border border-white/30 text-white font-bold px-8 py-4 rounded-full hover:bg-white/10 active:scale-95 transition-all text-lg w-full sm:w-auto backdrop-blur-sm">
                    <i class="fa-solid fa-phone mr-2"></i> +91 9689861811
                </a>
            </div>
        </div>
    </section>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
<?php
require_once __DIR__ . '/includes/db.php';

$categoryId = isset($_GET['category_id']) ? (int) $_GET['category_id'] : null;
$searchQuery = isset($_GET['q']) ? trim($_GET['q']) : '';

$services = [];
$categoryName = "All Services";

if ($categoryId) {
    // Fetch category name
    $stmtCat = $pdo->prepare("SELECT name FROM categories WHERE id = ?");
    $stmtCat->execute([$categoryId]);
    $cat = $stmtCat->fetch();
    if ($cat) {
        $categoryName = $cat->name;
    }

    $stmt = $pdo->prepare("SELECT * FROM services WHERE category_id = ?");
    $stmt->execute([$categoryId]);
    $services = $stmt->fetchAll();
} elseif ($searchQuery) {
    $categoryName = "Search Results for '" . htmlspecialchars($searchQuery) . "'";
    $stmt = $pdo->prepare("SELECT * FROM services WHERE name LIKE ? OR description LIKE ?");
    $searchTerm = '%' . $searchQuery . '%';
    $stmt->execute([$searchTerm, $searchTerm]);
    $services = $stmt->fetchAll();
} else {
    // Fetch all services
    $stmt = $pdo->query("SELECT * FROM services LIMIT 50");
    $services = $stmt->fetchAll();
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="bg-gray-50 min-h-screen text-gray-900 py-12">
    <div class="max-w-6xl mx-auto px-4">
        <div class="mb-8">
            <h1 class="text-3xl font-black mb-2">
                <?= htmlspecialchars($categoryName) ?>
            </h1>
            <div class="h-1 w-24 bg-orange-500 rounded-full"></div>
        </div>

        <?php if (count($services) > 0): ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                <?php foreach ($services as $service): ?>
                    <div
                        class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-lg transition-all group flex flex-col">
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
                        <div class="p-4 flex-grow flex flex-col justify-between">
                            <p class="text-gray-600 text-sm line-clamp-2 mb-4">
                                <?= htmlspecialchars($service->description) ?>
                            </p>
                            <a href="/service-details.php?id=<?= $service->id ?>"
                                class="w-full block text-center bg-orange-50 text-orange-600 font-semibold py-2 rounded-xl group-hover:bg-orange-600 group-hover:text-white transition-colors">
                                View Details & Book
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-16 bg-white rounded-2xl shadow-sm border border-gray-100">
                <i class="fa-solid fa-search text-4xl text-gray-300 mb-4"></i>
                <h3 class="text-xl font-bold text-gray-700">No services found</h3>
                <p class="text-gray-500 mt-2">Try adjusting your search or category filter.</p>
                <a href="/services.php" class="inline-block mt-4 text-orange-600 hover:underline">View all services</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
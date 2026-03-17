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
                    <div class="bg-white rounded-[20px] shadow-sm border border-gray-100 overflow-hidden flex flex-col group transition-all duration-300 hover:shadow-md">
                        <!-- Top Image -->
                        <div class="relative h-56 overflow-hidden">
                            <img src="/frontend/public/<?= htmlspecialchars($service->image) ?>"
                                alt="<?= htmlspecialchars($service->name) ?>"
                                class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                                onerror="this.onerror=null;this.src='/frontend/public/default-service.png'">
                        </div>
                        
                        <!-- Card Content -->
                        <div class="p-6 flex-grow flex flex-col">
                            <!-- Title -->
                            <h3 class="font-bold text-lg text-gray-900 mb-1 leading-tight">
                                <?= htmlspecialchars($service->name) ?>
                            </h3>
                            
                            <!-- Static Rating (Placeholder since no rating column exists per service) -->
                            <div class="flex items-center gap-1 mb-3">
                                <i class="fa-solid fa-star text-orange-500 text-sm"></i>
                                <span class="text-sm font-medium text-gray-700">4.0 (50 reviews)</span>
                            </div>
                            
                            <!-- Contact for Pricing -->
                            <a href="/service-details.php?id=<?= $service->id ?>" class="text-blue-600 font-semibold mb-3 hover:text-blue-700 transition">
                                Contact for Pricing
                            </a>
                            
                            <!-- Short Description -->
                            <p class="text-gray-600 text-sm line-clamp-2 leading-relaxed mb-6">
                                <?= htmlspecialchars($service->description) ?>
                            </p>
                            
                            <!-- Full Width Button -->
                            <div class="mt-auto">
                                <a href="/service-details.php?id=<?= $service->id ?>"
                                    class="w-full block text-center bg-blue-600 text-white font-semibold py-3 rounded-lg hover:bg-blue-700 transition-colors shadow-sm">
                                    Get Quote
                                </a>
                            </div>
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
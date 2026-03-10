<?php
require_once __DIR__ . '/../includes/db.php';
requireLogin();
requireRole(['admin', 'superadmin']);

$success = '';
$error = '';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create' || $action === 'update') {
        $id = $_POST['id'] ?? null;
        $category_id = trim($_POST['category_id'] ?? '');
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $base_price = trim($_POST['base_price'] ?? 0);
        $image = trim($_POST['image'] ?? 'default-service.png');

        if (empty($name) || empty($category_id) || empty($base_price)) {
            $error = 'Name, category, and base price are required.';
        } else {
            if ($action === 'update' && $id) {
                $stmt = $pdo->prepare("UPDATE services SET category_id = ?, name = ?, description = ?, base_price = ?, image = ? WHERE id = ?");
                if ($stmt->execute([$category_id, $name, $description, $base_price, $image, $id])) {
                    $success = 'Service updated successfully.';
                } else {
                    $error = 'Failed to update service.';
                }
            } else {
                $stmt = $pdo->prepare("INSERT INTO services (category_id, name, description, base_price, image) VALUES (?, ?, ?, ?, ?)");
                if ($stmt->execute([$category_id, $name, $description, $base_price, $image])) {
                    $success = 'Service created successfully.';
                } else {
                    $error = 'Failed to create service.';
                }
            }
        }
    } elseif ($action === 'delete') {
        $id = $_POST['id'] ?? null;
        if ($id) {
            $stmt = $pdo->prepare("DELETE FROM services WHERE id = ?");
            if ($stmt->execute([$id])) {
                $success = 'Service deleted successfully.';
            } else {
                $error = 'Failed to delete service.';
            }
        }
    }
}

// Fetch categories for dropdown
$categories = $pdo->query("SELECT id, name FROM categories ORDER BY name ASC")->fetchAll();

// Fetch services joined with categories
$stmt = $pdo->query("
    SELECT s.*, c.name as category_name 
    FROM services s 
    LEFT JOIN categories c ON s.category_id = c.id 
    ORDER BY s.name ASC
");
$services = $stmt->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<div class="mb-6 flex justify-between items-center">
    <div>
        <h1 class="text-2xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-gray-900 to-gray-600">Services
            Management</h1>
        <p class="text-gray-500 text-sm">Create and manage offered services.</p>
    </div>
    <button onclick="document.getElementById('service-form-modal').classList.remove('hidden')"
        class="bg-orange-600 text-white px-4 py-2 rounded-lg font-bold hover:bg-orange-700 transition flex items-center gap-2 text-sm">
        <i class="fa-solid fa-plus"></i> Add Service
    </button>
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
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider border-b border-gray-100">
                    <th class="p-4 font-bold w-16">ID</th>
                    <th class="p-4 font-bold w-24">Image</th>
                    <th class="p-4 font-bold">Service Details</th>
                    <th class="p-4 font-bold">Category</th>
                    <th class="p-4 font-bold">Base Price</th>
                    <th class="p-4 font-bold text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php foreach ($services as $service): ?>
                    <tr class="hover:bg-gray-50 transition p-2">
                        <td class="p-4 font-bold text-gray-700">#
                            <?= $service->id ?>
                        </td>
                        <td class="p-4">
                            <img src="/frontend/public/<?= htmlspecialchars($service->image) ?>"
                                alt="<?= htmlspecialchars($service->name) ?>"
                                class="w-16 h-12 rounded bg-gray-100 object-cover border border-gray-200"
                                onerror="this.src='/frontend/public/default-service.png'">
                        </td>
                        <td class="p-4">
                            <p class="font-bold text-gray-800">
                                <?= htmlspecialchars($service->name) ?>
                            </p>
                            <p class="text-sm text-gray-500 line-clamp-1 max-w-xs">
                                <?= htmlspecialchars($service->description) ?>
                            </p>
                        </td>
                        <td class="p-4 font-medium text-gray-600 text-sm">
                            <span class="bg-gray-100 px-2.5 py-1 rounded-md border border-gray-200">
                                <?= htmlspecialchars($service->category_name) ?>
                            </span>
                        </td>
                        <td class="p-4 font-bold text-orange-600">₹
                            <?= number_format($service->base_price, 2) ?>
                        </td>
                        <td class="p-4 text-right space-x-2">
                            <button onclick="editService(<?= htmlspecialchars(json_encode($service)) ?>)"
                                class="text-blue-600 hover:text-blue-800 w-8 h-8 rounded-full bg-blue-50 hover:bg-blue-100 transition inline-flex items-center justify-center">
                                <i class="fa-solid fa-pen"></i>
                            </button>
                            <form method="POST" class="inline-block"
                                onsubmit="return confirm('Are you sure you want to delete this service?');">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= $service->id ?>">
                                <button type="submit"
                                    class="text-red-600 hover:text-red-800 w-8 h-8 rounded-full bg-red-50 hover:bg-red-100 transition inline-flex items-center justify-center">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (count($services) === 0): ?>
                    <tr>
                        <td colspan="6" class="p-8 text-center text-gray-500 font-medium">No services found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Service Form Modal -->
<div id="service-form-modal"
    class="hidden fixed inset-0 bg-gray-900 bg-opacity-50 z-50 flex items-center justify-center px-4 backdrop-blur-sm pt-10 pb-10">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-xl overflow-y-auto max-h-screen transform transition-all">
        <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50 sticky top-0 z-10">
            <h3 class="text-lg font-bold text-gray-800" id="modal-title">Add Service</h3>
            <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600 transition">
                <i class="fa-solid fa-times text-xl"></i>
            </button>
        </div>
        <form method="POST" action="services.php" class="p-6">
            <input type="hidden" name="action" id="form-action" value="create">
            <input type="hidden" name="id" id="form-id" value="">

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Service Name <span
                            class="text-red-500">*</span></label>
                    <input type="text" name="name" id="form-name" required
                        class="w-full p-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 outline-none transition">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Category <span
                                class="text-red-500">*</span></label>
                        <select name="category_id" id="form-category" required
                            class="w-full p-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 outline-none transition bg-white">
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat->id ?>">
                                    <?= htmlspecialchars($cat->name) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Base Price (₹) <span
                                class="text-red-500">*</span></label>
                        <input type="number" step="0.01" name="base_price" id="form-price" required
                            class="w-full p-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 outline-none transition">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea name="description" id="form-description" rows="4"
                        class="w-full p-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 outline-none transition"></textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Image Filename</label>
                    <input type="text" name="image" id="form-image" value="default-service.png"
                        class="w-full p-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 outline-none transition"
                        placeholder="e.g., modern-sofa.jpg">
                    <p class="text-xs text-gray-500 mt-1">Image must exist in /frontend/public/ folder</p>
                </div>
            </div>

            <div class="mt-8 flex justify-end gap-3 pt-4 border-t border-gray-100">
                <button type="button" onclick="closeModal()"
                    class="px-4 py-2 text-gray-600 font-medium hover:bg-gray-100 rounded-lg transition">Cancel</button>
                <button type="submit"
                    class="bg-orange-600 text-white px-6 py-2 rounded-lg font-bold hover:bg-orange-700 transition shadow-md">Save
                    Service</button>
            </div>
        </form>
    </div>
</div>

<script>
    function editService(service) {
        document.getElementById('modal-title').innerText = 'Edit Service';
        document.getElementById('form-action').value = 'update';
        document.getElementById('form-id').value = service.id;
        document.getElementById('form-name').value = service.name;
        document.getElementById('form-category').value = service.category_id;
        document.getElementById('form-price').value = service.base_price;
        document.getElementById('form-description').value = service.description;
        document.getElementById('form-image').value = service.image;
        document.getElementById('service-form-modal').classList.remove('hidden');
    }

    function closeModal() {
        document.getElementById('service-form-modal').classList.add('hidden');
        document.getElementById('form-action').value = 'create';
        document.getElementById('form-id').value = '';
        document.getElementById('form-name').value = '';
        document.getElementById('form-category').value = '';
        document.getElementById('form-price').value = '';
        document.getElementById('form-description').value = '';
        document.getElementById('form-image').value = 'default-service.png';
        document.getElementById('modal-title').innerText = 'Add Service';
    }
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
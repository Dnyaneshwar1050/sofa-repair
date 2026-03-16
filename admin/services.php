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
        $max_expected_price = trim($_POST['max_expected_price'] ?? 0);
        $is_disabled = isset($_POST['is_disabled']) ? 1 : 0;
        
        // Handle dynamic Service Options parsing
        $options_names = $_POST['option_name'] ?? [];
        $options_prices = $_POST['option_price'] ?? [];
        $options_desc = $_POST['option_description'] ?? [];
        
        $service_options = [];
        for ($i = 0; $i < count($options_names); $i++) {
            if (!empty(trim($options_names[$i]))) {
                $service_options[] = [
                    'name' => trim($options_names[$i]),
                    'price_diff' => (float)($options_prices[$i] ?? 0),
                    'description' => trim($options_desc[$i] ?? '')
                ];
            }
        }
        $service_options_json = json_encode($service_options);

        // Handle File Upload for Main Image
        if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = __DIR__ . '/../frontend/public/';
            $file_extension = strtolower(pathinfo($_FILES['image_file']['name'], PATHINFO_EXTENSION));
            $new_filename = 'service_' . uniqid() . '.' . $file_extension;
            
            if (move_uploaded_file($_FILES['image_file']['tmp_name'], $upload_dir . $new_filename)) {
                $image = $new_filename;
            } else {
                $error = 'Failed to upload main image.';
            }
        }
        
        // Handle Multiple Gallery Images
        $gallery_images = [];
        $existing_gallery = json_decode($_POST['existing_gallery'] ?? '[]', true) ?: [];
        
        if (isset($_FILES['gallery_files'])) {
            $upload_dir = __DIR__ . '/../frontend/public/';
            $file_count = count($_FILES['gallery_files']['name']);
            
            for ($i = 0; $i < $file_count; $i++) {
                if ($_FILES['gallery_files']['error'][$i] === UPLOAD_ERR_OK) {
                    $file_extension = strtolower(pathinfo($_FILES['gallery_files']['name'][$i], PATHINFO_EXTENSION));
                    $new_filename = 'gallery_' . uniqid() . '.' . $file_extension;
                    
                    if (move_uploaded_file($_FILES['gallery_files']['tmp_name'][$i], $upload_dir . $new_filename)) {
                        $gallery_images[] = $new_filename;
                    }
                }
            }
        }
        
        // Merge with existing images up to max 5
        $final_gallery = array_slice(array_merge($existing_gallery, $gallery_images), 0, 5);
        $gallery_json = json_encode($final_gallery);

        if (empty($name) || empty($category_id) || empty($base_price)) {
            $error = 'Name, category, and base price are required.';
        } else if (empty($error)) {
            if ($action === 'update' && $id) {
                $stmt = $pdo->prepare("UPDATE services SET category_id = ?, name = ?, description = ?, base_price = ?, image = ?, max_expected_price = ?, is_disabled = ?, service_options = ?, gallery_images = ? WHERE id = ?");
                if ($stmt->execute([$category_id, $name, $description, $base_price, $image, $max_expected_price, $is_disabled, $service_options_json, $gallery_json, $id])) {
                    $success = 'Service updated successfully.';
                } else {
                    $error = 'Failed to update service.';
                }
            } else {
                $stmt = $pdo->prepare("INSERT INTO services (category_id, name, description, base_price, image, max_expected_price, is_disabled, service_options, gallery_images) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                if ($stmt->execute([$category_id, $name, $description, $base_price, $image, $max_expected_price, $is_disabled, $service_options_json, $gallery_json])) {
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
        class="bg-green-600 text-white px-5 py-2.5 rounded-lg font-bold hover:bg-green-700 transition shadow-sm flex items-center gap-2 text-sm tracking-wide">
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
                    <th class="p-4 font-bold text-center">Status</th>
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
                            <?php if (!empty($service->max_expected_price)): ?>
                                <span class="text-xs text-gray-400 block font-normal">- ₹<?= number_format($service->max_expected_price, 2) ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="p-4 text-center">
                            <?php if ($service->is_disabled): ?>
                                <span class="bg-red-100 text-red-700 px-2 py-1 rounded text-xs font-bold">Disabled</span>
                            <?php else: ?>
                                <span class="bg-green-100 text-green-700 px-2 py-1 rounded text-xs font-bold">Enabled</span>
                            <?php endif; ?>
                        </td>
                        <td class="p-4 text-right space-x-2">
                            <button onclick='editService(<?= htmlspecialchars(json_encode($service), ENT_QUOTES, "UTF-8") ?>)'
                                class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1.5 rounded-md text-xs font-bold transition shadow-sm inline-flex items-center gap-1">
                                Edit
                            </button>
                            <form method="POST" class="inline-block"
                                onsubmit="return confirm('<?= $service->is_disabled ? 'Enable' : 'Disable' ?> this service?');">
                                <input type="hidden" name="action" value="update">
                                <input type="hidden" name="id" value="<?= $service->id ?>">
                                <input type="hidden" name="name" value="<?= htmlspecialchars($service->name) ?>">
                                <input type="hidden" name="category_id" value="<?= $service->category_id ?>">
                                <input type="hidden" name="base_price" value="<?= $service->base_price ?>">
                                <?php if (!$service->is_disabled): ?>
                                    <input type="hidden" name="is_disabled" value="1">
                                <?php endif; ?>
                                <button type="submit"
                                    class="bg-orange-500 hover:bg-orange-600 text-white px-3 py-1.5 rounded-md text-xs font-bold transition shadow-sm inline-flex items-center gap-1">
                                    <?= $service->is_disabled ? 'Enable' : 'Disable' ?>
                                </button>
                            </form>
                            <form method="POST" class="inline-block"
                                onsubmit="return confirm('Are you sure you want to delete this service?');">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= $service->id ?>">
                                <button type="submit"
                                    class="bg-red-600 hover:bg-red-700 text-white px-3 py-1.5 rounded-md text-xs font-bold transition shadow-sm inline-flex items-center gap-1">
                                    Delete
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
        <form method="POST" action="services.php" enctype="multipart/form-data" class="p-6">
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
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Maximum Expected Price (₹)</label>
                        <input type="number" step="0.01" name="max_expected_price" id="form-max-price"
                            class="w-full p-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 outline-none transition">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea name="description" id="form-description" rows="4"
                        class="w-full p-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 outline-none transition"></textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1 text-lg border-b pb-2 mt-4 font-bold">Service Images (Max 5)</label>
                    
                    <div class="mt-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Main Featured Image (Thumbnail)</label>
                        <input type="file" name="image_file" id="form-image-file" accept="image/*"
                            class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 transition">
                        <input type="hidden" name="existing_image" id="form-existing-image" value="default-service.png">
                    </div>
                    
                    <div class="mt-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Gallery Images (Multiple)</label>
                        <input type="file" name="gallery_files[]" id="form-gallery-files" accept="image/*" multiple
                            class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200 transition">
                        <input type="hidden" name="existing_gallery" id="form-existing-gallery" value="[]">
                    </div>
                </div>
                
                <div class="mt-6 flex items-center">
                    <input type="checkbox" name="is_disabled" id="form-is-disabled" value="1" class="w-4 h-4 text-orange-600 bg-gray-100 border-gray-300 rounded focus:ring-orange-500">
                    <label for="form-is-disabled" class="ml-2 text-sm font-medium text-gray-700">Disable Service (Hide from public view)</label>
                </div>

                <div class="mt-8 border-t border-gray-200 pt-6">
                    <div class="flex justify-between items-center mb-4">
                        <h4 class="text-lg font-bold text-gray-800">Service Options/Packages</h4>
                    </div>
                    <div id="service-options-container" class="space-y-4">
                        <!-- Options will be injected here via JS -->
                    </div>
                    <button type="button" onclick="addOptionField()" class="mt-4 w-full py-2 bg-gray-100 text-gray-700 hover:bg-gray-200 rounded-lg text-sm font-medium transition flex items-center justify-center gap-2">
                        <i class="fa-solid fa-plus"></i> Add Another Option
                    </button>
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
    let optionCount = 0;

    function addOptionField(name = '', price = '', desc = '') {
        optionCount++;
        const container = document.getElementById('service-options-container');
        const html = `
            <div id="option-group-${optionCount}" class="border border-gray-200 rounded-lg p-4 bg-gray-50 relative">
                <button type="button" onclick="removeOptionField(${optionCount})" class="absolute top-4 right-4 text-red-500 hover:text-red-700">
                    <i class="fa-solid fa-trash"></i>
                </button>
                <div class="mb-3">
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">Option Title</label>
                    <input type="text" name="option_name[]" value="${name}" placeholder="e.g. Basic Package" class="w-full p-2 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 outline-none text-sm">
                </div>
                <div class="mb-3">
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">Price Difference (₹)</label>
                    <input type="number" step="0.01" name="option_price[]" value="${price}" placeholder="e.g. 0 or 500" class="w-full p-2 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 outline-none text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">Option Description</label>
                    <input type="text" name="option_description[]" value="${desc}" placeholder="Brief details" class="w-full p-2 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 outline-none text-sm">
                </div>
            </div>
        `;
        container.insertAdjacentHTML('beforeend', html);
    }

    function removeOptionField(id) {
        document.getElementById(`option-group-${id}`).remove();
    }

    function editService(service) {
        document.getElementById('modal-title').innerText = 'Edit Service';
        document.getElementById('form-action').value = 'update';
        document.getElementById('form-id').value = service.id;
        document.getElementById('form-name').value = service.name;
        document.getElementById('form-category').value = service.category_id;
        document.getElementById('form-price').value = service.base_price;
        document.getElementById('form-max-price').value = service.max_expected_price || '';
        document.getElementById('form-description').value = service.description;
        document.getElementById('form-existing-image').value = service.image;
        document.getElementById('form-existing-gallery').value = service.gallery_images || '[]';
        document.getElementById('form-image-file').value = '';
        document.getElementById('form-gallery-files').value = '';
        document.getElementById('form-is-disabled').checked = service.is_disabled == 1;
        
        // Clear and populate options
        document.getElementById('service-options-container').innerHTML = '';
        let options = [];
        try {
            options = JSON.parse(service.service_options) || [];
        } catch(e) {}
        
        if (options.length > 0) {
            options.forEach(opt => addOptionField(opt.name, opt.price_diff, opt.description));
        } else {
            addOptionField(); // Add one empty by default
        }
        
        document.getElementById('service-form-modal').classList.remove('hidden');
    }

    function closeModal() {
        document.getElementById('service-form-modal').classList.add('hidden');
        document.getElementById('form-action').value = 'create';
        document.getElementById('form-id').value = '';
        document.getElementById('form-name').value = '';
        document.getElementById('form-category').value = '';
        document.getElementById('form-price').value = '';
        document.getElementById('form-max-price').value = '';
        document.getElementById('form-description').value = '';
        document.getElementById('form-existing-image').value = 'default-service.png';
        document.getElementById('form-existing-gallery').value = '[]';
        document.getElementById('form-image-file').value = '';
        document.getElementById('form-gallery-files').value = '';
        document.getElementById('form-is-disabled').checked = false;
        
        document.getElementById('service-options-container').innerHTML = '';
        addOptionField(); // Add one empty by default
        
        document.getElementById('modal-title').innerText = 'Add Service';
    }
    
    // Initialize one empty option field on page load
    document.addEventListener('DOMContentLoaded', () => {
        addOptionField();
    });
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
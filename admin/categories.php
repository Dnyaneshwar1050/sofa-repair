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
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $image = trim($_POST['existing_image'] ?? 'default-category.png');

        // Handle File Upload
        if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = __DIR__ . '/../frontend/public/';
            $file_extension = pathinfo($_FILES['image_file']['name'], PATHINFO_EXTENSION);
            $new_filename = 'category_' . uniqid() . '.' . $file_extension;
            
            if (move_uploaded_file($_FILES['image_file']['tmp_name'], $upload_dir . $new_filename)) {
                $image = $new_filename;
            } else {
                $error = 'Failed to upload image.';
            }
        }

        if (empty($name)) {
            $error = 'Category name is required.';
        } else if (empty($error)) {
            if ($action === 'update' && $id) {
                $stmt = $pdo->prepare("UPDATE categories SET name = ?, description = ?, image = ? WHERE id = ?");
                if ($stmt->execute([$name, $description, $image, $id])) {
                    $success = 'Category updated successfully.';
                } else {
                    $error = 'Failed to update category.';
                }
            } else {
                $stmt = $pdo->prepare("INSERT INTO categories (name, description, image) VALUES (?, ?, ?)");
                if ($stmt->execute([$name, $description, $image])) {
                    $success = 'Category created successfully.';
                } else {
                    $error = 'Failed to create category.';
                }
            }
        }
    } elseif ($action === 'delete') {
        $id = $_POST['id'] ?? null;
        if ($id) {
            $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
            if ($stmt->execute([$id])) {
                $success = 'Category deleted successfully.';
            } else {
                $error = 'Failed to delete category.';
            }
        }
    }
}

// Fetch categories
$stmt = $pdo->query("SELECT * FROM categories ORDER BY name ASC");
$categories = $stmt->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<div class="mb-6 flex justify-between items-center">
    <div>
        <h1 class="text-2xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-gray-900 to-gray-600">
            Categories Management</h1>
        <p class="text-gray-500 text-sm">Create and manage service categories.</p>
    </div>
    <button onclick="document.getElementById('category-form-modal').classList.remove('hidden')"
        class="bg-green-600 text-white px-5 py-2.5 rounded-lg font-bold hover:bg-green-700 transition shadow-sm flex items-center gap-2 text-sm tracking-wide">
        <i class="fa-solid fa-plus"></i> Add Category
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
                    <th class="p-4 font-bold">Name</th>
                    <th class="p-4 font-bold hidden md:table-cell">Description</th>
                    <th class="p-4 font-bold text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php foreach ($categories as $category): ?>
                    <tr class="hover:bg-gray-50 transition p-2">
                        <td class="p-4 font-bold text-gray-700">#
                            <?= $category->id ?>
                        </td>
                        <td class="p-4">
                            <img src="/frontend/public/<?= htmlspecialchars($category->image) ?>"
                                alt="<?= htmlspecialchars($category->name) ?>"
                                class="w-12 h-12 rounded bg-gray-100 object-cover"
                                onerror="this.src='/frontend/public/default-category.png'">
                        </td>
                        <td class="p-4 font-medium text-gray-800">
                            <?= htmlspecialchars($category->name) ?>
                        </td>
                        <td class="p-4 text-gray-500 text-sm hidden md:table-cell max-w-xs truncate">
                            <?= htmlspecialchars($category->description) ?>
                        </td>
                        <td class="p-4 text-right space-x-2">
                            <button onclick="editCategory(<?= htmlspecialchars(json_encode($category)) ?>)"
                                class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-1.5 rounded-md text-xs font-bold transition shadow-sm inline-flex items-center gap-1">
                                Edit
                            </button>
                            <form method="POST" class="inline-block"
                                onsubmit="return confirm('Are you sure you want to delete this category?');">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= $category->id ?>">
                                <button type="submit"
                                    class="bg-red-600 hover:bg-red-700 text-white px-4 py-1.5 rounded-md text-xs font-bold transition shadow-sm inline-flex items-center gap-1">
                                    Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (count($categories) === 0): ?>
                    <tr>
                        <td colspan="5" class="p-8 text-center text-gray-500 font-medium">No categories found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Category Form Modal -->
<div id="category-form-modal"
    class="hidden fixed inset-0 bg-gray-900 bg-opacity-50 z-50 flex items-center justify-center px-4 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md overflow-hidden transform transition-all">
        <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
            <h3 class="text-lg font-bold text-gray-800" id="modal-title">Add Category</h3>
            <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600 transition">
                <i class="fa-solid fa-times text-xl"></i>
            </button>
        </div>
        <form method="POST" action="categories.php" enctype="multipart/form-data" class="p-6">
            <input type="hidden" name="action" id="form-action" value="create">
            <input type="hidden" name="id" id="form-id" value="">

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Name <span
                            class="text-red-500">*</span></label>
                    <input type="text" name="name" id="form-name" required
                        class="w-full p-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 outline-none transition">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea name="description" id="form-description" rows="3"
                        class="w-full p-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 outline-none transition"></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Category Image</label>
                    <input type="file" name="image_file" id="form-image-file" accept="image/*"
                        class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-orange-50 file:text-orange-700 hover:file:bg-orange-100 transition">
                    <p class="text-xs text-gray-500 mt-1">Leave empty to keep existing image</p>
                    <input type="hidden" name="existing_image" id="form-existing-image" value="default-category.png">
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-3 pt-4 border-t border-gray-100">
                <button type="button" onclick="closeModal()"
                    class="px-4 py-2 text-gray-600 font-medium hover:bg-gray-100 rounded-lg transition">Cancel</button>
                <button type="submit"
                    class="bg-orange-600 text-white px-6 py-2 rounded-lg font-bold hover:bg-orange-700 transition shadow-md">Save
                    Category</button>
            </div>
        </form>
    </div>
</div>

<script>
    function editCategory(category) {
        document.getElementById('modal-title').innerText = 'Edit Category';
        document.getElementById('form-action').value = 'update';
        document.getElementById('form-id').value = category.id;
        document.getElementById('form-name').value = category.name;
        document.getElementById('form-description').value = category.description;
        document.getElementById('form-existing-image').value = category.image;
        document.getElementById('form-image-file').value = '';
        document.getElementById('category-form-modal').classList.remove('hidden');
    }

    function closeModal() {
        document.getElementById('category-form-modal').classList.add('hidden');
        document.getElementById('form-action').value = 'create';
        document.getElementById('form-id').value = '';
        document.getElementById('form-name').value = '';
        document.getElementById('form-description').value = '';
        document.getElementById('form-existing-image').value = 'default-category.png';
        document.getElementById('form-image-file').value = '';
        document.getElementById('modal-title').innerText = 'Add Category';
    }
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
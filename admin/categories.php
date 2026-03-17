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
    } elseif ($action === 'toggle_status') {
        $id = $_POST['id'] ?? null;
        $status = $_POST['status'] ?? 0;
        if ($id !== null) {
            $stmt = $pdo->prepare("UPDATE categories SET is_disabled = ? WHERE id = ?");
            if ($stmt->execute([$status, $id])) {
                $success = 'Category status updated successfully.';
            } else {
                $error = 'Failed to update status.';
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

<!-- Updated Header to match screenshot -->
<div class="mb-8 flex justify-between items-start">
    <div>
        <h1 class="text-3xl font-bold text-gray-900 mb-1">Category Management</h1>
        <p class="text-gray-500 text-sm">Use the buttons to control the public visibility of categories.</p>
    </div>
    <button onclick="document.getElementById('category-form-modal').classList.remove('hidden')"
        class="bg-[#2563eb] text-white px-6 py-2.5 rounded-lg font-bold hover:bg-blue-700 transition shadow-md flex items-center gap-2 text-sm">
        <i class="fa-solid fa-plus"></i> Create Category
    </button>
</div>

<?php if ($success): ?>
    <div class="bg-green-50 text-green-700 p-4 rounded-xl mb-6 flex items-center shadow-sm border border-green-200">
        <i class="fa-solid fa-circle-check mr-2 text-xl block"></i>
        <?= htmlspecialchars($success) ?>
    </div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="bg-red-50 text-red-700 p-4 rounded-xl mb-6 flex items-center shadow-sm border border-red-200">
        <i class="fa-solid fa-circle-exclamation mr-2 text-xl block"></i>
        <?= htmlspecialchars($error) ?>
    </div>
<?php endif; ?>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50 text-gray-400 text-[11px] uppercase tracking-wider border-b border-gray-100">
                    <th class="p-6 font-bold">Name</th>
                    <th class="p-6 font-bold">Slug</th>
                    <th class="p-6 font-bold">Status</th>
                    <th class="p-6 font-bold">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                <?php foreach ($categories as $category): ?>
                    <tr class="hover:bg-gray-50 transition group">
                        <td class="p-6">
                            <div class="flex items-center gap-3">
                                <?php if ($category->image): ?>
                                    <img src="/frontend/public/<?= htmlspecialchars($category->image) ?>" 
                                         class="w-10 h-10 rounded-lg object-cover shadow-sm"
                                         onerror="this.onerror=null;this.src='/frontend/public/default-category.png'">
                                <?php endif; ?>
                                <span class="font-bold text-gray-900 text-sm"><?= htmlspecialchars($category->name) ?></span>
                            </div>
                        </td>
                        <td class="p-6">
                            <span class="text-gray-400 text-sm"><?= strtolower(str_replace(' ', '-', $category->name)) ?></span>
                        </td>
                        <td class="p-6">
                            <?php if ($category->is_disabled): ?>
                                <span class="bg-red-50 text-red-600 text-[10px] font-bold px-3 py-1 rounded-full uppercase">Disabled</span>
                            <?php else: ?>
                                <span class="bg-green-50 text-green-600 text-[10px] font-bold px-3 py-1 rounded-full uppercase">Enabled</span>
                            <?php endif; ?>
                        </td>
                        <td class="p-6 flex items-center gap-3">
                            <button onclick="editCategory(<?= htmlspecialchars(json_encode($category)) ?>)"
                                class="text-yellow-600 font-bold text-xs hover:underline">
                                Edit
                            </button>
                            
                            <form method="POST" class="inline-block">
                                <input type="hidden" name="action" value="toggle_status">
                                <input type="hidden" name="id" value="<?= $category->id ?>">
                                <input type="hidden" name="status" value="<?= $category->is_disabled ? 0 : 1 ?>">
                                <?php if ($category->is_disabled): ?>
                                    <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded-lg text-xs font-bold hover:bg-green-700 transition w-24 shadow-sm">
                                        Enable
                                    </button>
                                <?php else: ?>
                                    <button type="submit" class="bg-orange-500 text-white px-6 py-2 rounded-lg text-xs font-bold hover:bg-orange-600 transition w-24 shadow-sm">
                                        Disable
                                    </button>
                                <?php endif; ?>
                            </form>

                            <form method="POST" class="hidden md:inline-block" onsubmit="return confirm('Are you sure?');">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= $category->id ?>">
                                <button type="submit" class="text-red-600 hover:text-red-800 transition p-2">
                                    <i class="fa-solid fa-trash-can"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (count($categories) === 0): ?>
                    <tr>
                        <td colspan="4" class="p-12 text-center">
                            <div class="flex flex-col items-center opacity-40">
                                <i class="fa-solid fa-folder-open text-4xl mb-4"></i>
                                <span class="font-medium">No categories found.</span>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal logic stays similar but styled -->
<div id="category-form-modal"
    class="hidden fixed inset-0 bg-gray-900 bg-opacity-50 z-50 flex items-center justify-center px-4 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center">
            <h3 class="text-lg font-bold text-gray-900" id="modal-title">Create Category</h3>
            <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fa-solid fa-times text-xl"></i>
            </button>
        </div>
        <form method="POST" action="categories.php" enctype="multipart/form-data" class="p-6">
            <input type="hidden" name="action" id="form-action" value="create">
            <input type="hidden" name="id" id="form-id" value="">

            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Category Name</label>
                    <input type="text" name="name" id="form-name" required placeholder="e.g. Sofa Cleaning"
                        class="w-full p-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none transition text-sm">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Description</label>
                    <textarea name="description" id="form-description" rows="3" placeholder="Briefly describe what this category covers..."
                        class="w-full p-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none transition text-sm"></textarea>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Cover Image</label>
                    <input type="file" name="image_file" id="form-image-file" accept="image/*"
                        class="w-full text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 transition">
                    <input type="hidden" name="existing_image" id="form-existing-image" value="default-category.png">
                </div>
            </div>

            <div class="mt-8 flex justify-end gap-3">
                <button type="button" onclick="closeModal()"
                    class="px-5 py-2 text-gray-500 font-bold text-sm hover:bg-gray-50 rounded-lg transition">Cancel</button>
                <button type="submit"
                    class="bg-blue-600 text-white px-6 py-2 rounded-lg font-bold text-sm hover:bg-blue-700 transition shadow-md">
                    Save Category
                </button>
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
        document.getElementById('category-form-modal').classList.remove('hidden');
    }

    function closeModal() {
        document.getElementById('category-form-modal').classList.add('hidden');
        document.getElementById('form-action').value = 'create';
        document.getElementById('form-id').value = '';
        document.getElementById('form-name').value = '';
        document.getElementById('form-description').value = '';
        document.getElementById('modal-title').innerText = 'Create Category';
    }
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
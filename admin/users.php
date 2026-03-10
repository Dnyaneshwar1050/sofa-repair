<?php
require_once __DIR__ . '/../includes/db.php';
requireLogin();
requireRole(['superadmin']); // Only superadmin can manage users

$success = '';
$error = '';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create' || $action === 'update') {
        $id = $_POST['id'] ?? null;
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $role = trim($_POST['role'] ?? 'user');
        $password = trim($_POST['password'] ?? '');

        if (empty($name) || empty($email)) {
            $error = 'Name and email are required.';
        } else {
            if ($action === 'update' && $id) {
                if (!empty($password)) {
                    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, phone = ?, role = ?, password = ? WHERE id = ?");
                    if ($stmt->execute([$name, $email, $phone, $role, $hashedPassword, $id])) {
                        $success = 'User updated successfully with new password.';
                    } else {
                        $error = 'Failed to update user.';
                    }
                } else {
                    $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, phone = ?, role = ? WHERE id = ?");
                    if ($stmt->execute([$name, $email, $phone, $role, $id])) {
                        $success = 'User updated successfully.';
                    } else {
                        $error = 'Failed to update user.';
                    }
                }
            } else {
                if (empty($password)) {
                    $error = 'Password is required for new users.';
                } else {
                    // Check email
                    $stmtCheck = $pdo->prepare("SELECT id FROM users WHERE email = ?");
                    $stmtCheck->execute([$email]);
                    if ($stmtCheck->rowCount() > 0) {
                        $error = 'Email is already registered.';
                    } else {
                        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                        $stmt = $pdo->prepare("INSERT INTO users (name, email, phone, role, password) VALUES (?, ?, ?, ?, ?)");
                        if ($stmt->execute([$name, $email, $phone, $role, $hashedPassword])) {
                            $success = 'User created successfully.';
                        } else {
                            $error = 'Failed to create user.';
                        }
                    }
                }
            }
        }
    } elseif ($action === 'delete') {
        $id = $_POST['id'] ?? null;
        if ($id && $id != $_SESSION['user_id']) { // Check cannot delete self
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            if ($stmt->execute([$id])) {
                $success = 'User deleted successfully.';
            } else {
                $error = 'Failed to delete user.';
            }
        } else {
            $error = 'You cannot delete your own account.';
        }
    }
}

// Fetch users
$stmt = $pdo->query("SELECT id, name, email, phone, role, created_at FROM users ORDER BY created_at DESC");
$users = $stmt->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<div class="mb-6 flex justify-between items-center">
    <div>
        <h1 class="text-2xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-gray-900 to-gray-600">User
            Management</h1>
        <p class="text-gray-500 text-sm">Create and manage system users and their roles.</p>
    </div>
    <button onclick="document.getElementById('user-form-modal').classList.remove('hidden')"
        class="bg-orange-600 text-white px-4 py-2 rounded-lg font-bold hover:bg-orange-700 transition flex items-center gap-2 text-sm">
        <i class="fa-solid fa-user-plus"></i> Add User
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
                    <th class="p-4 font-bold">User</th>
                    <th class="p-4 font-bold">Role</th>
                    <th class="p-4 font-bold">Phone</th>
                    <th class="p-4 font-bold hidden md:table-cell">Joined</th>
                    <th class="p-4 font-bold text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php foreach ($users as $u): ?>
                    <tr class="hover:bg-gray-50 transition p-2">
                        <td class="p-4">
                            <p class="font-bold text-gray-800">
                                <?= htmlspecialchars($u->name) ?>
                            </p>
                            <p class="text-sm text-gray-500">
                                <?= htmlspecialchars($u->email) ?>
                            </p>
                        </td>
                        <td class="p-4">
                            <?php
                            $roleClass = match ($u->role) {
                                'superadmin' => 'bg-red-100 text-red-800',
                                'admin' => 'bg-purple-100 text-purple-800',
                                'provider' => 'bg-blue-100 text-blue-800',
                                default => 'bg-gray-100 text-gray-800'
                            };
                            ?>
                            <span
                                class="px-3 py-1 text-xs font-bold rounded-full border bg-white <?= str_replace('bg-', 'border-', explode(' ', $roleClass)[0]) ?> <?= explode(' ', $roleClass)[1] ?>">
                                <?= htmlspecialchars(strtoupper($u->role)) ?>
                            </span>
                        </td>
                        <td class="p-4 font-medium text-gray-700">
                            <?= htmlspecialchars($u->phone ?? 'N/A') ?>
                        </td>
                        <td class="p-4 text-gray-500 text-sm hidden md:table-cell">
                            <?= date('M d, Y', strtotime($u->created_at)) ?>
                        </td>
                        <td class="p-4 text-right space-x-2">
                            <button onclick="editUser(<?= htmlspecialchars(json_encode($u)) ?>)"
                                class="text-blue-600 hover:text-blue-800 w-8 h-8 rounded-full bg-blue-50 hover:bg-blue-100 transition inline-flex items-center justify-center">
                                <i class="fa-solid fa-user-pen"></i>
                            </button>
                            <?php if ($u->id != $_SESSION['user_id']): ?>
                                <form method="POST" class="inline-block"
                                    onsubmit="return confirm('Are you sure you want to delete this user?');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= $u->id ?>">
                                    <button type="submit"
                                        class="text-red-600 hover:text-red-800 w-8 h-8 rounded-full bg-red-50 hover:bg-red-100 transition inline-flex items-center justify-center">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- User Form Modal -->
<div id="user-form-modal"
    class="hidden fixed inset-0 bg-gray-900 bg-opacity-50 z-50 flex items-center justify-center px-4 backdrop-blur-sm pt-10 pb-10">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg overflow-y-auto max-h-screen transform transition-all">
        <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50 sticky top-0 z-10">
            <h3 class="text-lg font-bold text-gray-800" id="modal-title">Add User</h3>
            <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600 transition">
                <i class="fa-solid fa-times text-xl"></i>
            </button>
        </div>
        <form method="POST" action="users.php" class="p-6">
            <input type="hidden" name="action" id="form-action" value="create">
            <input type="hidden" name="id" id="form-id" value="">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Full Name <span
                            class="text-red-500">*</span></label>
                    <input type="text" name="name" id="form-name" required
                        class="w-full p-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 outline-none transition">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email <span
                            class="text-red-500">*</span></label>
                    <input type="email" name="email" id="form-email" required
                        class="w-full p-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 outline-none transition">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                    <input type="tel" name="phone" id="form-phone"
                        class="w-full p-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 outline-none transition">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Role <span
                            class="text-red-500">*</span></label>
                    <select name="role" id="form-role" required
                        class="w-full p-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 outline-none transition bg-white">
                        <option value="user">Customer</option>
                        <option value="provider">Service Provider</option>
                        <option value="admin">Admin</option>
                        <option value="superadmin">Super Admin</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                    <input type="password" name="password" id="form-password"
                        class="w-full p-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 outline-none transition"
                        placeholder="Required for new users">
                    <p class="text-xs text-gray-500 mt-1" id="password-help">Leave blank to keep current password when
                        editing.</p>
                </div>
            </div>

            <div class="mt-8 flex justify-end gap-3 pt-4 border-t border-gray-100">
                <button type="button" onclick="closeModal()"
                    class="px-4 py-2 text-gray-600 font-medium hover:bg-gray-100 rounded-lg transition">Cancel</button>
                <button type="submit"
                    class="bg-orange-600 text-white px-6 py-2 rounded-lg font-bold hover:bg-orange-700 transition shadow-md">Save
                    User</button>
            </div>
        </form>
    </div>
</div>

<script>
    function editUser(user) {
        document.getElementById('modal-title').innerText = 'Edit User';
        document.getElementById('form-action').value = 'update';
        document.getElementById('form-id').value = user.id;
        document.getElementById('form-name').value = user.name;
        document.getElementById('form-email').value = user.email;
        document.getElementById('form-phone').value = user.phone;
        document.getElementById('form-role').value = user.role;
        document.getElementById('form-password').required = false;
        document.getElementById('password-help').classList.remove('hidden');
        document.getElementById('user-form-modal').classList.remove('hidden');
    }

    function closeModal() {
        document.getElementById('user-form-modal').classList.add('hidden');
        document.getElementById('form-action').value = 'create';
        document.getElementById('form-id').value = '';
        document.getElementById('form-name').value = '';
        document.getElementById('form-email').value = '';
        document.getElementById('form-phone').value = '';
        document.getElementById('form-role').value = 'user';
        document.getElementById('form-password').required = true;
        document.getElementById('password-help').classList.add('hidden');
        document.getElementById('modal-title').innerText = 'Add User';
    }
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
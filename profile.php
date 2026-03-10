<?php
require_once __DIR__ . '/includes/db.php';

requireLogin();

$userId = $_SESSION['user_id'];
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');

    // Check if updating password
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';

    if (empty($name) || empty($phone)) {
        $error = "Name and phone are required.";
    } else {
        $updatePassword = false;

        if (!empty($newPassword)) {
            // Verify current password
            $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch();

            if (password_verify($currentPassword, $user->password)) {
                $updatePassword = true;
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            } else {
                $error = "Current password is incorrect.";
            }
        }

        if (empty($error)) {
            if ($updatePassword) {
                $stmt = $pdo->prepare("UPDATE users SET name = ?, phone = ?, address = ?, password = ? WHERE id = ?");
                $result = $stmt->execute([$name, $phone, $address, $hashedPassword, $userId]);
            } else {
                $stmt = $pdo->prepare("UPDATE users SET name = ?, phone = ?, address = ? WHERE id = ?");
                $result = $stmt->execute([$name, $phone, $address, $userId]);
            }

            if ($result) {
                $_SESSION['user_name'] = $name;
                $success = "Profile updated successfully!";
            } else {
                $error = "Failed to update profile.";
            }
        }
    }
}

// Fetch current user details
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

require_once __DIR__ . '/includes/header.php';
?>

<div class="bg-gray-50 min-h-screen py-10">
    <div class="max-w-4xl mx-auto px-4">
        <h1 class="text-3xl font-black text-gray-900 mb-8"><i class="fa-solid fa-user-circle text-orange-600"></i> My
            Profile</h1>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden md:flex">
            <!-- Sidebar / Info Panel -->
            <div
                class="bg-gray-100 border-r border-gray-200 md:w-1/3 p-8 flex flex-col items-center justify-center text-center">
                <div
                    class="w-24 h-24 bg-orange-100 text-orange-600 rounded-full flex items-center justify-center text-4xl font-bold mb-4 shadow-inner">
                    <?= strtoupper(substr($user->name, 0, 1)) ?>
                </div>
                <h2 class="text-xl font-bold text-gray-800">
                    <?= htmlspecialchars($user->name) ?>
                </h2>
                <p class="text-sm text-gray-500 mb-4">
                    <?= htmlspecialchars($user->role) ?>
                </p>
                <div class="w-full h-px bg-gray-300 mb-4"></div>
                <p class="text-sm text-gray-600 mb-2"><i class="fa-solid fa-envelope mr-2 text-gray-400"></i>
                    <?= htmlspecialchars($user->email) ?>
                </p>
                <p class="text-sm text-gray-600"><i class="fa-solid fa-calendar mr-2 text-gray-400"></i> Joined
                    <?= date('M Y', strtotime($user->created_at)) ?>
                </p>
            </div>

            <!-- Form -->
            <div class="p-8 md:w-2/3">
                <h3 class="text-xl font-bold mb-6 border-b pb-2">Edit Profile</h3>

                <?php if ($success): ?>
                    <div class="bg-green-50 text-green-700 p-3 rounded mb-4"><i class="fa-solid fa-check-circle mr-2"></i>
                        <?= htmlspecialchars($success) ?>
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="bg-red-50 text-red-700 p-3 rounded mb-4"><i class="fa-solid fa-exclamation-circle mr-2"></i>
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="profile.php" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                            <input type="text" name="name" value="<?= htmlspecialchars($user->name) ?>" required
                                class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                            <input type="tel" name="phone" value="<?= htmlspecialchars($user->phone ?? '') ?>" required
                                class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 outline-none">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email (Cannot be changed)</label>
                        <input type="email" value="<?= htmlspecialchars($user->email) ?>" disabled
                            class="w-full p-3 border border-gray-200 bg-gray-50 rounded-lg text-gray-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Default Address</label>
                        <textarea name="address" rows="3"
                            class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 outline-none"><?= htmlspecialchars($user->address ?? '') ?></textarea>
                    </div>

                    <h4 class="font-bold pt-4 border-b pb-2 mt-6">Change Password (Optional)</h4>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Current Password</label>
                        <input type="password" name="current_password"
                            class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                        <input type="password" name="new_password"
                            class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 outline-none">
                    </div>

                    <div class="pt-4">
                        <button type="submit"
                            class="bg-orange-600 text-white font-bold py-3 px-6 rounded-lg hover:bg-orange-700 transition shadow-md">
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
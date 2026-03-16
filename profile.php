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

                <!-- Email Verification Section -->
                <?php if (!$user->email_verified): ?>
                <div class="mt-8 p-6 bg-red-50 border border-red-200 rounded-xl">
                    <h4 class="text-xl font-bold text-red-700 mb-2"><i class="fa-solid fa-triangle-exclamation mr-2"></i> Unverified Email</h4>
                    <p class="text-gray-700 mb-4">Please verify your email address to access all features like submitting reviews.</p>
                    
                    <div id="otp-request-section">
                        <button id="send-otp-btn" class="bg-red-600 text-white px-4 py-2 rounded font-semibold hover:bg-red-700 transition">
                            Send Verification Code
                        </button>
                    </div>

                    <div id="otp-verify-section" class="hidden mt-4 space-y-3">
                        <p class="text-sm text-green-700 font-semibold" id="otp-success-msg"></p>
                        <div class="flex gap-2">
                            <input type="text" id="otp-input" placeholder="Enter 6-digit OTP" class="p-2 border border-gray-300 rounded outline-none focus:border-red-500 w-40" maxlength="6">
                            <button id="verify-otp-btn" class="bg-green-600 text-white px-4 py-2 rounded font-semibold hover:bg-green-700 transition">
                                Verify
                            </button>
                        </div>
                        <p class="text-sm text-red-600 hidden" id="otp-error-msg"></p>
                    </div>
                </div>
                <?php else: ?>
                <div class="mt-8 p-4 bg-green-50 border border-green-200 rounded-xl flex items-center gap-3">
                    <i class="fa-solid fa-circle-check text-green-600 text-2xl"></i>
                    <div>
                        <h4 class="font-bold text-green-800">Email Verified</h4>
                        <p class="text-sm text-green-700">Your account is fully verified.</p>
                    </div>
                </div>
                <?php endif; ?>

            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const sendBtn = document.getElementById('send-otp-btn');
    const verifyBtn = document.getElementById('verify-otp-btn');
    
    if (sendBtn) {
        sendBtn.addEventListener('click', async () => {
            sendBtn.disabled = true;
            sendBtn.innerText = 'Sending...';
            
            try {
                const res = await fetch('/api/EmailVerificationController.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({ action: 'send' })
                });
                const data = await res.json();
                
                if (data.success) {
                    document.getElementById('otp-request-section').classList.add('hidden');
                    document.getElementById('otp-verify-section').classList.remove('hidden');
                    document.getElementById('otp-success-msg').innerText = data.message;
                } else {
                    alert(data.error || 'Failed to send OTP');
                    sendBtn.disabled = false;
                    sendBtn.innerText = 'Send Verification Code';
                }
            } catch (e) {
                console.error(e);
                alert('Network error');
                sendBtn.disabled = false;
                sendBtn.innerText = 'Send Verification Code';
            }
        });
    }

    if (verifyBtn) {
        verifyBtn.addEventListener('click', async () => {
            const otpCode = document.getElementById('otp-input').value;
            const errorMsg = document.getElementById('otp-error-msg');
            
            if (!otpCode || otpCode.length !== 6) {
                errorMsg.innerText = "Please enter a valid 6-digit OTP.";
                errorMsg.classList.remove('hidden');
                return;
            }
            
            verifyBtn.disabled = true;
            verifyBtn.innerText = 'Verifying...';
            errorMsg.classList.add('hidden');
            
            try {
                const res = await fetch('/api/EmailVerificationController.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({ action: 'verify', otp: otpCode })
                });
                const data = await res.json();
                
                if (data.success) {
                    window.location.reload(); // Reload to show verified badge
                } else {
                    errorMsg.innerText = data.error || 'Verification failed';
                    errorMsg.classList.remove('hidden');
                    verifyBtn.disabled = false;
                    verifyBtn.innerText = 'Verify';
                }
            } catch (e) {
                console.error(e);
                errorMsg.innerText = 'Network error';
                errorMsg.classList.remove('hidden');
                verifyBtn.disabled = false;
                verifyBtn.innerText = 'Verify';
            }
        });
    }
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
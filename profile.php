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

<!-- Header Section with Gradient -->
<div class="relative bg-gradient-to-r from-[#2563eb] to-[#1e40af] h-64 overflow-hidden">
    <div class="max-w-7xl mx-auto px-4 h-full flex flex-col justify-end pb-12 relative z-10">
        <div class="flex justify-between items-center text-white">
            <h1 class="text-4xl font-bold">Profile</h1>
            <a href="/logout.php" class="bg-white/10 hover:bg-white/20 px-6 py-2 rounded-lg font-bold transition flex items-center gap-2 text-sm border border-white/20">
                <i class="fa-solid fa-arrow-right-from-bracket"></i> Logout
            </a>
        </div>
    </div>
    <!-- Decorative Circles -->
    <div class="absolute -right-20 -top-20 w-96 h-96 bg-white/5 rounded-full"></div>
    <div class="absolute -left-20 -bottom-20 w-64 h-64 bg-white/5 rounded-full"></div>
</div>

<div class="max-w-7xl mx-auto px-4 -mt-20 relative z-20 pb-20">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Sidebar Profile Card -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-3xl shadow-xl overflow-hidden border border-gray-100 p-8 text-center sticky top-24">
                <div class="mb-6 relative inline-block">
                    <div class="w-28 h-28 bg-[#dbeafe] text-[#2563eb] rounded-full flex items-center justify-center text-5xl font-black shadow-inner">
                        <?= strtoupper(substr($user->name, 0, 1)) ?>
                    </div>
                </div>
                <h2 class="text-2xl font-black text-gray-900 mb-1"><?= htmlspecialchars($user->name) ?></h2>
                <div class="inline-flex items-center gap-1.5 px-3 py-1 bg-blue-50 text-[#2563eb] text-[10px] font-bold uppercase rounded-full tracking-wider mb-6">
                    <i class="fa-solid fa-shield-halved"></i> <?= htmlspecialchars($user->role) ?> Account
                </div>
                
                <div class="space-y-4 pt-6 border-t border-gray-100 text-left">
                    <div class="flex justify-between items-center">
                        <span class="text-xs font-bold text-gray-400 uppercase">Verification Status</span>
                        <?php if ($user->email_verified): ?>
                            <span class="bg-green-100 text-green-600 px-3 py-1 rounded-full text-[10px] font-bold flex items-center gap-1">
                                <i class="fa-solid fa-circle-check"></i> Verified
                            </span>
                        <?php else: ?>
                            <span class="bg-red-100 text-red-600 px-3 py-1 rounded-full text-[10px] font-bold flex items-center gap-1">
                                <i class="fa-solid fa-circle-xmark"></i> Not Verified
                            </span>
                        <?php endif; ?>
                    </div>
                    <div class="flex flex-col gap-1">
                        <span class="text-xs font-bold text-gray-400 uppercase">Email Address</span>
                        <span class="text-sm font-semibold text-gray-700"><?= htmlspecialchars($user->email) ?></span>
                    </div>
                    <div class="flex flex-col gap-1">
                        <span class="text-xs font-bold text-gray-400 uppercase">Member Since</span>
                        <span class="text-sm font-semibold text-gray-700"><?= date('F d, Y', strtotime($user->created_at)) ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Form Area -->
        <div class="lg:col-span-2 space-y-8">
            <!-- Account Information -->
            <div class="bg-white rounded-3xl shadow-xl border border-gray-100 overflow-hidden">
                <div class="px-8 py-6 border-b border-gray-100 flex items-center gap-3">
                    <div class="w-10 h-10 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center">
                        <i class="fa-solid fa-user-gear text-lg"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900">Account Information</h3>
                </div>
                
                <form method="POST" action="profile.php" class="p-8">
                    <?php if ($success): ?>
                        <div class="bg-green-50 text-green-700 p-4 rounded-xl mb-6 flex items-center shadow-sm border border-green-200">
                            <i class="fa-solid fa-circle-check mr-3 text-xl"></i> <?= htmlspecialchars($success) ?>
                        </div>
                    <?php endif; ?>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label class="block text-xs font-bold text-gray-400 uppercase mb-2 tracking-widest">Email Status</label>
                            <div class="flex items-center gap-2 p-3 bg-gray-50 border border-gray-200 rounded-xl text-sm font-bold text-gray-700">
                                <?php if ($user->email_verified): ?>
                                    <i class="fa-solid fa-check-circle text-green-500"></i> Verified
                                <?php else: ?>
                                    <i class="fa-solid fa-circle-xmark text-red-500"></i> Unverified
                                <?php endif; ?>
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-400 uppercase mb-2 tracking-widest">Phone Status</label>
                            <div class="flex items-center gap-2 p-3 bg-gray-50 border border-gray-200 rounded-xl text-sm font-bold text-gray-700">
                                <i class="fa-solid fa-check-circle text-green-500"></i> Verified
                            </div>
                        </div>
                    </div>

                    <div class="space-y-6">
                        <div>
                            <label class="block text-xs font-bold text-gray-400 uppercase mb-2 tracking-widest">Full Name</label>
                            <input type="text" name="name" value="<?= htmlspecialchars($user->name) ?>" required
                                class="w-full p-4 border border-gray-200 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition text-sm font-bold">
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-xs font-bold text-gray-400 uppercase mb-2 tracking-widest">Email Address</label>
                                <input type="email" value="<?= htmlspecialchars($user->email) ?>" disabled
                                    class="w-full p-4 border border-gray-100 bg-gray-50 rounded-2xl text-gray-500 text-sm font-bold">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-400 uppercase mb-2 tracking-widest">Phone Number</label>
                                <input type="tel" name="phone" value="<?= htmlspecialchars($user->phone ?? '') ?>" required
                                    class="w-full p-4 border border-gray-200 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition text-sm font-bold">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-400 uppercase mb-2 tracking-widest">Residential Address</label>
                            <textarea name="address" rows="3" placeholder="Enter your full address..."
                                class="w-full p-4 border border-gray-200 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition text-sm font-bold"><?= htmlspecialchars($user->address ?? '') ?></textarea>
                        </div>
                    </div>

                    <div class="mt-8 pt-6 border-t border-gray-100">
                        <button type="submit"
                            class="bg-[#2563eb] text-white font-black py-4 px-10 rounded-2xl hover:bg-blue-700 transition shadow-lg text-sm tracking-widest uppercase">
                            Update Profile
                        </button>
                    </div>
                </form>
            </div>

            <!-- Email Verification Logic (Keeping OTP functioning) -->
            <?php if (!$user->email_verified): ?>
            <div class="bg-red-50 rounded-3xl p-8 border border-red-100">
                <div class="flex flex-col md:flex-row gap-6 items-center md:items-start">
                    <div class="w-16 h-16 bg-red-100 text-red-600 rounded-2xl flex items-center justify-center shrink-0">
                        <i class="fa-solid fa-triangle-exclamation text-2xl"></i>
                    </div>
                    <div>
                        <h4 class="text-xl font-bold text-red-900 mb-2">Verify Your Email</h4>
                        <p class="text-red-700/80 text-sm mb-6 max-w-md">Please verify your email address to unlock premium features including review submissions and order history.</p>
                        
                        <div id="otp-request-section">
                            <button id="send-otp-btn" class="bg-red-600 text-white px-8 py-3 rounded-xl font-bold hover:bg-red-700 transition shadow-md text-sm">
                                Send Verification Code
                            </button>
                        </div>

                        <div id="otp-verify-section" class="hidden space-y-4">
                            <p class="text-xs font-bold text-green-700 uppercase" id="otp-success-msg"></p>
                            <div class="flex gap-3">
                                <input type="text" id="otp-input" placeholder="000000" class="w-40 p-3 border border-red-200 rounded-xl outline-none focus:ring-2 focus:ring-red-500 text-center font-black tracking-[0.5em] text-lg">
                                <button id="verify-otp-btn" class="bg-green-600 text-white px-8 py-3 rounded-xl font-bold hover:bg-green-700 transition shadow-md text-sm">
                                    Verify
                                </button>
                            </div>
                            <p class="text-xs font-bold text-red-600 hidden" id="otp-error-msg"></p>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
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
            sendBtn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> Sending...';
            
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
            verifyBtn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> Verifying...';
            errorMsg.classList.add('hidden');
            
            try {
                const res = await fetch('/api/EmailVerificationController.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({ action: 'verify', otp: otpCode })
                });
                const data = await res.json();
                
                if (data.success) {
                    window.location.reload(); 
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
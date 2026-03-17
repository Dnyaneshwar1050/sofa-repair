<?php
require_once __DIR__ . '/includes/db.php';

if (isLoggedIn()) {
    header("Location: /index.php");
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($name) || empty($email) || empty($phone) || empty($password)) {
        $error = 'All fields are required.';
    } else {
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND tenant_id = ?");
        $stmt->execute([$email, CURRENT_TENANT_ID]);
        if ($stmt->rowCount() > 0) {
            $error = 'Email is already registered.';
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (tenant_id, name, email, phone, password, role) VALUES (?, ?, ?, ?, ?, 'user')");

            if ($stmt->execute([CURRENT_TENANT_ID, $name, $email, $phone, $hashedPassword])) {
                // Auto login after registration
                $userId = $pdo->lastInsertId();
                $_SESSION['user_id'] = $userId;
                $_SESSION['user_role'] = 'user';
                $_SESSION['user_name'] = $name;

                header("Location: /index.php");
                exit;
            } else {
                $error = 'Failed to register. Please try again.';
            }
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="min-h-[calc(100vh-200px)] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 bg-gray-50">
    <div class="max-w-md w-full space-y-8 bg-white p-10 rounded-3xl shadow-premium border border-gray-100">
        <div class="flex flex-col items-center justify-center text-center">
            <img src="/frontend/public/logo-dark.png" alt="Silva Furniture Logo" class="h-16 w-auto mb-6 object-contain" />
            <h2 class="text-3xl font-heading font-black text-gray-900 mb-2">Create Account</h2>
            <p class="text-gray-500 font-medium">Join us for premium services</p>
        </div>

        <form method="POST" action="register.php" class="mt-8 space-y-6">
            <div class="space-y-4">
                <div>
                    <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">Full Name</label>
                    <input id="name" type="text" name="name" placeholder="John Doe" required
                        class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-colors" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" />
                </div>
                <div>
                    <label for="phone" class="block text-sm font-semibold text-gray-700 mb-2">Phone Number</label>
                    <input id="phone" type="tel" name="phone" placeholder="+91 98765 43210" required
                        class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-colors" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>" />
                </div>
                <div>
                    <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">Email Address</label>
                    <input id="email" type="email" name="email" placeholder="you@example.com" required
                        class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-colors" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" />
                </div>
                <div>
                    <label for="password" class="block text-sm font-semibold text-gray-700 mb-2">Password</label>
                    <input id="password" type="password" name="password" placeholder="••••••••" required
                        class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-colors" />
                </div>
            </div>

            <?php if ($error): ?>
                <div class="p-3 bg-red-50 text-red-600 rounded-xl text-sm font-medium border border-red-100 flex items-center gap-2">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-xl shadow-md text-base font-bold text-white bg-brand-600 hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-500 transition-all active:scale-[0.98]">
                Create Account
            </button>
        </form>

        <div class="mt-6">
            <p class="text-center text-sm text-gray-600 font-medium">
                Already have an account?
                <a href="/login.php" class="text-brand-600 hover:text-brand-700 font-bold hover:underline">Log in here</a>
            </p>
            <div class="mt-8 pt-6 border-t border-gray-100">
                <p class="text-center text-xs text-gray-400 leading-relaxed">
                    By continuing you agree to our<br/>
                    <a href="/policy.php" class="text-gray-500 hover:text-brand-600 transition-colors">Refund Policy</a> &middot;
                    <a href="/privacy.php" class="text-gray-500 hover:text-brand-600 transition-colors">Privacy</a> &middot;
                    <a href="/terms.php" class="text-gray-500 hover:text-brand-600 transition-colors">Terms</a>
                </p>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
<?php
require_once __DIR__ . '/includes/db.php';

if (isLoggedIn()) {
    header("Location: /index.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Email and password are required.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND tenant_id = ?");
        $stmt->execute([$email, CURRENT_TENANT_ID]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user->password)) {
            $_SESSION['user_id'] = $user->id;
            $_SESSION['user_role'] = $user->role;
            $_SESSION['user_name'] = $user->name;

            // Redirect based on role or to home
            if (in_array($user->role, ['admin', 'superadmin'])) {
                header("Location: /admin/index.php");
            } else {
                header("Location: /index.php");
            }
            exit;
        } else {
            $error = 'Invalid email or password.';
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="min-h-[calc(100vh-200px)] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 bg-gray-50">
    <div class="max-w-md w-full space-y-8 bg-white p-10 rounded-3xl shadow-premium border border-gray-100">
        <div class="flex flex-col items-center justify-center text-center">
            <img src="/frontend/public/logo-dark.png" alt="Silva Furniture Logo" class="h-16 w-auto mb-6 object-contain" />
            <h2 class="text-3xl font-heading font-black text-gray-900 mb-2">Welcome Back</h2>
            <p class="text-gray-500 font-medium">Please sign in to your account</p>
        </div>

        <form method="POST" action="login.php" class="mt-8 space-y-6">
            <div class="space-y-4">
                <div>
                    <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">Email Address</label>
                    <input id="email" type="email" name="email" placeholder="you@example.com" required
                        class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-colors" />
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
                Sign In
            </button>
        </form>

        <div class="mt-6">
            <p class="text-center text-sm text-gray-600 font-medium">
                Don't have an account?
                <a href="/register.php" class="text-brand-600 hover:text-brand-700 font-bold hover:underline">Register here</a>
            </p>
            <div class="mt-8 pt-6 border-t border-gray-100">
                <p class="text-center text-xs text-gray-400 leading-relaxed">
                    By continuing you agree to our<br/>
                    <a href="/refund-policy.php" class="text-gray-500 hover:text-brand-600 transition-colors">Refund Policy</a> &middot;
                    <a href="/privacy-policy.php" class="text-gray-500 hover:text-brand-600 transition-colors">Privacy</a> &middot;
                    <a href="/terms.php" class="text-gray-500 hover:text-brand-600 transition-colors">Terms</a>
                </p>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
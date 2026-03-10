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
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
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

<div class="max-w-md mx-auto mt-10 p-6 bg-white shadow-xl rounded-lg">
    <div class="flex justify-center mb-4 py-5">
        <img src="/frontend/public/logo-dark.png" alt="Khushi Home Sofa Repairing Logo" class="h-30 w-auto scale-150" />
    </div>
    <h1 class="text-3xl font-bold text-center mb-6">Customer Login</h1>

    <form method="POST" action="login.php" class="space-y-4">
        <input type="email" name="email" placeholder="Email Address" required
            class="w-full p-3 border border-gray-300 rounded" />
        <input type="password" name="password" placeholder="Password" required
            class="w-full p-3 border border-gray-300 rounded" />

        <?php if ($error): ?>
            <p class="text-red-500 text-sm">
                <?= htmlspecialchars($error) ?>
            </p>
        <?php endif; ?>

        <button type="submit" class="w-full bg-blue-600 text-white p-3 rounded font-semibold hover:bg-blue-700">
            Login
        </button>
    </form>

    <p class="text-center mt-4">
        Don't have an account?
        <a href="/register.php" class="text-blue-600 font-medium hover:underline">Register here</a>
    </p>
    <p class="text-center text-xs text-gray-600 mt-3">
        By continuing you agree to our
        <a href="/policy.php" class="text-blue-600 underline">Refund & Cancellation Policy</a>,
        <a href="/privacy.php" class="text-blue-600 underline">Privacy Policy</a> and
        <a href="/terms.php" class="text-blue-600 underline">Terms & Conditions</a>.
    </p>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
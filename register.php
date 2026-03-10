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
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->rowCount() > 0) {
            $error = 'Email is already registered.';
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (name, email, phone, password, role) VALUES (?, ?, ?, ?, 'user')");

            if ($stmt->execute([$name, $email, $phone, $hashedPassword])) {
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

<div class="max-w-md mx-auto mt-10 p-6 bg-white shadow-xl rounded-lg mb-10">
    <div class="flex justify-center mb-4 py-5">
        <img src="/frontend/public/logo-dark.png" alt="Khushi Home Sofa Repairing Logo" class="h-30 w-auto scale-150" />
    </div>
    <h1 class="text-3xl font-bold text-center mb-6">Customer Registration</h1>

    <form method="POST" action="register.php" class="space-y-4">
        <input type="text" name="name" placeholder="Full Name" required
            class="w-full p-3 border border-gray-300 rounded" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" />
        <input type="tel" name="phone" placeholder="Phone Number" required
            class="w-full p-3 border border-gray-300 rounded" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>" />
        <input type="email" name="email" placeholder="Email Address" required
            class="w-full p-3 border border-gray-300 rounded" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" />
        <input type="password" name="password" placeholder="Password" required
            class="w-full p-3 border border-gray-300 rounded" />

        <?php if ($error): ?>
            <p class="text-red-500 text-sm">
                <?= htmlspecialchars($error) ?>
            </p>
        <?php endif; ?>

        <button type="submit" class="w-full bg-blue-600 text-white p-3 rounded font-semibold hover:bg-blue-700">
            Register
        </button>
    </form>

    <p class="text-center mt-4">
        Already have an account?
        <a href="/login.php" class="text-blue-600 font-medium hover:underline">Login here</a>
    </p>
    <p class="text-center text-xs text-gray-600 mt-3">
        By continuing you agree to our
        <a href="/policy.php" class="text-blue-600 underline">Refund & Cancellation Policy</a>,
        <a href="/privacy.php" class="text-blue-600 underline">Privacy Policy</a> and
        <a href="/terms.php" class="text-blue-600 underline">Terms & Conditions</a>.
    </p>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
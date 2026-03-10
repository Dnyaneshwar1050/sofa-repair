<?php
require_once __DIR__ . '/../includes/db.php';

if (isLoggedIn() && in_array($_SESSION['user_role'] ?? '', ['admin', 'superadmin'])) {
    header("Location: /admin/index.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Email and password are required.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND role IN ('admin', 'superadmin')");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user->password)) {
            $_SESSION['user_id'] = $user->id;
            $_SESSION['user_role'] = $user->role;
            $_SESSION['user_name'] = $user->name;
            header("Location: /admin/index.php");
            exit;
        } else {
            $error = 'Invalid credentials or unauthorized access.';
        }
    }
}
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Admin Login - Khushi Home Sofa Repair</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="max-w-md w-full p-8 bg-white shadow-xl rounded-2xl">
        <div class="flex justify-center mb-6">
            <img src="/frontend/public/logo-dark.png" alt="Logo" class="h-20" />
        </div>
        <h1 class="text-2xl font-black text-center mb-8 text-gray-800">Admin Portal</h1>

        <?php if ($error): ?>
            <div class="bg-red-50 text-red-700 p-3 rounded-lg mb-4 text-center text-sm font-medium">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="login.php" class="space-y-5">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Admin Email</label>
                <input type="email" name="email" required
                    class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 outline-none"
                    placeholder="admin@example.com" />
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                <input type="password" name="password" required
                    class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 outline-none"
                    placeholder="••••••••" />
            </div>
            <button type="submit"
                class="w-full bg-gray-900 text-white p-3 rounded-lg font-bold hover:bg-gray-800 transition active:scale-95">
                Secure Login
            </button>
        </form>

        <div class="mt-6 text-center">
            <a href="/" class="text-sm text-gray-500 hover:text-orange-600 font-medium">← Back to Main Site</a>
        </div>
    </div>
</body>

</html>
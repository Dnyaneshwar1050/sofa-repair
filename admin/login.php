<?php
declare(strict_types=1);
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../includes/helpers.php';

if (is_admin_logged_in()) {
    redirect('/admin/dashboard.php');
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $value = trim((string)($_POST['value'] ?? ''));
    $password = (string)($_POST['password'] ?? '');

    if ($value === '' || $password === '') {
        $error = 'Phone/email and password are required.';
    } else {
        $stmt = db()->prepare('SELECT id, name, email, phone, role, password_hash FROM users WHERE (phone = :v OR email = :v) AND role = "admin" LIMIT 1');
        $stmt->execute([':v' => $value]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, (string)$user['password_hash'])) {
            $_SESSION['admin_user_id'] = (int)$user['id'];
            $_SESSION['admin_username'] = (string)($user['name'] ?: ($user['email'] ?: $user['phone']));
            redirect('/admin/dashboard.php');
        } else {
            $error = 'Invalid credentials.';
        }
    }
}

$pageTitle = 'Admin Login';
require_once __DIR__ . '/../includes/header.php';
?>

<section class="hero">
  <h1>Admin login</h1>
  <p class="muted">Use the admin account you created via <code>/admin/create_admin.php</code>.</p>
</section>

<div class="grid">
  <div class="card span-6">
    <h2>Sign in</h2>
    <?php if ($error): ?>
      <div class="notice err"><?php echo h($error); ?></div>
    <?php endif; ?>
    <form method="post">
      <input type="hidden" name="csrf_token" value="<?php echo h(csrf_token()); ?>">
      <label>Phone or email</label>
      <input name="value" required>
      <label>Password</label>
      <input type="password" name="password" required>
      <div style="margin-top:12px;display:flex;gap:10px;flex-wrap:wrap">
        <button class="btn primary" type="submit">Login</button>
        <a class="btn" href="/index.php">Back</a>
      </div>
    </form>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>


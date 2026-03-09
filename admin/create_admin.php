<?php
declare(strict_types=1);
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../includes/helpers.php';

// One-time helper to create an admin user for the React app (JWT login via /api/auth/login).
// After creating the user successfully, delete this file for safety.

$error = '';
$ok = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $name = trim((string)($_POST['name'] ?? ''));
    $phone = trim((string)($_POST['phone'] ?? ''));
    $email = trim((string)($_POST['email'] ?? ''));
    $password = (string)($_POST['password'] ?? '');

    if ($phone === '' || $password === '') {
        $error = 'Phone and password are required.';
    } else {
        try {
            $stmt = db()->prepare('INSERT INTO users (name, email, phone, password_hash, role, is_super_admin) VALUES (?, ?, ?, ?, ?, ?)');
            $stmt->execute([
              ($name !== '' ? $name : 'Admin'),
              ($email !== '' ? $email : null),
              $phone,
              password_hash($password, PASSWORD_DEFAULT),
              'admin',
              1
            ]);
            $ok = 'Admin created. You can now login.';
        } catch (Throwable $e) {
            $error = 'Could not create admin (phone/email may already exist).';
        }
    }
}

$pageTitle = 'Create Admin';
require_once __DIR__ . '/../includes/header.php';
?>

<section class="hero">
  <h1>Create admin user</h1>
  <p class="muted">After creating the admin, delete this file: <code>/admin/create_admin.php</code>.</p>
</section>

<div class="grid">
  <div class="card span-6">
    <?php if ($error): ?><div class="notice err"><?php echo h($error); ?></div><?php endif; ?>
    <?php if ($ok): ?><div class="notice ok"><?php echo h($ok); ?></div><?php endif; ?>
    <form method="post">
      <input type="hidden" name="csrf_token" value="<?php echo h(csrf_token()); ?>">
      <label>Name (optional)</label>
      <input name="name" value="<?php echo h($_POST['name'] ?? 'Admin'); ?>">
      <div class="row two">
        <div>
          <label>Phone (login)</label>
          <input name="phone" required value="<?php echo h($_POST['phone'] ?? ''); ?>">
        </div>
        <div>
          <label>Email (optional)</label>
          <input name="email" type="email" value="<?php echo h($_POST['email'] ?? ''); ?>">
        </div>
      </div>
      <label>Password</label>
      <input type="password" name="password" required>
      <div style="margin-top:12px;display:flex;gap:10px;flex-wrap:wrap">
        <button class="btn primary" type="submit">Create</button>
        <a class="btn" href="/admin/login.php">Go to login</a>
      </div>
    </form>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>


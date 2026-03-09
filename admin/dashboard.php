<?php
declare(strict_types=1);
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../includes/helpers.php';

require_admin();

$stmt = db()->prepare('
  SELECT s.id, s.name, s.short_description, s.base_price, s.is_disabled, s.created_at, c.name AS category_name
  FROM services s
  JOIN categories c ON c.id = s.category_id
  ORDER BY s.id DESC
');
$stmt->execute();
$services = $stmt->fetchAll();

$pageTitle = 'Admin Dashboard';
require_once __DIR__ . '/../includes/header.php';
?>

<section class="hero">
  <h1>Dashboard</h1>
  <p class="muted">Welcome, <?php echo h($_SESSION['admin_username'] ?? 'admin'); ?>.</p>
  <div style="margin-top:12px;display:flex;gap:10px;flex-wrap:wrap">
    <a class="btn primary" href="/admin/add_service.php">Add service</a>
    <a class="btn" href="/admin/logout.php">Logout</a>
  </div>
</section>

<div class="card">
  <h2>Services</h2>
  <?php if (!$services): ?>
    <div class="notice">No services yet.</div>
  <?php else: ?>
    <table class="table">
      <thead>
        <tr>
          <th>Name</th>
          <th>Price</th>
          <th>Status</th>
          <th style="width:140px">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($services as $s): ?>
          <tr>
            <td>
              <strong><?php echo h($s['name']); ?></strong><br>
              <span class="pill"><?php echo h($s['category_name']); ?></span>
              <div class="muted" style="margin-top:6px"><?php echo h($s['short_description']); ?></div>
            </td>
            <td><?php echo $s['base_price'] ? ('₹' . (int)$s['base_price']) : '-'; ?></td>
            <td>
              <?php if ((int)$s['is_disabled'] === 1): ?>
                <span class="pill">Disabled</span>
              <?php else: ?>
                <span class="pill" style="color:var(--accent)">Enabled</span>
              <?php endif; ?>
            </td>
            <td>
              <form method="post" action="/admin/delete_service.php" onsubmit="return confirm('Delete this service?')">
                <input type="hidden" name="csrf_token" value="<?php echo h(csrf_token()); ?>">
                <input type="hidden" name="id" value="<?php echo (int)$s['id']; ?>">
                <button class="btn danger" type="submit">Delete</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>


<?php
declare(strict_types=1);
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../includes/helpers.php';

require_admin();

$error = '';
$ok = '';

$catStmt = db()->prepare('SELECT id, name FROM categories ORDER BY name');
$catStmt->execute();
$categories = $catStmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $name = trim((string)($_POST['name'] ?? ''));
    $short = trim((string)($_POST['short_description'] ?? ''));
    $price = trim((string)($_POST['base_price'] ?? ''));
    $categoryId = (int)($_POST['category_id'] ?? 0);

    if ($name === '' || $short === '' || $categoryId <= 0) {
        $error = 'Name, short description, and category are required.';
    } else {
        try {
            $stmt = db()->prepare('
              INSERT INTO services (name, category_id, short_description, base_price, images_json, options_json, is_disabled)
              VALUES (:name, :category_id, :short, :price, :images_json, :options_json, 0)
            ');
            $stmt->execute([
                ':name' => $name,
                ':category_id' => $categoryId,
                ':short' => $short,
                ':price' => ($price !== '' ? (int)$price : 4500),
                ':images_json' => json_encode([], JSON_UNESCAPED_UNICODE),
                ':options_json' => json_encode([], JSON_UNESCAPED_UNICODE),
            ]);
            $ok = 'Service added.';
        } catch (Throwable $e) {
            $error = 'Could not add service.';
        }
    }
}

$pageTitle = 'Add Service';
require_once __DIR__ . '/../includes/header.php';
?>

<section class="hero">
  <h1>Add service</h1>
  <p class="muted">This writes to the MySQL <code>services</code> table.</p>
</section>

<div class="grid">
  <div class="card span-7">
    <?php if ($error): ?><div class="notice err"><?php echo h($error); ?></div><?php endif; ?>
    <?php if ($ok): ?><div class="notice ok"><?php echo h($ok); ?></div><?php endif; ?>
    <form method="post">
      <input type="hidden" name="csrf_token" value="<?php echo h(csrf_token()); ?>">
      <label>Name</label>
      <input name="name" required value="<?php echo h($_POST['name'] ?? ''); ?>">

      <label>Category</label>
      <select name="category_id" required>
        <option value="">Select</option>
        <?php foreach ($categories as $c): ?>
          <option value="<?php echo (int)$c['id']; ?>" <?php echo ((int)($_POST['category_id'] ?? 0) === (int)$c['id']) ? 'selected' : ''; ?>>
            <?php echo h($c['name']); ?>
          </option>
        <?php endforeach; ?>
      </select>

      <label>Short description</label>
      <textarea name="short_description" rows="4" required><?php echo h($_POST['short_description'] ?? ''); ?></textarea>

      <div class="row two">
        <div>
          <label>Base price (₹)</label>
          <input name="base_price" type="number" min="0" value="<?php echo h($_POST['base_price'] ?? '4500'); ?>">
        </div>
        <div>
          <div class="notice">
            For best results, upload images via Cloudinary from the React admin and store URLs in the service images array.
          </div>
        </div>
      </div>

      <div style="margin-top:12px;display:flex;gap:10px;flex-wrap:wrap">
        <button class="btn primary" type="submit">Save</button>
        <a class="btn" href="/admin/dashboard.php">Back</a>
      </div>
    </form>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>


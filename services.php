<?php
declare(strict_types=1);
$pageTitle = 'Services | Khushi Home Sofa Repair';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/includes/header.php';

$stmt = db()->prepare('SELECT id, name, short_description, base_price, image_url FROM services WHERE is_disabled = 0 ORDER BY id DESC');
$stmt->execute();
$services = $stmt->fetchAll();
?>

<section class="hero">
  <h1>Services</h1>
  <p class="muted">Browse available services. You can also book directly from this page.</p>
</section>

<section class="grid">
  <div class="card span-7">
    <h2>Available services</h2>
    <?php if (!$services): ?>
      <div class="notice">No services found yet. Add some from the admin panel.</div>
    <?php else: ?>
      <table class="table">
        <thead>
          <tr>
            <th>Service</th>
            <th>Starting price</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($services as $s): ?>
            <tr>
              <td>
                <strong><?php echo h($s['name']); ?></strong><br>
                <span class="muted"><?php echo h($s['short_description']); ?></span>
              </td>
              <td><?php echo $s['base_price'] ? ('₹' . (int)$s['base_price']) : '-'; ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>

  <div class="card span-5">
    <h2>Book a visit</h2>
    <form data-endpoint="/api/add_booking.php" method="post">
      <input type="hidden" name="csrf_token" value="<?php echo h(csrf_token()); ?>">
      <label>Service</label>
      <select name="service_id">
        <option value="">Select a service</option>
        <?php foreach ($services as $s): ?>
          <option value="<?php echo (int)$s['id']; ?>"><?php echo h($s['name']); ?></option>
        <?php endforeach; ?>
      </select>

      <label>If not listed, type service name (optional)</label>
      <input name="service_name_custom" placeholder="e.g. Cushion replacement">

      <div class="row two">
        <div>
          <label>Your name</label>
          <input name="customer_name" required>
        </div>
        <div>
          <label>Phone</label>
          <input name="phone" required>
        </div>
      </div>

      <div class="row two">
        <div>
          <label>House/Flat no.</label>
          <input name="address_house_no" required>
        </div>
        <div>
          <label>Area</label>
          <input name="address_area" required>
        </div>
      </div>

      <div class="row two">
        <div>
          <label>City</label>
          <input name="address_city" required value="Pune">
        </div>
        <div>
          <label>Pincode</label>
          <input name="address_pincode" required>
        </div>
      </div>

      <label>Notes (optional)</label>
      <textarea name="notes" rows="3"></textarea>
      <div data-notice class="notice" style="margin:12px 0"></div>
      <button class="btn primary" type="submit">Submit booking</button>
    </form>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>


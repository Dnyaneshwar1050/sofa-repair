<?php
declare(strict_types=1);
$pageTitle = 'Contact | Khushi Home Sofa Repair';
require_once __DIR__ . '/includes/header.php';
?>

<section class="hero">
  <h1>Contact</h1>
  <p class="muted">Send us a message and we’ll get back to you.</p>
</section>

<section class="grid">
  <div class="card span-6">
    <h2>Message</h2>
    <form data-endpoint="/api/contact_submit.php" method="post">
      <input type="hidden" name="csrf_token" value="<?php echo h(csrf_token()); ?>">
      <div class="row two">
        <div>
          <label>Your name</label>
          <input name="name" required>
        </div>
        <div>
          <label>Phone (optional)</label>
          <input name="phone">
        </div>
      </div>
      <label>Email (optional)</label>
      <input type="email" name="email">
      <label>Message</label>
      <textarea name="message" rows="5" required></textarea>
      <div data-notice class="notice" style="margin:12px 0"></div>
      <button class="btn primary" type="submit">Send</button>
    </form>
  </div>

  <div class="card span-6">
    <h2>Business details</h2>
    <p class="muted">Update this section with your phone, WhatsApp, address, and service areas.</p>
    <div class="notice">
      If you want, we can also add a simple Google Map embed and WhatsApp “click to chat”.
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>


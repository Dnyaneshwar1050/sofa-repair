<?php
declare(strict_types=1);
$pageTitle = 'Home | Khushi Home Sofa Repair';
require_once __DIR__ . '/includes/header.php';
?>

<section class="hero">
  <h1>Sofa Repair & Upholstery</h1>
  <p>Beginner-friendly PHP + MySQL version for shared hosting (InfinityFree). Browse services and book a visit in minutes.</p>
  <div style="margin-top:14px;display:flex;gap:10px;flex-wrap:wrap">
    <a class="btn primary" href="/services.php">View Services</a>
    <a class="btn" href="/contact.php">Contact</a>
  </div>
</section>

<section class="grid">
  <div class="card span-7">
    <h2>Quick booking</h2>
    <p class="muted">Submit your details and we’ll contact you.</p>
    <form data-endpoint="/api/add_booking.php" method="post">
      <input type="hidden" name="csrf_token" value="<?php echo h(csrf_token()); ?>">
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
          <label>Email (optional)</label>
          <input name="email" type="email">
        </div>
        <div>
          <label>Budget (optional)</label>
          <input name="budget" type="number" min="0">
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
      <label>Service (optional)</label>
      <select name="service_id">
        <option value="">Select a service</option>
      </select>
      <label>Notes (optional)</label>
      <textarea name="notes" rows="3" placeholder="Any details about the repair..."></textarea>
      <div data-notice class="notice" style="margin:12px 0"></div>
      <button class="btn primary" type="submit">Book now</button>
    </form>
  </div>

  <div class="card span-5">
    <h2>Admin</h2>
    <p class="muted">Manage services from a simple admin panel.</p>
    <div class="notice">
      After uploading, import <code>schema.sql</code>, create an admin via <code>/admin/create_admin.php</code>,
      then log in at <a href="/admin/login.php"><u>/admin/login.php</u></a>.
    </div>
  </div>
</section>

<script>
  // Populate service dropdown from API
  (async function(){
    const select = document.querySelector('select[name="service_id"]');
    if(!select) return;
    try{
      const res = await fetch('/api/get_services.php');
      const services = await res.json();
      if(!Array.isArray(services)) return;
      services.forEach(s => {
        const opt = document.createElement('option');
        opt.value = s.id;
        opt.textContent = s.name + (s.base_price ? ` (from ₹${s.base_price})` : '');
        select.appendChild(opt);
      });
    }catch(e){}
  })();
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>


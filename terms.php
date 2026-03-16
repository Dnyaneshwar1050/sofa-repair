<?php
$pageTitle = 'Terms & Conditions - Silva Furniture';
require_once __DIR__ . '/includes/header.php';
?>

<div class="bg-gray-50 min-h-screen text-gray-900 pb-20">
    <div class="bg-gradient-to-r from-brand-900 to-brand-700 text-white py-16 relative overflow-hidden">
        <div class="absolute right-0 top-0 opacity-10">
            <svg width="404" height="384" fill="none" viewBox="0 0 404 384"><defs><pattern id="d3eb07ae-5182-43e6-857d-35c643af9034" x="0" y="0" width="20" height="20" patternUnits="userSpaceOnUse"><rect x="0" y="0" width="4" height="4" fill="currentColor"></rect></pattern></defs><rect width="404" height="384" fill="url(#d3eb07ae-5182-43e6-857d-35c643af9034)"></rect></svg>
        </div>
        <div class="max-w-4xl mx-auto px-4 text-center relative z-10">
            <h1 class="text-4xl md:text-6xl font-heading font-extrabold mb-4">Terms & Conditions</h1>
            <p class="text-lg text-brand-100 font-light">Last Updated: <?php echo date('F j, Y'); ?></p>
        </div>
    </div>
    
    <div class="max-w-4xl mx-auto px-4 mt-10">
        <div class="bg-white rounded-2xl shadow-premium p-8 md:p-14">
            <div class="prose prose-lg prose-orange max-w-none text-gray-600">
                <h2 class="font-heading text-2xl font-bold text-gray-900 mb-4 mt-8">1. Acceptance of Terms</h2>
                <p class="mb-6 leading-relaxed">By accessing and using the Silva Furniture website and services, you agree to be bound by these Terms and Conditions. If you do not agree, please do not use our services.</p>
                
                <h2 class="font-heading text-2xl font-bold text-gray-900 mb-4 mt-8">2. Service Provision</h2>
                <p class="mb-6 leading-relaxed">Silva Furniture connects you with professional service providers for furniture repair, cleaning, and restoration. We do not guarantee the exact outcome of every service, but we stand by our quality commitment to strive for 100% customer satisfaction on every job.</p>
                
                <h2 class="font-heading text-2xl font-bold text-gray-900 mb-4 mt-8">3. User Responsibilities</h2>
                <p class="mb-6 leading-relaxed">You are responsible for providing accurate contact and location information when booking services, and for ensuring a safe and accessible environment for our service providers to operate efficiently.</p>
                
                <h2 class="font-heading text-2xl font-bold text-gray-900 mb-4 mt-8">4. Cancellations and Modifications</h2>
                <p class="mb-6 leading-relaxed">You may cancel or modify your service request up to 24 hours before the scheduled time without penalty. Late cancellations or changes made on the day of service may incur a cancellation fee.</p>

                <div class="bg-brand-50 p-6 rounded-xl border border-brand-100 mt-10">
                    <h3 class="font-heading font-bold text-brand-900 text-lg mb-2">Need to cancel or reschedule?</h3>
                    <p class="text-brand-700 text-sm mb-4">Manage your bookings efficiently via your account dashboard.</p>
                    <a href="/my-bookings.php" class="text-brand-600 font-bold hover:text-brand-800 transition-colors">Go to My Requests &rarr;</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

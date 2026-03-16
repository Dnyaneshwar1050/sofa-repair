<?php
$pageTitle = 'Privacy Policy - Silva Furniture';
require_once __DIR__ . '/includes/header.php';
?>

<div class="bg-gray-50 min-h-screen text-gray-900 pb-20">
    <div class="bg-gradient-to-r from-brand-900 to-brand-700 text-white py-16 relative overflow-hidden">
        <div class="absolute right-0 top-0 opacity-10">
            <svg width="404" height="384" fill="none" viewBox="0 0 404 384"><defs><pattern id="d3eb07ae-5182-43e6-857d-35c643af9034" x="0" y="0" width="20" height="20" patternUnits="userSpaceOnUse"><rect x="0" y="0" width="4" height="4" fill="currentColor"></rect></pattern></defs><rect width="404" height="384" fill="url(#d3eb07ae-5182-43e6-857d-35c643af9034)"></rect></svg>
        </div>
        <div class="max-w-4xl mx-auto px-4 text-center relative z-10">
            <h1 class="text-4xl md:text-6xl font-heading font-extrabold mb-4">Privacy Policy</h1>
            <p class="text-lg text-brand-100 font-light">Last Updated: <?php echo date('F j, Y'); ?></p>
        </div>
    </div>
    
    <div class="max-w-4xl mx-auto px-4 mt-10">
        <div class="bg-white rounded-2xl shadow-premium p-8 md:p-14">
            <div class="prose prose-lg prose-orange max-w-none text-gray-600">
                <h2 class="font-heading text-2xl font-bold text-gray-900 mb-4 mt-8">1. Information We Collect</h2>
                <p class="mb-6 leading-relaxed">At Silva Furniture, we collect information you provide directly to us when you request a service, create an account, or contact customer support. This may include your name, phone number, email address, physical address, and service preferences.</p>
                
                <h2 class="font-heading text-2xl font-bold text-gray-900 mb-4 mt-8">2. How We Use Your Information</h2>
                <p class="mb-6 leading-relaxed">We use the information we collect to provide, maintain, and improve our services, communicate with you, schedule appointments, send updates about your repair jobs, and protect Silva Furniture and our users.</p>
                
                <h2 class="font-heading text-2xl font-bold text-gray-900 mb-4 mt-8">3. Information Sharing</h2>
                <p class="mb-6 leading-relaxed">We do not sell or share your personal information with third parties except as described in this privacy policy, such as providing your address to verified service providers to fulfill your repair requests, or as required by law.</p>
                
                <h2 class="font-heading text-2xl font-bold text-gray-900 mb-4 mt-8">4. Data Security</h2>
                <p class="mb-6 leading-relaxed">We take reasonable measures to help protect information about you from loss, theft, misuse, unauthorized access, disclosure, alteration, and destruction.</p>
                
                <div class="bg-brand-50 p-6 rounded-xl border border-brand-100 mt-10">
                    <h3 class="font-heading font-bold text-brand-900 text-lg mb-2">Have questions about your privacy?</h3>
                    <p class="text-brand-700 text-sm mb-4">Our support team is here to help you understand how we protect your data.</p>
                    <a href="/contact.php" class="text-brand-600 font-bold hover:text-brand-800 transition-colors">Contact Support &rarr;</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

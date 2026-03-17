<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/header.php';
?>

<div class="bg-gray-50 min-h-screen py-20">
    <div class="max-w-4xl mx-auto px-4">
        <!-- Breadcrumbs -->
        <nav class="flex mb-8 text-sm font-bold uppercase tracking-widest" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li><a href="/" class="text-gray-400 hover:text-blue-600 transition">Home</a></li>
                <li class="flex items-center">
                    <i class="fa-solid fa-chevron-right text-[10px] text-gray-300 mx-2"></i>
                    <span class="text-gray-900">Terms & Conditions</span>
                </li>
            </ol>
        </nav>

        <div class="bg-white rounded-3xl shadow-xl p-10 md:p-16 border border-gray-100 overflow-hidden relative">
            <div class="absolute top-0 left-0 w-full h-2 bg-gradient-to-r from-purple-600 to-blue-600"></div>
            
            <h1 class="text-4xl md:text-5xl font-black text-gray-900 mb-8 tracking-tight">Terms & Conditions</h1>
            <p class="text-gray-400 text-sm mb-12 font-bold uppercase tracking-widest">Effective Date: March 17, 2026</p>

            <div class="prose prose-blue max-w-none text-gray-600 space-y-8">
                <section>
                    <h2 class="text-2xl font-black text-gray-900 mb-4 flex items-center gap-3">
                        <span class="w-8 h-8 bg-blue-50 text-blue-600 rounded-lg flex items-center justify-center text-sm">01</span>
                        Acceptance of Terms
                    </h2>
                    <p class="leading-relaxed">By accessing and using the services of Khushi Home Sofa Repairing, you agree to comply with and be bound by these Terms and Conditions. If you do not agree, please do not use our services.</p>
                </section>

                <section>
                    <h2 class="text-2xl font-black text-gray-900 mb-4 flex items-center gap-3">
                        <span class="w-8 h-8 bg-blue-50 text-blue-600 rounded-lg flex items-center justify-center text-sm">02</span>
                        Service Provision
                    </h2>
                    <p class="leading-relaxed">We provide sofa repair, cleaning, and upholstery services. We reserve the right to refuse service or cancel bookings at our discretion due to location constraints or other factors.</p>
                </section>

                <section>
                    <h2 class="text-2xl font-black text-gray-900 mb-4 flex items-center gap-3">
                        <span class="w-8 h-8 bg-blue-50 text-blue-600 rounded-lg flex items-center justify-center text-sm">03</span>
                        User Accounts
                    </h2>
                    <p class="leading-relaxed">Users are responsible for maintaining the confidentiality of their account credentials and for all activities that occur under their account. Accuracy in provide phone numbers and addresses is critical for service delivery.</p>
                </section>

                <section>
                    <h2 class="text-2xl font-black text-gray-900 mb-4 flex items-center gap-3">
                        <span class="w-8 h-8 bg-blue-50 text-blue-600 rounded-lg flex items-center justify-center text-sm">04</span>
                        Liability Limitation
                    </h2>
                    <p class="leading-relaxed">Khushi Home Sofa Repairing is not liable for indirect, incidental, or consequential damages arising from the use of our platform or services.</p>
                </section>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

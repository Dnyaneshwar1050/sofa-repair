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
                    <span class="text-gray-900">Refund Policy</span>
                </li>
            </ol>
        </nav>

        <div class="bg-white rounded-3xl shadow-xl p-10 md:p-16 border border-gray-100 overflow-hidden relative">
            <div class="absolute top-0 left-0 w-full h-2 bg-gradient-to-r from-blue-600 to-purple-600"></div>
            
            <h1 class="text-4xl md:text-5xl font-black text-gray-900 mb-8 tracking-tight">Refund Policy</h1>
            <p class="text-gray-400 text-sm mb-12 font-bold uppercase tracking-widest">Last Updated: March 17, 2026</p>

            <div class="prose prose-blue max-w-none text-gray-600 space-y-8">
                <section>
                    <h2 class="text-2xl font-black text-gray-900 mb-4 flex items-center gap-3">
                        <span class="w-8 h-8 bg-blue-50 text-blue-600 rounded-lg flex items-center justify-center text-sm">01</span>
                        Service Satisfaction
                    </h2>
                    <p class="leading-relaxed">At Khushi Home Sofa Repairing, we strive for 100% customer satisfaction. If you are not satisfied with the repair or cleaning service provided, please contact us within 24 hours of service completion.</p>
                </section>

                <section>
                    <h2 class="text-2xl font-black text-gray-900 mb-4 flex items-center gap-3">
                        <span class="w-8 h-8 bg-blue-50 text-blue-600 rounded-lg flex items-center justify-center text-sm">02</span>
                        Cancellation & Refunds
                    </h2>
                    <p class="leading-relaxed">Cancellations made 24 hours prior to the scheduled service time are eligible for a full refund of any advance payments. Cancellations made less than 24 hours before the service may incur a 20% cancellation fee.</p>
                </section>

                <section>
                    <h2 class="text-2xl font-black text-gray-900 mb-4 flex items-center gap-3">
                        <span class="w-8 h-8 bg-blue-50 text-blue-600 rounded-lg flex items-center justify-center text-sm">03</span>
                        Process for Refunds
                    </h2>
                    <p class="leading-relaxed">Approved refunds will be processed back to the original payment method within 5-7 business days. For cash payments, refunds will be issued via bank transfer or digital wallet.</p>
                </section>

                <section class="bg-blue-50/50 p-8 rounded-2xl border border-blue-100">
                    <h2 class="text-xl font-black text-gray-900 mb-4">Need Help with Refunds?</h2>
                    <p class="mb-6 text-sm">If you have any questions regarding our refund policy, please reach out to our support team.</p>
                    <div class="flex flex-wrap gap-4">
                        <a href="tel:+919689861811" class="bg-blue-600 text-white px-6 py-3 rounded-xl font-bold flex items-center gap-2 hover:bg-blue-700 transition shadow-md">
                            <i class="fa-solid fa-phone"></i> +91 9689861811
                        </a>
                        <a href="mailto:info@silvafurniture.com" class="bg-white text-blue-600 px-6 py-3 rounded-xl font-bold border border-blue-100 hover:bg-gray-50 transition">
                            <i class="fa-solid fa-envelope"></i> Email Support
                        </a>
                    </div>
                </section>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

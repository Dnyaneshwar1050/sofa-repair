</main>

<footer class="bg-slate-900 text-gray-300 py-16 border-t border-slate-800">
    <div class="container mx-auto grid grid-cols-1 md:grid-cols-4 gap-12 px-6 lg:px-12">
        <!-- Logo and About -->
        <div class="flex flex-col items-start col-span-1 md:col-span-1">
            <a href="/" class="inline-block mb-6">
                <!-- Using a brightness filter to make the logo pop on dark bg -->
                <img src="/frontend/public/logo-dark.png" alt="Silva Furniture Logo" class="h-14 w-auto brightness-200 grayscale contrast-200" />
            </a>
            <p class="text-gray-400 text-sm leading-relaxed mb-6 font-light">
                Restore the comfort and beauty of your furniture with expert repair and upholstery services right in
                Pune. Your peace of mind is our priority.
            </p>
            <div class="flex gap-4">
                <a href="#" class="w-10 h-10 rounded-full bg-slate-800 flex items-center justify-center hover:bg-brand-600 transition-colors"><i class="fa-brands fa-facebook-f text-white"></i></a>
                <a href="#" class="w-10 h-10 rounded-full bg-slate-800 flex items-center justify-center hover:bg-brand-600 transition-colors"><i class="fa-brands fa-instagram text-white"></i></a>
                <a href="#" class="w-10 h-10 rounded-full bg-slate-800 flex items-center justify-center hover:bg-brand-600 transition-colors"><i class="fa-brands fa-twitter text-white"></i></a>
            </div>
        </div>

        <!-- Quick Links -->
        <div>
            <h4 class="text-lg font-heading font-bold mb-6 text-white flex items-center gap-2"><div class="w-2 h-2 bg-brand-500 rounded-full"></div> Quick Links</h4>
            <ul class="space-y-3">
                <li><a href="/" class="text-sm text-gray-400 hover:text-brand-400 transition-colors flex items-center gap-2"><i class="fa-solid fa-angle-right text-gray-600 text-xs"></i> Home</a></li>
                <li><a href="/my-bookings.php" class="text-sm text-gray-400 hover:text-brand-400 transition-colors flex items-center gap-2"><i class="fa-solid fa-angle-right text-gray-600 text-xs"></i> My Requests</a></li>
                <li><a href="/blog.php" class="text-sm text-gray-400 hover:text-brand-400 transition-colors flex items-center gap-2"><i class="fa-solid fa-angle-right text-gray-600 text-xs"></i> Blog & News</a></li>
                <li><a href="/contact.php" class="text-sm text-gray-400 hover:text-brand-400 transition-colors flex items-center gap-2"><i class="fa-solid fa-angle-right text-gray-600 text-xs"></i> Contact Us</a></li>
                <li><a href="/policy.php" class="text-sm text-gray-400 hover:text-white transition-colors flex items-center gap-2"><i class="fa-solid fa-angle-right text-gray-600 text-xs"></i> Refund Policy</a></li>
                <li><a href="/privacy.php" class="text-sm text-gray-400 hover:text-white transition-colors flex items-center gap-2"><i class="fa-solid fa-angle-right text-gray-600 text-xs"></i> Privacy Policy</a></li>
            </ul>
        </div>

        <!-- Services -->
        <div>
            <h4 class="text-lg font-heading font-bold mb-6 text-white flex items-center gap-2"><div class="w-2 h-2 bg-brand-500 rounded-full"></div> Our Expertise</h4>
            <ul class="space-y-3 text-sm text-gray-400">
                <li class="flex items-center gap-2"><i class="fa-regular fa-circle-check text-brand-500"></i> Premium Sofa Repair</li>
                <li class="flex items-center gap-2"><i class="fa-regular fa-circle-check text-brand-500"></i> Custom Reupholstery</li>
                <li class="flex items-center gap-2"><i class="fa-regular fa-circle-check text-brand-500"></i> Deep Fabric Cleaning</li>
                <li class="flex items-center gap-2"><i class="fa-regular fa-circle-check text-brand-500"></i> Wood Polishing</li>
            </ul>
        </div>

        <!-- Contact Information -->
        <div class="col-span-1 md:col-span-1">
            <h4 class="text-lg font-heading font-bold mb-6 text-white flex items-center gap-2"><div class="w-2 h-2 bg-brand-500 rounded-full"></div> Reach Us</h4>
            <div class="space-y-4">
                <div class="flex items-start gap-4 p-4 rounded-xl bg-slate-800/50 border border-slate-700 hover:bg-slate-800 transition-colors">
                    <div class="w-10 h-10 rounded-full bg-brand-500/10 text-brand-500 flex items-center justify-center shrink-0">
                        <i class="fa-solid fa-phone"></i>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Call Us</p>
                        <a href="tel:+919689861811"
                            class="text-white font-medium hover:text-brand-400 transition-colors text-sm">+91 9689861811</a>
                    </div>
                </div>
                
                <div class="flex items-start gap-4 p-4 rounded-xl bg-slate-800/50 border border-slate-700 hover:bg-slate-800 transition-colors">
                    <div class="w-10 h-10 rounded-full bg-brand-500/10 text-brand-500 flex items-center justify-center shrink-0">
                        <i class="fa-solid fa-envelope"></i>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Email Support</p>
                        <a href="mailto:info@silvafurniture.com"
                            class="text-white font-medium hover:text-brand-400 transition-colors text-sm break-all">info@silvafurniture.com</a>
                    </div>
                </div>

                <a href="https://play.google.com/store/apps/details?id=com.lsoysapps.khushihomesofarepairing" target="_blank"
                    class="mt-4 w-full flex items-center justify-center gap-2 bg-brand-600 text-white px-4 py-3 rounded-xl font-semibold hover:bg-brand-700 transition shadow-lg active:scale-95">
                    <i class="fa-brands fa-google-play"></i> Download App
                </a>
            </div>
        </div>
    </div>

    <!-- Copyright -->
    <div class="container mx-auto px-6 mt-16 pt-8 border-t border-slate-800 text-center flex flex-col md:flex-row justify-between items-center gap-4">
        <p class="text-xl font-heading font-bold text-white">Silva Furniture</p>
        <p class="text-gray-500 text-sm font-light">
            &copy; <?php echo date('Y'); ?> Silva Furniture. All rights reserved.
        </p>
    </div>
</footer>

<!-- Custom Javascript (if any) -->
<script src="/assets/js/script.js"></script>
</body>

</html>
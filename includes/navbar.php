<?php
$current_page = basename($_SERVER['PHP_SELF']);
$isLoggedIn = isLoggedIn();
$userRole = $_SESSION['user_role'] ?? 'user';
?>
<nav class="bg-white text-gray-900 shadow-lg border-b-2 border-gray-100 sticky top-0 z-50">
    <div class="container mx-auto px-4 py-2">
        <div class="flex justify-between items-center">
            <!-- Logo -->
            <a href="/" class="flex items-center">
                <!-- Using frontend logo for now -->
                <img src="/frontend/public/logo-light.png" alt="Khushi Home Sofa Repairing"
                    class="h-12 w-auto scale-150 ml-4 hidden dark:block" id="logo-dark">
                <img src="/frontend/public/logo-dark.png" alt="Khushi Home Sofa Repairing"
                    class="h-12 w-auto scale-150 ml-4 block dark:hidden" id="logo-light">
            </a>

            <!-- Desktop Navigation -->
            <div class="hidden lg:flex space-x-4 items-center">
                <a href="/"
                    class="text-lg font-medium transition-colors p-2 <?= ($current_page == 'index.php' || $current_page == '') ? 'text-orange-500 font-bold border-b-2 border-orange-500' : 'text-gray-700 hover:text-orange-500' ?>">Home</a>
                <a href="/blog.php"
                    class="text-lg font-medium transition-colors p-2 <?= ($current_page == 'blog.php') ? 'text-orange-500 font-bold border-b-2 border-orange-500' : 'text-gray-700 hover:text-orange-500' ?>">Blog</a>
                <a href="/contact.php"
                    class="text-lg font-medium transition-colors p-2 <?= ($current_page == 'contact.php') ? 'text-orange-500 font-bold border-b-2 border-orange-500' : 'text-gray-700 hover:text-orange-500' ?>">Contact</a>

                <?php if ($isLoggedIn && $userRole === 'provider'): ?>
                    <a href="/provider-services.php"
                        class="text-lg font-medium transition-colors p-2 text-gray-700 hover:text-orange-500">My
                        Services</a>
                    <a href="/my-jobs.php"
                        class="text-lg font-medium transition-colors p-2 text-gray-700 hover:text-orange-500">My Jobs</a>
                <?php endif; ?>

                <?php if ($isLoggedIn && in_array($userRole, ['admin', 'superadmin'])): ?>
                    <a href="/admin/index.php"
                        class="text-lg font-medium transition-colors p-2 text-gray-700 hover:text-orange-500">Admin</a>
                <?php endif; ?>

                <?php if ($isLoggedIn): ?>
                    <div class="flex items-center space-x-3">
                        <a href="/profile.php"
                            class="text-lg font-medium transition-colors p-2 text-gray-700 hover:text-orange-500">
                            <i class="fa-solid fa-circle-user mr-1"></i> Profile
                        </a>
                        <a href="/my-bookings.php"
                            class="text-lg font-medium transition-colors p-2 text-gray-700 hover:text-orange-500">My
                            Requests</a>
                        <a href="/logout.php"
                            class="bg-red-600 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-red-700 active:scale-95 transition-all shadow-md">Logout</a>
                    </div>
                <?php else: ?>
                    <a href="/login.php"
                        class="bg-blue-600 text-white px-6 py-2 rounded-lg text-sm font-semibold hover:bg-orange-500 active:scale-95 transition-all shadow-md">Login
                        / Register</a>
                <?php endif; ?>
            </div>

            <!-- Mobile Menu Button -->
            <button id="mobile-menu-btn"
                class="lg:hidden p-2 rounded-lg hover:bg-gray-100 active:bg-gray-200 transition-colors">
                <i class="fa-solid fa-bars h-6 w-6 text-gray-700" id="mobile-menu-icon-open"></i>
                <i class="fa-solid fa-times h-6 w-6 text-gray-700 hidden" id="mobile-menu-icon-close"></i>
            </button>
        </div>

        <!-- Mobile Menu -->
        <div id="mobile-menu" class="hidden lg:hidden mt-4 pb-4 space-y-2 border-t pt-4 animate-slideDown">
            <a href="/"
                class="block text-lg font-medium transition-all p-3 rounded-lg <?= ($current_page == 'index.php' || $current_page == '') ? 'bg-orange-500 text-white font-bold shadow-md' : 'text-gray-700 hover:bg-gray-100' ?>">🏠
                Home</a>
            <a href="/blog.php"
                class="block text-lg font-medium transition-all p-3 rounded-lg <?= ($current_page == 'blog.php') ? 'bg-orange-500 text-white font-bold shadow-md' : 'text-gray-700 hover:bg-gray-100' ?>">📝
                Blog</a>
            <a href="/contact.php"
                class="block text-lg font-medium transition-all p-3 rounded-lg <?= ($current_page == 'contact.php') ? 'bg-orange-500 text-white font-bold shadow-md' : 'text-gray-700 hover:bg-gray-100' ?>">📞
                Contact</a>

            <?php if ($isLoggedIn && $userRole === 'provider'): ?>
                <a href="/provider-services.php"
                    class="block text-lg font-medium transition-all p-3 rounded-lg text-gray-700 hover:bg-gray-100">🛠️ My
                    Services</a>
                <a href="/my-jobs.php"
                    class="block text-lg font-medium transition-all p-3 rounded-lg text-gray-700 hover:bg-gray-100">💼 My
                    Jobs</a>
            <?php endif; ?>

            <?php if ($isLoggedIn && in_array($userRole, ['admin', 'superadmin'])): ?>
                <a href="/admin/index.php"
                    class="block text-lg font-medium transition-all p-3 rounded-lg text-gray-700 hover:bg-gray-100">⚙️ Admin
                    Panel</a>
            <?php endif; ?>

            <?php if ($isLoggedIn): ?>
                <a href="/profile.php"
                    class="block text-lg font-medium transition-all p-3 rounded-lg text-gray-700 hover:bg-gray-100">
                    <i class="fa-solid fa-circle-user mr-2"></i> Profile
                </a>
                <a href="/my-bookings.php"
                    class="block text-lg font-medium transition-all p-3 rounded-lg text-gray-700 hover:bg-gray-100">📋 My
                    Requests</a>
                <a href="/logout.php"
                    class="w-full text-center block bg-red-600 text-white px-4 py-3 rounded-lg text-base font-semibold hover:bg-red-700 active:scale-95 transition-all shadow-md">🚪
                    Logout</a>
            <?php else: ?>
                <a href="/login.php"
                    class="block text-center bg-blue-600 text-white px-4 py-3 rounded-lg text-base font-semibold hover:bg-orange-500 active:scale-95 transition-all shadow-md">🔐
                    Login / Register</a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const mobileMenuBtn = document.getElementById('mobile-menu-btn');
        const mobileMenu = document.getElementById('mobile-menu');
        const iconOpen = document.getElementById('mobile-menu-icon-open');
        const iconClose = document.getElementById('mobile-menu-icon-close');

        // Handle dark mode for logo
        const isDarkMode = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
        if (isDarkMode) {
            document.getElementById('logo-light').classList.add('hidden');
            document.getElementById('logo-dark').classList.remove('hidden');
        } else {
            document.getElementById('logo-dark').classList.add('hidden');
            document.getElementById('logo-light').classList.remove('hidden');
        }

        if (mobileMenuBtn) {
            mobileMenuBtn.addEventListener('click', () => {
                mobileMenu.classList.toggle('hidden');
                iconOpen.classList.toggle('hidden');
                iconClose.classList.toggle('hidden');
            });
        }
    });
</script>
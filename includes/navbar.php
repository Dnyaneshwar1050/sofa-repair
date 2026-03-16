<?php
$current_page = basename($_SERVER['PHP_SELF']);
$isLoggedIn = isLoggedIn();
$userRole = $_SESSION['user_role'] ?? 'user';
?>
<nav class="bg-white/80 backdrop-blur-md text-gray-900 border-b border-gray-100 sticky top-0 z-50 transition-all duration-300 shadow-sm">
    <div class="container mx-auto px-4 py-2">
        <div class="flex justify-between items-center">
            <!-- Logo -->
            <a href="/" class="flex items-center">
                <!-- Using frontend logo for now -->
                <img src="/frontend/public/logo-light.png" alt="Silva Furniture"
                    class="h-12 w-auto scale-150 ml-4 hidden dark:block" id="logo-dark">
                <img src="/frontend/public/logo-dark.png" alt="Silva Furniture"
                    class="h-12 w-auto scale-150 ml-4 block dark:hidden" id="logo-light">
            </a>

            <!-- Desktop Navigation -->
            <div class="hidden lg:flex space-x-4 items-center">
                <a href="/"
                    class="text-sm tracking-wide font-semibold transition-colors p-2 <?= ($current_page == 'index.php' || $current_page == '') ? 'text-brand-600 border-b-2 border-brand-600' : 'text-gray-600 hover:text-brand-600' ?>">Home</a>
                <a href="/blog.php"
                    class="text-sm tracking-wide font-semibold transition-colors p-2 <?= ($current_page == 'blog.php') ? 'text-brand-600 border-b-2 border-brand-600' : 'text-gray-600 hover:text-brand-600' ?>">Blog</a>
                <a href="/contact.php"
                    class="text-sm tracking-wide font-semibold transition-colors p-2 <?= ($current_page == 'contact.php') ? 'text-brand-600 border-b-2 border-brand-600' : 'text-gray-600 hover:text-brand-600' ?>">Contact</a>

                <?php if ($isLoggedIn && $userRole === 'provider'): ?>
                    <a href="/provider-services.php"
                        class="text-sm tracking-wide font-semibold transition-colors p-2 text-gray-600 hover:text-brand-600">My
                        Services</a>
                    <a href="/my-jobs.php"
                        class="text-sm tracking-wide font-semibold transition-colors p-2 text-gray-600 hover:text-brand-600">My Jobs</a>
                <?php endif; ?>

                <?php if ($isLoggedIn && in_array($userRole, ['admin', 'superadmin'])): ?>
                    <a href="/admin/index.php"
                        class="text-sm tracking-wide font-semibold transition-colors p-2 text-gray-600 hover:text-brand-600">Admin</a>
                <?php endif; ?>

                <?php if ($isLoggedIn): ?>
                    <div class="flex items-center space-x-4 pl-4 border-l border-gray-200">
                        <a href="/profile.php"
                            class="text-sm tracking-wide font-semibold transition-colors text-gray-600 hover:text-brand-600">
                            <i class="fa-solid fa-circle-user mr-1 text-lg align-middle"></i> Profile
                        </a>
                        <a href="/my-bookings.php"
                            class="text-sm tracking-wide font-semibold transition-colors text-gray-600 hover:text-brand-600">My
                            Requests</a>
                        <div class="relative inline-block cursor-pointer px-1">
                            <i class="fa-solid fa-bell text-gray-500 text-lg hover:text-brand-600 transition-colors"></i>
                            <span id="notification-badge" class="absolute -top-1 -right-1 bg-red-500 text-white rounded-full text-[10px] w-4 h-4 flex items-center justify-center font-bold shadow-sm" style="display: none;">0</span>
                        </div>
                        <a href="/logout.php"
                            class="bg-gray-100 text-gray-700 px-5 py-2 rounded-full text-sm font-semibold hover:bg-gray-200 active:scale-95 transition-all">Logout</a>
                    </div>
                <?php else: ?>
                    <div class="pl-4 border-l border-gray-200">
                        <a href="/login.php"
                            class="bg-brand-600 text-white px-6 py-2.5 rounded-full text-sm font-semibold hover:bg-brand-700 hover:shadow-lg active:scale-95 transition-all">Login
                            / Register</a>
                    </div>
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
                class="block text-base font-medium transition-all p-3 rounded-lg <?= ($current_page == 'index.php' || $current_page == '') ? 'bg-brand-50 text-brand-700 font-bold' : 'text-gray-600 hover:bg-gray-50 hover:text-brand-600' ?>">🏠
                Home</a>
            <a href="/blog.php"
                class="block text-base font-medium transition-all p-3 rounded-lg <?= ($current_page == 'blog.php') ? 'bg-brand-50 text-brand-700 font-bold' : 'text-gray-600 hover:bg-gray-50 hover:text-brand-600' ?>">📝
                Blog</a>
            <a href="/contact.php"
                class="block text-base font-medium transition-all p-3 rounded-lg <?= ($current_page == 'contact.php') ? 'bg-brand-50 text-brand-700 font-bold' : 'text-gray-600 hover:bg-gray-50 hover:text-brand-600' ?>">📞
                Contact</a>

            <?php if ($isLoggedIn && $userRole === 'provider'): ?>
                <a href="/provider-services.php"
                    class="block text-base font-medium transition-all p-3 rounded-lg text-gray-600 hover:bg-gray-50 hover:text-brand-600">🛠️ My
                    Services</a>
                <a href="/my-jobs.php"
                    class="block text-base font-medium transition-all p-3 rounded-lg text-gray-600 hover:bg-gray-50 hover:text-brand-600">💼 My
                    Jobs</a>
            <?php endif; ?>

            <?php if ($isLoggedIn && in_array($userRole, ['admin', 'superadmin'])): ?>
                <a href="/admin/index.php"
                    class="block text-base font-medium transition-all p-3 rounded-lg text-gray-600 hover:bg-gray-50 hover:text-brand-600">⚙️ Admin
                    Panel</a>
            <?php endif; ?>

            <?php if ($isLoggedIn): ?>
                <a href="/profile.php"
                    class="block text-base font-medium transition-all p-3 rounded-lg text-gray-600 hover:bg-gray-50 hover:text-brand-600">
                    <i class="fa-solid fa-circle-user mr-2"></i> Profile
                </a>
                <a href="/my-bookings.php"
                    class="block text-base font-medium transition-all p-3 rounded-lg text-gray-600 hover:bg-gray-50 hover:text-brand-600">📋 My
                    Requests</a>
                <a href="/logout.php"
                    class="w-full text-center block bg-gray-100 text-gray-700 mt-4 px-4 py-3 rounded-xl text-sm font-semibold hover:bg-gray-200 active:scale-95 transition-all">🚪
                    Logout</a>
            <?php else: ?>
                <a href="/login.php"
                    class="block text-center mt-4 bg-brand-600 text-white px-4 py-3 rounded-xl text-sm font-semibold hover:bg-brand-700 shadow-md active:scale-95 transition-all">🔐
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
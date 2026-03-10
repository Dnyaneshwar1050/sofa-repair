<?php
// admin/includes/sidebar.php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<aside class="w-64 bg-gray-900 h-screen flex flex-col shadow-2xl shrink-0 transition-all duration-300 relative z-20">
    <!-- Header -->
    <div class="h-16 flex items-center justify-center border-b border-gray-800 px-4 bg-gray-950">
        <a href="/admin/index.php" class="flex items-center gap-2">
            <span class="text-orange-500 text-xl font-black tracking-wider uppercase">Khushi</span>
            <span class="text-white text-sm tracking-widest font-light opacity-80">ADMIN</span>
        </a>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 overflow-y-auto py-6 px-3 custom-scrollbar">
        <ul class="space-y-1">
            <li>
                <a href="/admin/index.php"
                    class="<?= ($current_page == 'index.php') ? 'bg-orange-600 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' ?> group flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all duration-200">
                    <i
                        class="fa-solid fa-chart-pie w-6 opacity-70 group-hover:opacity-100 flex-shrink-0 text-center"></i>
                    Dashboard
                </a>
            </li>

            <li class="pt-4 pb-2">
                <span class="text-xs font-bold text-gray-500 uppercase tracking-wider px-4">Management</span>
            </li>

            <li>
                <a href="/admin/bookings.php"
                    class="<?= ($current_page == 'bookings.php') ? 'bg-orange-600 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' ?> group flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all duration-200">
                    <i
                        class="fa-solid fa-calendar-check w-6 opacity-70 group-hover:opacity-100 flex-shrink-0 text-center"></i>
                    Bookings
                </a>
            </li>

            <li>
                <a href="/admin/services.php"
                    class="<?= ($current_page == 'services.php') ? 'bg-orange-600 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' ?> group flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all duration-200">
                    <i class="fa-solid fa-couch w-6 opacity-70 group-hover:opacity-100 flex-shrink-0 text-center"></i>
                    Services
                </a>
            </li>

            <li>
                <a href="/admin/categories.php"
                    class="<?= ($current_page == 'categories.php') ? 'bg-orange-600 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' ?> group flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all duration-200">
                    <i class="fa-solid fa-tags w-6 opacity-70 group-hover:opacity-100 flex-shrink-0 text-center"></i>
                    Categories
                </a>
            </li>

            <?php if ($_SESSION['user_role'] === 'superadmin'): ?>
                <li class="pt-4 pb-2">
                    <span class="text-xs font-bold text-gray-500 uppercase tracking-wider px-4">Administration</span>
                </li>

                <li>
                    <a href="/admin/users.php"
                        class="<?= ($current_page == 'users.php') ? 'bg-orange-600 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' ?> group flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all duration-200">
                        <i class="fa-solid fa-users w-6 opacity-70 group-hover:opacity-100 flex-shrink-0 text-center"></i>
                        Users
                    </a>
                </li>
            <?php endif; ?>
        </ul>
    </nav>

    <!-- Footer info -->
    <div class="p-4 border-t border-gray-800 flex flex-col space-y-2 text-xs text-gray-500 text-center">
        <a href="/" target="_blank"
            class="flex justify-center items-center text-gray-400 hover:text-white transition duration-200 py-1 bg-gray-800 rounded-lg group">
            <i class="fa-solid fa-up-right-from-square mr-2 text-[10px] opacity-70 group-hover:text-orange-400"></i>
            View Website
        </a>
        <p class="mt-2">Khushi Admin v1.0</p>
    </div>
</aside>
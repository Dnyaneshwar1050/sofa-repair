<?php
// admin/includes/sidebar.php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<aside class="w-[280px] bg-[#253b70] h-full flex flex-col shrink-0 relative z-20 text-white shadow-xl">
    <!-- Header Logo -->
    <div class="pt-8 pb-6 px-6 flex flex-col items-center justify-center border-b border-light-blue-900/30">
        <h1 class="text-[#fbbd06] text-2xl font-serif-custom font-bold text-center leading-tight tracking-wide">
            Silva<br>Furniture
        </h1>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 overflow-y-auto py-6 custom-scrollbar">
        <ul class="space-y-1">
            <li class="px-6 py-2">
                <span class="text-sm font-bold text-white mb-2 block tracking-wide">Management</span>
            </li>

            <!-- We don't have a specific dashboard link in the screenshot sidebar, but we need one -->
            <li class="px-2">
                <a href="/admin/index.php"
                    class="<?= ($current_page == 'index.php') ? 'bg-white/10 text-white border-l-4 border-[#fbbd06]' : 'text-gray-300 hover:bg-white/5 hover:text-white border-l-4 border-transparent' ?> group flex items-center px-4 py-3 text-sm font-semibold transition-all duration-200">
                    <i class="fa-solid fa-chart-line w-8 text-center text-lg"></i>
                    Admin Dashboard
                </a>
            </li>

            <li class="px-2">
                <a href="/admin/users.php"
                    class="<?= ($current_page == 'users.php') ? 'bg-white/10 text-white border-l-4 border-[#fbbd06]' : 'text-gray-300 hover:bg-white/5 hover:text-white border-l-4 border-transparent' ?> group flex items-center px-4 py-3 text-sm font-semibold transition-all duration-200">
                    <i class="fa-solid fa-user w-8 text-center text-lg"></i>
                    User Accounts
                </a>
            </li>

            <li class="px-2">
                <a href="/admin/categories.php"
                    class="<?= ($current_page == 'categories.php') ? 'bg-white/10 text-white border-l-4 border-[#fbbd06]' : 'text-gray-300 hover:bg-white/5 hover:text-white border-l-4 border-transparent' ?> group flex items-center px-4 py-3 text-sm font-semibold transition-all duration-200">
                    <i class="fa-solid fa-tags w-8 text-center text-lg"></i>
                    Categories
                </a>
            </li>

            <li class="px-2">
                <a href="/admin/services.php"
                    class="<?= ($current_page == 'services.php') ? 'bg-white/10 text-white border-l-4 border-[#fbbd06]' : 'text-gray-300 hover:bg-white/5 hover:text-white border-l-4 border-transparent' ?> group flex items-center px-4 py-3 text-sm font-semibold transition-all duration-200">
                    <i class="fa-solid fa-couch w-8 text-center text-lg"></i>
                    Service Catalog
                </a>
            </li>

            <li class="px-2">
                <a href="/admin/bookings.php"
                    class="<?= ($current_page == 'bookings.php') ? 'bg-white/10 text-white border-l-4 border-[#fbbd06]' : 'text-gray-300 hover:bg-white/5 hover:text-white border-l-4 border-transparent' ?> group flex items-center px-4 py-3 text-sm font-semibold transition-all duration-200">
                    <i class="fa-solid fa-clipboard-list w-8 text-center text-lg"></i>
                    Bookings/Requests
                </a>
            </li>

            <!-- Separator -->
            <li class="my-4 border-t border-white/10 mx-6"></li>

            <li class="px-2">
                <a href="#"
                    class="text-gray-300 hover:bg-white/5 hover:text-white border-l-4 border-transparent group flex items-center justify-between px-4 py-3 text-sm font-semibold transition-all duration-200">
                    <div class="flex items-center">
                        <i class="fa-solid fa-bell w-8 text-center text-lg"></i>
                        Notifications
                    </div>
                    <span class="bg-red-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full">20</span>
                </a>
            </li>

            <li class="px-2">
                <a href="#"
                    class="text-gray-300 hover:bg-white/5 hover:text-white border-l-4 border-transparent group flex items-center px-4 py-3 text-sm font-semibold transition-all duration-200">
                    <i class="fa-regular fa-clock w-8 text-center text-lg"></i>
                    <div class="leading-tight">
                        Recent Requests<br>
                        <span class="text-[10px] text-gray-400 font-normal">(24h)</span>
                    </div>
                </a>
            </li>

            <li class="px-2">
                <a href="#"
                    class="text-gray-300 hover:bg-white/5 hover:text-white border-l-4 border-transparent group flex items-center px-4 py-3 text-sm font-semibold transition-all duration-200">
                    <i class="fa-solid fa-envelope w-8 text-center text-lg"></i>
                    Contact Messages
                </a>
            </li>

            <li class="px-2">
                <a href="#" class="text-gray-300 hover:bg-white/5 hover:text-white border-l-4 border-transparent group flex items-center px-4 py-3 text-sm font-semibold transition-all duration-200">
                    <i class="fa-brands fa-blogger-b w-8 text-center text-lg"></i>
                    Blog Management
                </a>
            </li>

            <li class="px-2 pt-6">
                <a href="#" class="bg-[#fbbd06] text-black border-l-4 border-transparent rounded group flex items-center mx-4 px-4 py-3 text-sm font-bold shadow-md transition-all duration-200">
                    <i class="fa-solid fa-gear w-8 text-center text-lg"></i>
                    System Settings
                </a>
            </li>
            
            <li class="px-2 pt-4">
                <a href="/" class="text-gray-300 hover:text-white group flex items-center px-4 py-2 text-sm font-semibold transition-all duration-200">
                    <i class="fa-solid fa-desktop w-8 text-center text-lg"></i>
                    Back to Client Site
                </a>
            </li>
        </ul>
    </nav>
    
    <!-- Footer Logout Button -->
    <div class="p-6">
        <a href="/logout.php"
            class="flex justify-center items-center text-white bg-red-600 hover:bg-red-700 font-bold rounded shadow-lg transition duration-200 py-3 w-full">
            <i class="fa-solid fa-right-from-bracket mr-2"></i>
            Logout
        </a>
    </div>
</aside>
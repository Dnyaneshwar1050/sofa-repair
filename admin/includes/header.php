<?php
// admin/includes/header.php
require_once __DIR__ . '/../../includes/db.php';

requireLogin();
requireRole(['admin', 'superadmin']);

$current_page = basename($_SERVER['PHP_SELF']);
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Admin Panel - Khushi Home Sofa Repair</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body class="bg-gray-100 flex">

    <!-- Sidebar -->
    <?php include __DIR__ . '/sidebar.php'; ?>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col h-screen overflow-hidden text-gray-900">
        <!-- Topbar -->
        <header
            class="bg-white shadow-sm border-b border-gray-200 h-16 flex items-center justify-between px-6 shrink-0 z-10">
            <h2 class="text-xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-orange-600 to-orange-400">
                Dashboard
            </h2>
            <div class="flex items-center gap-4">
                <span class="text-sm font-medium text-gray-600 bg-gray-100 px-3 py-1 rounded-full border">
                    <i class="fa-solid fa-user-shield text-orange-500 mr-1"></i>
                    <?= htmlspecialchars($_SESSION['user_name'] ?? 'Admin') ?>
                </span>
                <a href="/logout.php" class="text-gray-500 hover:text-red-600 transition" title="Logout">
                    <i class="fa-solid fa-right-from-bracket text-lg"></i>
                </a>
            </div>
        </header>

        <!-- Page Content -->
        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-6">
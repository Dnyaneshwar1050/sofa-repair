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
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .font-serif-custom { font-family: 'Playfair Display', serif; }
        .font-sans-custom { font-family: 'Inter', sans-serif; }
    </style>
</head>

<body class="bg-[#f8f9fa] flex flex-col h-screen font-sans-custom">

    <!-- Top Navbar -->
    <div class="shrink-0 relative z-50">
        <?php include __DIR__ . '/../../includes/navbar.php'; ?>
    </div>

    <!-- Main Workspace -->
    <div class="flex-1 flex overflow-hidden">
        <!-- Sidebar -->
        <?php include __DIR__ . '/sidebar.php'; ?>

        <!-- Page Content -->
        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-[#f8f9fa] p-8">
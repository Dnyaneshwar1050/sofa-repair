<?php
declare(strict_types=1);
require_once __DIR__ . '/helpers.php';
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php echo h($pageTitle ?? 'Khushi Home Sofa Repair'); ?></title>
  <link rel="stylesheet" href="/css/style.css">
</head>
<body>
  <header class="site-header">
    <div class="container header-inner">
      <a class="brand" href="/index.php">Khushi Home Sofa Repair</a>
      <nav class="nav">
        <a href="/index.php">Home</a>
        <a href="/services.php">Services</a>
        <a href="/contact.php">Contact</a>
        <a href="/admin/login.php">Admin</a>
      </nav>
    </div>
  </header>
  <main class="container">


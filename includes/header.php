<?php
$current = basename($_SERVER['PHP_SELF']);
$basePrefix = (strpos($_SERVER['PHP_SELF'], '/admin/') !== false || strpos($_SERVER['PHP_SELF'], '/user/') !== false) ? '../' : '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? e($pageTitle) . ' - ' : '' ?>Biliiard And Cafe</title>
    <link rel="stylesheet" href="<?= $basePrefix ?>assets/css/style.css">
</head>
<body>
<header class="navbar">
    <a href="<?= $basePrefix ?>index.php" class="brand">
        <span class="brand-icon">🎱</span>
        <span>Biliiard And Cafe</span>
    </a>
    <button class="nav-toggle" onclick="toggleNav()">☰</button>
    <nav id="navMenu">
        <?php if (isLoggedIn()): ?>
            <?php if (isAdmin()): ?>
                <a href="<?= $basePrefix ?>admin/dashboard.php">Dashboard</a>
                <a href="<?= $basePrefix ?>admin/tables.php">Meja</a>
                <a href="<?= $basePrefix ?>admin/menus.php">Menu</a>
                <a href="<?= $basePrefix ?>admin/bookings.php">Booking</a>
                <a href="<?= $basePrefix ?>admin/orders.php">Pesanan</a>
            <?php else: ?>
                <a href="<?= $basePrefix ?>user/dashboard.php">Dashboard</a>
                <a href="<?= $basePrefix ?>user/booking.php">Booking</a>
                <a href="<?= $basePrefix ?>user/menu.php">Menu Cafe</a>
                <a href="<?= $basePrefix ?>user/my_bookings.php">Booking Saya</a>
                <a href="<?= $basePrefix ?>user/my_orders.php">Pesanan Saya</a>
            <?php endif; ?>
            <a href="<?= $basePrefix ?>logout.php" class="btn btn-outline">Logout</a>
        <?php else: ?>
            <a href="<?= $basePrefix ?>index.php">Home</a>
            <a href="<?= $basePrefix ?>login.php">Login</a>
            <a href="<?= $basePrefix ?>register.php" class="btn btn-primary">Daftar</a>
        <?php endif; ?>
    </nav>
</header>
<main>

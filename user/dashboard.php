<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
requireLogin();
if (isAdmin()) redirect('../admin/dashboard.php');

$userId = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT COUNT(*) total FROM bookings WHERE user_id=?");
$stmt->execute([$userId]);
$totalBookings = $stmt->fetch()['total'];
$stmt = $pdo->prepare("SELECT COUNT(*) total FROM orders WHERE user_id=?");
$stmt->execute([$userId]);
$totalOrders = $stmt->fetch()['total'];
$stmt = $pdo->prepare("SELECT b.*, t.table_name FROM bookings b JOIN billiard_tables t ON t.id=b.table_id WHERE b.user_id=? ORDER BY b.created_at DESC LIMIT 5");
$stmt->execute([$userId]);
$bookings = $stmt->fetchAll();
$pageTitle = 'Dashboard User';
require_once '../includes/header.php';
?>
<section class="container">
    <div class="section-title">
        <div><h2>Dashboard User</h2><p>Halo, <?= e($_SESSION['name']) ?>. Silakan booking meja atau pesan menu cafe.</p></div>
        <div class="actions"><a href="booking.php" class="btn btn-primary">Booking Meja</a><a href="menu.php" class="btn btn-brown">Pesan Menu</a></div>
    </div>
    <div class="stats">
        <div class="stat-card"><b><?= $totalBookings ?></b><span>Total Booking</span></div>
        <div class="stat-card"><b><?= $totalOrders ?></b><span>Total Pesanan</span></div>
        <div class="stat-card"><b>🎱</b><span>Billiard</span></div>
        <div class="stat-card"><b>☕</b><span>Cafe</span></div>
    </div>
    <div class="card">
        <h3>Booking Terbaru Saya</h3>
        <div class="table-responsive">
            <table class="data-table">
                <thead><tr><th>Kode</th><th>Meja</th><th>Tanggal</th><th>Jam</th><th>Total</th><th>Status</th></tr></thead>
                <tbody>
                <?php foreach ($bookings as $b): ?><tr><td><?= e($b['booking_code']) ?></td><td><?= e($b['table_name']) ?></td><td><?= e($b['booking_date']) ?></td><td><?= e(substr($b['start_time'],0,5)) ?> - <?= e(substr($b['end_time'],0,5)) ?></td><td><?= rupiah($b['total_price']) ?></td><td><span class="badge badge-<?= badgeClass($b['status']) ?>"><?= e($b['status']) ?></span></td></tr><?php endforeach; ?>
                <?php if (!$bookings): ?><tr><td colspan="6">Belum ada booking.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>
<?php require_once '../includes/footer.php'; ?>

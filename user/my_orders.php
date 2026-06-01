<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
requireLogin();
if (isAdmin()) redirect('../admin/dashboard.php');
$stmt = $pdo->prepare("SELECT o.*, b.booking_code FROM orders o LEFT JOIN bookings b ON b.id=o.booking_id WHERE o.user_id=? ORDER BY o.created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$orders = $stmt->fetchAll();
$pageTitle = 'Pesanan Saya';
require_once '../includes/header.php';
?>
<section class="container">
    <div class="section-title"><div><h2>Pesanan Saya</h2><p>Riwayat pesanan menu cafe.</p></div><a class="btn btn-primary" href="menu.php">Pesan Lagi</a></div>
    <div class="grid">
    <?php foreach ($orders as $o): ?>
        <?php $stmt = $pdo->prepare("SELECT oi.*, m.menu_name FROM order_items oi JOIN menus m ON m.id=oi.menu_id WHERE oi.order_id=?"); $stmt->execute([$o['id']]); $items = $stmt->fetchAll(); ?>
        <div class="card">
            <div class="section-title"><div><h3><?= e($o['order_code']) ?></h3><p>Booking: <?= e($o['booking_code'] ?? '-') ?> | Total: <b><?= rupiah($o['total_price']) ?></b></p></div><span class="badge badge-<?= badgeClass($o['status']) ?>"><?= e($o['status']) ?></span></div>
            <div class="table-responsive"><table class="data-table"><thead><tr><th>Menu</th><th>Qty</th><th>Harga</th><th>Subtotal</th></tr></thead><tbody><?php foreach ($items as $it): ?><tr><td><?= e($it['menu_name']) ?></td><td><?= e($it['quantity']) ?></td><td><?= rupiah($it['price']) ?></td><td><?= rupiah($it['subtotal']) ?></td></tr><?php endforeach; ?></tbody></table></div>
        </div>
    <?php endforeach; ?>
    <?php if (!$orders): ?><div class="card">Belum ada pesanan.</div><?php endif; ?>
    </div>
</section>
<?php require_once '../includes/footer.php'; ?>

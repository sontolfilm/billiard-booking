<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("UPDATE orders SET status=? WHERE id=?");
    $stmt->execute([$_POST['status'], $_POST['id']]);
    redirect('orders.php?msg=saved');
}

$message = isset($_GET['msg']) ? 'Status pesanan berhasil diperbarui.' : '';
$orders = $pdo->query("SELECT o.*, u.name, b.booking_code FROM orders o JOIN users u ON u.id=o.user_id LEFT JOIN bookings b ON b.id=o.booking_id ORDER BY o.created_at DESC")->fetchAll();
$pageTitle = 'Kelola Pesanan';
require_once '../includes/header.php';
?>
<section class="container">
    <div class="section-title"><div><h2>Kelola Pesanan Cafe</h2><p>Atur status pesanan makanan dan minuman.</p></div></div>
    <?php if ($message): ?><div class="alert alert-success"><?= e($message) ?></div><?php endif; ?>
    <div class="grid">
        <?php foreach ($orders as $o): ?>
            <?php
            $stmt = $pdo->prepare("SELECT oi.*, m.menu_name FROM order_items oi JOIN menus m ON m.id=oi.menu_id WHERE oi.order_id=?");
            $stmt->execute([$o['id']]);
            $items = $stmt->fetchAll();
            ?>
            <div class="card">
                <div class="section-title">
                    <div>
                        <h3><?= e($o['order_code']) ?> - <?= e($o['name']) ?></h3>
                        <p>Booking: <?= e($o['booking_code'] ?? '-') ?> | Total: <b><?= rupiah($o['total_price']) ?></b></p>
                    </div>
                    <span class="badge badge-<?= badgeClass($o['status']) ?>"><?= e($o['status']) ?></span>
                </div>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead><tr><th>Menu</th><th>Qty</th><th>Harga</th><th>Subtotal</th></tr></thead>
                        <tbody><?php foreach ($items as $it): ?><tr><td><?= e($it['menu_name']) ?></td><td><?= e($it['quantity']) ?></td><td><?= rupiah($it['price']) ?></td><td><?= rupiah($it['subtotal']) ?></td></tr><?php endforeach; ?></tbody>
                    </table>
                </div>
                <form method="post" class="actions" style="margin-top:14px">
                    <input type="hidden" name="id" value="<?= $o['id'] ?>">
                    <select name="status" class="form-control" style="max-width:170px"><option <?= $o['status']=='pending'?'selected':'' ?>>pending</option><option <?= $o['status']=='process'?'selected':'' ?>>process</option><option <?= $o['status']=='ready'?'selected':'' ?>>ready</option><option <?= $o['status']=='completed'?'selected':'' ?>>completed</option><option <?= $o['status']=='cancelled'?'selected':'' ?>>cancelled</option></select>
                    <button class="btn btn-primary">Update Status</button>
                </form>
            </div>
        <?php endforeach; ?>
        <?php if (!$orders): ?><div class="card">Belum ada pesanan.</div><?php endif; ?>
    </div>
</section>
<?php require_once '../includes/footer.php'; ?>

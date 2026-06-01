<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("UPDATE bookings SET status=?, payment_status=? WHERE id=?");
    $stmt->execute([$_POST['status'], $_POST['payment_status'], $_POST['id']]);
    redirect('bookings.php?msg=saved');
}

$message = isset($_GET['msg']) ? 'Status booking berhasil diperbarui.' : '';
$bookings = $pdo->query("SELECT b.*, u.name, u.phone, t.table_name, t.table_type FROM bookings b JOIN users u ON u.id=b.user_id JOIN billiard_tables t ON t.id=b.table_id ORDER BY b.booking_date DESC, b.start_time DESC")->fetchAll();
$pageTitle = 'Kelola Booking';
require_once '../includes/header.php';
?>
<section class="container">
    <div class="section-title"><div><h2>Kelola Booking</h2><p>Konfirmasi booking dan update pembayaran.</p></div></div>
    <?php if ($message): ?><div class="alert alert-success"><?= e($message) ?></div><?php endif; ?>
    <div class="card">
        <div class="table-responsive">
            <table class="data-table">
                <thead><tr><th>Kode</th><th>User</th><th>Meja</th><th>Tanggal</th><th>Jam</th><th>Total</th><th>Status</th><th>Bayar</th><th>Aksi</th></tr></thead>
                <tbody>
                <?php foreach ($bookings as $b): ?>
                    <tr>
                        <td><?= e($b['booking_code']) ?></td>
                        <td><?= e($b['name']) ?><br><span class="small muted"><?= e($b['phone']) ?></span></td>
                        <td><?= e($b['table_name']) ?><br><span class="small muted"><?= e($b['table_type']) ?></span></td>
                        <td><?= e($b['booking_date']) ?></td>
                        <td><?= e(substr($b['start_time'],0,5)) ?> - <?= e(substr($b['end_time'],0,5)) ?></td>
                        <td><?= rupiah($b['total_price']) ?></td>
                        <td><span class="badge badge-<?= badgeClass($b['status']) ?>"><?= e($b['status']) ?></span></td>
                        <td><span class="badge badge-<?= badgeClass($b['payment_status']) ?>"><?= e($b['payment_status']) ?></span></td>
                        <td>
                            <form method="post" class="actions">
                                <input type="hidden" name="id" value="<?= $b['id'] ?>">
                                <select name="status" class="form-control" style="width:130px"><option <?= $b['status']=='pending'?'selected':'' ?>>pending</option><option <?= $b['status']=='confirmed'?'selected':'' ?>>confirmed</option><option <?= $b['status']=='completed'?'selected':'' ?>>completed</option><option <?= $b['status']=='cancelled'?'selected':'' ?>>cancelled</option></select>
                                <select name="payment_status" class="form-control" style="width:105px"><option <?= $b['payment_status']=='unpaid'?'selected':'' ?>>unpaid</option><option <?= $b['payment_status']=='paid'?'selected':'' ?>>paid</option></select>
                                <button class="btn btn-sm btn-primary">Update</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$bookings): ?><tr><td colspan="9">Belum ada booking.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>
<?php require_once '../includes/footer.php'; ?>

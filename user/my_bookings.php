<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
requireLogin();
if (isAdmin()) redirect('../admin/dashboard.php');

if (isset($_GET['cancel'])) {
    $stmt = $pdo->prepare("UPDATE bookings SET status='cancelled' WHERE id=? AND user_id=? AND status='pending'");
    $stmt->execute([$_GET['cancel'], $_SESSION['user_id']]);
    redirect('my_bookings.php?msg=cancelled');
}
$message = isset($_GET['msg']) ? 'Booking berhasil dibatalkan.' : '';
$stmt = $pdo->prepare("SELECT b.*, t.table_name, t.table_type FROM bookings b JOIN billiard_tables t ON t.id=b.table_id WHERE b.user_id=? ORDER BY b.booking_date DESC, b.start_time DESC");
$stmt->execute([$_SESSION['user_id']]);
$bookings = $stmt->fetchAll();
$pageTitle = 'Booking Saya';
require_once '../includes/header.php';
?>
<section class="container">
    <div class="section-title"><div><h2>Booking Saya</h2><p>Riwayat booking meja billiard.</p></div><a class="btn btn-primary" href="booking.php">Booking Baru</a></div>
    <?php if ($message): ?><div class="alert alert-success"><?= e($message) ?></div><?php endif; ?>
    <div class="card table-responsive">
        <table class="data-table">
            <thead><tr><th>Kode</th><th>Meja</th><th>Tanggal</th><th>Jam</th><th>Durasi</th><th>Total</th><th>Status</th><th>Bayar</th><th>Aksi</th></tr></thead>
            <tbody>
            <?php foreach ($bookings as $b): ?>
                <tr>
                    <td><?= e($b['booking_code']) ?></td><td><?= e($b['table_name']) ?><br><span class="small muted"><?= e($b['table_type']) ?></span></td><td><?= e($b['booking_date']) ?></td><td><?= e(substr($b['start_time'],0,5)) ?> - <?= e(substr($b['end_time'],0,5)) ?></td><td><?= e($b['duration_hours']) ?> jam</td><td><?= rupiah($b['total_price']) ?></td><td><span class="badge badge-<?= badgeClass($b['status']) ?>"><?= e($b['status']) ?></span></td><td><span class="badge badge-<?= badgeClass($b['payment_status']) ?>"><?= e($b['payment_status']) ?></span></td><td><?php if ($b['status'] === 'pending'): ?><a onclick="return confirmDelete('Batalkan booking ini?')" class="btn btn-sm btn-danger" href="?cancel=<?= $b['id'] ?>">Batal</a><?php else: ?>-<?php endif; ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$bookings): ?><tr><td colspan="9">Belum ada booking.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</section>
<?php require_once '../includes/footer.php'; ?>

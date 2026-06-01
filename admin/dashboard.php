<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
requireAdmin();
$pageTitle = 'Dashboard Admin';
require_once '../includes/header.php';

$totalUsers = getCount($pdo, 'users', "role='user'");
$totalTables = getCount($pdo, 'billiard_tables');
$totalMenus = getCount($pdo, 'menus');
$totalBookings = getCount($pdo, 'bookings');
$latestBookings = $pdo->query("SELECT b.*, u.name, t.table_name FROM bookings b JOIN users u ON u.id=b.user_id JOIN billiard_tables t ON t.id=b.table_id ORDER BY b.created_at DESC LIMIT 5")->fetchAll();
?>
<section class="container">
    <div class="section-title">
        <div>
            <h2>Dashboard Admin</h2>
            <p>Selamat datang, <?= e($_SESSION['name']) ?>.</p>
        </div>
    </div>
    <div class="stats">
        <div class="stat-card"><b><?= $totalUsers ?></b><span>User</span></div>
        <div class="stat-card"><b><?= $totalTables ?></b><span>Meja</span></div>
        <div class="stat-card"><b><?= $totalMenus ?></b><span>Menu Cafe</span></div>
        <div class="stat-card"><b><?= $totalBookings ?></b><span>Booking</span></div>
    </div>
    <div class="card">
        <div class="section-title">
            <h2>Booking Terbaru</h2>
            <a class="btn btn-outline" href="bookings.php">Lihat Semua</a>
        </div>
        <div class="table-responsive">
            <table class="data-table">
                <thead><tr><th>Kode</th><th>User</th><th>Meja</th><th>Tanggal</th><th>Jam</th><th>Status</th></tr></thead>
                <tbody>
                    <?php foreach ($latestBookings as $b): ?>
                        <tr>
                            <td><?= e($b['booking_code']) ?></td>
                            <td><?= e($b['name']) ?></td>
                            <td><?= e($b['table_name']) ?></td>
                            <td><?= e($b['booking_date']) ?></td>
                            <td><?= e(substr($b['start_time'],0,5)) ?> - <?= e(substr($b['end_time'],0,5)) ?></td>
                            <td><span class="badge badge-<?= badgeClass($b['status']) ?>"><?= e($b['status']) ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$latestBookings): ?><tr><td colspan="6">Belum ada booking.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>
<?php require_once '../includes/footer.php'; ?>

<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
requireLogin();
if (isAdmin()) redirect('../admin/dashboard.php');

$error = '';
$success = '';

$tables = $pdo->query("SELECT * FROM billiard_tables WHERE status='available' ORDER BY FIELD(table_type,'Standard','VIP','Premium'), table_name")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tableId = (int)$_POST['table_id'];
    $date = $_POST['booking_date'];
    $start = $_POST['start_time'];
    $duration = max(1, (int)$_POST['duration_hours']);
    $notes = trim($_POST['notes'] ?? '');

    $startDT = new DateTime($date . ' ' . $start);
    $endDT = clone $startDT;
    $endDT->modify('+' . $duration . ' hour');
    $end = $endDT->format('H:i:s');
    $startSql = $startDT->format('H:i:s');

    $stmt = $pdo->prepare("SELECT * FROM billiard_tables WHERE id=? AND status='available'");
    $stmt->execute([$tableId]);
    $table = $stmt->fetch();

    if (!$table) {
        $error = 'Meja tidak tersedia.';
    } elseif (checkBookingConflict($pdo, $tableId, $date, $startSql, $end)) {
        $error = 'Jadwal tersebut sudah dibooking. Pilih jam lain.';
    } else {
        $total = $duration * (float)$table['price_per_hour'];
        $code = generateCode('BK');
        $stmt = $pdo->prepare("INSERT INTO bookings (booking_code, user_id, table_id, booking_date, start_time, end_time, duration_hours, total_price, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$code, $_SESSION['user_id'], $tableId, $date, $startSql, $end, $duration, $total, $notes]);
        $success = 'Booking berhasil dibuat dengan kode ' . $code . '. Tunggu konfirmasi admin.';
    }
}

$pageTitle = 'Booking Meja';
require_once '../includes/header.php';
?>
<section class="container">
    <div class="section-title"><div><h2>Booking Meja Billiard</h2><p>Pilih meja, tanggal, dan jam bermain.</p></div></div>
    <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
    <?php if ($success): ?><div class="alert alert-success"><?= e($success) ?></div><?php endif; ?>
    <div class="grid grid-2">
        <div class="card">
            <h3>Form Booking</h3>
            <form method="post">
                <div class="form-group"><label>Pilih Meja</label><select name="table_id" class="form-control" required><?php foreach ($tables as $t): ?><option value="<?= $t['id'] ?>"><?= e($t['table_name']) ?> - <?= e($t['table_type']) ?> - <?= rupiah($t['price_per_hour']) ?>/jam</option><?php endforeach; ?></select></div>
                <div class="form-row">
                    <div class="form-group"><label>Tanggal</label><input class="form-control" type="date" name="booking_date" min="<?= date('Y-m-d') ?>" required></div>
                    <div class="form-group"><label>Jam Mulai</label><input class="form-control" type="time" name="start_time" required></div>
                </div>
                <div class="form-group"><label>Durasi Jam</label><input class="form-control" type="number" name="duration_hours" min="1" max="8" value="1" required></div>
                <div class="form-group"><label>Catatan</label><textarea class="form-control" name="notes" placeholder="Contoh: datang 4 orang"></textarea></div>
                <button class="btn btn-primary">Booking Sekarang</button>
            </form>
        </div>
        <div class="grid">
            <?php foreach ($tables as $t): ?>
                <div class="card">
                    <h3><?= e($t['table_name']) ?></h3>
                    <span class="badge badge-success"><?= e($t['table_type']) ?></span>
                    <p><?= e($t['description']) ?></p>
                    <div class="divider"></div>
                    <p class="price"><?= rupiah($t['price_per_hour']) ?>/jam</p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php require_once '../includes/footer.php'; ?>

<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
requireLogin();
if (isAdmin()) redirect('../admin/dashboard.php');

$error = '';
$success = '';

$stmt = $pdo->prepare("SELECT id, booking_code FROM bookings WHERE user_id=? AND status IN ('pending','confirmed') ORDER BY booking_date DESC");
$stmt->execute([$_SESSION['user_id']]);
$myBookings = $stmt->fetchAll();

$menus = $pdo->query("SELECT m.*, c.category_name FROM menus m LEFT JOIN menu_categories c ON c.id=m.category_id WHERE m.status='available' ORDER BY c.category_name, m.menu_name")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $quantities = $_POST['qty'] ?? [];
    $bookingId = $_POST['booking_id'] ?: null;
    $notes = trim($_POST['notes'] ?? '');
    $items = [];
    $total = 0;

    foreach ($menus as $menu) {
        $qty = isset($quantities[$menu['id']]) ? (int)$quantities[$menu['id']] : 0;
        if ($qty > 0) {
            $subtotal = $qty * (float)$menu['price'];
            $items[] = ['id' => $menu['id'], 'qty' => $qty, 'price' => $menu['price'], 'subtotal' => $subtotal];
            $total += $subtotal;
        }
    }

    if (!$items) {
        $error = 'Pilih minimal satu menu.';
    } else {
        $pdo->beginTransaction();
        try {
            $code = generateCode('OD');
            $stmt = $pdo->prepare("INSERT INTO orders (order_code, user_id, booking_id, total_price, notes) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$code, $_SESSION['user_id'], $bookingId, $total, $notes]);
            $orderId = $pdo->lastInsertId();
            $itemStmt = $pdo->prepare("INSERT INTO order_items (order_id, menu_id, quantity, price, subtotal) VALUES (?, ?, ?, ?, ?)");
            foreach ($items as $it) {
                $itemStmt->execute([$orderId, $it['id'], $it['qty'], $it['price'], $it['subtotal']]);
            }
            $pdo->commit();
            $success = 'Pesanan berhasil dibuat dengan kode ' . $code . '.';
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = 'Gagal membuat pesanan.';
        }
    }
}

$pageTitle = 'Menu Cafe';
require_once '../includes/header.php';
?>
<section class="container">
    <div class="section-title"><div><h2>Menu Cafe</h2><p>Pilih makanan atau minuman, lalu buat pesanan.</p></div></div>
    <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
    <?php if ($success): ?><div class="alert alert-success"><?= e($success) ?></div><?php endif; ?>
    <form method="post">
        <div class="card" style="margin-bottom:22px">
            <div class="form-row">
                <div class="form-group">
                    <label>Hubungkan dengan Booking</label>
                    <select name="booking_id" class="form-control">
                        <option value="">Tanpa booking</option>
                        <?php foreach ($myBookings as $b): ?><option value="<?= $b['id'] ?>"><?= e($b['booking_code']) ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group"><label>Catatan Pesanan</label><input class="form-control" name="notes" placeholder="Contoh: tidak pedas / antar ke meja"></div>
            </div>
        </div>
        <div class="grid grid-3">
            <?php foreach ($menus as $menu): ?>
                <div class="card menu-card">
                    <div class="menu-img">
                        <?php if ($menu['image']): ?><img src="../assets/img/uploads/<?= e($menu['image']) ?>" alt="<?= e($menu['menu_name']) ?>"><?php else: ?>🍽️<?php endif; ?>
                    </div>
                    <div class="menu-body">
                        <span class="badge badge-success"><?= e($menu['category_name'] ?? 'Menu') ?></span>
                        <h3><?= e($menu['menu_name']) ?></h3>
                        <p><?= e($menu['description']) ?></p>
                        <p class="price"><?= rupiah($menu['price']) ?></p>
                        <div class="qty-box"><label>Jumlah</label><input class="form-control" type="number" name="qty[<?= $menu['id'] ?>]" min="0" value="0"></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="card" style="margin-top:22px"><button class="btn btn-primary">Buat Pesanan</button></div>
    </form>
</section>
<?php require_once '../includes/footer.php'; ?>

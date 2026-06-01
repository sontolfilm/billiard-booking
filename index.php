<?php
require_once 'config/database.php';
require_once 'includes/functions.php';
$pageTitle = 'Home';
require_once 'includes/header.php';

$tables = $pdo->query("SELECT * FROM billiard_tables WHERE status='available' ORDER BY FIELD(table_type,'Standard','VIP','Premium'), table_name LIMIT 3")->fetchAll();
$menus = $pdo->query("SELECT m.*, c.category_name FROM menus m LEFT JOIN menu_categories c ON c.id=m.category_id WHERE m.status='available' ORDER BY m.created_at DESC LIMIT 3")->fetchAll();
?>
<section class="container hero">
    <div>
        <h1>Booking Billiard & Nikmati Menu <span>Cafe</span></h1>
        <p>Biliiard And Cafe menghadirkan kemudahan booking meja billiard dan pemesanan makanan di menu cafe secara online. Lebih cepat, lebih praktis, dan cocok untuk tempat santai bersama teman anda.</p>
        <div class="actions">
            <a href="login.php" class="btn btn-primary">Login Sekarang</a>
            <a href="register.php" class="btn btn-outline">Daftar User</a>
        </div>
    </div>
    <div class="hero-card hero-visual">
        <div class="pool-table">
            <span class="ball b1"></span><span class="ball b2"></span><span class="ball b3"></span><span class="ball b4"></span>
        </div>
        <div class="cafe-strip">
            <div class="cafe-item">☕ Kopi</div>
            <div class="cafe-item">🍛 Makanan</div>
            <div class="cafe-item">🥤 Minuman</div>
        </div>
    </div>
</section>

<section class="container">
    <div class="section-title">
        <div>
            <h2>Meja Tersedia</h2>
            <p>Pilih meja sesuai kebutuhan pelanggan.</p>
        </div>
    </div>
    <div class="grid grid-3">
        <?php foreach ($tables as $table): ?>
            <div class="card">
                <h3><?= e($table['table_name']) ?></h3>
                <span class="badge badge-success"><?= e($table['table_type']) ?></span>
                <p><?= e($table['description']) ?></p>
                <div class="divider"></div>
                <p class="price"><?= rupiah($table['price_per_hour']) ?>/jam</p>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<section class="container">
    <div class="section-title">
        <div>
            <h2>Menu Cafe Favorit</h2>
            <p>Admin dapat menambahkan menu lengkap dengan gambar.</p>
        </div>
    </div>
    <div class="grid grid-3">
        <?php foreach ($menus as $menu): ?>
            <div class="card menu-card">
                <div class="menu-img">
                    <?php if ($menu['image']): ?>
                        <img src="assets/img/uploads/<?= e($menu['image']) ?>" alt="<?= e($menu['menu_name']) ?>">
                    <?php else: ?>
                        🍽️
                    <?php endif; ?>
                </div>
                <div class="menu-body">
                    <span class="badge badge-success"><?= e($menu['category_name'] ?? 'Menu') ?></span>
                    <h3><?= e($menu['menu_name']) ?></h3>
                    <p><?= e($menu['description']) ?></p>
                    <p class="price"><?= rupiah($menu['price']) ?></p>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>
<?php require_once 'includes/footer.php'; ?>

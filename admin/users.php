<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
requireAdmin();
$users = $pdo->query("SELECT * FROM users ORDER BY created_at DESC")->fetchAll();
$pageTitle = 'Data User';
require_once '../includes/header.php';
?>
<section class="container">
    <div class="section-title"><div><h2>Data User</h2><p>Daftar akun admin dan pelanggan.</p></div></div>
    <div class="card table-responsive">
        <table class="data-table">
            <thead><tr><th>Nama</th><th>Username</th><th>Email</th><th>HP</th><th>Role</th><th>Status</th></tr></thead>
            <tbody><?php foreach ($users as $u): ?><tr><td><?= e($u['name']) ?></td><td><?= e($u['username']) ?></td><td><?= e($u['email']) ?></td><td><?= e($u['phone']) ?></td><td><?= e($u['role']) ?></td><td><span class="badge badge-<?= badgeClass($u['status']) ?>"><?= e($u['status']) ?></span></td></tr><?php endforeach; ?></tbody>
        </table>
    </div>
</section>
<?php require_once '../includes/footer.php'; ?>

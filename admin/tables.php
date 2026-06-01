<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
requireAdmin();

$message = '';
$error = '';
$editData = null;

if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM billiard_tables WHERE id=?");
    $stmt->execute([$_GET['edit']]);
    $editData = $stmt->fetch();
}

if (isset($_GET['delete'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM billiard_tables WHERE id=?");
        $stmt->execute([$_GET['delete']]);
        redirect('tables.php?msg=deleted');
    } catch (PDOException $e) {
        $error = 'Meja tidak bisa dihapus karena sudah punya data booking.';
    }
}

if (isset($_GET['msg'])) {
    $message = 'Data meja berhasil diperbarui.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? '';
    $tableName = trim($_POST['table_name']);
    $type = $_POST['table_type'];
    $price = (float)$_POST['price_per_hour'];
    $status = $_POST['status'];
    $description = trim($_POST['description']);

    if ($id) {
        $stmt = $pdo->prepare("UPDATE billiard_tables SET table_name=?, table_type=?, price_per_hour=?, status=?, description=? WHERE id=?");
        $stmt->execute([$tableName, $type, $price, $status, $description, $id]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO billiard_tables (table_name, table_type, price_per_hour, status, description) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$tableName, $type, $price, $status, $description]);
    }
    redirect('tables.php?msg=saved');
}

$tables = $pdo->query("SELECT * FROM billiard_tables ORDER BY FIELD(table_type,'Standard','VIP','Premium'), table_name")->fetchAll();
$pageTitle = 'Kelola Meja';
require_once '../includes/header.php';
?>
<section class="container">
    <div class="section-title"><div><h2>Kelola Meja Billiard</h2><p>Tambah, edit, dan atur status meja.</p></div></div>
    <?php if ($message): ?><div class="alert alert-success"><?= e($message) ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
    <div class="grid grid-2">
        <div class="card">
            <h3><?= $editData ? 'Edit Meja' : 'Tambah Meja' ?></h3>
            <form method="post">
                <input type="hidden" name="id" value="<?= e($editData['id'] ?? '') ?>">
                <div class="form-group"><label>Nama Meja</label><input class="form-control" name="table_name" value="<?= e($editData['table_name'] ?? '') ?>" required></div>
                <div class="form-row">
                    <div class="form-group"><label>Tipe</label><select class="form-control" name="table_type"><option <?= (($editData['table_type'] ?? '')==='Standard')?'selected':'' ?>>Standard</option><option <?= (($editData['table_type'] ?? '')==='VIP')?'selected':'' ?>>VIP</option><option <?= (($editData['table_type'] ?? '')==='Premium')?'selected':'' ?>>Premium</option></select></div>
                    <div class="form-group"><label>Harga / Jam</label><input class="form-control" type="number" name="price_per_hour" value="<?= e($editData['price_per_hour'] ?? '') ?>" required></div>
                </div>
                <div class="form-group"><label>Status</label><select class="form-control" name="status"><option value="available" <?= (($editData['status'] ?? '')==='available')?'selected':'' ?>>available</option><option value="maintenance" <?= (($editData['status'] ?? '')==='maintenance')?'selected':'' ?>>maintenance</option><option value="inactive" <?= (($editData['status'] ?? '')==='inactive')?'selected':'' ?>>inactive</option></select></div>
                <div class="form-group"><label>Deskripsi</label><textarea class="form-control" name="description"><?= e($editData['description'] ?? '') ?></textarea></div>
                <button class="btn btn-primary">Simpan</button>
                <?php if ($editData): ?><a class="btn btn-outline" href="tables.php">Batal</a><?php endif; ?>
            </form>
        </div>
        <div class="card">
            <h3>Daftar Meja</h3>
            <div class="table-responsive">
                <table class="data-table">
                    <thead><tr><th>Nama</th><th>Tipe</th><th>Harga</th><th>Status</th><th>Aksi</th></tr></thead>
                    <tbody>
                    <?php foreach ($tables as $t): ?>
                        <tr>
                            <td><?= e($t['table_name']) ?></td>
                            <td><?= e($t['table_type']) ?></td>
                            <td><?= rupiah($t['price_per_hour']) ?></td>
                            <td><span class="badge badge-<?= badgeClass($t['status']) ?>"><?= e($t['status']) ?></span></td>
                            <td class="actions"><a class="btn btn-sm btn-outline" href="?edit=<?= $t['id'] ?>">Edit</a><a class="btn btn-sm btn-danger" onclick="return confirmDelete()" href="?delete=<?= $t['id'] ?>">Hapus</a></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>
<?php require_once '../includes/footer.php'; ?>

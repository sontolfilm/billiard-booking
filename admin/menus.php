<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
requireAdmin();

$message = '';
$error = '';
$editData = null;

if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM menus WHERE id=?");
    $stmt->execute([$_GET['edit']]);
    $editData = $stmt->fetch();
}

if (isset($_GET['delete'])) {
    try {
        $stmt = $pdo->prepare("SELECT image FROM menus WHERE id=?");
        $stmt->execute([$_GET['delete']]);
        $old = $stmt->fetch();
        if ($old && $old['image']) {
            $path = __DIR__ . '/../assets/img/uploads/' . $old['image'];
            if (is_file($path)) unlink($path);
        }
        $stmt = $pdo->prepare("DELETE FROM menus WHERE id=?");
        $stmt->execute([$_GET['delete']]);
        redirect('menus.php?msg=deleted');
    } catch (PDOException $e) {
        $error = 'Menu tidak bisa dihapus karena sudah pernah dipesan.';
    }
}

if (isset($_GET['msg'])) $message = 'Data menu berhasil diperbarui.';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $id = $_POST['id'] ?? '';
        $categoryId = $_POST['category_id'] ?: null;
        $menuName = trim($_POST['menu_name']);
        $description = trim($_POST['description']);
        $price = (float)$_POST['price'];
        $status = $_POST['status'];
        $oldImage = $_POST['old_image'] ?? null;
        $image = uploadImage($_FILES['image'] ?? null, $oldImage);

        if ($id) {
            $stmt = $pdo->prepare("UPDATE menus SET category_id=?, menu_name=?, description=?, price=?, image=?, status=? WHERE id=?");
            $stmt->execute([$categoryId, $menuName, $description, $price, $image, $status, $id]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO menus (category_id, menu_name, description, price, image, status) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$categoryId, $menuName, $description, $price, $image, $status]);
        }
        redirect('menus.php?msg=saved');
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

$categories = $pdo->query("SELECT * FROM menu_categories ORDER BY category_name")->fetchAll();
$menus = $pdo->query("SELECT m.*, c.category_name FROM menus m LEFT JOIN menu_categories c ON c.id=m.category_id ORDER BY m.created_at DESC")->fetchAll();
$pageTitle = 'Kelola Menu';
require_once '../includes/header.php';
?>
<section class="container">
    <div class="section-title"><div><h2>Kelola Menu Cafe</h2><p>Admin bisa menambahkan makanan/minuman lengkap dengan gambar.</p></div></div>
    <?php if ($message): ?><div class="alert alert-success"><?= e($message) ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
    <div class="grid grid-2">
        <div class="card">
            <h3><?= $editData ? 'Edit Menu' : 'Tambah Menu' ?></h3>
            <form method="post" enctype="multipart/form-data">
                <input type="hidden" name="id" value="<?= e($editData['id'] ?? '') ?>">
                <input type="hidden" name="old_image" value="<?= e($editData['image'] ?? '') ?>">
                <div class="form-group"><label>Nama Menu</label><input class="form-control" name="menu_name" value="<?= e($editData['menu_name'] ?? '') ?>" required></div>
                <div class="form-row">
                    <div class="form-group"><label>Kategori</label><select class="form-control" name="category_id"><option value="">Pilih Kategori</option><?php foreach ($categories as $c): ?><option value="<?= $c['id'] ?>" <?= (($editData['category_id'] ?? '')==$c['id'])?'selected':'' ?>><?= e($c['category_name']) ?></option><?php endforeach; ?></select></div>
                    <div class="form-group"><label>Harga</label><input class="form-control" type="number" name="price" value="<?= e($editData['price'] ?? '') ?>" required></div>
                </div>
                <div class="form-group"><label>Deskripsi</label><textarea class="form-control" name="description"><?= e($editData['description'] ?? '') ?></textarea></div>
                <div class="form-row">
                    <div class="form-group"><label>Status</label><select class="form-control" name="status"><option value="available" <?= (($editData['status'] ?? '')==='available')?'selected':'' ?>>available</option><option value="sold_out" <?= (($editData['status'] ?? '')==='sold_out')?'selected':'' ?>>sold_out</option><option value="inactive" <?= (($editData['status'] ?? '')==='inactive')?'selected':'' ?>>inactive</option></select></div>
                    <div class="form-group"><label>Gambar Menu</label><input class="form-control" type="file" name="image" accept="image/*" onchange="previewImage(this,'preview')"></div>
                </div>
                <?php if (!empty($editData['image'])): ?><img id="preview" src="../assets/img/uploads/<?= e($editData['image']) ?>" style="max-width:140px;border-radius:14px;margin-bottom:14px;display:block"><?php else: ?><img id="preview" style="max-width:140px;border-radius:14px;margin-bottom:14px;display:none"><?php endif; ?>
                <button class="btn btn-primary">Simpan Menu</button>
                <?php if ($editData): ?><a class="btn btn-outline" href="menus.php">Batal</a><?php endif; ?>
            </form>
        </div>
        <div class="card">
            <h3>Daftar Menu</h3>
            <div class="table-responsive">
                <table class="data-table">
                    <thead><tr><th>Gambar</th><th>Menu</th><th>Kategori</th><th>Harga</th><th>Status</th><th>Aksi</th></tr></thead>
                    <tbody>
                    <?php foreach ($menus as $m): ?>
                        <tr>
                            <td><?php if ($m['image']): ?><img src="../assets/img/uploads/<?= e($m['image']) ?>" style="width:60px;height:50px;object-fit:cover;border-radius:10px"><?php else: ?>🍽️<?php endif; ?></td>
                            <td><?= e($m['menu_name']) ?></td>
                            <td><?= e($m['category_name'] ?? '-') ?></td>
                            <td><?= rupiah($m['price']) ?></td>
                            <td><span class="badge badge-<?= badgeClass($m['status']) ?>"><?= e($m['status']) ?></span></td>
                            <td class="actions"><a class="btn btn-sm btn-outline" href="?edit=<?= $m['id'] ?>">Edit</a><a class="btn btn-sm btn-danger" onclick="return confirmDelete()" href="?delete=<?= $m['id'] ?>">Hapus</a></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>
<?php require_once '../includes/footer.php'; ?>

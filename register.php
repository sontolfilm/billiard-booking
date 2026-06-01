<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

if (isLoggedIn()) {
    redirect(isAdmin() ? 'admin/dashboard.php' : 'user/dashboard.php');
}

$error = '';
$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if ($password !== $confirm) {
        $error = 'Konfirmasi password tidak sama.';
    } elseif (strlen($password) < 6) {
        $error = 'Password minimal 6 karakter.';
    } else {
        try {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (name, username, email, phone, password, role) VALUES (?, ?, ?, ?, ?, 'user')");
            $stmt->execute([$name, $username, $email, $phone, $hash]);
            $success = 'Registrasi berhasil. Silakan login.';
        } catch (PDOException $e) {
            $error = 'Username atau email sudah digunakan.';
        }
    }
}

$pageTitle = 'Daftar';
require_once 'includes/header.php';
?>
<div class="form-wrap">
    <h2>Daftar User</h2>
    <p class="subtitle">Buat akun pelanggan baru</p>
    <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
    <?php if ($success): ?><div class="alert alert-success"><?= e($success) ?></div><?php endif; ?>
    <form method="post">
        <div class="form-group">
            <label>Nama Lengkap</label>
            <input type="text" name="name" class="form-control" required>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" class="form-control" required>
            </div>
            <div class="form-group">
                <label>No. HP</label>
                <input type="text" name="phone" class="form-control">
            </div>
        </div>
        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" class="form-control" required>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Konfirmasi Password</label>
                <input type="password" name="confirm_password" class="form-control" required>
            </div>
        </div>
        <button class="btn btn-primary" style="width:100%">Daftar</button>
    </form>
</div>
<?php require_once 'includes/footer.php'; ?>

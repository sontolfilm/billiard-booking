<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

if (isLoggedIn()) {
    redirect(isAdmin() ? 'admin/dashboard.php' : 'user/dashboard.php');
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['login'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM users WHERE (username = ? OR email = ?) AND status='active' LIMIT 1");
    $stmt->execute([$login, $login]);
    $user = $stmt->fetch();

    $valid = false;
    if ($user) {
        if (password_get_info($user['password'])['algo']) {
            $valid = password_verify($password, $user['password']);
        } else {
            $valid = hash_equals($user['password'], hash('sha256', $password));
            if ($valid) {
                $newHash = password_hash($password, PASSWORD_DEFAULT);
                $update = $pdo->prepare("UPDATE users SET password=? WHERE id=?");
                $update->execute([$newHash, $user['id']]);
            }
        }
    }

    if ($valid) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['name'] = $user['name'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        redirect($user['role'] === 'admin' ? 'admin/dashboard.php' : 'user/dashboard.php');
    } else {
        $error = 'Username/email atau password salah.';
    }
}

$pageTitle = 'Login';
require_once 'includes/header.php';
?>
<div class="form-wrap">
    <h2>Login</h2>
    <p class="subtitle">Masuk ke Biliiard And Cafe</p>
    <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
    <form method="post">
        <div class="form-group">
            <label>Username / Email</label>
            <input type="text" name="login" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <button class="btn btn-primary" style="width:100%">Login</button>
    </form>
    <div class="divider"></div>
    <p class="small" style="text-align:center;margin-top:12px">Belum punya akun? <a href="register.php" style="color:var(--green);font-weight:800">Daftar</a></p>
</div>
<?php require_once 'includes/footer.php'; ?>

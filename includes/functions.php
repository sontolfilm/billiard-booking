<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function redirect($url) {
    header('Location: ' . $url);
    exit;
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function requireLogin() {
    if (!isLoggedIn()) {
        redirect('../login.php');
    }
}

function requireAdmin() {
    if (!isLoggedIn() || !isAdmin()) {
        redirect('../login.php');
    }
}

function rupiah($number) {
    return 'Rp ' . number_format((float)$number, 0, ',', '.');
}

function badgeClass($status) {
    $map = [
        'available' => 'success',
        'active' => 'success',
        'paid' => 'success',
        'confirmed' => 'success',
        'completed' => 'success',
        'ready' => 'success',
        'pending' => 'warning',
        'process' => 'info',
        'unpaid' => 'danger',
        'sold_out' => 'danger',
        'cancelled' => 'danger',
        'maintenance' => 'warning',
        'inactive' => 'dark'
    ];
    return $map[$status] ?? 'dark';
}

function generateCode($prefix) {
    return $prefix . date('ymdHis') . rand(10, 99);
}

function getCount($pdo, $table, $where = '1=1') {
    $stmt = $pdo->query("SELECT COUNT(*) AS total FROM $table WHERE $where");
    return (int)$stmt->fetch()['total'];
}

function uploadImage($file, $oldImage = null) {
    if (!isset($file) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return $oldImage;
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('Upload gambar gagal.');
    }

    $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    $mime = mime_content_type($file['tmp_name']);

    if (!isset($allowed[$mime])) {
        throw new Exception('Format gambar harus JPG, PNG, atau WEBP.');
    }

    if ($file['size'] > 2 * 1024 * 1024) {
        throw new Exception('Ukuran gambar maksimal 2 MB.');
    }

    $filename = 'menu_' . time() . '_' . rand(1000, 9999) . '.' . $allowed[$mime];
    $destination = __DIR__ . '/../assets/img/uploads/' . $filename;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        throw new Exception('Gagal menyimpan gambar.');
    }

    if ($oldImage) {
        $oldPath = __DIR__ . '/../assets/img/uploads/' . $oldImage;
        if (is_file($oldPath)) {
            unlink($oldPath);
        }
    }

    return $filename;
}

function checkBookingConflict($pdo, $tableId, $date, $startTime, $endTime, $ignoreBookingId = null) {
    $sql = "SELECT COUNT(*) AS total FROM bookings
            WHERE table_id = ?
            AND booking_date = ?
            AND status IN ('pending','confirmed')
            AND (? < end_time AND ? > start_time)";
    $params = [$tableId, $date, $startTime, $endTime];

    if ($ignoreBookingId) {
        $sql .= " AND id != ?";
        $params[] = $ignoreBookingId;
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return (int)$stmt->fetch()['total'] > 0;
}

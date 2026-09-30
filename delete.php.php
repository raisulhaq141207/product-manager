<?php
require __DIR__ . '/includes/bootstrap.php';

// Hapus hanya boleh lewat POST (bukan link GET)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('index.php');
}

if (!csrf_verify($_POST['csrf_token'] ?? null)) {
    flash_set('error', 'Permintaan tidak valid (token CSRF salah).');
    redirect('index.php');
}

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if (!$id) {
    flash_set('error', 'ID produk tidak valid.');
    redirect('index.php');
}

$stmt = db()->prepare('DELETE FROM products WHERE id = ?');
$stmt->execute([$id]);

if ($stmt->rowCount() > 0) {
    flash_set('success', 'Produk berhasil dihapus.');
} else {
    flash_set('error', 'Produk tidak ditemukan.');
}

redirect('index.php');

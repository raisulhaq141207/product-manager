<?php
require __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit('Metode tidak diizinkan.');
}

if (!csrf_valid()) {
    http_response_code(403);
    exit('Token CSRF tidak valid.');
}

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if (!$id) {
    flash('ID produk tidak valid.', 'error');
    redirect('index.php');
}

$stmt = $pdo->prepare('DELETE FROM products WHERE id = ?');
$stmt->execute([$id]);

flash($stmt->rowCount() ? 'Produk dihapus.' : 'Produk tidak ditemukan.', $stmt->rowCount() ? 'ok' : 'error');
redirect('index.php');

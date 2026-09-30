<?php
require __DIR__ . '/config.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if (!$id) {
    http_response_code(400);
    exit('ID produk tidak valid.');
}

$stmt = $pdo->prepare('SELECT id, name, category, price, stock FROM products WHERE id = ?');
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    http_response_code(404);
    exit('Produk tidak ditemukan.');
}

$values = $product;
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valid()) {
        http_response_code(403);
        exit('Token CSRF tidak valid. Muat ulang halaman lalu coba lagi.');
    }

    [$values, $errors] = validate_product($pdo, $_POST, $id);

    if (!$errors) {
        try {
            $stmt = $pdo->prepare(
                'UPDATE products SET name = ?, category = ?, price = ?, stock = ? WHERE id = ?'
            );
            $stmt->execute([$values['name'], $values['category'], $values['price'], $values['stock'], $id]);

            flash('Perubahan produk disimpan.');
            redirect('index.php');
        } catch (PDOException $ex) {
            if ($ex->getCode() === '23000') {
                $errors['name'] = 'Nama produk sudah dipakai.';
            } else {
                throw $ex;
            }
        }
    }
}

$action = 'edit.php?id=' . $id;
$submitLabel = 'Simpan perubahan';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit produk</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<main class="wrap narrow">
    <h1>Edit produk</h1>
    <?php require __DIR__ . '/_form.php'; ?>
</main>
</body>
</html>

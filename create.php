<?php
require __DIR__ . '/config.php';

$values = ['name' => '', 'category' => '', 'price' => '', 'stock' => '0'];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valid()) {
        http_response_code(403);
        exit('Token CSRF tidak valid. Muat ulang halaman lalu coba lagi.');
    }

    [$values, $errors] = validate_product($pdo, $_POST);

    if (!$errors) {
        try {
            $stmt = $pdo->prepare(
                'INSERT INTO products (name, category, price, stock) VALUES (?, ?, ?, ?)'
            );
            $stmt->execute([$values['name'], $values['category'], $values['price'], $values['stock']]);

            flash('Produk berhasil ditambahkan.');
            redirect('index.php');            // PRG: refresh tidak mengirim ulang POST
        } catch (PDOException $ex) {
            if ($ex->getCode() === '23000') { // duplikat dari UNIQUE key (race condition)
                $errors['name'] = 'Nama produk sudah dipakai.';
            } else {
                throw $ex;
            }
        }
    }
}

$action = 'create.php';
$submitLabel = 'Simpan produk';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tambah produk</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<main class="wrap narrow">
    <h1>Tambah produk</h1>
    <?php require __DIR__ . '/_form.php'; ?>
</main>
</body>
</html>

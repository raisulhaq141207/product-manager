<?php
require __DIR__ . '/includes/bootstrap.php';

$pdo = db();

// Ambil ID dari URL dan pastikan berupa bilangan bulat positif
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if ($id === null || $id === false) {
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
}
if (!$id) {
    flash_set('error', 'ID produk tidak valid.');
    redirect('index.php');
}

// SELECT by ID
$stmt = $pdo->prepare('SELECT id, name, category, price, stock FROM products WHERE id = ?');
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    flash_set('error', 'Produk tidak ditemukan.');
    redirect('index.php');
}

$errors = [];
$old = [
    'name'     => $product['name'],
    'category' => $product['category'],
    'price'    => (string) (int) $product['price'],
    'stock'    => (string) $product['stock'],
];

// UPDATE
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old = [
        'name'     => trim((string) ($_POST['name'] ?? '')),
        'category' => trim((string) ($_POST['category'] ?? '')),
        'price'    => trim((string) ($_POST['price'] ?? '')),
        'stock'    => trim((string) ($_POST['stock'] ?? '')),
    ];

    if (!csrf_verify($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Sesi formulir tidak valid. Muat ulang halaman lalu coba lagi.';
    } else {
        $result = validate_product($pdo, $_POST, $id);
        $errors = $result['errors'];

        if (!$errors) {
            try {
                $stmt = $pdo->prepare(
                    'UPDATE products SET name = ?, category = ?, price = ?, stock = ? WHERE id = ?'
                );
                $stmt->execute([
                    $result['data']['name'],
                    $result['data']['category'],
                    $result['data']['price'],
                    $result['data']['stock'],
                    $id,
                ]);
                flash_set('success', 'Produk berhasil diperbarui.');
                redirect('index.php');
            } catch (PDOException $ex) {
                if ($ex->getCode() === '23000') {
                    $errors[] = 'Nama produk sudah dipakai, gunakan nama lain.';
                } else {
                    throw $ex;
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Produk - Product Manager</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<div class="container narrow">
    <header class="page-header">
        <h1>Edit Produk</h1>
        <p>Mengubah: <strong><?= htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8') ?></strong></p>
    </header>

    <section class="panel">
        <?php if ($errors): ?>
            <div class="alert alert-error">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= e($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="post" action="edit.php?id=<?= (int) $id ?>" class="form-grid" novalidate>
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= (int) $id ?>">

            <label>Nama
                <input type="text" name="name" value="<?= e($old['name']) ?>" maxlength="100" required>
            </label>

            <label>Kategori
                <select name="category" required>
                    <option value="">-- Pilih --</option>
                    <?php foreach (CATEGORIES as $cat): ?>
                        <option value="<?= e($cat) ?>" <?= $old['category'] === $cat ? 'selected' : '' ?>>
                            <?= e($cat) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label>Harga (Rp)
                <input type="number" name="price" value="<?= e($old['price']) ?>" min="1" step="any" required>
            </label>

            <label>Stok
                <input type="number" name="stock" value="<?= e($old['stock']) ?>" min="0" step="1" required>
            </label>

            <div class="actions">
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                <a class="btn btn-secondary" href="index.php">Batal</a>
            </div>
        </form>
    </section>
</div>
</body>
</html>

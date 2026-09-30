<?php
require __DIR__ . '/includes/bootstrap.php';

$pdo    = db();
$errors = [];
$old    = ['name' => '', 'category' => '', 'price' => '', 'stock' => '0'];

// CREATE
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
        $result = validate_product($pdo, $_POST);
        $errors = $result['errors'];

        if (!$errors) {
            try {
                $stmt = $pdo->prepare(
                    'INSERT INTO products (name, category, price, stock) VALUES (?, ?, ?, ?)'
                );
                $stmt->execute([
                    $result['data']['name'],
                    $result['data']['category'],
                    $result['data']['price'],
                    $result['data']['stock'],
                ]);
                flash_set('success', 'Produk berhasil ditambahkan.');
                redirect('index.php'); // Post-Redirect-Get: refresh tidak membuat data ganda
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

// READ
$stmt = $pdo->prepare('SELECT id, name, category, price, stock FROM products ORDER BY id DESC');
$stmt->execute();
$products = $stmt->fetchAll();

$flash = flash_get();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Product Manager - Showroom Mobil</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<div class="container">
    <header class="page-header">
        <h1>Product Manager</h1>
        <p>Kelola stok produk showroom mobil.</p>
    </header>

    <?php if ($flash): ?>
        <div class="alert alert-<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
    <?php endif; ?>

    <section class="panel">
        <h2>Tambah Produk</h2>

        <?php if ($errors): ?>
            <div class="alert alert-error">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= e($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="post" action="index.php" class="form-grid" novalidate>
            <?= csrf_field() ?>

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

            <button type="submit" class="btn btn-primary">Simpan</button>
        </form>
    </section>

    <section>
        <h2>Daftar Produk (<?= count($products) ?>)</h2>

        <?php if (!$products): ?>
            <p class="empty">Belum ada produk. Tambahkan produk pertama di atas.</p>
        <?php else: ?>
            <div class="cards">
                <?php foreach ($products as $product): ?>
                    <article class="card">
                        <span class="badge"><?= htmlspecialchars($product['category'], ENT_QUOTES, 'UTF-8') ?></span>
                        <h3><?= htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8') ?></h3>
                        <p class="price"><?= e(rupiah($product['price'])) ?></p>
                        <p class="stock">Stok: <strong><?= (int) $product['stock'] ?></strong></p>

                        <div class="actions">
                            <a class="btn btn-secondary" href="edit.php?id=<?= (int) $product['id'] ?>">Edit</a>

                            <form method="post" action="delete.php"
                                  onsubmit="return confirm('Yakin ingin menghapus produk ini?');">
                                <?= csrf_field() ?>
                                <input type="hidden" name="id" value="<?= (int) $product['id'] ?>">
                                <button type="submit" class="btn btn-danger">Hapus</button>
                            </form>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</div>
</body>
</html>

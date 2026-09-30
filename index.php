<?php
require __DIR__ . '/config.php';

$stmt = $pdo->prepare('SELECT id, name, category, price, stock FROM products ORDER BY id DESC');
$stmt->execute();
$products = $stmt->fetchAll();
$flash = pull_flash();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Product Manager</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<main class="wrap">
    <header class="top">
        <h1>Produk</h1>
        <a href="create.php" class="btn primary">Tambah produk</a>
    </header>

    <?php if ($flash): ?>
        <p class="flash <?= e($flash['type']) ?>" role="status"><?= e($flash['message']) ?></p>
    <?php endif; ?>

    <?php if (!$products): ?>
        <p class="empty">Belum ada produk. Tambahkan produk pertama kamu.</p>
    <?php else: ?>
        <section class="grid">
            <?php foreach ($products as $product): ?>
                <article class="card">
                    <p class="cat"><?= e($product['category']) ?></p>
                    <h2><?= htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8') ?></h2>
                    <p class="price">Rp <?= e(number_format((float)$product['price'], 0, ',', '.')) ?></p>
                    <p class="stock <?= (int)$product['stock'] === 0 ? 'out' : '' ?>">
                        <?= (int)$product['stock'] === 0 ? 'Stok habis' : 'Stok ' . (int)$product['stock'] ?>
                    </p>
                    <div class="actions">
                        <a class="btn" href="edit.php?id=<?= (int)$product['id'] ?>">Edit</a>
                        <form method="post" action="delete.php"
                              onsubmit="return confirm('Hapus produk ini?');">
                            <?= csrf_field() ?>
                            <input type="hidden" name="id" value="<?= (int)$product['id'] ?>">
                            <button type="submit" class="btn danger">Hapus</button>
                        </form>
                    </div>
                </article>
            <?php endforeach; ?>
        </section>
    <?php endif; ?>
</main>
</body>
</html>

<?php
/** Variabel yang dibutuhkan: $values, $errors, $action, $submitLabel */
$field = static function (string $key, string $label, string $type, string $extra = '') use ($values, $errors): void {
    $err = $errors[$key] ?? null;
    echo '<label class="field' . ($err ? ' has-error' : '') . '">';
    echo '<span>' . e($label) . '</span>';
    echo '<input type="' . $type . '" name="' . $key . '" value="' . e((string)($values[$key] ?? '')) . '" ' . $extra . ' required>';
    if ($err) echo '<small>' . e($err) . '</small>';
    echo '</label>';
};
?>
<form method="post" action="<?= e($action) ?>" class="form" novalidate>
    <?= csrf_field() ?>

    <?php $field('name', 'Nama produk', 'text', 'maxlength="100"'); ?>

    <label class="field<?= isset($errors['category']) ? ' has-error' : '' ?>">
        <span>Kategori</span>
        <select name="category" required>
            <option value="">Pilih kategori</option>
            <?php foreach (CATEGORIES as $cat): ?>
                <option value="<?= e($cat) ?>" <?= ($values['category'] ?? '') === $cat ? 'selected' : '' ?>>
                    <?= e($cat) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php if (isset($errors['category'])): ?><small><?= e($errors['category']) ?></small><?php endif; ?>
    </label>

    <div class="row">
        <?php $field('price', 'Harga (Rp)', 'number', 'step="0.01" min="0.01"'); ?>
        <?php $field('stock', 'Stok', 'number', 'step="1" min="0"'); ?>
    </div>

    <div class="actions">
        <button type="submit" class="btn primary"><?= e($submitLabel) ?></button>
        <a href="index.php" class="btn">Batal</a>
    </div>
</form>

<?php
declare(strict_types=1);

session_start();

$pdo = new PDO(
    'mysql:host=localhost;dbname=product_manager;charset=utf8mb4',
    'root',   // ganti sesuai server kamu
    '',
    [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,   // prepared statement asli
    ]
);

const CATEGORIES = ['Makanan', 'Minuman', 'Elektronik', 'Pakaian', 'Lainnya'];

/** Escape output: wajib dipakai untuk semua data yang ditampilkan. */
function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

/* ---------- CSRF ---------- */
function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

function csrf_valid(): bool
{
    $sent = $_POST['csrf'] ?? '';
    return is_string($sent) && hash_equals($_SESSION['csrf'] ?? '', $sent);
}

/* ---------- Flash message + redirect (Post/Redirect/Get) ---------- */
function flash(string $message, string $type = 'ok'): void
{
    $_SESSION['flash'] = ['message' => $message, 'type' => $type];
}

function pull_flash(): ?array
{
    $f = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $f;
}

function redirect(string $url): never
{
    header('Location: ' . $url);
    exit;
}

/* ---------- Validasi ---------- */
/**
 * @return array{0: array, 1: array} [data bersih, daftar error]
 */
function validate_product(PDO $pdo, array $input, int $ignoreId = 0): array
{
    $name     = trim((string)($input['name'] ?? ''));
    $category = trim((string)($input['category'] ?? ''));
    $priceRaw = trim((string)($input['price'] ?? ''));
    $stockRaw = trim((string)($input['stock'] ?? ''));

    $errors = [];

    if (mb_strlen($name) < 3) {
        $errors['name'] = 'Nama minimal 3 karakter.';
    } elseif (mb_strlen($name) > 100) {
        $errors['name'] = 'Nama maksimal 100 karakter.';
    } else {
        $stmt = $pdo->prepare('SELECT id FROM products WHERE name = ? AND id <> ?');
        $stmt->execute([$name, $ignoreId]);
        if ($stmt->fetch()) {
            $errors['name'] = 'Nama produk sudah dipakai.';
        }
    }

    if (!in_array($category, CATEGORIES, true)) {
        $errors['category'] = 'Pilih kategori dari daftar.';
    }

    if (!preg_match('/^\d{1,10}(\.\d{1,2})?$/', $priceRaw) || (float)$priceRaw <= 0) {
        $errors['price'] = 'Harga harus angka lebih dari 0.';
    }

    if (!preg_match('/^\d{1,9}$/', $stockRaw)) {
        $errors['stock'] = 'Stok harus bilangan bulat 0 atau lebih.';
    }

    $data = [
        'name'     => $name,
        'category' => $category,
        'price'    => $priceRaw,
        'stock'    => $stockRaw,
    ];

    return [$data, $errors];
}

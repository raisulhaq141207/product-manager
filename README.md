# Product Manager (PHP + MySQL + PDO)

## Cara menjalankan
1. Import `schema.sql` (phpMyAdmin atau `mysql -u root < schema.sql`).
2. Sesuaikan user/password database di `config.php`.
3. Taruh folder ini di `htdocs` (XAMPP) lalu buka `http://localhost/product-manager/`.

## Checklist syarat
- Semua query memakai `$pdo->prepare()` (INSERT, SELECT by ID, UPDATE, DELETE).
- Semua output di-escape dengan `htmlspecialchars(..., ENT_QUOTES, "UTF-8")` (helper `e()`).
- Anti data ganda saat refresh: Post/Redirect/Get (`redirect()` setelah POST sukses).
- Delete hanya lewat POST + token CSRF; Create/Update juga diverifikasi CSRF.
- Validasi: nama >= 3 karakter, harga > 0, stok >= 0, nama unik (cek di PHP + UNIQUE KEY di DB).

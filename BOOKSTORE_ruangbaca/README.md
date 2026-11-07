# Ruang Baca – BookStore Website

Website toko buku online dengan 2 aktor: **Admin** dan **User**, dibangun dengan
HTML, CSS, JavaScript, PHP (native, mysqli), dan MySQL. Tabel `users` menggabungkan
Admin & User (dibedakan lewat kolom `role`), sesuai desain Figma yang diberikan.

## 1. Instalasi

1. Pastikan sudah punya **XAMPP / Laragon** (Apache + PHP 8+ + MySQL).
2. **Extract zip ini**, lalu copy folder `bookstore/` (yang isinya langsung `index.php`, `admin/`, dll)
   ke `htdocs/` (XAMPP) atau `www/` (Laragon).
   ⚠️ Pastikan tidak ke-nested dobel, misal jangan sampai jadi
   `htdocs/bookstore-ruangbaca/bookstore/` — cukup `htdocs/bookstore/`.
   Base URL sudah otomatis menyesuaikan folder tempat project ditaruh,
   jadi tidak masalah walau namanya diganti, asal tidak dobel folder.
3. Buat database dengan mengimpor `database.sql` lewat phpMyAdmin, atau via terminal:
   ```
   mysql -u root -p < database.sql
   ```
4. Cek kredensial database di `config/db.php` (default: host `localhost`, user `root`, password kosong).
5. Buka `http://localhost/bookstore/` di browser.

## 2. Akun Default

Setelah import database, buka file berikut **sekali saja** lewat browser untuk memastikan
password admin default aktif dan valid di server kamu (hash password dibuat langsung oleh
PHP di server kamu sendiri, jadi dijamin cocok):

```
http://localhost/bookstore/reset_admin.php
```

Setelah muncul pesan berhasil, **hapus file `reset_admin.php`** dari server (alasan keamanan).

**Login Admin** (`/admin/login.php`):
- Email: `admin@ruangbaca.com`
- Password: `admin123`

**User**: silakan daftar sendiri lewat halaman **Buat Akun** (`/register.php`).

## 3. Struktur Folder

```
bookstore/
├── admin/              -> Panel Admin (kelola kategori, buku, pengguna, pesanan, pesan masuk)
│   └── includes/        -> sidebar & layout khusus admin
├── assets/
│   ├── css/style.css    -> tema utama (cream & coklat, sesuai desain)
│   └── css/admin.css    -> tema panel admin (sidebar)
├── config/db.php        -> koneksi database
├── includes/            -> header/footer/fungsi untuk sisi user
├── uploads/              -> gambar sampul buku yang diupload admin
├── database.sql          -> struktur + data awal database
├── index.php             -> beranda
├── katalog.php            -> katalog + pencarian & filter kategori
├── buku_detail.php        -> detail buku
├── cart.php / checkout.php / proses_checkout.php  -> alur belanja
├── login.php / register.php / logout.php          -> autentikasi user
└── contact.php            -> form kontak ke admin
```

## 4. Fitur

**Admin**
- Login khusus admin (`/admin/login.php`)
- Dashboard ringkasan (total buku, pengguna, pesanan, pesan masuk)
- CRUD Kategori Buku
- CRUD Data Buku (dengan upload gambar sampul)
- List pengguna terdaftar
- List & update status pesanan dari semua user

**User**
- Registrasi & Login
- Lihat halaman About Us / Tentang Kami (di beranda)
- Contact to Admin (kirim pesan)
- Pencarian & filter buku
- Add to Cart
- Checkout dengan metode **Payment at Delivery** (bayar saat buku diterima)

## 5. Catatan Keamanan

Ini adalah project pembelajaran. Untuk produksi, tambahkan CSRF token,
rate limiting login, validasi upload file lebih ketat, dan gunakan prepared
statements (mysqli_prepare) alih-alih string interpolation langsung.

## Metode Pembayaran (COD, GoPay, DANA, Transfer Bank)

1. Jalankan `migration_pembayaran.sql` di phpMyAdmin (database `ruang_baca` → tab SQL) **sekali saja**.
   Instalasi baru dari `database.sql` sudah otomatis punya kolomnya.
2. Ganti nomor GoPay / DANA / rekening bank di `includes/payment.php` (fungsi `daftar_metode_bayar()`).
3. Gambar QRIS ada di `assets/img/qris.jpg` (ganti dengan QRIS asli kalau sudah ada).
4. Bukti pembayaran user tersimpan di `uploads/bukti_bayar/` dan bisa dilihat/diverifikasi admin di menu Pesanan.

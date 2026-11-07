-- =========================================================
-- TAMBAH PELANGGAN + PESANAN CONTOH (sesuai nama di Figma)
-- Jalankan di phpMyAdmin -> pilih database ruang_baca -> tab SQL -> paste -> Go
-- Password default untuk ke-4 akun ini: demo1234
-- Script ini akan MENGHAPUS semua pesanan lama lebih dulu.
-- =========================================================

USE ruang_baca;

-- Hapus semua pesanan lama (pesanan_detail ikut terhapus otomatis)
DELETE FROM pesanan;
ALTER TABLE pesanan AUTO_INCREMENT = 1;

-- Tambah 4 akun pelanggan contoh (skip kalau emailnya sudah ada)
INSERT INTO users (nama, email, password, role)
SELECT 'Anjani Dina', 'AnjaniDina@gmail.com', '$2b$12$rbJQW21mYvIk9vMzvmWVKujCY9Igvp2kVdtUBnxRGv6Zd869OwHi2', 'user'
WHERE NOT EXISTS (SELECT 1 FROM users WHERE email = 'AnjaniDina@gmail.com');

INSERT INTO users (nama, email, password, role)
SELECT 'Bayu Aditya', 'BayuAditya@gmail.com', '$2b$12$rbJQW21mYvIk9vMzvmWVKujCY9Igvp2kVdtUBnxRGv6Zd869OwHi2', 'user'
WHERE NOT EXISTS (SELECT 1 FROM users WHERE email = 'BayuAditya@gmail.com');

INSERT INTO users (nama, email, password, role)
SELECT 'Gina Saputri', 'GinaSaputri@gmail.com', '$2b$12$rbJQW21mYvIk9vMzvmWVKujCY9Igvp2kVdtUBnxRGv6Zd869OwHi2', 'user'
WHERE NOT EXISTS (SELECT 1 FROM users WHERE email = 'GinaSaputri@gmail.com');

INSERT INTO users (nama, email, password, role)
SELECT 'Rafael Sanjaya', 'RafaelSanjaya@gmail.com', '$2b$12$rbJQW21mYvIk9vMzvmWVKujCY9Igvp2kVdtUBnxRGv6Zd869OwHi2', 'user'
WHERE NOT EXISTS (SELECT 1 FROM users WHERE email = 'RafaelSanjaya@gmail.com');

-- Buat pesanan untuk masing-masing, memakai buku id pertama yang ada sebagai referensi item
INSERT INTO pesanan (user_id, total, metode_pembayaran, status, alamat_kirim)
SELECT id, 153000, 'Payment at Delivery', 'Diproses', 'Jakarta, Indonesia' FROM users WHERE email = 'BayuAditya@gmail.com';
INSERT INTO pesanan_detail (pesanan_id, buku_id, jumlah, harga_satuan)
SELECT LAST_INSERT_ID(), (SELECT id FROM buku ORDER BY id ASC LIMIT 1), 1, 153000;

INSERT INTO pesanan (user_id, total, metode_pembayaran, status, alamat_kirim)
SELECT id, 275000, 'Payment at Delivery', 'Diproses', 'Jakarta, Indonesia' FROM users WHERE email = 'AnjaniDina@gmail.com';
INSERT INTO pesanan_detail (pesanan_id, buku_id, jumlah, harga_satuan)
SELECT LAST_INSERT_ID(), (SELECT id FROM buku ORDER BY id ASC LIMIT 1), 1, 275000;

INSERT INTO pesanan (user_id, total, metode_pembayaran, status, alamat_kirim)
SELECT id, 288000, 'Payment at Delivery', 'Diproses', 'Jakarta, Indonesia' FROM users WHERE email = 'GinaSaputri@gmail.com';
INSERT INTO pesanan_detail (pesanan_id, buku_id, jumlah, harga_satuan)
SELECT LAST_INSERT_ID(), (SELECT id FROM buku ORDER BY id ASC LIMIT 1), 1, 288000;

INSERT INTO pesanan (user_id, total, metode_pembayaran, status, alamat_kirim)
SELECT id, 115000, 'Payment at Delivery', 'Selesai', 'Jakarta, Indonesia' FROM users WHERE email = 'RafaelSanjaya@gmail.com';
INSERT INTO pesanan_detail (pesanan_id, buku_id, jumlah, harga_satuan)
SELECT LAST_INSERT_ID(), (SELECT id FROM buku ORDER BY id ASC LIMIT 1), 1, 115000;

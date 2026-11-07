-- =========================================================
-- TAMBAH KOLOM TELEPON DI TABEL pesan_masuk
-- Jalankan di phpMyAdmin -> pilih database ruang_baca -> tab SQL -> paste -> Go
-- Aman dijalankan tanpa menghapus data pesan yang sudah ada.
-- =========================================================

USE ruang_baca;

ALTER TABLE pesan_masuk ADD COLUMN telepon VARCHAR(30) DEFAULT NULL AFTER email;

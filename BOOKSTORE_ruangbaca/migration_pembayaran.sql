-- =========================================================
-- METODE PEMBAYARAN BARU (GoPay, DANA, Transfer Bank) + BUKTI BAYAR
-- Jalankan di phpMyAdmin -> pilih database ruang_baca -> tab SQL -> paste -> Go
-- Aman untuk data pesanan yang sudah ada (pesanan lama dianggap COD).
-- Cukup dijalankan SEKALI. (Kalau muncul error "Duplicate column", berarti sudah pernah.)
-- =========================================================

USE ruang_baca;

ALTER TABLE pesanan
  ADD COLUMN bukti_bayar  VARCHAR(255) DEFAULT NULL AFTER metode_pembayaran,
  ADD COLUMN status_bayar VARCHAR(30)  NOT NULL DEFAULT 'cod' AFTER bukti_bayar;

-- status_bayar: cod | menunggu_verifikasi | terverifikasi | ditolak

-- =========================================================
-- FITUR REFUND (buku cacat / bermasalah + kirim balik buku + bukti transfer)
-- Jalankan di phpMyAdmin -> pilih database ruang_baca -> tab SQL -> paste -> Go
-- Aman dijalankan berulang, dan aman juga kalau versi lama tabel refund sudah pernah dibuat.
--
-- Alur status:
--   Menunggu -> Disetujui (= menunggu barang kembali) -> Barang Diterima -> Selesai
--   (atau Menunggu -> Ditolak)
-- =========================================================

USE ruang_baca;

CREATE TABLE IF NOT EXISTS refund (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pesanan_id INT NOT NULL,
    user_id INT NOT NULL,
    alasan VARCHAR(60) NOT NULL,
    deskripsi TEXT NOT NULL,
    foto_bukti VARCHAR(255) NOT NULL,
    tujuan_dana VARCHAR(150) NOT NULL,
    status ENUM('Menunggu','Disetujui','Ditolak','Barang Diterima','Selesai') NOT NULL DEFAULT 'Menunggu',
    catatan_admin VARCHAR(255) DEFAULT NULL,
    kurir VARCHAR(50) DEFAULT NULL,
    no_resi VARCHAR(60) DEFAULT NULL,
    foto_resi VARCHAR(255) DEFAULT NULL,
    bukti_transfer VARCHAR(255) DEFAULT NULL,
    tanggal DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_refund_pesanan (pesanan_id),
    FOREIGN KEY (pesanan_id) REFERENCES pesanan(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---- Upgrade dari versi lama (kalau tabel refund sudah ada) ----
ALTER TABLE refund MODIFY status ENUM('Menunggu','Diajukan','Disetujui','Ditolak','Dikirim Balik','Diterima','Barang Diterima','Selesai') NOT NULL DEFAULT 'Menunggu';
UPDATE refund SET status='Menunggu'        WHERE status='Diajukan';
UPDATE refund SET status='Disetujui'       WHERE status='Dikirim Balik';
UPDATE refund SET status='Barang Diterima' WHERE status='Diterima';
ALTER TABLE refund MODIFY status ENUM('Menunggu','Disetujui','Ditolak','Barang Diterima','Selesai') NOT NULL DEFAULT 'Menunggu';
ALTER TABLE refund ADD COLUMN IF NOT EXISTS bukti_transfer VARCHAR(255) DEFAULT NULL AFTER foto_resi;

-- =========================================================
-- RUANG BACA - BOOKSTORE DATABASE
-- Import file ini di phpMyAdmin / mysql client
-- =========================================================

CREATE DATABASE IF NOT EXISTS ruang_baca CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE ruang_baca;

-- ---------------------------------------------------------
-- Tabel users (Admin & User digabung, dibedakan oleh kolom role)
-- ---------------------------------------------------------
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    kontak VARCHAR(50) DEFAULT NULL,
    alamat VARCHAR(255) DEFAULT NULL,
    role ENUM('admin','user') NOT NULL DEFAULT 'user',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- Tabel kategori
-- ---------------------------------------------------------
CREATE TABLE kategori (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_kategori VARCHAR(100) NOT NULL,
    deskripsi VARCHAR(255) DEFAULT NULL,
    icon VARCHAR(10) DEFAULT '📚',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- Tabel buku
-- ---------------------------------------------------------
CREATE TABLE buku (
    id INT AUTO_INCREMENT PRIMARY KEY,
    judul VARCHAR(150) NOT NULL,
    penulis VARCHAR(100) NOT NULL,
    kategori_id INT DEFAULT NULL,
    harga DECIMAL(12,2) NOT NULL DEFAULT 0,
    stok INT NOT NULL DEFAULT 0,
    deskripsi TEXT,
    gambar VARCHAR(255) DEFAULT NULL,
    rating DECIMAL(2,1) NOT NULL DEFAULT 4.5,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (kategori_id) REFERENCES kategori(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- Tabel pesanan (header)
-- ---------------------------------------------------------
CREATE TABLE pesanan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    total DECIMAL(12,2) NOT NULL DEFAULT 0,
    metode_pembayaran VARCHAR(50) DEFAULT 'Payment at Delivery',
    status ENUM('Menunggu','Diproses','Dikirim','Selesai','Dibatalkan') DEFAULT 'Menunggu',
    alamat_kirim VARCHAR(255) DEFAULT NULL,
    tanggal DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- Tabel detail pesanan (isi keranjang saat checkout)
-- ---------------------------------------------------------
CREATE TABLE pesanan_detail (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pesanan_id INT NOT NULL,
    buku_id INT NOT NULL,
    jumlah INT NOT NULL DEFAULT 1,
    harga_satuan DECIMAL(12,2) NOT NULL,
    FOREIGN KEY (pesanan_id) REFERENCES pesanan(id) ON DELETE CASCADE,
    FOREIGN KEY (buku_id) REFERENCES buku(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- Tabel pesan masuk (Contact to Admin)
-- ---------------------------------------------------------
CREATE TABLE pesan_masuk (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL,
    subjek VARCHAR(150) DEFAULT NULL,
    pesan TEXT NOT NULL,
    dibaca TINYINT(1) DEFAULT 0,
    tanggal DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- =========================================================
-- SEED DATA
-- =========================================================

-- Admin default (password: admin123)
INSERT INTO users (nama, email, password, role) VALUES
('Administrator', 'admin@ruangbaca.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');
-- NOTE: hash di atas contoh bcrypt "admin123" - lihat catatan di README untuk generate ulang jika perlu

-- Kategori
INSERT INTO kategori (nama_kategori, deskripsi, icon) VALUES
('Fiksi', 'Novel dan cerita fiksi terbaik', '📖'),
('Non Fiksi', 'Pengembangan diri dan bisnis', '📘'),
('Akademik & Pendidikan', 'Ilmu pengetahuan dan sejarah', '🎓');

-- Buku contoh
INSERT INTO buku (judul, penulis, kategori_id, harga, stok, deskripsi, gambar, rating) VALUES
('Laut Bercerita', 'Leila S. Chudori', 1, 95000, 20, 'Novel tentang persahabatan dan perjuangan di masa penuh gejolak.', NULL, 4.8),
('Bumi Manusia', 'Pramoedya Ananta Toer', 1, 110000, 15, 'Tetralogi Buru yang legendaris tentang cinta dan perjuangan.', NULL, 4.9),
('Atomic Habits', 'James Clear', 2, 125000, 30, 'Membangun kebiasaan baik lewat langkah kecil, hasil besar.', NULL, 4.7),
('Cantik Itu Luka', 'Eka Kurniawan', 1, 98000, 12, 'Kisah magis realis tentang keluarga dan sejarah bangsa.', NULL, 4.6),
('Sejarah Dunia', 'Tim Penulis', 3, 150000, 10, 'Ringkasan sejarah dunia dari masa ke masa secara padat.', NULL, 4.3),
('Filosofi Teras', 'Henry Manampiring', 2, 89000, 25, 'Filsafat Stoa untuk hidup yang lebih tenang dan bijak.', NULL, 4.8);

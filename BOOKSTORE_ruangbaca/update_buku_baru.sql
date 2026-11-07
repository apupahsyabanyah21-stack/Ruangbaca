-- =========================================================
-- UPDATE DATA BUKU + TAMBAH BUKU BARU (dengan cover asli)
-- Jalankan ini di phpMyAdmin -> pilih database ruang_baca -> tab SQL
-- Aman dijalankan tanpa menghapus data admin / user / pesanan yang sudah ada.
-- =========================================================

USE ruang_baca;

-- Pasang cover untuk buku yang sudah ada
UPDATE buku SET gambar = 'laut_bercerita.jpeg' WHERE judul = 'Laut Bercerita';
UPDATE buku SET gambar = 'auto_habits.jpeg' WHERE judul = 'Atomic Habits';
UPDATE buku SET gambar = 'cantik_itu_luka.jpeg' WHERE judul = 'Cantik Itu Luka';
UPDATE buku SET gambar = 'filosofi_teras.jpeg' WHERE judul = 'Filosofi Teras';

-- Tambah buku-buku baru dari koleksi yang diupload
INSERT INTO buku (judul, penulis, kategori_id, harga, stok, deskripsi, gambar, rating) VALUES
('Catatan Seorang Demonstran', 'Soe Hok Gie', 2, 85000, 18, 'Catatan harian aktivis mahasiswa era 1960-an tentang idealisme dan perjuangan.', 'catatan_seorang_demonstran.jpeg', 4.8),
('Educated: Terdidik', 'Tara Westover', 2, 115000, 14, 'Memoar tentang perjuangan menempuh pendidikan di tengah keterbatasan keluarga.', 'educated_terdidik.jpeg', 4.9),
('Goodbye, Mr. Chips', 'James Hilton', 1, 79000, 16, 'Kisah klasik seorang guru sekolah Inggris yang penuh kehangatan.', 'goodbye_mr_chips.jpeg', 4.5),
('Koala Kumal', 'Raditya Dika', 1, 75000, 22, 'Kumpulan cerita komedi tentang cinta dan patah hati.', 'koala_kumal.jpeg', 4.4),
('Laskar Pelangi', 'Andrea Hirata', 1, 89000, 25, 'Kisah perjuangan anak-anak Belitung meraih pendidikan dan mimpi.', 'Laskar_pelangi.jpeg', 4.9),
('Lima Cerita', 'Desi Anwar', 1, 95000, 10, 'Lima kisah tentang perjalanan, perpisahan, dan proses menjadi dewasa.', 'lima_cerita.jpeg', 4.5),
('Madilog', 'Tan Malaka', 3, 105000, 12, 'Materialisme, Dialektika, dan Logika - pemikiran filsafat karya Tan Malaka.', 'madilog.jpeg', 4.6),
('Negeri 5 Menara', 'A. Fuadi', 1, 99000, 20, 'Kisah santri pondok pesantren yang mengejar mimpi ke lima benua.', 'negeri_5menara.jpeg', 4.8),
('Sang Pemimpi', 'Andrea Hirata', 1, 89000, 18, 'Buku kedua tetralogi Laskar Pelangi tentang mimpi dan persahabatan.', 'Sang_Pemimpi.jpeg', 4.8),
('The Art of Holding On and Letting Go', 'Kristin Bartley Lenz', 1, 110000, 8, 'Novel YA tentang petualangan, kehilangan, dan menemukan diri sendiri.', 'the_art_holding.jpeg', 4.3),
('9 Summers 10 Autumns', 'Iwan Setyawan', 2, 82000, 15, 'Kisah nyata perjalanan hidup dari Batu hingga New York.', '9_summers_10_autmns.jpeg', 4.7),
('3726 MDPL', 'Nurwina Sari', 1, 78000, 20, 'Novel alternative universe tentang perjalanan dan pendakian penuh makna.', '3726mdpl.jpeg', 4.4);

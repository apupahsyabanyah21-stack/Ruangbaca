<?php
// =========================================================
// HELPER FITUR REFUND
// =========================================================
require_once __DIR__ . '/functions.php';

define('REFUND_DIR', dirname(__DIR__) . '/uploads/refund/');
const REFUND_MAX_BYTES = 2 * 1024 * 1024; // 2 MB
const REFUND_MIME = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
const REFUND_ALAMAT_TOKO = 'Ruang Baca, Jl. Raya Buku No. 1, Jakarta Timur, Indonesia';

const REFUND_ALASAN = [
    'Buku cacat / rusak',
    'Halaman hilang atau salah cetak',
    'Buku tidak sesuai pesanan',
    'Lainnya',
];
const REFUND_KURIR = ['JNE', 'J&T', 'SiCepat', 'Pos Indonesia', 'Lainnya'];

// Alur: Menunggu -> Disetujui (menunggu barang kembali) -> Barang Diterima -> Selesai (bukti transfer diunggah admin)
// Urutan tahapan refund (dipakai untuk progres di halaman Pesanan Saya)
const REFUND_TAHAPAN = ['Menunggu', 'Disetujui', 'Barang Diterima', 'Selesai'];

function refund_url($namaFile) {
    return base_url('uploads/refund/' . rawurlencode($namaFile));
}

function kode_refund(array $r) {
    return 'RF' . str_pad((string)(int)$r['id'], 3, '0', STR_PAD_LEFT);
}

// [label, class css]
function refund_badge($status) {
    return match ($status) {
        'Menunggu'        => ['Menunggu', 'st-rf-diajukan'],
        'Disetujui'       => ['Disetujui', 'st-rf-disetujui'],
        'Ditolak'         => ['Ditolak', 'st-rf-ditolak'],
        'Barang Diterima' => ['Barang Diterima', 'st-rf-terima'],
        'Selesai'         => ['Selesai', 'st-rf-selesai'],
        default           => [$status, 'st-rf-diajukan'],
    };
}

function refund_validasi_foto($file, $wajib = true) {
    if (!is_array($file) || !isset($file['error']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return $wajib ? 'Foto wajib diunggah.' : null;
    }
    if ($file['error'] === UPLOAD_ERR_INI_SIZE || $file['error'] === UPLOAD_ERR_FORM_SIZE || $file['size'] > REFUND_MAX_BYTES) {
        return 'Ukuran foto maksimal 2 MB.';
    }
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return 'Upload foto gagal, silakan coba lagi.';
    }
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    if (!isset(REFUND_MIME[$mime])) {
        return 'Format foto harus JPG, PNG, atau WEBP.';
    }
    return null;
}

// Mengembalikan nama file yang tersimpan, atau false jika gagal
function refund_simpan_foto($file, $prefix) {
    if (refund_validasi_foto($file) !== null) return false;
    $ext = REFUND_MIME[(new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name'])];
    if (!is_dir(REFUND_DIR)) mkdir(REFUND_DIR, 0755, true);
    $nama = $prefix . '_' . date('YmdHis') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    return move_uploaded_file($file['tmp_name'], REFUND_DIR . $nama) ? $nama : false;
}

// Ambil 1 pesanan milik user (lengkap dengan urut_transaksi untuk kode TBJ...)
function refund_ambil_pesanan($conn, $pesananId, $userId) {
    $pesananId = (int)$pesananId; $userId = (int)$userId;
    $res = mysqli_query($conn, "SELECT p.*, " . SQL_URUT_TRANSAKSI . " FROM pesanan p WHERE p.id=$pesananId AND p.user_id=$userId");
    return $res ? mysqli_fetch_assoc($res) : null;
}

// -------------------------------------------------------------
// Upgrade skema otomatis: membuat tabel refund kalau belum ada, dan
// mengubah versi lama ke alur baru (Menunggu -> Disetujui -> Barang Diterima -> Selesai)
// Setara dengan menjalankan migration_refund.sql, jadi tidak perlu manual lagi.
// -------------------------------------------------------------
function refund_pastikan_skema($conn) {
    static $sudah = false;
    if ($sudah) return;
    $sudah = true;
    try {
        mysqli_query($conn, "CREATE TABLE IF NOT EXISTS refund (
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
        ) ENGINE=InnoDB");

        $q = mysqli_query($conn, "SHOW COLUMNS FROM refund LIKE 'status'");
        $col = $q ? mysqli_fetch_assoc($q) : null;
        if ($col && strpos($col['Type'], 'Barang Diterima') === false) {
            mysqli_query($conn, "ALTER TABLE refund MODIFY status ENUM('Menunggu','Diajukan','Disetujui','Ditolak','Dikirim Balik','Diterima','Barang Diterima','Selesai') NOT NULL DEFAULT 'Menunggu'");
            mysqli_query($conn, "UPDATE refund SET status='Menunggu' WHERE status='Diajukan'");
            mysqli_query($conn, "UPDATE refund SET status='Disetujui' WHERE status='Dikirim Balik'");
            mysqli_query($conn, "UPDATE refund SET status='Barang Diterima' WHERE status='Diterima'");
            mysqli_query($conn, "ALTER TABLE refund MODIFY status ENUM('Menunggu','Disetujui','Ditolak','Barang Diterima','Selesai') NOT NULL DEFAULT 'Menunggu'");
        }
        $q = mysqli_query($conn, "SHOW COLUMNS FROM refund LIKE 'bukti_transfer'");
        if ($q && mysqli_num_rows($q) === 0) {
            mysqli_query($conn, "ALTER TABLE refund ADD COLUMN bukti_transfer VARCHAR(255) DEFAULT NULL AFTER foto_resi");
        }
    } catch (mysqli_sql_exception $e) {
        error_log('[refund] gagal upgrade skema: ' . $e->getMessage());
    }
}
refund_pastikan_skema($conn);

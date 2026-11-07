<?php
// =========================================================
// METODE PEMBAYARAN
// Ganti nomor HP / rekening di bawah dengan data asli toko.
// =========================================================
require_once __DIR__ . '/functions.php';

define('BUKTI_DIR', dirname(__DIR__) . '/uploads/bukti_bayar/');
define('BUKTI_MAX_BYTES', 2 * 1024 * 1024); // 2 MB
const BUKTI_MIME = [
    'image/jpeg' => 'jpg',
    'image/png'  => 'png',
    'image/webp' => 'webp',
];

function daftar_metode_bayar() {
    $daftar = [
        'cod' => [
            'nama' => 'Payment at Delivery', 'keterangan' => 'Bayar tunai saat buku diterima',
            'ikon' => '💵', 'butuh_bukti' => false,
        ],
        'gopay' => [
            'nama' => 'GoPay', 'keterangan' => 'Scan QRIS atau kirim ke nomor GoPay',
            'logo' => 'assets/img/payment/gopay.png', 'butuh_bukti' => true, 'label_nomor' => 'No. GoPay',
            'nomor' => '0812-3456-7890', 'atas_nama' => 'Ruang Baca',
        ],
        'dana' => [
            'nama' => 'DANA', 'keterangan' => 'Scan QRIS atau kirim ke nomor DANA',
            'logo' => 'assets/img/payment/dana.png', 'butuh_bukti' => true, 'label_nomor' => 'No. DANA',
            'nomor' => '0812-3456-7890', 'atas_nama' => 'Ruang Baca',
        ],
        'bca' => [
            'nama' => 'Transfer Bank BCA', 'keterangan' => 'Transfer ke rekening BCA',
            'logo' => 'assets/img/payment/bca.png', 'butuh_bukti' => true, 'label_nomor' => 'No. rekening',
            'nomor' => '1234567890', 'atas_nama' => 'Ruang Baca',
        ],
        'bri' => [
            'nama' => 'Transfer Bank BRI', 'keterangan' => 'Transfer ke rekening BRI',
            'logo' => 'assets/img/payment/bri.png', 'butuh_bukti' => true, 'label_nomor' => 'No. rekening',
            'nomor' => '0123-01-000123-45-6', 'atas_nama' => 'Ruang Baca',
        ],
        'mandiri' => [
            'nama' => 'Transfer Bank Mandiri', 'keterangan' => 'Transfer ke rekening Mandiri',
            'logo' => 'assets/img/payment/mandiri.png', 'butuh_bukti' => true, 'label_nomor' => 'No. rekening',
            'nomor' => '123-00-1234567-8', 'atas_nama' => 'Ruang Baca',
        ],
    ];
    // Ubah path logo jadi URL penuh (dipakai juga oleh JavaScript di checkout)
    foreach ($daftar as $kode => $m) {
        if (!empty($m['logo'])) $daftar[$kode]['logo'] = base_url($m['logo']);
    }
    return $daftar;
}

/** Logo metode pembayaran (kotak seragam). Kalau tidak punya logo (COD), tampilkan emoji ikonnya. */
function logo_metode_html(array $m, $kelas = 'pay-logo') {
    if (empty($m['logo'])) {
        $ikon = htmlspecialchars((string)($m['ikon'] ?? ''), ENT_QUOTES, 'UTF-8');
        return strpos($kelas, 'pay-logo-sm') !== false ? $ikon : '<span class="pay-ico">' . $ikon . '</span>';
    }
    return '<span class="' . $kelas . '"><img src="' . htmlspecialchars($m['logo'], ENT_QUOTES, 'UTF-8')
         . '" alt="' . htmlspecialchars($m['nama'], ENT_QUOTES, 'UTF-8') . '"></span>';
}

/** Cari metode dari nama yang tersimpan di kolom pesanan.metode_pembayaran. */
function metode_dari_nama($nama) {
    foreach (daftar_metode_bayar() as $kode => $m) {
        if (strcasecmp($m['nama'], (string)$nama) === 0) return $m + ['kode' => $kode];
    }
    $cod = daftar_metode_bayar()['cod'];
    return $cod + ['kode' => 'cod'];
}

function bukti_url($namaFile) {
    return base_url('uploads/bukti_bayar/' . rawurlencode($namaFile));
}

// -------------------- Upload bukti pembayaran --------------------

/** Mengembalikan pesan error (string) atau null kalau file valid. */
function validasi_bukti_bayar($file) {
    if (!is_array($file) || !isset($file['error']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return 'Bukti pembayaran wajib diunggah untuk metode ini.';
    }
    if ($file['error'] === UPLOAD_ERR_INI_SIZE || $file['error'] === UPLOAD_ERR_FORM_SIZE || $file['size'] > BUKTI_MAX_BYTES) {
        return 'Ukuran bukti pembayaran maksimal 2 MB.';
    }
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return 'Upload bukti pembayaran gagal, silakan coba lagi.';
    }
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    if (!isset(BUKTI_MIME[$mime])) {
        return 'Format bukti pembayaran harus JPG, PNG, atau WEBP.';
    }
    return null;
}

/** Simpan ke uploads/bukti_bayar/. Mengembalikan nama file, atau false jika gagal. */
function simpan_bukti_bayar($file, $pesananId) {
    if (validasi_bukti_bayar($file) !== null) return false;
    $ext = BUKTI_MIME[(new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name'])];
    if (!is_dir(BUKTI_DIR)) mkdir(BUKTI_DIR, 0755, true);
    $nama = 'bukti_' . (int)$pesananId . '_' . date('YmdHis') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    return move_uploaded_file($file['tmp_name'], BUKTI_DIR . $nama) ? $nama : false;
}

function label_status_bayar($status) {
    switch ($status) {
        case 'terverifikasi':       return 'Lunas (terverifikasi)';
        case 'menunggu_verifikasi': return 'Menunggu verifikasi';
        case 'ditolak':             return 'Bukti ditolak';
        default:                    return 'Bayar saat buku diterima';
    }
}

// -------------------- Halaman sukses --------------------

/** Kotak info di sukses.php sesuai metode & status pembayaran. */
function render_info_pembayaran(array $pesanan) {
    $m = metode_dari_nama($pesanan['metode_pembayaran']);
    $status = $pesanan['status_bayar'] ?? 'cod';
    $e = fn($s) => htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
    echo '<style>.alert-pending{background:#FFF4DB;color:#7A5410;border:1px solid #F0DAA6}'
       . '.bukti-thumb{margin-top:12px}.bukti-thumb img{display:block;width:110px;border-radius:8px;border:1px solid var(--border)}'
       . '.bukti-thumb small{display:block;margin-top:4px;opacity:.8}'
       . '.pay-logo{display:inline-flex;align-items:center;justify-content:center;width:88px;height:40px;padding:5px 8px;background:#fff;border:1px solid var(--border);border-radius:8px;flex-shrink:0;vertical-align:middle}'
       . '.pay-logo img{max-width:100%;max-height:100%;object-fit:contain;display:block}'
       . '.pay-logo-sm{width:68px;height:32px;margin-right:8px}</style>';

    if (!$m['butuh_bukti']) {
        echo '<div class="alert alert-success" style="margin-top:22px;text-align:left;">'
           . logo_metode_html($m, 'pay-logo pay-logo-sm') . ' Pesanan kamu akan diproses dan dikirim. Silakan bayar tunai saat buku diterima.</div>';
        return;
    }

    if ($status === 'terverifikasi') {
        $pesan = 'Pembayaran via ' . $m['nama'] . ' sudah diverifikasi. Pesananmu sedang diproses dan akan segera dikirim.';
        $kelas = 'alert-success';
    } elseif ($status === 'ditolak') {
        $pesan = 'Bukti pembayaran via ' . $m['nama'] . ' belum bisa kami verifikasi. Silakan hubungi kami lewat halaman Kontak.';
        $kelas = 'alert-error';
    } else {
        $pesan = 'Bukti pembayaran via ' . $m['nama'] . ' sudah kami terima. Pesananmu akan diproses setelah pembayaran diverifikasi.';
        $kelas = 'alert-pending';
    }
    echo '<div class="alert ' . $kelas . '" style="margin-top:22px;text-align:left;">' . logo_metode_html($m, 'pay-logo pay-logo-sm') . ' ' . $e($pesan);
    if (!empty($pesanan['bukti_bayar'])) {
        $url = $e(bukti_url($pesanan['bukti_bayar']));
        echo '<div class="bukti-thumb"><a href="' . $url . '" target="_blank" rel="noopener"><img src="' . $url . '" alt="Bukti pembayaran"></a>'
           . '<small>Bukti pembayaran kamu</small></div>';
    }
    echo '</div>';
}

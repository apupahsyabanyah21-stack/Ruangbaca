<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/payment.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_SESSION['cart'])) {
    header('Location: ' . base_url('cart.php'));
    exit;
}

$user_id = (int)$_SESSION['user_id'];
$alamat = clean($conn, $_POST['alamat']);

// ---- Metode pembayaran (COD / GoPay / DANA / Bank) ----
$kodeMetode = $_POST['metode_bayar'] ?? 'cod';
$daftarMetode = daftar_metode_bayar();
if (!isset($daftarMetode[$kodeMetode])) {
    flash('error', 'Metode pembayaran tidak valid.');
    header('Location: ' . base_url('checkout.php'));
    exit;
}
$infoMetode = $daftarMetode[$kodeMetode];
if ($infoMetode['butuh_bukti']) {
    $errBukti = validasi_bukti_bayar($_FILES['bukti_bayar'] ?? null);
    if ($errBukti !== null) {
        flash('error', $errBukti);
        header('Location: ' . base_url('checkout.php'));
        exit;
    }
}
$metode = clean($conn, $infoMetode['nama']);
$statusBayar = $infoMetode['butuh_bukti'] ? 'menunggu_verifikasi' : 'cod';

$ids = implode(',', array_map('intval', array_keys($_SESSION['cart'])));
$res = mysqli_query($conn, "SELECT * FROM buku WHERE id IN ($ids)");

$items = [];
$total = 0;
while ($row = mysqli_fetch_assoc($res)) {
    $jumlah = $_SESSION['cart'][$row['id']]['jumlah'];
    $total += $jumlah * $row['harga'];
    $items[] = ['id' => $row['id'], 'jumlah' => $jumlah, 'harga' => $row['harga']];
}

$namaBukti = null;
mysqli_begin_transaction($conn);
try {
    mysqli_query($conn, "INSERT INTO pesanan (user_id, total, metode_pembayaran, status_bayar, status, alamat_kirim) 
        VALUES ($user_id, $total, '$metode', '$statusBayar', 'Menunggu', '$alamat')");
    $pesanan_id = mysqli_insert_id($conn);

    foreach ($items as $it) {
        mysqli_query($conn, "INSERT INTO pesanan_detail (pesanan_id, buku_id, jumlah, harga_satuan) 
            VALUES ($pesanan_id, {$it['id']}, {$it['jumlah']}, {$it['harga']})");
        mysqli_query($conn, "UPDATE buku SET stok = GREATEST(stok - {$it['jumlah']}, 0) WHERE id = {$it['id']}");
    }
    // Simpan bukti pembayaran (hanya untuk GoPay / DANA / Bank)
    if ($infoMetode['butuh_bukti']) {
        $namaBukti = simpan_bukti_bayar($_FILES['bukti_bayar'], $pesanan_id);
        if ($namaBukti === false) {
            throw new Exception('Gagal menyimpan bukti pembayaran.');
        }
        $stmt = mysqli_prepare($conn, "UPDATE pesanan SET bukti_bayar = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt, 'si', $namaBukti, $pesanan_id);
        mysqli_stmt_execute($stmt);
    }
    mysqli_commit($conn);
    $_SESSION['cart'] = [];
    header('Location: ' . base_url('sukses.php?id=' . $pesanan_id));
    exit;
} catch (Exception $e) {
    mysqli_rollback($conn);
    if (is_string($namaBukti) && is_file(BUKTI_DIR . $namaBukti)) {
        unlink(BUKTI_DIR . $namaBukti);
    }
    error_log('[checkout] ' . $e->getMessage());
    $pesanGagal = 'Gagal membuat pesanan, silakan coba lagi.';
    if (stripos($e->getMessage(), 'Unknown column') !== false) {
        $pesanGagal = 'Database belum diperbarui. Jalankan migration_pembayaran.sql di phpMyAdmin, lalu coba lagi.';
    }
    flash('error', $pesanGagal);
    header('Location: ' . base_url('checkout.php'));
    exit;
}

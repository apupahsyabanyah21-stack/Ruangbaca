<?php
require_once __DIR__ . '/includes/functions.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_SESSION['cart'])) {
    header('Location: ' . base_url('cart.php'));
    exit;
}

$user_id = (int)$_SESSION['user_id'];
$alamat = clean($conn, $_POST['alamat']);
$metode = clean($conn, $_POST['metode']);

$ids = implode(',', array_map('intval', array_keys($_SESSION['cart'])));
$res = mysqli_query($conn, "SELECT * FROM buku WHERE id IN ($ids)");

$items = [];
$total = 0;
while ($row = mysqli_fetch_assoc($res)) {
    $jumlah = $_SESSION['cart'][$row['id']]['jumlah'];
    $total += $jumlah * $row['harga'];
    $items[] = ['id' => $row['id'], 'jumlah' => $jumlah, 'harga' => $row['harga']];
}

mysqli_begin_transaction($conn);
try {
    mysqli_query($conn, "INSERT INTO pesanan (user_id, total, metode_pembayaran, status, alamat_kirim) 
        VALUES ($user_id, $total, '$metode', 'Menunggu', '$alamat')");
    $pesanan_id = mysqli_insert_id($conn);

    foreach ($items as $it) {
        mysqli_query($conn, "INSERT INTO pesanan_detail (pesanan_id, buku_id, jumlah, harga_satuan) 
            VALUES ($pesanan_id, {$it['id']}, {$it['jumlah']}, {$it['harga']})");
        mysqli_query($conn, "UPDATE buku SET stok = GREATEST(stok - {$it['jumlah']}, 0) WHERE id = {$it['id']}");
    }
    mysqli_commit($conn);
    $_SESSION['cart'] = [];
    flash('success', 'Pesanan berhasil dibuat! Silakan bayar saat buku diterima.');
    header('Location: ' . base_url('index.php'));
    exit;
} catch (Exception $e) {
    mysqli_rollback($conn);
    flash('error', 'Gagal membuat pesanan, silakan coba lagi.');
    header('Location: ' . base_url('checkout.php'));
    exit;
}

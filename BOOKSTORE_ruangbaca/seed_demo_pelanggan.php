<?php
// =========================================================
// SEED DATA PELANGGAN + PESANAN CONTOH (sesuai nama di Figma)
// Jalankan SEKALI SAJA lewat browser, setelah itu HAPUS file ini.
// =========================================================
require_once __DIR__ . '/config/db.php';

// Pastikan ada minimal 1 buku untuk dijadikan referensi item pesanan
$bukuRow = mysqli_fetch_assoc(mysqli_query($conn, "SELECT id, harga FROM buku ORDER BY id ASC LIMIT 1"));
if (!$bukuRow) {
    die('Belum ada data buku di database. Tambahkan minimal 1 buku dulu sebelum menjalankan script ini.');
}
$bukuId = $bukuRow['id'];

$pelanggan = [
    ['nama' => 'Anjani Dina',   'email' => 'AnjaniDina@gmail.com',   'total' => 275000, 'status' => 'Diproses'],
    ['nama' => 'Bayu Aditya',   'email' => 'BayuAditya@gmail.com',   'total' => 153000, 'status' => 'Diproses'],
    ['nama' => 'Gina Saputri',  'email' => 'GinaSaputri@gmail.com',  'total' => 288000, 'status' => 'Diproses'],
    ['nama' => 'Rafael Sanjaya','email' => 'RafaelSanjaya@gmail.com','total' => 115000, 'status' => 'Selesai'],
];

$passwordHash = password_hash('demo1234', PASSWORD_DEFAULT);
$hasil = [];

// Hapus semua pesanan lama (pesanan_detail ikut terhapus otomatis karena ON DELETE CASCADE)
mysqli_query($conn, "DELETE FROM pesanan");
mysqli_query($conn, "ALTER TABLE pesanan AUTO_INCREMENT = 1");

foreach ($pelanggan as $p) {
    $nama = mysqli_real_escape_string($conn, $p['nama']);
    $email = mysqli_real_escape_string($conn, $p['email']);

    // Cek apakah user sudah ada, kalau belum buat baru
    $cek = mysqli_query($conn, "SELECT id FROM users WHERE email='$email'");
    $row = mysqli_fetch_assoc($cek);
    if ($row) {
        $userId = $row['id'];
    } else {
        mysqli_query($conn, "INSERT INTO users (nama, email, password, role) VALUES ('$nama','$email','$passwordHash','user')");
        $userId = mysqli_insert_id($conn);
    }

    // Buat pesanan untuk user ini
    $total = (int)$p['total'];
    $status = mysqli_real_escape_string($conn, $p['status']);
    mysqli_query($conn, "INSERT INTO pesanan (user_id, total, metode_pembayaran, status, alamat_kirim)
        VALUES ($userId, $total, 'Payment at Delivery', '$status', 'Jakarta, Indonesia')");
    $pesananId = mysqli_insert_id($conn);

    // Tambahkan detail pesanan (referensi ke 1 buku, harga disesuaikan agar totalnya konsisten)
    mysqli_query($conn, "INSERT INTO pesanan_detail (pesanan_id, buku_id, jumlah, harga_satuan)
        VALUES ($pesananId, $bukuId, 1, $total)");

    $hasil[] = "{$p['nama']} ({$p['email']}) — pesanan #$pesananId dibuat, status {$p['status']}";
}

echo "<h3>Selesai!</h3><p>Semua pesanan lama sudah dihapus, dan pesanan baru berikut sudah dibuat:</p><ul>";
foreach ($hasil as $h) echo "<li>" . htmlspecialchars($h) . "</li>";
echo "</ul>";
echo "<p><strong style='color:red;'>PENTING: Hapus file seed_demo_pelanggan.php ini dari server sekarang juga.</strong></p>";

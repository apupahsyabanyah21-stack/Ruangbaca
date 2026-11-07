<?php
// =========================================================
// PERBAIKI LOGIN ADMIN + USER DEMO
// Jalankan SEKALI SAJA lewat browser. Setelah selesai, HAPUS file ini.
// Aman dijalankan berkali-kali (tidak akan membuat akun dobel,
// dan TIDAK menghapus/mengubah data pesanan).
// =========================================================
require_once __DIR__ . '/config/db.php';

function setPassword($conn, $email, $nama, $role, $plainPassword) {
    $hash = password_hash($plainPassword, PASSWORD_DEFAULT);
    $emailEsc = mysqli_real_escape_string($conn, $email);
    $namaEsc = mysqli_real_escape_string($conn, $nama);

    $cek = mysqli_query($conn, "SELECT id FROM users WHERE email='$emailEsc'");
    $row = mysqli_fetch_assoc($cek);

    if ($row) {
        mysqli_query($conn, "UPDATE users SET password='$hash' WHERE id={$row['id']}");
        return "Password untuk $email berhasil di-reset.";
    } else {
        mysqli_query($conn, "INSERT INTO users (nama, email, password, role) VALUES ('$namaEsc','$emailEsc','$hash','$role')");
        return "Akun baru $email berhasil dibuat.";
    }
}

$hasil = [];
$hasil[] = setPassword($conn, 'admin@ruangbaca.com', 'Administrator', 'admin', 'admin123');
$hasil[] = setPassword($conn, 'AnjaniDina@gmail.com', 'Anjani Dina', 'user', 'demo1234');
$hasil[] = setPassword($conn, 'BayuAditya@gmail.com', 'Bayu Aditya', 'user', 'demo1234');
$hasil[] = setPassword($conn, 'GinaSaputri@gmail.com', 'Gina Saputri', 'user', 'demo1234');
$hasil[] = setPassword($conn, 'RafaelSanjaya@gmail.com', 'Rafael Sanjaya', 'user', 'demo1234');
?>
<h3>Selesai!</h3>
<ul>
<?php foreach ($hasil as $h) echo '<li>' . htmlspecialchars($h) . '</li>'; ?>
</ul>
<p>Sekarang kamu bisa login dengan:</p>
<ul>
  <li><strong>Admin</strong> — admin@ruangbaca.com / admin123</li>
  <li><strong>User</strong> — AnjaniDina@gmail.com / demo1234 (atau BayuAditya@gmail.com, GinaSaputri@gmail.com, RafaelSanjaya@gmail.com, password sama)</li>
</ul>
<p><strong style="color:red;">PENTING: Hapus file perbaiki_login.php ini dari server sekarang juga.</strong></p>

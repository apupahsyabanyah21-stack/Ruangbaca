<?php
// =========================================================
// RESET PASSWORD ADMIN (jalankan sekali saja lewat browser)
// Setelah berhasil, HAPUS file ini dari server untuk keamanan.
// =========================================================
require_once __DIR__ . '/config/db.php';

$email = 'admin@ruangbaca.com';
$password_baru = 'admin123';

$hash = password_hash($password_baru, PASSWORD_DEFAULT);
$hash_escaped = mysqli_real_escape_string($conn, $hash);

$cek = mysqli_query($conn, "SELECT id FROM users WHERE email='$email'");

if (mysqli_num_rows($cek) > 0) {
    mysqli_query($conn, "UPDATE users SET password='$hash_escaped' WHERE email='$email'");
    echo "Password admin berhasil di-reset.<br>";
} else {
    mysqli_query($conn, "INSERT INTO users (nama, email, password, role) VALUES ('Administrator', '$email', '$hash_escaped', 'admin')");
    echo "Akun admin baru berhasil dibuat.<br>";
}

echo "<br>Email: <b>$email</b><br>";
echo "Password: <b>$password_baru</b><br><br>";
echo "<strong style='color:red;'>PENTING: Hapus file reset_admin.php ini dari server sekarang juga.</strong>";

<?php
$page_title = 'Buat Akun';
require_once __DIR__ . '/includes/functions.php';
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = clean($conn, $_POST['nama']);
    $email = clean($conn, $_POST['email']);
    $password = $_POST['password'];
    $konfirmasi = $_POST['konfirmasi'];

    if ($nama === '' || $email === '' || $password === '') {
        $error = 'Semua field wajib diisi.';
    } elseif ($password !== $konfirmasi) {
        $error = 'Konfirmasi password tidak sama.';
    } else {
        $cek = mysqli_query($conn, "SELECT id FROM users WHERE email='$email'");
        if (mysqli_num_rows($cek) > 0) {
            $error = 'Email sudah terdaftar. Silakan login.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            mysqli_query($conn, "INSERT INTO users (nama, email, password, role) VALUES ('$nama','$email','$hash','user')");
            flash('success', 'Akun berhasil dibuat, silakan login.');
            header('Location: ' . base_url('login.php'));
            exit;
        }
    }
}
require_once __DIR__ . '/includes/header.php';
?>
<div class="auth-wrap">
  <div class="auth-card card">
    <h2>Buat Akun</h2>
    <p class="sub">Gabung dengan Ruang Baca sekarang karena dengan membaca akan menambah pengetahuan.</p>
    <?php if ($error): ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <form method="post">
      <div class="form-group">
        <label>Nama Lengkap</label>
        <input type="text" name="nama" class="form-control" required>
      </div>
      <div class="form-group">
        <label>Email</label>
        <input type="email" name="email" class="form-control" required>
      </div>
      <div class="form-group">
        <label>Password</label>
        <input type="password" name="password" class="form-control" required>
      </div>
      <div class="form-group">
        <label>Konfirmasi Password</label>
        <input type="password" name="konfirmasi" class="form-control" required>
      </div>
      <button type="submit" class="btn btn-primary btn-block">Daftar</button>
    </form>
    <div class="foot">Sudah punya akun? <a href="<?= base_url('login.php') ?>">Masuk di sini</a></div>
  </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>

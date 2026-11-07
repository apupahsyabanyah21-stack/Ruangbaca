<?php
$page_title = 'Buat Akun';
require_once __DIR__ . '/includes/functions.php';
$error = null;

$redirect = $_POST['redirect'] ?? $_GET['redirect'] ?? '';
if ($redirect !== '' && (str_starts_with($redirect, 'http://') || str_starts_with($redirect, 'https://') || str_starts_with($redirect, '//'))) {
    $redirect = '';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = clean($conn, $_POST['nama']);
    $email = clean($conn, $_POST['email']);
    $password = $_POST['password'];

    if ($nama === '' || $email === '' || $password === '') {
        $error = 'Semua field wajib diisi.';
    } else {
        $cek = mysqli_query($conn, "SELECT id FROM users WHERE email='$email'");
        if (mysqli_num_rows($cek) > 0) {
            $error = 'Email sudah terdaftar. Silakan login.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            mysqli_query($conn, "INSERT INTO users (nama, email, password, role) VALUES ('$nama','$email','$hash','user')");
            flash('success', 'Akun berhasil dibuat, silakan login.');
            $loginUrl = base_url('login.php') . ($redirect !== '' ? '?redirect=' . urlencode($redirect) : '');
            header('Location: ' . $loginUrl);
            exit;
        }
    }
}
require_once __DIR__ . '/includes/header.php';
?>
<div class="auth-split-wrap">
  <div class="auth-split">
    <div class="auth-quote-panel">
      <div>
        <blockquote>&ldquo;Aku rela di penjara asalkan bersama buku, karena dengan buku aku bebas.&rdquo;</blockquote>
        <cite>&mdash;Mohammad Hatta</cite>
      </div>
    </div>
    <div class="auth-form-panel">
      <h2>Buat Akun</h2>
      <p class="sub">Bergabung dengan ribuan pembaca</p>
      <?php if ($error): ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
      <form method="post" autocomplete="off">
        <input type="hidden" name="redirect" value="<?= htmlspecialchars($redirect) ?>">
        <div class="form-group has-icon">
          <?= icon('user', 17) ?>
          <input type="text" name="nama" class="form-control" placeholder="Nama Lengkap" autocomplete="off" required>
        </div>
        <div class="form-group has-icon">
          <?= icon('mail', 17) ?>
          <input type="email" name="email" class="form-control" placeholder="Email" autocomplete="off" required>
        </div>
        <div class="form-group has-icon">
          <?= icon('lock', 17) ?>
          <input type="password" name="password" class="form-control" placeholder="Password" autocomplete="new-password" required>
        </div>
        <button type="submit" class="btn btn-gold">Daftar</button>
      </form>
      <div class="foot">Sudah punya akun? <a href="<?= base_url('login.php') . ($redirect !== '' ? '?redirect=' . urlencode($redirect) : '') ?>">Masuk di sini</a></div>
      <a href="<?= base_url('index.php') ?>" class="back-link"><?= icon('arrow-left', 13) ?> Kembali ke Beranda</a>
    </div>
  </div>
</div>
</body>
</html>

<?php
$page_title = 'Selamat Datang';
require_once __DIR__ . '/includes/functions.php';
$error = null;
$success = flash('success');

// Alamat tujuan setelah login berhasil (misal: checkout.php kalau tadinya mau checkout)
$redirect = $_POST['redirect'] ?? $_GET['redirect'] ?? '';
// Validasi sederhana: hanya izinkan path relatif di dalam situs sendiri, bukan URL luar
if ($redirect !== '' && (str_starts_with($redirect, 'http://') || str_starts_with($redirect, 'https://') || str_starts_with($redirect, '//'))) {
    $redirect = '';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = clean($conn, $_POST['email']);
    $password = $_POST['password'];

    $res = mysqli_query($conn, "SELECT * FROM users WHERE email='$email' LIMIT 1");
    $user = mysqli_fetch_assoc($res);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['nama'] = $user['nama'];
        $_SESSION['role'] = $user['role'];

        // Kalau tadinya mau tambah buku ke keranjang sebelum diminta login, selesaikan sekarang
        if (!empty($_SESSION['pending_add_to_cart'])) {
            $pendingId = (int)$_SESSION['pending_add_to_cart'];
            unset($_SESSION['pending_add_to_cart']);
            if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
            if (isset($_SESSION['cart'][$pendingId])) {
                $_SESSION['cart'][$pendingId]['jumlah'] += 1;
            } else {
                $_SESSION['cart'][$pendingId] = ['jumlah' => 1];
            }
        }

        if ($redirect !== '') {
            header('Location: ' . $redirect);
        } elseif ($user['role'] === 'admin') {
            header('Location: ' . base_url('admin/dashboard.php'));
        } else {
            header('Location: ' . base_url('index.php'));
        }
        exit;
    } else {
        $error = 'Email atau password salah.';
    }
}
require_once __DIR__ . '/includes/header.php';
?>
<div class="auth-split-wrap">
  <div class="auth-split">
    <div class="auth-quote-panel">
      <div>
        <blockquote>&ldquo;Cuma perlu satu buku untuk jatuh cinta pada membaca. Cari buku itu, mari jatuh cinta!&rdquo;</blockquote>
        <cite>&mdash;Najwa Shihab</cite>
      </div>
    </div>
    <div class="auth-form-panel">
      <h2>Selamat Datang</h2>
      <p class="sub">Masuk ke akun Ruang Baca kamu untuk mulai berbelanja.</p>
      <?php if ($success): ?><div class="alert alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>
      <?php if ($error): ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
      <form method="post" autocomplete="off">
        <input type="hidden" name="redirect" value="<?= htmlspecialchars($redirect) ?>">
        <div class="form-group has-icon">
          <?= icon('mail', 17) ?>
          <input type="email" name="email" class="form-control" placeholder="Enter Your Email" autocomplete="off" required>
        </div>
        <div class="form-group has-icon">
          <?= icon('lock', 17) ?>
          <input type="password" name="password" class="form-control" placeholder="Enter Your Password" autocomplete="new-password" required>
        </div>
        <button type="submit" class="btn btn-gold">Login</button>
      </form>
      <div class="foot">Belum punya akun? <a href="<?= base_url('register.php') . ($redirect !== '' ? '?redirect=' . urlencode($redirect) : '') ?>">Daftar di sini</a></div>
      <a href="<?= base_url('index.php') ?>" class="back-link"><?= icon('arrow-left', 13) ?> Kembali ke Beranda</a>
    </div>
  </div>
</div>
</body>
</html>

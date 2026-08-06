<?php
require_once __DIR__ . '/../includes/functions.php';
$error = null;

if (is_admin()) { header('Location: ' . base_url('admin/dashboard.php')); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = clean($conn, $_POST['email']);
    $password = $_POST['password'];
    $res = mysqli_query($conn, "SELECT * FROM users WHERE email='$email' AND role='admin' LIMIT 1");
    $user = mysqli_fetch_assoc($res);
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['nama'] = $user['nama'];
        $_SESSION['role'] = $user['role'];
        header('Location: ' . base_url('admin/dashboard.php'));
        exit;
    } else {
        $error = 'Email atau password admin salah.';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login Admin - Ruang Baca</title>
<link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>">
</head>
<body style="background:var(--brown-dark);">
<div class="auth-wrap">
  <div class="auth-card card">
    <h2>🔐 Login Admin</h2>
    <p class="sub">Masuk ke Panel Admin Ruang Baca</p>
    <?php if ($error): ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <form method="post">
      <div class="form-group"><label>Email</label>
        <input type="email" name="email" class="form-control" required></div>
      <div class="form-group"><label>Password</label>
        <input type="password" name="password" class="form-control" required></div>
      <button type="submit" class="btn btn-primary btn-block">Login</button>
    </form>
    <div class="foot"><a href="<?= base_url('index.php') ?>">&larr; Kembali ke halaman utama</a></div>
  </div>
</div>
</body>
</html>

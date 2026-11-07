<?php require_once __DIR__ . '/functions.php'; ?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= isset($page_title) ? $page_title . ' - Ruang Baca' : 'Ruang Baca - Toko Buku Online Terpercaya' ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,500;0,600;0,700;0,800;1,600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>?v=<?= @filemtime(__DIR__ . '/../assets/css/style.css') ?>">
</head>
<body>
<header class="site-header">
  <div class="container">
    <a href="<?= base_url('index.php') ?>" class="logo"><img src="<?= base_url('assets/img/logo.png') ?>" class="logo-img" alt="Ruang Baca"> Ruang Baca</a>
    <nav class="nav-main">
      <a href="<?= base_url('index.php') ?>" class="<?= ($active ?? '')=='beranda'?'active':'' ?>">Beranda</a>
      <a href="<?= base_url('katalog.php') ?>" class="<?= ($active ?? '')=='katalog'?'active':'' ?>">Katalog</a>
      <a href="<?= base_url('about.php') ?>" class="<?= ($active ?? '')=='about'?'active':'' ?>">Tentang kami</a>
      <a href="<?= base_url('contact.php') ?>" class="<?= ($active ?? '')=='kontak'?'active':'' ?>">Kontak</a>
    </nav>
    <div class="nav-actions">
      <?php if (is_logged_in()): ?>
        <a href="<?= base_url('pesanan_saya.php') ?>" class="cart-icon" title="Pesanan Saya"><?= icon('package', 23) ?></a>
      <?php endif; ?>
      <a href="<?= base_url('cart.php') ?>" class="cart-icon"><?= icon('cart', 25) ?>
        <?php if (cart_count() > 0): ?><span class="cart-badge"><?= cart_count() ?></span><?php endif; ?>
      </a>
      <?php if (is_logged_in()): ?>
        <a href="<?= base_url('logout.php') ?>" class="btn btn-outline-light btn-sm">Keluar (<?= htmlspecialchars($_SESSION['nama']) ?>)</a>
      <?php else: ?>
        <a href="<?= base_url('login.php') ?>" class="btn btn-gold btn-sm">Masuk</a>
      <?php endif; ?>
    </div>
  </div>
</header>
<?php if (!empty($_SESSION['flash_cart'])): ?>
  <div class="flash-cart"><?= icon('cart', 16) ?> <?= htmlspecialchars($_SESSION['flash_cart']) ?></div>
  <?php unset($_SESSION['flash_cart']); ?>
<?php endif; ?>

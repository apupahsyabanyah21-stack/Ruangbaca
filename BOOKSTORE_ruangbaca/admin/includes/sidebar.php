<aside class="sidebar">
  <div class="brand">
    <img src="<?= base_url('assets/img/logo.png') ?>" alt="Ruang Baca">
    <span class="txt">Ruang Baca</span>
  </div>
  <nav>
    <a href="dashboard.php" class="<?= $active=='dashboard'?'active':'' ?>"><?= icon('home', 18) ?> <span class="txt">Dashboard</span></a>
    <a href="buku.php" class="<?= $active=='buku'?'active':'' ?>"><?= icon('book-open', 18) ?> <span class="txt">Buku</span></a>
    <a href="kategori.php" class="<?= $active=='kategori'?'active':'' ?>"><?= icon('tag', 18) ?> <span class="txt">Kategori</span></a>
    <a href="pengguna.php" class="<?= $active=='pengguna'?'active':'' ?>"><?= icon('user', 18) ?> <span class="txt">Pengguna</span></a>
    <a href="pesanan.php" class="<?= $active=='pesanan'?'active':'' ?>"><?= icon('package', 18) ?> <span class="txt">Pesanan</span></a>
    <a href="pesan_masuk.php" class="<?= $active=='pesan_masuk'?'active':'' ?>"><?= icon('mail', 18) ?> <span class="txt">Pesan Masuk</span></a>
  </nav>
  <div class="logout-link">
    <a href="logout.php"><?= icon('logout', 18) ?> <span class="txt">Keluar</span></a>
  </div>
</aside>

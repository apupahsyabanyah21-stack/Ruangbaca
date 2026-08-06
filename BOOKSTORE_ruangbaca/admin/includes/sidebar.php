<aside class="sidebar">
  <div class="brand">📚 <span class="txt">Ruang Baca</span></div>
  <nav>
    <a href="dashboard.php" class="<?= $active=='dashboard'?'active':'' ?>">📊 <span class="txt">Dashboard</span></a>
    <a href="buku.php" class="<?= $active=='buku'?'active':'' ?>">📖 <span class="txt">Buku</span></a>
    <a href="kategori.php" class="<?= $active=='kategori'?'active':'' ?>">🏷️ <span class="txt">Kategori</span></a>
    <a href="pengguna.php" class="<?= $active=='pengguna'?'active':'' ?>">👥 <span class="txt">Pengguna</span></a>
    <a href="pesanan.php" class="<?= $active=='pesanan'?'active':'' ?>">🧾 <span class="txt">Pesanan</span></a>
    <a href="pesan_masuk.php" class="<?= $active=='pesan_masuk'?'active':'' ?>">✉️ <span class="txt">Pesan Masuk</span></a>
  </nav>
  <div class="logout-link">
    <a href="logout.php">↩️ <span class="txt">Keluar</span></a>
  </div>
</aside>

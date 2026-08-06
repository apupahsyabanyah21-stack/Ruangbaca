<?php
$page_title = 'Beranda';
$active = 'beranda';
require_once __DIR__ . '/includes/header.php';

$kategoriList = mysqli_query($conn, "SELECT * FROM kategori ORDER BY id ASC");
$rekomendasi = mysqli_query($conn, "SELECT b.*, k.nama_kategori FROM buku b LEFT JOIN kategori k ON b.kategori_id=k.id ORDER BY b.rating DESC LIMIT 3");
$terbaru = mysqli_query($conn, "SELECT b.*, k.nama_kategori FROM buku b LEFT JOIN kategori k ON b.kategori_id=k.id ORDER BY b.created_at DESC LIMIT 3");
?>

<section class="hero">
  <div class="container">
    <div class="eyebrow">SELAMAT DATANG DI</div>
    <h1>TOKO BUKU</h1>
    <h2 class="sub">Terpercaya, Yuk Temukan Bukumu Di Sini</h2>
    <a href="<?= base_url('katalog.php') ?>" class="btn btn-primary">Jelajahi Katalog →</a>
    <a href="<?= base_url('register.php') ?>" class="btn btn-outline-light">Daftar Sekarang</a>
  </div>
</section>
<div class="spine-strip"></div>

<section class="section">
  <div class="container">
    <div class="section-title">
      <h2>Kategori Buku</h2>
      <p>Temukan berbagai jenis buku favoritmu</p>
    </div>
    <div class="grid grid-3">
      <?php while ($k = mysqli_fetch_assoc($kategoriList)): ?>
        <a href="<?= base_url('katalog.php?kategori=' . $k['id']) ?>" class="kategori-card card">
          <div class="ic"><?= htmlspecialchars($k['icon']) ?></div>
          <h4><?= htmlspecialchars($k['nama_kategori']) ?></h4>
          <span class="tag-btn"><?= htmlspecialchars($k['nama_kategori']) ?></span>
        </a>
      <?php endwhile; ?>
    </div>
  </div>
</section>

<div class="trust-strip">
  <div class="container trust-grid">
    <div><div class="ic">🛡️</div><div class="t">Buku Original</div><div class="d">100% buku original dan berkualitas</div></div>
    <div><div class="ic">🚚</div><div class="t">Pengiriman Cepat</div><div class="d">Dikirim ke seluruh Indonesia</div></div>
    <div><div class="ic">🏷️</div><div class="t">Harga Terbaik</div><div class="d">Dapatkan harga terbaik setiap hari</div></div>
    <div><div class="ic">🎧</div><div class="t">Layanan 24/7</div><div class="d">Kami siap membantu</div></div>
  </div>
</div>

<section class="section">
  <div class="container">
    <div class="section-head">
      <div>
        <div class="eyebrow">Rekomendasi</div>
        <h2>Buku Pilihan Untukmu</h2>
      </div>
      <a href="<?= base_url('katalog.php') ?>" class="see-all">Lihat Semua →</a>
    </div>
    <div class="grid grid-3">
      <?php while ($b = mysqli_fetch_assoc($rekomendasi)): ?>
        <div class="book-card card">
          <div class="book-cover"><?= $b['gambar'] ? '<img src="'.base_url('uploads/'.$b['gambar']).'">' : '📖' ?></div>
          <div class="book-info">
            <h4><?= htmlspecialchars($b['judul']) ?></h4>
            <div class="penulis"><?= htmlspecialchars($b['penulis']) ?></div>
            <div class="stars"><?= render_stars($b['rating']) ?><span class="val"><?= number_format($b['rating'],1) ?></span></div>
            <div class="harga"><?= rupiah($b['harga']) ?></div>
            <div class="row-btn">
              <span class="kategori-tag"><?= htmlspecialchars($b['nama_kategori'] ?? '-') ?></span>
              <form method="post" action="<?= base_url('cart.php') ?>">
                <input type="hidden" name="buku_id" value="<?= $b['id'] ?>">
                <button type="submit" name="add_to_cart" class="btn btn-primary btn-sm">🛒 Keranjang</button>
              </form>
            </div>
          </div>
        </div>
      <?php endwhile; ?>
    </div>
  </div>
</section>

<section class="section" style="padding-top:0;">
  <div class="container">
    <div class="section-head">
      <div>
        <div class="eyebrow">Terbaru</div>
        <h2>Koleksi Terbaru</h2>
      </div>
      <a href="<?= base_url('katalog.php') ?>" class="see-all">Lihat Semua →</a>
    </div>
    <div class="grid grid-3">
      <?php while ($b = mysqli_fetch_assoc($terbaru)): ?>
        <div class="book-card card">
          <div class="book-cover"><?= $b['gambar'] ? '<img src="'.base_url('uploads/'.$b['gambar']).'">' : '📖' ?></div>
          <div class="book-info">
            <h4><?= htmlspecialchars($b['judul']) ?></h4>
            <div class="penulis"><?= htmlspecialchars($b['penulis']) ?></div>
            <div class="stars"><?= render_stars($b['rating']) ?><span class="val"><?= number_format($b['rating'],1) ?></span></div>
            <div class="desc"><?= htmlspecialchars($b['deskripsi']) ?></div>
            <div class="row-btn">
              <span class="kategori-tag"><?= htmlspecialchars($b['nama_kategori'] ?? '-') ?></span>
              <form method="post" action="<?= base_url('cart.php') ?>">
                <input type="hidden" name="buku_id" value="<?= $b['id'] ?>">
                <button type="submit" name="add_to_cart" class="btn btn-primary btn-sm">🛒 Keranjang</button>
              </form>
            </div>
          </div>
        </div>
      <?php endwhile; ?>
    </div>
  </div>
</section>

<section class="section" style="padding-top:0;">
  <div class="container">
    <div class="promo-banner">
      <div class="eyebrow">PENAWARAN SPESIAL</div>
      <h2>Gratis Ongkir untuk Pembelian</h2>
      <p>Belanja lebih banyak, hemat lebih banyak!!</p>
      <a href="<?= base_url('katalog.php') ?>" class="btn btn-primary">Mulai Belanja</a>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

<?php
$page_title = 'Beranda';
$active = 'beranda';
require_once __DIR__ . '/includes/header.php';

$kategoriList = mysqli_query($conn, "SELECT * FROM kategori ORDER BY id ASC");
$rekomendasi = mysqli_query($conn, "SELECT b.*, k.nama_kategori FROM buku b LEFT JOIN kategori k ON b.kategori_id=k.id WHERE b.stok > 0 ORDER BY b.rating DESC LIMIT 4");
$terbaru = mysqli_query($conn, "SELECT b.*, k.nama_kategori FROM buku b LEFT JOIN kategori k ON b.kategori_id=k.id WHERE b.stok > 0 ORDER BY b.created_at DESC LIMIT 4");
$totalBuku = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) c FROM buku WHERE stok > 0"))['c'] ?? 0;

function render_book_card($b, $isFirst) {
    $img = $b['gambar'] ? '<img src="'.base_url('uploads/'.$b['gambar']).'" alt="'.htmlspecialchars($b['judul']).'">' : icon('book-open', 40);
    $tagClass = kategori_tag_class($b['kategori_id'] ?? 1);
    ob_start(); ?>
    <div class="book-card card">
      <div class="book-cover">
        <?= $img ?>
        <?php if ($isFirst): ?><span class="book-badge">Pilihan</span><?php endif; ?>
      </div>
      <div class="book-info">
        <div class="top-row">
          <div class="stars"><?= render_stars($b['rating']) ?><span class="val"><?= number_format($b['rating'],1) ?></span></div>
          <span class="kategori-tag <?= $tagClass ?>"><?= htmlspecialchars($b['nama_kategori'] ?? '-') ?></span>
        </div>
        <h4><?= htmlspecialchars($b['judul']) ?></h4>
        <div class="penulis"><?= htmlspecialchars($b['penulis']) ?></div>
        <div class="stok-info<?= $b['stok'] <= 5 ? ' low' : '' ?>">Stok: <?= (int)$b['stok'] ?></div>
        <div class="bottom-row">
          <div class="harga"><?= rupiah($b['harga']) ?></div>
          <form method="post" action="<?= base_url('cart.php') ?>" class="add-to-cart-form">
            <input type="hidden" name="buku_id" value="<?= $b['id'] ?>">
            <input type="hidden" name="kembali_ke" value="<?= htmlspecialchars($_SERVER['REQUEST_URI']) ?>">
            <button type="submit" name="add_to_cart" class="btn btn-primary btn-sm"><?= icon('cart', 15) ?> Keranjang</button>
          </form>
        </div>
      </div>
    </div>
    <?php return ob_get_clean();
}
?>

<section class="hero">
  <div class="container">
    <div class="eyebrow">SELAMAT DATANG DI</div>
    <h1><span class="line">Ruang Baca</span><span class="accent">Toko Buku</span></h1>
    <h2 class="hero-sub">Terpercaya, Yuk Temukan Bukumu Di Sini</h2>
    <form class="hero-search" action="<?= base_url('katalog.php') ?>" method="get">
      <input type="text" name="search" placeholder="Cari judul buku atau penulis...">
      <button type="submit"><?= icon('search', 18) ?></button>
    </form>
    <a href="<?= base_url('katalog.php') ?>" class="btn btn-primary">Jelajahi Katalog <?= icon('arrow-right', 15) ?></a>
    <a href="<?= base_url('register.php') ?>" class="btn btn-outline-light">Daftar Sekarang</a>
  </div>
</section>

<div class="trust-strip">
  <div class="container trust-row">
    <div class="item"><?= icon('book-open', 17) ?> <?= (int)$totalBuku ?>+ Judul Buku</div>
    <div class="divider"></div>
    <div class="item"><?= icon('truck', 17) ?> Gratis Ongkir &ge; Rp 200.000</div>
    <div class="divider"></div>
    <div class="item"><?= icon('tag', 17) ?> Bayar di Tempat (COD)</div>
    <div class="divider"></div>
    <div class="item"><?= icon('shield', 17) ?> Pengiriman ke Seluruh Indonesia</div>
  </div>
</div>

<section class="section">
  <div class="container">
    <div class="section-title">
      <div class="eyebrow">JELAJAHI KOLEKSI</div>
      <h2>Kategori Buku</h2>
    </div>
    <div class="grid grid-3 grid-kategori">
      <?php while ($k = mysqli_fetch_assoc($kategoriList)): ?>
        <a href="<?= base_url('katalog.php?kategori=' . $k['id']) ?>" class="kategori-card card">
          <div class="ic"><?= icon(kategori_icon($k['nama_kategori']), 24) ?></div>
          <h4><?= htmlspecialchars($k['nama_kategori']) ?></h4>
          <div class="d"><?= htmlspecialchars($k['deskripsi'] ?? '') ?></div>
        </a>
      <?php endwhile; ?>
    </div>
  </div>
</section>

<style>
/* Bagian Pilihan Editor & Koleksi Terbaru selebar header (kiri sejajar logo, kanan sejajar tombol Keluar) */
.container.container-wide { max-width: 1400px; padding-left: 40px; padding-right: 40px; }
@media (max-width: 700px) { .container.container-wide { padding-left: 24px; padding-right: 24px; } }
</style>
<section class="section" style="padding-top:0;">
  <div class="container container-wide">
    <div class="section-head">
      <div>
        <div class="eyebrow">Rekomendasi</div>
        <h2>Buku Pilihan Editor</h2>
      </div>
      <a href="<?= base_url('katalog.php') ?>" class="see-all">Lihat Semua <?= icon('arrow-right', 14) ?></a>
    </div>
    <div class="grid grid-4">
      <?php $i = 0; while ($b = mysqli_fetch_assoc($rekomendasi)): ?>
        <?= render_book_card($b, $i === 0) ?>
      <?php $i++; endwhile; ?>
    </div>
  </div>
</section>

<section class="section" style="padding-top:0;padding-bottom:0;">
  <div class="container container-wide">
    <div class="section-head">
      <div>
        <div class="eyebrow">Terbaru</div>
        <h2>Koleksi Terbaru</h2>
      </div>
      <a href="<?= base_url('katalog.php') ?>" class="see-all">Lihat Semua <?= icon('arrow-right', 14) ?></a>
    </div>
    <div class="grid grid-4" style="margin-bottom:0;">
      <?php $i = 0; while ($b = mysqli_fetch_assoc($terbaru)): ?>
        <?= render_book_card($b, $i === 0) ?>
      <?php $i++; endwhile; ?>
    </div>
  </div>
</section>

<section class="promo-banner">
  <div class="container" style="text-align:center;">
    <div class="eyebrow">PENAWARAN SPESIAL</div>
    <h2>Gratis Ongkir untuk Pembelian <span>&ge; Rp 200.000</span></h2>
    <p>Belanja lebih banyak, hemat lebih banyak! Nikmati gratis ongkos kirim untuk setiap transaksi di atas Rp 200.000.</p>
    <a href="<?= base_url('katalog.php') ?>" class="btn btn-gold">Mulai Belanja</a>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

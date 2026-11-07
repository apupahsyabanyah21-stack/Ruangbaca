<?php
$page_title = 'Katalog Buku';
$active = 'katalog';
require_once __DIR__ . '/includes/header.php';

$q = isset($_GET['q']) ? clean($conn, $_GET['q']) : '';
$kategori_id = isset($_GET['kategori']) ? (int)$_GET['kategori'] : 0;

$sql = "SELECT b.*, k.nama_kategori FROM buku b LEFT JOIN kategori k ON b.kategori_id = k.id WHERE b.stok > 0";
if ($q !== '') {
    $sql .= " AND (b.judul LIKE '%$q%' OR b.penulis LIKE '%$q%')";
}
if ($kategori_id > 0) {
    $sql .= " AND b.kategori_id = $kategori_id";
}
$sql .= " ORDER BY b.created_at DESC";
$result = mysqli_query($conn, $sql);
$kategoriList = mysqli_query($conn, "SELECT * FROM kategori ORDER BY nama_kategori ASC");
$kategoriArr = [];
while ($k = mysqli_fetch_assoc($kategoriList)) $kategoriArr[] = $k;
?>
<div class="container">
  <div class="page-hero">
    <div class="eyebrow" style="color:var(--gold);font-weight:700;font-size:13px;letter-spacing:1.5px;text-transform:uppercase;margin-bottom:8px;">Semua Koleksi</div>
    <h1>Katalog Buku</h1>
  </div>

  <form method="get" class="search-bar" style="margin:20px 0 6px;">
    <?php if ($kategori_id > 0): ?><input type="hidden" name="kategori" value="<?= $kategori_id ?>"><?php endif; ?>
    <input type="search" name="q" placeholder="Cari judul atau penulis..." value="<?= htmlspecialchars($q) ?>">
    <button type="submit"><?= icon('search', 18) ?></button>
  </form>

  <div class="filter-pills">
    <a href="?q=<?= urlencode($q) ?>&kategori=0" class="<?= $kategori_id==0?'active':'' ?>">Semua</a>
    <?php foreach ($kategoriArr as $k): ?>
      <a href="?q=<?= urlencode($q) ?>&kategori=<?= $k['id'] ?>" class="<?= $kategori_id==$k['id']?'active':'' ?>"><?= icon(kategori_icon($k['nama_kategori']), 15) ?> <?= htmlspecialchars($k['nama_kategori']) ?></a>
    <?php endforeach; ?>
  </div>

  <p style="color:var(--text-muted);margin:16px 0;font-size:14px;">Menampilkan <strong><?= mysqli_num_rows($result) ?></strong> buku</p>

  <div class="grid grid-4" style="margin-bottom:50px;">
    <?php if (mysqli_num_rows($result) === 0): ?>
      <p style="grid-column:1/-1;text-align:center;color:var(--text-muted);padding:40px 0;">Buku tidak ditemukan.</p>
    <?php endif; ?>
    <?php $i = 0; while ($b = mysqli_fetch_assoc($result)): ?>
      <div class="book-card card">
        <a href="<?= base_url('buku_detail.php?id='.$b['id']) ?>">
          <div class="book-cover">
            <?= $b['gambar'] ? '<img src="'.base_url('uploads/'.$b['gambar']).'" alt="'.htmlspecialchars($b['judul']).'">' : icon('book-open', 40) ?>
            <?php if ($i === 0): ?><span class="book-badge">Pilihan</span><?php endif; ?>
          </div>
        </a>
        <div class="book-info">
          <div class="top-row">
            <div class="stars"><?= render_stars($b['rating']) ?><span class="val"><?= number_format($b['rating'],1) ?></span></div>
            <span class="kategori-tag <?= kategori_tag_class($b['kategori_id'] ?? 1) ?>"><?= htmlspecialchars($b['nama_kategori'] ?? '-') ?></span>
          </div>
          <a href="<?= base_url('buku_detail.php?id='.$b['id']) ?>"><h4><?= htmlspecialchars($b['judul']) ?></h4></a>
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
    <?php $i++; endwhile; ?>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

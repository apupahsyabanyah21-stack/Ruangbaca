<?php
$page_title = 'Katalog Buku';
$active = 'katalog';
require_once __DIR__ . '/includes/header.php';

$q = isset($_GET['q']) ? clean($conn, $_GET['q']) : '';
$kategori_id = isset($_GET['kategori']) ? (int)$_GET['kategori'] : 0;

$sql = "SELECT b.*, k.nama_kategori FROM buku b LEFT JOIN kategori k ON b.kategori_id = k.id WHERE 1=1";
if ($q !== '') {
    $sql .= " AND (b.judul LIKE '%$q%' OR b.penulis LIKE '%$q%')";
}
if ($kategori_id > 0) {
    $sql .= " AND b.kategori_id = $kategori_id";
}
$sql .= " ORDER BY b.created_at DESC";
$result = mysqli_query($conn, $sql);
$kategoriList = mysqli_query($conn, "SELECT * FROM kategori ORDER BY nama_kategori ASC");
?>
<div class="page-hero"><h1>Katalog Buku</h1></div>

<div class="container">
  <form method="get" class="filter-bar">
    <input type="search" name="q" placeholder="Cari judul atau penulis..." value="<?= htmlspecialchars($q) ?>">
    <select name="kategori" onchange="this.form.submit()">
      <option value="0">Semua Kategori</option>
      <?php mysqli_data_seek($kategoriList, 0); while ($k = mysqli_fetch_assoc($kategoriList)): ?>
        <option value="<?= $k['id'] ?>" <?= $kategori_id == $k['id'] ? 'selected' : '' ?>><?= htmlspecialchars($k['nama_kategori']) ?></option>
      <?php endwhile; ?>
    </select>
    <button type="submit" class="btn btn-primary">Cari</button>
  </form>

  <p style="color:var(--text-muted);margin-bottom:16px;font-size:14px;"><?= mysqli_num_rows($result) ?> buku ditemukan</p>

  <div class="grid grid-4" style="margin-bottom:50px;">
    <?php if (mysqli_num_rows($result) === 0): ?>
      <p style="grid-column:1/-1;text-align:center;color:var(--text-muted);padding:40px 0;">Buku tidak ditemukan.</p>
    <?php endif; ?>
    <?php while ($b = mysqli_fetch_assoc($result)): ?>
      <div class="book-card card">
        <div class="book-cover"><?= $b['gambar'] ? '<img src="'.base_url('uploads/'.$b['gambar']).'">' : '📖' ?></div>
        <div class="book-info">
          <span class="kategori-tag"><?= htmlspecialchars($b['nama_kategori'] ?? '-') ?></span>
          <h4><?= htmlspecialchars($b['judul']) ?></h4>
          <div class="penulis">oleh <?= htmlspecialchars($b['penulis']) ?></div>
          <div class="harga"><?= rupiah($b['harga']) ?></div>
          <div class="row-btn">
            <a href="<?= base_url('buku_detail.php?id='.$b['id']) ?>" class="btn btn-outline btn-sm">Detail</a>
            <form method="post" action="<?= base_url('cart.php') ?>">
              <input type="hidden" name="buku_id" value="<?= $b['id'] ?>">
              <button type="submit" name="add_to_cart" class="btn btn-primary btn-sm">+ Keranjang</button>
            </form>
          </div>
        </div>
      </div>
    <?php endwhile; ?>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

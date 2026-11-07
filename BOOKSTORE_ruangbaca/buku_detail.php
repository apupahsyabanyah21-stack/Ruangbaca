<?php
require_once __DIR__ . '/includes/functions.php';
$id = (int)($_GET['id'] ?? 0);
$res = mysqli_query($conn, "SELECT b.*, k.nama_kategori FROM buku b LEFT JOIN kategori k ON b.kategori_id=k.id WHERE b.id=$id");
$b = mysqli_fetch_assoc($res);
if (!$b) { header('Location: ' . base_url('katalog.php')); exit; }
$page_title = $b['judul'];
require_once __DIR__ . '/includes/header.php';
?>
<div class="container section">
  <div class="grid" style="grid-template-columns: 320px 1fr; gap:40px;">
    <div class="book-cover card" style="height:420px;font-size:70px;">
      <?= $b['gambar'] ? '<img src="'.base_url('uploads/'.$b['gambar']).'">' : icon('book-open', 40) ?>
    </div>
    <div>
      <span class="kategori-tag"><?= htmlspecialchars($b['nama_kategori'] ?? '-') ?></span>
      <h1 style="margin:8px 0 4px;"><?= htmlspecialchars($b['judul']) ?></h1>
      <p style="color:var(--text-muted);margin-bottom:14px;">oleh <?= htmlspecialchars($b['penulis']) ?></p>
      <div class="harga" style="font-size:26px;margin-bottom:16px;"><?= rupiah($b['harga']) ?></div>
      <p style="margin-bottom:20px;line-height:1.8;"><?= nl2br(htmlspecialchars($b['deskripsi'])) ?></p>
      <p style="font-size:13px;color:var(--text-muted);margin-bottom:20px;">Stok tersedia: <?= (int)$b['stok'] ?></p>
      <form method="post" action="<?= base_url('cart.php') ?>" class="add-to-cart-form" style="display:inline-block;">
        <input type="hidden" name="buku_id" value="<?= $b['id'] ?>">
        <input type="hidden" name="kembali_ke" value="<?= htmlspecialchars($_SERVER['REQUEST_URI']) ?>">
        <button type="submit" name="add_to_cart" class="btn btn-primary" <?= $b['stok']<1?'disabled':'' ?>>+ Tambah ke Keranjang</button>
      </form>
      <a href="<?= base_url('katalog.php') ?>" class="btn btn-outline">Kembali ke Katalog</a>
    </div>
  </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>

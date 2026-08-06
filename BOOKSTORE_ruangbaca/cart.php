<?php
$page_title = 'Keranjang Belanja';
require_once __DIR__ . '/includes/functions.php';

if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];

// Tambah ke keranjang
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    require_login();
    $buku_id = (int)$_POST['buku_id'];
    if (isset($_SESSION['cart'][$buku_id])) {
        $_SESSION['cart'][$buku_id]['jumlah'] += 1;
    } else {
        $_SESSION['cart'][$buku_id] = ['jumlah' => 1];
    }
    header('Location: ' . base_url('cart.php'));
    exit;
}

// Update jumlah
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_qty'])) {
    $buku_id = (int)$_POST['buku_id'];
    $jumlah = max(1, (int)$_POST['jumlah']);
    if (isset($_SESSION['cart'][$buku_id])) $_SESSION['cart'][$buku_id]['jumlah'] = $jumlah;
    header('Location: ' . base_url('cart.php'));
    exit;
}

// Hapus item
if (isset($_GET['remove'])) {
    unset($_SESSION['cart'][(int)$_GET['remove']]);
    header('Location: ' . base_url('cart.php'));
    exit;
}

require_login();
require_once __DIR__ . '/includes/header.php';

$items = [];
$total = 0;
if (!empty($_SESSION['cart'])) {
    $ids = implode(',', array_map('intval', array_keys($_SESSION['cart'])));
    $res = mysqli_query($conn, "SELECT * FROM buku WHERE id IN ($ids)");
    while ($row = mysqli_fetch_assoc($res)) {
        $jumlah = $_SESSION['cart'][$row['id']]['jumlah'];
        $subtotal = $jumlah * $row['harga'];
        $total += $subtotal;
        $items[] = array_merge($row, ['jumlah' => $jumlah, 'subtotal' => $subtotal]);
    }
}
?>
<div class="container section">
  <h2 style="margin-bottom:20px;">Keranjang Belanja (<?= cart_count() ?> item)</h2>
  <?php if (empty($items)): ?>
    <div class="card" style="padding:50px;text-align:center;color:var(--text-muted);">
      Keranjang kamu masih kosong. <a href="<?= base_url('katalog.php') ?>" style="color:var(--gold-dark);font-weight:600;">Yuk cari buku</a>
    </div>
  <?php else: ?>
    <div class="grid" style="grid-template-columns: 2fr 1fr; gap:30px; align-items:start;">
      <div class="card" style="padding:20px;">
        <?php foreach ($items as $it): ?>
          <div class="cart-item">
            <div class="cover"><?= $it['gambar'] ? '<img src="'.base_url('uploads/'.$it['gambar']).'">' : '📖' ?></div>
            <div class="info">
              <h4><?= htmlspecialchars($it['judul']) ?></h4>
              <div style="font-size:13px;color:var(--text-muted);"><?= rupiah($it['harga']) ?></div>
            </div>
            <form method="post" class="qty-box">
              <input type="hidden" name="buku_id" value="<?= $it['id'] ?>">
              <input type="number" name="jumlah" value="<?= $it['jumlah'] ?>" min="1" max="<?= $it['stok'] ?>" style="width:50px;text-align:center;border:1px solid var(--border);border-radius:4px;">
              <button type="submit" name="update_qty" class="btn btn-outline btn-sm">Ubah</button>
            </form>
            <div style="font-weight:700;width:110px;text-align:right;"><?= rupiah($it['subtotal']) ?></div>
            <a href="?remove=<?= $it['id'] ?>" style="color:#A13B25;font-size:13px;margin-left:12px;">Hapus</a>
          </div>
        <?php endforeach; ?>
      </div>
      <div class="card" style="padding:22px;">
        <h3 style="margin-bottom:14px;">Ringkasan Belanja</h3>
        <div class="summary-row"><span>Subtotal</span><span><?= rupiah($total) ?></span></div>
        <div class="summary-row"><span>Ongkos Kirim</span><span>Gratis</span></div>
        <div class="summary-row total"><span>Total</span><span><?= rupiah($total) ?></span></div>
        <a href="<?= base_url('checkout.php') ?>" class="btn btn-primary btn-block" style="margin-top:16px;">Checkout Sekarang</a>
      </div>
    </div>
  <?php endif; ?>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>

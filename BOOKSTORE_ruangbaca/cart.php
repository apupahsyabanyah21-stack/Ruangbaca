<?php
$page_title = 'Keranjang Belanja';
require_once __DIR__ . '/includes/functions.php';

if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];

// Tambah ke keranjang
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    $buku_id = (int)$_POST['buku_id'];

    if (!is_logged_in()) {
        $_SESSION['pending_add_to_cart'] = $buku_id;
        require_login();
    }

    if (isset($_SESSION['cart'][$buku_id])) {
        $_SESSION['cart'][$buku_id]['jumlah'] += 1;
    } else {
        $_SESSION['cart'][$buku_id] = ['jumlah' => 1];
    }
    $_SESSION['flash_cart'] = 'Buku berhasil ditambahkan ke keranjang.';

    // Kembali ke halaman asal (tempat tombol Keranjang diklik), bukan ke halaman checkout
    $kembali = $_POST['kembali_ke'] ?? ($_SERVER['HTTP_REFERER'] ?? base_url('index.php'));
    header('Location: ' . $kembali);
    exit;
}

// Ubah jumlah (tombol - / +)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_qty'])) {
    require_login();
    $buku_id = (int)$_POST['buku_id'];
    $delta = (int)$_POST['delta'];
    if (isset($_SESSION['cart'][$buku_id])) {
        $baru = $_SESSION['cart'][$buku_id]['jumlah'] + $delta;
        $_SESSION['cart'][$buku_id]['jumlah'] = max(1, $baru);
    }
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
    $res = mysqli_query($conn, "SELECT b.*, k.nama_kategori FROM buku b LEFT JOIN kategori k ON b.kategori_id=k.id WHERE b.id IN ($ids)");
    while ($row = mysqli_fetch_assoc($res)) {
        $jumlah = $_SESSION['cart'][$row['id']]['jumlah'];
        $subtotal = $jumlah * $row['harga'];
        $total += $subtotal;
        $items[] = array_merge($row, ['jumlah' => $jumlah, 'subtotal' => $subtotal]);
    }
}
$ongkir = 0;
$grandTotal = $total + $ongkir;
?>
<div class="container section">
  <h2 style="margin-bottom:24px;">Keranjang Belanja (<span style="color:var(--orange-dark);"><?= cart_count() ?></span> item)</h2>
  <?php if (empty($items)): ?>
    <div class="card" style="padding:50px;text-align:center;color:var(--text-muted);">
      Keranjang kamu masih kosong. <a href="<?= base_url('katalog.php') ?>" style="color:var(--orange-dark);font-weight:600;">Yuk cari buku</a>
    </div>
  <?php else: ?>

    <div class="cart-layout">
      <div class="cart-list-card">
        <?php foreach ($items as $it): ?>
          <div class="cart-item-row">
            <div class="thumb"><?= $it['gambar'] ? '<img src="'.base_url('uploads/'.$it['gambar']).'">' : icon('book-open', 28) ?></div>
            <div class="info">
              <h4><?= htmlspecialchars($it['judul']) ?></h4>
              <div class="author"><?= htmlspecialchars($it['penulis']) ?></div>
              <div class="stars"><?= render_stars($it['rating']) ?></div>
            </div>
            <div class="price"><?= rupiah($it['harga']) ?></div>
            <div class="qty-pill">
              <form method="post">
                <input type="hidden" name="buku_id" value="<?= $it['id'] ?>">
                <input type="hidden" name="delta" value="-1">
                <button type="submit" name="change_qty">&minus;</button>
              </form>
              <span class="val"><?= $it['jumlah'] ?></span>
              <form method="post">
                <input type="hidden" name="buku_id" value="<?= $it['id'] ?>">
                <input type="hidden" name="delta" value="1">
                <button type="submit" name="change_qty">&plus;</button>
              </form>
            </div>
            <a href="?remove=<?= $it['id'] ?>" class="cart-delete-icon" title="Hapus"><?= icon('trash', 18) ?></a>
          </div>
        <?php endforeach; ?>
      </div>

      <div class="cart-summary-col">
        <div class="order-lines cart-summary-sticky">
          <h3 style="font-size:19px;margin-bottom:16px;">Ringkasan Order</h3>
          <div class="row"><span>Subtotal (<?= cart_count() ?> item)</span><span><?= rupiah($total) ?></span></div>
          <div class="row"><span>Ongkos Kirim</span><span><?= $ongkir > 0 ? rupiah($ongkir) : 'Gratis' ?></span></div>
          <hr>
          <div class="row total"><span>Total</span><span><?= rupiah($grandTotal) ?></span></div>
          <a href="<?= base_url('checkout.php') ?>" class="btn-checkout-dark"><?= icon('lock', 15) ?> Lanjut Checkout</a>
          <div class="secure-note"><?= icon('shield', 14) ?> Pembayaran aman &amp; terpercaya</div>
        </div>
      </div>
    </div>

  <?php endif; ?>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>

<?php
$page_title = 'Checkout';
require_once __DIR__ . '/includes/functions.php';
require_login();

if (empty($_SESSION['cart'])) {
    header('Location: ' . base_url('cart.php'));
    exit;
}

$items = [];
$total = 0;
$ids = implode(',', array_map('intval', array_keys($_SESSION['cart'])));
$res = mysqli_query($conn, "SELECT * FROM buku WHERE id IN ($ids)");
while ($row = mysqli_fetch_assoc($res)) {
    $jumlah = $_SESSION['cart'][$row['id']]['jumlah'];
    $subtotal = $jumlah * $row['harga'];
    $total += $subtotal;
    $items[] = array_merge($row, ['jumlah' => $jumlah, 'subtotal' => $subtotal]);
}

$userRes = mysqli_query($conn, "SELECT * FROM users WHERE id=" . (int)$_SESSION['user_id']);
$userData = mysqli_fetch_assoc($userRes);

require_once __DIR__ . '/includes/header.php';
?>
<div class="container section">
  <h2 style="margin-bottom:20px;">Checkout</h2>
  <div class="grid" style="grid-template-columns: 2fr 1fr; gap:30px; align-items:start;">
    <form method="post" action="proses_checkout.php">
      <div class="card" style="padding:22px;margin-bottom:20px;">
        <h3 style="margin-bottom:14px;">Data Penerima</h3>
        <div class="form-group">
          <label>Nama Penerima</label>
          <input type="text" name="nama" class="form-control" value="<?= htmlspecialchars($userData['nama']) ?>" required>
        </div>
        <div class="form-group">
          <label>No. Telepon</label>
          <input type="text" name="kontak" class="form-control" value="<?= htmlspecialchars($userData['kontak'] ?? '') ?>" required>
        </div>
        <div class="form-group">
          <label>Alamat Lengkap</label>
          <textarea name="alamat" class="form-control" required><?= htmlspecialchars($userData['alamat'] ?? '') ?></textarea>
        </div>
      </div>
      <div class="card" style="padding:22px;">
        <h3 style="margin-bottom:14px;">Metode Pembayaran</h3>
        <label style="display:flex;align-items:center;gap:10px;padding:12px;border:1.5px solid var(--gold);border-radius:8px;background:#FFF8ED;">
          <input type="radio" name="metode" value="Payment at Delivery" checked> 💵 Payment at Delivery (Bayar saat buku diterima)
        </label>
        <button type="submit" class="btn btn-primary btn-block" style="margin-top:20px;">Buat Pesanan</button>
      </div>
    </form>
    <div class="card" style="padding:22px;">
      <h3 style="margin-bottom:14px;">Ringkasan Pesanan</h3>
      <?php foreach ($items as $it): ?>
        <div class="summary-row"><span><?= htmlspecialchars($it['judul']) ?> x<?= $it['jumlah'] ?></span><span><?= rupiah($it['subtotal']) ?></span></div>
      <?php endforeach; ?>
      <div class="summary-row total"><span>Total Bayar</span><span><?= rupiah($total) ?></span></div>
    </div>
  </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>

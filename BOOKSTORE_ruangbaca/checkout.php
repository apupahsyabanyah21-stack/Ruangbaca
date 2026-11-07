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

$errCheckout = flash('error');

require_once __DIR__ . '/includes/header.php';
$jumlahItem = array_sum(array_column($items, 'jumlah'));
?>
<style>
.co-bg{position:relative;overflow:hidden}
.co-bg::before,.co-bg::after{content:'';position:absolute;border-radius:50%;pointer-events:none;z-index:0}
.co-bg::before{width:420px;height:420px;right:-140px;top:-60px;background:radial-gradient(circle,rgba(217,184,118,.38),rgba(217,184,118,0) 68%)}
.co-bg::after{width:360px;height:360px;left:-150px;bottom:-80px;background:radial-gradient(circle,rgba(180,134,62,.22),rgba(180,134,62,0) 68%)}
.co-bg .container{position:relative;z-index:1}
.co-title{display:flex;align-items:center;gap:12px;font-size:36px;margin-bottom:24px}
.co-title svg{color:var(--brown)}
.co-title::after{content:'';flex:1;height:1px;background:linear-gradient(90deg,var(--gold-soft),rgba(217,184,118,0))}
.co-grid{display:grid;grid-template-columns:minmax(0,1.7fr) minmax(0,1fr);gap:28px;align-items:start}
.co-card{background:#FFFDF8;border:1px solid var(--border);border-radius:18px;padding:24px;box-shadow:var(--shadow)}
.co-card + .co-card{margin-top:22px}
.co-card-title{display:flex;align-items:center;gap:12px;font-size:19px;margin-bottom:18px}
.co-ic-dark{width:34px;height:34px;border-radius:50%;background:var(--brown-dark2);color:#fff;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.co-ic{color:var(--brown);display:flex}
.co-field{margin-bottom:16px}
.co-field:last-child{margin-bottom:0}
.co-field label{display:block;font-size:13.5px;font-weight:700;margin-bottom:7px;color:var(--brown-dark2)}
.co-field input,.co-field textarea{width:100%;padding:12px 14px;border:1.5px solid var(--border);border-radius:12px;background:#fff;font:inherit;font-size:14.5px;color:var(--text-dark)}
.co-field input:focus,.co-field textarea:focus{outline:none;border-color:var(--gold);box-shadow:0 0 0 3px rgba(180,134,62,.15)}
.co-field textarea{min-height:110px;resize:vertical}
.co-summary{position:sticky;top:20px;background:#FFFDF8;border:1px solid var(--border);border-radius:18px;box-shadow:var(--shadow);overflow:hidden}
.co-sum-head{display:flex;align-items:center;gap:12px;background:var(--brown-dark2);color:#fff;padding:18px 24px;font-family:Georgia,'Playfair Display',serif;font-size:18px;font-weight:700}
.co-sum-body{padding:6px 24px 24px}
.co-item{display:flex;align-items:center;gap:14px;padding:16px 0;border-bottom:1px solid var(--border)}
.co-thumb{width:46px;height:64px;border-radius:6px;overflow:hidden;background:var(--cream-soft);display:flex;align-items:center;justify-content:center;color:var(--brown-light);flex-shrink:0;box-shadow:0 3px 8px rgba(43,27,14,.18)}
.co-thumb img{width:100%;height:100%;object-fit:cover}
.co-item-info{flex:1;min-width:0}
.co-item-title{font-weight:700;font-size:14.5px;color:var(--brown-dark2);line-height:1.35}
.co-item-sub{font-size:12.5px;color:var(--text-muted);margin-top:3px}
.co-item-price{font-weight:700;font-size:14px;white-space:nowrap}
.co-total{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:20px 0 18px}
.co-total span:first-child{font-family:Georgia,'Playfair Display',serif;font-size:19px;font-weight:700;color:var(--brown-dark2)}
.co-total-pill{background:var(--brown-dark2);color:#fff;font-weight:700;font-size:18px;padding:9px 20px;border-radius:14px}
.co-promo{position:relative;display:flex;align-items:center;gap:14px;background:var(--cream-soft);border-radius:14px;padding:16px 18px;margin-bottom:18px}
.co-promo-ic{width:46px;height:46px;border-radius:10px;background:#fff;color:var(--brown);display:flex;align-items:center;justify-content:center;flex-shrink:0}
.co-promo strong{display:block;font-size:14.5px;color:var(--brown-dark2)}
.co-promo small{color:var(--text-muted);font-size:12.5px}
.co-promo::after{content:'✦';position:absolute;right:16px;top:10px;color:var(--gold);font-size:15px}
.co-btn{display:flex;align-items:center;justify-content:center;gap:10px;width:100%;padding:16px;border:none;border-radius:999px;background:var(--brown-dark2);color:#fff;font:inherit;font-weight:700;font-size:15.5px;cursor:pointer;box-shadow:0 6px 16px rgba(43,27,14,.25);transition:background .2s ease}
.co-btn:hover{background:var(--brown)}
.co-secure{display:flex;align-items:center;justify-content:center;gap:6px;margin-top:12px;font-size:12.5px;color:var(--text-muted)}
@media (max-width:900px){.co-grid{grid-template-columns:1fr}.co-summary{position:static}.co-title{font-size:30px}}
</style>

<div class="co-bg">
<div class="container section">
  <h2 class="co-title"><?= icon('book-open', 32) ?> Checkout</h2>
  <?php if ($errCheckout): ?><div class="alert alert-error"><?= htmlspecialchars($errCheckout) ?></div><?php endif; ?>

  <div class="co-grid">
    <form id="formCheckout" method="post" action="proses_checkout.php" enctype="multipart/form-data">
      <section class="co-card">
        <h3 class="co-card-title"><span class="co-ic-dark"><?= icon('user', 18) ?></span> Data Penerima</h3>
        <div class="co-field">
          <label for="nama">Nama Penerima</label>
          <input type="text" id="nama" name="nama" value="<?= htmlspecialchars($userData['nama']) ?>" required>
        </div>
        <div class="co-field">
          <label for="kontak">No. Telepon</label>
          <input type="text" id="kontak" name="kontak" value="<?= htmlspecialchars($userData['kontak'] ?? '') ?>" placeholder="Contoh: 08123456789" required>
        </div>
        <div class="co-field">
          <label for="alamat">Alamat Lengkap</label>
          <textarea id="alamat" name="alamat" placeholder="Nama jalan, RT/RW, kelurahan, kecamatan, kota" required><?= htmlspecialchars($userData['alamat'] ?? '') ?></textarea>
        </div>
      </section>

      <section class="co-card">
        <h3 class="co-card-title"><span class="co-ic"><?= icon('wallet', 24) ?></span> Metode Pembayaran</h3>
        <?php include __DIR__ . '/includes/payment_form.php'; ?>
      </section>
    </form>

    <aside class="co-summary">
      <div class="co-sum-head"><?= icon('cart', 21) ?> Ringkasan Pesanan</div>
      <div class="co-sum-body">
        <?php foreach ($items as $it): ?>
          <div class="co-item">
            <div class="co-thumb"><?= !empty($it['gambar']) ? '<img src="' . htmlspecialchars(base_url('uploads/' . $it['gambar'])) . '" alt="">' : icon('book-open', 20) ?></div>
            <div class="co-item-info">
              <div class="co-item-title"><?= htmlspecialchars($it['judul']) ?></div>
              <div class="co-item-sub"><?= $it['jumlah'] ?> x <?= rupiah($it['harga']) ?></div>
            </div>
            <div class="co-item-price"><?= rupiah($it['subtotal']) ?></div>
          </div>
        <?php endforeach; ?>

        <div class="co-total"><span>Total Bayar</span><span class="co-total-pill"><?= rupiah($total) ?></span></div>

        <div class="co-promo">
          <div class="co-promo-ic"><?= icon('truck', 26) ?></div>
          <div><strong>Gratis Ongkir</strong><small>untuk semua pesanan, <?= $jumlahItem ?> buku siap dikirim</small></div>
        </div>

        <button type="submit" form="formCheckout" class="co-btn"><?= icon('bag', 19) ?> Buat Pesanan <?= icon('arrow-right', 17) ?></button>
        <div class="co-secure"><?= icon('shield', 14) ?> Pembayaran aman &amp; terpercaya</div>
      </div>
    </aside>
  </div>
</div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>

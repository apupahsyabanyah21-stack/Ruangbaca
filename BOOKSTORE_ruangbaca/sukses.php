<?php
$page_title = 'Pesanan Berhasil';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/payment.php';
require_login();

$id = (int)($_GET['id'] ?? 0);
$res = mysqli_query($conn, "SELECT p.*, u.nama FROM pesanan p JOIN users u ON p.user_id = u.id WHERE p.id=$id AND p.user_id=" . (int)$_SESSION['user_id']);
$pesanan = mysqli_fetch_assoc($res);

if (!$pesanan) {
    header('Location: ' . base_url('index.php'));
    exit;
}

$detail = mysqli_query($conn, "SELECT pd.*, b.judul FROM pesanan_detail pd JOIN buku b ON pd.buku_id = b.id WHERE pd.pesanan_id=$id");
$judulBuku = [];
while ($d = mysqli_fetch_assoc($detail)) {
    $judulBuku[] = $d['judul'] . ' x' . $d['jumlah'];
}

$nomorRef = str_pad($pesanan['id'], 12, '0', STR_PAD_LEFT);

require_once __DIR__ . '/includes/header.php';
?>
<div class="auth-wrap">
  <div class="card" style="max-width:480px;width:100%;padding:40px 36px;text-align:center;">
    <div style="width:56px;height:56px;border-radius:50%;background:var(--brown-dark2);color:#fff;font-size:26px;display:flex;align-items:center;justify-content:center;margin:0 auto 18px;">✔</div>
    <p style="color:var(--text-muted);font-size:15px;margin-bottom:6px;">Pesanan Berhasil!</p>
    <h2 style="font-size:30px;margin-bottom:24px;"><?= rupiah($pesanan['total']) ?></h2>
    <hr style="border:none;border-top:1px dashed var(--border);margin-bottom:20px;">

    <div style="text-align:left;font-size:14px;">
      <div class="summary-row"><span style="color:var(--text-muted);">Nomor Ref</span><span style="font-weight:600;"><?= $nomorRef ?></span></div>
      <div class="summary-row"><span style="color:var(--text-muted);">Waktu Pemesanan</span><span style="font-weight:600;"><?= date('d-m-Y, H:i', strtotime($pesanan['tanggal'])) ?></span></div>
      <div class="summary-row"><span style="color:var(--text-muted);">Metode Pembayaran</span><span style="font-weight:600;"><?= htmlspecialchars($pesanan['metode_pembayaran']) ?></span></div>
      <div class="summary-row"><span style="color:var(--text-muted);">Nama Pemesan</span><span style="font-weight:600;"><?= htmlspecialchars($pesanan['nama']) ?></span></div>
    </div>

    <hr style="border:none;border-top:1px dashed var(--border);margin:18px 0;">

    <div style="text-align:left;font-size:14px;">
      <div class="summary-row"><span style="color:var(--text-muted);">Jumlah</span><span style="font-weight:700;"><?= rupiah($pesanan['total']) ?></span></div>
      <div class="summary-row" style="align-items:flex-start;">
        <span style="color:var(--text-muted);">Detail Buku</span>
        <span style="font-weight:600;text-align:right;max-width:65%;"><?= implode('<br>', array_map('htmlspecialchars', $judulBuku)) ?></span>
      </div>
    </div>

    <?php render_info_pembayaran($pesanan); ?>

    <a href="<?= base_url('cetak_struk.php?id=' . (int)$pesanan['id'] . '&auto=1') ?>" target="_blank" rel="noopener" class="btn btn-outline btn-block" style="margin-top:22px;display:flex;align-items:center;justify-content:center;gap:8px;"><?= icon('printer', 18) ?> Cetak PDF Bukti Pembayaran</a>
    <a href="<?= base_url('katalog.php') ?>" class="btn btn-primary btn-block" style="margin-top:10px;">Lanjut Belanja</a>
    <a href="<?= base_url('index.php') ?>" class="btn btn-outline btn-block" style="margin-top:10px;">Kembali ke Beranda</a>
  </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>

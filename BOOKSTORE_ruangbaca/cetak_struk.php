<?php
// Bukti pembayaran / struk yang bisa dicetak atau disimpan sebagai PDF.
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/payment.php';
require_login();

// ---- DATA ----
$id = (int)($_GET['id'] ?? 0);
$filterUser = is_admin() ? '' : ' AND p.user_id=' . (int)$_SESSION['user_id'];
$res = mysqli_query($conn, "SELECT p.*, u.nama, u.kontak, " . SQL_URUT_TRANSAKSI . " FROM pesanan p JOIN users u ON p.user_id = u.id WHERE p.id=$id" . $filterUser);
$pesanan = mysqli_fetch_assoc($res);
if (!$pesanan) {
    header('Location: ' . base_url('index.php'));
    exit;
}
$rows = [];
$detail = mysqli_query($conn, "SELECT pd.jumlah, pd.harga_satuan, b.judul FROM pesanan_detail pd JOIN buku b ON pd.buku_id = b.id WHERE pd.pesanan_id=$id");
while ($d = mysqli_fetch_assoc($detail)) $rows[] = $d;
// ---- END DATA ----

$e = fn($s) => htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
$nomorRef = kode_transaksi($pesanan);
$metode = metode_dari_nama($pesanan['metode_pembayaran']);
$statusBayar = $pesanan['status_bayar'] ?? 'cod';
$auto = isset($_GET['auto']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Bukti Pembayaran <?= $nomorRef ?> - Ruang Baca</title>
<style>
@page { size: A4; margin: 14mm; }
* { box-sizing: border-box; margin: 0; padding: 0; }
body { background: #EFE6D3; font-family: 'Segoe UI', Arial, sans-serif; color: #2B1B0E; font-size: 13.5px; line-height: 1.5; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
.toolbar { max-width: 800px; margin: 20px auto 0; display: flex; gap: 10px; justify-content: flex-end; padding: 0 12px; }
.toolbar a, .toolbar button { font: inherit; font-weight: 600; padding: 10px 18px; border-radius: 999px; border: 1.5px solid #2B1B0E; background: #fff; color: #2B1B0E; cursor: pointer; text-decoration: none; }
.toolbar button { background: #2B1B0E; color: #fff; }
.hint { max-width: 800px; margin: 8px auto 0; padding: 0 12px; text-align: right; font-size: 12px; color: #8A7660; }
.paper { max-width: 800px; margin: 14px auto 30px; background: #fff; border: 1px solid #E6D8BB; border-radius: 6px; overflow: hidden; box-shadow: 0 6px 20px rgba(43,27,14,.08); }
.head { background: #2B1B0E; color: #fff; padding: 22px 30px; display: flex; justify-content: space-between; align-items: center; gap: 16px; }
.brand { display: flex; align-items: center; gap: 12px; font-family: Georgia, 'Playfair Display', serif; font-size: 24px; font-weight: 700; }
.brand img { height: 34px; }
.head .judul { text-align: right; }
.head .judul strong { display: block; font-size: 15px; letter-spacing: .08em; }
.head .judul span { font-size: 12.5px; opacity: .8; }
.body { padding: 26px 30px 30px; }
.meta { display: grid; grid-template-columns: 1fr 1fr; gap: 22px; margin-bottom: 22px; }
.meta h4 { font-size: 11.5px; text-transform: uppercase; letter-spacing: .08em; color: #8A7660; margin-bottom: 6px; }
.meta p { margin-bottom: 2px; }
.meta .nm { font-weight: 700; }
.badge { display: inline-block; padding: 3px 12px; border-radius: 999px; font-size: 12px; font-weight: 700; }
.b-cod { background: #EADFC4; color: #5C4028; }
.b-menunggu { background: #FCEBC7; color: #8A5B00; }
.b-ok { background: #DCF0DC; color: #2E6B2E; }
.b-tolak { background: #F5DCDC; color: #8A2E2E; }
table { width: 100%; border-collapse: collapse; margin-top: 4px; }
th { text-align: left; font-size: 11.5px; text-transform: uppercase; letter-spacing: .06em; color: #8A7660; padding: 9px 8px; border-bottom: 1.5px solid #2B1B0E; }
td { padding: 11px 8px; border-bottom: 1px solid #E6D8BB; vertical-align: top; }
.r { text-align: right; white-space: nowrap; }
.c { text-align: center; }
.sum { margin: 16px 0 0 auto; width: 290px; }
.sum div { display: flex; justify-content: space-between; padding: 5px 0; }
.sum .tot { margin-top: 6px; padding: 12px 14px; background: #2B1B0E; color: #fff; border-radius: 10px; font-weight: 700; font-size: 16px; }
.note { margin-top: 26px; padding: 14px 16px; border: 1px dashed #D9B876; border-radius: 10px; background: #FFFDF8; font-size: 12.5px; color: #5C4028; }
.foot { margin-top: 22px; text-align: center; font-size: 12px; color: #8A7660; }
@media print {
  body { background: #fff; }
  .toolbar, .hint { display: none !important; }
  .paper { margin: 0; border: none; box-shadow: none; border-radius: 0; max-width: none; }
}
@media (max-width: 600px) { .meta { grid-template-columns: 1fr; } .sum { width: 100%; } .head { flex-direction: column; align-items: flex-start; } .head .judul { text-align: left; } }
</style>
</head>
<body>
<div class="toolbar">
  <a href="<?= $e(base_url('sukses.php?id=' . (int)$pesanan['id'])) ?>">← Kembali</a>
  <button type="button" onclick="window.print()">Cetak / Simpan PDF</button>
</div>
<p class="hint">Di jendela cetak, pilih tujuan <strong>Simpan sebagai PDF</strong> untuk mengunduhnya.</p>

<div class="paper">
  <div class="head">
    <div class="brand"><img src="<?= $e(base_url('assets/img/logo.png')) ?>" alt=""> Ruang Baca</div>
    <div class="judul"><strong>BUKTI PEMBAYARAN</strong><span>ID Transaksi <?= $nomorRef ?></span></div>
  </div>
  <div class="body">
    <div class="meta">
      <div>
        <h4>Penerima</h4>
        <p class="nm"><?= $e($pesanan['nama']) ?></p>
        <?php if (!empty($pesanan['kontak'])): ?><p><?= $e($pesanan['kontak']) ?></p><?php endif; ?>
        <p><?= nl2br($e($pesanan['alamat_kirim'])) ?></p>
      </div>
      <div>
        <h4>Rincian Pembayaran</h4>
        <p>Tanggal: <strong><?= date('d-m-Y, H:i', strtotime($pesanan['tanggal'])) ?></strong></p>
        <p>Metode: <strong><?= $e($metode['nama']) ?></strong></p>
        <p>Status:
          <?php
            $kls = ['terverifikasi' => 'b-ok', 'ditolak' => 'b-tolak', 'menunggu_verifikasi' => 'b-menunggu'][$statusBayar] ?? 'b-cod';
          ?>
          <span class="badge <?= $kls ?>"><?= $e(label_status_bayar($statusBayar)) ?></span>
        </p>
      </div>
    </div>

    <table>
      <thead><tr><th style="width:36px">No</th><th>Judul Buku</th><th class="c" style="width:50px">Qty</th><th class="r">Harga</th><th class="r">Subtotal</th></tr></thead>
      <tbody>
      <?php $no = 1; $subtotal = 0; foreach ($rows as $r): $sub = $r['harga_satuan'] * $r['jumlah']; $subtotal += $sub; ?>
        <tr>
          <td><?= $no++ ?></td>
          <td><?= $e($r['judul']) ?></td>
          <td class="c"><?= (int)$r['jumlah'] ?></td>
          <td class="r"><?= rupiah($r['harga_satuan']) ?></td>
          <td class="r"><?= rupiah($sub) ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>

    <div class="sum">
      <div><span>Subtotal</span><span><?= rupiah($subtotal) ?></span></div>
      <div><span>Ongkos Kirim</span><span>Gratis</span></div>
      <div class="tot"><span>Total Bayar</span><span><?= rupiah($pesanan['total']) ?></span></div>
    </div>

    <div class="note">
      <?php if (!$metode['butuh_bukti']): ?>
        Pembayaran dilakukan tunai saat buku diterima. Siapkan uang pas sejumlah <strong><?= rupiah($pesanan['total']) ?></strong>.
      <?php elseif ($statusBayar === 'terverifikasi'): ?>
        Pembayaran via <?= $e($metode['nama']) ?> telah diverifikasi. Terima kasih, pesananmu sedang diproses.
      <?php elseif ($statusBayar === 'ditolak'): ?>
        Bukti pembayaran via <?= $e($metode['nama']) ?> belum bisa diverifikasi. Silakan hubungi kami melalui halaman Kontak.
      <?php else: ?>
        Bukti pembayaran via <?= $e($metode['nama']) ?> sudah diterima dan menunggu verifikasi admin. Pesanan diproses setelah pembayaran terverifikasi.
      <?php endif; ?>
    </div>

    <p class="foot">Terima kasih telah berbelanja di Ruang Baca. Dokumen ini dibuat otomatis oleh sistem.</p>
  </div>
</div>
<?php if ($auto): ?>
<script>window.addEventListener('load', function () { setTimeout(function () { window.print(); }, 400); });</script>
<?php endif; ?>
</body>
</html>

<?php
$page_title = 'Kirim Balik Buku';
$active = 'pesanan_saya';
require_once __DIR__ . '/includes/refund.php';
require_login();

$user_id = (int)$_SESSION['user_id'];
$refund_id = (int)($_GET['id'] ?? $_POST['refund_id'] ?? 0);
$back = base_url('pesanan_saya.php');

$res = mysqli_query($conn, "SELECT r.*, p.tanggal, p.total, " . SQL_URUT_TRANSAKSI . " FROM refund r JOIN pesanan p ON p.id=r.pesanan_id WHERE r.id=$refund_id AND r.user_id=$user_id");
$r = mysqli_fetch_assoc($res);
if (!$r || $r['status'] !== 'Disetujui') {
    flash('error', 'Pengiriman balik hanya bisa diisi setelah refund disetujui.');
    header('Location: ' . $back); exit;
}

$err = '';
$kurir = $_POST['kurir'] ?? ($r['kurir'] ?? '');
$resi = trim($_POST['no_resi'] ?? ($r['no_resi'] ?? ''));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!in_array($kurir, REFUND_KURIR, true)) {
        $err = 'Pilih kurir pengiriman.';
    } elseif ($resi === '' || mb_strlen($resi) > 60) {
        $err = 'Nomor resi wajib diisi.';
    } elseif (($e = refund_validasi_foto($_FILES['foto_resi'] ?? null, false)) !== null) {
        $err = $e;
    } else {
        $foto = null;
        if (!empty($_FILES['foto_resi']['name'])) {
            $foto = refund_simpan_foto($_FILES['foto_resi'], 'resi_' . $refund_id);
            if ($foto === false) $err = 'Gagal menyimpan foto resi.';
        }
        if ($err === '') {
            $stmt = mysqli_prepare($conn, "UPDATE refund SET kurir=?, no_resi=?, foto_resi=COALESCE(?, foto_resi) WHERE id=? AND user_id=? AND status='Disetujui'");
            mysqli_stmt_bind_param($stmt, 'sssii', $kurir, $resi, $foto, $refund_id, $user_id);
            mysqli_stmt_execute($stmt);
            flash('success', 'Data pengiriman balik tersimpan. Status refund menunggu barang kembali, kami akan memeriksa bukunya begitu tiba.');
            header('Location: ' . $back); exit;
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>
<div class="container section" style="padding-top:36px;max-width:760px;">
  <a href="<?= $back ?>" class="rf-back"><?= icon('arrow-left', 15) ?> Kembali ke Pesanan Saya</a>
  <div class="page-hero" style="padding-top:8px;"><h1 style="font-size:38px;">Kirim Balik Buku</h1></div>
  <p style="color:var(--text-muted);margin:10px 0 24px;">Refund <b><?= kode_refund($r) ?></b> &middot; Pesanan <b><?= kode_transaksi($r) ?></b></p>

  <?php if (!empty($r['catatan_admin'])): ?>
    <div class="rf-note"><?= icon('mail', 18) ?> <div><b>Catatan dari admin:</b><br><?= nl2br(htmlspecialchars($r['catatan_admin'])) ?></div></div>
  <?php endif; ?>

  <div class="card rf-addr">
    <div class="pib-label">Kirim bukunya ke alamat ini</div>
    <div style="font-weight:700;margin-top:4px;"><?= htmlspecialchars(REFUND_ALAMAT_TOKO) ?></div>
    <div style="color:var(--text-muted);font-size:13px;margin-top:8px;">Bungkus buku dengan rapi supaya tidak rusak di perjalanan, lalu isi data pengiriman di bawah ini.</div>
  </div>

  <?php if ($err): ?><div class="alert alert-error"><?= htmlspecialchars($err) ?></div><?php endif; ?>

  <form method="post" enctype="multipart/form-data" class="card rf-form">
    <input type="hidden" name="refund_id" value="<?= $refund_id ?>">
    <div class="form-group">
      <label>Kurir</label>
      <select name="kurir" class="form-control" required>
        <option value="">-- Pilih kurir --</option>
        <?php foreach (REFUND_KURIR as $k): ?>
          <option value="<?= htmlspecialchars($k) ?>" <?= $kurir === $k ? 'selected' : '' ?>><?= htmlspecialchars($k) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="form-group">
      <label>Nomor resi</label>
      <input type="text" name="no_resi" class="form-control" value="<?= htmlspecialchars($resi) ?>" maxlength="60" required>
    </div>
    <div class="form-group">
      <label>Foto bukti resi (opsional)</label>
      <input type="file" name="foto_resi" class="form-control" accept="image/jpeg,image/png,image/webp">
    </div>
    <button type="submit" class="btn btn-primary btn-block">Konfirmasi Pengiriman</button>
  </form>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>

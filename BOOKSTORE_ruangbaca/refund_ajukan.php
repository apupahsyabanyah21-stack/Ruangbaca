<?php
$page_title = 'Ajukan Refund';
$active = 'pesanan_saya';
require_once __DIR__ . '/includes/refund.php';
require_login();

$user_id = (int)$_SESSION['user_id'];
$pesanan_id = (int)($_GET['id'] ?? $_POST['pesanan_id'] ?? 0);
$back = base_url('pesanan_saya.php');

$p = refund_ambil_pesanan($conn, $pesanan_id, $user_id);
if (!$p || $p['status'] !== 'Selesai') {
    flash('error', 'Refund hanya bisa diajukan untuk pesanan yang sudah selesai.');
    header('Location: ' . $back); exit;
}
$sudah = mysqli_query($conn, "SELECT id FROM refund WHERE pesanan_id=$pesanan_id");
if (mysqli_num_rows($sudah) > 0) {
    flash('error', 'Pesanan ini sudah pernah diajukan refund.');
    header('Location: ' . $back); exit;
}

$err = '';
$alasan = $_POST['alasan'] ?? '';
$deskripsi = trim($_POST['deskripsi'] ?? '');
$tujuan = trim($_POST['tujuan_dana'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!in_array($alasan, REFUND_ALASAN, true)) {
        $err = 'Pilih alasan refund.';
    } elseif (mb_strlen($deskripsi) < 10) {
        $err = 'Jelaskan masalah bukunya minimal 10 karakter.';
    } elseif ($tujuan === '') {
        $err = 'Isi tujuan pengembalian dana (bank/e-wallet dan nomornya).';
    } elseif (($e = refund_validasi_foto($_FILES['foto_bukti'] ?? null)) !== null) {
        $err = $e;
    } else {
        $foto = refund_simpan_foto($_FILES['foto_bukti'], 'refund_' . $pesanan_id);
        if ($foto === false) {
            $err = 'Gagal menyimpan foto, silakan coba lagi.';
        } else {
            $stmt = mysqli_prepare($conn, "INSERT INTO refund (pesanan_id, user_id, alasan, deskripsi, foto_bukti, tujuan_dana) VALUES (?,?,?,?,?,?)");
            mysqli_stmt_bind_param($stmt, 'iissss', $pesanan_id, $user_id, $alasan, $deskripsi, $foto, $tujuan);
            if (mysqli_stmt_execute($stmt)) {
                flash('success', 'Pengajuan refund terkirim. Kami akan meninjaunya secepatnya.');
                header('Location: ' . $back); exit;
            }
            @unlink(REFUND_DIR . $foto);
            $err = 'Gagal menyimpan pengajuan. Pastikan migration_refund.sql sudah dijalankan.';
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>
<div class="container section" style="padding-top:36px;max-width:760px;">
  <a href="<?= $back ?>" class="rf-back"><?= icon('arrow-left', 15) ?> Kembali ke Pesanan Saya</a>
  <div class="page-hero" style="padding-top:8px;"><h1 style="font-size:38px;">Ajukan Refund</h1></div>
  <p style="color:var(--text-muted);margin:10px 0 24px;">Pesanan <b><?= kode_transaksi($p) ?></b> &middot; <?= rupiah($p['total']) ?></p>

  <div class="rf-note"><?= icon('shield', 18) ?> <div>Refund hanya untuk buku yang cacat atau bermasalah. Setelah disetujui admin, kamu perlu mengirim balik bukunya, lalu dana dikembalikan.</div></div>

  <?php if ($err): ?><div class="alert alert-error"><?= htmlspecialchars($err) ?></div><?php endif; ?>

  <form method="post" enctype="multipart/form-data" class="card rf-form">
    <input type="hidden" name="pesanan_id" value="<?= $pesanan_id ?>">
    <div class="form-group">
      <label>Alasan refund</label>
      <select name="alasan" class="form-control" required>
        <option value="">-- Pilih alasan --</option>
        <?php foreach (REFUND_ALASAN as $a): ?>
          <option value="<?= htmlspecialchars($a) ?>" <?= $alasan === $a ? 'selected' : '' ?>><?= htmlspecialchars($a) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="form-group">
      <label>Jelaskan masalahnya</label>
      <textarea name="deskripsi" class="form-control" placeholder="Contoh: halaman 45-60 kosong, sampul sobek saat diterima..." required><?= htmlspecialchars($deskripsi) ?></textarea>
    </div>
    <div class="form-group">
      <label>Foto bukti pendukung</label>
      <input type="file" name="foto_bukti" class="form-control" accept="image/jpeg,image/png,image/webp" required>
      <small style="color:var(--text-muted);">JPG / PNG / WEBP, maksimal 2 MB.</small>
    </div>
    <div class="form-group">
      <label>Tujuan pengembalian dana</label>
      <input type="text" name="tujuan_dana" class="form-control" value="<?= htmlspecialchars($tujuan) ?>" placeholder="Contoh: DANA 0812xxxxxxx a.n. Nama kamu / BCA 1234567890 a.n. Nama kamu" required>
    </div>
    <button type="submit" class="btn btn-primary btn-block">Kirim Pengajuan Refund</button>
  </form>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>

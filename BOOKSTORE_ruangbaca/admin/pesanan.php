<?php
$page_title = 'Pesanan';
$active = 'pesanan';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/payment.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $id = (int)$_POST['id'];
    $status = clean($conn, $_POST['status']);
    mysqli_query($conn, "UPDATE pesanan SET status='$status' WHERE id=$id");
    header('Location: pesanan.php');
    exit;
}

// Verifikasi / tolak bukti pembayaran
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_bayar'])) {
    $id = (int)$_POST['id'];
    if ($_POST['update_bayar'] === 'verifikasi') {
        mysqli_query($conn, "UPDATE pesanan SET status_bayar='terverifikasi', status=IF(status='Menunggu','Diproses',status) WHERE id=$id AND bukti_bayar IS NOT NULL");
    } elseif ($_POST['update_bayar'] === 'tolak') {
        mysqli_query($conn, "UPDATE pesanan SET status_bayar='ditolak' WHERE id=$id AND bukti_bayar IS NOT NULL");
    }
    header('Location: pesanan.php');
    exit;
}

require_once __DIR__ . '/includes/header.php';
$list = mysqli_query($conn, "SELECT p.*, u.nama, u.email, " . SQL_URUT_TRANSAKSI . " FROM pesanan p JOIN users u ON p.user_id=u.id ORDER BY p.id ASC");

function badgeClass($status) {
    return match($status) {
        'Menunggu' => 'st-menunggu',
        'Diproses' => 'st-diproses',
        'Dikirim' => 'st-dikirim',
        'Selesai' => 'st-selesai',
        'Dibatalkan' => 'st-dibatalkan',
        default => 'st-menunggu'
    };
}

function bayarBadge($s) {
    return match($s) {
        'terverifikasi'       => ['Terverifikasi', 'st-bayar-ok'],
        'ditolak'             => ['Ditolak', 'st-bayar-tolak'],
        'menunggu_verifikasi' => ['Menunggu verifikasi', 'st-bayar-menunggu'],
        default               => ['Bayar di tempat', 'st-bayar-cod'],
    };
}
?>
<div class="admin-card">
  <div class="card-head">
    <div><h3>Daftar Pesanan</h3><p><?= mysqli_num_rows($list) ?> total pesanan</p></div>
  </div>
  <div class="table-wrap">
    <table class="data-table" style="text-align:center;">
      <thead><tr><th>ID Transaksi</th><th>Pelanggan</th><th>Total</th><th>Metode</th><th>Pembayaran</th><th>Status</th><th>Tanggal</th><th>Aksi</th></tr></thead>
      <tbody>
      <?php if (mysqli_num_rows($list) === 0): ?>
        <tr><td colspan="8" style="text-align:center;color:var(--text-muted);">Belum ada pesanan</td></tr>
      <?php endif; ?>
      <?php while ($p = mysqli_fetch_assoc($list)): ?>
        <tr>
          <td style="white-space:nowrap;font-weight:600;"><?= kode_transaksi($p) ?></td>
          <td><div style="line-height:1.4;"><?= htmlspecialchars($p['nama']) ?></div><span style="color:var(--text-muted);font-size:12px;"><?= htmlspecialchars($p['email']) ?></span></td>
          <td><?= rupiah($p['total']) ?></td>
          <td><?= htmlspecialchars($p['metode_pembayaran']) ?></td>
          <td>
            <?php [$bLabel, $bKelas] = bayarBadge($p['status_bayar'] ?? 'cod'); ?>
            <div class="bayar-cell">
              <span class="status-badge <?= $bKelas ?>"><?= $bLabel ?></span>
              <?php if (!empty($p['bukti_bayar'])): ?>
                <a href="<?= bukti_url($p['bukti_bayar']) ?>" target="_blank" rel="noopener" title="Lihat bukti pembayaran"><img src="<?= bukti_url($p['bukti_bayar']) ?>" alt="Bukti"></a>
                <?php if (($p['status_bayar'] ?? '') === 'menunggu_verifikasi'): ?>
                  <form method="post">
                    <input type="hidden" name="id" value="<?= $p['id'] ?>">
                    <button type="submit" name="update_bayar" value="verifikasi" class="btn btn-outline btn-sm">Verifikasi</button>
                    <button type="submit" name="update_bayar" value="tolak" class="btn btn-outline btn-sm">Tolak</button>
                  </form>
                <?php endif; ?>
              <?php endif; ?>
            </div>
          </td>
          <td><span class="status-badge <?= badgeClass($p['status']) ?>"><?= $p['status'] ?></span></td>
          <td><?= date('d M Y H:i', strtotime($p['tanggal'])) ?></td>
          <td>
            <form method="post" style="display:flex;gap:6px;justify-content:center;">
              <input type="hidden" name="id" value="<?= $p['id'] ?>">
              <select name="status" class="form-control" style="padding:6px;font-size:12px;">
                <?php foreach (['Menunggu','Diproses','Dikirim','Selesai','Dibatalkan'] as $s): ?>
                  <option value="<?= $s ?>" <?= $p['status']==$s?'selected':'' ?>><?= $s ?></option>
                <?php endforeach; ?>
              </select>
              <button type="submit" name="update_status" class="btn btn-outline btn-sm">✓</button>
            </form>
          </td>
        </tr>
      <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>

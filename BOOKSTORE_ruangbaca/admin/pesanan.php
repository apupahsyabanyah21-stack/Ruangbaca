<?php
$page_title = 'Pesanan';
$active = 'pesanan';
require_once __DIR__ . '/../includes/functions.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $id = (int)$_POST['id'];
    $status = clean($conn, $_POST['status']);
    mysqli_query($conn, "UPDATE pesanan SET status='$status' WHERE id=$id");
    header('Location: pesanan.php');
    exit;
}

require_once __DIR__ . '/includes/header.php';
$list = mysqli_query($conn, "SELECT p.*, u.nama, u.email FROM pesanan p JOIN users u ON p.user_id=u.id ORDER BY p.tanggal DESC");

function badgeClass($status) {
    return match($status) {
        'Menunggu' => 'badge-yellow',
        'Diproses' => 'badge-blue',
        'Dikirim' => 'badge-blue',
        'Selesai' => 'badge-green',
        'Dibatalkan' => 'badge-red',
        default => 'badge-yellow'
    };
}
?>
<div class="admin-card">
  <div class="card-head">
    <div><h3>Daftar Pesanan</h3><p><?= mysqli_num_rows($list) ?> total pesanan</p></div>
  </div>
  <div class="table-wrap">
    <table class="data-table">
      <thead><tr><th>ID</th><th>Pelanggan</th><th>Total</th><th>Metode</th><th>Status</th><th>Tanggal</th><th>Aksi</th></tr></thead>
      <tbody>
      <?php if (mysqli_num_rows($list) === 0): ?>
        <tr><td colspan="7" style="text-align:center;color:var(--text-muted);">Belum ada pesanan</td></tr>
      <?php endif; ?>
      <?php while ($p = mysqli_fetch_assoc($list)): ?>
        <tr>
          <td>#<?= $p['id'] ?></td>
          <td><?= htmlspecialchars($p['nama']) ?><br><span style="color:var(--text-muted);font-size:12px;"><?= htmlspecialchars($p['email']) ?></span></td>
          <td><?= rupiah($p['total']) ?></td>
          <td><?= htmlspecialchars($p['metode_pembayaran']) ?></td>
          <td><span class="badge <?= badgeClass($p['status']) ?>"><?= $p['status'] ?></span></td>
          <td><?= date('d M Y H:i', strtotime($p['tanggal'])) ?></td>
          <td>
            <form method="post" style="display:flex;gap:6px;">
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

<?php
$page_title = 'Pengguna';
$active = 'pengguna';
require_once __DIR__ . '/includes/header.php';

$list = mysqli_query($conn, "SELECT * FROM users WHERE role='user' ORDER BY created_at DESC");
?>
<div class="admin-card">
  <div class="card-head">
    <div><h3>Daftar Pengguna</h3><p><?= mysqli_num_rows($list) ?> pengguna terdaftar</p></div>
  </div>
  <div class="table-wrap">
    <table class="data-table">
      <thead><tr><th>Nama</th><th>Email</th><th>Kontak</th><th>Alamat</th><th>Tanggal Daftar</th></tr></thead>
      <tbody>
      <?php if (mysqli_num_rows($list) === 0): ?>
        <tr><td colspan="5" style="text-align:center;color:var(--text-muted);">Belum ada pengguna terdaftar</td></tr>
      <?php endif; ?>
      <?php while ($u = mysqli_fetch_assoc($list)): ?>
        <tr>
          <td><?= htmlspecialchars($u['nama']) ?></td>
          <td><?= htmlspecialchars($u['email']) ?></td>
          <td><?= htmlspecialchars($u['kontak'] ?? '-') ?></td>
          <td><?= htmlspecialchars($u['alamat'] ?? '-') ?></td>
          <td><?= date('d M Y', strtotime($u['created_at'])) ?></td>
        </tr>
      <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>

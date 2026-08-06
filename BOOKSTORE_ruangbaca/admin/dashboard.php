<?php
$page_title = 'Dashboard';
$active = 'dashboard';
require_once __DIR__ . '/includes/header.php';

$totalBuku = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) c FROM buku"))['c'];
$totalUser = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) c FROM users WHERE role='user'"))['c'];
$totalPesanan = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) c FROM pesanan"))['c'];
$pesanBelumDibaca = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) c FROM pesan_masuk WHERE dibaca=0"))['c'];

$recent = mysqli_query($conn, "SELECT p.*, u.nama FROM pesanan p JOIN users u ON p.user_id=u.id ORDER BY p.tanggal DESC LIMIT 6");
?>
<p style="color:var(--text-muted);margin-top:-14px;margin-bottom:20px;font-size:14px;">Ringkasan aktivitas toko BookShelf</p>

<div class="stat-grid">
  <div class="stat-box"><div class="top"><span class="ic">📖</span></div><div class="num"><?= $totalBuku ?></div><div class="label">Total Buku Aktif</div></div>
  <div class="stat-box"><div class="top"><span class="ic">👥</span></div><div class="num"><?= $totalUser ?></div><div class="label">Total Pengguna</div></div>
  <div class="stat-box"><div class="top"><span class="ic">🧾</span></div><div class="num"><?= $totalPesanan ?></div><div class="label">Total Pesanan</div></div>
  <div class="stat-box"><div class="top"><span class="ic">✉️</span></div><div class="num"><?= $pesanBelumDibaca ?></div><div class="label">Pesan Belum Dibaca</div></div>
</div>

<div class="admin-card">
  <div class="card-head"><h3>Pesanan Terbaru</h3></div>
  <div class="table-wrap">
    <table class="data-table">
      <thead><tr><th>ID</th><th>Pelanggan</th><th>Total</th><th>Status</th><th>Tanggal</th></tr></thead>
      <tbody>
      <?php if (mysqli_num_rows($recent) === 0): ?>
        <tr><td colspan="5" style="text-align:center;color:var(--text-muted);">Belum ada pesanan</td></tr>
      <?php endif; ?>
      <?php while ($r = mysqli_fetch_assoc($recent)): ?>
        <tr>
          <td>#<?= $r['id'] ?></td>
          <td><?= htmlspecialchars($r['nama']) ?></td>
          <td><?= rupiah($r['total']) ?></td>
          <td><span class="badge badge-yellow"><?= $r['status'] ?></span></td>
          <td><?= date('d M Y', strtotime($r['tanggal'])) ?></td>
        </tr>
      <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

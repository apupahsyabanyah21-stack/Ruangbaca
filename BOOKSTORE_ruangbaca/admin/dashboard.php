<?php
$page_title = 'Dashboard';
$active = 'dashboard';
require_once __DIR__ . '/includes/header.php';

$totalBuku = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) c FROM buku"))['c'];
$totalUser = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) c FROM users WHERE role='user'"))['c'];
$totalPesanan = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) c FROM pesanan"))['c'];
$pesanBelumDibaca = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) c FROM pesan_masuk WHERE dibaca=0"))['c'];

$recent = mysqli_query($conn, "SELECT p.*, u.nama, u.email FROM pesanan p JOIN users u ON p.user_id=u.id ORDER BY p.tanggal DESC LIMIT 6");

function statusBadgeClass($status) {
    return match($status) {
        'Menunggu' => 'st-menunggu',
        'Diproses' => 'st-diproses',
        'Dikirim' => 'st-dikirim',
        'Selesai' => 'st-selesai',
        'Dibatalkan' => 'st-dibatalkan',
        default => 'st-menunggu'
    };
}
?>
<p style="color:var(--text-muted);margin-top:-14px;margin-bottom:20px;font-size:14px;">Ringkasan aktivitas toko BookShelf</p>

<div class="stat-grid">
  <div class="stat-box">
    <div class="ic-circle"><?= icon('book-open', 22) ?></div>
    <div><div class="num"><?= $totalBuku ?></div><div class="label">Buku Terdaftar</div></div>
  </div>
  <div class="stat-box">
    <div class="ic-circle"><?= icon('user', 22) ?></div>
    <div><div class="num"><?= $totalUser ?></div><div class="label">Pengguna Terdaftar</div></div>
  </div>
  <div class="stat-box">
    <div class="ic-circle"><?= icon('package', 22) ?></div>
    <div><div class="num"><?= $totalPesanan ?></div><div class="label">Pesanan Masuk</div></div>
  </div>
  <div class="stat-box">
    <div class="ic-circle"><?= icon('mail', 22) ?></div>
    <div><div class="num"><?= $pesanBelumDibaca ?></div><div class="label">Pesan Masuk</div></div>
  </div>
</div>

<div class="admin-card">
  <div class="card-head"><h3>Pesanan Terbaru</h3></div>
  <div class="table-wrap">
    <table class="data-table" style="text-align:center;">
      <thead><tr><th>Id Pesanan</th><th>Pelanggan</th><th>Total</th><th>Status</th></tr></thead>
      <tbody>
      <?php if (mysqli_num_rows($recent) === 0): ?>
        <tr><td colspan="4" style="text-align:center;color:var(--text-muted);">Belum ada pesanan</td></tr>
      <?php endif; ?>
      <?php while ($r = mysqli_fetch_assoc($recent)): ?>
        <tr>
          <td>ord<?= $r['id'] ?></td>
          <td><div style="line-height:1.4;"><?= htmlspecialchars($r['nama']) ?></div><span style="color:var(--text-muted);font-size:12px;"><?= htmlspecialchars($r['email']) ?></span></td>
          <td><?= rupiah($r['total']) ?></td>
          <td><span class="status-badge <?= statusBadgeClass($r['status']) ?>"><?= $r['status'] ?></span></td>
        </tr>
      <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

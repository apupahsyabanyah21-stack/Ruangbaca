<?php
$page_title = 'Pesan Masuk';
$active = 'pesan_masuk';
require_once __DIR__ . '/../includes/functions.php';
require_admin();

if (isset($_GET['read'])) {
    mysqli_query($conn, "UPDATE pesan_masuk SET dibaca=1 WHERE id=" . (int)$_GET['read']);
    header('Location: pesan_masuk.php');
    exit;
}
if (isset($_GET['delete'])) {
    mysqli_query($conn, "DELETE FROM pesan_masuk WHERE id=" . (int)$_GET['delete']);
    header('Location: pesan_masuk.php');
    exit;
}

require_once __DIR__ . '/includes/header.php';
$list = mysqli_query($conn, "SELECT * FROM pesan_masuk ORDER BY tanggal DESC");
$total = mysqli_num_rows($list);
mysqli_data_seek($list, 0);
$belumDibaca = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) c FROM pesan_masuk WHERE dibaca=0"))['c'];
?>
<div class="admin-card">
  <div class="card-head">
    <div><h3>Pesan Masuk</h3><p><?= $total ?> pesan, <?= $belumDibaca ?> belum dibaca</p></div>
  </div>

  <?php if ($total === 0): ?>
    <div class="empty-box"><div class="ic">✉️</div>Belum ada pesan masuk</div>
  <?php endif; ?>

  <?php while ($m = mysqli_fetch_assoc($list)): ?>
    <div class="card" style="padding:16px 18px;margin-bottom:12px;<?= $m['dibaca']?'':'border-left:4px solid var(--gold);' ?>">
      <div style="display:flex;justify-content:space-between;align-items:start;">
        <div>
          <strong><?= htmlspecialchars($m['nama']) ?></strong>
          <span style="color:var(--text-muted);font-size:12.5px;"> — <?= htmlspecialchars($m['email']) ?><?= !empty($m['telepon']) ? ' — ' . htmlspecialchars($m['telepon']) : '' ?></span>
          <?php if (!$m['dibaca']): ?><span class="badge badge-yellow" style="margin-left:8px;">Baru</span><?php endif; ?>
        </div>
        <span style="font-size:12px;color:var(--text-muted);"><?= date('d M Y H:i', strtotime($m['tanggal'])) ?></span>
      </div>
      <?php if ($m['subjek']): ?><p style="font-weight:600;margin-top:8px;font-size:14px;"><?= htmlspecialchars($m['subjek']) ?></p><?php endif; ?>
      <p style="margin-top:4px;color:var(--text-dark);font-size:14px;"><?= nl2br(htmlspecialchars($m['pesan'])) ?></p>
      <div class="action-icons" style="margin-top:10px;">
        <?php if (!$m['dibaca']): ?><a href="?read=<?= $m['id'] ?>" class="edit"><?= icon('check-circle', 15) ?> Tandai Dibaca</a><?php endif; ?>
        <a href="?delete=<?= $m['id'] ?>" class="delete" onclick="return confirm('Hapus pesan ini?')"><?= icon('trash', 15) ?> Hapus</a>
      </div>
    </div>
  <?php endwhile; ?>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>

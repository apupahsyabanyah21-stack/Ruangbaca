<?php
$page_title = 'Refund';
$active = 'refund';
require_once __DIR__ . '/../includes/refund.php';
require_admin();

// ---- Aksi admin ----
// Alur: Menunggu -> (setuju) Disetujui -> (terima) Barang Diterima -> (selesai + bukti transfer) Selesai
//       Menunggu -> (tolak) Ditolak
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aksi'])) {
    $id = (int)$_POST['id'];
    $aksi = $_POST['aksi'];
    $catatan = trim($_POST['catatan'] ?? '');

    // aksi => [status baru, status yang harus sedang aktif]
    $alur = [
        'setuju' => ['Disetujui', 'Menunggu'],
        'tolak'  => ['Ditolak', 'Menunggu'],
        'terima' => ['Barang Diterima', 'Disetujui'],
    ];

    if ($aksi === 'selesai') {
        // Admin transfer dana ke user, lalu unggah bukti transfer -> status Selesai
        $err = refund_validasi_foto($_FILES['bukti_transfer'] ?? null);
        if ($err !== null) {
            flash('error', 'Bukti transfer: ' . $err);
        } else {
            $nama = refund_simpan_foto($_FILES['bukti_transfer'], 'transfer_' . $id);
            if ($nama === false) {
                flash('error', 'Gagal menyimpan bukti transfer, silakan coba lagi.');
            } else {
                $stmt = mysqli_prepare($conn, "UPDATE refund SET status='Selesai', bukti_transfer=? WHERE id=? AND status='Barang Diterima'");
                mysqli_stmt_bind_param($stmt, 'si', $nama, $id);
                mysqli_stmt_execute($stmt);
                if (mysqli_stmt_affected_rows($stmt) > 0) {
                    flash('success', 'Bukti transfer tersimpan. Refund selesai dan user sudah bisa melihat buktinya.');
                } else {
                    @unlink(REFUND_DIR . $nama);
                    flash('error', 'Status refund sudah berubah, muat ulang halaman.');
                }
            }
        }
    } elseif (!isset($alur[$aksi])) {
        flash('error', 'Aksi tidak dikenal.');
    } elseif ($aksi === 'tolak' && $catatan === '') {
        flash('error', 'Alasan penolakan wajib diisi supaya pelanggan tahu penyebabnya.');
    } else {
        [$baru, $syarat] = $alur[$aksi];
        $catatanDb = $catatan !== '' ? $catatan : null;
        $stmt = mysqli_prepare($conn, "UPDATE refund SET status=?, catatan_admin=COALESCE(?, catatan_admin) WHERE id=? AND status=?");
        mysqli_stmt_bind_param($stmt, 'ssis', $baru, $catatanDb, $id, $syarat);
        mysqli_stmt_execute($stmt);
        if (mysqli_stmt_affected_rows($stmt) > 0) {
            flash('success', "Status refund diubah menjadi \"$baru\".");
        } else {
            flash('error', 'Status refund sudah berubah, muat ulang halaman.');
        }
    }
    header('Location: refund.php');
    exit;
}

$msgOk = flash('success');
$msgErr = flash('error');

// ---- Upgrade otomatis tabel refund dari versi lama ke alur baru ----
$errUpgrade = null;
try {
    $qs = mysqli_query($conn, "SHOW COLUMNS FROM refund LIKE 'status'");
    $colStatus = $qs ? mysqli_fetch_assoc($qs) : null;
    if ($colStatus && strpos($colStatus['Type'], 'Barang Diterima') === false) {
        mysqli_query($conn, "ALTER TABLE refund MODIFY status ENUM('Menunggu','Diajukan','Disetujui','Ditolak','Dikirim Balik','Diterima','Barang Diterima','Selesai') NOT NULL DEFAULT 'Menunggu'");
        mysqli_query($conn, "UPDATE refund SET status='Menunggu' WHERE status='Diajukan'");
        mysqli_query($conn, "UPDATE refund SET status='Disetujui' WHERE status='Dikirim Balik'");
        mysqli_query($conn, "UPDATE refund SET status='Barang Diterima' WHERE status='Diterima'");
        mysqli_query($conn, "ALTER TABLE refund MODIFY status ENUM('Menunggu','Disetujui','Ditolak','Barang Diterima','Selesai') NOT NULL DEFAULT 'Menunggu'");
    }
    $qb = mysqli_query($conn, "SHOW COLUMNS FROM refund LIKE 'bukti_transfer'");
    if ($qb && mysqli_num_rows($qb) === 0) {
        mysqli_query($conn, "ALTER TABLE refund ADD COLUMN bukti_transfer VARCHAR(255) DEFAULT NULL AFTER foto_resi");
    }
} catch (mysqli_sql_exception $e) {
    $errUpgrade = $e->getMessage();
}

require_once __DIR__ . '/includes/header.php';
try {
    $list = mysqli_query($conn, "SELECT r.*, r.tanggal AS tgl_refund, u.nama, u.email, p.tanggal, p.total, p.metode_pembayaran, " . SQL_URUT_TRANSAKSI . "
        FROM refund r
        JOIN pesanan p ON p.id = r.pesanan_id
        JOIN users u ON u.id = r.user_id
        ORDER BY r.id DESC");
} catch (mysqli_sql_exception $e) {
    $list = false; // tabel refund belum dibuat / belum diupgrade
}
?>
<?php if ($errUpgrade): ?>
  <div class="alert alert-error"><b>Upgrade database refund gagal:</b> <?= htmlspecialchars($errUpgrade) ?><br>Jalankan <b>migration_refund.sql</b> manual di phpMyAdmin, atau kirim pesan error ini ke pengembang.</div>
<?php endif; ?>
<?php if ($msgOk): ?><div class="alert alert-success"><?= htmlspecialchars($msgOk) ?></div><?php endif; ?>
<?php if ($msgErr): ?><div class="alert alert-error"><?= htmlspecialchars($msgErr) ?></div><?php endif; ?>

<div class="admin-card">
  <div class="card-head">
    <div><h3>Daftar Refund</h3><p><?= $list ? mysqli_num_rows($list) : 0 ?> pengajuan</p></div>
  </div>

  <?php if (!$list): ?>
    <div class="alert alert-error">Tabel refund belum siap. Jalankan <b>migration_refund.sql</b> di phpMyAdmin terlebih dahulu.</div>
  <?php else: ?>
  <div class="table-wrap">
    <table class="data-table" style="text-align:center;">
      <thead><tr><th>ID Refund</th><th>Pesanan</th><th>Pelanggan</th><th>Masalah</th><th>Bukti</th><th>Dana Kembali</th><th>Kirim Balik</th><th>Status</th><th>Aksi</th></tr></thead>
      <tbody>
      <?php if (mysqli_num_rows($list) === 0): ?>
        <tr><td colspan="9" style="text-align:center;color:var(--text-muted);">Belum ada pengajuan refund</td></tr>
      <?php endif; ?>
      <?php while ($r = mysqli_fetch_assoc($list)): [$bLabel, $bKelas] = refund_badge($r['status']); ?>
        <tr>
          <td style="white-space:nowrap;font-weight:600;"><?= kode_refund($r) ?><br><span style="color:var(--text-muted);font-size:12px;font-weight:400;"><?= date('d M Y', strtotime($r['tgl_refund'])) ?></span></td>
          <td style="white-space:nowrap;"><?= kode_transaksi($r) ?></td>
          <td><div style="line-height:1.4;"><?= htmlspecialchars($r['nama']) ?></div><span style="color:var(--text-muted);font-size:12px;"><?= htmlspecialchars($r['email']) ?></span></td>
          <td><div class="rf-cell-text"><b><?= htmlspecialchars($r['alasan']) ?></b><br><?= nl2br(htmlspecialchars($r['deskripsi'])) ?></div></td>
          <td><a href="<?= refund_url($r['foto_bukti']) ?>" target="_blank" rel="noopener" title="Lihat bukti pendukung"><img class="rf-thumb" style="width:52px;height:52px;object-fit:cover;border-radius:8px;border:1px solid #E6D8BB;display:block;margin:4px auto 0;" src="<?= refund_url($r['foto_bukti']) ?>" alt="Bukti"></a></td>
          <td>
            <div class="rf-cell-text" style="text-align:center;"><b><?= rupiah($r['total']) ?></b><br><?= htmlspecialchars($r['tujuan_dana']) ?></div>
            <?php if (!empty($r['bukti_transfer'])): ?>
              <a href="<?= refund_url($r['bukti_transfer']) ?>" target="_blank" rel="noopener" title="Bukti transfer"><img class="rf-thumb" style="width:52px;height:52px;object-fit:cover;border-radius:8px;border:1px solid #E6D8BB;display:block;margin:4px auto 0;" src="<?= refund_url($r['bukti_transfer']) ?>" alt="Bukti transfer"></a>
            <?php endif; ?>
          </td>
          <td>
            <?php if ($r['no_resi']): ?>
              <div class="rf-cell-text" style="text-align:center;"><?= htmlspecialchars($r['kurir']) ?><br><b><?= htmlspecialchars($r['no_resi']) ?></b></div>
              <?php if ($r['foto_resi']): ?><a href="<?= refund_url($r['foto_resi']) ?>" target="_blank" rel="noopener"><img class="rf-thumb" style="width:52px;height:52px;object-fit:cover;border-radius:8px;border:1px solid #E6D8BB;display:block;margin:4px auto 0;" src="<?= refund_url($r['foto_resi']) ?>" alt="Resi"></a><?php endif; ?>
            <?php elseif ($r['status'] === 'Disetujui'): ?>
              <span style="color:var(--text-muted);font-size:12.5px;">Menunggu barang kembali</span>
            <?php else: ?>-<?php endif; ?>
          </td>
          <td>
            <span class="status-badge <?= $bKelas ?>"><?= $bLabel ?></span>
            <?php if ($r['catatan_admin']): ?><div class="rf-cell-text" style="margin-top:6px;color:var(--text-muted);text-align:center;"><i><?= htmlspecialchars($r['catatan_admin']) ?></i></div><?php endif; ?>
          </td>
          <td>
            <form method="post" enctype="multipart/form-data" class="rf-aksi">
              <input type="hidden" name="id" value="<?= $r['id'] ?>">
              <?php if ($r['status'] === 'Menunggu'): ?>
                <input type="text" name="catatan" class="form-control" style="padding:6px;font-size:12px;" placeholder="Catatan / alasan tolak">
                <div class="row">
                  <button type="submit" name="aksi" value="setuju" class="btn btn-outline btn-sm">Setujui</button>
                  <button type="submit" name="aksi" value="tolak" class="btn btn-outline btn-sm">Tolak</button>
                </div>
              <?php elseif ($r['status'] === 'Disetujui'): ?>
                <button type="submit" name="aksi" value="terima" class="btn btn-outline btn-sm" onclick="return confirm('Pastikan buku sudah benar-benar diterima dan diperiksa. Lanjutkan?')">Barang Diterima</button>
              <?php elseif ($r['status'] === 'Barang Diterima'): ?>
                <label style="font-size:11.5px;color:var(--text-muted);text-align:left;">Bukti transfer ke user</label>
                <input type="file" name="bukti_transfer" class="form-control" style="padding:6px;font-size:12px;" accept="image/jpeg,image/png,image/webp" required>
                <button type="submit" name="aksi" value="selesai" class="btn btn-outline btn-sm">Unggah &amp; Selesaikan</button>
              <?php else: ?>
                <span style="color:var(--text-muted);font-size:12.5px;">-</span>
              <?php endif; ?>
            </form>
          </td>
        </tr>
      <?php endwhile; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>

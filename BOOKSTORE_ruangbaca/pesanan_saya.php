<?php
$page_title = 'Pesanan Saya';
$active = 'pesanan_saya';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/refund.php';
require_login();

$user_id = (int)$_SESSION['user_id'];

// Data refund milik user, diindeks per id pesanan
$refunds = [];
try {
    $qRefund = mysqli_query($conn, "SELECT * FROM refund WHERE user_id=$user_id");
} catch (mysqli_sql_exception $e) {
    $qRefund = false; // tabel refund belum dibuat (jalankan migration_refund.sql)
}
if ($qRefund) {
    while ($row = mysqli_fetch_assoc($qRefund)) $refunds[$row['pesanan_id']] = $row;
}
$msgOk = flash('success');
$msgErr = flash('error');
$list = mysqli_query($conn, "SELECT p.*, " . SQL_URUT_TRANSAKSI . " FROM pesanan p WHERE p.user_id=$user_id ORDER BY p.tanggal DESC, p.id DESC");

function badgeClassUser($status) {
    return match ($status) {
        'Menunggu'   => 'st-menunggu',
        'Diproses'   => 'st-diproses',
        'Dikirim'    => 'st-dikirim',
        'Selesai'    => 'st-selesai',
        'Dibatalkan' => 'st-dibatalkan',
        default      => 'st-menunggu',
    };
}

// Tahapan progres pengiriman: kunci status => [ikon, judul, deskripsi]
$tahapan = [
    'Menunggu' => ['file-text', 'Menunggu', 'Pesananmu sedang menunggu konfirmasi.'],
    'Diproses' => ['package', 'Diproses', 'Pesananmu sedang diproses oleh kami.'],
    'Dikirim'  => ['truck', 'Dikirim', 'Pesananmu sedang dalam perjalanan.'],
    'Selesai'  => ['check-circle', 'Selesai', 'Pesanan telah diterima dengan baik.'],
];
$urutanTahapan = array_keys($tahapan);

require_once __DIR__ . '/includes/header.php';
?>
<div class="container section" style="padding-top:36px;">
  <div class="page-hero" style="padding-top:8px;"><h1 style="font-size:42px;">Pesanan Saya</h1></div>
  <p style="color:var(--text-muted);margin:12px 0 30px;font-size:16.5px;">Pantau status dan riwayat pengiriman buku yang kamu pesan di sini.</p>

  <?php if ($msgOk): ?><div class="alert alert-success"><?= htmlspecialchars($msgOk) ?></div><?php endif; ?>
  <?php if ($msgErr): ?><div class="alert alert-error"><?= htmlspecialchars($msgErr) ?></div><?php endif; ?>

  <?php if (mysqli_num_rows($list) === 0): ?>
    <div class="card" style="padding:50px;text-align:center;color:var(--text-muted);">
      Kamu belum punya pesanan. <a href="<?= base_url('katalog.php') ?>" style="color:var(--orange-dark);font-weight:600;">Yuk cari buku</a>
    </div>
  <?php else: ?>
    <div style="display:flex;flex-direction:column;gap:22px;">
      <?php while ($p = mysqli_fetch_assoc($list)):
        $detail = mysqli_query($conn, "SELECT pd.*, b.judul, b.gambar FROM pesanan_detail pd JOIN buku b ON pd.buku_id=b.id WHERE pd.pesanan_id={$p['id']}");
        $stepIndex = array_search($p['status'], $urutanTahapan);
      ?>
        <div class="card pesanan-card">
          <div class="pesanan-card-head">
            <div>
              <div style="font-weight:700;font-size:17px;"><?= kode_transaksi($p) ?></div>
              <div style="color:var(--text-muted);font-size:13px;margin-top:4px;display:flex;align-items:center;gap:6px;"><?= icon('calendar', 13) ?> <?= date('d M Y, H:i', strtotime($p['tanggal'])) ?> WIB</div>
            </div>
            <span class="status-badge <?= badgeClassUser($p['status']) ?>"><?= htmlspecialchars($p['status']) ?></span>
          </div>

          <?php if ($p['status'] !== 'Dibatalkan'): ?>
          <div class="pesanan-progress">
            <?php foreach ($tahapan as $key => $t): $i = array_search($key, $urutanTahapan); ?>
              <div class="pp-step <?= $i <= $stepIndex ? 'done' : '' ?>">
                <div class="pp-icon-wrap">
                  <div class="pp-dot"><?= icon($t[0], 17) ?></div>
                  <span class="pp-num"><?= $i + 1 ?></span>
                </div>
                <div class="pp-label"><?= $t[1] ?></div>
                <div class="pp-desc"><?= $t[2] ?></div>
              </div>
              <?php if ($i < count($urutanTahapan) - 1): ?><div class="pp-line <?= $i < $stepIndex ? 'done' : '' ?>"></div><?php endif; ?>
            <?php endforeach; ?>
          </div>
          <?php else: ?>
            <div style="background:#F5DCDC;color:#8A2E2E;padding:12px 16px;border-radius:10px;font-size:13.5px;margin-bottom:6px;">Pesanan ini dibatalkan.</div>
          <?php endif; ?>

          <div class="pesanan-detail-head"><?= icon('file-text', 15) ?> Detail Pesanan</div>
          <div class="pesanan-items">
            <?php while ($d = mysqli_fetch_assoc($detail)): ?>
              <div class="pesanan-item-row">
                <div class="thumb-sm"><?= $d['gambar'] ? '<img src="'.base_url('uploads/'.$d['gambar']).'">' : icon('book-open', 18) ?></div>
                <div style="flex:1;">
                  <div style="font-weight:600;font-size:14px;"><?= htmlspecialchars($d['judul']) ?></div>
                  <div style="color:var(--text-muted);font-size:12.5px;"><?= $d['jumlah'] ?> x <?= rupiah($d['harga_satuan']) ?></div>
                </div>
                <div style="font-weight:700;font-size:14px;"><?= rupiah($d['jumlah'] * $d['harga_satuan']) ?></div>
              </div>
            <?php endwhile; ?>
          </div>

          <div class="pesanan-info-bar">
            <div class="pib-item"><?= icon('truck', 17) ?> <div><div class="pib-label">Metode Pengiriman</div><div class="pib-val"><?= htmlspecialchars($p['metode_pembayaran']) ?></div></div></div>
            <div class="pib-item"><?= icon('calendar', 17) ?> <div><div class="pib-label">Estimasi Pengiriman</div><div class="pib-val">2 - 4 hari kerja</div></div></div>
            <div class="pib-total"><div class="pib-label">Total Pembayaran</div><div class="pib-val-total"><?= rupiah($p['total']) ?></div></div>
          </div>

          <?php
            $rf = $refunds[$p['id']] ?? null;
            if (!$rf && $p['status'] === 'Selesai'): ?>
            <div class="rf-box">
              <div class="rf-box-info"><?= icon('shield', 20) ?>
                <div><b>Buku cacat atau bermasalah?</b><span>Ajukan refund, lalu kirim balik bukunya ke kami.</span></div>
              </div>
              <a href="<?= base_url('refund_ajukan.php?id=' . $p['id']) ?>" class="btn btn-outline btn-sm">Ajukan Refund</a>
            </div>
          <?php elseif ($rf):
            [$rfLabel, $rfKelas] = refund_badge($rf['status']);
            $rfIdx = array_search($rf['status'], REFUND_TAHAPAN);
          ?>
            <div class="rf-box rf-box-col">
              <div class="rf-head">
                <div><b>Refund <?= kode_refund($rf) ?></b><span><?= htmlspecialchars($rf['alasan']) ?></span></div>
                <span class="status-badge <?= $rfKelas ?>"><?= $rfLabel ?></span>
              </div>

              <?php if ($rf['status'] !== 'Ditolak'): ?>
              <div class="rf-steps">
                <?php foreach (REFUND_TAHAPAN as $i => $t): ?>
                  <div class="rf-step <?= $i <= $rfIdx ? 'done' : '' ?>"><span><?= $i + 1 ?></span><?= $t ?></div>
                <?php endforeach; ?>
              </div>
              <?php endif; ?>

              <div class="rf-msg">
                <?php if ($rf['status'] === 'Menunggu'): ?>
                  Pengajuanmu sedang diperiksa admin.
                <?php elseif ($rf['status'] === 'Disetujui'): ?>
                  Refund disetujui. <b>Menunggu barang kembali.</b>
                  <?php if (!empty($rf['no_resi'])): ?>
                    Bukumu dikirim lewat <?= htmlspecialchars($rf['kurir']) ?> (resi <b><?= htmlspecialchars($rf['no_resi']) ?></b>), kami akan memeriksanya begitu tiba.
                  <?php else: ?>
                    Silakan kirim balik bukunya ke admin, lalu isi nomor resinya.
                  <?php endif; ?>
                  <?php if (!empty($rf['catatan_admin'])): ?><br><i>Catatan admin: <?= htmlspecialchars($rf['catatan_admin']) ?></i><?php endif; ?>
                <?php elseif ($rf['status'] === 'Ditolak'): ?>
                  Pengajuan ditolak.<?= !empty($rf['catatan_admin']) ? ' Alasan: ' . htmlspecialchars($rf['catatan_admin']) : '' ?>
                  Jika ada pertanyaan, hubungi kami lewat halaman Kontak.
                <?php elseif ($rf['status'] === 'Barang Diterima'): ?>
                  Bukumu sudah kami terima dan diperiksa. Dana <?= rupiah($p['total']) ?> sedang ditransfer ke <?= htmlspecialchars($rf['tujuan_dana']) ?>.
                <?php else: ?>
                  Refund selesai. Dana <?= rupiah($p['total']) ?> sudah ditransfer ke <?= htmlspecialchars($rf['tujuan_dana']) ?>.
                <?php endif; ?>
              </div>

              <?php if ($rf['status'] === 'Selesai' && !empty($rf['bukti_transfer'])): ?>
                <div class="rf-bukti">
                  <a href="<?= refund_url($rf['bukti_transfer']) ?>" target="_blank" rel="noopener" title="Lihat bukti transfer"><img src="<?= refund_url($rf['bukti_transfer']) ?>" alt="Bukti transfer"></a>
                  <div><b>Bukti transfer dari admin</b><span>Klik gambar untuk memperbesar.</span></div>
                </div>
              <?php endif; ?>

              <?php if ($rf['status'] === 'Disetujui'): ?>
                <a href="<?= base_url('refund_kirim.php?id=' . $rf['id']) ?>" class="btn btn-primary btn-sm" style="align-self:flex-start;"><?= icon('truck', 15) ?> <?= empty($rf['no_resi']) ? 'Kirim Balik Buku' : 'Ubah Data Pengiriman' ?></a>
              <?php endif; ?>
            </div>
          <?php endif; ?>
        </div>
      <?php endwhile; ?>

      <div class="bantuan-box">
        <div style="display:flex;align-items:center;gap:14px;">
          <div class="bantuan-ic"><?= icon('package', 20) ?></div>
          <div>
            <div style="font-weight:700;">Butuh bantuan?</div>
            <div style="color:var(--text-muted);font-size:13.5px;">Jika ada pertanyaan mengenai pesananmu, silakan hubungi kami melalui halaman Kontak.</div>
          </div>
        </div>
        <a href="<?= base_url('contact.php') ?>" class="btn btn-dark btn-sm">Hubungi Kami <?= icon('arrow-right', 14) ?></a>
      </div>
    </div>
  <?php endif; ?>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>

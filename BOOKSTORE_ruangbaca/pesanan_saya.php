<?php
$page_title = 'Pesanan Saya';
$active = 'pesanan_saya';
require_once __DIR__ . '/includes/functions.php';
require_login();

$user_id = (int)$_SESSION['user_id'];
$list = mysqli_query($conn, "SELECT * FROM pesanan WHERE user_id=$user_id ORDER BY tanggal DESC");

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
              <div style="font-weight:700;font-size:17px;">Pesanan #<?= $p['id'] ?></div>
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

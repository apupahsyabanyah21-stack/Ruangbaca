<?php
$page_title = 'Tentang Kami';
$active = 'about';
require_once __DIR__ . '/includes/header.php';

$totalBuku = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) c FROM buku"))['c'] ?? 0;
?>
<section class="section">
  <div class="container">
    <div class="mission-split" style="align-items:flex-start;">
      <div>
        <div class="eyebrow" style="color:var(--orange-dark);font-weight:700;font-size:17px;letter-spacing:1px;text-transform:uppercase;margin-bottom:14px;">Tentang Kami</div>
        <h2 style="margin-bottom:22px;font-size:56px;">Ruang Baca</h2>
        <p style="color:var(--brown-dark2);margin-bottom:46px;font-size:21px;line-height:1.85;">Ruang Baca adalah toko buku online sejak 2018, menghadirkan buku berkualitas untuk semua kalangan.
        Kami percaya bahwa setiap orang berhak mendapatkan akses terhadap pengetahuan dan kisah-kisah terbaik
        lewat buku pilihan yang mudah dijangkau.</p>
        <hr style="border:none;border-top:3px solid var(--brown-dark2);width:100%;margin-bottom:46px;">
        <div style="display:flex;gap:54px;flex-wrap:wrap;">
          <div><div style="font-size:58px;font-weight:700;color:var(--orange-dark);">100+</div><div style="color:var(--brown);font-size:19px;margin-top:6px;font-weight:500;">Kategori Buku</div></div>
          <div><div style="font-size:58px;font-weight:700;color:var(--orange-dark);">500K+</div><div style="color:var(--brown);font-size:19px;margin-top:6px;font-weight:500;">Buku Terjual</div></div>
          <div><div style="font-size:58px;font-weight:700;color:var(--orange-dark);">200K+</div><div style="color:var(--brown);font-size:19px;margin-top:6px;font-weight:500;">Pelanggan Setia</div></div>
        </div>
      </div>
      <div><img src="<?= base_url('assets/img/head-books.png') ?>" alt="Ruang Baca - dunia buku"></div>
    </div>

    <div class="section-title">
      <h2 style="font-size:40px;">Our Mission</h2>
      <p style="color:var(--brown-dark2);font-size:18px;max-width:800px;margin-left:auto;margin-right:auto;line-height:1.9;">Ruang Baca berkomitmen untuk menyediakan buku berkualitas, memberikan pelayanan terbaik,
      menghadirkan pengalaman berbelanja yang nyaman, serta menginspirasi masyarakat agar semakin
      gemar membaca dan terus mengembangkan wawasan melalui literasi.</p>
    </div>

    <div class="section-title" style="text-align:left;margin-bottom:24px;"><h2 style="font-size:36px;">Our Services</h2></div>
    <div class="services-row">
      <div class="item"><div class="ic"><?= icon('shield', 26) ?></div><div class="t">Buku Original</div><div class="d">100% buku original dan berkualitas</div></div>
      <div class="item"><div class="ic"><?= icon('truck', 26) ?></div><div class="t">Pengiriman Cepat</div><div class="d">Dikirim ke seluruh Indonesia</div></div>
      <div class="item"><div class="ic"><?= icon('tag', 26) ?></div><div class="t">Harga Terbaik</div><div class="d">Dapatkan harga terbaik setiap hari</div></div>
      <div class="item"><div class="ic"><?= icon('headset', 26) ?></div><div class="t">Layanan 24/7</div><div class="d">Kami siap membantu</div></div>
    </div>

    <div class="grid" style="grid-template-columns: 1fr 1fr; gap:30px;">
      <div class="card" style="padding:26px;">
        <h3 style="font-size:20px;">Informasi Kontak</h3>
        <p style="margin-top:14px;color:var(--text-muted);font-size:16px;line-height:2.2;">
          <span style="display:flex;align-items:center;gap:8px;"><?= icon('pin', 16) ?> Ruang Baca, Jakarta Timur, Indonesia</span>
          <span style="display:flex;align-items:center;gap:8px;"><?= icon('phone', 16) ?> (021) 1234-5678</span>
          <span style="display:flex;align-items:center;gap:8px;"><?= icon('mail', 16) ?> info@ruangbaca.co.id</span>
        </p>
      </div>
      <div class="card" style="padding:26px;">
        <h3 style="font-size:20px;">Kirim Pesan</h3>
        <p style="color:var(--text-muted);font-size:15px;margin:10px 0 18px;">Ada pertanyaan untuk kami? Hubungi admin lewat form kontak.</p>
        <a href="<?= base_url('contact.php') ?>" class="btn btn-primary btn-block">Buka Form Kontak</a>
      </div>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

<?php
$page_title = 'Tentang Kami';
$active = 'about';
require_once __DIR__ . '/includes/header.php';

$totalBuku = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) c FROM buku"))['c'] ?? 0;
?>
<div class="page-hero"><h1>Tentang Ruang Baca</h1></div>

<section class="section">
  <div class="container">
    <div class="section-title">
      <h2>Ruang Baca</h2>
      <p>Ruang Baca adalah toko buku online sejak 2018, menghadirkan buku berkualitas untuk semua kalangan.
      Kami percaya bahwa setiap orang berhak mendapatkan akses terhadap pengetahuan dan kisah-kisah terbaik
      lewat buku pilihan yang mudah dijangkau.</p>
    </div>

    <div class="grid grid-3" style="margin-bottom:50px;">
      <div class="card" style="padding:26px;text-align:center;">
        <div style="font-size:26px;font-weight:700;color:var(--orange-dark);">100+</div>
        <div style="color:var(--text-muted);font-size:13px;margin-top:4px;">Kategori Buku</div>
      </div>
      <div class="card" style="padding:26px;text-align:center;">
        <div style="font-size:26px;font-weight:700;color:var(--orange-dark);">500K+</div>
        <div style="color:var(--text-muted);font-size:13px;margin-top:4px;">Buku Terjual</div>
      </div>
      <div class="card" style="padding:26px;text-align:center;">
        <div style="font-size:26px;font-weight:700;color:var(--orange-dark);">200K+</div>
        <div style="color:var(--text-muted);font-size:13px;margin-top:4px;">Pelanggan Setia</div>
      </div>
    </div>

    <div class="section-title">
      <h2>Our Mission</h2>
      <p>Ruang Baca berkomitmen untuk menyediakan akses buku berkualitas dengan harga terjangkau
      bagi seluruh pembaca di Indonesia, kapan pun dan di mana pun.</p>
    </div>

    <div class="section-title"><h2>Our Services</h2></div>
    <div class="grid grid-3" style="margin-bottom:50px;">
      <div class="card" style="padding:28px 20px;text-align:center;">
        <div style="font-size:30px;margin-bottom:10px;">🚚</div>
        <h4>Pengiriman Cepat</h4>
        <p style="color:var(--text-muted);font-size:13px;margin-top:4px;">Ke seluruh Indonesia</p>
      </div>
      <div class="card" style="padding:28px 20px;text-align:center;">
        <div style="font-size:30px;margin-bottom:10px;">💵</div>
        <h4>Payment at Delivery</h4>
        <p style="color:var(--text-muted);font-size:13px;margin-top:4px;">Bayar saat buku diterima</p>
      </div>
      <div class="card" style="padding:28px 20px;text-align:center;">
        <div style="font-size:30px;margin-bottom:10px;">📚</div>
        <h4><?= $totalBuku ?>+ Koleksi Buku</h4>
        <p style="color:var(--text-muted);font-size:13px;margin-top:4px;">Terus diperbarui</p>
      </div>
    </div>

    <div class="grid" style="grid-template-columns: 1fr 1fr; gap:30px;">
      <div class="card" style="padding:26px;">
        <h3>Informasi Kontak</h3>
        <p style="margin-top:12px;color:var(--text-muted);font-size:14px;line-height:2;">
          📍 Ruang Baca, Jakarta Timur, Indonesia<br>
          📞 (021) 1234-5678<br>
          ✉️ info@ruangbaca.co.id
        </p>
      </div>
      <div class="card" style="padding:26px;">
        <h3>Kirim Pesan</h3>
        <p style="color:var(--text-muted);font-size:13px;margin:8px 0 16px;">Ada pertanyaan untuk kami? Hubungi admin lewat form kontak.</p>
        <a href="<?= base_url('contact.php') ?>" class="btn btn-primary btn-block">Buka Form Kontak</a>
      </div>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

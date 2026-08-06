<?php
$page_title = 'Kontak';
$active = 'kontak';
require_once __DIR__ . '/includes/functions.php';
$success = null; $error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = clean($conn, $_POST['nama']);
    $email = clean($conn, $_POST['email']);
    $subjek = clean($conn, $_POST['subjek']);
    $pesan = clean($conn, $_POST['pesan']);
    if ($nama === '' || $email === '' || $pesan === '') {
        $error = 'Nama, email, dan pesan wajib diisi.';
    } else {
        mysqli_query($conn, "INSERT INTO pesan_masuk (nama, email, subjek, pesan) VALUES ('$nama','$email','$subjek','$pesan')");
        $success = 'Pesan kamu berhasil dikirim ke admin. Terima kasih!';
    }
}
require_once __DIR__ . '/includes/header.php';
?>
<div class="page-hero"><h1>Hubungi Kami</h1></div>
<div class="container section">
  <div class="grid" style="grid-template-columns: 1fr 1.2fr; gap:30px;">
    <div class="card" style="padding:26px;">
      <h3>Informasi Kontak</h3>
      <p style="margin-top:14px;color:var(--text-muted);font-size:14px;line-height:2;">
        📍 Ruang Baca, Jl. Raya Buku No. 1<br>Jakarta Timur, Indonesia<br>
        📞 (021) 1234-5678<br>
        ✉️ info@ruangbaca.com<br>
        🕐 Senin - Sabtu, 09.00 - 18.00
      </p>
    </div>
    <div class="card" style="padding:26px;">
      <h3 style="margin-bottom:16px;">Kirim Pesan</h3>
      <?php if ($success): ?><div class="alert alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>
      <?php if ($error): ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
      <form method="post">
        <div class="form-group"><label>Nama</label>
          <input type="text" name="nama" class="form-control" value="<?= htmlspecialchars($_SESSION['nama'] ?? '') ?>" required></div>
        <div class="form-group"><label>Email</label>
          <input type="email" name="email" class="form-control" required></div>
        <div class="form-group"><label>Subjek</label>
          <input type="text" name="subjek" class="form-control"></div>
        <div class="form-group"><label>Pesan</label>
          <textarea name="pesan" class="form-control" required></textarea></div>
        <button type="submit" class="btn btn-primary btn-block">Kirim Pesan</button>
      </form>
    </div>
  </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>

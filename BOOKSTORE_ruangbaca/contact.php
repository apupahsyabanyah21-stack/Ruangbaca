<?php
$page_title = 'Kontak';
$active = 'kontak';
require_once __DIR__ . '/includes/functions.php';
$success = null; $error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $namaDepan = clean($conn, $_POST['nama_depan']);
    $namaBelakang = clean($conn, $_POST['nama_belakang']);
    $nama = trim($namaDepan . ' ' . $namaBelakang);
    $email = clean($conn, $_POST['email']);
    $telepon = clean($conn, $_POST['telepon']);
    $pesan = clean($conn, $_POST['pesan']);

    if ($namaDepan === '' || $email === '' || $pesan === '') {
        $error = 'Nama depan, email, dan pesan wajib diisi.';
    } else {
        mysqli_query($conn, "INSERT INTO pesan_masuk (nama, email, telepon, pesan) VALUES ('$nama','$email','$telepon','$pesan')");
        $success = 'Pesan kamu berhasil dikirim ke admin. Terima kasih!';
    }
}
require_once __DIR__ . '/includes/header.php';
?>
<div class="container"><div class="page-hero"><h1>Hubungi Kami</h1></div></div>
<div class="container section" style="padding-top:0;">
  <div class="grid" style="grid-template-columns: 1fr 1.2fr; gap:30px;align-items:start;">
    <div class="contact-info-panel">
      <h3>Informasi Kontak</h3>
      <div class="row"><div class="lbl">Email</div><div class="val">info@ruangbaca.co.id</div></div>
      <div class="row"><div class="lbl">Telepon</div><div class="val">(021) 1234-5678</div></div>
      <div class="row"><div class="lbl">Alamat</div><div class="val">Jl. Raya Buku No. 1, Jakarta Timur, Indonesia</div></div>
      <div class="row"><div class="lbl">Jam Operasional</div><div class="val">Senin - Sabtu, 09.00 - 18.00 WIB</div></div>
    </div>

    <div class="card" style="padding:36px;">
      <h3 style="font-size:26px;margin-bottom:20px;">Kirim Pesan</h3>
      <?php if ($success): ?><div class="alert alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>
      <?php if ($error): ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
      <form method="post" class="underline-form">
        <div class="form-row">
          <div><label>Nama Depan*</label><input type="text" name="nama_depan" class="underline-input" required></div>
          <div><label>Nama Belakang*</label><input type="text" name="nama_belakang" class="underline-input"></div>
        </div>
        <div class="form-row">
          <div><label>Email*</label><input type="email" name="email" class="underline-input" required></div>
          <div><label>No Telp*</label><input type="text" name="telepon" class="underline-input"></div>
        </div>
        <div style="margin-bottom:28px;">
          <label>Pesan*</label>
          <textarea name="pesan" class="underline-input" required></textarea>
        </div>
        <div style="text-align:right;">
          <button type="submit" class="btn btn-primary">Kirim Pesan</button>
        </div>
      </form>
    </div>
  </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>

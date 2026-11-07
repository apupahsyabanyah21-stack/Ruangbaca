<?php
$page_title = 'Data Buku';
$active = 'buku';
require_once __DIR__ . '/../includes/functions.php';
require_admin();

// CREATE / UPDATE
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul = clean($conn, $_POST['judul']);
    $penulis = clean($conn, $_POST['penulis']);
    $kategori_id = (int)$_POST['kategori_id'];
    $harga = (float)$_POST['harga'];
    $stok = (int)$_POST['stok'];
    $deskripsi = clean($conn, $_POST['deskripsi']);

    // Upload gambar (opsional)
    $gambarClause = '';
    if (!empty($_FILES['gambar']['name'])) {
        $ext = pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION);
        $filename = uniqid('buku_') . '.' . $ext;
        move_uploaded_file($_FILES['gambar']['tmp_name'], __DIR__ . '/../uploads/' . $filename);
        $gambarClause = ", gambar='$filename'";
    }

    if (!empty($_POST['id'])) {
        $id = (int)$_POST['id'];
        mysqli_query($conn, "UPDATE buku SET judul='$judul', penulis='$penulis', kategori_id=$kategori_id,
            harga=$harga, stok=$stok, deskripsi='$deskripsi' $gambarClause WHERE id=$id");
    } else {
        mysqli_query($conn, "INSERT INTO buku (judul, penulis, kategori_id, harga, stok, deskripsi)
            VALUES ('$judul','$penulis',$kategori_id,$harga,$stok,'$deskripsi')");
        if ($gambarClause) {
            $newId = mysqli_insert_id($conn);
            mysqli_query($conn, "UPDATE buku SET " . ltrim($gambarClause, ', ') . " WHERE id=$newId");
        }
    }
    header('Location: buku.php');
    exit;
}

// DELETE
if (isset($_GET['delete'])) {
    mysqli_query($conn, "DELETE FROM buku WHERE id=" . (int)$_GET['delete']);
    header('Location: buku.php');
    exit;
}

require_once __DIR__ . '/includes/header.php';
$list = mysqli_query($conn, "SELECT b.*, k.nama_kategori FROM buku b LEFT JOIN kategori k ON b.kategori_id=k.id ORDER BY b.id DESC");
$kategoriList = mysqli_query($conn, "SELECT * FROM kategori ORDER BY nama_kategori");
$kategoriArr = [];
mysqli_data_seek($kategoriList, 0);
while ($k = mysqli_fetch_assoc($kategoriList)) $kategoriArr[] = $k;
?>
<div class="admin-card">
  <div class="card-head">
    <div><h3>Data Buku</h3><p><?= mysqli_num_rows($list) ?> buku terdaftar</p></div>
    <button class="btn btn-primary btn-sm" onclick="openModal()">+ Tambah Buku</button>
  </div>
  <div class="table-wrap">
    <table class="data-table">
      <thead><tr><th>Judul</th><th>Penulis</th><th>Kategori</th><th>Harga</th><th>Stok</th><th>Aksi</th></tr></thead>
      <tbody>
      <?php if (mysqli_num_rows($list) === 0): ?>
        <tr><td colspan="6" style="text-align:center;color:var(--text-muted);">Belum ada buku</td></tr>
      <?php endif; ?>
      <?php while ($b = mysqli_fetch_assoc($list)): ?>
        <tr>
          <td><?= htmlspecialchars($b['judul']) ?></td>
          <td><?= htmlspecialchars($b['penulis']) ?></td>
          <td><?= htmlspecialchars($b['nama_kategori'] ?? '-') ?></td>
          <td><?= rupiah($b['harga']) ?></td>
          <td><?= $b['stok'] ?></td>
          <td class="action-icons">
            <a href="#" class="edit" onclick='openModal(<?= json_encode($b) ?>)'><?= icon('edit', 16) ?></a>
            <a href="?delete=<?= $b['id'] ?>" class="delete" onclick="return confirm('Hapus buku ini?')"><?= icon('trash', 16) ?></a>
          </td>
        </tr>
      <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>

<div class="modal-overlay" id="modal">
  <div class="modal-box">
    <span class="modal-close" onclick="closeModal()">&times;</span>
    <h3 id="modalTitle">Tambah Buku</h3>
    <form method="post" enctype="multipart/form-data">
      <input type="hidden" name="id" id="f_id">
      <div class="form-group"><label>Judul Buku</label><input type="text" name="judul" id="f_judul" class="form-control" required></div>
      <div class="form-group"><label>Penulis</label><input type="text" name="penulis" id="f_penulis" class="form-control" required></div>
      <div class="form-group"><label>Kategori</label>
        <select name="kategori_id" id="f_kategori" class="form-control" required>
          <?php foreach ($kategoriArr as $k): ?>
            <option value="<?= $k['id'] ?>"><?= htmlspecialchars($k['nama_kategori']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group"><label>Harga (Rp)</label><input type="number" name="harga" id="f_harga" class="form-control" required></div>
      <div class="form-group"><label>Stok</label><input type="number" name="stok" id="f_stok" class="form-control" required></div>
      <div class="form-group"><label>Deskripsi</label><textarea name="deskripsi" id="f_deskripsi" class="form-control"></textarea></div>
      <div class="form-group"><label>Gambar Sampul</label><input type="file" name="gambar" class="form-control" accept="image/*"></div>
      <button type="submit" class="btn btn-primary btn-block">Simpan</button>
    </form>
  </div>
</div>

<script>
function openModal(data) {
  document.getElementById('modal').classList.add('show');
  document.getElementById('modalTitle').innerText = data ? 'Edit Buku' : 'Tambah Buku';
  document.getElementById('f_id').value = data ? data.id : '';
  document.getElementById('f_judul').value = data ? data.judul : '';
  document.getElementById('f_penulis').value = data ? data.penulis : '';
  document.getElementById('f_kategori').value = data ? data.kategori_id : '';
  document.getElementById('f_harga').value = data ? data.harga : '';
  document.getElementById('f_stok').value = data ? data.stok : '';
  document.getElementById('f_deskripsi').value = data ? data.deskripsi : '';
}
function closeModal() { document.getElementById('modal').classList.remove('show'); }
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

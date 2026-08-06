<?php
$page_title = 'Kategori Buku';
$active = 'kategori';
require_once __DIR__ . '/../includes/functions.php';
require_admin();

// CREATE / UPDATE
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = clean($conn, $_POST['nama_kategori']);
    $deskripsi = clean($conn, $_POST['deskripsi']);
    $icon = clean($conn, $_POST['icon']) ?: '📚';

    if (!empty($_POST['id'])) {
        $id = (int)$_POST['id'];
        mysqli_query($conn, "UPDATE kategori SET nama_kategori='$nama', deskripsi='$deskripsi', icon='$icon' WHERE id=$id");
    } else {
        mysqli_query($conn, "INSERT INTO kategori (nama_kategori, deskripsi, icon) VALUES ('$nama','$deskripsi','$icon')");
    }
    header('Location: kategori.php');
    exit;
}

// DELETE
if (isset($_GET['delete'])) {
    mysqli_query($conn, "DELETE FROM kategori WHERE id=" . (int)$_GET['delete']);
    header('Location: kategori.php');
    exit;
}

require_once __DIR__ . '/includes/header.php';
$list = mysqli_query($conn, "SELECT * FROM kategori ORDER BY id DESC");
?>
<div class="admin-card">
  <div class="card-head">
    <div><h3>Kategori Buku</h3><p>Kelola kategori untuk pengelompokan buku</p></div>
    <button class="btn btn-primary btn-sm" onclick="openModal()">+ Tambah Kategori</button>
  </div>
  <div class="grid grid-3">
    <?php mysqli_data_seek($list, 0); if (mysqli_num_rows($list)===0): ?>
      <div class="empty-box" style="grid-column:1/-1;"><div class="ic">🏷️</div>Belum ada kategori</div>
    <?php endif; ?>
    <?php while ($k = mysqli_fetch_assoc($list)): ?>
      <div class="card" style="padding:18px;">
        <div style="font-size:26px;"><?= htmlspecialchars($k['icon']) ?></div>
        <h4 style="margin:8px 0 2px;"><?= htmlspecialchars($k['nama_kategori']) ?></h4>
        <p style="font-size:13px;color:var(--text-muted);margin-bottom:12px;"><?= htmlspecialchars($k['deskripsi']) ?></p>
        <div class="action-icons">
          <a href="#" class="edit" onclick='openModal(<?= json_encode($k) ?>)'>✏️ Edit</a>
          <a href="?delete=<?= $k['id'] ?>" class="delete" onclick="return confirm('Hapus kategori ini?')">🗑️ Hapus</a>
        </div>
      </div>
    <?php endwhile; ?>
  </div>
</div>

<div class="modal-overlay" id="modal">
  <div class="modal-box">
    <span class="modal-close" onclick="closeModal()">&times;</span>
    <h3 id="modalTitle">Tambah Kategori</h3>
    <form method="post">
      <input type="hidden" name="id" id="f_id">
      <div class="form-group"><label>Icon (emoji)</label><input type="text" name="icon" id="f_icon" class="form-control" placeholder="📚"></div>
      <div class="form-group"><label>Nama Kategori</label><input type="text" name="nama_kategori" id="f_nama" class="form-control" required></div>
      <div class="form-group"><label>Deskripsi</label><textarea name="deskripsi" id="f_deskripsi" class="form-control"></textarea></div>
      <button type="submit" class="btn btn-primary btn-block">Simpan</button>
    </form>
  </div>
</div>

<script>
function openModal(data) {
  document.getElementById('modal').classList.add('show');
  if (data) {
    document.getElementById('modalTitle').innerText = 'Edit Kategori';
    document.getElementById('f_id').value = data.id;
    document.getElementById('f_icon').value = data.icon;
    document.getElementById('f_nama').value = data.nama_kategori;
    document.getElementById('f_deskripsi').value = data.deskripsi;
  } else {
    document.getElementById('modalTitle').innerText = 'Tambah Kategori';
    document.getElementById('f_id').value = '';
    document.getElementById('f_icon').value = '';
    document.getElementById('f_nama').value = '';
    document.getElementById('f_deskripsi').value = '';
  }
}
function closeModal() { document.getElementById('modal').classList.remove('show'); }
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

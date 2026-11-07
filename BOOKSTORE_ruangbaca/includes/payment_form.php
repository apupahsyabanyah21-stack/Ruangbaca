<?php
// Bagian "Metode Pembayaran" untuk checkout.php (dropdown turun). Butuh variabel $total.
require_once __DIR__ . '/payment.php';
$metodeList = daftar_metode_bayar();
$dipilih = $_POST['metode_bayar'] ?? 'cod';
if (!isset($metodeList[$dipilih])) $dipilih = 'cod';
$cur = $metodeList[$dipilih];
?>
<style>
.pay-select{position:relative}
.pay-current{width:100%;display:flex;align-items:center;gap:10px;padding:12px;border:1.5px solid var(--gold);border-radius:12px;background:#FFF8ED;cursor:pointer;font:inherit;color:inherit;text-align:left}
.pay-current .pay-dot{width:16px;height:16px;border-radius:50%;border:5px solid var(--orange);background:#fff;flex-shrink:0}
.pay-current .pay-label{flex:1;min-width:0}
.pay-current .pay-label small{color:var(--text-muted)}
.pay-caret{transition:transform .2s ease;color:var(--brown)}
.pay-select.open .pay-caret{transform:rotate(180deg)}
.pay-options{margin-top:8px;border:1px solid var(--border);border-radius:12px;background:#fff;overflow:hidden}
.pay-options[hidden]{display:none}
.pay-opt{display:flex;align-items:center;gap:12px;padding:12px 14px;cursor:pointer;border-top:1px solid var(--border)}
.pay-opt:first-child{border-top:none}
.pay-opt:hover{background:#FFF8ED}
.pay-opt.is-active{background:#FFF8ED}
.pay-opt input{accent-color:var(--orange);margin:0}
.pay-ico{font-size:19px;width:24px;text-align:center}
.pay-logo{display:inline-flex;align-items:center;justify-content:center;width:88px;height:40px;padding:5px 8px;background:#fff;border:1px solid var(--border);border-radius:8px;flex-shrink:0}
.pay-logo img{max-width:100%;max-height:100%;object-fit:contain;display:block}
.pay-txt{display:flex;flex-direction:column;line-height:1.35}
.pay-txt small{color:var(--text-muted);font-size:12.5px}
.pay-panel{display:flex;gap:20px;flex-wrap:wrap;padding:16px;margin-top:12px;border:1.5px dashed var(--gold-soft);border-radius:12px;background:#FFFDF8}
.pay-panel[hidden]{display:none}
.pay-qris{flex:0 0 190px;text-align:center}
.pay-qris img{width:190px;height:190px;border:1px solid var(--border);border-radius:8px;background:#fff}
.pay-qris small{display:block;margin-top:6px;color:var(--text-muted);font-size:12.5px}
.pay-detail{flex:1 1 240px;min-width:0}
.pay-detail dl{margin:0 0 14px;display:grid;grid-template-columns:auto 1fr;gap:6px 14px;font-size:14px}
.pay-detail dt{color:var(--text-muted)}
.pay-detail dd{margin:0;font-weight:600;word-break:break-all}
.pay-copy{margin-left:8px;font-size:12px;padding:2px 10px;border:1px solid var(--orange);border-radius:999px;background:none;color:var(--orange);cursor:pointer}
.pay-upload label{display:block;font-size:13px;font-weight:600;margin-bottom:6px;color:var(--brown-dark2)}
.pay-upload input[type=file]{width:100%;font-size:13px}
.pay-upload small{display:block;margin-top:4px;color:var(--text-muted);font-size:12.5px}
#buktiPreview{display:none;margin-top:8px;max-width:140px;border-radius:8px;border:1px solid var(--border)}
</style>

<div class="pay-select" id="paySelect">
  <button type="button" class="pay-current" id="payCurrent" aria-expanded="false" aria-controls="payOptions">
    <span class="pay-dot"></span>
    <span id="curIcoWrap" style="display:inline-flex"><?= logo_metode_html($cur) ?></span>
    <span class="pay-label"><strong id="curNama"><?= htmlspecialchars($cur['nama']) ?></strong> <small id="curKet">(<?= htmlspecialchars($cur['keterangan']) ?>)</small></span>
    <span class="pay-caret">▾</span>
  </button>

  <div class="pay-options" id="payOptions" hidden>
  <?php foreach ($metodeList as $kode => $m): ?>
    <label class="pay-opt<?= $kode === $dipilih ? ' is-active' : '' ?>">
      <input type="radio" name="metode_bayar" value="<?= htmlspecialchars($kode) ?>" <?= $kode === $dipilih ? 'checked' : '' ?>>
      <?= logo_metode_html($m) ?>
      <span class="pay-txt"><strong><?= htmlspecialchars($m['nama']) ?></strong><small><?= htmlspecialchars($m['keterangan']) ?></small></span>
    </label>
  <?php endforeach; ?>
  </div>
</div>

<div id="payPanel" class="pay-panel" hidden>
  <div class="pay-qris">
    <img src="<?= base_url('assets/img/qris.jpg') ?>" alt="QRIS Ruang Baca">
    <small>Scan dengan GoPay, DANA, atau aplikasi bank</small>
  </div>
  <div class="pay-detail">
    <dl>
      <dt>Total</dt><dd><?= rupiah($total) ?></dd>
      <dt id="payLblNomor">Nomor</dt>
      <dd><span id="payNomor"></span><button type="button" class="pay-copy" id="payCopy">Salin</button></dd>
      <dt>Atas nama</dt><dd id="payNama"></dd>
    </dl>
    <div class="pay-upload">
      <label for="buktiBayar">Upload bukti pembayaran</label>
      <input type="file" id="buktiBayar" name="bukti_bayar" accept="image/jpeg,image/png,image/webp">
      <small>JPG, PNG, atau WEBP, maksimal 2 MB.</small>
      <img id="buktiPreview" alt="Pratinjau bukti">
    </div>
  </div>
</div>

<script>
(function () {
  var PAY = <?= json_encode($metodeList, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) ?>;
  var box = document.getElementById('paySelect');
  var cur = document.getElementById('payCurrent');
  var list = document.getElementById('payOptions');
  var panel = document.getElementById('payPanel');
  var file = document.getElementById('buktiBayar');
  var prev = document.getElementById('buktiPreview');

  function toggle(open) {
    if (typeof open !== 'boolean') open = list.hidden;
    list.hidden = !open;
    box.classList.toggle('open', open);
    cur.setAttribute('aria-expanded', open ? 'true' : 'false');
  }

  function update() {
    var r = document.querySelector('input[name="metode_bayar"]:checked');
    if (!r) return;
    var m = PAY[r.value];
    document.querySelectorAll('.pay-opt').forEach(function (l) { l.classList.toggle('is-active', l.contains(r)); });
    var wrap = document.getElementById('curIcoWrap');
    wrap.textContent = '';
    if (m.logo) {
      var box = document.createElement('span'); box.className = 'pay-logo';
      var im = document.createElement('img'); im.src = m.logo; im.alt = m.nama;
      box.appendChild(im); wrap.appendChild(box);
    } else {
      var em = document.createElement('span'); em.className = 'pay-ico'; em.textContent = m.ikon || '';
      wrap.appendChild(em);
    }
    document.getElementById('curNama').textContent = m.nama;
    document.getElementById('curKet').textContent = '(' + m.keterangan + ')';
    var perlu = !!m.butuh_bukti;
    panel.hidden = !perlu;
    file.required = perlu;
    if (perlu) {
      document.getElementById('payLblNomor').textContent = m.label_nomor;
      document.getElementById('payNomor').textContent = m.nomor;
      document.getElementById('payNama').textContent = m.atas_nama;
    }
  }

  cur.addEventListener('click', function () { toggle(); });
  document.querySelectorAll('input[name="metode_bayar"]').forEach(function (i) {
    i.addEventListener('change', function () { update(); toggle(false); });
  });
  document.addEventListener('click', function (e) { if (!box.contains(e.target)) toggle(false); });
  document.addEventListener('keydown', function (e) { if (e.key === 'Escape') toggle(false); });

  file.addEventListener('change', function () {
    var f = file.files && file.files[0];
    if (f && f.size > 2 * 1024 * 1024) { alert('Ukuran file maksimal 2 MB.'); file.value = ''; prev.style.display = 'none'; return; }
    if (f) { prev.src = URL.createObjectURL(f); prev.style.display = 'block'; } else { prev.style.display = 'none'; }
  });

  var btn = document.getElementById('payCopy');
  btn.addEventListener('click', function () {
    var teks = document.getElementById('payNomor').textContent.replace(/[^0-9]/g, '');
    if (navigator.clipboard) navigator.clipboard.writeText(teks);
    btn.textContent = 'Tersalin';
    setTimeout(function () { btn.textContent = 'Salin'; }, 1500);
  });

  update();
})();
</script>

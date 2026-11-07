<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/db.php';

// -------------------- Helpers --------------------
function rupiah($angka) {
    return 'Rp ' . number_format((float)$angka, 0, ',', '.');
}

function render_stars($rating) {
    $rating = (float)$rating;
    $full = floor($rating);
    $half = ($rating - $full) >= 0.5;
    $out = str_repeat('★', (int)$full);
    if ($half) $out .= '½';
    return $out;
}

function clean($conn, $str) {
    return mysqli_real_escape_string($conn, trim($str));
}

// -------------------- Ikon SVG (garis, bukan emoji) --------------------
function icon($name, $size = 20) {
    $paths = [
        'cart'       => '<circle cx="9" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.5 3h2l2.6 12.6a2 2 0 0 0 2 1.6h8.4a2 2 0 0 0 2-1.6L21.5 7H6"/>',
        'book'       => '<path d="M4 4.5A2.5 2.5 0 0 1 6.5 2H20v17H6.5A2.5 2.5 0 0 0 4 21.5"/><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/>',
        'book-open'  => '<path d="M12 5.2c-1.8-1-4.3-1.4-7-1.1v13.4c2.7-.3 5.2.1 7 1.1 1.8-1 4.3-1.4 7-1.1V4.1c-2.7-.3-5.2.1-7 1.1Z"/><path d="M12 5.2v13.4"/>',
        'flask'      => '<path d="M9.5 2.5h5"/><path d="M10.5 2.5v6.2L5.8 18a2 2 0 0 0 1.8 2.9h8.8a2 2 0 0 0 1.8-2.9l-4.7-9.3V2.5"/><path d="M8 15h8"/>',
        'bank'       => '<path d="M12 2.5 21.5 8H2.5L12 2.5Z"/><path d="M4.5 8v11"/><path d="M9 8v11"/><path d="M15 8v11"/><path d="M19.5 8v11"/><path d="M2.5 21.5h19"/>',
        'briefcase'  => '<rect x="2.5" y="7" width="19" height="13" rx="2"/><path d="M8 7V5.5A1.5 1.5 0 0 1 9.5 4h5A1.5 1.5 0 0 1 16 5.5V7"/><path d="M2.5 12.5h19"/>',
        'cap'        => '<path d="M2 9l10-5 10 5-10 5-10-5Z"/><path d="M6 11.5V16c0 1.4 2.7 3 6 3s6-1.6 6-3v-4.5"/><path d="M22 9v6"/>',
        'shield'     => '<path d="M12 2.5l8 3.2v6c0 5-3.4 8.6-8 9.8-4.6-1.2-8-4.8-8-9.8v-6l8-3.2Z"/><path d="m8.5 12 2.3 2.3L15.5 9.5"/>',
        'truck'      => '<rect x="1.5" y="6.5" width="13" height="10" rx="1.2"/><path d="M14.5 10.5H18l3.5 3.5v2.5h-3.5"/><circle cx="6" cy="18" r="1.8"/><circle cx="17.5" cy="18" r="1.8"/>',
        'tag'        => '<path d="M12.5 2.5H20a1.5 1.5 0 0 1 1.5 1.5v7.5a1.5 1.5 0 0 1-.44 1.06l-9 9a1.5 1.5 0 0 1-2.12 0l-8-8a1.5 1.5 0 0 1 0-2.12l9-9A1.5 1.5 0 0 1 12.5 2.5Z"/><circle cx="16.5" cy="7.5" r="1.5"/>',
        'headset'    => '<path d="M3 13.5v-2a9 9 0 0 1 18 0v2"/><rect x="3" y="13.5" width="5" height="6.5" rx="1.5"/><rect x="16" y="13.5" width="5" height="6.5" rx="1.5"/><path d="M21 20v.5a3 3 0 0 1-3 3h-4"/>',
        'search'     => '<circle cx="10.5" cy="10.5" r="7"/><path d="m20.5 20.5-4.6-4.6"/>',
        'mail'       => '<rect x="2.5" y="4.5" width="19" height="15" rx="2"/><path d="m3 6 9 7 9-7"/>',
        'phone'      => '<path d="M4.5 3.5h3.6l1.6 4.4-2.2 2A13 13 0 0 0 13.6 16l2-2.2 4.4 1.6v3.6a1.5 1.5 0 0 1-1.6 1.5A17 17 0 0 1 3 4.6a1.5 1.5 0 0 1 1.5-1.1Z"/>',
        'pin'        => '<path d="M12 21.5s7-6.6 7-12A7 7 0 0 0 5 9.5c0 5.4 7 12 7 12Z"/><circle cx="12" cy="9.5" r="2.5"/>',
        'arrow-right'=> '<path d="M4 12h16"/><path d="m14 6 6 6-6 6"/>',
        'arrow-left' => '<path d="M20 12H4"/><path d="m10 18-6-6 6-6"/>',
        'lock'       => '<rect x="4.5" y="10.5" width="15" height="10" rx="2"/><path d="M8 10.5V7a4 4 0 0 1 8 0v3.5"/>',
        'trash'      => '<path d="M4 7h16"/><path d="M9.5 7V4.8c0-.7.6-1.3 1.3-1.3h2.4c.7 0 1.3.6 1.3 1.3V7"/><path d="M6.5 7 7.3 20a1.5 1.5 0 0 0 1.5 1.4h6.4a1.5 1.5 0 0 0 1.5-1.4L17.5 7"/><path d="M10 11v6"/><path d="M14 11v6"/>',
        'user'       => '<circle cx="12" cy="8" r="3.8"/><path d="M4.5 20.5a7.5 7.5 0 0 1 15 0"/>',
        'package'    => '<path d="M3.5 7.5 12 3l8.5 4.5v9L12 21l-8.5-4.5v-9Z"/><path d="M3.8 7.7 12 12l8.2-4.3"/><path d="M12 12v9"/>',
        'check-circle' => '<circle cx="12" cy="12" r="9"/><path d="m8.5 12.5 2.3 2.3 4.7-5"/>',
        'file-text'  => '<path d="M6.5 2.5h7l4 4v13a1.5 1.5 0 0 1-1.5 1.5h-9.5A1.5 1.5 0 0 1 5 19.5v-15A1.5 1.5 0 0 1 6.5 2.5Z"/><path d="M13.5 2.5v4h4"/><path d="M8.5 12.5h7"/><path d="M8.5 16h7"/>',
        'calendar'   => '<rect x="3.5" y="5" width="17" height="15.5" rx="2"/><path d="M8 3v4"/><path d="M16 3v4"/><path d="M3.5 10h17"/>',
        'home'       => '<path d="m3.5 10.5 8.5-7 8.5 7"/><path d="M5.5 9v10.5a1 1 0 0 0 1 1h11a1 1 0 0 0 1-1V9"/><path d="M9.5 20.5V14a1 1 0 0 1 1-1h3a1 1 0 0 1 1 1v6.5"/>',
        'logout'     => '<path d="M9 4.5H6a2 2 0 0 0-2 2v11a2 2 0 0 0 2 2h3"/><path d="M15.5 16.5 20 12l-4.5-4.5"/><path d="M20 12H9"/>',
        'edit'       => '<path d="M4 20h4L18.5 9.5a2.1 2.1 0 0 0-3-3L5 17v3Z"/><path d="M13.5 7.5 16.5 10.5"/>',
        'menu'       => '<path d="M4 6h16"/><path d="M4 12h16"/><path d="M4 18h16"/>',
        'wallet'     => '<path d="M3.5 7.5A2 2 0 0 1 5.5 5.5H17a1.5 1.5 0 0 1 1.5 1.5v1.5"/><path d="M3.5 7.5v10a2 2 0 0 0 2 2h13a1.5 1.5 0 0 0 1.5-1.5v-8a1.5 1.5 0 0 0-1.5-1.5H5.5a2 2 0 0 1-2-1.5"/><circle cx="16" cy="13.5" r="1.1"/>',
        'bag'        => '<path d="M5 8h14l-1 12a1 1 0 0 1-1 .9H7a1 1 0 0 1-1-.9L5 8Z"/><path d="M8.5 8V6.5a3.5 3.5 0 0 1 7 0V8"/>',
        'printer'    => '<path d="M7 9V4h10v5"/><rect x="4.5" y="9" width="15" height="8" rx="1.8"/><path d="M7.5 14h9v6h-9z"/>',
        'clock'      => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3.5 2"/>',
    ];
    $p = $paths[$name] ?? $paths['book'];
    return '<svg width="'.(int)$size.'" height="'.(int)$size.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">'.$p.'</svg>';
}

// Pilih ikon kategori berdasarkan nama kategori (bukan dari kolom emoji)
function kategori_icon($nama) {
    $n = mb_strtolower($nama);
    if (strpos($n, 'non') !== false) return 'briefcase';
    if (strpos($n, 'akademik') !== false || strpos($n, 'pendidikan') !== false) return 'cap';
    if (strpos($n, 'anak') !== false) return 'shield';
    if (strpos($n, 'sains') !== false || strpos($n, 'teknologi') !== false) return 'flask';
    if (strpos($n, 'sejarah') !== false || strpos($n, 'biografi') !== false) return 'bank';
    return 'book-open';
}

// Warna pill kategori, dirotasi per id supaya tidak monoton satu warna
function kategori_tag_class($kategoriId) {
    $palet = ['tag-rose', 'tag-teal', 'tag-tan', 'tag-blue'];
    $i = ((int)$kategoriId - 1) % count($palet);
    if ($i < 0) $i = 0;
    return $palet[$i];
}

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function is_admin() {
    return is_logged_in() && $_SESSION['role'] === 'admin';
}

function require_login() {
    if (!is_logged_in()) {
        $current = $_SERVER['REQUEST_URI'] ?? base_url('index.php');
        header('Location: ' . base_url('login.php') . '?redirect=' . urlencode($current));
        exit;
    }
}

function require_admin() {
    if (!is_admin()) {
        header('Location: ' . base_url('admin/login.php'));
        exit;
    }
}

// Base url helper: otomatis mendeteksi folder tempat project ini
// ditaruh di dalam htdocs/www, jadi tetap berfungsi walau foldernya
// dipindah, di-rename, atau ada di dalam beberapa folder sekaligus
// (mis. htdocs/bookstore-ruangbaca/bookstore/).
function base_url($path = '') {
    static $base = null;
    if ($base === null) {
        $docRoot = isset($_SERVER['DOCUMENT_ROOT'])
            ? str_replace('\\', '/', rtrim($_SERVER['DOCUMENT_ROOT'], '/\\'))
            : '';
        $projectRoot = str_replace('\\', '/', realpath(__DIR__ . '/..'));
        if ($docRoot !== '' && $projectRoot !== false && strpos($projectRoot, $docRoot) === 0) {
            $base = substr($projectRoot, strlen($docRoot));
        } else {
            $base = '';
        }
    }
    return rtrim($base, '/') . '/' . ltrim($path, '/');
}

function cart_count() {
    if (!is_logged_in() || empty($_SESSION['cart'])) return 0;
    $count = 0;
    foreach ($_SESSION['cart'] as $item) {
        $count += $item['jumlah'];
    }
    return $count;
}

function flash($key, $msg = null) {
    if ($msg !== null) {
        $_SESSION['flash'][$key] = $msg;
        return;
    }
    if (!empty($_SESSION['flash'][$key])) {
        $m = $_SESSION['flash'][$key];
        unset($_SESSION['flash'][$key]);
        return $m;
    }
    return null;
}

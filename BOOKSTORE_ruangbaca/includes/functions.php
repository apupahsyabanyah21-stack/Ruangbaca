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

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function is_admin() {
    return is_logged_in() && $_SESSION['role'] === 'admin';
}

function require_login() {
    if (!is_logged_in()) {
        header('Location: ' . base_url('login.php'));
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
    if (empty($_SESSION['cart'])) return 0;
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

<?php
require_once __DIR__ . '/../../includes/functions.php';
require_admin();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $page_title ?? 'Admin' ?> - Ruang Baca</title>
<link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>">
<link rel="stylesheet" href="<?= base_url('assets/css/admin.css') ?>">
</head>
<body>
<div class="admin-body">
<?php require __DIR__ . '/sidebar.php'; ?>
<main class="admin-main">
  <div class="admin-topbar">
    <h1><?= $page_title ?? '' ?></h1>
    <div class="who">👤 <?= htmlspecialchars($_SESSION['nama']) ?> (Admin)</div>
  </div>

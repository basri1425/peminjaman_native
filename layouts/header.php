<?php
/*
|--------------------------------------------------------------------------
| File        : header.php
| Folder      : layouts
| Fungsi      : Header Layout Aplikasi
|--------------------------------------------------------------------------
|
| File ini berisi:
| - Deklarasi HTML
| - Meta Tag
| - Judul Halaman
| - Bootstrap CSS
| - Bootstrap Icons
| - CSS Tambahan
|
|--------------------------------------------------------------------------
*/
require_once __DIR__ . "/../config/config.php";
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aplikasi Peminjaman Alat</title>
    <!-- Bootstrap CSS -->
    <link href="<?= BASE_URL ?>/assets/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="<?= BASE_URL ?>/assets/icons/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <!-- CSS Tambahan -->
    <link href="<?= BASE_URL ?>/assets/css/style.css" rel="stylesheet">
</head>

<body class="bg-light">

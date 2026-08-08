<?php

require 'config/session.php';

if (!isset($_SESSION['login'])) {
    header('Location: login.php');
    exit();
}

?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Akses Ditolak</title>
    <?php include 'layouts/header.php'; ?>
</head>

<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center mt-5">
            <div class="col-lg-6">
                <div class="card shadow">
                    <div class="card-body text-center p-5">
                        <div class="mb-4">
                            <i class="bi bi-shield-lock-fill
                                  text-danger"
                                style="font-size:80px;"></i>
                        </div>
                        <h2 class="text-danger mb-3">
                            Akses Ditolak
                        </h2>
                        <p class="text-muted">
                            Maaf,
                            <strong>
                                <?= htmlspecialchars($_SESSION['nama_lengkap']) ?>
                            </strong>
                            tidak memiliki hak akses untuk membuka halaman ini.
                        </p>
                        <hr>
                        <p>
                            Silakan kembali ke dashboard sesuai hak akses Anda.
                        </p>
                        <?php
                        switch ($_SESSION['level']) {
                            case 'Administrator':
                                $dashboard = 'administrator/dashboard/index.php';
                                break;
                            case 'Petugas':
                                $dashboard = 'petugas/dashboard/index.php';
                                break;
                            case 'Peminjam':
                                $dashboard = 'peminjam/dashboard/index.php';
                                break;
                            default:
                                $dashboard = 'login.php';
                        }
                        ?>

                        <a href="<?= $dashboard ?>" class="btn btn-primary">
                            <i class="bi bi-house-door-fill"></i>
                            Kembali ke Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include 'layouts/script.php'; ?>
</body>
</html>
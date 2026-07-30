<?php

/*
|--------------------------------------------------------------------------
| Konfigurasi Aplikasi
|--------------------------------------------------------------------------
*/

require '../../config/session.php';
require '../../config/database.php';

/*
|--------------------------------------------------------------------------
| Validasi Login
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION['login'])) {
    header('Location: ../../login.php');
    exit();
}

/*
|--------------------------------------------------------------------------
| Validasi Hak Akses
|--------------------------------------------------------------------------
*/

if ($_SESSION['level'] != 'Peminjam') {
    header('Location: ../../unauthorized.php');
    exit();
}

/*
|--------------------------------------------------------------------------
| Judul Halaman
|--------------------------------------------------------------------------
*/

$title = 'Detail Alat';

/*
|--------------------------------------------------------------------------
| Validasi ID Alat
|--------------------------------------------------------------------------
*/

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: index.php');
    exit();
}

$idAlat = (int) $_GET['id'];

/*
|--------------------------------------------------------------------------
| Data Detail Alat
|--------------------------------------------------------------------------
*/

$sql = "

    SELECT

        a.id_alat,
        a.nama_alat,
        a.stok,
        a.kondisi,
        a.lokasi,
        a.foto,
        a.id_kategori,

        k.nama_kategori

    FROM alat a

    INNER JOIN kategori k

        ON a.id_kategori = k.id_kategori

    WHERE

        a.id_alat = ?

    LIMIT 1

";

$stmt = $conn->prepare($sql);

$stmt->bind_param('i', $idAlat);

$stmt->execute();

$result = $stmt->get_result();

/*
|--------------------------------------------------------------------------
| Validasi Data
|--------------------------------------------------------------------------
*/

if ($result->num_rows == 0) {
    $stmt->close();

    $conn->close();

    header('Location: index.php');

    exit();
}

$alat = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= $title ?></title>

    <?php include '../../layouts/header.php'; ?>

</head>

<body>

    <?php include '../../layouts/navbar.php'; ?>

    <?php include '../../layouts/sidebar.php'; ?>

    <div class="content-wrapper">

        <!-- Header Halaman -->

        <section class="content-header">

            <div class="container-fluid">

                <div class="row mb-2">

                    <div class="col-sm-6">

                        <h1>

                            <i class="bi bi-tools text-primary"></i>

                            Detail Alat

                        </h1>

                    </div>

                    <div class="col-sm-6">

                        <nav aria-label="breadcrumb">

                            <ol class="breadcrumb float-sm-end">

                                <li class="breadcrumb-item">

                                    Dashboard

                                </li>

                                <li class="breadcrumb-item">

                                    Daftar Alat

                                </li>

                                <li class="breadcrumb-item active">

                                    Detail Alat

                                </li>

                            </ol>

                        </nav>

                    </div>

                </div>

            </div>

        </section>

        <!-- Content -->

        <section class="content">

            <div class="container-fluid">

                <div class="card shadow-sm">

                    <div class="card-header bg-primary text-white">

                        <h5 class="mb-0">

                            <i class="bi bi-info-circle"></i>

                            Informasi Detail Alat

                        </h5>

                    </div>

                    <div class="card-body">

                        <div class="row">

                            <!-- Foto -->

                            <div class="col-lg-4 text-center mb-4">

                                <?php if (!empty($alat['foto']) && file_exists("../../assets/img/alat/" . $alat['foto'])) : ?>

                                <img src="../../assets/img/alat/<?= htmlspecialchars($alat['foto']) ?>"
                                    class="img-fluid img-thumbnail rounded shadow-sm"
                                    style="max-height:320px; object-fit:cover;">

                                <?php else : ?>

                                <img src="../../assets/img/no-image.png"
                                    class="img-fluid img-thumbnail rounded shadow-sm"
                                    style="max-height:320px; object-fit:cover;">

                                <?php endif; ?>

                            </div>

                            <!-- Informasi -->

                            <div class="col-lg-8">

                                <table class="table table-bordered">

                                    <tr>

                                        <th width="30%">

                                            Nama Alat

                                        </th>

                                        <td>

                                            <?= htmlspecialchars($alat['nama_alat']) ?>

                                        </td>

                                    </tr>

                                    <tr>

                                        <th>

                                            Kategori

                                        </th>

                                        <td>

                                            <?= htmlspecialchars($alat['nama_kategori']) ?>

                                        </td>

                                    </tr>

                                    <tr>

                                        <th>

                                            Lokasi

                                        </th>

                                        <td>

                                            <?= htmlspecialchars($alat['lokasi']) ?>

                                        </td>

                                    </tr>

                                    <tr>

                                        <th>

                                            Kondisi

                                        </th>

                                        <td>
                                            <?php
                                            
                                            /*
|--------------------------------------------------------------------------
| Badge Kondisi
|--------------------------------------------------------------------------
*/
                                            
                                            switch ($alat['kondisi']) {
                                                case 'Baik':
                                                    $badgeKondisi = 'success';
                                                    break;
                                            
                                                case 'Rusak Ringan':
                                                    $badgeKondisi = 'warning';
                                                    break;
                                            
                                                case 'Rusak Berat':
                                                    $badgeKondisi = 'danger';
                                                    break;
                                            
                                                default:
                                                    $badgeKondisi = 'secondary';
                                                    break;
                                            }
                                            
                                            /*
|--------------------------------------------------------------------------
| Badge Stok
|--------------------------------------------------------------------------
*/
                                            
                                            if ($alat['stok'] > 5) {
                                                $badgeStok = 'success';
                                            } elseif ($alat['stok'] > 0) {
                                                $badgeStok = 'warning';
                                            } else {
                                                $badgeStok = 'danger';
                                            }
                                            
                                            ?>

                                            <span class="badge bg-<?= $badgeKondisi ?>">

                                                <?= htmlspecialchars($alat['kondisi']) ?>

                                            </span>

                                        </td>

                                    </tr>

                                    <tr>

                                        <th>

                                            Stok

                                        </th>

                                        <td>

                                            <span class="badge bg-<?= $badgeStok ?>">

                                                <?= (int) $alat['stok'] ?> Unit

                                            </span>

                                        </td>

                                    </tr>

                                </table>

                            </div>

                        </div>

                        <!-- Deskripsi -->

                        <div class="row mt-3">

                            <div class="col-12">

                                <div class="card border">

                                    <div class="card-header bg-light">

                                        <strong>

                                            <i class="bi bi-card-text"></i>

                                            Deskripsi Alat

                                        </strong>

                                    </div>

                                    <div class="card-body">

                                        <?php if (isset($alat['deskripsi']) && trim($alat['deskripsi']) != '') : ?>

                                        <?= nl2br(htmlspecialchars($alat['deskripsi'])) ?>

                                        <?php else : ?>

                                        <span class="text-muted">

                                            Belum ada deskripsi alat.

                                        </span>

                                        <?php endif; ?>

                                    </div>

                                </div>

                            </div>

                        </div>

                        <!-- Tombol -->

                        <div class="row mt-4">

                            <div class="col-12">

                                <a href="index.php" class="btn btn-secondary">

                                    <i class="bi bi-arrow-left"></i>

                                    Kembali

                                </a>

                                <?php if ($alat['stok'] > 0) : ?>

                                <a href="../peminjaman/tambah.php?id=<?= $alat['id_alat'] ?>" class="btn btn-primary">

                                    <i class="bi bi-box-arrow-in-right"></i>

                                    Ajukan Peminjaman

                                </a>

                                <?php else : ?>

                                <button class="btn btn-danger" disabled>

                                    <i class="bi bi-x-circle"></i>

                                    Stok Habis

                                </button>

                                <?php endif; ?>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </section>

    </div>
    <?php include '../../layouts/footer.php'; ?>

</body>

</html>

<?php

/*
|--------------------------------------------------------------------------
| Membebaskan Resource Result
|--------------------------------------------------------------------------
*/

if (isset($result) && $result instanceof mysqli_result) {
    $result->free();
}

/*
|--------------------------------------------------------------------------
| Menutup Prepared Statement
|--------------------------------------------------------------------------
*/

if (isset($stmt) && $stmt instanceof mysqli_stmt) {
    $stmt->close();
}

/*
|--------------------------------------------------------------------------
| Menutup Koneksi Database
|--------------------------------------------------------------------------
*/

if (isset($conn) && $conn instanceof mysqli) {
    $conn->close();
}

?>

<?php

/*
|--------------------------------------------------------------------------
| Konfigurasi Aplikasi
|--------------------------------------------------------------------------
*/

require "../../config/session.php";
require "../../config/database.php";

/*
|--------------------------------------------------------------------------
| Validasi Login
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION['login'])) {

    header("Location: ../../login.php");
    exit;

}

/*
|--------------------------------------------------------------------------
| Mengambil ID Pengembalian
|--------------------------------------------------------------------------
*/

$idPengembalian = isset($_GET['id'])
    ? (int) $_GET['id']
    : 0;

if ($idPengembalian <= 0) {

    header("Location: index.php");
    exit;

}

/*
|--------------------------------------------------------------------------
| Mengambil Data Header Pengembalian
|--------------------------------------------------------------------------
*/

$sql = "

SELECT

    pg.id_pengembalian,

    pg.tanggal_pengembalian,

    pg.keterangan,

    p.id_peminjaman,

    p.tanggal_pinjam,

    p.tanggal_kembali,

    p.status,

    u.nama_lengkap

FROM pengembalian pg

INNER JOIN peminjaman p
ON pg.id_peminjaman = p.id_peminjaman

INNER JOIN users u
ON p.id_user = u.id_user

WHERE

pg.id_pengembalian = ?

LIMIT 1

";

$stmt = $conn->prepare($sql);

if (!$stmt) {

    die($conn->error);

}

$stmt->bind_param(

    "i",

    $idPengembalian

);

$stmt->execute();

$data = $stmt->get_result()->fetch_assoc();

$stmt->close();

/*
|--------------------------------------------------------------------------
| Validasi Data
|--------------------------------------------------------------------------
*/

if (!$data) {

    echo "

    <script>

        alert('Data pengembalian tidak ditemukan.');

        window.location='index.php';

    </script>

    ";

    exit;

}

/*
|--------------------------------------------------------------------------
| Mengambil Detail Pengembalian
|--------------------------------------------------------------------------
*/

$sql = "

SELECT

    dp.id_detail_pengembalian,

    dp.id_alat,

    dp.jumlah,

    dp.kondisi,

    dp.keterangan,

    a.nama_alat

FROM detail_pengembalian dp

INNER JOIN alat a
ON dp.id_alat = a.id_alat

WHERE

dp.id_pengembalian = ?

ORDER BY

a.nama_alat ASC

";

$stmt = $conn->prepare($sql);

if (!$stmt) {

    die($conn->error);

}

$stmt->bind_param(

    "i",

    $idPengembalian

);

$stmt->execute();

$resultDetail = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Detail Pengembalian</title>

    <?php include "../../layouts/header.php"; ?>

</head>

<body>

    <div class="wrapper">

        <?php include '../../layouts/navbar.php'; ?>

        <?php include '../../layouts/sidebar.php'; ?>

        <div class="content-wrapper">

            <!-- Header -->

            <section class="content-header">

                <div class="container-fluid">

                    <div class="row mb-2">

                        <div class="col-sm-6">

                            <h1>

                                <i class="fas fa-eye text-success"></i>

                                Detail Pengembalian

                            </h1>

                        </div>

                        <div class="col-sm-6">

                            <ol class="breadcrumb float-sm-right">

                                <li class="breadcrumb-item">

                                    <a href="../dashboard/index.php">

                                        Dashboard

                                    </a>

                                </li>

                                <li class="breadcrumb-item">

                                    <a href="index.php">

                                        Pengembalian

                                    </a>

                                </li>

                                <li class="breadcrumb-item active">

                                    Detail

                                </li>

                            </ol>

                        </div>

                    </div>

                </div>

            </section>

            <!-- Content -->

            <section class="content">

                <div class="container-fluid">

                    <div class="card card-success">

                        <div class="card-header">

                            <h3 class="card-title">

                                <i class="fas fa-file-alt"></i>

                                Informasi Pengembalian

                            </h3>

                        </div>

                        <div class="card-body">

                            <div class="row">

                                <div class="col-md-6">

                                    <table class="table table-borderless">

                                        <tr>

                                            <th width="40%">

                                                No. Pengembalian

                                            </th>

                                            <td>

                                                :

                                            </td>

                                            <td>

                                                <?= 'KMB-' . str_pad($data['id_pengembalian'], 5, '0', STR_PAD_LEFT)
                                                ?>

                                            </td>

                                        </tr>

                                        <tr>

                                            <th>

                                                No. Peminjaman

                                            </th>

                                            <td>

                                                :

                                            </td>

                                            <td>

                                                <?= 'PJM-' . str_pad($data['id_peminjaman'], 5, '0', STR_PAD_LEFT)
                                                ?>

                                            </td>

                                        </tr>

                                        <tr>

                                            <th>

                                                Nama Peminjam

                                            </th>

                                            <td>

                                                :

                                            </td>

                                            <td>

                                                <?= htmlspecialchars($data['nama_lengkap']) ?>

                                            </td>

                                        </tr>

                                        <tr>

                                            <th>

                                                Status

                                            </th>

                                            <td>

                                                :

                                            </td>

                                            <td>

                                                <span class="badge bg-success">

                                                    <?= htmlspecialchars($data['status']) ?>

                                                </span>

                                            </td>

                                        </tr>

                                    </table>

                                </div>

                                <div class="col-md-6">

                                    <table class="table table-borderless">

                                        <tr>

                                            <th width="40%">

                                                Tanggal Pinjam

                                            </th>

                                            <td>

                                                :

                                            </td>

                                            <td>

                                                <?= date('d-m-Y', strtotime($data['tanggal_pinjam'])) ?>

                                            </td>

                                        </tr>

                                        <tr>

                                            <th>

                                                Rencana Kembali

                                            </th>

                                            <td>

                                                :

                                            </td>

                                            <td>

                                                <?= date('d-m-Y', strtotime($data['tanggal_kembali'])) ?>

                                            </td>

                                        </tr>

                                        <tr>

                                            <th>

                                                Tanggal Pengembalian

                                            </th>

                                            <td>

                                                :

                                            </td>

                                            <td>

                                                <?= date('d-m-Y', strtotime($data['tanggal_pengembalian'])) ?>

                                            </td>

                                        </tr>

                                    </table>

                                </div>

                            </div>

                            <div class="mb-3">

                                <label>

                                    <strong>

                                        Keterangan Pengembalian

                                    </strong>

                                </label>

                                <textarea class="form-control" rows="3" readonly><?= htmlspecialchars($data['keterangan']) ?></textarea>

                            </div>

                            <hr>

                            <h5>

                                <i class="fas fa-box"></i>

                                Daftar Alat Yang Dikembalikan

                            </h5>

                            <div class="table-responsive">
                                <table class="table table-bordered table-hover table-striped">

                                    <thead class="table-success text-center">

                                        <tr>

                                            <th width="5%">No</th>

                                            <th>Nama Alat</th>

                                            <th width="10%">Jumlah</th>

                                            <th width="18%">Kondisi</th>

                                            <th>Keterangan</th>

                                        </tr>

                                    </thead>

                                    <tbody>

                                        <?php

        $no = 1;

        while ($row = $resultDetail->fetch_assoc()) :

        ?>

                                        <tr>

                                            <td class="text-center">

                                                <?= $no++ ?>

                                            </td>

                                            <td>

                                                <?= htmlspecialchars($row['nama_alat']) ?>

                                            </td>

                                            <td class="text-center">

                                                <?= $row['jumlah'] ?>

                                            </td>

                                            <td class="text-center">

                                                <?php
                                                
                                                switch ($row['kondisi']) {
                                                    case 'Baik':
                                                        echo '<span class="badge bg-success">Baik</span>';
                                                
                                                        break;
                                                
                                                    case 'Rusak Ringan':
                                                        echo '<span class="badge bg-warning text-dark">Rusak Ringan</span>';
                                                
                                                        break;
                                                
                                                    case 'Rusak Berat':
                                                        echo '<span class="badge bg-danger">Rusak Berat</span>';
                                                
                                                        break;
                                                
                                                    case 'Hilang':
                                                        echo '<span class="badge bg-dark">Hilang</span>';
                                                
                                                        break;
                                                
                                                    default:
                                                        echo '<span class="badge bg-secondary">' . htmlspecialchars($row['kondisi']) . '</span>';
                                                }
                                                
                                                ?>

                                            </td>

                                            <td>

                                                <?= !empty($row['keterangan']) ? htmlspecialchars($row['keterangan']) : '-' ?>

                                            </td>

                                        </tr>

                                        <?php endwhile; ?>

                                    </tbody>

                                </table>

                            </div>

                            <hr>

                            <div class="d-flex justify-content-end">

                                <a href="index.php" class="btn btn-secondary">

                                    <i class="fas fa-arrow-left"></i>

                                    Kembali

                                </a>

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
| Menutup Statement
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

$conn->close();

?>

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

if ($_SESSION['level'] != 'Petugas') {
    header('Location: ../../unauthorized.php');
    exit();
}

/*
|--------------------------------------------------------------------------
| Judul Halaman
|--------------------------------------------------------------------------
*/

$title = 'Data Peminjaman';

/*
|--------------------------------------------------------------------------
| Mengambil Data Peminjaman
|--------------------------------------------------------------------------
*/

$sql = "

    SELECT

        p.id_peminjaman,
        p.id_user,
        p.tanggal_pinjam,
        p.tanggal_kembali,
        p.status,
        p.created_at,

        u.nama_lengkap

    FROM peminjaman p

    INNER JOIN users u

        ON p.id_user = u.id_user

    ORDER BY

        p.id_peminjaman DESC

";

$result = $conn->query($sql);

if (!$result) {
    die($conn->error);
}

/*
|--------------------------------------------------------------------------
| Menghitung Jumlah Data
|--------------------------------------------------------------------------
*/

$totalData = $result->num_rows;

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

                            <i class="bi bi-box-seam text-primary"></i>

                            Data Peminjaman

                        </h1>

                    </div>

                    <div class="col-sm-6">

                        <nav aria-label="breadcrumb">

                            <ol class="breadcrumb float-sm-end">

                                <li class="breadcrumb-item">

                                    <a href="../dashboard/index.php">

                                        Dashboard

                                    </a>

                                </li>

                                <li class="breadcrumb-item active">

                                    Data Peminjaman

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

                        <div class="d-flex justify-content-between align-items-center">

                            <h5 class="mb-0">

                                <i class="bi bi-list-ul"></i>

                                Daftar Peminjaman

                            </h5>

                            <span class="badge bg-light text-dark">

                                Total Data :

                                <?= number_format($totalData) ?>

                            </span>

                        </div>

                    </div>

                    <div class="card-body">

                        <div class="table-responsive">

                            <table class="table table-bordered table-hover align-middle">

                                <thead class="table-light">

                                    <tr class="text-center">

                                        <th width="5%">

                                            No

                                        </th>

                                        <th width="15%">

                                            Tanggal Pinjam

                                        </th>

                                        <th>

                                            Nama Peminjam

                                        </th>

                                        <th width="15%">

                                            Tanggal Kembali

                                        </th>

                                        <th width="15%">

                                            Status

                                        </th>

                                        <th width="20%">

                                            Aksi

                                        </th>

                                    </tr>

                                </thead>

                                <tbody>
                                    <?php

if ($totalData > 0) :

    $no = 1;

    while ($row = $result->fetch_assoc()) :

?>

                                    <tr>

                                        <td class="text-center">

                                            <?= $no++ ?>

                                        </td>

                                        <td>

                                            <?= date('d-m-Y', strtotime($row['tanggal_pinjam'])) ?>

                                        </td>

                                        <td>

                                            <?= htmlspecialchars($row['nama_lengkap']) ?>

                                        </td>

                                        <td>

                                            <?= date('d-m-Y', strtotime($row['tanggal_kembali'])) ?>

                                        </td>

                                        <td class="text-center">

                                            <?php
                                            
                                            switch ($row['status']) {
                                                case 'Menunggu':
                                                    echo '<span class="badge bg-warning text-dark">
                                                                    Menunggu
                                                                  </span>';
                                            
                                                    break;
                                            
                                                case 'Disetujui':
                                                    echo '<span class="badge bg-primary">
                                                                    Disetujui
                                                                  </span>';
                                            
                                                    break;
                                            
                                                case 'Dipinjam':
                                                    echo '<span class="badge bg-info">
                                                                    Dipinjam
                                                                  </span>';
                                            
                                                    break;
                                            
                                                case 'Selesai':
                                                    echo '<span class="badge bg-success">
                                                                    Selesai
                                                                  </span>';
                                            
                                                    break;
                                            
                                                default:
                                                    echo '<span class="badge bg-danger">
                                                                    Ditolak
                                                                  </span>';
                                            }
                                            
                                            ?>

                                        </td>

                                        <td class="text-center">

                                            <a href="detail.php?id=<?= $row['id_peminjaman'] ?>"
                                                class="btn btn-info btn-sm">

                                                <i class="bi bi-eye"></i>

                                                Detail

                                            </a>

                                            <?php if ($row['status'] == 'Menunggu') : ?>

                                            <a href="setujui.php?id=<?= $row['id_peminjaman'] ?>"
                                                class="btn btn-success btn-sm"
                                                onclick="return confirm('Setujui peminjaman ini?')">

                                                <i class="bi bi-check-circle"></i>

                                                Setujui

                                            </a>

                                            <a href="tolak.php?id=<?= $row['id_peminjaman'] ?>"
                                                class="btn btn-danger btn-sm"
                                                onclick="return confirm('Tolak peminjaman ini?')">

                                                <i class="bi bi-x-circle"></i>

                                                Tolak

                                            </a>

                                            <?php elseif ($row['status'] == 'Disetujui') : ?>

                                            <a href="serahkan.php?id=<?= $row['id_peminjaman'] ?>"
                                                class="btn btn-primary btn-sm"
                                                onclick="return confirm('Serahkan alat kepada peminjam?')">

                                                <i class="bi bi-box-arrow-right"></i>

                                                Serahkan

                                            </a>

                                            <?php endif; ?>

                                        </td>

                                    </tr>

                                    <?php

    endwhile;

else :

?>

                                    <tr>

                                        <td colspan="6" class="text-center py-4">

                                            <i class="bi bi-inbox fs-1 text-secondary"></i>

                                            <br><br>

                                            Belum ada data peminjaman.

                                        </td>

                                    </tr>

                                    <?php endif; ?>

                                </tbody>

                            </table>

                        </div>

                    </div>

                </div>

            </div>

        </section>

    </div>

    <?php include '../../layouts/footer.php'; ?>

    <?php include '../../layouts/script.php'; ?>

</body>

</html>

<?php

/*
|--------------------------------------------------------------------------
| Menutup Resource Query
|--------------------------------------------------------------------------
*/

if (isset($result) && $result instanceof mysqli_result) {
    $result->free();
}

/*
|--------------------------------------------------------------------------
| Menutup Koneksi Database
|--------------------------------------------------------------------------
*/

$conn->close();

?>

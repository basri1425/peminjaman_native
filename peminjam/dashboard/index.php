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

$title = 'Dashboard Peminjam';

$idUser = (int) $_SESSION['id_user'];

/*
|--------------------------------------------------------------------------
| Statistik Dashboard
|--------------------------------------------------------------------------
*/

/*
|--------------------------------------------------------------------------
| Total Alat Tersedia
|--------------------------------------------------------------------------
*/

$sqlAlat = "

    SELECT

        COUNT(*) AS total

    FROM alat

    WHERE stok > 0

";

$stmtAlat = $conn->prepare($sqlAlat);

$stmtAlat->execute();

$resultAlat = $stmtAlat->get_result();

$totalAlat = $resultAlat->fetch_assoc()['total'];

/*
|--------------------------------------------------------------------------
| Total Pengajuan Menunggu
|--------------------------------------------------------------------------
*/

$sqlMenunggu = "

    SELECT

        COUNT(*) AS total

    FROM peminjaman

    WHERE id_user = ?

    AND status = 'Menunggu'

";

$stmtMenunggu = $conn->prepare($sqlMenunggu);

$stmtMenunggu->bind_param('i', $idUser);

$stmtMenunggu->execute();

$resultMenunggu = $stmtMenunggu->get_result();

$totalMenunggu = $resultMenunggu->fetch_assoc()['total'];

/*
|--------------------------------------------------------------------------
| Total Sedang Dipinjam
|--------------------------------------------------------------------------
*/

$sqlDipinjam = "

    SELECT

        COUNT(*) AS total

    FROM peminjaman

    WHERE id_user = ?

    AND status = 'Dipinjam'

";

$stmtDipinjam = $conn->prepare($sqlDipinjam);

$stmtDipinjam->bind_param('i', $idUser);

$stmtDipinjam->execute();

$resultDipinjam = $stmtDipinjam->get_result();

$totalDipinjam = $resultDipinjam->fetch_assoc()['total'];

/*
|--------------------------------------------------------------------------
| Total Riwayat Selesai
|--------------------------------------------------------------------------
*/

$sqlSelesai = "

    SELECT

        COUNT(*) AS total

    FROM peminjaman

    WHERE id_user = ?

    AND status = 'Selesai'

";

$stmtSelesai = $conn->prepare($sqlSelesai);

$stmtSelesai->bind_param('i', $idUser);

$stmtSelesai->execute();

$resultSelesai = $stmtSelesai->get_result();

$totalSelesai = $resultSelesai->fetch_assoc()['total'];

/*
|--------------------------------------------------------------------------
| Data Riwayat Peminjaman Terbaru
|--------------------------------------------------------------------------
*/

$sql = "

    SELECT

        p.id_peminjaman,
        p.tanggal_pinjam,
        p.tanggal_kembali,
        p.status

    FROM peminjaman p

    WHERE p.id_user = ?

    ORDER BY

        p.id_peminjaman DESC

    LIMIT 5

";

$stmt = $conn->prepare($sql);

$stmt->bind_param('i', $idUser);

$stmt->execute();

$result = $stmt->get_result();

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

                            <i class="bi bi-speedometer2 text-primary"></i>

                            Dashboard Peminjam

                        </h1>

                    </div>

                    <div class="col-sm-6">

                        <nav aria-label="breadcrumb">

                            <ol class="breadcrumb float-sm-end">

                                <li class="breadcrumb-item active">

                                    Dashboard

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

                <!-- Welcome Card -->

                <div class="card border-0 shadow-sm mb-4">

                    <div class="card-body">

                        <div class="row align-items-center">

                            <div class="col-md-8">

                                <h4 class="mb-1">

                                    Selamat Datang,

                                    <strong>

                                        <?= htmlspecialchars($_SESSION['nama_lengkap']) ?>

                                    </strong>

                                </h4>

                                <p class="text-muted mb-0">

                                    Selamat datang di Sistem Peminjaman Alat.
                                    Melalui dashboard ini Anda dapat melihat
                                    ketersediaan alat, mengajukan peminjaman,
                                    memantau status peminjaman, serta melihat
                                    riwayat transaksi yang pernah dilakukan.

                                </p>

                            </div>

                            <div class="col-md-4 text-md-end mt-3 mt-md-0">

                                <span class="badge bg-primary fs-6">

                                    <?= date('d F Y') ?>

                                </span>

                            </div>

                        </div>

                    </div>

                </div>

                <!-- Statistik Dashboard -->

                <div class="row">

                    <div class="col-lg-3 col-md-6 mb-4">

                        <div class="card border-primary shadow-sm h-100">

                            <div class="card-body text-center">

                                <i class="bi bi-tools fs-1 text-primary"></i>

                                <h6 class="mt-3">

                                    Alat Tersedia

                                </h6>

                                <h2 class="fw-bold">

                                    <?= $totalAlat ?>

                                </h2>

                            </div>

                        </div>

                    </div>

                    <div class="col-lg-3 col-md-6 mb-4">

                        <div class="card border-warning shadow-sm h-100">

                            <div class="card-body text-center">

                                <i class="bi bi-hourglass-split fs-1 text-warning"></i>

                                <h6 class="mt-3">

                                    Menunggu

                                </h6>

                                <h2 class="fw-bold">

                                    <?= $totalMenunggu ?>

                                </h2>

                            </div>

                        </div>

                    </div>

                    <div class="col-lg-3 col-md-6 mb-4">

                        <div class="card border-success shadow-sm h-100">

                            <div class="card-body text-center">

                                <i class="bi bi-box-seam fs-1 text-success"></i>

                                <h6 class="mt-3">

                                    Sedang Dipinjam

                                </h6>

                                <h2 class="fw-bold">

                                    <?= $totalDipinjam ?>

                                </h2>

                            </div>

                        </div>

                    </div>

                    <div class="col-lg-3 col-md-6 mb-4">

                        <div class="card border-info shadow-sm h-100">

                            <div class="card-body text-center">

                                <i class="bi bi-check-circle fs-1 text-info"></i>

                                <h6 class="mt-3">

                                    Riwayat Selesai

                                </h6>

                                <h2 class="fw-bold">

                                    <?= $totalSelesai ?>

                                </h2>

                            </div>

                        </div>

                    </div>

                </div>

                <!-- Card Riwayat Peminjaman -->

                <div class="card shadow-sm">

                    <div class="card-header bg-primary text-white">

                        <h5 class="mb-0">

                            <i class="bi bi-clock-history"></i>

                            Riwayat Peminjaman Terbaru

                        </h5>

                    </div>

                    <div class="card-body table-responsive">
                        <table class="table table-bordered table-hover align-middle">

                            <thead class="table-light">

                                <tr class="text-center">

                                    <th width="5%">No</th>

                                    <th width="18%">Tanggal Pinjam</th>

                                    <th width="18%">Tanggal Kembali</th>

                                    <th width="18%">Status</th>

                                    <th width="12%">Aksi</th>

                                </tr>

                            </thead>

                            <tbody>

                                <?php

        if ($result->num_rows > 0) :

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

                                        <a href="../peminjaman/detail.php?id=<?= $row['id_peminjaman'] ?>"
                                            class="btn btn-sm btn-outline-primary">

                                            <i class="bi bi-eye"></i>

                                            Detail

                                        </a>

                                    </td>

                                </tr>

                                <?php

            endwhile;

        else :

        ?>

                                <tr>

                                    <td colspan="5" class="text-center text-muted py-4">

                                        <i class="bi bi-inbox fs-4 d-block mb-2"></i>

                                        Belum ada riwayat peminjaman.

                                    </td>

                                </tr>

                                <?php endif; ?>

                            </tbody>

                        </table>

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
| Menutup Resource Query
|--------------------------------------------------------------------------
*/

if (isset($result) && $result instanceof mysqli_result) {
    $result->free();
}

if (isset($resultAlat) && $resultAlat instanceof mysqli_result) {
    $resultAlat->free();
}

if (isset($resultMenunggu) && $resultMenunggu instanceof mysqli_result) {
    $resultMenunggu->free();
}

if (isset($resultDipinjam) && $resultDipinjam instanceof mysqli_result) {
    $resultDipinjam->free();
}

if (isset($resultSelesai) && $resultSelesai instanceof mysqli_result) {
    $resultSelesai->free();
}

/*
|--------------------------------------------------------------------------
| Menutup Prepared Statement
|--------------------------------------------------------------------------
*/

if (isset($stmt) && $stmt instanceof mysqli_stmt) {
    $stmt->close();
}

if (isset($stmtAlat) && $stmtAlat instanceof mysqli_stmt) {
    $stmtAlat->close();
}

if (isset($stmtMenunggu) && $stmtMenunggu instanceof mysqli_stmt) {
    $stmtMenunggu->close();
}

if (isset($stmtDipinjam) && $stmtDipinjam instanceof mysqli_stmt) {
    $stmtDipinjam->close();
}

if (isset($stmtSelesai) && $stmtSelesai instanceof mysqli_stmt) {
    $stmtSelesai->close();
}

/*
|--------------------------------------------------------------------------
| Menutup Koneksi Database
|--------------------------------------------------------------------------
*/

$conn->close();

?>

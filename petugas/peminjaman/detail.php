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
| Validasi Parameter ID
|--------------------------------------------------------------------------
*/

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: index.php');
    exit();
}

$id = (int) $_GET['id'];

$title = 'Detail Peminjaman';

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

    u.nama_lengkap,
    u.username,
    u.level,
    u.status AS status_user

FROM peminjaman p

INNER JOIN users u
ON p.id_user = u.id_user

WHERE

p.id_peminjaman = ?

LIMIT 1

";

$stmt = $conn->prepare($sql);

$stmt->bind_param('i', $id);

$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows == 0) {
    header('Location: index.php');
    exit();
}

$data = $result->fetch_assoc();

/*
|--------------------------------------------------------------------------
| Mengambil Detail Alat
|--------------------------------------------------------------------------
*/

$sqlDetail = "

SELECT

    dp.id_detail,
    dp.jumlah,

    a.id_alat,
    a.nama_alat,

    k.nama_kategori

FROM detail_peminjaman dp

INNER JOIN alat a
ON dp.id_alat = a.id_alat

INNER JOIN kategori k
ON a.id_kategori = k.id_kategori

WHERE

dp.id_peminjaman = ?

ORDER BY

a.nama_alat ASC

";

$stmtDetail = $conn->prepare($sqlDetail);

$stmtDetail->bind_param('i', $id);

$stmtDetail->execute();

$resultDetail = $stmtDetail->get_result();

/*
|--------------------------------------------------------------------------
| Ringkasan Transaksi
|--------------------------------------------------------------------------
*/

$sqlRingkasan = "

SELECT

COUNT(*) AS total_jenis,

COALESCE(SUM(jumlah),0) AS total_item

FROM detail_peminjaman

WHERE id_peminjaman = ?

";

$stmtRingkasan = $conn->prepare($sqlRingkasan);

$stmtRingkasan->bind_param('i', $id);

$stmtRingkasan->execute();

$resultRingkasan = $stmtRingkasan->get_result();

$ringkasan = $resultRingkasan->fetch_assoc();

$totalJenis = $ringkasan['total_jenis'];

$totalItem = $ringkasan['total_item'];

?>
<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= htmlspecialchars($title) ?></title>

    <?php include '../../layouts/header.php'; ?>

</head>

<body>

    <?php include '../../layouts/navbar.php'; ?>

    <?php include '../../layouts/sidebar.php'; ?>

    <div class="content-wrapper">

        <!-- Header -->

        <section class="content-header">

            <div class="container-fluid">

                <div class="row mb-2">

                    <div class="col-sm-6">

                        <h1>

                            <i class="bi bi-file-earmark-text text-primary"></i>

                            Detail Peminjaman

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

                                <li class="breadcrumb-item">

                                    <a href="index.php">

                                        Data Peminjaman

                                    </a>

                                </li>

                                <li class="breadcrumb-item active">

                                    Detail

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

                <div class="row">

                    <!-- Informasi Peminjam -->

                    <div class="col-lg-6">

                        <div class="card shadow-sm">

                            <div class="card-header bg-primary text-white">

                                <h5 class="mb-0">

                                    <i class="bi bi-person-circle"></i>

                                    Informasi Peminjam

                                </h5>

                            </div>

                            <div class="card-body">

                                <table class="table table-borderless">

                                    <tr>

                                        <th width="35%">

                                            Nama Lengkap

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

                                            Username

                                        </th>

                                        <td>

                                            :

                                        </td>

                                        <td>

                                            <?= htmlspecialchars($data['username']) ?>

                                        </td>

                                    </tr>

                                    <tr>

                                        <th>

                                            Level

                                        </th>

                                        <td>

                                            :

                                        </td>

                                        <td>

                                            <?= htmlspecialchars($data['level']) ?>

                                        </td>

                                    </tr>

                                    <tr>

                                        <th>

                                            Status Akun

                                        </th>

                                        <td>

                                            :

                                        </td>

                                        <td>

                                            <?php if ($data['status_user'] == 'Aktif') : ?>

                                            <span class="badge bg-success">

                                                Aktif

                                            </span>

                                            <?php else : ?>

                                            <span class="badge bg-danger">

                                                Tidak Aktif

                                            </span>

                                            <?php endif; ?>

                                        </td>

                                    </tr>

                                </table>

                            </div>

                        </div>

                    </div>

                    <!-- Informasi Peminjaman -->

                    <div class="col-lg-6">

                        <div class="card shadow-sm">

                            <div class="card-header bg-success text-white">

                                <h5 class="mb-0">

                                    <i class="bi bi-box-seam"></i>

                                    Informasi Peminjaman

                                </h5>

                            </div>

                            <div class="card-body">

                                <table class="table table-borderless">

                                    <tr>

                                        <th width="40%">

                                            ID Peminjaman

                                        </th>

                                        <td>

                                            :

                                        </td>

                                        <td>

                                            <?= $data['id_peminjaman'] ?>

                                        </td>

                                    </tr>

                                    <tr>

                                        <th>

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

                                            Tanggal Kembali

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

                                            Status

                                        </th>

                                        <td>

                                            :

                                        </td>

                                        <td>

                                            <?php
                                            
                                            switch ($data['status']) {
                                                case 'Menunggu':
                                                    echo '<span class="badge bg-warning text-dark">Menunggu</span>';
                                                    break;
                                            
                                                case 'Disetujui':
                                                    echo '<span class="badge bg-primary">Disetujui</span>';
                                                    break;
                                            
                                                case 'Dipinjam':
                                                    echo '<span class="badge bg-info">Dipinjam</span>';
                                                    break;
                                            
                                                case 'Selesai':
                                                    echo '<span class="badge bg-success">Selesai</span>';
                                                    break;
                                            
                                                default:
                                                    echo '<span class="badge bg-danger">Ditolak</span>';
                                            }
                                            
                                            ?>

                                        </td>

                                    </tr>

                                    <tr>

                                        <th>

                                            Dibuat Pada

                                        </th>

                                        <td>

                                            :

                                        </td>

                                        <td>

                                            <?= date('d-m-Y H:i', strtotime($data['created_at'])) ?>

                                        </td>

                                    </tr>

                                </table>

                            </div>

                        </div>

                    </div>

                </div>

                <div class="row">

                    <div class="col-12">
                        <div class="card shadow-sm">

                            <div class="card-header bg-info text-white">

                                <h5 class="mb-0">

                                    <i class="bi bi-list-check"></i>

                                    Daftar Alat yang Dipinjam

                                </h5>

                            </div>

                            <div class="card-body">

                                <div class="table-responsive">

                                    <table class="table table-bordered table-hover align-middle">

                                        <thead class="table-light">

                                            <tr class="text-center">

                                                <th width="8%">No</th>

                                                <th>Nama Alat</th>

                                                <th width="30%">Kategori</th>

                                                <th width="15%">Jumlah</th>

                                            </tr>

                                        </thead>

                                        <tbody>

                                            <?php

                                    if ($resultDetail->num_rows > 0) :

                                        $no = 1;

                                        while ($detail = $resultDetail->fetch_assoc()) :

                                    ?>

                                            <tr>

                                                <td class="text-center">

                                                    <?= $no++ ?>

                                                </td>

                                                <td>

                                                    <?= htmlspecialchars($detail['nama_alat']) ?>

                                                </td>

                                                <td>

                                                    <?= htmlspecialchars($detail['nama_kategori']) ?>

                                                </td>

                                                <td class="text-center">

                                                    <?= number_format($detail['jumlah']) ?>

                                                </td>

                                            </tr>

                                            <?php

                                        endwhile;

                                    else :

                                    ?>

                                            <tr>

                                                <td colspan="4" class="text-center py-4">

                                                    <i class="bi bi-inbox fs-2 text-secondary"></i>

                                                    <br><br>

                                                    Tidak ada data alat.

                                                </td>

                                            </tr>

                                            <?php endif; ?>

                                        </tbody>

                                    </table>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

                <!-- Ringkasan -->

                <div class="row mt-3">

                    <div class="col-md-6">

                        <div class="card border-primary shadow-sm">

                            <div class="card-body">

                                <h6 class="text-primary">

                                    Total Jenis Alat

                                </h6>

                                <h2>

                                    <?= number_format($totalJenis) ?>

                                </h2>

                            </div>

                        </div>

                    </div>

                    <div class="col-md-6">

                        <div class="card border-success shadow-sm">

                            <div class="card-body">

                                <h6 class="text-success">

                                    Total Item Dipinjam

                                </h6>

                                <h2>

                                    <?= number_format($totalItem) ?>

                                </h2>

                            </div>

                        </div>

                    </div>

                </div>

                <!-- Tombol -->

                <div class="row mt-3">

                    <div class="col-12">

                        <a href="index.php" class="btn btn-secondary">

                            <i class="bi bi-arrow-left"></i>

                            Kembali

                        </a>

                        <?php if ($data['status'] == 'Menunggu') : ?>

                        <a href="setujui.php?id=<?= $data['id_peminjaman'] ?>" class="btn btn-success"
                            onclick="return confirm('Setujui peminjaman ini?')">

                            <i class="bi bi-check-circle"></i>

                            Setujui

                        </a>

                        <a href="tolak.php?id=<?= $data['id_peminjaman'] ?>" class="btn btn-danger"
                            onclick="return confirm('Tolak peminjaman ini?')">

                            <i class="bi bi-x-circle"></i>

                            Tolak

                        </a>

                        <?php elseif ($data['status'] == 'Disetujui') : ?>

                        <a href="serahkan.php?id=<?= $data['id_peminjaman'] ?>" class="btn btn-primary"
                            onclick="return confirm('Serahkan alat kepada peminjam?')">

                            <i class="bi bi-box-arrow-right"></i>

                            Serahkan

                        </a>

                        <?php endif; ?>

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

if (isset($resultDetail) && $resultDetail instanceof mysqli_result) {
    $resultDetail->free();
}

if (isset($resultRingkasan) && $resultRingkasan instanceof mysqli_result) {
    $resultRingkasan->free();
}

/*
|--------------------------------------------------------------------------
| Menutup Prepared Statement
|--------------------------------------------------------------------------
*/

if (isset($stmt)) {
    $stmt->close();
}

if (isset($stmtDetail)) {
    $stmtDetail->close();
}

if (isset($stmtRingkasan)) {
    $stmtRingkasan->close();
}

/*
|--------------------------------------------------------------------------
| Menutup Koneksi Database
|--------------------------------------------------------------------------
*/

$conn->close();

?>

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

$title = 'Detail Peminjaman';

/*
|--------------------------------------------------------------------------
| ID User Login
|--------------------------------------------------------------------------
*/

$idUser = (int) $_SESSION['id_user'];

/*
|--------------------------------------------------------------------------
| Mengambil ID Peminjaman
|--------------------------------------------------------------------------
*/

$idPeminjaman = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($idPeminjaman <= 0) {

    $_SESSION['error'] = 'Data peminjaman tidak valid.';

    header('Location: index.php');
    exit();
}

/*
|--------------------------------------------------------------------------
| Query Detail Peminjaman
|--------------------------------------------------------------------------
*/

$sql = "

SELECT

    p.id_peminjaman,
    p.tanggal_pinjam,
    p.tanggal_kembali,
    p.status,
    p.created_at,

    d.id_detail,
    d.jumlah,

    a.id_alat,
    a.nama_alat,

    k.nama_kategori,

    u.nama_lengkap

FROM peminjaman p

INNER JOIN detail_peminjaman d

    ON p.id_peminjaman = d.id_peminjaman

INNER JOIN alat a

    ON d.id_alat = a.id_alat

INNER JOIN kategori k

    ON a.id_kategori = k.id_kategori

INNER JOIN users u

    ON p.id_user = u.id_user

WHERE

    p.id_peminjaman = ?

AND

    p.id_user = ?

LIMIT 1

";

$stmt = $conn->prepare($sql);

$stmt->bind_param(

    "ii",

    $idPeminjaman,
    $idUser

);

$stmt->execute();

$result = $stmt->get_result();

/*
|--------------------------------------------------------------------------
| Validasi Data
|--------------------------------------------------------------------------
*/

if ($result->num_rows == 0) {

    $_SESSION['error'] = 'Data peminjaman tidak ditemukan.';

    $stmt->close();

    $result->free();

    $conn->close();

    header('Location: index.php');

    exit();
}

/*
|--------------------------------------------------------------------------
| Mengambil Data
|--------------------------------------------------------------------------
*/

$data = $result->fetch_assoc();

?>
<!DOCTYPE html>
<html lang="id">

<head>

    <?php include '../../layouts/header.php'; ?>

</head>

<body class="hold-transition sidebar-mini layout-fixed">

    <div class="wrapper">

        <?php include '../../layouts/navbar.php'; ?>

        <?php include '../../layouts/sidebar.php'; ?>

        <div class="content-wrapper">

            <!-- Header Halaman -->

            <section class="content-header">

                <div class="container-fluid">

                    <div class="row mb-2">

                        <div class="col-sm-6">

                            <h1><?= $title; ?></h1>

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

                                        Peminjaman Saya

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

                    <div class="card card-primary card-outline">

                        <div class="card-header">

                            <h3 class="card-title">

                                Informasi Detail Peminjaman

                            </h3>

                        </div>

                        <div class="card-body">

                            <div class="row">

                                <div class="col-md-6">

                                    <table class="table table-bordered">

                                        <tr>

                                            <th width="180">

                                                Nama Peminjam

                                            </th>

                                            <td>

                                                <?= htmlspecialchars($data['nama_lengkap']); ?>

                                            </td>

                                        </tr>

                                        <tr>

                                            <th>

                                                Nama Alat

                                            </th>

                                            <td>

                                                <?= htmlspecialchars($data['nama_alat']); ?>

                                            </td>

                                        </tr>

                                        <tr>

                                            <th>

                                                Kategori

                                            </th>

                                            <td>

                                                <?= htmlspecialchars($data['nama_kategori']); ?>

                                            </td>

                                        </tr>

                                        <tr>

                                            <th>

                                                Jumlah Dipinjam

                                            </th>

                                            <td>

                                                <?= $data['jumlah']; ?> Unit

                                            </td>

                                        </tr>

                                    </table>

                                </div>

                                <div class="col-md-6">

                                    <table class="table table-bordered">

                                        <tr>

                                            <th width="180">

                                                Tanggal Pinjam

                                            </th>

                                            <td>

                                                <?= date('d-m-Y', strtotime($data['tanggal_pinjam'])); ?>

                                            </td>

                                        </tr>

                                        <tr>

                                            <th>

                                                Tanggal Kembali

                                            </th>

                                            <td>

                                                <?= date('d-m-Y', strtotime($data['tanggal_kembali'])); ?>

                                            </td>

                                        </tr>

                                        <tr>

                                            <th>

                                                Status

                                            </th>

                                            <td>
                                                <?php

                                                switch ($data['status']) {

                                                    case 'Menunggu':
                                                        $badge = 'warning';
                                                        break;

                                                    case 'Disetujui':
                                                        $badge = 'success';
                                                        break;

                                                    case 'Ditolak':
                                                        $badge = 'danger';
                                                        break;

                                                    case 'Dipinjam':
                                                        $badge = 'primary';
                                                        break;

                                                    case 'Selesai':
                                                        $badge = 'secondary';
                                                        break;

                                                    default:
                                                        $badge = 'dark';
                                                        break;
                                                }

                                                ?>

                                                <span class="badge bg-<?= $badge; ?>">

                                                    <?= htmlspecialchars($data['status']); ?>

                                                </span>

                                            </td>

                                        </tr>

                                        <tr>

                                            <th>

                                                Tanggal Pengajuan

                                            </th>

                                            <td>

                                                <?= date('d-m-Y H:i', strtotime($data['created_at'])); ?>

                                            </td>

                                        </tr>

                                    </table>

                                </div>

                            </div>

                        </div>

                        <div class="card-footer">

                            <a
                                href="index.php"
                                class="btn btn-secondary">

                                <i class="fas fa-arrow-left"></i>

                                Kembali

                            </a>

                            <?php if ($data['status'] == 'Disetujui') : ?>

                                <a
                                    href="cetak.php?id=<?= $data['id_peminjaman']; ?>"
                                    target="_blank"
                                    class="btn btn-success float-end">

                                    <i class="fas fa-print"></i>

                                    Cetak Bukti

                                </a>

                            <?php endif; ?>

                        </div>

                    </div>

                </div>

            </section>
            </section>

        </div>

        <?php include '../../layouts/footer.php'; ?>

    </div>

</body>

</html>

<?php

/*
|--------------------------------------------------------------------------
| Membebaskan Resource Query
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
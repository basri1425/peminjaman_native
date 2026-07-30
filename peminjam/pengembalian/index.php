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

$title = 'Pengembalian Alat';

/*
|--------------------------------------------------------------------------
| Data User Login
|--------------------------------------------------------------------------
*/

$idUser = (int) $_SESSION['id_user'];

/*
|--------------------------------------------------------------------------
| Keyword Pencarian
|--------------------------------------------------------------------------
*/

$keyword = trim($_GET['keyword'] ?? '');

/*
|--------------------------------------------------------------------------
| Query Data Pengembalian
|--------------------------------------------------------------------------
*/

$sql = "

SELECT

    p.id_peminjaman,
    p.tanggal_pinjam,
    p.tanggal_kembali,
    p.status,

    d.id_detail,
    d.jumlah,

    a.id_alat,
    a.nama_alat

FROM peminjaman p

INNER JOIN detail_peminjaman d

    ON p.id_peminjaman = d.id_peminjaman

INNER JOIN alat a

    ON d.id_alat = a.id_alat

WHERE

    p.id_user = ?

AND

    p.status = 'Dipinjam'

";

$parameter = [];
$tipe = '';

$parameter[] = $idUser;
$tipe .= 'i';

/*
|--------------------------------------------------------------------------
| Filter Pencarian
|--------------------------------------------------------------------------
*/

if ($keyword != '') {

    $sql .= "

    AND

        a.nama_alat LIKE ?

    ";

    $parameter[] = '%' . $keyword . '%';
    $tipe .= 's';
}

/*
|--------------------------------------------------------------------------
| Sorting
|--------------------------------------------------------------------------
*/

$sql .= "

ORDER BY

    p.tanggal_pinjam DESC

";

/*
|--------------------------------------------------------------------------
| Prepared Statement
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare($sql);

$stmt->bind_param(

    $tipe,

    ...$parameter

);

$stmt->execute();

$result = $stmt->get_result();

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

                                <li class="breadcrumb-item active">

                                    Pengembalian Alat

                                </li>

                            </ol>

                        </div>

                    </div>

                </div>

            </section>

            <!-- Content -->

            <section class="content">

                <div class="container-fluid">

                    <?php if (isset($_SESSION['success'])) : ?>

                        <div class="alert alert-success alert-dismissible fade show">

                            <?= $_SESSION['success']; ?>

                            <button
                                type="button"
                                class="btn-close"
                                data-bs-dismiss="alert">
                            </button>

                        </div>

                        <?php unset($_SESSION['success']); ?>

                    <?php endif; ?>

                    <?php if (isset($_SESSION['error'])) : ?>

                        <div class="alert alert-danger alert-dismissible fade show">

                            <?= $_SESSION['error']; ?>

                            <button
                                type="button"
                                class="btn-close"
                                data-bs-dismiss="alert">
                            </button>

                        </div>

                        <?php unset($_SESSION['error']); ?>

                    <?php endif; ?>

                    <div class="card card-primary card-outline">

                        <div class="card-header">

                            <h3 class="card-title">

                                Daftar Alat yang Sedang Dipinjam

                            </h3>

                        </div>

                        <div class="card-body">

                            <!-- Form Pencarian -->

                            <form method="GET" class="mb-3">

                                <div class="row">

                                    <div class="col-md-6">

                                        <div class="input-group">

                                            <input
                                                type="text"
                                                name="keyword"
                                                class="form-control"
                                                placeholder="Cari nama alat..."
                                                value="<?= htmlspecialchars($keyword); ?>">

                                            <button
                                                type="submit"
                                                class="btn btn-primary">

                                                <i class="fas fa-search"></i>

                                                Cari

                                            </button>

                                            <a
                                                href="index.php"
                                                class="btn btn-secondary">

                                                Reset

                                            </a>

                                        </div>

                                    </div>

                                </div>

                            </form>

                            <div class="table-responsive">

                                <table class="table table-bordered table-hover">

                                    <thead class="table-light">

                                        <tr>

                                            <th width="60">No</th>

                                            <th>Nama Alat</th>

                                            <th width="90">Jumlah</th>

                                            <th width="130">Tanggal Pinjam</th>

                                            <th width="130">Batas Kembali</th>

                                            <th width="110">Status</th>

                                            <th width="200">Aksi</th>

                                        </tr>

                                    </thead>

                                    <tbody>

                                        <?php

                                        $no = 1;

                                        while ($row = $result->fetch_assoc()) :

                                        ?>
                                            <?php

                                            switch ($row['status']) {

                                                case 'Dipinjam':
                                                    $badge = 'primary';
                                                    break;

                                                case 'Menunggu':
                                                    $badge = 'warning';
                                                    break;

                                                case 'Disetujui':
                                                    $badge = 'success';
                                                    break;

                                                case 'Ditolak':
                                                    $badge = 'danger';
                                                    break;

                                                case 'Selesai':
                                                    $badge = 'secondary';
                                                    break;

                                                default:
                                                    $badge = 'dark';
                                                    break;
                                            }

                                            ?>

                                            <tr>

                                                <td class="text-center">

                                                    <?= $no++; ?>

                                                </td>

                                                <td>

                                                    <?= htmlspecialchars($row['nama_alat']); ?>

                                                </td>

                                                <td class="text-center">

                                                    <?= $row['jumlah']; ?>

                                                </td>

                                                <td>

                                                    <?= date('d-m-Y', strtotime($row['tanggal_pinjam'])); ?>

                                                </td>

                                                <td>

                                                    <?= date('d-m-Y', strtotime($row['tanggal_kembali'])); ?>

                                                </td>

                                                <td class="text-center">

                                                    <span class="badge bg-<?= $badge; ?>">

                                                        <?= htmlspecialchars($row['status']); ?>

                                                    </span>

                                                </td>

                                                <td class="text-center">

                                                    <a
                                                        href="detail.php?id=<?= $row['id_peminjaman']; ?>"
                                                        class="btn btn-info btn-sm">

                                                        <i class="fas fa-eye"></i>

                                                        Detail

                                                    </a>

                                                    <a
                                                        href="ajukan.php?id=<?= $row['id_peminjaman']; ?>"
                                                        class="btn btn-success btn-sm"
                                                        onclick="return confirm('Ajukan pengembalian alat ini?')">

                                                        <i class="fas fa-undo"></i>

                                                        Ajukan Pengembalian

                                                    </a>

                                                </td>

                                            </tr>

                                        <?php endwhile; ?>

                                        <?php if ($no == 1) : ?>

                                            <tr>

                                                <td
                                                    colspan="7"
                                                    class="text-center text-muted">

                                                    Belum ada alat yang dapat diajukan pengembaliannya.

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
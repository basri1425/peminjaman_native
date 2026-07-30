<?php

require '../../config/session.php';
require '../../config/database.php';

/*
|--------------------------------------------------------------------------
| Validasi Login
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION['id_user'])) {
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
| Mengambil Data Peminjaman
|--------------------------------------------------------------------------
*/

$sql = "

    SELECT

        p.id_peminjaman,
        p.tanggal_pinjam,
        p.tanggal_kembali,
        p.status,
        u.nama_lengkap

    FROM peminjaman p

    INNER JOIN users u

        ON p.id_user = u.id_user

    WHERE

        p.status = 'Dipinjam'

    ORDER BY

        p.tanggal_pinjam DESC

";

$result = $conn->query($sql);

$no = 1;

$pageTitle = 'Data Pengembalian Alat';

include '../../layouts/header.php';
include '../../layouts/navbar.php';
include '../../layouts/sidebar.php';

?>

<div class="content-wrapper">

    <section class="content-header">

        <div class="container-fluid">

            <div class="row mb-2">

                <div class="col-sm-6">

                    <h1>Data Pengembalian Alat</h1>

                </div>

                <div class="col-sm-6">

                    <ol class="breadcrumb float-sm-right">

                        <li class="breadcrumb-item">

                            <a href="../dashboard/index.php">

                                Dashboard

                            </a>

                        </li>

                        <li class="breadcrumb-item active">

                            Pengembalian

                        </li>

                    </ol>

                </div>

            </div>

        </div>

    </section>

    <section class="content">

        <div class="container-fluid">
            <div class="row">

                <div class="col-lg-12">

                    <div class="card card-primary card-outline">

                        <div class="card-header">

                            <h3 class="card-title">

                                Daftar Pengembalian Alat

                            </h3>

                        </div>

                        <div class="card-body">

                            <?php if (isset($_GET['pesan'])) : ?>

                            <?php if ($_GET['pesan'] == "berhasil") : ?>

                            <div class="alert alert-success alert-dismissible fade show">

                                <i class="fas fa-check-circle"></i>

                                Data pengembalian berhasil disimpan.

                                <button type="button" class="btn-close" data-bs-dismiss="alert">
                                </button>

                            </div>

                            <?php elseif ($_GET['pesan'] == "gagal") : ?>

                            <div class="alert alert-danger alert-dismissible fade show">

                                <i class="fas fa-times-circle"></i>

                                Data pengembalian gagal disimpan.

                                <button type="button" class="btn-close" data-bs-dismiss="alert">
                                </button>

                            </div>

                            <?php endif; ?>

                            <?php endif; ?>

                            <div class="table-responsive">

                                <table class="table table-bordered table-hover table-striped align-middle">

                                    <thead class="table-dark text-center">

                                        <tr>

                                            <th width="60">

                                                No

                                            </th>

                                            <th>

                                                Nama Peminjam

                                            </th>

                                            <th width="140">

                                                Tanggal Pinjam

                                            </th>

                                            <th width="140">

                                                Tanggal Kembali

                                            </th>

                                            <th width="120">

                                                Status

                                            </th>

                                            <th width="120">

                                                Aksi

                                            </th>

                                        </tr>

                                    </thead>

                                    <tbody>
                                        <?php if ($result && $result->num_rows > 0) : ?>

                                        <?php while ($row = $result->fetch_assoc()) : ?>

                                        <tr>

                                            <td class="text-center">

                                                <?= $no++ ?>

                                            </td>

                                            <td>

                                                <?= htmlspecialchars($row['nama_lengkap']) ?>

                                            </td>

                                            <td class="text-center">

                                                <?= date('d-m-Y', strtotime($row['tanggal_pinjam'])) ?>

                                            </td>

                                            <td class="text-center">

                                                <?= date('d-m-Y', strtotime($row['tanggal_kembali'])) ?>

                                            </td>

                                            <td class="text-center">

                                                <span class="badge bg-primary">

                                                    <?= htmlspecialchars($row['status']) ?>

                                                </span>

                                            </td>

                                            <td class="text-center">

                                                <a href="detail.php?id=<?= $row['id_peminjaman'] ?>"
                                                    class="btn btn-success btn-sm">

                                                    <i class="fas fa-undo"></i>

                                                    Detail Pengembalian

                                                </a>

                                            </td>

                                        </tr>

                                        <?php endwhile; ?>

                                        <?php else : ?>

                                        <tr>

                                            <td colspan="6" class="text-center text-muted">

                                                <i class="fas fa-info-circle"></i>

                                                Belum ada transaksi yang sedang dipinjam.

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

        </div>

    </section>

</div>

<?php

include '../../layouts/footer.php';

/*
|--------------------------------------------------------------------------
| Membebaskan Hasil Query
|--------------------------------------------------------------------------
*/

if ($result instanceof mysqli_result) {
    $result->free();
}

/*
|--------------------------------------------------------------------------
| Menutup Koneksi Database
|--------------------------------------------------------------------------
*/

$conn->close();

?>

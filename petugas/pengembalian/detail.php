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
| Validasi ID
|--------------------------------------------------------------------------
*/

if (!isset($_GET['id'])) {
    header('Location: index.php');

    exit();
}

$id = (int) $_GET['id'];

if ($id <= 0) {
    header('Location: index.php');

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
        u.id_user,
        u.nama_lengkap

    FROM peminjaman p

    INNER JOIN users u

        ON p.id_user = u.id_user

    WHERE

        p.id_peminjaman = ?

    LIMIT 1

";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    $conn->close();

    header('Location: index.php');

    exit();
}

$stmt->bind_param('i', $id);

$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows == 0) {
    $stmt->close();

    $result->free();

    $conn->close();

    header('Location: index.php');

    exit();
}

$data = $result->fetch_assoc();

/*
|--------------------------------------------------------------------------
| Validasi Status
|--------------------------------------------------------------------------
*/

if ($data['status'] != 'Dipinjam') {
    $stmt->close();

    $result->free();

    $conn->close();

    header('Location: index.php');

    exit();
}

/*
|--------------------------------------------------------------------------
| Mengambil Detail Alat
|--------------------------------------------------------------------------
*/

$sqlDetail = "

    SELECT

        dp.id_alat,
        dp.jumlah,
        a.nama_alat

    FROM detail_peminjaman dp

    INNER JOIN alat a

        ON dp.id_alat = a.id_alat

    WHERE

        dp.id_peminjaman = ?

    ORDER BY

        a.nama_alat ASC

";

$stmtDetail = $conn->prepare($sqlDetail);

if (!$stmtDetail) {
    $stmt->close();

    $result->free();

    $conn->close();

    header('Location: index.php');

    exit();
}

$stmtDetail->bind_param('i', $id);

$stmtDetail->execute();

$resultDetail = $stmtDetail->get_result();

$pageTitle = 'Detail Pengembalian';

include '../../layouts/header.php';
include '../../layouts/navbar.php';
include '../../layouts/sidebar.php';

?>

<div class="content-wrapper">

    <section class="content-header">

        <div class="container-fluid">

            <div class="row mb-2">

                <div class="col-sm-6">

                    <h1>Detail Pengembalian Alat</h1>

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

    <section class="content">

        <div class="container-fluid">
            <div class="row">

                <div class="col-lg-12">

                    <div class="card card-primary card-outline">

                        <div class="card-header">

                            <h3 class="card-title">

                                Form Pengembalian Alat

                            </h3>

                        </div>

                        <form action="proses.php" method="POST">

                            <div class="card-body">

                                <input type="hidden" name="id_peminjaman" value="<?= $data['id_peminjaman'] ?>">

                                <div class="row">

                                    <div class="col-md-6">

                                        <div class="mb-3">

                                            <label class="form-label">

                                                Nama Peminjam

                                            </label>

                                            <input type="text" class="form-control"
                                                value="<?= htmlspecialchars($data['nama_lengkap']) ?>" readonly>

                                        </div>

                                    </div>

                                    <div class="col-md-3">

                                        <div class="mb-3">

                                            <label class="form-label">

                                                Tanggal Pinjam

                                            </label>

                                            <input type="text" class="form-control"
                                                value="<?= date('d-m-Y', strtotime($data['tanggal_pinjam'])) ?>"
                                                readonly>

                                        </div>

                                    </div>

                                    <div class="col-md-3">

                                        <div class="mb-3">

                                            <label class="form-label">

                                                Tanggal Kembali

                                            </label>

                                            <input type="text" class="form-control"
                                                value="<?= date('d-m-Y', strtotime($data['tanggal_kembali'])) ?>"
                                                readonly>

                                        </div>

                                    </div>

                                </div>

                                <div class="row">

                                    <div class="col-md-4">

                                        <div class="mb-3">

                                            <label class="form-label">

                                                Status

                                            </label>

                                            <input type="text" class="form-control"
                                                value="<?= htmlspecialchars($data['status']) ?>" readonly>

                                        </div>

                                    </div>

                                    <div class="col-md-4">

                                        <div class="mb-3">

                                            <label class="form-label">

                                                Tanggal Pengembalian

                                            </label>

                                            <input type="date" name="tanggal_pengembalian" class="form-control"
                                                value="<?= date('Y-m-d') ?>" required>

                                        </div>

                                    </div>

                                    <div class="col-md-4">

                                        <div class="mb-3">

                                            <label class="form-label">

                                                Keterangan Umum

                                            </label>

                                            <input type="text" name="keterangan" class="form-control" maxlength="255"
                                                placeholder="Opsional">

                                        </div>

                                    </div>

                                </div>

                                <hr>

                                <h5 class="mb-3">

                                    Daftar Alat yang Dikembalikan

                                </h5>

                                <div class="table-responsive">

                                    <table class="table table-bordered table-striped align-middle">

                                        <thead class="table-dark text-center">

                                            <tr>

                                                <th width="60">

                                                    No

                                                </th>

                                                <th>

                                                    Nama Alat

                                                </th>

                                                <th width="100">

                                                    Jumlah

                                                </th>

                                                <th width="220">

                                                    Kondisi

                                                </th>

                                                <th>

                                                    Keterangan

                                                </th>

                                            </tr>

                                        </thead>

                                        <tbody>

                                            <?php
                                            
                                            $no = 1;
                                            
                                            ?>
                                            <?php if ($resultDetail && $resultDetail->num_rows > 0) : ?>

                                            <?php while ($alat = $resultDetail->fetch_assoc()) : ?>

                                            <tr>

                                                <td class="text-center">

                                                    <?= $no++ ?>

                                                </td>

                                                <td>

                                                    <?= htmlspecialchars($alat['nama_alat']) ?>

                                                    <input type="hidden" name="id_alat[]"
                                                        value="<?= $alat['id_alat'] ?>">

                                                    <input type="hidden" name="jumlah[]"
                                                        value="<?= $alat['jumlah'] ?>">

                                                </td>

                                                <td class="text-center">

                                                    <?= $alat['jumlah'] ?>

                                                </td>

                                                <td>

                                                    <select name="kondisi[]" class="form-select" required>

                                                        <option value="Baik">

                                                            Baik

                                                        </option>

                                                        <option value="Rusak Ringan">

                                                            Rusak Ringan

                                                        </option>

                                                        <option value="Rusak Berat">

                                                            Rusak Berat

                                                        </option>

                                                        <option value="Hilang">

                                                            Hilang

                                                        </option>

                                                    </select>

                                                </td>

                                                <td>

                                                    <input type="text" name="keterangan_alat[]" class="form-control"
                                                        maxlength="255"
                                                        placeholder="Keterangan kondisi alat (opsional)">

                                                </td>

                                            </tr>

                                            <?php endwhile; ?>

                                            <?php else : ?>

                                            <tr>

                                                <td colspan="5" class="text-center text-muted">

                                                    <i class="fas fa-info-circle"></i>

                                                    Tidak ada data alat pada transaksi ini.

                                                </td>

                                            </tr>

                                            <?php endif; ?>

                                        </tbody>

                                    </table>

                                </div>

                            </div>

                            <div class="card-footer">

                                <button type="submit" class="btn btn-primary">

                                    <i class="fas fa-save"></i>

                                    Simpan Pengembalian

                                </button>

                                <a href="index.php" class="btn btn-secondary">

                                    <i class="fas fa-arrow-left"></i>

                                    Kembali

                                </a>

                            </div>

                        </form>
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
| Membebaskan Result
|--------------------------------------------------------------------------
*/

if (isset($result) && $result instanceof mysqli_result) {
    $result->free();
}

if (isset($resultDetail) && $resultDetail instanceof mysqli_result) {
    $resultDetail->free();
}

/*
|--------------------------------------------------------------------------
| Menutup Statement
|--------------------------------------------------------------------------
*/

if (isset($stmt)) {
    $stmt->close();
}

if (isset($stmtDetail)) {
    $stmtDetail->close();
}

/*
|--------------------------------------------------------------------------
| Menutup Koneksi Database
|--------------------------------------------------------------------------
*/

$conn->close();

?>

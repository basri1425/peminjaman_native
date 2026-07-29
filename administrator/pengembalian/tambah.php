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
| Mengambil Daftar Transaksi Yang Bisa Dikembalikan
|--------------------------------------------------------------------------
|
| Syarat:
| - Status = Dipinjam
| - Belum pernah dibuat pengembalian
|
*/

$sql = "

SELECT

    p.id_peminjaman,

    p.id_user,

    p.tanggal_pinjam,

    p.tanggal_kembali,

    u.nama_lengkap

FROM peminjaman p

INNER JOIN users u
ON p.id_user = u.id_user

LEFT JOIN pengembalian pg
ON p.id_peminjaman = pg.id_peminjaman

WHERE

p.status = 'Dipinjam'

AND

pg.id_pengembalian IS NULL

ORDER BY

p.id_peminjaman DESC

";

$resultPeminjaman = $conn->query($sql);

/*
|--------------------------------------------------------------------------
| Mengambil ID Peminjaman Yang Dipilih
|--------------------------------------------------------------------------
*/

$idPeminjaman = isset($_GET['id']) ? (int) $_GET['id'] : 0;

$dataPeminjaman = null;

$detailAlat = [];

/*
|--------------------------------------------------------------------------
| Jika Transaksi Dipilih
|--------------------------------------------------------------------------
*/

if ($idPeminjaman > 0) {
    /*
    |--------------------------------------------------------------------------
    | Data Header Peminjaman
    |--------------------------------------------------------------------------
    */

    $sql = "

    SELECT

        p.id_peminjaman,

        p.id_user,

        p.tanggal_pinjam,

        p.tanggal_kembali,

        p.status,

        u.nama_lengkap

    FROM peminjaman p

    INNER JOIN users u
    ON p.id_user = u.id_user

    WHERE

    p.id_peminjaman = ?

    ";

    $stmt = $conn->prepare($sql);

    $stmt->bind_param(
        'i',

        $idPeminjaman,
    );

    $stmt->execute();

    $dataPeminjaman = $stmt->get_result()->fetch_assoc();

    $stmt->close();

    /*
    |--------------------------------------------------------------------------
    | Detail Alat Yang Dipinjam
    |--------------------------------------------------------------------------
    */

    $sql = "

    SELECT

        dp.id_alat,

        dp.jumlah,

        a.nama_alat,

        a.kondisi

    FROM detail_peminjaman dp

    INNER JOIN alat a
    ON dp.id_alat = a.id_alat

    WHERE

    dp.id_peminjaman = ?

    ORDER BY

    a.nama_alat ASC

    ";

    $stmt = $conn->prepare($sql);

    $stmt->bind_param(
        'i',

        $idPeminjaman,
    );

    $stmt->execute();

    $resultDetail = $stmt->get_result();

    while ($row = $resultDetail->fetch_assoc()) {
        $detailAlat[] = $row;
    }

    $stmt->close();
}

?>
<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Tambah Pengembalian</title>

    <?php include '../../layouts/header.php'; ?>

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

                                <i class="fas fa-undo-alt text-success"></i>

                                Tambah Pengembalian Alat

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

                                    Tambah

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

                                <i class="fas fa-plus-circle"></i>

                                Form Pengembalian Alat

                            </h3>

                        </div>

                        <form action="simpan.php" method="POST">

                            <div class="card-body">

                                <!-- Pilih Transaksi -->

                                <div class="mb-3">

                                    <label class="form-label">

                                        Transaksi Peminjaman

                                    </label>

                                    <select name="id_peminjaman" class="form-select"
                                        onchange="location='tambah.php?id='+this.value" required>

                                        <option value="">

                                            -- Pilih Transaksi --

                                        </option>

                                        <?php while ($trx = $resultPeminjaman->fetch_assoc()) : ?>

                                        <option value="<?= $trx['id_peminjaman'] ?>"
                                            <?= $trx['id_peminjaman'] == $idPeminjaman ? 'selected' : '' ?>>

                                            <?= 'PJM-' .
                                            str_pad(
                                            $trx['id_peminjaman'],

                                            5,

                                            '0',

                                            STR_PAD_LEFT,
                                            ) .
                                            ' | ' .
                                            htmlspecialchars($trx['nama_lengkap']) ?>

                                        </option>

                                        <?php endwhile; ?>

                                    </select>

                                </div>

                                <?php if ($dataPeminjaman) : ?>

                                <div class="row">

                                    <div class="col-md-6">

                                        <div class="mb-3">

                                            <label class="form-label">

                                                Nama Peminjam

                                            </label>

                                            <input type="text" class="form-control"
                                                value="<?= htmlspecialchars($dataPeminjaman['nama_lengkap']) ?>"
                                                readonly>

                                        </div>

                                    </div>

                                    <div class="col-md-6">

                                        <div class="mb-3">

                                            <label class="form-label">

                                                Status

                                            </label>

                                            <input type="text" class="form-control"
                                                value="<?= htmlspecialchars($dataPeminjaman['status']) ?>" readonly>

                                        </div>

                                    </div>

                                </div>

                                <div class="row">

                                    <div class="col-md-4">

                                        <div class="mb-3">

                                            <label class="form-label">

                                                Tanggal Pinjam

                                            </label>

                                            <input type="text" class="form-control"
                                                value="<?= date('d-m-Y', strtotime($dataPeminjaman['tanggal_pinjam'])) ?>"
                                                readonly>

                                        </div>

                                    </div>

                                    <div class="col-md-4">

                                        <div class="mb-3">

                                            <label class="form-label">

                                                Rencana Kembali

                                            </label>

                                            <input type="text" class="form-control"
                                                value="<?= date('d-m-Y', strtotime($dataPeminjaman['tanggal_kembali'])) ?>"
                                                readonly>

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

                                </div>

                                <div class="mb-3">

                                    <label class="form-label">

                                        Keterangan Pengembalian

                                    </label>

                                    <textarea name="keterangan" rows="3" class="form-control" placeholder="Masukkan keterangan apabila diperlukan..."></textarea>

                                </div>

                                <hr>

                                <h5>

                                    <i class="fas fa-box"></i>

                                    Daftar Alat Yang Dikembalikan

                                </h5>

                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped align-middle">

                                        <thead class="table-success text-center">

                                            <tr>

                                                <th width="5%">No</th>

                                                <th>Nama Alat</th>

                                                <th width="12%">Jumlah</th>

                                                <th width="20%">Kondisi</th>

                                                <th>Keterangan</th>

                                            </tr>

                                        </thead>

                                        <tbody>

                                            <?php if (!empty($detailAlat)) : ?>

                                            <?php

                                            $no = 1;

                                            foreach ($detailAlat as $alat) :

                                            ?>

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

                                                        <option value="Baik"
                                                            <?= $alat['kondisi'] == 'Baik' ? 'selected' : '' ?>>

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

                                                    <input type="text" name="keterangan_detail[]"
                                                        class="form-control"
                                                        placeholder="Keterangan kondisi alat (opsional)">

                                                </td>

                                            </tr>

                                            <?php endforeach; ?>

                                            <?php else : ?>

                                            <tr>

                                                <td colspan="5" class="text-center">

                                                    <div class="alert alert-warning mb-0">

                                                        <i class="fas fa-info-circle"></i>

                                                        Silakan pilih transaksi peminjaman terlebih dahulu.

                                                    </div>

                                                </td>

                                            </tr>

                                            <?php endif; ?>

                                        </tbody>

                                    </table>

                                </div>

                                <hr>

                                <div class="d-flex justify-content-between">

                                    <a href="index.php" class="btn btn-secondary">

                                        <i class="fas fa-arrow-left"></i>

                                        Kembali

                                    </a>

                                    <?php if (!empty($detailAlat)) : ?>

                                    <button type="submit" class="btn btn-success"
                                        onclick="return confirm('Simpan data pengembalian alat?');">

                                        <i class="fas fa-save"></i>

                                        Simpan Pengembalian

                                    </button>

                                    <?php endif; ?>

                                </div>
                                <?php endif; ?>

                            </div>

                        </form>

                    </div>

                </div>

            </section>

        </div>

        <?php include '../../layouts/footer.php'; ?>


</body>

</html>


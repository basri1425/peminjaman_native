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
| Mengambil ID Pengembalian
|--------------------------------------------------------------------------
*/

$idPengembalian = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($idPengembalian <= 0) {
    header('Location: index.php');
    exit();
}

/*
|--------------------------------------------------------------------------
| Mengambil Data Header Pengembalian
|--------------------------------------------------------------------------
*/

$sql = "

SELECT

    pg.id_pengembalian,
    pg.id_peminjaman,
    pg.tanggal_pengembalian,
    pg.keterangan,

    p.tanggal_pinjam,
    p.tanggal_kembali,
    p.status,

    u.id_user,
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

$stmt->bind_param('i', $idPengembalian);

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

    exit();
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

$stmt->bind_param('i', $idPengembalian);

$stmt->execute();

$resultDetail = $stmt->get_result();

?>
<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Edit Pengembalian</title>

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

                                <i class="fas fa-edit text-warning"></i>

                                Edit Pengembalian

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

                                    Edit

                                </li>

                            </ol>

                        </div>

                    </div>

                </div>

            </section>

            <!-- Content -->

            <section class="content">

                <div class="container-fluid">

                    <form action="update.php" method="POST">

                        <input type="hidden" name="id_pengembalian" value="<?= $data['id_pengembalian'] ?>">

                        <input type="hidden" name="id_peminjaman" value="<?= $data['id_peminjaman'] ?>">

                        <div class="card card-warning">

                            <div class="card-header">

                                <h3 class="card-title">

                                    <i class="fas fa-edit"></i>

                                    Form Edit Pengembalian

                                </h3>

                            </div>

                            <div class="card-body">

                                <div class="row">

                                    <div class="col-md-6">

                                        <div class="form-group">

                                            <label>

                                                No. Pengembalian

                                            </label>

                                            <input type="text" class="form-control"
                                                value="<?= 'KMB-' . str_pad($data['id_pengembalian'], 5, '0', STR_PAD_LEFT) ?>"
                                                readonly>

                                        </div>

                                        <div class="form-group">

                                            <label>

                                                No. Peminjaman

                                            </label>

                                            <input type="text" class="form-control"
                                                value="<?= 'PJM-' . str_pad($data['id_peminjaman'], 5, '0', STR_PAD_LEFT) ?>"
                                                readonly>

                                        </div>

                                        <div class="form-group">

                                            <label>

                                                Nama Peminjam

                                            </label>

                                            <input type="text" class="form-control"
                                                value="<?= htmlspecialchars($data['nama_lengkap']) ?>" readonly>

                                        </div>

                                    </div>

                                    <div class="col-md-6">

                                        <div class="form-group">

                                            <label>

                                                Tanggal Pinjam

                                            </label>

                                            <input type="text" class="form-control"
                                                value="<?= date('d-m-Y', strtotime($data['tanggal_pinjam'])) ?>"
                                                readonly>

                                        </div>

                                        <div class="form-group">

                                            <label>

                                                Rencana Kembali

                                            </label>

                                            <input type="text" class="form-control"
                                                value="<?= date('d-m-Y', strtotime($data['tanggal_kembali'])) ?>"
                                                readonly>

                                        </div>

                                        <div class="form-group">

                                            <label>

                                                Tanggal Pengembalian

                                            </label>

                                            <input type="date" name="tanggal_pengembalian" class="form-control"
                                                value="<?= htmlspecialchars($data['tanggal_pengembalian']) ?>" required>

                                        </div>

                                    </div>

                                </div>

                                <div class="form-group">

                                    <label>

                                        Keterangan Pengembalian

                                    </label>

                                    <textarea name="keterangan" class="form-control" rows="4" placeholder="Masukkan keterangan pengembalian..."><?= htmlspecialchars($data['keterangan']) ?></textarea>

                                </div>

                                <hr>

                                <h5>

                                    <i class="fas fa-box"></i>

                                    Detail Alat Yang Dikembalikan

                                </h5>

                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover table-striped">

                                        <thead class="table-warning text-center">

                                            <tr>

                                                <th width="5%">No</th>

                                                <th>Nama Alat</th>

                                                <th width="10%">Jumlah</th>

                                                <th width="20%">Kondisi</th>

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

                                                    <input type="hidden" name="id_detail_pengembalian[]"
                                                        value="<?= $row['id_detail_pengembalian'] ?>">

                                                    <input type="hidden" name="id_alat[]"
                                                        value="<?= $row['id_alat'] ?>">

                                                </td>

                                                <td class="text-center">

                                                    <?= $row['jumlah'] ?>

                                                    <input type="hidden" name="jumlah[]" value="<?= $row['jumlah'] ?>">

                                                </td>

                                                <td>

                                                    <select name="kondisi[]" class="form-control" required>

                                                        <option value="Baik"
                                                            <?= $row['kondisi'] == 'Baik' ? 'selected' : '' ?>>

                                                            Baik

                                                        </option>

                                                        <option value="Rusak Ringan"
                                                            <?= $row['kondisi'] == 'Rusak Ringan' ? 'selected' : '' ?>>

                                                            Rusak Ringan

                                                        </option>

                                                        <option value="Rusak Berat"
                                                            <?= $row['kondisi'] == 'Rusak Berat' ? 'selected' : '' ?>>

                                                            Rusak Berat

                                                        </option>

                                                        <option value="Hilang"
                                                            <?= $row['kondisi'] == 'Hilang' ? 'selected' : '' ?>>

                                                            Hilang

                                                        </option>

                                                    </select>

                                                </td>

                                                <td>

                                                    <input type="text" name="keterangan_detail[]"
                                                        class="form-control" maxlength="255"
                                                        placeholder="Keterangan kondisi alat..."
                                                        value="<?= htmlspecialchars($row['keterangan']) ?>">

                                                </td>

                                            </tr>

                                            <?php endwhile; ?>

                                        </tbody>

                                    </table>

                                </div>

                                <hr>

                                <div class="d-flex justify-content-between">

                                    <a href="index.php" class="btn btn-secondary">

                                        <i class="fas fa-arrow-left"></i>

                                        Kembali

                                    </a>

                                    <button type="submit" class="btn btn-warning">

                                        <i class="fas fa-save"></i>

                                        Simpan Perubahan

                                    </button>

                                </div>

                    </form>

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

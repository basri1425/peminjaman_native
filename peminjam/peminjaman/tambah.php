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

$title = 'Ajukan Peminjaman';

/*
|--------------------------------------------------------------------------
| Validasi Parameter ID Alat
|--------------------------------------------------------------------------
*/

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: ../alat/index.php');
    exit();
}

$idAlat = (int) $_GET['id'];

/*
|--------------------------------------------------------------------------
| Mengambil Data Alat
|--------------------------------------------------------------------------
*/

$sql = "

    SELECT

        a.id_alat,
        a.nama_alat,
        a.stok,
        a.kondisi,
        a.lokasi,
        a.foto,

        k.nama_kategori

    FROM alat a

    INNER JOIN kategori k

        ON a.id_kategori = k.id_kategori

    WHERE

        a.id_alat = ?

    LIMIT 1

";

$stmt = $conn->prepare($sql);

$stmt->bind_param('i', $idAlat);

$stmt->execute();

$result = $stmt->get_result();

/*
|--------------------------------------------------------------------------
| Validasi Data Alat
|--------------------------------------------------------------------------
*/

if ($result->num_rows == 0) {
    $stmt->close();

    $conn->close();

    header('Location: ../alat/index.php');

    exit();
}

$alat = $result->fetch_assoc();

/*
|--------------------------------------------------------------------------
| Validasi Stok
|--------------------------------------------------------------------------
*/

if ($alat['stok'] <= 0) {
    $stmt->close();

    $result->free();

    $conn->close();

    $_SESSION['warning'] = 'Maaf, alat yang dipilih sedang tidak tersedia.';

    header('Location: ../alat/detail.php?id=' . $idAlat);

    exit();
}

/*
|--------------------------------------------------------------------------
| Data Peminjam
|--------------------------------------------------------------------------
*/

$idUser = $_SESSION['id_user'];

/*
|--------------------------------------------------------------------------
| Nilai Default Form
|--------------------------------------------------------------------------
*/

$tanggalPinjam = date('Y-m-d');

$tanggalKembali = date('Y-m-d', strtotime('+1 day'));

$jumlah = 1;

$keperluan = '';

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

        <!-- Header -->

        <section class="content-header">

            <div class="container-fluid">

                <div class="row mb-2">

                    <div class="col-sm-6">

                        <h1>

                            <i class="bi bi-box-arrow-in-right text-primary"></i>

                            Ajukan Peminjaman

                        </h1>

                    </div>

                    <div class="col-sm-6">

                        <ol class="breadcrumb float-sm-end">

                            <li class="breadcrumb-item">

                                Dashboard

                            </li>

                            <li class="breadcrumb-item">

                                Detail Alat

                            </li>

                            <li class="breadcrumb-item active">

                                Ajukan Peminjaman

                            </li>

                        </ol>

                    </div>

                </div>

            </div>

        </section>

        <!-- Content -->

        <section class="content">

            <div class="container-fluid">

                <form action="simpan.php" method="POST">

                    <input type="hidden" name="id_alat" value="<?= $alat['id_alat'] ?>">

                    <!-- Informasi Alat -->

                    <div class="card shadow-sm mb-4">

                        <div class="card-header bg-primary text-white">

                            <h5 class="mb-0">

                                <i class="bi bi-tools"></i>

                                Informasi Alat

                            </h5>

                        </div>

                        <div class="card-body">

                            <div class="row">

                                <!-- Foto -->

                                <div class="col-md-4 text-center mb-3">

                                    <?php if (!empty($alat['foto']) && file_exists("../../assets/img/alat/" . $alat['foto'])) : ?>

                                    <img src="../../assets/img/alat/<?= htmlspecialchars($alat['foto']) ?>"
                                        class="img-fluid img-thumbnail shadow-sm"
                                        style="max-height:300px;object-fit:cover;">

                                    <?php else : ?>

                                    <img src="../../assets/img/no-image.png" class="img-fluid img-thumbnail shadow-sm"
                                        style="max-height:300px;object-fit:cover;">

                                    <?php endif; ?>

                                </div>

                                <!-- Informasi -->

                                <div class="col-md-8">

                                    <table class="table table-bordered">

                                        <tr>

                                            <th width="30%">

                                                Nama Alat

                                            </th>

                                            <td>

                                                <?= htmlspecialchars($alat['nama_alat']) ?>

                                            </td>

                                        </tr>

                                        <tr>

                                            <th>

                                                Kategori

                                            </th>

                                            <td>

                                                <?= htmlspecialchars($alat['nama_kategori']) ?>

                                            </td>

                                        </tr>

                                        <tr>

                                            <th>

                                                Lokasi

                                            </th>

                                            <td>

                                                <?= htmlspecialchars($alat['lokasi']) ?>

                                            </td>

                                        </tr>

                                        <tr>

                                            <th>

                                                Kondisi

                                            </th>

                                            <td>
                                                <div class="col-md-6 mb-3">

                                                    <label class="form-label">

                                                        Tanggal Pinjam
                                                    </label>

                                                    <input type="date" name="tanggal_pinjam" class="form-control"
                                                        value="<?= htmlspecialchars($tanggalPinjam) ?>"
                                                        min="<?= date('Y-m-d') ?>" required>

                                                </div>

                                                <div class="col-md-6 mb-3">

                                                    <label class="form-label">

                                                        Tanggal Kembali

                                                    </label>

                                                    <input type="date" name="tanggal_kembali" class="form-control"
                                                        value="<?= htmlspecialchars($tanggalKembali) ?>"
                                                        min="<?= date('Y-m-d') ?>" required>

                                                </div>

                                                <div class="col-md-6 mb-3">

                                                    <label class="form-label">

                                                        Jumlah Pinjam

                                                    </label>

                                                    <input type="number" name="jumlah" class="form-control"
                                                        min="1" max="<?= (int) $alat['stok'] ?>"
                                                        value="<?= (int) $jumlah ?>" required>

                                                    <small class="text-muted">

                                                        Maksimal <?= (int) $alat['stok'] ?> unit.

                                                    </small>

                                                </div>

                                                <div class="col-md-6 mb-3">

                                                    <label class="form-label">

                                                        Status Pengajuan

                                                    </label>

                                                    <input type="text" class="form-control bg-light"
                                                        value="Menunggu" readonly>

                                                </div>


                                </div>

                                <hr>

                                <div class="d-flex justify-content-between">

                                    <a href="../alat/detail.php?id=<?= $alat['id_alat'] ?>" class="btn btn-secondary">

                                        <i class="bi bi-arrow-left"></i>

                                        Kembali

                                    </a>

                                    <button type="submit" class="btn btn-success">

                                        <i class="bi bi-send-check"></i>

                                        Ajukan Peminjaman

                                    </button>

                                </div>

                            </div>

                        </div>

                </form>

            </div>

        </section>

    </div>
    <?php include '../../layouts/footer.php'; ?>

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

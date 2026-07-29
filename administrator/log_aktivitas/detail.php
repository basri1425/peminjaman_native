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
| Mengambil ID Log
|--------------------------------------------------------------------------
*/

$idLog = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($idLog <= 0) {
    header('Location: index.php');
    exit();
}

/*
|--------------------------------------------------------------------------
| Mengambil Data Log Aktivitas
|--------------------------------------------------------------------------
*/

$sql = "

    SELECT

        l.id_log,
        l.aktivitas,
        l.waktu,
        l.ip_address,

        u.id_user,
        u.nama_lengkap,
        u.level

    FROM log_aktivitas l

    INNER JOIN users u
        ON l.id_user = u.id_user

    WHERE

        l.id_log = ?

    LIMIT 1

";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die($conn->error);
}

$stmt->bind_param(
    'i',

    $idLog,
);

if (!$stmt->execute()) {
    die($stmt->error);
}

$data = $stmt->get_result()->fetch_assoc();

/*
|--------------------------------------------------------------------------
| Validasi Data
|--------------------------------------------------------------------------
*/

if (!$data) {
    echo "

    <script>

        alert('Data log aktivitas tidak ditemukan.');

        window.location='index.php';

    </script>

    ";

    exit();
}

?>
<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Detail Log Aktivitas</title>

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

                                <i class="fas fa-eye text-primary"></i>

                                Detail Log Aktivitas

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

                                        Log Aktivitas

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

                    <div class="card card-primary">

                        <div class="card-header">

                            <h3 class="card-title">

                                <i class="fas fa-info-circle"></i>

                                Informasi Detail Log Aktivitas

                            </h3>

                        </div>

                        <div class="card-body">

                            <div class="row">

                                <div class="col-md-12">

                                    <table class="table table-bordered">
                                        <tr>

                                            <th width="25%">ID Log</th>

                                            <td>

                                                <?= $data['id_log'] ?>

                                            </td>

                                        </tr>

                                        <tr>

                                            <th>Nama Pengguna</th>

                                            <td>

                                                <?= htmlspecialchars($data['nama_lengkap']) ?>

                                            </td>

                                        </tr>

                                        <tr>

                                            <th>Level Pengguna</th>

                                            <td>

                                                <?php
                                                
                                                switch ($data['level']) {
                                                    case 'Administrator':
                                                        echo '<span class="badge badge-danger">Administrator</span>';
                                                
                                                        break;
                                                
                                                    case 'Petugas':
                                                        echo '<span class="badge badge-info">Petugas</span>';
                                                
                                                        break;
                                                
                                                    case 'Peminjam':
                                                        echo '<span class="badge badge-success">Peminjam</span>';
                                                
                                                        break;
                                                
                                                    default:
                                                        echo '<span class="badge badge-secondary">' . htmlspecialchars($data['level']) . '</span>';
                                                }
                                                
                                                ?>

                                            </td>

                                        </tr>

                                        <tr>

                                            <th>Aktivitas</th>

                                            <td>

                                                <?= htmlspecialchars($data['aktivitas']) ?>

                                            </td>

                                        </tr>

                                        <tr>

                                            <th>Tanggal & Waktu</th>

                                            <td>

                                                <?= date('d F Y H:i:s', strtotime($data['waktu'])) ?>

                                            </td>

                                        </tr>

                                        <tr>

                                            <th>IP Address</th>

                                            <td>

                                                <?= htmlspecialchars($data['ip_address']) ?>

                                            </td>

                                        </tr>

                                    </table>

                                </div>

                            </div>

                        </div>

                        <div class="card-footer">

                            <a href="index.php" class="btn btn-secondary">

                                <i class="fas fa-arrow-left"></i>

                                Kembali

                            </a>

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

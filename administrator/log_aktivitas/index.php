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

    ORDER BY

        l.waktu DESC,
        l.id_log DESC

";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die($conn->error);
}

if (!$stmt->execute()) {
    die($stmt->error);
}

$result = $stmt->get_result();

/*
|--------------------------------------------------------------------------
| Menghitung Jumlah Data
|--------------------------------------------------------------------------
*/

$totalData = $result->num_rows;

?>
<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Log Aktivitas</title>

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

                                <i class="fas fa-history text-primary"></i>

                                Log Aktivitas

                            </h1>

                        </div>

                        <div class="col-sm-6">

                            <ol class="breadcrumb float-sm-right">

                                <li class="breadcrumb-item">

                                    <a href="../dashboard/index.php">

                                        Dashboard

                                    </a>

                                </li>

                                <li class="breadcrumb-item active">

                                    Log Aktivitas

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

                                <i class="fas fa-list"></i>

                                Daftar Log Aktivitas

                            </h3>

                        </div>

                        <div class="card-body">

                            <div class="row mb-3">

                                <div class="col-md-6">

                                    <span class="badge badge-primary p-2">

                                        Total Log :

                                        <?= number_format($totalData) ?>

                                        Data

                                    </span>

                                </div>

                            </div>

                            <div class="table-responsive">
                                <table class="table table-bordered table-hover table-striped">

                                    <thead class="table-primary text-center">

                                        <tr>

                                            <th width="5%">No</th>

                                            <th width="18%">Waktu</th>

                                            <th width="20%">Nama Pengguna</th>

                                            <th width="12%">Level</th>

                                            <th>Aktivitas</th>

                                            <th width="15%">IP Address</th>

                                            <th width="10%">Aksi</th>

                                        </tr>

                                    </thead>

                                    <tbody>

                                        <?php if ($totalData > 0) : ?>

                                        <?php

            $no = 1;

            while ($row = $result->fetch_assoc()) :

            ?>

                                        <tr>

                                            <td class="text-center">

                                                <?= $no++ ?>

                                            </td>

                                            <td>

                                                <?= date('d-m-Y H:i:s', strtotime($row['waktu'])) ?>

                                            </td>

                                            <td>

                                                <?= htmlspecialchars($row['nama_lengkap']) ?>

                                            </td>

                                            <td class="text-center">

                                                <?php
                                                
                                                switch ($row['level']) {
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
                                                        echo '<span class="badge badge-secondary">' . htmlspecialchars($row['level']) . '</span>';
                                                }
                                                
                                                ?>

                                            </td>

                                            <td>

                                                <?= htmlspecialchars($row['aktivitas']) ?>

                                            </td>

                                            <td class="text-center">

                                                <?= htmlspecialchars($row['ip_address']) ?>

                                            </td>

                                            <td class="text-center">

                                                <a href="detail.php?id=<?= $row['id_log'] ?>"
                                                    class="btn btn-info btn-sm">

                                                    <i class="fas fa-eye"></i>

                                                    Detail

                                                </a>

                                            </td>

                                        </tr>

                                        <?php endwhile; ?>

                                        <?php else : ?>

                                        <tr>

                                            <td colspan="7" class="text-center">

                                                <div class="alert alert-warning mb-0">

                                                    <i class="fas fa-exclamation-circle"></i>

                                                    Belum ada data log aktivitas.

                                                </div>

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

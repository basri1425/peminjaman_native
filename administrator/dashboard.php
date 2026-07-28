<?php
/*
|--------------------------------------------------------------------------
| File        : dashboard.php
| Folder      : administrator
| Fungsi      : Dashboard Administrator
|--------------------------------------------------------------------------
*/

require_once '../config/session.php';
require_once '../config/database.php';

$title = 'Dashboard Administrator';

require_once '../layouts/header.php';
require_once '../layouts/navbar.php';
require_once '../layouts/sidebar.php';

/*
|--------------------------------------------------------------------------
| Mengambil Data Statistik
|--------------------------------------------------------------------------
*/

// Jumlah User
$queryUser = $conn->query('SELECT COUNT(*) AS total FROM users');
$totalUser = $queryUser->fetch_assoc()['total'];

// Jumlah Kategori
$queryKategori = $conn->query('SELECT COUNT(*) AS total FROM kategori');
$totalKategori = $queryKategori->fetch_assoc()['total'];

// Jumlah Alat
$queryAlat = $conn->query('SELECT COUNT(*) AS total FROM alat');
$totalAlat = $queryAlat->fetch_assoc()['total'];

// Jumlah Peminjaman
$queryPinjam = $conn->query('SELECT COUNT(*) AS total FROM peminjaman');
$totalPinjam = $queryPinjam->fetch_assoc()['total'];

// Jumlah Pengembalian
$queryKembali = $conn->query('SELECT COUNT(*) AS total FROM pengembalian');
$totalKembali = $queryKembali->fetch_assoc()['total'];
?>

<div class="container-fluid">

    <h2 class="mb-2">

        <i class="bi bi-speedometer2"></i>

        Dashboard Administrator

    </h2>

    <p class="text-muted">

        Selamat datang,
        <strong><?= $_SESSION['nama_lengkap'] ?></strong>

    </p>

    <div class="row">

        <!-- User -->
        <div class="col-lg-3 col-md-6 mb-4">

            <div class="card border-primary shadow-sm">

                <div class="card-body">

                    <h6>Total User</h6>

                    <h2><?= $totalUser ?></h2>

                </div>

            </div>

        </div>

        <!-- Kategori -->
        <div class="col-lg-3 col-md-6 mb-4">

            <div class="card border-success shadow-sm">

                <div class="card-body">

                    <h6>Kategori</h6>

                    <h2><?= $totalKategori ?></h2>

                </div>

            </div>

        </div>

        <!-- Alat -->
        <div class="col-lg-3 col-md-6 mb-4">

            <div class="card border-warning shadow-sm">

                <div class="card-body">

                    <h6>Data Alat</h6>

                    <h2><?= $totalAlat ?></h2>

                </div>

            </div>

        </div>

        <!-- Peminjaman -->
        <div class="col-lg-3 col-md-6 mb-4">

            <div class="card border-danger shadow-sm">

                <div class="card-body">

                    <h6>Peminjaman</h6>

                    <h2><?= $totalPinjam ?></h2>

                </div>

            </div>

        </div>

    </div>

    <div class="row">

        <div class="col-md-12">

            <div class="card shadow-sm">

                <div class="card-header bg-primary text-white">

                    Data Pengembalian

                </div>

                <div class="card-body">

                    <h3 class="text-center">

                        <?= $totalKembali ?>

                    </h3>

                    <p class="text-center">

                        Total Data Pengembalian

                    </p>

                </div>

            </div>

        </div>

    </div>

    <br>

    <div class="card shadow-sm">

        <div class="card-header bg-success text-white">

            Peminjaman Terbaru

        </div>

        <div class="card-body table-responsive">

            <table class="table table-bordered table-hover">

                <thead class="table-light">

                    <tr>

                        <th>No</th>
                        <th>Tanggal</th>
                        <th>Peminjam</th>
                        <th>Status</th>

                    </tr>

                </thead>

                <tbody>

                    <?php

                    $sql = "
                        SELECT
                            p.*,
                            u.nama_lengkap
                        FROM peminjaman p
                        JOIN users u
                            ON p.id_user = u.id_user
                        ORDER BY p.id_peminjaman DESC
                        LIMIT 5
                    ";

                    $result = $conn->query($sql);

                    $no = 1;

                    while ($row = $result->fetch_assoc()) :

                    ?>

                    <tr>

                        <td><?= $no++ ?></td>

                        <td><?= $row['tanggal_pinjam'] ?></td>

                        <td><?= $row['nama_lengkap'] ?></td>

                        <td><?= $row['status'] ?></td>

                    </tr>

                    <?php endwhile; ?>

                    <?php
                    
                    if ($result->num_rows == 0) {
                        echo '
                                            <tr>
                                                <td colspan="4" class="text-center">
                                                    Belum ada data peminjaman.
                                                </td>
                                            </tr>';
                    }
                    
                    ?>

                </tbody>

            </table>

        </div>

    </div>

</div>

<?php

require_once '../layouts/footer.php';

?>

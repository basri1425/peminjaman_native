<?php
/*
|--------------------------------------------------------------------------
| File        : dashboard.php
| Folder      : petugas
| Fungsi      : Dashboard Petugas
|--------------------------------------------------------------------------
*/

require_once '../config/session.php';
require_once '../config/database.php';

$title = 'Dashboard Petugas';

require_once '../layouts/header.php';
require_once '../layouts/navbar.php';
require_once '../layouts/sidebar.php';

/*
|--------------------------------------------------------------------------
| Statistik Dashboard
|--------------------------------------------------------------------------
*/

// Menunggu Persetujuan
$query = $conn->query("
    SELECT COUNT(*) AS total
    FROM peminjaman
    WHERE status='Menunggu'
");
$totalMenunggu = $query->fetch_assoc()['total'];

// Sedang Dipinjam
$query = $conn->query("
    SELECT COUNT(*) AS total
    FROM peminjaman
    WHERE status='Dipinjam'
");
$totalDipinjam = $query->fetch_assoc()['total'];

// Pengembalian
$query = $conn->query("
    SELECT COUNT(*) AS total
    FROM pengembalian
");
$totalPengembalian = $query->fetch_assoc()['total'];

// Total Alat
$query = $conn->query("
    SELECT SUM(stok) AS total
    FROM alat
");
$dataAlat = $query->fetch_assoc();
$totalAlat = $dataAlat['total'] ?? 0;
?>

<div class="container-fluid">

    <h2 class="mb-2">

        <i class="bi bi-person-workspace"></i>

        Dashboard Petugas

    </h2>

    <p class="text-muted">

        Selamat datang,

        <strong><?= $_SESSION['nama_lengkap'] ?></strong>

    </p>

    <div class="row">

        <div class="col-lg-3 col-md-6 mb-4">

            <div class="card border-warning shadow-sm">

                <div class="card-body">

                    <h6>Menunggu Persetujuan</h6>

                    <h2><?= $totalMenunggu ?></h2>

                </div>

            </div>

        </div>

        <div class="col-lg-3 col-md-6 mb-4">

            <div class="card border-primary shadow-sm">

                <div class="card-body">

                    <h6>Sedang Dipinjam</h6>

                    <h2><?= $totalDipinjam ?></h2>

                </div>

            </div>

        </div>

        <div class="col-lg-3 col-md-6 mb-4">

            <div class="card border-success shadow-sm">

                <div class="card-body">

                    <h6>Pengembalian</h6>

                    <h2><?= $totalPengembalian ?></h2>

                </div>

            </div>

        </div>

        <div class="col-lg-3 col-md-6 mb-4">

            <div class="card border-info shadow-sm">

                <div class="card-body">

                    <h6>Total Stok Alat</h6>

                    <h2><?= $totalAlat ?></h2>

                </div>

            </div>

        </div>

    </div>

    <div class="card shadow-sm">

        <div class="card-header bg-primary text-white">

            Permohonan Peminjaman Terbaru

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
                        ON p.id_user=u.id_user
                    ORDER BY p.id_peminjaman DESC
                    LIMIT 5
                ";

                $result = $conn->query($sql);

                $no = 1;

                if($result->num_rows > 0):

                    while($row = $result->fetch_assoc()):

                ?>

                    <tr>

                        <td><?= $no++ ?></td>

                        <td><?= $row['tanggal_pinjam'] ?></td>

                        <td><?= $row['nama_lengkap'] ?></td>

                        <td>

                            <?php
                            
                            switch ($row['status']) {
                                case 'Menunggu':
                                    echo '<span class="badge bg-warning text-dark">Menunggu</span>';
                                    break;
                            
                                case 'Disetujui':
                                    echo '<span class="badge bg-primary">Disetujui</span>';
                                    break;
                            
                                case 'Dipinjam':
                                    echo '<span class="badge bg-info">Dipinjam</span>';
                                    break;
                            
                                case 'Selesai':
                                    echo '<span class="badge bg-success">Selesai</span>';
                                    break;
                            
                                default:
                                    echo '<span class="badge bg-danger">Ditolak</span>';
                            }
                            
                            ?>

                        </td>

                    </tr>

                    <?php

                    endwhile;

                else:

                ?>

                    <tr>

                        <td colspan="4" class="text-center">

                            Belum ada data peminjaman.

                        </td>

                    </tr>

                    <?php endif; ?>

                </tbody>

            </table>

        </div>

    </div>

</div>

<?php
require_once '../layouts/footer.php';
?>

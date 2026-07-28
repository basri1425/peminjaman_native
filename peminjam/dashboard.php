<?php
/*
|--------------------------------------------------------------------------
| File        : dashboard.php
| Folder      : peminjam
| Fungsi      : Dashboard Peminjam
|--------------------------------------------------------------------------
*/

require_once '../config/session.php';
require_once '../config/database.php';

if ($_SESSION['level'] != 'Peminjam') {
    header('Location: ../auth/login.php');
    exit();
}

$title = 'Dashboard Peminjam';

require_once '../layouts/header.php';
require_once '../layouts/navbar.php';
require_once '../layouts/sidebar.php';

$idUser = $_SESSION['id_user'];

/*
|--------------------------------------------------------------------------
| Statistik Dashboard
|--------------------------------------------------------------------------
*/

// Total Pengajuan
$stmt = $conn->prepare("
    SELECT COUNT(*) AS total
    FROM peminjaman
    WHERE id_user = ?
");
$stmt->bind_param('i', $idUser);
$stmt->execute();
$totalPengajuan = $stmt->get_result()->fetch_assoc()['total'];

// Menunggu
$stmt = $conn->prepare("
    SELECT COUNT(*) AS total
    FROM peminjaman
    WHERE id_user = ?
    AND status='Menunggu'
");
$stmt->bind_param('i', $idUser);
$stmt->execute();
$totalMenunggu = $stmt->get_result()->fetch_assoc()['total'];

// Dipinjam
$stmt = $conn->prepare("
    SELECT COUNT(*) AS total
    FROM peminjaman
    WHERE id_user = ?
    AND status='Dipinjam'
");
$stmt->bind_param('i', $idUser);
$stmt->execute();
$totalDipinjam = $stmt->get_result()->fetch_assoc()['total'];

// Selesai
$stmt = $conn->prepare("
    SELECT COUNT(*) AS total
    FROM peminjaman
    WHERE id_user = ?
    AND status='Selesai'
");
$stmt->bind_param('i', $idUser);
$stmt->execute();
$totalSelesai = $stmt->get_result()->fetch_assoc()['total'];
?>

<div class="container-fluid">

    <h2 class="mb-2">

        <i class="bi bi-person-circle"></i>

        Dashboard Peminjam

    </h2>

    <p class="text-muted">

        Selamat datang,

        <strong><?= htmlspecialchars($_SESSION['nama_lengkap']) ?></strong>

    </p>

    <div class="row">

        <div class="col-lg-3 col-md-6 mb-4">

            <div class="card border-primary shadow-sm">

                <div class="card-body text-center">

                    <h6>Total Pengajuan</h6>

                    <h2><?= $totalPengajuan ?></h2>

                </div>

            </div>

        </div>

        <div class="col-lg-3 col-md-6 mb-4">

            <div class="card border-warning shadow-sm">

                <div class="card-body text-center">

                    <h6>Menunggu</h6>

                    <h2><?= $totalMenunggu ?></h2>

                </div>

            </div>

        </div>

        <div class="col-lg-3 col-md-6 mb-4">

            <div class="card border-info shadow-sm">

                <div class="card-body text-center">

                    <h6>Sedang Dipinjam</h6>

                    <h2><?= $totalDipinjam ?></h2>

                </div>

            </div>

        </div>

        <div class="col-lg-3 col-md-6 mb-4">

            <div class="card border-success shadow-sm">

                <div class="card-body text-center">

                    <h6>Selesai</h6>

                    <h2><?= $totalSelesai ?></h2>

                </div>

            </div>

        </div>

    </div>

    <div class="card shadow-sm">

        <div class="card-header bg-primary text-white">

            Riwayat Peminjaman Terbaru

        </div>

        <div class="card-body table-responsive">

            <table class="table table-bordered table-hover">

                <thead class="table-light">

                    <tr>

                        <th>No</th>
                        <th>Tanggal Pinjam</th>
                        <th>Tanggal Kembali</th>
                        <th>Status</th>

                    </tr>

                </thead>

                <tbody>

                    <?php

                $stmt = $conn->prepare("
                    SELECT *
                    FROM peminjaman
                    WHERE id_user = ?
                    ORDER BY id_peminjaman DESC
                    LIMIT 5
                ");

                $stmt->bind_param("i", $idUser);
                $stmt->execute();

                $result = $stmt->get_result();

                $no = 1;

                if ($result->num_rows > 0):

                    while ($row = $result->fetch_assoc()):

                ?>

                    <tr>

                        <td><?= $no++ ?></td>

                        <td><?= htmlspecialchars($row['tanggal_pinjam']) ?></td>

                        <td><?= htmlspecialchars($row['tanggal_kembali']) ?></td>

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
                                    echo '<span class="badge bg-info text-dark">Dipinjam</span>';
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

                            Belum ada riwayat peminjaman.

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

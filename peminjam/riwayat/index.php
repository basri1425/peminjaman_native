<?php

require '../../config/session.php';
require '../../config/database.php';

/*
|--------------------------------------------------------------------------
| Validasi Login
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION['id_user'])) {

    header("Location: ../../login.php");
    exit();
}

/*
|--------------------------------------------------------------------------
| Validasi Hak Akses
|--------------------------------------------------------------------------
*/

if ($_SESSION['level'] != 'Peminjam') {

    header("Location: ../../unauthorized.php");
    exit();
}

$idUser = $_SESSION['id_user'];

/*
|--------------------------------------------------------------------------
| Statistik
|--------------------------------------------------------------------------
*/

/* Total Transaksi */

$sql = "

SELECT

COUNT(*) AS total

FROM peminjaman

WHERE

id_user = ?

";

$stmt = $conn->prepare($sql);

$stmt->bind_param("i", $idUser);

$stmt->execute();

$totalTransaksi = $stmt->get_result()->fetch_assoc()['total'];

$stmt->close();

/* Sedang Dipinjam */

$sql = "

SELECT

COUNT(*) AS total

FROM peminjaman

WHERE

id_user = ?

AND

status = 'Dipinjam'

";

$stmt = $conn->prepare($sql);

$stmt->bind_param("i", $idUser);

$stmt->execute();

$totalDipinjam = $stmt->get_result()->fetch_assoc()['total'];

$stmt->close();

/* Menunggu Persetujuan */

$sql = "

SELECT

COUNT(*) AS total

FROM peminjaman

WHERE

id_user = ?

AND

status = 'Menunggu'

";

$stmt = $conn->prepare($sql);

$stmt->bind_param("i", $idUser);

$stmt->execute();

$totalMenunggu = $stmt->get_result()->fetch_assoc()['total'];

$stmt->close();

/* Selesai */

$sql = "

SELECT

COUNT(*) AS total

FROM peminjaman

WHERE

id_user = ?

AND

status = 'Selesai'

";

$stmt = $conn->prepare($sql);

$stmt->bind_param("i", $idUser);

$stmt->execute();

$totalSelesai = $stmt->get_result()->fetch_assoc()['total'];

$stmt->close();

/*
|--------------------------------------------------------------------------
| Riwayat Peminjaman
|--------------------------------------------------------------------------
*/

$sql = "

SELECT

    p.id_peminjaman,
    p.tanggal_pinjam,
    p.tanggal_kembali,
    p.status,
    p.created_at,

    COUNT(dp.id_detail) AS jumlah_item

FROM peminjaman p

INNER JOIN detail_peminjaman dp
ON dp.id_peminjaman = p.id_peminjaman

WHERE

p.id_user = ?

GROUP BY

    p.id_peminjaman,
    p.tanggal_pinjam,
    p.tanggal_kembali,
    p.status,
    p.created_at

ORDER BY

p.created_at DESC

";

$stmt = $conn->prepare($sql);

$stmt->bind_param("i", $idUser);

$stmt->execute();

$riwayat = $stmt->get_result();

?>

<?php include '../../layouts/header.php'; ?>
<?php include '../../layouts/navbar.php'; ?>
<?php include '../../layouts/sidebar.php'; ?>

<div class="content-wrapper">

    <section class="content-header">

        <div class="container-fluid">

            <div class="row mb-2">

                <div class="col-sm-6">

                    <h1>

                        <i class="fas fa-history"></i>

                        Riwayat Peminjaman

                    </h1>

                    <p class="text-muted">

                        Menampilkan seluruh riwayat peminjaman alat yang pernah Anda lakukan.

                    </p>

                </div>

            </div>

        </div>

    </section>

    <section class="content">

        <div class="container-fluid">
            <!-- Alert -->

            <?php if (isset($_SESSION['success'])) { ?>

                <div class="alert alert-success alert-dismissible fade show">

                    <button type="button"
                        class="btn-close"
                        data-bs-dismiss="alert"></button>

                    <?= htmlspecialchars($_SESSION['success']); ?>

                </div>

            <?php unset($_SESSION['success']);
            } ?>

            <?php if (isset($_SESSION['error'])) { ?>

                <div class="alert alert-danger alert-dismissible fade show">

                    <button type="button"
                        class="btn-close"
                        data-bs-dismiss="alert"></button>

                    <?= htmlspecialchars($_SESSION['error']); ?>

                </div>

            <?php unset($_SESSION['error']);
            } ?>

            <!-- Statistik -->

            <div class="row">

                <div class="col-lg-3 col-md-6">

                    <div class="small-box bg-primary">

                        <div class="inner">

                            <h3><?= $totalTransaksi; ?></h3>

                            <p>Total Transaksi</p>

                        </div>

                        <div class="icon">

                            <i class="fas fa-folder-open"></i>

                        </div>

                    </div>

                </div>

                <div class="col-lg-3 col-md-6">

                    <div class="small-box bg-info">

                        <div class="inner">

                            <h3><?= $totalDipinjam; ?></h3>

                            <p>Sedang Dipinjam</p>

                        </div>

                        <div class="icon">

                            <i class="fas fa-hand-holding"></i>

                        </div>

                    </div>

                </div>

                <div class="col-lg-3 col-md-6">

                    <div class="small-box bg-warning">

                        <div class="inner">

                            <h3><?= $totalMenunggu; ?></h3>

                            <p>Menunggu Persetujuan</p>

                        </div>

                        <div class="icon">

                            <i class="fas fa-hourglass-half"></i>

                        </div>

                    </div>

                </div>

                <div class="col-lg-3 col-md-6">

                    <div class="small-box bg-success">

                        <div class="inner">

                            <h3><?= $totalSelesai; ?></h3>

                            <p>Selesai</p>

                        </div>

                        <div class="icon">

                            <i class="fas fa-check-circle"></i>

                        </div>

                    </div>

                </div>

            </div>

            <!-- Card Riwayat -->

            <div class="card">

                <div class="card-header">

                    <h3 class="card-title">

                        <i class="fas fa-history"></i>

                        Daftar Riwayat Peminjaman

                    </h3>

                </div>

                <div class="card-body">
                    <div class="table-responsive">

                        <table id="datatable" class="table table-bordered table-striped">

                            <thead>

                                <tr>

                                    <th width="5%" class="text-center">No</th>

                                    <th>No. Peminjaman</th>

                                    <th>Tanggal Pinjam</th>

                                    <th>Tanggal Kembali</th>

                                    <th class="text-center">Jumlah Item</th>

                                    <th class="text-center">Status</th>

                                    <th width="10%" class="text-center">Aksi</th>

                                </tr>

                            </thead>

                            <tbody>

                                <?php

                                $no = 1;

                                while ($row = $riwayat->fetch_assoc()) {

                                ?>

                                    <tr>

                                        <td class="text-center">

                                            <?= $no++; ?>

                                        </td>

                                        <td>

                                            PMJ<?= str_pad($row['id_peminjaman'], 5, '0', STR_PAD_LEFT); ?>

                                        </td>

                                        <td>

                                            <?= date('d-m-Y', strtotime($row['tanggal_pinjam'])); ?>

                                        </td>

                                        <td>

                                            <?= date('d-m-Y', strtotime($row['tanggal_kembali'])); ?>

                                        </td>

                                        <td class="text-center">

                                            <?= $row['jumlah_item']; ?>

                                        </td>

                                        <td class="text-center">

                                            <?php

                                            switch ($row['status']) {

                                                case 'Menunggu':
                                                    $badge = 'secondary';
                                                    break;

                                                case 'Disetujui':
                                                    $badge = 'primary';
                                                    break;

                                                case 'Dipinjam':
                                                    $badge = 'info';
                                                    break;

                                                case 'Menunggu Pengembalian':
                                                    $badge = 'warning';
                                                    break;

                                                case 'Selesai':
                                                    $badge = 'success';
                                                    break;

                                                case 'Ditolak':
                                                    $badge = 'danger';
                                                    break;

                                                default:
                                                    $badge = 'dark';
                                            }

                                            ?>

                                            <span class="badge bg-<?= $badge; ?>">

                                                <?= htmlspecialchars($row['status']); ?>

                                            </span>

                                        </td>

                                        <td class="text-center">

                                            <a

                                                href="detail.php?id=<?= $row['id_peminjaman']; ?>"

                                                class="btn btn-primary btn-sm">

                                                <i class="fas fa-search"></i>

                                                Detail

                                            </a>

                                        </td>

                                    </tr>

                                <?php } ?>

                            </tbody>

                        </table>

                    </div>

                    <?php if ($riwayat->num_rows == 0) { ?>

                        <div class="text-center py-5">

                            <i class="fas fa-folder-open fa-4x text-secondary mb-3"></i>

                            <h5>Belum ada riwayat peminjaman.</h5>

                            <p class="text-muted">

                                Silakan lakukan peminjaman alat terlebih dahulu.

                            </p>

                        </div>

                    <?php } ?>
                </div>

            </div>

        </div>

    </section>

</div>

<?php

$stmt->close();

$conn->close();

?>

<?php include '../../layouts/footer.php'; ?>


<script>
    $(document).ready(function() {

        $('#datatable').DataTable({

            responsive: true,

            autoWidth: false,

            pageLength: 10,

            language: {

                emptyTable: "Belum ada riwayat peminjaman.",

                zeroRecords: "Data tidak ditemukan.",

                search: "Cari :",

                lengthMenu: "Tampilkan _MENU_ data",

                info: "Menampilkan _START_ - _END_ dari _TOTAL_ data",

                infoEmpty: "Tidak ada data",

                paginate: {

                    first: "Awal",

                    last: "Akhir",

                    next: "›",

                    previous: "‹"

                }

            }

        });

    });
</script>
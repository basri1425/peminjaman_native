<?php

require '../../config/session.php';
require '../../config/database.php';

if (!isset($_SESSION['id_user'])) {
    header("Location: ../../login.php");
    exit;
}

if ($_SESSION['level'] != 'Petugas') {
    header("Location: ../../unauthorized.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| Query Data
|--------------------------------------------------------------------------
*/

$sql = "

SELECT

    p.id_peminjaman,
    p.tanggal_pinjam,
    p.tanggal_kembali,
    p.updated_at,

    u.nama,

    COUNT(dp.id_detail) AS jumlah_item

FROM peminjaman p

INNER JOIN users u
ON p.id_user=u.id_user

INNER JOIN detail_peminjaman dp
ON dp.id_peminjaman=p.id_peminjaman

WHERE p.status='Menunggu Pengembalian'

GROUP BY p.id_peminjaman

ORDER BY p.updated_at ASC

";

$result = $conn->query($sql);

/*
|--------------------------------------------------------------------------
| Statistik
|--------------------------------------------------------------------------
*/

$totalPengajuan = $result->num_rows;

$hariIni = date('Y-m-d');

$sqlHariIni = "

SELECT COUNT(*) total

FROM peminjaman

WHERE

status='Menunggu Pengembalian'

AND DATE(updated_at)=?

";

$stmt = $conn->prepare($sqlHariIni);

$stmt->bind_param("s", $hariIni);

$stmt->execute();

$dataHariIni = $stmt->get_result()->fetch_assoc();

$stmt->close();

$totalHariIni = $dataHariIni['total'];
?>

<?php include '../../template/header.php'; ?>
<?php include '../../template/navbar.php'; ?>
<?php include '../../template/sidebar.php'; ?>

<div class="content-wrapper">

    <section class="content-header">

        <div class="container-fluid">

            <div class="row mb-2">

                <div class="col-sm-6">

                    <h1>
                        <i class="fas fa-clipboard-check"></i>

                        Verifikasi Pengembalian
                    </h1>

                    <p class="text-muted">
                        Daftar pengajuan pengembalian yang menunggu verifikasi petugas.
                    </p>

                </div>

            </div>

        </div>

    </section>

    <section class="content">

        <div class="container-fluid">

            <?php if (isset($_SESSION['success'])) { ?>

                <div class="alert alert-success alert-dismissible">

                    <button class="close" data-dismiss="alert">&times;</button>

                    <?= $_SESSION['success']; ?>

                </div>

            <?php unset($_SESSION['success']);
            } ?>

            <?php if (isset($_SESSION['error'])) { ?>

                <div class="alert alert-danger alert-dismissible">

                    <button class="close" data-dismiss="alert">&times;</button>

                    <?= $_SESSION['error']; ?>

                </div>

            <?php unset($_SESSION['error']);
            } ?>

            <div class="row">

                <div class="col-md-6">

                    <div class="small-box bg-primary">

                        <div class="inner">

                            <h3><?= $totalPengajuan ?></h3>

                            <p>Total Pengajuan</p>

                        </div>

                        <div class="icon">

                            <i class="fas fa-file-import"></i>

                        </div>

                    </div>

                </div>

                <div class="col-md-6">

                    <div class="small-box bg-warning">

                        <div class="inner">

                            <h3><?= $totalHariIni ?></h3>

                            <p>Pengajuan Hari Ini</p>

                        </div>

                        <div class="icon">

                            <i class="fas fa-calendar-day"></i>

                        </div>

                    </div>

                </div>

            </div>

            <div class="card">

                <div class="card-header">

                    <h3 class="card-title">

                        Daftar Pengajuan Pengembalian

                    </h3>

                </div>

                <div class="card-body">

                    <table id="datatable" class="table table-bordered table-striped">

                        <thead>

                            <tr>

                                <th width="5%">No</th>

                                <th>No Peminjaman</th>

                                <th>Nama Peminjam</th>

                                <th>Tanggal Pinjam</th>

                                <th>Tanggal Pengajuan</th>

                                <th>Jumlah Item</th>

                                <th>Status</th>

                                <th width="10%">Aksi</th>

                            </tr>

                        </thead>

                        <tbody>

                            <?php

                            $no = 1;

                            while ($row = $result->fetch_assoc()) {

                            ?>

                                <tr>

                                    <td><?= $no++ ?></td>

                                    <td>

                                        PMJ<?= str_pad($row['id_peminjaman'], 5, '0', STR_PAD_LEFT) ?>

                                    </td>

                                    <td><?= htmlspecialchars($row['nama']) ?></td>

                                    <td><?= date('d-m-Y', strtotime($row['tanggal_pinjam'])) ?></td>

                                    <td><?= date('d-m-Y', strtotime($row['updated_at'])) ?></td>

                                    <td class="text-center">

                                        <?= $row['jumlah_item'] ?>

                                    </td>

                                    <td>

                                        <span class="badge bg-warning">

                                            Menunggu Pengembalian

                                        </span>

                                    </td>

                                    <td class="text-center">

                                        <a

                                            href="detail.php?id=<?= $row['id_peminjaman'] ?>"

                                            class="btn btn-primary btn-sm">

                                            <i class="fas fa-search"></i>

                                            Detail

                                        </a>

                                    </td>

                                </tr>

                            <?php } ?>

                        </tbody>

                    </table>

                    <?php if ($totalPengajuan == 0) { ?>

                        <div class="text-center p-5">

                            <i class="fas fa-box-open fa-4x text-secondary mb-3"></i>

                            <h5>

                                Belum ada pengajuan pengembalian.

                            </h5>

                        </div>

                    <?php } ?>

                </div>

            </div>

        </div>

    </section>

</div>

<?php include '../../template/footer.php'; ?>


<script>
    $(function() {

        $("#datatable").DataTable({

            responsive: true,

            autoWidth: false,

            language: {

                url: "../../assets/datatables/id.json"

            }

        });

    });
</script>
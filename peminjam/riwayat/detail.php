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
| Validasi Parameter
|--------------------------------------------------------------------------
*/

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {

    $_SESSION['error'] = "Data peminjaman tidak ditemukan.";

    header("Location: index.php");
    exit();
}

$idPeminjaman = (int) $_GET['id'];

/*
|--------------------------------------------------------------------------
| Informasi Peminjaman
|--------------------------------------------------------------------------
*/

$sql = "

SELECT

    p.id_peminjaman,
    p.tanggal_pinjam,
    p.tanggal_kembali,
    p.status,

    u.nama_lengkap

FROM peminjaman p

INNER JOIN users u
ON u.id_user = p.id_user

WHERE

p.id_peminjaman = ?

AND

p.id_user = ?

LIMIT 1

";

$stmt = $conn->prepare($sql);

$stmt->bind_param("ii", $idPeminjaman, $idUser);

$stmt->execute();

$peminjaman = $stmt->get_result();

if ($peminjaman->num_rows == 0) {

    $stmt->close();

    $_SESSION['error'] = "Data peminjaman tidak ditemukan.";

    header("Location: index.php");
    exit();
}

$dataPeminjaman = $peminjaman->fetch_assoc();

$stmt->close();

/*
|--------------------------------------------------------------------------
| Daftar Alat
|--------------------------------------------------------------------------
*/

$sql = "

SELECT

    a.nama_alat,
    a.lokasi,

    dp.jumlah

FROM detail_peminjaman dp

INNER JOIN alat a
ON a.id_alat = dp.id_alat

WHERE

dp.id_peminjaman = ?

ORDER BY

a.nama_alat ASC

";

$stmt = $conn->prepare($sql);

$stmt->bind_param("i", $idPeminjaman);

$stmt->execute();

$daftarAlat = $stmt->get_result();

$stmt->close();

/*
|--------------------------------------------------------------------------
| Informasi Pengembalian
|--------------------------------------------------------------------------
*/

$sql = "

SELECT

id_pengembalian,
tanggal_pengembalian,
kondisi_kembali,
keterangan

FROM pengembalian

WHERE

id_peminjaman = ?

LIMIT 1

";

$stmt = $conn->prepare($sql);

$stmt->bind_param("i", $idPeminjaman);

$stmt->execute();

$pengembalian = $stmt->get_result();

$dataPengembalian = null;

if ($pengembalian->num_rows > 0) {

    $dataPengembalian = $pengembalian->fetch_assoc();
}

$stmt->close();

/*
|--------------------------------------------------------------------------
| Detail Pemeriksaan Pengembalian
|--------------------------------------------------------------------------
*/

$detailPengembalian = null;

if ($dataPengembalian) {

    $sql = "

    SELECT

        a.nama_alat,

        dp.jumlah,
        dp.kondisi,
        dp.keterangan

    FROM detail_pengembalian dp

    INNER JOIN alat a
    ON a.id_alat = dp.id_alat

    WHERE

    dp.id_pengembalian = ?

    ORDER BY

    a.nama_alat ASC

    ";

    $stmt = $conn->prepare($sql);

    $stmt->bind_param("i", $dataPengembalian['id_pengembalian']);

    $stmt->execute();

    $detailPengembalian = $stmt->get_result();

    $stmt->close();
}

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

                        <i class="fas fa-file-alt"></i>

                        Detail Riwayat Peminjaman

                    </h1>

                    <p class="text-muted">

                        Informasi lengkap transaksi peminjaman dan pengembalian alat.

                    </p>

                </div>

            </div>

        </div>

    </section>

    <section class="content">

        <div class="container-fluid">
            <!-- Card Informasi Peminjaman -->

            <div class="card">

                <div class="card-header">

                    <h3 class="card-title">

                        <i class="fas fa-info-circle"></i>

                        Informasi Peminjaman

                    </h3>

                </div>

                <div class="card-body">

                    <div class="row">

                        <div class="col-md-6">

                            <table class="table table-borderless">

                                <tr>

                                    <th width="35%">No. Peminjaman</th>

                                    <td width="5%">:</td>

                                    <td>

                                        PMJ<?= str_pad($dataPeminjaman['id_peminjaman'], 5, '0', STR_PAD_LEFT); ?>

                                    </td>

                                </tr>

                                <tr>

                                    <th>Nama Peminjam</th>

                                    <td>:</td>

                                    <td>

                                        <?= htmlspecialchars($dataPeminjaman['nama_lengkap']); ?>

                                    </td>

                                </tr>

                                <tr>

                                    <th>Tanggal Pinjam</th>

                                    <td>:</td>

                                    <td>

                                        <?= date('d F Y', strtotime($dataPeminjaman['tanggal_pinjam'])); ?>

                                    </td>

                                </tr>

                            </table>

                        </div>

                        <div class="col-md-6">

                            <table class="table table-borderless">

                                <tr>

                                    <th width="35%">Tanggal Kembali</th>

                                    <td width="5%">:</td>

                                    <td>

                                        <?= date('d F Y', strtotime($dataPeminjaman['tanggal_kembali'])); ?>

                                    </td>

                                </tr>

                                <tr>

                                    <th>Status</th>

                                    <td>:</td>

                                    <td>

                                        <?php

                                        switch ($dataPeminjaman['status']) {

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

                                            <?= htmlspecialchars($dataPeminjaman['status']); ?>

                                        </span>

                                    </td>

                                </tr>

                            </table>

                        </div>

                    </div>

                </div>

            </div>
            <!-- Card Daftar Alat -->

            <div class="card">

                <div class="card-header">

                    <h3 class="card-title">

                        <i class="fas fa-toolbox"></i>

                        Daftar Alat yang Dipinjam

                    </h3>

                </div>

                <div class="card-body">

                    <div class="table-responsive">

                        <table class="table table-bordered table-striped">

                            <thead>

                                <tr>

                                    <th width="5%" class="text-center">No</th>

                                    <th>Nama Alat</th>

                                    <th>Lokasi</th>

                                    <th width="12%" class="text-center">Jumlah</th>


                                </tr>

                            </thead>

                            <tbody>

                                <?php

                                $no = 1;

                                if ($daftarAlat->num_rows > 0) {

                                    while ($alat = $daftarAlat->fetch_assoc()) {

                                ?>

                                        <tr>

                                            <td class="text-center">

                                                <?= $no++; ?>

                                            </td>

                                            <td>

                                                <?= htmlspecialchars($alat['nama_alat']); ?>

                                            </td>

                                            <td>

                                                <?= htmlspecialchars($alat['lokasi']); ?>

                                            </td>

                                            <td class="text-center">

                                                <?= $alat['jumlah']; ?>

                                            </td>


                                        </tr>

                                    <?php

                                    }
                                } else {

                                    ?>

                                    <tr>

                                        <td colspan="5" class="text-center text-muted">

                                            Tidak ada data alat.

                                        </td>

                                    </tr>

                                <?php } ?>

                            </tbody>

                        </table>

                    </div>

                </div>

            </div>
            <?php if ($dataPengembalian) { ?>

                <!-- Informasi Pengembalian -->

                <div class="card">

                    <div class="card-header">

                        <h3 class="card-title">

                            <i class="fas fa-undo-alt"></i>

                            Informasi Pengembalian

                        </h3>

                    </div>

                    <div class="card-body">

                        <div class="row">

                            <div class="col-md-6">

                                <table class="table table-borderless">

                                    <tr>

                                        <th width="35%">Tanggal Pengembalian</th>

                                        <td width="5%">:</td>

                                        <td>

                                            <?= date('d F Y', strtotime($dataPengembalian['tanggal_pengembalian'])); ?>

                                        </td>

                                    </tr>

                                    <tr>

                                        <th>Kondisi Pengembalian</th>

                                        <td>:</td>

                                        <td>

                                            <?php

                                            switch ($dataPengembalian['kondisi_kembali']) {

                                                case 'Baik':
                                                    $badge = 'success';
                                                    break;

                                                case 'Rusak Ringan':
                                                    $badge = 'warning';
                                                    break;

                                                case 'Rusak Berat':
                                                    $badge = 'danger';
                                                    break;

                                                default:
                                                    $badge = 'secondary';
                                            }

                                            ?>

                                            <span class="badge bg-<?= $badge; ?>">

                                                <?= htmlspecialchars($dataPengembalian['kondisi_kembali']); ?>

                                            </span>

                                        </td>

                                    </tr>

                                </table>

                            </div>

                            <div class="col-md-6">

                                <table class="table table-borderless">

                                    <tr>

                                        <th width="35%">Catatan Petugas</th>

                                        <td width="5%">:</td>

                                        <td>

                                            <?= !empty($dataPengembalian['keterangan'])
                                                ? nl2br(htmlspecialchars($dataPengembalian['keterangan']))
                                                : '<span class="text-muted">-</span>'; ?>

                                        </td>

                                    </tr>

                                </table>

                            </div>

                        </div>

                    </div>

                </div>

                <!-- Detail Pemeriksaan -->

                <div class="card">

                    <div class="card-header">

                        <h3 class="card-title">

                            <i class="fas fa-clipboard-check"></i>

                            Detail Hasil Pemeriksaan

                        </h3>

                    </div>

                    <div class="card-body">

                        <div class="table-responsive">

                            <table class="table table-bordered table-striped">

                                <thead>

                                    <tr>

                                        <th width="5%" class="text-center">No</th>

                                        <th>Nama Alat</th>

                                        <th width="12%" class="text-center">Jumlah</th>

                                        <th width="20%" class="text-center">Kondisi Saat Kembali</th>

                                        <th>Keterangan</th>

                                    </tr>

                                </thead>

                                <tbody>

                                    <?php

                                    $no = 1;

                                    if ($detailPengembalian && $detailPengembalian->num_rows > 0) {

                                        while ($detail = $detailPengembalian->fetch_assoc()) {

                                    ?>

                                            <tr>

                                                <td class="text-center">

                                                    <?= $no++; ?>

                                                </td>

                                                <td>

                                                    <?= htmlspecialchars($detail['nama_alat']); ?>

                                                </td>

                                                <td class="text-center">

                                                    <?= $detail['jumlah']; ?>

                                                </td>

                                                <td class="text-center">

                                                    <?php

                                                    switch ($detail['kondisi']) {

                                                        case 'Baik':
                                                            $badge = 'success';
                                                            break;

                                                        case 'Rusak Ringan':
                                                            $badge = 'warning';
                                                            break;

                                                        case 'Rusak Berat':
                                                            $badge = 'danger';
                                                            break;

                                                        case 'Hilang':
                                                            $badge = 'dark';
                                                            break;

                                                        default:
                                                            $badge = 'secondary';
                                                    }

                                                    ?>

                                                    <span class="badge bg-<?= $badge; ?>">

                                                        <?= htmlspecialchars($detail['kondisi']); ?>

                                                    </span>

                                                </td>

                                                <td>

                                                    <?= !empty($detail['keterangan'])
                                                        ? nl2br(htmlspecialchars($detail['keterangan']))
                                                        : '<span class="text-muted">-</span>'; ?>

                                                </td>

                                            </tr>

                                        <?php

                                        }
                                    } else {

                                        ?>

                                        <tr>

                                            <td colspan="5" class="text-center text-muted">

                                                Tidak ada data hasil pemeriksaan.

                                            </td>

                                        </tr>

                                    <?php } ?>

                                </tbody>

                            </table>

                        </div>

                    </div>

                </div>

            <?php } ?>
            <!-- Tombol Aksi -->

            <div class="card">

                <div class="card-body text-end">

                    <a href="index.php" class="btn btn-secondary">

                        <i class="fas fa-arrow-left"></i>

                        Kembali

                    </a>

                </div>

            </div>

        </div>

    </section>

</div>

<?php

$conn->close();

?>

<?php include '../../layouts/footer.php'; ?>

<?php

require '../../config/session.php';
require '../../config/database.php';

/*
|--------------------------------------------------------------------------
| Validasi Login
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION['id_user'])) {
    header('Location: ../../login.php');

    exit();
}

/*
|--------------------------------------------------------------------------
| Validasi Hak Akses
|--------------------------------------------------------------------------
*/

if ($_SESSION['level'] != 'Petugas') {
    header('Location: ../../unauthorized.php');

    exit();
}

/*
|--------------------------------------------------------------------------
| Mengambil Filter
|--------------------------------------------------------------------------
*/

$tanggalAwal = trim($_GET['tanggal_awal'] ?? '');

$tanggalAkhir = trim($_GET['tanggal_akhir'] ?? '');

$status = trim($_GET['status'] ?? '');

/*
|--------------------------------------------------------------------------
| Query Dasar
|--------------------------------------------------------------------------
*/

$sql = "

    SELECT

        p.id_peminjaman,
        p.tanggal_pinjam,
        p.tanggal_kembali,
        p.status,

        u.nama_lengkap,

        COUNT(dp.id_detail) AS jumlah_alat

    FROM peminjaman p

    INNER JOIN users u

        ON p.id_user = u.id_user

    LEFT JOIN detail_peminjaman dp

        ON p.id_peminjaman = dp.id_peminjaman

";

/*
|--------------------------------------------------------------------------
| Menyusun Filter
|--------------------------------------------------------------------------
*/

$where = [];

$params = [];

$types = '';

/*
|--------------------------------------------------------------------------
| Filter Tanggal
|--------------------------------------------------------------------------
*/

if (!empty($tanggalAwal) && !empty($tanggalAkhir)) {
    $where[] = "

        DATE(p.tanggal_pinjam)

        BETWEEN ?

        AND ?

    ";

    $types .= 'ss';

    $params[] = $tanggalAwal;

    $params[] = $tanggalAkhir;
}

/*
|--------------------------------------------------------------------------
| Filter Status
|--------------------------------------------------------------------------
*/

if (!empty($status)) {
    $where[] = "

        p.status = ?

    ";

    $types .= 's';

    $params[] = $status;
}

/*
|--------------------------------------------------------------------------
| Menambahkan WHERE
|--------------------------------------------------------------------------
*/

if (!empty($where)) {
    $sql .=
        "

        WHERE

        " . implode(' AND ', $where);
}

/*
|--------------------------------------------------------------------------
| GROUP BY dan ORDER BY
|--------------------------------------------------------------------------
*/

$sql .= "

    GROUP BY

        p.id_peminjaman,
        p.tanggal_pinjam,
        p.tanggal_kembali,
        p.status,
        u.nama_lengkap

    ORDER BY

        p.tanggal_pinjam DESC

";

/*
|--------------------------------------------------------------------------
| Prepared Statement
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die('Query gagal dipersiapkan.');
}

if (!empty($params)) {
    $stmt->bind_param(
        $types,

        ...$params,
    );
}

$stmt->execute();

$result = $stmt->get_result();

$totalData = $result->num_rows;

$pageTitle = 'Laporan Peminjaman';

include '../../layouts/header.php';
include '../../layouts/navbar.php';
include '../../layouts/sidebar.php';

?>

<div class="content-wrapper">

    <section class="content-header">

        <div class="container-fluid">

            <div class="row mb-2">

                <div class="col-sm-6">

                    <h1>

                        Laporan Peminjaman Alat

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

                            Laporan

                        </li>

                    </ol>

                </div>

            </div>

        </div>

    </section>

    <section class="content">

        <div class="container-fluid">
            <!-- ===========================================================
     Filter Laporan
=========================================================== -->

            <div class="card card-primary">

                <div class="card-header">

                    <h3 class="card-title">

                        Filter Laporan

                    </h3>

                </div>

                <form method="GET">

                    <div class="card-body">

                        <div class="row">

                            <div class="col-md-4">

                                <div class="form-group">

                                    <label>

                                        Tanggal Awal

                                    </label>

                                    <input type="date" name="tanggal_awal" class="form-control"
                                        value="<?= htmlspecialchars($tanggalAwal) ?>">

                                </div>

                            </div>

                            <div class="col-md-4">

                                <div class="form-group">

                                    <label>

                                        Tanggal Akhir

                                    </label>

                                    <input type="date" name="tanggal_akhir" class="form-control"
                                        value="<?= htmlspecialchars($tanggalAkhir) ?>">

                                </div>

                            </div>

                            <div class="col-md-4">

                                <div class="form-group">

                                    <label>

                                        Status

                                    </label>

                                    <select name="status" class="form-control">

                                        <option value="">

                                            Semua Status

                                        </option>

                                        <?php

                            $daftarStatus = [

                                "Menunggu",
                                "Disetujui",
                                "Ditolak",
                                "Dipinjam",
                                "Selesai"

                            ];

                            foreach ($daftarStatus as $itemStatus) :

                            ?>

                                        <option value="<?= $itemStatus ?>"
                                            <?= $status == $itemStatus ? 'selected' : '' ?>>

                                            <?= htmlspecialchars($itemStatus) ?>

                                        </option>

                                        <?php endforeach; ?>

                                    </select>

                                </div>

                            </div>

                        </div>

                    </div>

                    <div class="card-footer">

                        <button type="submit" class="btn btn-primary">

                            <i class="fas fa-search"></i>

                            Tampilkan

                        </button>

                        <a href="index.php" class="btn btn-secondary">

                            <i class="fas fa-sync-alt"></i>

                            Reset

                        </a>

                    </div>

                </form>

            </div>

            <!-- ===========================================================
     Statistik
=========================================================== -->

            <div class="row">

                <div class="col-md-4">

                    <div class="small-box bg-info">

                        <div class="inner">

                            <h3>

                                <?= $totalData ?>

                            </h3>

                            <p>

                                Total Transaksi

                            </p>

                        </div>

                        <div class="icon">

                            <i class="fas fa-file-alt"></i>

                        </div>

                    </div>

                </div>

            </div>

            <!-- ===========================================================
     Data Laporan
=========================================================== -->

            <div class="card">

                <div class="card-header">

                    <h3 class="card-title">

                        Data Laporan Peminjaman

                    </h3>

                </div>

                <div class="card-body table-responsive p-0">

                    <table class="table table-bordered table-hover">

                        <thead class="thead-light">

                            <tr>

                                <th width="60">

                                    No

                                </th>

                                <th>

                                    Tanggal Pinjam

                                </th>

                                <th>

                                    Tanggal Kembali

                                </th>

                                <th>

                                    Nama Peminjam

                                </th>

                                <th class="text-center">

                                    Jumlah Alat

                                </th>

                                <th class="text-center">

                                    Status

                                </th>

                                <th width="150" class="text-center">

                                    Aksi

                                </th>

                            </tr>

                        </thead>

                        <tbody>
                            <?php

$no = 1;

while ($row = $result->fetch_assoc()) :

    /*
    |--------------------------------------------------------------------------
    | Warna Badge Status
    |--------------------------------------------------------------------------
    */

    switch ($row['status']) {

        case "Menunggu":
            $badge = "warning";
            break;

        case "Disetujui":
            $badge = "primary";
            break;

        case "Ditolak":
            $badge = "danger";
            break;

        case "Dipinjam":
            $badge = "info";
            break;

        case "Selesai":
            $badge = "success";
            break;

        default:
            $badge = "secondary";

    }

?>

                            <tr>

                                <td class="text-center">

                                    <?= $no++ ?>

                                </td>

                                <td>

                                    <?= htmlspecialchars($row['tanggal_pinjam']) ?>

                                </td>

                                <td>

                                    <?= htmlspecialchars($row['tanggal_kembali']) ?>

                                </td>

                                <td>

                                    <?= htmlspecialchars($row['nama_lengkap']) ?>

                                </td>

                                <td class="text-center">

                                    <?= (int) $row['jumlah_alat'] ?>

                                </td>

                                <td class="text-center">

                                    <span class="badge badge-<?= $badge ?>">

                                        <?= htmlspecialchars($row['status']) ?>

                                    </span>

                                </td>

                                <td class="text-center">

                                    <a href="../peminjaman/detail.php?id=<?= $row['id_peminjaman'] ?>"
                                        class="btn btn-info btn-sm">

                                        <i class="fas fa-eye"></i>

                                        Detail

                                    </a>

                                </td>

                            </tr>

                            <?php endwhile; ?>

                            <?php

if ($totalData == 0) :

?>

                            <tr>

                                <td colspan="7" class="text-center text-muted">

                                    Tidak ada data laporan.

                                </td>

                            </tr>

                            <?php endif; ?>

                        </tbody>

                    </table>

                </div>

                <div class="card-footer">

                    <div class="row">

                        <div class="col-md-6">

                            <strong>

                                Total Data :

                                <?= $totalData ?>

                                Transaksi

                            </strong>

                        </div>

                        <div class="col-md-6 text-right">

                            <a href="cetak.php?tanggal_awal=<?= urlencode($tanggalAwal) ?>&tanggal_akhir=<?= urlencode($tanggalAkhir) ?>&status=<?= urlencode($status) ?>"
                                class="btn btn-success" target="_blank">

                                <i class="fas fa-print"></i>

                                Cetak

                            </a>

                            <a href="export_excel.php?tanggal_awal=<?= urlencode($tanggalAwal) ?>&tanggal_akhir=<?= urlencode($tanggalAkhir) ?>&status=<?= urlencode($status) ?>"
                                class="btn btn-success">

                                <i class="fas fa-file-excel"></i>

                                Export Excel

                            </a>

                            <a href="export_pdf.php?tanggal_awal=<?= urlencode($tanggalAwal) ?>&tanggal_akhir=<?= urlencode($tanggalAkhir) ?>&status=<?= urlencode($status) ?>"
                                class="btn btn-danger" target="_blank">

                                <i class="fas fa-file-pdf"></i>

                                Export PDF

                            </a>

                        </div>

                    </div>

                </div>

            </div>
    </section>

</div>

<?php

include '../../layouts/footer.php';

/*
|--------------------------------------------------------------------------
| Membebaskan Result
|--------------------------------------------------------------------------
*/

if (isset($result) && $result instanceof mysqli_result) {
    $result->free();
}

/*
|--------------------------------------------------------------------------
| Menutup Statement
|--------------------------------------------------------------------------
*/

if (isset($stmt)) {
    $stmt->close();
}

/*
|--------------------------------------------------------------------------
| Menutup Koneksi Database
|--------------------------------------------------------------------------
*/

$conn->close();

?>

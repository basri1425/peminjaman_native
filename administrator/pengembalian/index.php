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
| Pencarian
|--------------------------------------------------------------------------
*/

$keyword = '';

if (isset($_GET['keyword'])) {
    $keyword = trim($_GET['keyword']);
}

/*
|--------------------------------------------------------------------------
| Pagination
|--------------------------------------------------------------------------
*/

$batasData = 10;

$halaman = isset($_GET['page']) ? (int) $_GET['page'] : 1;

if ($halaman < 1) {
    $halaman = 1;
}

$mulai = ($halaman - 1) * $batasData;

/*
|--------------------------------------------------------------------------
| Menghitung Total Data
|--------------------------------------------------------------------------
*/

$sqlTotal = "
SELECT COUNT(*) AS total

FROM pengembalian pg

INNER JOIN peminjaman p
ON pg.id_peminjaman = p.id_peminjaman

INNER JOIN users u
ON p.id_user = u.id_user

WHERE

u.nama_lengkap LIKE ?

OR

p.id_peminjaman LIKE ?

OR

pg.id_pengembalian LIKE ?
";

$stmt = $conn->prepare($sqlTotal);

$cari = '%' . $keyword . '%';

$stmt->bind_param(
    'sss',

    $cari,
    $cari,
    $cari,
);

$stmt->execute();

$totalData = $stmt->get_result()->fetch_assoc()['total'];

$stmt->close();

$totalHalaman = ceil($totalData / $batasData);

/*
|--------------------------------------------------------------------------
| Mengambil Data Pengembalian
|--------------------------------------------------------------------------
*/

$sql = "

SELECT

    pg.id_pengembalian,

    pg.id_peminjaman,

    pg.tanggal_pengembalian,

    pg.keterangan,

    p.status,

    u.nama_lengkap,

    COUNT(dp.id_detail_pengembalian) AS jumlah_alat

FROM pengembalian pg

INNER JOIN peminjaman p
ON pg.id_peminjaman = p.id_peminjaman

INNER JOIN users u
ON p.id_user = u.id_user

LEFT JOIN detail_pengembalian dp
ON pg.id_pengembalian = dp.id_pengembalian

WHERE

u.nama_lengkap LIKE ?

OR

p.id_peminjaman LIKE ?

OR

pg.id_pengembalian LIKE ?

GROUP BY

pg.id_pengembalian

ORDER BY

pg.id_pengembalian DESC

LIMIT ?, ?

";

$stmt = $conn->prepare($sql);

$stmt->bind_param(
    'sssii',

    $cari,
    $cari,
    $cari,
    $mulai,
    $batasData,
);

$stmt->execute();

$result = $stmt->get_result();

?>
<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Daftar Pengembalian Alat</title>

    <?php include '../../layouts/header.php'; ?>

</head>

<body>

    <div class="wrapper">

        <?php 
        require_once '../../layouts/navbar.php';
        require_once '../../layouts/sidebar.php'; 
        ?>

        <div class="content-wrapper">

            <!-- Header -->
            <section class="content-header">

                <div class="container-fluid">

                    <div class="row mb-2">

                        <div class="col-sm-6">

                            <h1>
                                <i class="fas fa-undo-alt text-success"></i>
                                Daftar Pengembalian Alat
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
                                    Pengembalian Alat
                                </li>

                            </ol>

                        </div>

                    </div>

                </div>

            </section>

            <!-- Content -->
            <section class="content">

                <div class="container-fluid">

                    <div class="card card-success">

                        <div class="card-header">

                            <h3 class="card-title">

                                <i class="fas fa-list"></i>

                                Data Pengembalian

                            </h3>

                        </div>

                        <div class="card-body">

                            <div class="row mb-3">

                                <!-- Tombol Tambah -->
                                <div class="col-md-4 mb-2">

                                    <a href="tambah.php" class="btn btn-success">

                                        <i class="fas fa-plus-circle"></i>

                                        Tambah Pengembalian

                                    </a>

                                </div>

                                <!-- Form Pencarian -->
                                <div class="col-md-8">

                                    <form method="GET">

                                        <div class="input-group">

                                            <input type="text" name="keyword" class="form-control"
                                                placeholder="Cari nomor pengembalian, nomor peminjaman atau nama peminjam..."
                                                value="<?= htmlspecialchars($keyword) ?>">

                                            <button class="btn btn-primary" type="submit">

                                                <i class="fas fa-search"></i>

                                                Cari

                                            </button>

                                            <?php if (!empty($keyword)) : ?>

                                            <a href="index.php" class="btn btn-secondary">

                                                <i class="fas fa-sync"></i>

                                                Reset

                                            </a>

                                            <?php endif; ?>

                                        </div>

                                    </form>

                                </div>

                            </div>

                            <!-- Tabel Data -->
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped table-hover">

                                    <thead class="table-success">

                                        <tr class="text-center">

                                            <th width="5%">No</th>

                                            <th>No. Pengembalian</th>

                                            <th>No. Peminjaman</th>

                                            <th>Peminjam</th>

                                            <th>Tanggal Pengembalian</th>

                                            <th>Jumlah Alat</th>

                                            <th>Status</th>

                                            <th width="18%">Aksi</th>

                                        </tr>

                                    </thead>

                                    <tbody>

                                        <?php if ($result->num_rows > 0) : ?>

                                        <?php

            $no = $mulai + 1;

            while ($row = $result->fetch_assoc()) :

                $nomorPengembalian = "KMB-" . str_pad(
                    $row['id_pengembalian'],
                    5,
                    "0",
                    STR_PAD_LEFT
                );

                $nomorPeminjaman = "PJM-" . str_pad(
                    $row['id_peminjaman'],
                    5,
                    "0",
                    STR_PAD_LEFT
                );

                switch ($row['status']) {

                    case 'Menunggu':
                        $badge = "warning";
                        break;

                    case 'Disetujui':
                        $badge = "info";
                        break;

                    case 'Dipinjam':
                        $badge = "primary";
                        break;

                    case 'Selesai':
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
                                                <?= htmlspecialchars($nomorPengembalian) ?>
                                            </td>

                                            <td>
                                                <?= htmlspecialchars($nomorPeminjaman) ?>
                                            </td>

                                            <td>
                                                <?= htmlspecialchars($row['nama_lengkap']) ?>
                                            </td>

                                            <td class="text-center">
                                                <?= date('d-m-Y', strtotime($row['tanggal_pengembalian'])) ?>
                                            </td>

                                            <td class="text-center">
                                                <?= $row['jumlah_alat'] ?>
                                            </td>

                                            <td class="text-center">

                                                <span class="badge bg-<?= $badge ?>">

                                                    <?= htmlspecialchars($row['status']) ?>

                                                </span>

                                            </td>

                                            <td class="text-center">

                                                <a href="detail.php?id=<?= $row['id_pengembalian'] ?>"
                                                    class="btn btn-info btn-sm">

                                                    <i class="fas fa-eye"></i>

                                                    Detail

                                                </a>

                                                <a href="edit.php?id=<?= $row['id_pengembalian'] ?>"
                                                    class="btn btn-warning btn-sm">

                                                    <i class="fas fa-edit"></i>

                                                    Edit

                                                </a>

                                                <a href="hapus.php?id=<?= $row['id_pengembalian'] ?>"
                                                    class="btn btn-danger btn-sm"
                                                    onclick="return confirm('Yakin ingin menghapus data pengembalian ini?');">

                                                    <i class="fas fa-trash"></i>

                                                    Hapus

                                                </a>

                                            </td>

                                        </tr>

                                        <?php endwhile; ?>

                                        <?php else : ?>

                                        <tr>

                                            <td colspan="8" class="text-center">

                                                <div class="alert alert-warning mb-0">

                                                    <i class="fas fa-exclamation-circle"></i>

                                                    Data pengembalian belum tersedia.

                                                </div>

                                            </td>

                                        </tr>

                                        <?php endif; ?>

                                    </tbody>

                                </table>

                            </div>

                            <div class="mt-3 d-flex justify-content-between align-items-center">

                                <div>

                                    Menampilkan

                                    <strong><?= $result->num_rows ?></strong>

                                    dari

                                    <strong><?= $totalData ?></strong>

                                    data

                                </div>

                                <?php if ($totalHalaman > 1) : ?>

                                <nav>

                                    <ul class="pagination mb-0">

                                        <?php for ($i = 1; $i <= $totalHalaman; $i++) : ?>

                                        <li class="page-item <?= $i == $halaman ? 'active' : '' ?>">

                                            <a class="page-link"
                                                href="?page=<?= $i ?>&keyword=<?= urlencode($keyword) ?>">

                                                <?= $i ?>

                                            </a>

                                        </li>

                                        <?php endfor; ?>

                                    </ul>

                                </nav>

                                <?php endif; ?>

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
| Menutup Statement dan Koneksi Database
|--------------------------------------------------------------------------
*/

if (isset($stmt)) {
    $stmt->close();
}

$conn->close();

?>

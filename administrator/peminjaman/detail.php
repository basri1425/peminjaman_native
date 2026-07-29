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
| Validasi Parameter
|--------------------------------------------------------------------------
*/

if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "
    <script>
        alert('ID transaksi tidak ditemukan.');
        window.location='index.php';
    </script>
    ";
    exit();
}

$idPeminjaman = (int) $_GET['id'];

/*
|--------------------------------------------------------------------------
| Mengambil Data Transaksi
|--------------------------------------------------------------------------
*/

$sql = "
SELECT
    p.id_peminjaman,
    p.tanggal_pinjam,
    p.tanggal_kembali,
    p.status,

    u.id_user,
    u.nama_lengkap,
    u.username,
    u.level
FROM peminjaman p
INNER JOIN users u
ON p.id_user = u.id_user
WHERE p.id_peminjaman = ?
LIMIT 1
";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die('Prepare gagal : ' . $conn->error);
}

$stmt->bind_param('i', $idPeminjaman);
$stmt->execute();
$result = $stmt->get_result();

/*
|--------------------------------------------------------------------------
| Validasi Data
|--------------------------------------------------------------------------
*/

if ($result->num_rows == 0) {
    echo "
    <script>
        alert('Data transaksi tidak ditemukan.');
        window.location='index.php';
    </script>
    ";
    exit();
}

$transaksi = $result->fetch_assoc();
$stmt->close();

/*
|--------------------------------------------------------------------------
| Variabel Informasi Transaksi
|--------------------------------------------------------------------------
*/

$nomorTransaksi = 'PJM-' . str_pad($transaksi['id_peminjaman'], 6, '0', STR_PAD_LEFT);

$tanggalPinjam = $transaksi['tanggal_pinjam'];
$tanggalKembali = $transaksi['tanggal_kembali'];

$namaPeminjam = $transaksi['nama_lengkap'];
$username = $transaksi['username'];
$level = $transaksi['level'];

$status = $transaksi['status'];

?>

<?php

/*
|--------------------------------------------------------------------------
| Mengambil Detail Alat yang Dipinjam
|--------------------------------------------------------------------------
*/

$sqlDetail = "
SELECT
    dp.id_detail,
    dp.id_alat,
    dp.jumlah,

    a.nama_alat,
    a.kondisi,
    a.lokasi,
    a.foto,

    k.nama_kategori
FROM detail_peminjaman dp
INNER JOIN alat a
ON dp.id_alat = a.id_alat
INNER JOIN kategori k
ON a.id_kategori = k.id_kategori
WHERE dp.id_peminjaman = ?
ORDER BY
k.nama_kategori ASC,
a.nama_alat ASC
";

$stmtDetail = $conn->prepare($sqlDetail);

if (!$stmtDetail) {
    die('Prepare gagal : ' . $conn->error);
}

$stmtDetail->bind_param('i', $idPeminjaman);
$stmtDetail->execute();
$resultDetail = $stmtDetail->get_result();

/*
|--------------------------------------------------------------------------
| Menyimpan Data Detail
|--------------------------------------------------------------------------
*/

$daftarAlat = [];
$totalJenis = 0;
$totalUnit = 0;

while ($row = $resultDetail->fetch_assoc()) {
    $daftarAlat[] = $row;
    $totalJenis++;
    $totalUnit += (int) $row['jumlah'];
}

$stmtDetail->close();

/*
|--------------------------------------------------------------------------
| Badge Status
|--------------------------------------------------------------------------
*/

$statusBadge = '';

switch ($status) {
    case 'Menunggu':
        $statusBadge = '
            <span class="badge bg-warning text-dark">
                Menunggu
            </span>
        ';

        break;

    case 'Disetujui':
        $statusBadge = '
            <span class="badge bg-info">
                Disetujui
            </span>
        ';

        break;

    case 'Dipinjam':
        $statusBadge = '
            <span class="badge bg-primary">
                Dipinjam
            </span>
        ';

        break;

    case 'Selesai':
        $statusBadge = '
            <span class="badge bg-success">
                Selesai
            </span>
        ';

        break;

    case 'Ditolak':
        $statusBadge = '
            <span class="badge bg-danger">
                Ditolak
            </span>
        ';

        break;

    default:
        $statusBadge = '
            <span class="badge bg-secondary">
                Tidak Diketahui
            </span>
        ';
}
?>

<?php include '../../layouts/header.php'; ?>

<div class="container-fluid">
    <!-- Judul Halaman -->
    <div class="row mb-4">
        <div class="col-md-12">
            <h3 class="fw-bold">
                Detail Transaksi Peminjaman
            </h3>
            <hr>
        </div>
    </div>

    <!-- Informasi Transaksi -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-primary text-white">
            <strong>Informasi Transaksi</strong>
        </div>

        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr>
                            <th width="180">Nomor Transaksi</th>
                            <td>: <?= htmlspecialchars($nomorTransaksi) ?></td>
                        </tr>

                        <tr>
                            <th>Nama Peminjam</th>
                            <td>: <?= htmlspecialchars($namaPeminjam) ?></td>
                        </tr>

                        <tr>
                            <th>Username</th>
                            <td>: <?= htmlspecialchars($username) ?></td>
                        </tr>

                    </table>

                </div>

                <div class="col-md-6">

                    <table class="table table-borderless">

                        <tr>
                            <th width="180">Tanggal Pinjam</th>
                            <td>: <?= date('d-m-Y', strtotime($tanggalPinjam)) ?></td>
                        </tr>

                        <tr>
                            <th>Tanggal Kembali</th>
                            <td>: <?= date('d-m-Y', strtotime($tanggalKembali)) ?></td>
                        </tr>

                        <tr>
                            <th>Status</th>
                            <td>: <?= $statusBadge ?></td>
                        </tr>

                    </table>

                </div>

            </div>

        </div>

    </div>

    <!-- Ringkasan -->
    <div class="row mb-4">

        <div class="col-md-3">

            <div class="card border-primary">

                <div class="card-body text-center">

                    <h6>Total Jenis</h6>

                    <h3><?= $totalJenis ?></h3>

                </div>

            </div>

        </div>

        <div class="col-md-3">

            <div class="card border-success">

                <div class="card-body text-center">

                    <h6>Total Unit</h6>

                    <h3><?= $totalUnit ?></h3>

                </div>

            </div>

        </div>

        <div class="col-md-3">

            <div class="card border-warning">

                <div class="card-body text-center">

                    <h6>Status</h6>

                    <?= $statusBadge ?>

                </div>

            </div>

        </div>

        <div class="col-md-3">

            <div class="card border-info">

                <div class="card-body text-center">

                    <h6>Peminjam</h6>

                    <strong><?= htmlspecialchars($namaPeminjam) ?></strong>

                </div>

            </div>

        </div>

    </div>

    <!-- Daftar Alat -->
    <div class="card shadow-sm">

        <div class="card-header bg-secondary text-white">

            <strong>Daftar Alat yang Dipinjam</strong>

        </div>

        <div class="card-body">

            <div class="table-responsive">

                <table class="table table-bordered table-hover align-middle">

                    <thead class="table-dark">

                        <tr>

                            <th width="50">No</th>

                            <th width="100">Foto</th>

                            <th>Nama Alat</th>

                            <th>Kategori</th>

                            <th>Kondisi</th>

                            <th>Lokasi</th>

                            <th width="90">Jumlah</th>

                        </tr>

                    </thead>

                    <tbody>

                        <?php

                        $no = 1;

                        foreach ($daftarAlat as $alat):

                        ?>

                        <tr>

                            <td class="text-center">

                                <?= $no++ ?>

                            </td>

                            <td class="text-center">

                                <?php if (!empty($alat['foto'])) { ?>

                                <img src="../../assets/img/alat/<?= htmlspecialchars($alat['foto']) ?>"
                                    class="img-thumbnail" width="70">

                                <?php } else { ?>

                                <img src="../../assets/img/no-image.png" class="img-thumbnail" width="70">

                                <?php } ?>

                            </td>

                            <td>

                                <strong>

                                    <?= htmlspecialchars($alat['nama_alat']) ?>

                                </strong>

                            </td>

                            <td>

                                <?= htmlspecialchars($alat['nama_kategori']) ?>

                            </td>

                            <td>

                                <?= htmlspecialchars($alat['kondisi']) ?>

                            </td>

                            <td>

                                <?= htmlspecialchars($alat['lokasi']) ?>

                            </td>

                            <td class="text-center">

                                <?= (int) $alat['jumlah'] ?>

                            </td>

                        </tr>

                        <?php endforeach; ?>

                    </tbody>

                    <tfoot>

                        <tr class="table-light">

                            <th colspan="6" class="text-end">

                                Total Unit

                            </th>

                            <th class="text-center">

                                <?= $totalUnit ?>

                            </th>

                        </tr>

                    </tfoot>

                </table>

            </div>

        </div>

    </div>

    <!-- Tombol Aksi -->
    <div class="mt-4 mb-5">

        <a href="index.php" class="btn btn-secondary">

            ← Kembali

        </a>

        <a href="edit.php?id=<?= $idPeminjaman ?>" class="btn btn-warning">

            ✏ Edit

        </a>

        <button type="button" class="btn btn-success" onclick="window.print();">

            🖨 Cetak

        </button>


    </div>

</div>

<?php include '../../layouts/footer.php'; ?>
<?php

/*
|--------------------------------------------------------------------------
| Menutup Koneksi Database
|--------------------------------------------------------------------------
*/

$conn->close();

?>

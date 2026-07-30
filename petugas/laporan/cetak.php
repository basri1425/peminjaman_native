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
| Mengambil Parameter Filter
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
| Menyusun WHERE Dinamis
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

        p.tanggal_pinjam ASC

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

/*
|--------------------------------------------------------------------------
| Menentukan Periode Laporan
|--------------------------------------------------------------------------
*/

if (!empty($tanggalAwal) && !empty($tanggalAkhir)) {
    $periode = date('d-m-Y', strtotime($tanggalAwal)) . ' s.d. ' . date('d-m-Y', strtotime($tanggalAkhir));
} else {
    $periode = 'Seluruh Periode';
}

?>
<!DOCTYPE html>

<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>

        Cetak Laporan Peminjaman Alat

    </title>

    <style>
        body {

            font-family: Arial, Helvetica, sans-serif;

            font-size: 12px;

            color: #000;

            margin: 30px;

        }

        h2,
        h3,
        p {

            margin: 0;

        }

        table {

            width: 100%;

            border-collapse: collapse;

            margin-top: 15px;

        }

        table th {

            border: 1px solid #000;

            padding: 8px;

            text-align: center;

            background: #f2f2f2;

        }

        table td {

            border: 1px solid #000;

            padding: 6px;

            vertical-align: top;

        }

        .text-center {

            text-align: center;

        }

        .text-right {

            text-align: right;

        }

        .header {

            text-align: center;

            margin-bottom: 15px;

        }

        .header img {

            width: 80px;

            float: left;

        }

        .header h2 {

            font-size: 22px;

        }

        .header h3 {

            font-size: 18px;

            margin-top: 5px;

        }

        .header p {

            margin-top: 3px;

        }

        hr {

            border: 1px solid #000;

            margin-top: 10px;

            margin-bottom: 20px;

        }

        .info {

            margin-bottom: 20px;

        }

        .info table {

            width: 60%;

            margin-top: 0;

            border: none;

        }

        .info td {

            border: none;

            padding: 3px 0;

        }

        @media print {

            body {

                margin: 15px;

            }

        }
    </style>

</head>

<body>

    <div class="header">

        <h2>

            SMK NEGERI TEKNOLOGI NUSANTARA

        </h2>

        <h3>

            LAPORAN PEMINJAMAN ALAT

        </h3>

        <p>

            Jl. Pendidikan No. 123

            Kota Nusantara

        </p>

    </div>

    <hr>

    <div class="info">

        <table>

            <tr>

                <td width="120">

                    Periode

                </td>

                <td width="20">

                    :

                </td>

                <td>

                    <?= htmlspecialchars($periode) ?>

                </td>

            </tr>

            <tr>

                <td>

                    Status

                </td>

                <td>

                    :

                </td>

                <td>

                    <?= htmlspecialchars(empty($status) ? 'Semua Status' : $status) ?>

                </td>

            </tr>

            <tr>

                <td>

                    Tanggal Cetak

                </td>

                <td>

                    :

                </td>

                <td>

                    <?= date('d-m-Y H:i') ?>

                </td>

            </tr>

        </table>

    </div>

    <table>

        <thead>

            <tr>

                <th width="50">

                    No

                </th>

                <th width="120">

                    Tanggal Pinjam

                </th>

                <th width="120">

                    Tanggal Kembali

                </th>

                <th>

                    Nama Peminjam

                </th>

                <th width="100">

                    Jumlah Alat

                </th>

                <th width="120">

                    Status

                </th>

            </tr>

        </thead>

        <tbody>
            <?php

$no = 1;

$totalAlat = 0;

while ($row = $result->fetch_assoc()) :

    $totalAlat += (int) $row['jumlah_alat'];

?>

            <tr>

                <td class="text-center">

                    <?= $no++ ?>

                </td>

                <td>

                    <?= htmlspecialchars(date('d-m-Y', strtotime($row['tanggal_pinjam']))) ?>

                </td>

                <td>

                    <?= htmlspecialchars(date('d-m-Y', strtotime($row['tanggal_kembali']))) ?>

                </td>

                <td>

                    <?= htmlspecialchars($row['nama_lengkap']) ?>

                </td>

                <td class="text-center">

                    <?= (int) $row['jumlah_alat'] ?>

                </td>

                <td class="text-center">

                    <?= htmlspecialchars($row['status']) ?>

                </td>

            </tr>

            <?php endwhile; ?>

            <?php if ($totalData == 0) : ?>

            <tr>

                <td colspan="6" class="text-center">

                    Tidak ada data laporan.

                </td>

            </tr>

            <?php endif; ?>

        </tbody>

        <tfoot>

            <tr>

                <th colspan="4" class="text-right">

                    Total Alat

                </th>

                <th class="text-center">

                    <?= $totalAlat ?>

                </th>

                <th></th>

            </tr>

        </tfoot>

    </table>

    <br><br>

    <table style="width:100%; border:none;">

        <tr>

            <td style="border:none; width:60%;">

                <strong>

                    Total Transaksi :

                    <?= $totalData ?>

                </strong>

            </td>

            <td style="border:none; text-align:center;">

                <?= date('d F Y') ?>

                <br><br>

                Petugas,

                <br><br><br><br><br>

                <strong>

                    <?= htmlspecialchars($_SESSION['nama_lengkap']) ?>

                </strong>

            </td>

        </tr>

    </table>
    <script>
        window.onload = function() {

            window.print();

        };
    </script>

</body>

</html>

<?php

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

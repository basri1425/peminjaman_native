<?php

require "../../config/session.php";
require "../../config/database.php";

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

if ($_SESSION['level'] != "Petugas") {

    header("Location: ../../unauthorized.php");

    exit();

}

/*
|--------------------------------------------------------------------------
| Header Export Excel
|--------------------------------------------------------------------------
*/

$namaFile =

    "Laporan_Peminjaman_" .

    date("Y-m-d_H-i-s") .

    ".xls";

header("Content-Type: application/vnd.ms-excel");

header("Content-Disposition: attachment; filename=\"$namaFile\"");

header("Pragma: no-cache");

header("Expires: 0");

/*
|--------------------------------------------------------------------------
| Mengambil Parameter Filter
|--------------------------------------------------------------------------
*/

$tanggalAwal = trim($_GET['tanggal_awal'] ?? "");

$tanggalAkhir = trim($_GET['tanggal_akhir'] ?? "");

$status = trim($_GET['status'] ?? "");

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

$types = "";

/*
|--------------------------------------------------------------------------
| Filter Tanggal
|--------------------------------------------------------------------------
*/

if (

    !empty($tanggalAwal)

    &&

    !empty($tanggalAkhir)

) {

    $where[] = "

        DATE(p.tanggal_pinjam)

        BETWEEN ?

        AND ?

    ";

    $types .= "ss";

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

    $types .= "s";

    $params[] = $status;

}

/*
|--------------------------------------------------------------------------
| Menambahkan WHERE
|--------------------------------------------------------------------------
*/

if (!empty($where)) {

    $sql .= "

        WHERE

        " . implode(" AND ", $where);

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

    die("Query gagal dipersiapkan.");

}

if (!empty($params)) {

    $stmt->bind_param(

        $types,

        ...$params

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

if (

    !empty($tanggalAwal)

    &&

    !empty($tanggalAkhir)

) {

    $periode =

        date("d-m-Y", strtotime($tanggalAwal))

        .

        " s.d. "

        .

        date("d-m-Y", strtotime($tanggalAkhir));

} else {

    $periode = "Seluruh Periode";

}
?>
<!DOCTYPE html>

<html lang="id">

<head>

    <meta charset="UTF-8">

    <title>

        Laporan Peminjaman Alat

    </title>

</head>

<body>

<table border="0">

    <tr>

        <td colspan="6" align="center">

            <strong style="font-size:18px;">

                SMK NEGERI TEKNOLOGI NUSANTARA

            </strong>

        </td>

    </tr>

    <tr>

        <td colspan="6" align="center">

            <strong style="font-size:16px;">

                LAPORAN PEMINJAMAN ALAT

            </strong>

        </td>

    </tr>

    <tr>

        <td colspan="6" align="center">

            Jl. Pendidikan No. 123 Kota Nusantara

        </td>

    </tr>

</table>

<br>

<table border="0">

    <tr>

        <td width="150">

            Periode

        </td>

        <td width="10">

            :

        </td>

        <td>

            <?= htmlspecialchars($periode); ?>

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

        Tanggal Export

    </td>

    <td>

        :

    </td>

    <td>

        <?= date('d-m-Y H:i:s') ?>

    </td>

</tr>

</table>

<br>

<table border="1" cellspacing="0" cellpadding="5">

    <thead>

        <tr style="background-color:#D9EAD3;">

            <th width="50">

                No

            </th>

            <th width="120">

                Tanggal Pinjam

            </th>

            <th width="120">

                Tanggal Kembali

            </th>

            <th width="250">

                Nama Peminjam

            </th>

            <th width="120">

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

    $jumlahAlat = (int) $row['jumlah_alat'];

    $totalAlat += $jumlahAlat;

?>

        <tr>

            <td align="center">

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

            <td align="center">

                <?= $jumlahAlat ?>

            </td>

            <td align="center">

                <?= htmlspecialchars($row['status']) ?>

            </td>

        </tr>

        <?php endwhile; ?>

        <?php if ($totalData == 0) : ?>

        <tr>

            <td colspan="6" align="center">

                Tidak ada data laporan.

            </td>

        </tr>

        <?php endif; ?>

    </tbody>

    <tfoot>

        <tr style="background-color:#F2F2F2;">

            <th colspan="4" align="right">

                Total Alat

            </th>

            <th align="center">

                <?= $totalAlat ?>

            </th>

            <th></th>

        </tr>

    </tfoot>

</table>

<br><br>

<table border="0">

    <tr>

        <td width="200">

            <strong>

                Total Transaksi

            </strong>

        </td>

        <td width="20">

            :

        </td>

        <td>

            <strong>

                <?= $totalData ?>

            </strong>

        </td>

    </tr>

    <tr>

        <td>

            <strong>

                Total Alat Dipinjam

            </strong>

        </td>

        <td>

            :

        </td>

        <td>

            <strong>

                <?= $totalAlat ?>

            </strong>

        </td>

    </tr>

</table>

<br><br>

<table border="0" width="100%">

    <tr>

        <td width="60%">

        </td>

        <td align="center">

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

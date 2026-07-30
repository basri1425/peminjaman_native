<?php

require '../../config/session.php';
require '../../config/database.php';
require '../../config/log.php';

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
| Validasi Parameter
|--------------------------------------------------------------------------
*/

if (!isset($_GET['id'])) {
    header('Location: index.php');

    exit();
}

$id = (int) $_GET['id'];

if ($id <= 0) {
    header('Location: index.php');

    exit();
}
/*
|--------------------------------------------------------------------------
| Mengambil Data Peminjaman
|--------------------------------------------------------------------------
*/

$sql = "

    SELECT

        id_peminjaman,
        status

    FROM peminjaman

    WHERE

        id_peminjaman = ?

    LIMIT 1

";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    $conn->close();

    header('Location: index.php');

    exit();
}

$stmt->bind_param('i', $id);

$stmt->execute();

$result = $stmt->get_result();

/*
|--------------------------------------------------------------------------
| Validasi Data
|--------------------------------------------------------------------------
*/

if ($result->num_rows == 0) {
    $result->free();

    $stmt->close();

    $conn->close();

    header('Location: index.php');

    exit();
}

$data = $result->fetch_assoc();

/*
|--------------------------------------------------------------------------
| Validasi Status
|--------------------------------------------------------------------------
*/

if ($data['status'] != 'Disetujui') {
    $result->free();

    $stmt->close();

    $conn->close();

    header('Location: detail.php?id=' . $id);

    exit();
}

/*
|--------------------------------------------------------------------------
| Mengambil Detail Alat
|--------------------------------------------------------------------------
*/

$sqlDetail = "

    SELECT

        dp.id_alat,
        dp.jumlah,
        a.nama_alat,
        a.stok

    FROM detail_peminjaman dp

    INNER JOIN alat a

        ON dp.id_alat = a.id_alat

    WHERE

        dp.id_peminjaman = ?

";

$stmtDetail = $conn->prepare($sqlDetail);

if (!$stmtDetail) {
    $result->free();

    $stmt->close();

    $conn->close();

    header('Location: detail.php?id=' . $id);

    exit();
}

$stmtDetail->bind_param('i', $id);

$stmtDetail->execute();

$resultDetail = $stmtDetail->get_result();

/*
|--------------------------------------------------------------------------
| Validasi Detail Alat
|--------------------------------------------------------------------------
*/

if ($resultDetail->num_rows == 0) {
    $resultDetail->free();

    $stmtDetail->close();

    $result->free();

    $stmt->close();

    $conn->close();

    header('Location: detail.php?id=' . $id);

    exit();
}

/*
|--------------------------------------------------------------------------
| Validasi Stok Semua Alat
|--------------------------------------------------------------------------
*/

while ($alat = $resultDetail->fetch_assoc()) {
    if ($alat['stok'] < $alat['jumlah']) {
        $resultDetail->free();

        $stmtDetail->close();

        $result->free();

        $stmt->close();

        $conn->close();

        header('Location: detail.php?id=' . $id . '&pesan=stok_tidak_cukup');

        exit();
    }
}

/*
|--------------------------------------------------------------------------
| Reset Pointer Result
|--------------------------------------------------------------------------
*/

$resultDetail->data_seek(0);

/*
|--------------------------------------------------------------------------
| Memulai Database Transaction
|--------------------------------------------------------------------------
*/

$conn->begin_transaction();

try {
    /*
    |--------------------------------------------------------------------------
    | Mengurangi Stok Semua Alat
    |--------------------------------------------------------------------------
    */

    $sqlStok = "

        UPDATE alat

        SET

            stok = stok - ?

        WHERE

            id_alat = ?

    ";

    $stmtStok = $conn->prepare($sqlStok);

    if (!$stmtStok) {
        throw new Exception('Gagal mempersiapkan query update stok.');
    }

    while ($alat = $resultDetail->fetch_assoc()) {
        $stmtStok->bind_param(
            'ii',

            $alat['jumlah'],

            $alat['id_alat'],
        );

        if (!$stmtStok->execute()) {
            throw new Exception('Gagal mengurangi stok alat.');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Mengubah Status Menjadi Dipinjam
    |--------------------------------------------------------------------------
    */

    $sqlUpdate = "

        UPDATE peminjaman

        SET

            status = ?,
            updated_at = NOW()

        WHERE

            id_peminjaman = ?

    ";

    $status = 'Dipinjam';

    $stmtUpdate = $conn->prepare($sqlUpdate);

    if (!$stmtUpdate) {
        throw new Exception('Gagal mempersiapkan query update peminjaman.');
    }

    $stmtUpdate->bind_param(
        'si',

        $status,

        $id,
    );

    if (!$stmtUpdate->execute()) {
        throw new Exception('Gagal mengubah status peminjaman.');
    }

    /*
    |--------------------------------------------------------------------------
    | Menyimpan Log Aktivitas
    |--------------------------------------------------------------------------
    */

    if (
        !tambahLog(
            $conn,

            "Menyerahkan alat untuk peminjaman ID #{$id}",
        )
    ) {
        throw new Exception('Gagal menyimpan log aktivitas.');
    }

    /*
    |--------------------------------------------------------------------------
    | Commit Transaction
    |--------------------------------------------------------------------------
    */

    $conn->commit();

    /*
    |--------------------------------------------------------------------------
    | Tutup Statement
    |--------------------------------------------------------------------------
    */

    $stmtStok->close();

    $stmtUpdate->close();

    /*
    |--------------------------------------------------------------------------
    | Redirect
    |--------------------------------------------------------------------------
    */

    header('Location: index.php?pesan=serahkan_berhasil');

    exit();
} catch (Exception $e) {
    /*
    |--------------------------------------------------------------------------
    | Rollback Transaction
    |--------------------------------------------------------------------------
    */

    $conn->rollback();

    /*
    |--------------------------------------------------------------------------
    | Tutup Statement
    |--------------------------------------------------------------------------
    */

    if (isset($stmtStok)) {
        $stmtStok->close();
    }

    if (isset($stmtUpdate)) {
        $stmtUpdate->close();
    }

    /*
    |--------------------------------------------------------------------------
    | Redirect Gagal
    |--------------------------------------------------------------------------
    */

    header('Location: detail.php?id=' . $id . '&pesan=serahkan_gagal');

    exit();
}

/*
|--------------------------------------------------------------------------
| Menutup Result
|--------------------------------------------------------------------------
*/

if (isset($result) && $result instanceof mysqli_result) {
    $result->free();
}

if (isset($resultDetail) && $resultDetail instanceof mysqli_result) {
    $resultDetail->free();
}

/*
|--------------------------------------------------------------------------
| Menutup Statement
|--------------------------------------------------------------------------
*/

if (isset($stmt)) {
    $stmt->close();
}

if (isset($stmtDetail)) {
    $stmtDetail->close();
}

if (isset($stmtStok)) {
    $stmtStok->close();
}

if (isset($stmtUpdate)) {
    $stmtUpdate->close();
}

/*
|--------------------------------------------------------------------------
| Menutup Koneksi
|--------------------------------------------------------------------------
*/

$conn->close();

?>

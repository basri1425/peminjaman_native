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
| Validasi Parameter ID
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
| Validasi Data Peminjaman
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
| Validasi Status Peminjaman
|--------------------------------------------------------------------------
*/

if ($data['status'] != 'Menunggu') {
    $result->free();

    $stmt->close();

    $conn->close();

    header('Location: detail.php?id=' . $id);

    exit();
}

/*
|--------------------------------------------------------------------------
| Memulai Database Transaction
|--------------------------------------------------------------------------
*/

$conn->begin_transaction();

try {
    /*
    |--------------------------------------------------------------------------
    | Mengubah Status Peminjaman
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

    $status = 'Ditolak';

    $stmtUpdate = $conn->prepare($sqlUpdate);

    if (!$stmtUpdate) {
        throw new Exception('Gagal mempersiapkan query update.');
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

            "Menolak peminjaman dengan ID #{$id}",
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
    | Tutup Statement Update
    |--------------------------------------------------------------------------
    */

    $stmtUpdate->close();

    /*
    |--------------------------------------------------------------------------
    | Redirect Berhasil
    |--------------------------------------------------------------------------
    */

    header('Location: index.php?pesan=tolak_berhasil');

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
    | Tutup Statement Update
    |--------------------------------------------------------------------------
    */

    if (isset($stmtUpdate)) {
        $stmtUpdate->close();
    }

    /*
    |--------------------------------------------------------------------------
    | Redirect Gagal
    |--------------------------------------------------------------------------
    */

    header('Location: detail.php?id=' . $id . '&pesan=tolak_gagal');

    exit();
}

/*
|--------------------------------------------------------------------------
| Menutup Resource Query
|--------------------------------------------------------------------------
*/

if (isset($result) && $result instanceof mysqli_result) {
    $result->free();
}

/*
|--------------------------------------------------------------------------
| Menutup Prepared Statement
|--------------------------------------------------------------------------
*/

if (isset($stmt)) {
    $stmt->close();
}

if (isset($stmtUpdate)) {
    $stmtUpdate->close();
}

/*
|--------------------------------------------------------------------------
| Menutup Koneksi Database
|--------------------------------------------------------------------------
*/

$conn->close();

?>

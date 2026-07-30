<?php

/*
|--------------------------------------------------------------------------
| Konfigurasi Aplikasi
|--------------------------------------------------------------------------
*/

require '../../config/session.php';
require '../../config/database.php';
require '../../config/log.php';

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
| Validasi Hak Akses
|--------------------------------------------------------------------------
*/

if ($_SESSION['level'] != 'Peminjam') {

    header('Location: ../../unauthorized.php');
    exit();
}

/*
|--------------------------------------------------------------------------
| Mengambil Data
|--------------------------------------------------------------------------
*/

$idUser = (int) $_SESSION['id_user'];

$idPeminjaman = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($idPeminjaman <= 0) {

    $_SESSION['error'] = 'Data peminjaman tidak valid.';

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

    p.id_peminjaman,
    p.status,

    d.id_detail,
    d.id_alat,
    d.jumlah,

    a.nama_alat

FROM peminjaman p

INNER JOIN detail_peminjaman d

    ON p.id_peminjaman = d.id_peminjaman

INNER JOIN alat a

    ON d.id_alat = a.id_alat

WHERE

    p.id_peminjaman = ?

AND

    p.id_user = ?

LIMIT 1

";

$stmt = $conn->prepare($sql);

$stmt->bind_param(

    "ii",

    $idPeminjaman,
    $idUser

);

$stmt->execute();

$result = $stmt->get_result();

/*
|--------------------------------------------------------------------------
| Validasi Data
|--------------------------------------------------------------------------
*/

if ($result->num_rows == 0) {

    $_SESSION['error'] = 'Data peminjaman tidak ditemukan.';

    $stmt->close();
    $result->free();
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

if ($data['status'] != 'Menunggu') {

    $_SESSION['error'] =
        'Pengajuan ini tidak dapat dibatalkan.';

    $stmt->close();
    $result->free();
    $conn->close();

    header('Location: index.php');
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
    | Menghapus Detail Peminjaman
    |--------------------------------------------------------------------------
    */

    $sqlDetail = "

        DELETE FROM detail_peminjaman

        WHERE id_peminjaman = ?

    ";

    $stmtDetail = $conn->prepare($sqlDetail);

    $stmtDetail->bind_param(

        "i",

        $idPeminjaman

    );

    $stmtDetail->execute();

    /*
    |--------------------------------------------------------------------------
    | Menghapus Data Peminjaman
    |--------------------------------------------------------------------------
    */

    $sqlPeminjaman = "

        DELETE FROM peminjaman

        WHERE id_peminjaman = ?

    ";

    $stmtPeminjaman = $conn->prepare($sqlPeminjaman);

    $stmtPeminjaman->bind_param(

        "i",

        $idPeminjaman

    );

    $stmtPeminjaman->execute();

    /*
    |--------------------------------------------------------------------------
    | Menambahkan Log Aktivitas
    |--------------------------------------------------------------------------
    */

    $aktivitas =
        'Membatalkan pengajuan peminjaman alat : '
        . $data['nama_alat']
        . ' sebanyak '
        . $data['jumlah']
        . ' unit.';

    tambahLog($conn, $aktivitas);

    /*
    |--------------------------------------------------------------------------
    | Commit Transaksi
    |--------------------------------------------------------------------------
    */

    $conn->commit();

    $_SESSION['success'] =
        'Pengajuan peminjaman berhasil dibatalkan.';

    header('Location: index.php');

    exit();
} catch (Exception $e) {

    /*
    |--------------------------------------------------------------------------
    | Rollback
    |--------------------------------------------------------------------------
    */

    $conn->rollback();

    error_log($e->getMessage());

    $_SESSION['error'] =
        'Terjadi kesalahan saat membatalkan pengajuan.';

    header('Location: index.php');

    exit();
}

/*
|--------------------------------------------------------------------------
| Membebaskan Resource Query
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

if (isset($stmt) && $stmt instanceof mysqli_stmt) {

    $stmt->close();
}

if (isset($stmtDetail) && $stmtDetail instanceof mysqli_stmt) {

    $stmtDetail->close();
}

if (isset($stmtPeminjaman) && $stmtPeminjaman instanceof mysqli_stmt) {

    $stmtPeminjaman->close();
}

/*
|--------------------------------------------------------------------------
| Menutup Koneksi Database
|--------------------------------------------------------------------------
*/

if (isset($conn) && $conn instanceof mysqli) {

    $conn->close();
}

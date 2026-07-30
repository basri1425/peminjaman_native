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
| Data User Login
|--------------------------------------------------------------------------
*/

$idUser = (int) $_SESSION['id_user'];

/*
|--------------------------------------------------------------------------
| Validasi ID Peminjaman
|--------------------------------------------------------------------------
*/

$idPeminjaman = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($idPeminjaman <= 0) {

    $_SESSION['error'] = 'Data peminjaman tidak valid.';

    header('Location: index.php');
    exit();
}

/*
|--------------------------------------------------------------------------
| Validasi Data Peminjaman
|--------------------------------------------------------------------------
*/

$sql = "

SELECT

    p.id_peminjaman,
    p.id_user,
    p.status

FROM peminjaman p

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

if ($result->num_rows == 0) {

    $_SESSION['error'] =
        'Data peminjaman tidak ditemukan.';

    $result->free();
    $stmt->close();
    $conn->close();

    header('Location: index.php');
    exit();
}

$peminjaman = $result->fetch_assoc();

$result->free();
$stmt->close();

/*
|--------------------------------------------------------------------------
| Validasi Status Peminjaman
|--------------------------------------------------------------------------
*/

if ($peminjaman['status'] != 'Dipinjam') {

    $_SESSION['error'] =
        'Pengembalian hanya dapat diajukan untuk alat yang sedang dipinjam.';

    $conn->close();

    header('Location: index.php');
    exit();
}

/*
|--------------------------------------------------------------------------
| Validasi Pengajuan Pengembalian
|--------------------------------------------------------------------------
*/

$sql = "

SELECT

    id_pengembalian

FROM pengembalian

WHERE

    id_peminjaman = ?

LIMIT 1

";

$stmt = $conn->prepare($sql);

$stmt->bind_param(

    "i",

    $idPeminjaman

);

$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows > 0) {

    $_SESSION['error'] =
        'Pengajuan pengembalian untuk transaksi ini sudah pernah dilakukan.';

    $result->free();
    $stmt->close();
    $conn->close();

    header('Location: index.php');
    exit();
}

$result->free();
$stmt->close();

?>
<?php

/*
|--------------------------------------------------------------------------
| Memulai Database Transaction
|--------------------------------------------------------------------------
*/

$conn->begin_transaction();

try {

    /*
    |--------------------------------------------------------------------------
    | Menyimpan Data Pengembalian
    |--------------------------------------------------------------------------
    */

    $tanggalPengembalian = date('Y-m-d');

    /*
    |--------------------------------------------------------------------------
    | Nilai Awal Pengajuan
    |--------------------------------------------------------------------------
    |
    | Kondisi dan keterangan akan diperbarui oleh petugas
    | setelah melakukan pemeriksaan fisik alat.
    |
    */

    $kondisiKembali = 'Baik';
    $keterangan = null;

    $sql = "

    INSERT INTO pengembalian (

        id_peminjaman,
        tanggal_pengembalian,
        kondisi_kembali,
        keterangan

    )

    VALUES (

        ?,
        ?,
        ?,
        ?

    )";

    $stmtPengembalian = $conn->prepare($sql);

    $stmtPengembalian->bind_param(

        "isss",

        $idPeminjaman,
        $tanggalPengembalian,
        $kondisiKembali,
        $keterangan

    );

    if (!$stmtPengembalian->execute()) {

        throw new Exception(
            'Gagal menyimpan data pengembalian.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Mengambil ID Pengembalian
    |--------------------------------------------------------------------------
    */

    $idPengembalian = $conn->insert_id;

    // $stmtPengembalian->close();

    /*
    |--------------------------------------------------------------------------
    | Mengambil Detail Peminjaman
    |--------------------------------------------------------------------------
    */

    $sql = "

    SELECT

        id_alat,
        jumlah

    FROM detail_peminjaman

    WHERE

        id_peminjaman = ?

    ";

    $stmtDetail = $conn->prepare($sql);

    $stmtDetail->bind_param(

        "i",

        $idPeminjaman

    );

    $stmtDetail->execute();

    $resultDetail = $stmtDetail->get_result();

    if ($resultDetail->num_rows == 0) {

        throw new Exception(
            'Detail peminjaman tidak ditemukan.'
        );
    }

?>
<?php

    /*
    |--------------------------------------------------------------------------
    | Menyalin Detail Peminjaman ke Detail Pengembalian
    |--------------------------------------------------------------------------
    */

    $sql = "

    INSERT INTO detail_pengembalian (

        id_pengembalian,
        id_alat,
        jumlah

    )

    VALUES (

        ?,
        ?,
        ?

    )

    ";

    $stmtInsertDetail = $conn->prepare($sql);

    while ($detail = $resultDetail->fetch_assoc()) {

        $stmtInsertDetail->bind_param(

            "iii",

            $idPengembalian,
            $detail['id_alat'],
            $detail['jumlah']

        );

        if (!$stmtInsertDetail->execute()) {

            throw new Exception(
                'Gagal menyimpan detail pengembalian.'
            );
        }
    }

    $stmtInsertDetail->close();

    $resultDetail->free();

    $stmtDetail->close();

    /*
    |--------------------------------------------------------------------------
    | Mencatat Aktivitas
    |--------------------------------------------------------------------------
    */

    $aktivitas = "Mengajukan pengembalian alat. ID Peminjaman : {$idPeminjaman}";

    if (!tambahLog($conn, $aktivitas)) {

        throw new Exception(
            'Gagal menyimpan log aktivitas.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Commit Transaction
    |--------------------------------------------------------------------------
    */

    $conn->commit();

    $_SESSION['success'] =
        'Pengajuan pengembalian berhasil dikirim dan menunggu verifikasi petugas.';

    header('Location: index.php');

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
    | Menutup Resource yang Masih Aktif
    |--------------------------------------------------------------------------
    */

    if (isset($resultDetail) && $resultDetail instanceof mysqli_result) {

        $resultDetail->free();
    }

    if (isset($stmtDetail) && $stmtDetail instanceof mysqli_stmt) {

        $stmtDetail->close();
    }

    if (isset($stmtInsertDetail) && $stmtInsertDetail instanceof mysqli_stmt) {

        $stmtInsertDetail->close();
    }

    if (isset($stmtPengembalian) && $stmtPengembalian instanceof mysqli_stmt) {

        $stmtPengembalian->close();
    }

    /*
    |--------------------------------------------------------------------------
    | Menampilkan Pesan Error
    |--------------------------------------------------------------------------
    */

    $_SESSION['error'] = $e->getMessage();
}

/*
|--------------------------------------------------------------------------
| Menutup Koneksi Database
|--------------------------------------------------------------------------
*/

if (isset($conn) && $conn instanceof mysqli) {

    $conn->close();
}

/*
|--------------------------------------------------------------------------
| Redirect
|--------------------------------------------------------------------------
*/

header('Location: index.php');
exit();

?>
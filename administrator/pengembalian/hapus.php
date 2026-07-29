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
| Mengambil ID Pengembalian
|--------------------------------------------------------------------------
*/

$idPengembalian = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($idPengembalian <= 0) {
    header('Location: index.php');
    exit();
}

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

        p.status

    FROM pengembalian pg

    INNER JOIN peminjaman p
        ON pg.id_peminjaman = p.id_peminjaman

    WHERE
        pg.id_pengembalian = ?

    LIMIT 1

";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die($conn->error);
}

$stmt->bind_param(
    'i',

    $idPengembalian,
);

$stmt->execute();

$data = $stmt->get_result()->fetch_assoc();

$stmt->close();

/*
|--------------------------------------------------------------------------
| Validasi Data
|--------------------------------------------------------------------------
*/

if (!$data) {
    echo "

    <script>

        alert('Data pengembalian tidak ditemukan.');

        window.location='index.php';

    </script>

    ";

    exit();
}

/*
|--------------------------------------------------------------------------
| Mengambil Detail Pengembalian
|--------------------------------------------------------------------------
*/

$sql = "

    SELECT

        dp.id_detail_pengembalian,
        dp.id_alat,
        dp.jumlah,
        dp.kondisi,

        a.nama_alat,
        a.stok

    FROM detail_pengembalian dp

    INNER JOIN alat a
        ON dp.id_alat = a.id_alat

    WHERE
        dp.id_pengembalian = ?

    ORDER BY
        a.nama_alat ASC

";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die($conn->error);
}

$stmt->bind_param(
    'i',

    $idPengembalian,
);

$stmt->execute();

$resultDetail = $stmt->get_result();
/*
|--------------------------------------------------------------------------
| Memulai Database Transaction
|--------------------------------------------------------------------------
*/

$conn->begin_transaction();

try {
    /*
    |--------------------------------------------------------------------------
    | Mengurangi Kembali Stok Alat
    |--------------------------------------------------------------------------
    */

    $sql = "

        UPDATE alat
        SET
            stok = stok - ?,
            updated_at = NOW()
        WHERE
            id_alat = ?

    ";

    $stmtUpdateStok = $conn->prepare($sql);

    if (!$stmtUpdateStok) {
        throw new Exception($conn->error);
    }

    while ($detail = $resultDetail->fetch_assoc()) {
        /*
        |--------------------------------------------------------------------------
        | Validasi Stok
        |--------------------------------------------------------------------------
        */

        if ($detail['stok'] < $detail['jumlah']) {
            throw new Exception("Stok alat '" . $detail['nama_alat'] . "' tidak mencukupi.");
        }

        $jumlah = (int) $detail['jumlah'];

        $idAlat = (int) $detail['id_alat'];

        $stmtUpdateStok->bind_param(
            'ii',

            $jumlah,

            $idAlat,
        );

        if (!$stmtUpdateStok->execute()) {
            throw new Exception($stmtUpdateStok->error);
        }
    }

    $stmtUpdateStok->close();

    /*
    |--------------------------------------------------------------------------
    | Update Status Peminjaman
    |--------------------------------------------------------------------------
    */

    $sql = "

        UPDATE peminjaman
        SET
            status = 'Dipinjam',
            updated_at = NOW()
        WHERE
            id_peminjaman = ?

    ";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        throw new Exception($conn->error);
    }

    $stmt->bind_param(
        'i',

        $data['id_peminjaman'],
    );

    if (!$stmt->execute()) {
        throw new Exception($stmt->error);
    }

    $stmt->close();
    /*
    |--------------------------------------------------------------------------
    | Menghapus Detail Pengembalian
    |--------------------------------------------------------------------------
    */

    $sql = "

        DELETE FROM detail_pengembalian
        WHERE
            id_pengembalian = ?

    ";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        throw new Exception($conn->error);
    }

    $stmt->bind_param(
        'i',

        $idPengembalian,
    );

    if (!$stmt->execute()) {
        throw new Exception($stmt->error);
    }

    $stmt->close();

    /*
    |--------------------------------------------------------------------------
    | Menghapus Data Pengembalian
    |--------------------------------------------------------------------------
    */

    $sql = "

        DELETE FROM pengembalian
        WHERE
            id_pengembalian = ?

    ";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        throw new Exception($conn->error);
    }

    $stmt->bind_param(
        'i',

        $idPengembalian,
    );

    if (!$stmt->execute()) {
        throw new Exception($stmt->error);
    }

    if ($stmt->affected_rows == 0) {
        throw new Exception('Data pengembalian gagal dihapus.');
    }

    $stmt->close();

    /*
    |--------------------------------------------------------------------------
    | Commit Transaction
    |--------------------------------------------------------------------------
    */

    $conn->commit();

    echo "

    <script>

        alert('Data pengembalian berhasil dihapus.');

        window.location='index.php';

    </script>

    ";

    exit();
} catch (Exception $e) {
    /*
    |--------------------------------------------------------------------------
    | Rollback Transaction
    |--------------------------------------------------------------------------
    */

    $conn->rollback();

    echo "

    <script>

        alert('Gagal menghapus data pengembalian.\n\n" .
        addslashes($e->getMessage()) .
        "');

        history.back();

    </script>

    ";
}

/*
|--------------------------------------------------------------------------
| Menutup Statement
|--------------------------------------------------------------------------
*/

if (isset($stmt) && $stmt instanceof mysqli_stmt) {
    $stmt->close();
}

if (isset($stmtUpdateStok) && $stmtUpdateStok instanceof mysqli_stmt) {
    $stmtUpdateStok->close();
}

/*
|--------------------------------------------------------------------------
| Menutup Koneksi Database
|--------------------------------------------------------------------------
*/

$conn->close();

?>

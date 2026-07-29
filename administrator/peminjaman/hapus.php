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
| Validasi Hak Akses
|--------------------------------------------------------------------------
*/

if ($_SESSION['level'] != 'Administrator') {
    echo "

    <script>

        alert('Anda tidak memiliki hak akses.');

        window.location='../../login.php';

    </script>

    ";

    exit();
}

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

    id_peminjaman,
    status

FROM peminjaman

WHERE id_peminjaman = ?

LIMIT 1
";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die('Prepare gagal : ' . $conn->error);
}

$stmt->bind_param(
    'i',

    $idPeminjaman,
);

$stmt->execute();

$result = $stmt->get_result();

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
| Validasi Status Transaksi
|--------------------------------------------------------------------------
|
| Hanya transaksi dengan status berikut yang boleh dihapus:
| - Menunggu
| - Ditolak
| - Disetujui
|--------------------------------------------------------------------------
*/

$statusDiizinkan = ['Menunggu', 'Ditolak', 'Disetujui'];

if (!in_array($transaksi['status'], $statusDiizinkan)) {
    echo "

    <script>

        alert('Transaksi dengan status " .
        $transaksi['status'] .
        " tidak dapat dihapus.');

        window.location='detail.php?id=" .
        $idPeminjaman .
        "';

    </script>

    ";

    exit();
}

/*
|--------------------------------------------------------------------------
| Mulai Database Transaction
|--------------------------------------------------------------------------
*/

$conn->begin_transaction();

try {
    /*
    |--------------------------------------------------------------------------
    | Mengambil Detail Transaksi
    |--------------------------------------------------------------------------
    */

    $sql = "
    SELECT

        id_alat,
        jumlah

    FROM detail_peminjaman

    WHERE id_peminjaman = ?
    ";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        throw new Exception('Gagal mengambil detail transaksi.');
    }

    $stmt->bind_param(
        'i',

        $idPeminjaman,
    );

    $stmt->execute();

    $result = $stmt->get_result();

    /*
    |--------------------------------------------------------------------------
    | Mengembalikan Stok Alat
    |--------------------------------------------------------------------------
    */

    while ($detail = $result->fetch_assoc()) {
        $sqlRestore = "
        UPDATE alat
        SET

            stok = stok + ?

        WHERE id_alat = ?
        ";

        $restore = $conn->prepare($sqlRestore);

        if (!$restore) {
            throw new Exception('Gagal mempersiapkan update stok.');
        }

        $restore->bind_param(
            'ii',

            $detail['jumlah'],
            $detail['id_alat'],
        );

        if (!$restore->execute()) {
            throw new Exception('Gagal mengembalikan stok alat.');
        }

        if ($restore->affected_rows == 0) {
            throw new Exception('Data alat tidak ditemukan.');
        }

        $restore->close();
    }

    $stmt->close();

    /*
    |--------------------------------------------------------------------------
    | Menghapus Detail Transaksi
    |--------------------------------------------------------------------------
    */

    $sql = "
    DELETE FROM detail_peminjaman
    WHERE id_peminjaman = ?
    ";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        throw new Exception('Gagal mempersiapkan penghapusan detail transaksi.');
    }

    $stmt->bind_param(
        'i',

        $idPeminjaman,
    );

    if (!$stmt->execute()) {
        throw new Exception('Gagal menghapus detail transaksi.');
    }

    $stmt->close();

    /*
    |--------------------------------------------------------------------------
    | Menghapus Header Transaksi
    |--------------------------------------------------------------------------
    */

    $sql = "
    DELETE FROM peminjaman
    WHERE id_peminjaman = ?
    ";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        throw new Exception('Gagal mempersiapkan penghapusan transaksi.');
    }

    $stmt->bind_param(
        'i',

        $idPeminjaman,
    );

    if (!$stmt->execute()) {
        throw new Exception('Gagal menghapus transaksi.');
    }

    if ($stmt->affected_rows == 0) {
        throw new Exception('Data transaksi tidak ditemukan.');
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

        alert('Transaksi berhasil dihapus.');

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

        alert('" .
        $e->getMessage() .
        "');

        history.back();

    </script>

    ";
}

$conn->close();

?>
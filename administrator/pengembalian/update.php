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
| Validasi Request
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header('Location: index.php');
    exit();
}

/*
|--------------------------------------------------------------------------
| Mengambil Data Form
|--------------------------------------------------------------------------
*/

$idPengembalian = isset($_POST['id_pengembalian']) ? (int) $_POST['id_pengembalian'] : 0;

$idPeminjaman = isset($_POST['id_peminjaman']) ? (int) $_POST['id_peminjaman'] : 0;

$tanggalPengembalian = trim($_POST['tanggal_pengembalian'] ?? '');

$keterangan = trim($_POST['keterangan'] ?? '');

$idDetail = $_POST['id_detail_pengembalian'] ?? [];

$idAlat = $_POST['id_alat'] ?? [];

$jumlah = $_POST['jumlah'] ?? [];

$kondisi = $_POST['kondisi'] ?? [];

$keteranganDetail = $_POST['keterangan_detail'] ?? [];

/*
|--------------------------------------------------------------------------
| Validasi Data Utama
|--------------------------------------------------------------------------
*/

if ($idPengembalian <= 0 || $idPeminjaman <= 0 || empty($tanggalPengembalian) || empty($idDetail)) {
    echo "

    <script>

        alert('Data pengembalian tidak lengkap.');

        history.back();

    </script>

    ";

    exit();
}

/*
|--------------------------------------------------------------------------
| Validasi Jumlah Array
|--------------------------------------------------------------------------
*/

$totalDetail = count($idDetail);

if ($totalDetail != count($idAlat) || $totalDetail != count($jumlah) || $totalDetail != count($kondisi) || $totalDetail != count($keteranganDetail)) {
    echo "

    <script>

        alert('Data detail pengembalian tidak valid.');

        history.back();

    </script>

    ";

    exit();
}

/*
|--------------------------------------------------------------------------
| Validasi Isi Detail
|--------------------------------------------------------------------------
*/

for ($i = 0; $i < $totalDetail; $i++) {
    if ((int) $idDetail[$i] <= 0 || (int) $idAlat[$i] <= 0 || (int) $jumlah[$i] <= 0 || empty(trim($kondisi[$i]))) {
        echo "

        <script>

            alert('Terdapat data detail yang tidak valid.');

            history.back();

        </script>

        ";

        exit();
    }
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
    | Update Data Pengembalian
    |--------------------------------------------------------------------------
    */

    $sql = "

        UPDATE pengembalian
        SET
            tanggal_pengembalian = ?,
            keterangan = ?,
            updated_at = NOW()
        WHERE
            id_pengembalian = ?

    ";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        throw new Exception($conn->error);
    }

    $stmt->bind_param(
        'ssi',

        $tanggalPengembalian,

        $keterangan,

        $idPengembalian,
    );

    if (!$stmt->execute()) {
        throw new Exception($stmt->error);
    }

    /*
    |--------------------------------------------------------------------------
    | Validasi Data Berhasil Diupdate
    |--------------------------------------------------------------------------
    */

    if ($stmt->affected_rows < 0) {
        throw new Exception('Gagal memperbarui data pengembalian.');
    }

    $stmt->close();

    /*
    |--------------------------------------------------------------------------
    | Menyiapkan Update Detail Pengembalian
    |--------------------------------------------------------------------------
    */

    $sql = "

        UPDATE detail_pengembalian
        SET
            kondisi = ?,
            keterangan = ?
        WHERE
            id_detail_pengembalian = ?

    ";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        throw new Exception($conn->error);
    }
    /*
    |--------------------------------------------------------------------------
    | Update Detail Pengembalian
    |--------------------------------------------------------------------------
    */

    for ($i = 0; $i < $totalDetail; $i++) {
        $idDetailItem = (int) $idDetail[$i];

        $idAlatItem = (int) $idAlat[$i];

        $kondisiItem = trim($kondisi[$i]);

        $keteranganItem = trim($keteranganDetail[$i]);

        /*
        |--------------------------------------------------------------------------
        | Update Detail Pengembalian
        |--------------------------------------------------------------------------
        */

        $stmt->bind_param(
            'ssi',

            $kondisiItem,

            $keteranganItem,

            $idDetailItem,
        );

        if (!$stmt->execute()) {
            throw new Exception($stmt->error);
        }

        /*
        |--------------------------------------------------------------------------
        | Update Kondisi Alat
        |--------------------------------------------------------------------------
        */

        $sqlAlat = "

            UPDATE alat
            SET
                kondisi = ?,
                updated_at = NOW()
            WHERE
                id_alat = ?

        ";

        $stmtAlat = $conn->prepare($sqlAlat);

        if (!$stmtAlat) {
            throw new Exception($conn->error);
        }

        $stmtAlat->bind_param(
            'si',

            $kondisiItem,

            $idAlatItem,
        );

        if (!$stmtAlat->execute()) {
            throw new Exception($stmtAlat->error);
        }

        $stmtAlat->close();
    }

    /*
    |--------------------------------------------------------------------------
    | Menutup Statement Detail
    |--------------------------------------------------------------------------
    */

    $stmt->close();

    /*
    |--------------------------------------------------------------------------
    | Commit Transaction
    |--------------------------------------------------------------------------
    */

    $conn->commit();

    echo "

    <script>

        alert('Data pengembalian berhasil diperbarui.');

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

        alert('Gagal memperbarui data pengembalian.\n\n" .
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

if (isset($stmtAlat) && $stmtAlat instanceof mysqli_stmt) {
    $stmtAlat->close();
}

/*
|--------------------------------------------------------------------------
| Menutup Koneksi Database
|--------------------------------------------------------------------------
*/

$conn->close();

?>

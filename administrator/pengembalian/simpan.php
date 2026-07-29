<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
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

$idPeminjaman = isset($_POST['id_peminjaman']) ? (int) $_POST['id_peminjaman'] : 0;

$tanggalPengembalian = trim($_POST['tanggal_pengembalian'] ?? '');

$keterangan = trim($_POST['keterangan'] ?? '');

$idAlat = $_POST['id_alat'] ?? [];

$jumlah = $_POST['jumlah'] ?? [];

$kondisi = $_POST['kondisi'] ?? [];

$keteranganDetail = $_POST['keterangan_detail'] ?? [];

/*
|--------------------------------------------------------------------------
| Validasi Data
|--------------------------------------------------------------------------
*/

if ($idPeminjaman <= 0 || empty($tanggalPengembalian) || empty($idAlat)) {
    echo "

    <script>

        alert('Data pengembalian tidak lengkap.');

        window.location='tambah.php';

    </script>

    ";

    exit();
}

/*
|--------------------------------------------------------------------------
| Validasi Jumlah Array
|--------------------------------------------------------------------------
*/

if (count($idAlat) != count($jumlah) || count($idAlat) != count($kondisi) || count($idAlat) != count($keteranganDetail)) {
    echo "

    <script>

        alert('Data detail pengembalian tidak valid.');

        window.location='tambah.php';

    </script>

    ";

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
    | Menyimpan Data Pengembalian
    |--------------------------------------------------------------------------
    */

    $sql = "

        INSERT INTO pengembalian
        (
            id_peminjaman,
            tanggal_pengembalian,
            keterangan,
            created_at,
            updated_at
        )
        VALUES
        (
            ?, ?, ?, NOW(), NOW()
        )

    ";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        throw new Exception($conn->error);
    }

    $stmt->bind_param(
        'iss',

        $idPeminjaman,

        $tanggalPengembalian,

        $keterangan,
    );

    if (!$stmt->execute()) {
        throw new Exception($stmt->error);
    }

    /*
    |--------------------------------------------------------------------------
    | Mengambil ID Pengembalian
    |--------------------------------------------------------------------------
    */

    $idPengembalian = $conn->insert_id;

    $stmt->close();

    /*
    |--------------------------------------------------------------------------
    | Menyimpan Detail Pengembalian
    |--------------------------------------------------------------------------
    */

    $sql = "

        INSERT INTO detail_pengembalian
        (
            id_pengembalian,
            id_alat,
            jumlah,
            kondisi,
            keterangan
        )
        VALUES
        (
            ?, ?, ?, ?, ?
        )

    ";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        throw new Exception($conn->error);
    }

    /*
    |--------------------------------------------------------------------------
    | Loop Seluruh Alat
    |--------------------------------------------------------------------------
    */

    for ($i = 0; $i < count($idAlat); $i++) {
        $idAlatItem = (int) $idAlat[$i];

        $jumlahItem = (int) $jumlah[$i];

        $kondisiItem = trim($kondisi[$i]);

        $keteranganItem = trim($keteranganDetail[$i]);

        $stmt->bind_param(
            'iiiss',

            $idPengembalian,

            $idAlatItem,

            $jumlahItem,

            $kondisiItem,

            $keteranganItem,
        );

        if (!$stmt->execute()) {
            throw new Exception($stmt->error);
        }
    }

    $stmt->close();
    /*
    |--------------------------------------------------------------------------
    | Update Stok dan Kondisi Alat
    |--------------------------------------------------------------------------
    */

    $sql = "

        UPDATE alat
        SET
            stok = stok + ?,
            kondisi = ?,
            updated_at = NOW()
        WHERE
            id_alat = ?

    ";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        throw new Exception($conn->error);
    }

    for ($i = 0; $i < count($idAlat); $i++) {
        $idAlatItem = (int) $idAlat[$i];

        $jumlahItem = (int) $jumlah[$i];

        $kondisiItem = trim($kondisi[$i]);

        $stmt->bind_param(
            'isi',

            $jumlahItem,

            $kondisiItem,

            $idAlatItem,
        );

        if (!$stmt->execute()) {
            throw new Exception($stmt->error);
        }
    }

    $stmt->close();

    /*
    |--------------------------------------------------------------------------
    | Update Status Peminjaman
    |--------------------------------------------------------------------------
    */

    $sql = "

        UPDATE peminjaman
        SET
            status = 'Selesai',
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

        $idPeminjaman,
    );

    if (!$stmt->execute()) {
        throw new Exception($stmt->error);
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

        alert('Data pengembalian berhasil disimpan.');

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

        alert('Gagal menyimpan data pengembalian.\n\n" .
        $e->getMessage() .
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

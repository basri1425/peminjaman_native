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

$keteranganAlat = $_POST['keterangan_alat'] ?? [];

/*
|--------------------------------------------------------------------------
| Validasi Data
|--------------------------------------------------------------------------
*/

if ($idPeminjaman <= 0 || empty($tanggalPengembalian) || empty($idAlat) || empty($jumlah) || empty($kondisi)) {
    $conn->close();

    header('Location: index.php');

    exit();
}

/*
|--------------------------------------------------------------------------
| Validasi Jumlah Array
|--------------------------------------------------------------------------
*/

if (count($idAlat) != count($jumlah) || count($idAlat) != count($kondisi) || count($idAlat) != count($keteranganAlat)) {
    $conn->close();

    header('Location: detail.php?id=' . $idPeminjaman);

    exit();
}

/*
|--------------------------------------------------------------------------
| Validasi Nilai Kondisi
|--------------------------------------------------------------------------
*/

$kondisiValid = ['Baik', 'Rusak Ringan', 'Rusak Berat', 'Hilang'];

foreach ($kondisi as $nilai) {
    if (!in_array($nilai, $kondisiValid, true)) {
        $conn->close();

        header('Location: detail.php?id=' . $idPeminjaman);

        exit();
    }
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

$stmt->bind_param(
    'i',

    $idPeminjaman,
);

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
| Validasi Status
|--------------------------------------------------------------------------
*/

if ($data['status'] != 'Dipinjam') {
    $result->free();

    $stmt->close();

    $conn->close();

    header('Location: detail.php?id=' . $idPeminjaman);

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

    $sqlPengembalian = "

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
            ?,
            ?,
            ?,
            NOW(),
            NOW()
        )

    ";

    $stmtPengembalian = $conn->prepare($sqlPengembalian);

    if (!$stmtPengembalian) {
        throw new Exception('Gagal mempersiapkan query pengembalian.');
    }

    $stmtPengembalian->bind_param(
        'iss',

        $idPeminjaman,

        $tanggalPengembalian,

        $keterangan,
    );

    if (!$stmtPengembalian->execute()) {
        throw new Exception('Gagal menyimpan data pengembalian.');
    }

    /*
    |--------------------------------------------------------------------------
    | Mengambil ID Pengembalian
    |--------------------------------------------------------------------------
    */

    $idPengembalian = $conn->insert_id;
    /*
    |--------------------------------------------------------------------------
    | Menyiapkan Query Detail Pengembalian
    |--------------------------------------------------------------------------
    */

    $sqlDetail = "

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
            ?,
            ?,
            ?,
            ?,
            ?
        )

    ";

    $stmtDetail = $conn->prepare($sqlDetail);

    if (!$stmtDetail) {
        throw new Exception('Gagal mempersiapkan query detail pengembalian.');
    }

    /*
    |--------------------------------------------------------------------------
    | Menyiapkan Query Update Stok
    |--------------------------------------------------------------------------
    */

    $sqlStok = "

        UPDATE alat

        SET

            stok = stok + ?

        WHERE

            id_alat = ?

    ";

    $stmtStok = $conn->prepare($sqlStok);

    if (!$stmtStok) {
        throw new Exception('Gagal mempersiapkan query update stok.');
    }

    /*
    |--------------------------------------------------------------------------
    | Menyimpan Detail Pengembalian
    |--------------------------------------------------------------------------
    */

    foreach ($idAlat as $index => $idAlatItem) {
        $idAlatItem = (int) $idAlatItem;

        $jumlahItem = (int) $jumlah[$index];

        $kondisiItem = trim($kondisi[$index]);

        $keteranganItem = trim($keteranganAlat[$index]);

        /*
        |--------------------------------------------------------------------------
        | Simpan Detail Pengembalian
        |--------------------------------------------------------------------------
        */

        $stmtDetail->bind_param(
            'iiiss',

            $idPengembalian,

            $idAlatItem,

            $jumlahItem,

            $kondisiItem,

            $keteranganItem,
        );

        if (!$stmtDetail->execute()) {
            throw new Exception('Gagal menyimpan detail pengembalian.');
        }

        /*
        |--------------------------------------------------------------------------
        | Tambah Stok
        |--------------------------------------------------------------------------
        */

        if ($kondisiItem != 'Hilang') {
            $stmtStok->bind_param(
                'ii',

                $jumlahItem,

                $idAlatItem,
            );

            if (!$stmtStok->execute()) {
                throw new Exception('Gagal menambah stok alat.');
            }
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Update Status Peminjaman
    |--------------------------------------------------------------------------
    */

    $status = 'Selesai';

    $sqlUpdate = "

        UPDATE peminjaman

        SET

            status = ?,
            updated_at = NOW()

        WHERE

            id_peminjaman = ?

    ";

    $stmtUpdate = $conn->prepare($sqlUpdate);

    if (!$stmtUpdate) {
        throw new Exception('Gagal mempersiapkan query update status.');
    }

    $stmtUpdate->bind_param(
        'si',

        $status,

        $idPeminjaman,
    );

    if (!$stmtUpdate->execute()) {
        throw new Exception('Gagal mengubah status peminjaman.');
    }

    /*
    |--------------------------------------------------------------------------
    | Simpan Log Aktivitas
    |--------------------------------------------------------------------------
    */

    if (
        !tambahLog(
            $conn,

            "Memproses pengembalian alat untuk peminjaman ID #{$idPeminjaman}",
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

    $stmtPengembalian->close();

    $stmtDetail->close();

    $stmtStok->close();

    $stmtUpdate->close();

    /*
    |--------------------------------------------------------------------------
    | Redirect Berhasil
    |--------------------------------------------------------------------------
    */

    header('Location: index.php?pesan=berhasil');

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

    if (isset($stmtPengembalian)) {
        $stmtPengembalian->close();
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
    | Redirect Gagal
    |--------------------------------------------------------------------------
    */

    header('Location: detail.php?id=' . $idPeminjaman . '&pesan=gagal');

    exit();
}

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

if (isset($stmtPengembalian)) {
    $stmtPengembalian->close();
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
| Menutup Koneksi Database
|--------------------------------------------------------------------------
*/

$conn->close();

?>

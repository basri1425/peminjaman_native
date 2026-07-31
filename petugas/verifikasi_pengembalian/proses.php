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

    header("Location: ../../login.php");
    exit();
}

/*
|--------------------------------------------------------------------------
| Validasi Hak Akses
|--------------------------------------------------------------------------
*/

if ($_SESSION['level'] != 'Petugas') {

    header("Location: ../../unauthorized.php");
    exit();
}

/*
|--------------------------------------------------------------------------
| Validasi Request
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] != 'POST') {

    header("Location:index.php");
    exit();
}

/*
|--------------------------------------------------------------------------
| Ambil Data Form
|--------------------------------------------------------------------------
*/

$idPeminjaman = (int) ($_POST['id_peminjaman'] ?? 0);

$tanggalPengembalian = trim($_POST['tanggal_pengembalian'] ?? '');

$keterangan = trim($_POST['keterangan'] ?? '');

$idAlat = $_POST['id_alat'] ?? [];

$jumlah = $_POST['jumlah'] ?? [];

$kondisi = $_POST['kondisi'] ?? [];

$keteranganAlat = $_POST['keterangan_alat'] ?? [];

/*
|--------------------------------------------------------------------------
| Validasi
|--------------------------------------------------------------------------
*/

if (

    $idPeminjaman <= 0 ||

    empty($tanggalPengembalian) ||

    count($idAlat) == 0

) {

    $_SESSION['error'] = "Data tidak lengkap.";

    header("Location:index.php");

    exit();
}

/*
|--------------------------------------------------------------------------
| Validasi Status
|--------------------------------------------------------------------------
*/

$sql = "

SELECT

status

FROM peminjaman

WHERE

id_peminjaman=?

LIMIT 1

";

$stmt = $conn->prepare($sql);

$stmt->bind_param("i", $idPeminjaman);

$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows == 0) {

    $_SESSION['error'] = "Data tidak ditemukan.";

    header("Location:index.php");

    exit();
}

$data = $result->fetch_assoc();

$stmt->close();

if ($data['status'] != 'Dipinjam') {

    $_SESSION['error'] = "Pengajuan sudah diproses.";

    header("Location:index.php");

    exit();
}

/*
|--------------------------------------------------------------------------
| Validasi Sudah Pernah Diverifikasi
|--------------------------------------------------------------------------
*/

// $sql = "

// SELECT

// id_pengembalian

// FROM pengembalian

// WHERE

// id_peminjaman=?

// LIMIT 1

// ";

// $stmt = $conn->prepare($sql);

// $stmt->bind_param("i", $idPeminjaman);

// $stmt->execute();

// $result = $stmt->get_result();

// if ($result->num_rows > 0) {

//     $_SESSION['error'] = "Pengembalian sudah pernah diverifikasi.";

//     header("Location:index.php");

//     exit();
// }

// $stmt->close();

/*
|--------------------------------------------------------------------------
| Database Transaction
|--------------------------------------------------------------------------
*/

$conn->begin_transaction();

try {
    /*
    |--------------------------------------------------------------------------
    | Menentukan Kondisi Pengembalian
    |--------------------------------------------------------------------------
    */

    $kondisiKembali = 'Baik';

    foreach ($kondisi as $item) {

        if ($item == 'Rusak Berat') {

            $kondisiKembali = 'Rusak Berat';
            break;
        }

        if ($item == 'Rusak Ringan' && $kondisiKembali != 'Rusak Berat') {

            $kondisiKembali = 'Rusak Ringan';
        }

        if ($item == 'Hilang') {

            $kondisiKembali = 'Rusak Berat';
            break;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Simpan Detail Pengembalian
    |--------------------------------------------------------------------------
    */

    foreach ($idAlat as $index => $idAlatItem) {

        $idAlatItem = (int) $idAlatItem;

        $jumlahItem = (int) $jumlah[$index];

        $kondisiItem = trim($kondisi[$index]);

        $keteranganItem = trim($keteranganAlat[$index]);

        
        /*
        |--------------------------------------------------------------------------
        | Update Stok
        |--------------------------------------------------------------------------
        |
        | Stok hanya bertambah jika alat benar-benar kembali.
        |
        */

        if ($kondisiItem != 'Hilang') {

            $sql = "

            UPDATE alat

            SET

                stok = stok + ?,
                updated_at = NOW()

            WHERE

                id_alat = ?

            ";

            $stmt = $conn->prepare($sql);

            if (!$stmt) {

                throw new Exception($conn->error);
            }

            $stmt->bind_param(

                "ii",

                $jumlahItem,
                $idAlatItem

            );

            if (!$stmt->execute()) {

                throw new Exception($stmt->error);
            }

            $stmt->close();
        }

        /*
        |--------------------------------------------------------------------------
        | Update Kondisi Alat
        |--------------------------------------------------------------------------
        */

        $kondisiAlat = ($kondisiItem == 'Hilang')
            ? 'Rusak Berat'
            : $kondisiItem;

        $sql = "

        UPDATE alat

        SET

            kondisi = ?,
            updated_at = NOW()

        WHERE

            id_alat = ?

        ";

        $stmt = $conn->prepare($sql);

        if (!$stmt) {

            throw new Exception($conn->error);
        }

        $stmt->bind_param(

            "si",

            $kondisiAlat,
            $idAlatItem

        );

        if (!$stmt->execute()) {

            throw new Exception($stmt->error);
        }

        $stmt->close();
    }
    /*
    |--------------------------------------------------------------------------
    | Update Status Peminjaman
    |--------------------------------------------------------------------------
    */

    $status = "Selesai";

    $sql = "

    UPDATE peminjaman

    SET

        status = ?,
        updated_at = NOW()

    WHERE

        id_peminjaman = ?

    ";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {

        throw new Exception($conn->error);
    }

    $stmt->bind_param(

        "si",

        $status,
        $idPeminjaman

    );

    if (!$stmt->execute()) {

        throw new Exception($stmt->error);
    }

    $stmt->close();

    /*
    |--------------------------------------------------------------------------
    | Simpan Log Aktivitas
    |--------------------------------------------------------------------------
    */

    $aktivitas = "Memverifikasi pengembalian alat. ID Peminjaman : " . $idPeminjaman;

    if (!tambahLog($conn, $aktivitas)) {

        throw new Exception("Gagal menyimpan log aktivitas.");
    }

    /*
    |--------------------------------------------------------------------------
    | Commit Transaction
    |--------------------------------------------------------------------------
    */

    $conn->commit();

    $_SESSION['success'] = "Verifikasi pengembalian berhasil diproses.";
} catch (Exception $e) {

    /*
    |--------------------------------------------------------------------------
    | Rollback
    |--------------------------------------------------------------------------
    */

    $conn->rollback();

    $_SESSION['error'] = $e->getMessage();
}

/*
|--------------------------------------------------------------------------
| Tutup Koneksi
|--------------------------------------------------------------------------
*/

$conn->close();

/*
|--------------------------------------------------------------------------
| Redirect
|--------------------------------------------------------------------------
*/

header("Location: index.php");
exit();

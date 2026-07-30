<?php

/*
|--------------------------------------------------------------------------
| Konfigurasi
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

$idPeminjaman = (int) ($_POST['id_peminjaman'] ?? 0);

$tanggalPengembalian = trim($_POST['tanggal_pengembalian'] ?? '');

$keterangan = trim($_POST['keterangan'] ?? '');

$idAlat = $_POST['id_alat'] ?? [];

$jumlah = $_POST['jumlah'] ?? [];

$kondisi = $_POST['kondisi'] ?? [];

$keteranganAlat = $_POST['keterangan_alat'] ?? [];

/*
|--------------------------------------------------------------------------
| Validasi Input
|--------------------------------------------------------------------------
*/

if (

    $idPeminjaman <= 0 ||

    empty($tanggalPengembalian) ||

    empty($idAlat)

) {

    $_SESSION['error'] = 'Data pengembalian tidak lengkap.';

    header('Location: index.php');
    exit();
}

/*
|--------------------------------------------------------------------------
| Validasi Status Peminjaman
|--------------------------------------------------------------------------
*/

$sql = "

SELECT

    status

FROM peminjaman

WHERE

    id_peminjaman = ?

LIMIT 1

";

$stmt = $conn->prepare($sql);

$stmt->bind_param("i", $idPeminjaman);

$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows == 0) {

    $stmt->close();

    $_SESSION['error'] = 'Data peminjaman tidak ditemukan.';

    header('Location: index.php');
    exit();
}

$data = $result->fetch_assoc();

$stmt->close();

$result->free();

if ($data['status'] != 'Dipinjam') {

    $_SESSION['error'] = 'Status peminjaman tidak dapat diproses.';

    header('Location: detail.php?id=' . $idPeminjaman);
    exit();
}

/*
|--------------------------------------------------------------------------
| Validasi Pengembalian
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

$stmt->bind_param("i", $idPeminjaman);

$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows > 0) {

    $stmt->close();

    $result->free();

    $_SESSION['error'] =
        'Pengembalian untuk transaksi ini sudah pernah diproses.';

    header('Location: detail.php?id=' . $idPeminjaman);
    exit();
}

$stmt->close();

$result->free();

/*
|--------------------------------------------------------------------------
| Memulai Database Transaction
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

        if ($item == 'Rusak Ringan') {

            $kondisiKembali = 'Rusak Ringan';
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Simpan Data Pengembalian
    |--------------------------------------------------------------------------
    */

    $sql = "

        INSERT INTO pengembalian
        (
            id_peminjaman,
            tanggal_pengembalian,
            kondisi_kembali,
            keterangan,
            created_at,
            updated_at
        )
        VALUES
        (
            ?,
            ?,
            ?,
            ?,
            NOW(),
            NOW()
        )

    ";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {

        throw new Exception($conn->error);
    }

    $stmt->bind_param(

        "isss",

        $idPeminjaman,
        $tanggalPengembalian,
        $kondisiKembali,
        $keterangan

    );

    if (!$stmt->execute()) {

        throw new Exception($stmt->error);
    }

    /*
    |--------------------------------------------------------------------------
    | ID Pengembalian
    |--------------------------------------------------------------------------
    */

    $idPengembalian = $conn->insert_id;

    $stmt->close();
    /*
    |--------------------------------------------------------------------------
    | Menyimpan Detail Pengembalian
    |--------------------------------------------------------------------------
    */

    foreach ($idAlat as $i => $idAlatItem) {

        $idAlatItem = (int) $idAlatItem;

        $jumlahItem = (int) $jumlah[$i];

        $kondisiItem = trim($kondisi[$i]);

        $keteranganItem = trim($keteranganAlat[$i]);

        /*
        |--------------------------------------------------------------------------
        | INSERT Detail Pengembalian
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
                ?,
                ?,
                ?,
                ?,
                ?
            )

        ";

        $stmt = $conn->prepare($sql);

        if (!$stmt) {

            throw new Exception($conn->error);
        }

        $stmt->bind_param(

            "iiiss",

            $idPengembalian,
            $idAlatItem,
            $jumlahItem,
            $kondisiItem,
            $keteranganItem

        );

        if (!$stmt->execute()) {

            throw new Exception($stmt->error);
        }

        $stmt->close();

        /*
        |--------------------------------------------------------------------------
        | Update Stok
        |--------------------------------------------------------------------------
        |
        | Jika alat hilang maka stok tidak bertambah.
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

            $kondisiItem,
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

    $status = 'Selesai';

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

    $aktivitas = "Memproses pengembalian alat. ID Peminjaman : {$idPeminjaman}";

    if (!tambahLog($conn, $aktivitas)) {

        throw new Exception('Gagal menyimpan log aktivitas.');
    }

    /*
    |--------------------------------------------------------------------------
    | Commit
    |--------------------------------------------------------------------------
    */

    $conn->commit();

    $_SESSION['success'] = 'Pengembalian alat berhasil diproses.';
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
| Menutup Koneksi
|--------------------------------------------------------------------------
*/

$conn->close();

/*
|--------------------------------------------------------------------------
| Redirect
|--------------------------------------------------------------------------
*/

if (isset($_SESSION['success'])) {

    header('Location: index.php');
} else {

    header('Location: detail.php?id=' . $idPeminjaman);
}

exit;

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

$idUser = (int) $_SESSION['id_user'];

$idAlat = isset($_POST['id_alat']) ? (int) $_POST['id_alat'] : 0;

$tanggalPinjam = trim($_POST['tanggal_pinjam'] ?? '');

$tanggalKembali = trim($_POST['tanggal_kembali'] ?? '');

$jumlah = isset($_POST['jumlah']) ? (int) $_POST['jumlah'] : 0;


/*
|--------------------------------------------------------------------------
| Status Awal Pengajuan
|--------------------------------------------------------------------------
*/

$status = 'Menunggu';

/*
|--------------------------------------------------------------------------
| Validasi Data Kosong
|--------------------------------------------------------------------------
*/

if ($idAlat <= 0 || empty($tanggalPinjam) || empty($tanggalKembali) || $jumlah <= 0) {
    $_SESSION['error'] = 'Semua data wajib diisi.';

    header('Location: tambah.php?id=' . $idAlat);

    exit();
}


/*
|--------------------------------------------------------------------------
| Mengambil Data Alat
|--------------------------------------------------------------------------
*/

$sql = "

    SELECT

        id_alat,
        nama_alat,
        stok

    FROM alat

    WHERE id_alat = ?

    LIMIT 1

";

$stmt = $conn->prepare($sql);

$stmt->bind_param('i', $idAlat);

$stmt->execute();

$result = $stmt->get_result();

/*
|--------------------------------------------------------------------------
| Validasi Data Alat
|--------------------------------------------------------------------------
*/

if ($result->num_rows == 0) {
    $_SESSION['error'] = 'Data alat tidak ditemukan.';

    $stmt->close();

    $result->free();

    $conn->close();

    header('Location: ../alat/index.php');

    exit();
}

$alat = $result->fetch_assoc();

/*
|--------------------------------------------------------------------------
| Validasi Stok
|--------------------------------------------------------------------------
*/

if ($alat['stok'] <= 0) {
    $_SESSION['error'] = 'Stok alat sudah habis.';

    $stmt->close();

    $result->free();

    $conn->close();

    header('Location: tambah.php?id=' . $idAlat);

    exit();
}

if ($jumlah > $alat['stok']) {
    $_SESSION['error'] = 'Jumlah peminjaman melebihi stok yang tersedia.';

    $stmt->close();

    $result->free();

    $conn->close();

    header('Location: tambah.php?id=' . $idAlat);

    exit();
}

/*
|--------------------------------------------------------------------------
| Validasi Tanggal
|--------------------------------------------------------------------------
*/

$tanggalHariIni = date('Y-m-d');

if ($tanggalPinjam < $tanggalHariIni) {
    $_SESSION['error'] = 'Tanggal pinjam tidak boleh sebelum hari ini.';

    $stmt->close();

    $result->free();

    $conn->close();

    header('Location: tambah.php?id=' . $idAlat);

    exit();
}

if ($tanggalKembali < $tanggalPinjam) {
    $_SESSION['error'] = 'Tanggal kembali tidak boleh lebih awal dari tanggal pinjam.';

    $stmt->close();

    $result->free();

    $conn->close();

    header('Location: tambah.php?id=' . $idAlat);

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
| Menyimpan Data Peminjaman
|--------------------------------------------------------------------------
*/

    $sqlPeminjaman = "

    INSERT INTO peminjaman (

        id_user,
        tanggal_pinjam,
        tanggal_kembali,
        status

    )

    VALUES (

        ?, ?, ?, ?

    )

";

    $stmtPeminjaman = $conn->prepare($sqlPeminjaman);

    $stmtPeminjaman->bind_param(
        'isss',

        $idUser,
        $tanggalPinjam,
        $tanggalKembali,
        $status,
    );

    $stmtPeminjaman->execute();

    /*
|--------------------------------------------------------------------------
| ID Peminjaman Baru
|--------------------------------------------------------------------------
*/

    $idPeminjaman = $conn->insert_id;

    /*
|--------------------------------------------------------------------------
| Menyimpan Detail Peminjaman
|--------------------------------------------------------------------------
*/

    $sqlDetail = "

    INSERT INTO detail_peminjaman (

        id_peminjaman,
        id_alat,
        jumlah

    )

    VALUES (

        ?, ?, ?

    )

";

    $stmtDetail = $conn->prepare($sqlDetail);

    $stmtDetail->bind_param(
        'iii',

        $idPeminjaman,
        $idAlat,
        $jumlah,
    );

    $stmtDetail->execute();

    /*
|--------------------------------------------------------------------------
| Menambahkan Log Aktivitas
|--------------------------------------------------------------------------
*/

    $aktivitas = 'Mengajukan peminjaman alat : ' . $alat['nama_alat'] . ' sebanyak ' . $jumlah . ' unit.';

    tambahLog(
        $conn,

        $aktivitas,
    );

    /*
|--------------------------------------------------------------------------
| Commit Transaksi
|--------------------------------------------------------------------------
*/

    $conn->commit();

    /*
|--------------------------------------------------------------------------
| Redirect Berhasil
|--------------------------------------------------------------------------
*/

    $_SESSION['success'] = 'Pengajuan peminjaman berhasil dikirim dan sedang menunggu persetujuan.';

    header('Location: index.php');

    exit();
} catch (Exception $e) {
    /*
    |--------------------------------------------------------------------------
    | Rollback Transaksi
    |--------------------------------------------------------------------------
    */

    $conn->rollback();

    /*
    |--------------------------------------------------------------------------
    | Menyimpan Log Error (Opsional)
    |--------------------------------------------------------------------------
    */

    error_log($e->getMessage());

    /*
    |--------------------------------------------------------------------------
    | Pesan Error
    |--------------------------------------------------------------------------
    */

    $_SESSION['error'] = 'Terjadi kesalahan saat menyimpan data peminjaman.';

    header('Location: tambah.php?id=' . $idAlat);

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
| Menutup Statement
|--------------------------------------------------------------------------
*/

if (isset($stmt) && $stmt instanceof mysqli_stmt) {
    $stmt->close();
}

if (isset($stmtPeminjaman) && $stmtPeminjaman instanceof mysqli_stmt) {
    $stmtPeminjaman->close();
}

if (isset($stmtDetail) && $stmtDetail instanceof mysqli_stmt) {
    $stmtDetail->close();
}

/*
|--------------------------------------------------------------------------
| Menutup Koneksi Database
|--------------------------------------------------------------------------
*/

if (isset($conn) && $conn instanceof mysqli) {
    $conn->close();
}

?>

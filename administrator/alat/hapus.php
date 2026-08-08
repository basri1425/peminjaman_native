<?php
/*
|--------------------------------------------------------------------------
| File        : hapus.php
| Folder      : administrator/alat
| Fungsi      : Menghapus Data Alat
|--------------------------------------------------------------------------
*/

require_once "../../config/session.php";
require_once "../../config/database.php";

/*
|--------------------------------------------------------------------------
| Hak Akses
|--------------------------------------------------------------------------
*/

if ($_SESSION['level'] != "Administrator") {
    header("Location: ../../auth/login.php");
    exit();
}

/*
|--------------------------------------------------------------------------
| Validasi Parameter ID
|--------------------------------------------------------------------------
*/

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$id_alat = (int) $_GET['id'];

/*
|--------------------------------------------------------------------------
| Mengambil Data Alat
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
SELECT
    id_alat,
    foto
FROM alat
WHERE id_alat = ?
");

$stmt->bind_param("i", $id_alat);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    header("Location: index.php");
    exit();
}

$alat = $result->fetch_assoc();

$stmt->close();

/*
|--------------------------------------------------------------------------
| Cek Apakah Alat Sudah Pernah Dipinjam
|--------------------------------------------------------------------------
|
| Aktifkan bagian ini apabila tabel detail_peminjaman sudah dibuat.
|
*/

/*

$stmt = $conn->prepare("
SELECT COUNT(*) AS jumlah
FROM detail_peminjaman
WHERE id_alat = ?
");

$stmt->bind_param("i", $id_alat);

$stmt->execute();

$data = $stmt->get_result()->fetch_assoc();

$stmt->close();

if ($data['jumlah'] > 0) {

    echo "<script>

            alert('Data alat tidak dapat dihapus karena sudah digunakan pada transaksi peminjaman.');

            window.location='index.php';

          </script>";

    exit();

}

*/

/*
|--------------------------------------------------------------------------
| Hapus Data
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
DELETE FROM alat
WHERE id_alat = ?
");

$stmt->bind_param("i", $id_alat);

/*
|--------------------------------------------------------------------------
| Eksekusi
|--------------------------------------------------------------------------
*/

if ($stmt->execute()) {

    /*
    |--------------------------------------------------------------------------
    | Hapus File Foto
    |--------------------------------------------------------------------------
    */

    if (!empty($alat['foto'])) {

        $file = "../../assets/img/alat/" . $alat['foto'];

        if (file_exists($file)) {
            unlink($file);
        }
    }

    header("Location: index.php?pesan=hapus");
} else {

    echo "<script>

            alert('Data alat gagal dihapus.');

            window.location='index.php';

          </script>";
}

$stmt->close();

$conn->close();

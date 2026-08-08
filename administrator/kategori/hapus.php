<?php
/*
|--------------------------------------------------------------------------
| File        : hapus.php
| Folder      : administrator/kategori
| Fungsi      : Menghapus Data Kategori
|--------------------------------------------------------------------------
*/

require_once '../../config/session.php';
require_once '../../config/database.php';

/*
|--------------------------------------------------------------------------
| Hak Akses
|--------------------------------------------------------------------------
*/

if ($_SESSION['level'] != 'Administrator') {
    header('Location: ../../auth/login.php');
    exit();
}

/*
|--------------------------------------------------------------------------
| Validasi Parameter ID
|--------------------------------------------------------------------------
*/

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: index.php');
    exit();
}

$id_kategori = (int) $_GET['id'];

/*
|--------------------------------------------------------------------------
| Pastikan Data Kategori Ada
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    SELECT id_kategori
    FROM kategori
    WHERE id_kategori = ?
");

$stmt->bind_param('i', $id_kategori);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    header('Location: index.php');
    exit();
}

$stmt->close();

/*
|--------------------------------------------------------------------------
| Cek Apakah Masih Digunakan Oleh Tabel Alat
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    SELECT COUNT(*) AS jumlah
    FROM alat
    WHERE id_kategori = ?
");

$stmt->bind_param('i', $id_kategori);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();
$stmt->close();

if ($data['jumlah'] > 0) {
    echo "
    <script>
        alert('Kategori tidak dapat dihapus karena masih digunakan oleh data alat.');
        window.location='index.php';
    </script>
    ";

    exit();
}

/*
|--------------------------------------------------------------------------
| Hapus Data
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    DELETE FROM kategori
    WHERE id_kategori = ?
");

$stmt->bind_param('i', $id_kategori);

/*
|--------------------------------------------------------------------------
| Eksekusi Query
|--------------------------------------------------------------------------
*/

if ($stmt->execute()) {
    header('Location: index.php?pesan=hapus');
} else {
    echo "
    <script>
        alert('Data kategori gagal dihapus.');
        window.location='index.php';
    </script>
    ";
}

$stmt->close();
$conn->close();

?>

<?php
/*
|--------------------------------------------------------------------------
| File        : update.php
| Folder      : administrator/kategori
| Fungsi      : Memperbarui Data Kategori
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
| Pastikan Request POST
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

$id_kategori = (int) $_POST['id_kategori'];
$nama_kategori = trim($_POST['nama_kategori']);

/*
|--------------------------------------------------------------------------
| Validasi Input
|--------------------------------------------------------------------------
*/

if (empty($id_kategori) || empty($nama_kategori)) {
    header('Location: edit.php?id=' . $id_kategori);
    exit();
}

/*
|--------------------------------------------------------------------------
| Cek Nama Kategori
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    SELECT id_kategori
    FROM kategori
    WHERE nama_kategori = ?
    AND id_kategori <> ?
");

$stmt->bind_param('si', $nama_kategori, $id_kategori);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo "
    <script>
        alert('Nama kategori sudah digunakan.');
        window.location='edit.php?id=$id_kategori';
    </script>
    ";

    exit();
}

$stmt->close();

/*
|--------------------------------------------------------------------------
| Update Data
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    UPDATE kategori
    SET
        nama_kategori = ?
    WHERE id_kategori = ?
");

$stmt->bind_param('si', $nama_kategori, $id_kategori);

/*
|--------------------------------------------------------------------------
| Eksekusi Query
|--------------------------------------------------------------------------
*/

if ($stmt->execute()) {
    header('Location: index.php?pesan=update');
} else {
    echo "
    <script>
        alert('Data kategori gagal diperbarui.');
        window.location='edit.php?id=$id_kategori';
    </script>
    ";
}

$stmt->close();
$conn->close();

?>

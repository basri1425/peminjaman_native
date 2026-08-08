<?php
/*
|--------------------------------------------------------------------------
| File        : simpan.php
| Folder      : administrator/kategori
| Fungsi      : Menyimpan Data Kategori
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

$nama_kategori = trim($_POST['nama_kategori']);

/*
|--------------------------------------------------------------------------
| Validasi Input
|--------------------------------------------------------------------------
*/

if (empty($nama_kategori)) {
    header('Location: create.php');
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
");

$stmt->bind_param('s', $nama_kategori);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo "
    <script>
        alert('Nama kategori sudah tersedia.');
        window.location='create.php';
    </script>
    ";

    exit();
}

$stmt->close();

/*
|--------------------------------------------------------------------------
| Simpan Data
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    INSERT INTO kategori
    (
        nama_kategori
    )
    VALUES
    (
        ?
    )
");

$stmt->bind_param('s', $nama_kategori);

/*
|--------------------------------------------------------------------------
| Eksekusi Query
|--------------------------------------------------------------------------
*/

if ($stmt->execute()) {
    header('Location: index.php?pesan=sukses');
} else {
    echo "
    <script>
        alert('Data kategori gagal disimpan.');
        window.location='create.php';
    </script>
    ";
}
$stmt->close();
$conn->close();
?>

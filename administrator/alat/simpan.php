<?php
/*
|--------------------------------------------------------------------------
| File        : simpan.php
| Folder      : administrator/alat
| Fungsi      : Menyimpan Data Alat
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

$id_kategori = (int) $_POST['id_kategori'];
$nama_alat = trim($_POST['nama_alat']);
$stok = (int) $_POST['stok'];
$kondisi = trim($_POST['kondisi']);
$lokasi = trim($_POST['lokasi']);

$foto = '';

/*
|--------------------------------------------------------------------------
| Validasi Input
|--------------------------------------------------------------------------
*/

if (empty($id_kategori) || empty($nama_alat)) {
    echo "<script>
            alert('Data belum lengkap.');
            window.location='create.php';
          </script>";
    exit();
}

/*
|--------------------------------------------------------------------------
| Validasi Kategori
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
SELECT id_kategori
FROM kategori
WHERE id_kategori=?
");

$stmt->bind_param('i', $id_kategori);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo "<script>
            alert('Kategori tidak ditemukan.');
            window.location='create.php';
          </script>";

    exit();
}

$stmt->close();

/*
|--------------------------------------------------------------------------
| Upload Foto
|--------------------------------------------------------------------------
*/

if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
    $namaFile = $_FILES['foto']['name'];
    $tmpFile = $_FILES['foto']['tmp_name'];
    $ukuran = $_FILES['foto']['size'];

    $ext = strtolower(pathinfo($namaFile, PATHINFO_EXTENSION));

    $allowed = ['jpg', 'jpeg', 'png'];

    if (!in_array($ext, $allowed)) {
        echo "<script>
                alert('Format foto harus JPG, JPEG, atau PNG.');
                window.location='create.php';
              </script>";

        exit();
    }

    if ($ukuran > 2 * 1024 * 1024) {
        echo "<script>
                alert('Ukuran foto maksimal 2 MB.');
                window.location='create.php';
              </script>";

        exit();
    }

    $foto = uniqid('alat_') . '.' . $ext;

    $tujuan = '../../assets/img/alat/' . $foto;

    move_uploaded_file($tmpFile, $tujuan);
}

/*
|--------------------------------------------------------------------------
| Simpan Data
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
INSERT INTO alat
(
id_kategori,
nama_alat,
stok,
kondisi,
lokasi,
foto
)
VALUES
(
?,?,?,?,?,?
)
");

$stmt->bind_param('isisss', $id_kategori, $nama_alat, $stok, $kondisi, $lokasi, $foto);

/*
|--------------------------------------------------------------------------
| Eksekusi
|--------------------------------------------------------------------------
*/

if ($stmt->execute()) {
    header('Location: index.php?pesan=sukses');
} else {
    if ($foto != '') {
        $file = '../../assets/img/alat/' . $foto;

        if (file_exists($file)) {
            unlink($file);
        }
    }

    echo "<script>
            alert('Data gagal disimpan.');
            window.location='create.php';
          </script>";
}

$stmt->close();

$conn->close();

?>

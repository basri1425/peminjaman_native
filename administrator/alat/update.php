<?php
/*
|--------------------------------------------------------------------------
| File        : update.php
| Folder      : administrator/alat
| Fungsi      : Memperbarui Data Alat
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
| Validasi Request
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] != "POST") {
    header("Location: index.php");
    exit();
}

/*
|--------------------------------------------------------------------------
| Mengambil Data Form
|--------------------------------------------------------------------------
*/

$id_alat      = (int) $_POST['id_alat'];
$id_kategori  = (int) $_POST['id_kategori'];
$nama_alat    = trim($_POST['nama_alat']);
$stok         = (int) $_POST['stok'];
$kondisi      = trim($_POST['kondisi']);
$lokasi       = trim($_POST['lokasi']);
$foto_lama    = trim($_POST['foto_lama']);

$foto = $foto_lama;

/*
|--------------------------------------------------------------------------
| Validasi Input
|--------------------------------------------------------------------------
*/

if (
    empty($id_alat) ||
    empty($id_kategori) ||
    empty($nama_alat)
) {

    echo "<script>
            alert('Data belum lengkap.');
            window.location='edit.php?id=$id_alat';
          </script>";

    exit();
}

/*
|--------------------------------------------------------------------------
| Validasi Data Alat
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
SELECT id_alat
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

$stmt->close();

/*
|--------------------------------------------------------------------------
| Validasi Kategori
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
SELECT id_kategori
FROM kategori
WHERE id_kategori = ?
");

$stmt->bind_param("i", $id_kategori);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows == 0) {
    echo "<script>
            alert('Kategori tidak ditemukan.');
            window.location='edit.php?id=$id_alat';
          </script>";
    exit();
}

$stmt->close();

/*
|--------------------------------------------------------------------------
| Upload Foto Baru (Jika Ada)
|--------------------------------------------------------------------------
*/

if (
    isset($_FILES['foto']) &&
    $_FILES['foto']['error'] == 0
) {

    $namaFile = $_FILES['foto']['name'];
    $tmpFile  = $_FILES['foto']['tmp_name'];
    $ukuran   = $_FILES['foto']['size'];

    $ext = strtolower(pathinfo($namaFile, PATHINFO_EXTENSION));

    $allowed = ['jpg', 'jpeg', 'png'];

    if (!in_array($ext, $allowed)) {

        echo "<script>
                alert('Format foto harus JPG, JPEG, atau PNG.');
                window.location='edit.php?id=$id_alat';
              </script>";

        exit();
    }

    if ($ukuran > 2 * 1024 * 1024) {

        echo "<script>
                alert('Ukuran foto maksimal 2 MB.');
                window.location='edit.php?id=$id_alat';
              </script>";

        exit();
    }

    $foto = uniqid('alat_') . "." . $ext;

    $tujuan = "../../assets/img/alat/" . $foto;

    if (move_uploaded_file($tmpFile, $tujuan)) {

        if (!empty($foto_lama)) {

            $fileLama = "../../assets/img/alat/" . $foto_lama;

            if (file_exists($fileLama)) {
                unlink($fileLama);
            }
        }
    } else {

        echo "<script>
                alert('Upload foto gagal.');
                window.location='edit.php?id=$id_alat';
              </script>";

        exit();
    }
}

/*
|--------------------------------------------------------------------------
| Update Data
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
UPDATE alat
SET
    id_kategori = ?,
    nama_alat   = ?,
    stok        = ?,
    kondisi     = ?,
    lokasi      = ?,
    foto        = ?
WHERE id_alat = ?
");

$stmt->bind_param(
    "isisssi",
    $id_kategori,
    $nama_alat,
    $stok,
    $kondisi,
    $lokasi,
    $foto,
    $id_alat
);

/*
|--------------------------------------------------------------------------
| Eksekusi
|--------------------------------------------------------------------------
*/

if ($stmt->execute()) {

    header("Location: index.php?pesan=update");
} else {

    /*
    |--------------------------------------------------------------
    | Hapus Foto Baru Jika Update Database Gagal
    |--------------------------------------------------------------
    */

    if ($foto != $foto_lama) {

        $fileBaru = "../../assets/img/alat/" . $foto;

        if (file_exists($fileBaru)) {
            unlink($fileBaru);
        }
    }

    echo "<script>
            alert('Data gagal diperbarui.');
            window.location='edit.php?id=$id_alat';
          </script>";
}

$stmt->close();

$conn->close();

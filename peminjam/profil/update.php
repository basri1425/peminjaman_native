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

if ($_SESSION['level'] != 'Peminjam') {

    header("Location: ../../unauthorized.php");
    exit();

}

/*
|--------------------------------------------------------------------------
| Validasi Request
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] != 'POST') {

    header("Location: index.php");
    exit();

}

$idUser = $_SESSION['id_user'];

$nama_lengkap = trim($_POST['nama_lengkap']);

/*
|--------------------------------------------------------------------------
| Validasi Input
|--------------------------------------------------------------------------
*/

if ($nama_lengkap == "") {

    $_SESSION['error'] = "Nama lengkap wajib diisi.";

    header("Location: edit.php");
    exit();

}


/*
|--------------------------------------------------------------------------
| Update Profil
|--------------------------------------------------------------------------
*/

$sql = "

UPDATE users

SET

nama_lengkap = ?


WHERE

id_user = ?

";

$stmt = $conn->prepare($sql);

$stmt->bind_param(

    "si",

    $nama_lengkap,
    $idUser

);

if ($stmt->execute()) {

    tambahLog($conn, "Mengubah profil.");

    $_SESSION['success'] = "Profil berhasil diperbarui.";

} else {

    $_SESSION['error'] = "Profil gagal diperbarui.";

}

$stmt->close();

$conn->close();

header("Location: index.php");
exit();
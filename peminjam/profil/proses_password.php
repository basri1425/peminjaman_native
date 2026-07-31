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

    header("Location: ubah_password.php");
    exit();
}

$idUser = $_SESSION['id_user'];

$passwordLama = $_POST['password_lama'] ?? '';
$passwordBaru = $_POST['password_baru'] ?? '';
$konfirmasiPassword = $_POST['konfirmasi_password'] ?? '';

/*
|--------------------------------------------------------------------------
| Validasi Input
|--------------------------------------------------------------------------
*/

if (
    empty($passwordLama) ||
    empty($passwordBaru) ||
    empty($konfirmasiPassword)
) {

    $_SESSION['error'] = "Semua field wajib diisi.";

    header("Location: ubah_password.php");
    exit();
}

if (strlen($passwordBaru) < 8) {

    $_SESSION['error'] = "Password baru minimal 8 karakter.";

    header("Location: ubah_password.php");
    exit();
}

if ($passwordBaru != $konfirmasiPassword) {

    $_SESSION['error'] = "Konfirmasi password tidak sesuai.";

    header("Location: ubah_password.php");
    exit();
}

/*
|--------------------------------------------------------------------------
| Ambil Password Lama
|--------------------------------------------------------------------------
*/

$sql = "

SELECT

password

FROM users

WHERE id_user = ?

LIMIT 1

";

$stmt = $conn->prepare($sql);

$stmt->bind_param("i", $idUser);

$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows == 0) {

    $stmt->close();

    $_SESSION['error'] = "Data pengguna tidak ditemukan.";

    header("Location: index.php");
    exit();
}

$user = $result->fetch_assoc();

$stmt->close();

/*
|--------------------------------------------------------------------------
| Verifikasi Password Lama
|--------------------------------------------------------------------------
*/

if (!password_verify($passwordLama, $user['password'])) {

    $_SESSION['error'] = "Password lama yang Anda masukkan tidak benar.";

    header("Location: ubah_password.php");
    exit();
}

if (password_verify($passwordBaru, $user['password'])) {

    $_SESSION['error'] = "Password baru tidak boleh sama dengan password lama.";

    header("Location: ubah_password.php");
    exit();
}
/*
|--------------------------------------------------------------------------
| Hash Password Baru
|--------------------------------------------------------------------------
*/

$passwordHash = password_hash($passwordBaru, PASSWORD_DEFAULT);

/*
|--------------------------------------------------------------------------
| Update Password
|--------------------------------------------------------------------------
*/

$sql = "

UPDATE users

SET

password = ?

WHERE

id_user = ?

";

$stmt = $conn->prepare($sql);

$stmt->bind_param(

    "si",

    $passwordHash,
    $idUser

);

if ($stmt->execute()) {

    tambahLog($conn, "Mengubah password akun.");

    $_SESSION['success'] = "Password berhasil diperbarui.";
} else {

    $_SESSION['error'] = "Password gagal diperbarui.";
}

$stmt->close();

$conn->close();

header("Location: index.php");
exit();

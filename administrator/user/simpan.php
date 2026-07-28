<?php
/*
|--------------------------------------------------------------------------
| File        : simpan.php
| Folder      : administrator/user
| Fungsi      : Menyimpan Data User Baru
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
| Pastikan Form Dikirim Menggunakan POST
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

$nama_lengkap = trim($_POST['nama_lengkap']);
$username = trim($_POST['username']);
$password = $_POST['password'];
$level = $_POST['level'];
$status = $_POST['status'];

/*
|--------------------------------------------------------------------------
| Validasi Input
|--------------------------------------------------------------------------
*/

if (empty($nama_lengkap) || empty($username) || empty($password) || empty($level) || empty($status)) {
    header('Location: create.php');
    exit();
}

/*
|--------------------------------------------------------------------------
| Cek Username Sudah Digunakan
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    SELECT id_user
    FROM users
    WHERE username = ?
");

$stmt->bind_param('s', $username);

$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo "
        <script>
            alert('Username sudah digunakan.');
            window.location = 'create.php';
        </script>
    ";

    exit();
}

/*
|--------------------------------------------------------------------------
| Enkripsi Password
|--------------------------------------------------------------------------
*/

$passwordHash = password_hash($password, PASSWORD_DEFAULT);

/*
|--------------------------------------------------------------------------
| Simpan Data
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    INSERT INTO users
    (
        nama_lengkap,
        username,
        password,
        level,
        status,
        created_at,
        updated_at
    )
    VALUES
    (
        ?, ?, ?, ?, ?, NOW(), NOW()
    )
");

$stmt->bind_param('sssss', $nama_lengkap, $username, $passwordHash, $level, $status);

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
            alert('Data gagal disimpan.');

            window.location = 'create.php';
        </script>
    ";
}

$stmt->close();

$conn->close();

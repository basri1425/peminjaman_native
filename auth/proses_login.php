<?php
/*
|--------------------------------------------------------------------------
| File        : proses_login.php
| Folder      : auth
| Fungsi      : Memproses login pengguna
|--------------------------------------------------------------------------
*/

session_start();

// Memanggil koneksi database
require_once '../config/database.php';

// Memastikan form dikirim menggunakan metode POST
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header('Location: login.php');
    exit();
}

// Mengambil data dari form
$username = trim($_POST['username']);
$password = trim($_POST['password']);

// Validasi input
if ($username == '' || $password == '') {
    $_SESSION['pesan'] = 'Username dan Password wajib diisi.';

    header('Location: login.php');
    exit();
}

// Query menggunakan Prepared Statement
$sql = "SELECT * FROM users
        WHERE username = ?
        LIMIT 1";

$stmt = $conn->prepare($sql);

$stmt->bind_param('s', $username);

$stmt->execute();

$result = $stmt->get_result();

// Username ditemukan
if ($result->num_rows == 1) {
    $user = $result->fetch_assoc();

    // Cek status akun
    if ($user['status'] != 'Aktif') {
        $_SESSION['pesan'] = 'Akun tidak aktif.';

        header('Location: login.php');
        exit();
    }

    // Verifikasi password
    if (password_verify($password, $user['password'])) {
        // Membuat session
        $_SESSION['login'] = true;
        $_SESSION['id_user'] = $user['id_user'];
        $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['level'] = $user['level'];

        // Redirect sesuai level
        switch ($user['level']) {
            case 'Administrator':
                header('Location: ../administrator/dashboard.php');
                break;

            case 'Petugas':
                header('Location: ../petugas/dashboard.php');
                break;

            case 'Peminjam':
                header('Location: ../peminjam/dashboard.php');
                break;

            default:
                session_destroy();

                header('Location: login.php');
        }

        exit();
    } else {
        $_SESSION['pesan'] = 'Password yang Anda masukkan salah.';

        header('Location: login.php');
        exit();
    }
} else {
    $_SESSION['pesan'] = 'Username tidak ditemukan.';

    header('Location: login.php');
    exit();
}

<?php
/*
|--------------------------------------------------------------------------
| File        : session.php
| Folder      : config
| Fungsi      : Mengelola session dan membatasi hak akses pengguna
|--------------------------------------------------------------------------
*/

/*
|--------------------------------------------------------------------------
| Memulai Session
|--------------------------------------------------------------------------
| Session digunakan untuk menyimpan data login pengguna selama aplikasi
| dijalankan.
|--------------------------------------------------------------------------
*/

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

/*
|--------------------------------------------------------------------------
| Mengecek Status Login
|--------------------------------------------------------------------------
| Jika pengguna belum login, maka diarahkan ke halaman login.
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION['login'])) {

    header("Location: ../auth/login.php");
    exit();

}
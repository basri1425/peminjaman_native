<?php

/*
|--------------------------------------------------------------------------
| File        : index.php
| Folder      : Root Project
| Fungsi      : Halaman awal aplikasi
|--------------------------------------------------------------------------
|
| File ini berfungsi sebagai gerbang utama aplikasi.
| Ketika aplikasi pertama kali dijalankan, pengguna akan diarahkan
| menuju halaman login.
|
|--------------------------------------------------------------------------
*/

// Mengarahkan pengguna ke halaman login
header("Location: auth/login.php");

// Menghentikan proses eksekusi PHP
exit();
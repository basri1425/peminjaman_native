<?php

/*
|--------------------------------------------------------------------------
| Konfigurasi Database
|--------------------------------------------------------------------------
| File ini digunakan untuk membuat koneksi ke database MySQL
| menggunakan ekstensi MySQLi Object Oriented.
|--------------------------------------------------------------------------
*/

$host = 'localhost';
$username = 'root';
$password = '';
$database = 'db_peminjaman';

$conn = new mysqli($host, $username, $password, $database);

/*
|--------------------------------------------------------------------------
| Cek Koneksi
|--------------------------------------------------------------------------
*/

if ($conn->connect_error) {
    die('Koneksi Database Gagal : ' . $conn->connect_error);
}

/*
|--------------------------------------------------------------------------
| Mengatur Character Set
|--------------------------------------------------------------------------
*/

$conn->set_charset('utf8');

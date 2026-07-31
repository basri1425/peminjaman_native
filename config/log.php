<?php

/**
 * --------------------------------------------------------------------------
 * Fungsi Menyimpan Log Aktivitas
 * --------------------------------------------------------------------------
 *
 * @param mysqli $conn
 * @param string $aktivitas
 * @return bool
 */

function tambahLog(mysqli $conn, string $aktivitas): bool
{
    /*
    |--------------------------------------------------------------------------
    | Validasi Session
    |--------------------------------------------------------------------------
    */

    if (!isset($_SESSION['id_user'])) {
        return false;
    }

    $idUser = (int) $_SESSION['id_user'];

    /*
    |--------------------------------------------------------------------------
    | Mengambil IP Address
    |--------------------------------------------------------------------------
    */

    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ipAddress = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ipAddress = trim(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0]);
    } else {
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
    }

    /*
    |--------------------------------------------------------------------------
    | Query Simpan Log
    |--------------------------------------------------------------------------
    */

    $sql = "

        INSERT INTO log_aktivitas
        (
            id_user,
            aktivitas,
            waktu,
            ip_address
        )

        VALUES

        (
            ?,
            ?,
            NOW(),
            ?
        )

    ";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        return false;
    }

    $stmt->bind_param(
        'iss',
        $idUser,
        $aktivitas,
        $ipAddress,
    );

    $berhasil = $stmt->execute();
    $stmt->close();
    return $berhasil;
}

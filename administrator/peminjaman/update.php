<?php

/*
|--------------------------------------------------------------------------
| Konfigurasi Aplikasi
|--------------------------------------------------------------------------
*/

require '../../config/session.php';
require '../../config/database.php';

/*
|--------------------------------------------------------------------------
| Validasi Login
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION['login'])) {
    header('Location: ../../login.php');
    exit();
}

/*
|--------------------------------------------------------------------------
| Validasi Hak Akses
|--------------------------------------------------------------------------
*/

if ($_SESSION['level'] != 'Administrator') {
    echo "

    <script>

        alert('Anda tidak memiliki hak akses.');

        window.location='../../login.php';

    </script>

    ";

    exit();
}

/*
|--------------------------------------------------------------------------
| Validasi Method
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header('Location: index.php');
    exit();
}

/*
|--------------------------------------------------------------------------
| Validasi Data POST
|--------------------------------------------------------------------------
*/

if (empty($_POST['id_peminjaman']) || empty($_POST['id_user']) || empty($_POST['tanggal_pinjam']) || empty($_POST['tanggal_kembali']) || !isset($_POST['id_alat']) || !isset($_POST['jumlah'])) {
    echo "

    <script>

        alert('Data transaksi belum lengkap.');

        window.location='index.php';

    </script>

    ";

    exit();
}

/*
|--------------------------------------------------------------------------
| Menyiapkan Variabel
|--------------------------------------------------------------------------
*/

$idPeminjaman = (int) $_POST['id_peminjaman'];
$idUser = (int) $_POST['id_user'];
$tanggalPinjam = trim($_POST['tanggal_pinjam']);
$tanggalKembali = trim($_POST['tanggal_kembali']);

$idAlat = $_POST['id_alat'];
$jumlah = $_POST['jumlah'];

/*
|--------------------------------------------------------------------------
| Validasi Jumlah Data
|--------------------------------------------------------------------------
*/

if (count($idAlat) != count($jumlah)) {
    echo "

    <script>

        alert('Data detail transaksi tidak valid.');

        history.back();

    </script>

    ";

    exit();
}

/*
|--------------------------------------------------------------------------
| Validasi Minimal Satu Alat
|--------------------------------------------------------------------------
*/

if (count($idAlat) == 0) {
    echo "

    <script>

        alert('Minimal terdapat satu alat.');

        history.back();

    </script>

    ";

    exit();
}

/*
|--------------------------------------------------------------------------
| Validasi Tanggal
|--------------------------------------------------------------------------
*/

if ($tanggalKembali < $tanggalPinjam) {
    echo "

    <script>

        alert('Tanggal kembali tidak boleh lebih awal dari tanggal pinjam.');

        history.back();

    </script>

    ";

    exit();
}

/*
|--------------------------------------------------------------------------
| Normalisasi Data Detail
|--------------------------------------------------------------------------
*/

$detailBaru = [];

for ($i = 0; $i < count($idAlat); $i++) {
    $alat = (int) $idAlat[$i];
    $qty = (int) $jumlah[$i];

    if ($alat <= 0 || $qty <= 0) {
        continue;
    }

    /*
    |--------------------------------------------------------------------------
    | Cegah ID Alat Duplikat
    |--------------------------------------------------------------------------
    */

    if (isset($detailBaru[$alat])) {
        echo "

        <script>

            alert('Terdapat alat yang dipilih lebih dari satu kali.');

            history.back();

        </script>

        ";

        exit();
    }

    $detailBaru[$alat] = $qty;
}

/*
|--------------------------------------------------------------------------
| Validasi Hasil Normalisasi
|--------------------------------------------------------------------------
*/

if (count($detailBaru) == 0) {
    echo "

    <script>

        alert('Detail transaksi tidak valid.');

        history.back();

    </script>

    ";

    exit();
}

/*
|--------------------------------------------------------------------------
| Mulai Database Transaction
|--------------------------------------------------------------------------
*/

$conn->begin_transaction();

try {
    /*
    |--------------------------------------------------------------------------
    | Update Header Peminjaman
    |--------------------------------------------------------------------------
    */

    $sql = "
    UPDATE peminjaman
    SET

        id_user = ?,
        tanggal_pinjam = ?,
        tanggal_kembali = ?,
        updated_at = NOW()

    WHERE id_peminjaman = ?
    ";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        throw new Exception('Gagal mempersiapkan update transaksi.');
    }

    $stmt->bind_param(
        'issi',

        $idUser,
        $tanggalPinjam,
        $tanggalKembali,
        $idPeminjaman,
    );

    if (!$stmt->execute()) {
        throw new Exception('Gagal memperbarui data transaksi.');
    }

    $stmt->close();

    /*
    |--------------------------------------------------------------------------
    | Mengambil Detail Lama
    |--------------------------------------------------------------------------
    */

    $sql = "
    SELECT

        id_alat,
        jumlah

    FROM detail_peminjaman

    WHERE id_peminjaman = ?
    ";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        throw new Exception('Gagal mengambil detail transaksi.');
    }

    $stmt->bind_param(
        'i',

        $idPeminjaman,
    );

    $stmt->execute();

    $result = $stmt->get_result();

    /*
    |--------------------------------------------------------------------------
    | Mengembalikan Stok Lama
    |--------------------------------------------------------------------------
    */

    while ($row = $result->fetch_assoc()) {
        $sqlRestore = "
        UPDATE alat
        SET

            stok = stok + ?

        WHERE id_alat = ?
        ";

        $restore = $conn->prepare($sqlRestore);

        if (!$restore) {
            throw new Exception('Gagal mempersiapkan update stok.');
        }

        $restore->bind_param(
            'ii',

            $row['jumlah'],
            $row['id_alat'],
        );

        if (!$restore->execute()) {
            throw new Exception('Gagal mengembalikan stok alat.');
        }

        $restore->close();
    }

    $stmt->close();

    /*
    |--------------------------------------------------------------------------
    | Menghapus Detail Lama
    |--------------------------------------------------------------------------
    */

    $sql = "
    DELETE FROM detail_peminjaman
    WHERE id_peminjaman = ?
    ";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        throw new Exception('Gagal menghapus detail transaksi.');
    }

    $stmt->bind_param(
        'i',

        $idPeminjaman,
    );

    if (!$stmt->execute()) {
        throw new Exception('Gagal menghapus detail transaksi.');
    }

    $stmt->close();

    /*
    |--------------------------------------------------------------------------
    | Menyimpan Detail Baru
    |--------------------------------------------------------------------------
    */

    foreach ($detailBaru as $idAlat => $qty) {
        /*
        |--------------------------------------------------------------------------
        | Cek Stok Terbaru
        |--------------------------------------------------------------------------
        */

        $sql = "
        SELECT

            stok

        FROM alat

        WHERE id_alat = ?

        LIMIT 1
        ";

        $stmt = $conn->prepare($sql);

        if (!$stmt) {
            throw new Exception('Gagal mempersiapkan pengecekan stok.');
        }

        $stmt->bind_param(
            'i',

            $idAlat,
        );

        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows == 0) {
            throw new Exception('Data alat tidak ditemukan.');
        }

        $alat = $result->fetch_assoc();

        $stmt->close();

        /*
        |--------------------------------------------------------------------------
        | Validasi Stok
        |--------------------------------------------------------------------------
        */

        if ($qty > $alat['stok']) {
            throw new Exception('Stok alat tidak mencukupi.');
        }

        /*
        |--------------------------------------------------------------------------
        | Simpan Detail Baru
        |--------------------------------------------------------------------------
        */

        $sql = "
        INSERT INTO detail_peminjaman
        (

            id_peminjaman,
            id_alat,
            jumlah

        )

        VALUES
        (

            ?,
            ?,
            ?

        )
        ";

        $stmt = $conn->prepare($sql);

        if (!$stmt) {
            throw new Exception('Gagal menyimpan detail transaksi.');
        }

        $stmt->bind_param(
            'iii',

            $idPeminjaman,
            $idAlat,
            $qty,
        );

        if (!$stmt->execute()) {
            throw new Exception('Gagal menyimpan detail transaksi.');
        }

        $stmt->close();

        /*
        |--------------------------------------------------------------------------
        | Kurangi Stok
        |--------------------------------------------------------------------------
        */

        $sql = "
        UPDATE alat
        SET

            stok = stok - ?

        WHERE id_alat = ?
        ";

        $stmt = $conn->prepare($sql);

        if (!$stmt) {
            throw new Exception('Gagal mempersiapkan update stok.');
        }

        $stmt->bind_param(
            'ii',

            $qty,
            $idAlat,
        );

        if (!$stmt->execute()) {
            throw new Exception('Gagal memperbarui stok alat.');
        }

        $stmt->close();
    }

    /*
    |--------------------------------------------------------------------------
    | Commit Transaction
    |--------------------------------------------------------------------------
    */

    $conn->commit();

    echo "

    <script>

        alert('Transaksi berhasil diperbarui.');

        window.location='detail.php?id=$idPeminjaman';

    </script>

    ";

    exit();
} catch (Exception $e) {
    /*
    |--------------------------------------------------------------------------
    | Rollback Transaction
    |--------------------------------------------------------------------------
    */

    $conn->rollback();

    echo "

    <script>

        alert('" .
        $e->getMessage() .
        "');

        history.back();

    </script>

    ";
}

$conn->close();

?>

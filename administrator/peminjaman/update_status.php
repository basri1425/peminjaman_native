<?php
require_once '../../config/session.php';
require_once '../../config/database.php';

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
| Koneksi Database
|--------------------------------------------------------------------------
*/

// require '../../database/koneksi.php';

/*
|--------------------------------------------------------------------------
| Validasi Parameter
|--------------------------------------------------------------------------
*/

if (!isset($_GET['id']) || !isset($_GET['status'])) {
    echo "

    <script>

        alert('Parameter tidak lengkap.');

        window.location='index.php';

    </script>

    ";

    exit();
}

$idPeminjaman = (int) $_GET['id'];
$statusBaru = trim($_GET['status']);

/*
|--------------------------------------------------------------------------
| Mulai Database Transaction
|--------------------------------------------------------------------------
*/

$conn->begin_transaction();

try {
    /*
    |--------------------------------------------------------------------------
    | Update Status Peminjaman
    |--------------------------------------------------------------------------
    */

    $stmt = $conn->prepare("
    UPDATE peminjaman
    SET
        status = ?,
        updated_at = NOW()
    WHERE id_peminjaman = ?
    ");

    if (!$stmt) {
        throw new Exception('Gagal mempersiapkan query.');
    }

    $stmt->bind_param('si', $statusBaru, $idPeminjaman);

    if (!$stmt->execute()) {
        throw new Exception('Gagal memperbarui status transaksi.');
    }

    $stmt->close();

    /*
    |--------------------------------------------------------------------------
    | Commit Transaction
    |--------------------------------------------------------------------------
    */

    $conn->commit();

    echo "

    <script>

        alert('Status transaksi berhasil diperbarui.');

        window.location='index.php';

    </script>

    ";
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

        window.location='index.php';

    </script>

    ";
}

$conn->close();

?>

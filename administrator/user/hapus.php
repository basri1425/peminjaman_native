<?php
/*
|--------------------------------------------------------------------------
| File        : hapus.php
| Folder      : administrator/user
| Fungsi      : Menghapus Data User
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
| Validasi Parameter ID
|--------------------------------------------------------------------------
*/

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: index.php');
    exit();
}

$id_user = (int) $_GET['id'];

/*
|--------------------------------------------------------------------------
| Cegah Menghapus Akun Sendiri
|--------------------------------------------------------------------------
*/

if ($id_user == $_SESSION['id_user']) {
    echo "
    <script>

        alert('Akun yang sedang digunakan tidak dapat dihapus.');

        window.location='index.php';

    </script>
    ";

    exit();
}

/*
|--------------------------------------------------------------------------
| Pastikan Data Ada
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    SELECT id_user
    FROM users
    WHERE id_user = ?
");

$stmt->bind_param('i', $id_user);

$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows == 0) {
    header('Location: index.php');
    exit();
}

$stmt->close();

/*
|--------------------------------------------------------------------------
| Hapus Data
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    DELETE FROM users
    WHERE id_user = ?
");

$stmt->bind_param('i', $id_user);

/*
|--------------------------------------------------------------------------
| Eksekusi Query
|--------------------------------------------------------------------------
*/

if ($stmt->execute()) {
    header('Location: index.php?pesan=hapus');
} else {
    echo "
    <script>

        alert('Data gagal dihapus.');

        window.location='index.php';

    </script>
    ";
}

$stmt->close();

$conn->close();

?>

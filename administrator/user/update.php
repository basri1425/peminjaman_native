<?php
/*
|--------------------------------------------------------------------------
| File        : update.php
| Folder      : administrator/user
| Fungsi      : Memperbarui Data User
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
| Pastikan Request POST
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

$id_user = (int) $_POST['id_user'];
$nama_lengkap = trim($_POST['nama_lengkap']);
$username = trim($_POST['username']);
$password = trim($_POST['password']);
$level = trim($_POST['level']);
$status = trim($_POST['status']);

/*
|--------------------------------------------------------------------------
| Validasi Input
|--------------------------------------------------------------------------
*/

if (empty($id_user) || empty($nama_lengkap) || empty($username) || empty($level) || empty($status)) {
    header('Location: edit.php?id=' . $id_user);
    exit();
}

/*
|--------------------------------------------------------------------------
| Cek Username Selain User Yang Sedang Diedit
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    SELECT id_user
    FROM users
    WHERE username = ?
    AND id_user <> ?
");

$stmt->bind_param('si', $username, $id_user);

$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo "
    <script>

        alert('Username sudah digunakan.');

        window.location='edit.php?id=$id_user';

    </script>
    ";

    exit();
}

$stmt->close();

/*
|--------------------------------------------------------------------------
| Password Diubah
|--------------------------------------------------------------------------
*/

if (!empty($password)) {
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("
        UPDATE users
        SET
            nama_lengkap = ?,
            username = ?,
            password = ?,
            level = ?,
            status = ?,
            updated_at = NOW()
        WHERE id_user = ?
    ");

    $stmt->bind_param('sssssi', $nama_lengkap, $username, $passwordHash, $level, $status, $id_user);
} else {
    /*
    --------------------------------------------------------------------------
    | Password Tidak Diubah
    --------------------------------------------------------------------------
    */

    $stmt = $conn->prepare("
        UPDATE users
        SET
            nama_lengkap = ?,
            username = ?,
            level = ?,
            status = ?,
            updated_at = NOW()
        WHERE id_user = ?
    ");

    $stmt->bind_param('ssssi', $nama_lengkap, $username, $level, $status, $id_user);
}

/*
|--------------------------------------------------------------------------
| Eksekusi Update
|--------------------------------------------------------------------------
*/

if ($stmt->execute()) {
    header('Location: index.php?pesan=update');
} else {
    echo "
    <script>

        alert('Data gagal diperbarui.');

        window.location='edit.php?id=$id_user';

    </script>
    ";
}

$stmt->close();

$conn->close();

?>

<?php
/*
|--------------------------------------------------------------------------
| File        : edit.php
| Folder      : administrator/user
| Fungsi      : Form Edit Data User
|--------------------------------------------------------------------------
*/

require_once '../../config/session.php';
require_once '../../config/database.php';

if ($_SESSION['level'] != 'Administrator') {
    header('Location: ../../auth/login.php');
    exit();
}

/*
|--------------------------------------------------------------------------
| Validasi ID User
|--------------------------------------------------------------------------
*/

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: index.php');
    exit();
}

$id_user = (int) $_GET['id'];

/*
|--------------------------------------------------------------------------
| Mengambil Data User
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    SELECT
        id_user,
        nama_lengkap,
        username,
        level,
        status
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

$user = $result->fetch_assoc();

$title = 'Edit User';

require_once '../../layouts/header.php';
require_once '../../layouts/navbar.php';
require_once '../../layouts/sidebar.php';
?>

<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h3>

                <i class="bi bi-pencil-square"></i>

                Edit User

            </h3>

            <p class="text-muted mb-0">

                Perbarui informasi pengguna.

            </p>

        </div>

        <a href="index.php" class="btn btn-secondary">

            <i class="bi bi-arrow-left"></i>

            Kembali

        </a>

    </div>

    <div class="card shadow-sm">

        <div class="card-header bg-warning">

            Form Edit User

        </div>

        <div class="card-body">

            <form action="update.php" method="POST">

                <input type="hidden" name="id_user" value="<?= $user['id_user'] ?>">

                <div class="mb-3">

                    <label class="form-label">

                        Nama Lengkap

                    </label>

                    <input type="text" name="nama_lengkap" class="form-control" maxlength="100" required
                        value="<?= htmlspecialchars($user['nama_lengkap']) ?>">

                </div>

                <div class="mb-3">

                    <label class="form-label">

                        Username

                    </label>

                    <input type="text" name="username" class="form-control" maxlength="50" required
                        value="<?= htmlspecialchars($user['username']) ?>">

                </div>

                <div class="mb-3">

                    <label class="form-label">

                        Password Baru

                    </label>

                    <input type="password" name="password" class="form-control">

                    <small class="text-muted">

                        Kosongkan apabila password tidak ingin diubah.

                    </small>

                </div>

                <div class="mb-3">

                    <label class="form-label">

                        Level

                    </label>

                    <select name="level" class="form-select" required>

                        <option value="Administrator" <?= $user['level'] == 'Administrator' ? 'selected' : '' ?>>

                            Administrator

                        </option>

                        <option value="Petugas" <?= $user['level'] == 'Petugas' ? 'selected' : '' ?>>

                            Petugas

                        </option>

                        <option value="Peminjam" <?= $user['level'] == 'Peminjam' ? 'selected' : '' ?>>

                            Peminjam

                        </option>

                    </select>

                </div>

                <div class="mb-3">

                    <label class="form-label">

                        Status

                    </label>

                    <select name="status" class="form-select" required>

                        <option value="Aktif" <?= $user['status'] == 'Aktif' ? 'selected' : '' ?>>

                            Aktif

                        </option>

                        <option value="Tidak Aktif" <?= $user['status'] == 'Tidak Aktif' ? 'selected' : '' ?>>

                            Tidak Aktif

                        </option>

                    </select>

                </div>

                <hr>

                <button type="submit" class="btn btn-warning">

                    <i class="bi bi-save"></i>

                    Update

                </button>

                <a href="index.php" class="btn btn-secondary">

                    <i class="bi bi-x-circle"></i>

                    Batal

                </a>

            </form>

        </div>

    </div>

</div>

<?php

$stmt->close();
$conn->close();

require_once '../../layouts/footer.php';

?>

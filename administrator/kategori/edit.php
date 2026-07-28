<?php
/*
|--------------------------------------------------------------------------
| File        : edit.php
| Folder      : administrator/kategori
| Fungsi      : Form Edit Data Kategori
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

$id_kategori = (int) $_GET['id'];

/*
|--------------------------------------------------------------------------
| Mengambil Data Kategori
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    SELECT
        id_kategori,
        nama_kategori
    FROM kategori
    WHERE id_kategori = ?
");

$stmt->bind_param('i', $id_kategori);

$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows == 0) {
    header('Location: index.php');
    exit();
}

$kategori = $result->fetch_assoc();

$title = 'Edit Kategori';

require_once '../../layouts/header.php';
require_once '../../layouts/navbar.php';
require_once '../../layouts/sidebar.php';

?>

<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h3>

                <i class="bi bi-pencil-square"></i>

                Edit Kategori

            </h3>

            <p class="text-muted mb-0">

                Perbarui data kategori alat.

            </p>

        </div>

        <a href="index.php" class="btn btn-secondary">

            <i class="bi bi-arrow-left"></i>

            Kembali

        </a>

    </div>

    <div class="card shadow-sm">

        <div class="card-header bg-warning">

            Form Edit Kategori

        </div>

        <div class="card-body">

            <form action="update.php" method="POST">

                <input type="hidden" name="id_kategori" value="<?= $kategori['id_kategori'] ?>">

                <div class="mb-3">

                    <label class="form-label">

                        Nama Kategori
                        <span class="text-danger">*</span>

                    </label>

                    <input type="text" name="nama_kategori" class="form-control" maxlength="100"
                        value="<?= htmlspecialchars($kategori['nama_kategori']) ?>" required autofocus>

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

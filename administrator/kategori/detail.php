<?php
/*
|--------------------------------------------------------------------------
| File        : detail.php
| Folder      : administrator/kategori
| Fungsi      : Menampilkan Detail Data Kategori
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

$title = 'Detail Kategori';

require_once '../../layouts/header.php';
require_once '../../layouts/navbar.php';
require_once '../../layouts/sidebar.php';
?>

<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h3>

                <i class="bi bi-tags-fill"></i>

                Detail Kategori

            </h3>

            <p class="text-muted mb-0">

                Informasi lengkap data kategori alat.

            </p>

        </div>

        <a href="index.php" class="btn btn-secondary">

            <i class="bi bi-arrow-left"></i>

            Kembali

        </a>

    </div>

    <div class="card shadow-sm">

        <div class="card-header bg-primary text-white">

            Informasi Kategori

        </div>

        <div class="card-body">

            <table class="table table-bordered">

                <tr>

                    <th width="250">

                        ID Kategori

                    </th>

                    <td>

                        <?= htmlspecialchars($kategori['id_kategori']) ?>

                    </td>

                </tr>

                <tr>

                    <th>

                        Nama Kategori

                    </th>

                    <td>

                        <?= htmlspecialchars($kategori['nama_kategori']) ?>

                    </td>

                </tr>

            </table>

        </div>

        <div class="card-footer">

            <a href="edit.php?id=<?= $kategori['id_kategori'] ?>" class="btn btn-warning">

                <i class="bi bi-pencil-square"></i>

                Edit

            </a>

            <a href="index.php" class="btn btn-secondary">

                <i class="bi bi-arrow-left"></i>

                Kembali

            </a>

        </div>

    </div>

</div>

<?php

$stmt->close();
$conn->close();

require_once '../../layouts/footer.php';

?>

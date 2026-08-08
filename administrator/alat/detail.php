<?php
/*
|--------------------------------------------------------------------------
| File        : detail.php
| Folder      : administrator/alat
| Fungsi      : Menampilkan Detail Data Alat
|--------------------------------------------------------------------------
*/

require_once "../../config/session.php";
require_once "../../config/database.php";

/*
|--------------------------------------------------------------------------
| Hak Akses
|--------------------------------------------------------------------------
*/

if ($_SESSION['level'] != "Administrator") {
    header("Location: ../../auth/login.php");
    exit();
}

/*
|--------------------------------------------------------------------------
| Validasi Parameter ID
|--------------------------------------------------------------------------
*/

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$id_alat = (int) $_GET['id'];

/*
|--------------------------------------------------------------------------
| Mengambil Data Alat
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
SELECT
    a.id_alat,
    a.nama_alat,
    a.stok,
    a.kondisi,
    a.lokasi,
    a.foto,
    k.nama_kategori
FROM alat a
INNER JOIN kategori k
ON a.id_kategori = k.id_kategori
WHERE a.id_alat = ?
");

$stmt->bind_param("i", $id_alat);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows == 0) {
    header("Location: index.php");
    exit();
}

$alat = $result->fetch_assoc();
$title = "Detail Alat";

require_once "../../layouts/header.php";
require_once "../../layouts/navbar.php";
require_once "../../layouts/sidebar.php";

?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3>
                <i class="bi bi-eye"></i>
                Detail Data Alat
            </h3>
            <p class="text-muted mb-0">
                Informasi lengkap data alat.
            </p>
        </div>
        <a href="index.php" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i>
            Kembali
        </a>
    </div>
    <div class="card shadow-sm">
        <div class="card-header bg-info text-white">
            Detail Alat
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4 text-center">
                    <?php
                    $foto = "../../assets/img/alat/" . $alat['foto'];
                    if (!empty($alat['foto']) && file_exists($foto)) {
                    ?>
                        <img
                            src="<?= $foto; ?>"
                            class="img-fluid img-thumbnail"
                            style="max-height:300px;">
                    <?php } else { ?>
                        <div class="border rounded p-5 text-muted">
                            <i class="bi bi-image fs-1"></i>
                            <br>
                            Foto Tidak Tersedia
                        </div>
                    <?php } ?>
                </div>
                <div class="col-md-8">
                    <table class="table table-bordered">
                        <tr>
                            <th width="220">ID Alat</th>
                            <td><?= htmlspecialchars($alat['id_alat']); ?></td>
                        </tr>
                        <tr>
                            <th>Nama Alat</th>
                            <td><?= htmlspecialchars($alat['nama_alat']); ?></td>
                        </tr>
                        <tr>
                            <th>Kategori</th>
                            <td><?= htmlspecialchars($alat['nama_kategori']); ?></td>
                        </tr>
                        <tr>
                            <th>Stok</th>
                            <td><?= htmlspecialchars($alat['stok']); ?></td>
                        </tr>
                        <tr>
                            <th>Kondisi</th>
                            <td>
                                <?php
                                switch ($alat['kondisi']) {
                                    case "Baik":
                                        echo '<span class="badge bg-success">Baik</span>';
                                        break;
                                    case "Rusak Ringan":
                                        echo '<span class="badge bg-warning text-dark">Rusak Ringan</span>';
                                        break;
                                    default:
                                        echo '<span class="badge bg-danger">Rusak Berat</span>';
                                }
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <th>Lokasi</th>
                            <td><?= htmlspecialchars($alat['lokasi']); ?></td>
                        </tr>
                    </table>
                    <div class="mt-3">
                        <a
                            href="edit.php?id=<?= $alat['id_alat']; ?>"
                            class="btn btn-warning">
                            <i class="bi bi-pencil-square"></i>
                            Edit
                        </a>
                        <a
                            href="index.php"
                            class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i>
                            Kembali
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php

$stmt->close();
$conn->close();
require_once "../../layouts/footer.php";
?>
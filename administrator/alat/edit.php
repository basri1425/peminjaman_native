<?php
/*
|--------------------------------------------------------------------------
| File        : edit.php
| Folder      : administrator/alat
| Fungsi      : Form Edit Data Alat
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
| Validasi ID
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
    id_alat,
    id_kategori,
    nama_alat,
    stok,
    kondisi,
    lokasi,
    foto
FROM alat
WHERE id_alat = ?
");

$stmt->bind_param("i", $id_alat);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows == 0) {
    header("Location: index.php");
    exit();
}

$alat = $result->fetch_assoc();

$stmt->close();

/*
|--------------------------------------------------------------------------
| Mengambil Data Kategori
|--------------------------------------------------------------------------
*/

$queryKategori = "
SELECT
    id_kategori,
    nama_kategori
FROM kategori
ORDER BY nama_kategori ASC
";

$resultKategori = $conn->query($queryKategori);

$title = "Edit Alat";

require_once "../../layouts/header.php";
require_once "../../layouts/navbar.php";
require_once "../../layouts/sidebar.php";

?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3>
                <i class="bi bi-pencil-square"></i>
                Edit Data Alat
            </h3>
            <p class="text-muted mb-0">
                Perbarui informasi data alat.
            </p>
        </div>
        <a href="index.php" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i>
            Kembali
        </a>
    </div>
    <div class="card shadow-sm">
        <div class="card-header bg-warning">
            Form Edit Alat
        </div>
        <div class="card-body">
            <form
                action="update.php"
                method="POST"
                enctype="multipart/form-data">
                <input
                    type="hidden"
                    name="id_alat"
                    value="<?= $alat['id_alat']; ?>">
                <input
                    type="hidden"
                    name="foto_lama"
                    value="<?= htmlspecialchars($alat['foto']); ?>">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">
                            Kategori
                            <span class="text-danger">*</span>
                        </label>
                        <select
                            name="id_kategori"
                            class="form-select"
                            required>
                            <?php while ($kategori = $resultKategori->fetch_assoc()) : ?>
                                <option
                                    value="<?= $kategori['id_kategori']; ?>"
                                    <?= ($kategori['id_kategori'] == $alat['id_kategori']) ? 'selected' : ''; ?>>
                                    <?= htmlspecialchars($kategori['nama_kategori']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">
                            Nama Alat
                        </label>
                        <input
                            type="text"
                            name="nama_alat"
                            class="form-control"
                            maxlength="150"
                            value="<?= htmlspecialchars($alat['nama_alat']); ?>"
                            required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">
                            Stok
                        </label>
                        <input
                            type="number"
                            name="stok"
                            class="form-control"
                            min="0"
                            value="<?= htmlspecialchars($alat['stok']); ?>"
                            required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">
                            Kondisi
                        </label>
                        <select
                            name="kondisi"
                            class="form-select"
                            required>
                            <option value="Baik"
                                <?= ($alat['kondisi'] == "Baik") ? "selected" : ""; ?>>
                                Baik
                            </option>
                            <option value="Rusak Ringan"
                                <?= ($alat['kondisi'] == "Rusak Ringan") ? "selected" : ""; ?>>
                                Rusak Ringan
                            </option>
                            <option value="Rusak Berat"
                                <?= ($alat['kondisi'] == "Rusak Berat") ? "selected" : ""; ?>>
                                Rusak Berat
                            </option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">
                            Lokasi
                        </label>
                        <input
                            type="text"
                            name="lokasi"
                            class="form-control"
                            maxlength="100"
                            value="<?= htmlspecialchars($alat['lokasi']); ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">
                            Foto Saat Ini
                        </label>
                        <br>
                        <?php
                        $foto = "../../assets/img/alat/" . $alat['foto'];
                        if (!empty($alat['foto']) && file_exists($foto)) {
                        ?>
                            <img
                                src="<?= $foto; ?>"
                                width="180"
                                class="img-thumbnail">
                        <?php
                        } else {
                            echo "<p class='text-muted'>Tidak ada foto.</p>";
                        }
                        ?>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">
                            Ganti Foto
                        </label>
                        <input
                            type="file"
                            name="foto"
                            class="form-control"
                            accept=".jpg,.jpeg,.png">
                        <small class="text-muted">
                            Kosongkan apabila foto tidak ingin diganti.
                        </small>
                    </div>
                </div>
                <hr>
                <button
                    type="submit"
                    class="btn btn-warning">
                    <i class="bi bi-save"></i>
                    Update
                </button>
                <a
                    href="index.php"
                    class="btn btn-secondary">
                    <i class="bi bi-x-circle"></i>
                    Batal
                </a>
            </form>
        </div>
    </div>
</div>
<?php
$conn->close();
require_once "../../layouts/footer.php";
?>
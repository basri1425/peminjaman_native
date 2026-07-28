<?php
/*
|--------------------------------------------------------------------------
| File        : create.php
| Folder      : administrator/alat
| Fungsi      : Form Tambah Data Alat
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

$title = 'Tambah Alat';

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

require_once '../../layouts/header.php';
require_once '../../layouts/navbar.php';
require_once '../../layouts/sidebar.php';
?>

<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h3>

                <i class="bi bi-plus-circle"></i>

                Tambah Data Alat

            </h3>

            <p class="text-muted mb-0">

                Tambahkan data alat baru ke dalam sistem.

            </p>

        </div>

        <a href="index.php" class="btn btn-secondary">

            <i class="bi bi-arrow-left"></i>

            Kembali

        </a>

    </div>

    <div class="card shadow-sm">

        <div class="card-header bg-primary text-white">

            Form Tambah Alat

        </div>

        <div class="card-body">

            <form action="simpan.php" method="POST" enctype="multipart/form-data">

                <div class="row">

                    <div class="col-md-6 mb-3">

                        <label class="form-label">

                            Kategori

                            <span class="text-danger">*</span>

                        </label>

                        <select name="id_kategori" class="form-select" required>

                            <option value="">

                                -- Pilih Kategori --

                            </option>

                            <?php while($kategori = $resultKategori->fetch_assoc()) : ?>

                            <option value="<?= $kategori['id_kategori'] ?>">

                                <?= htmlspecialchars($kategori['nama_kategori']) ?>

                            </option>

                            <?php endwhile; ?>

                        </select>

                    </div>

                    <div class="col-md-6 mb-3">

                        <label class="form-label">

                            Nama Alat

                            <span class="text-danger">*</span>

                        </label>

                        <input type="text" name="nama_alat" class="form-control" maxlength="150" required>

                    </div>

                    <div class="col-md-4 mb-3">

                        <label class="form-label">

                            Stok

                            <span class="text-danger">*</span>

                        </label>

                        <input type="number" name="stok" class="form-control" min="0" value="0"
                            required>

                    </div>

                    <div class="col-md-4 mb-3">

                        <label class="form-label">

                            Kondisi

                            <span class="text-danger">*</span>

                        </label>

                        <select name="kondisi" class="form-select" required>

                            <option value="Baik">

                                Baik

                            </option>

                            <option value="Rusak Ringan">

                                Rusak Ringan

                            </option>

                            <option value="Rusak Berat">

                                Rusak Berat

                            </option>

                        </select>

                    </div>

                    <div class="col-md-4 mb-3">

                        <label class="form-label">

                            Lokasi

                        </label>

                        <input type="text" name="lokasi" class="form-control" maxlength="100">

                    </div>

                    <div class="col-md-12 mb-3">

                        <label class="form-label">

                            Foto Alat

                        </label>

                        <input type="file" name="foto" class="form-control" accept=".jpg,.jpeg,.png">

                        <small class="text-muted">

                            Format: JPG, JPEG, PNG. Maksimal 2 MB.

                        </small>

                    </div>

                </div>

                <hr>

                <button type="submit" class="btn btn-primary">

                    <i class="bi bi-save"></i>

                    Simpan

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

$conn->close();

require_once '../../layouts/footer.php';

?>

<?php
/*
|--------------------------------------------------------------------------
| File        : index.php
| Folder      : administrator/kategori
| Fungsi      : Menampilkan Daftar Kategori
|--------------------------------------------------------------------------
*/

require_once '../../config/session.php';
require_once '../../config/database.php';

if ($_SESSION['level'] != 'Administrator') {
    header('Location: ../../auth/login.php');
    exit();
}

$title = 'Master Kategori';

$query = "
    SELECT
        id_kategori,
        nama_kategori
    FROM kategori
    ORDER BY nama_kategori ASC
";

$result = $conn->query($query);

require_once '../../layouts/header.php';
require_once '../../layouts/navbar.php';
require_once '../../layouts/sidebar.php';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3>
                <i class="bi bi-tags-fill"></i>
                Master Kategori
            </h3>
            <p class="text-muted mb-0">
                Kelola data kategori alat.
            </p>
        </div>
        <a href="tambah.php" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i>
            Tambah Kategori
        </a>
    </div>
    <?php if (isset($_GET['pesan'])) : ?>
    <?php if ($_GET['pesan'] == "sukses") : ?>
    <div class="alert alert-success alert-dismissible fade show">
        Data kategori berhasil disimpan.
        <button class="btn-close" data-bs-dismiss="alert">
        </button>
    </div>
    <?php elseif ($_GET['pesan'] == "update") : ?>
    <div class="alert alert-warning alert-dismissible fade show">
        Data kategori berhasil diperbarui.
        <button class="btn-close" data-bs-dismiss="alert">
        </button>
    </div>
    <?php elseif ($_GET['pesan'] == "hapus") : ?>
    <div class="alert alert-danger alert-dismissible fade show">
        Data kategori berhasil dihapus.
tambah
        <button class="btn-close" data-bs-dismiss="alert">
        </button>
    </div>
    <?php endif; ?>
    <?php endif; ?>
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            Daftar Kategori
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th width="60">No</th>
                            <th>Nama Kategori</th>
                            <th width="170">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no = 1;
                        if ($result->num_rows > 0) :
                            while ($row = $result->fetch_assoc()) :
                        ?>
                        <tr>
                            <td class="text-center">
                                <?= $no++ ?>
                            </td>
                            <td>
                                <?= htmlspecialchars($row['nama_kategori']) ?>
                            </td>
                            <td class="text-center">
                                <a href="detail.php?id=<?= $row['id_kategori'] ?>" class="btn btn-info btn-sm">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="edit.php?id=<?= $row['id_kategori'] ?>" class="btn btn-warning btn-sm">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                                <a href="hapus.php?id=<?= $row['id_kategori'] ?>" class="btn btn-danger btn-sm"
                                    onclick="return confirm('Yakin ingin menghapus kategori ini?')">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </td>
                        </tr>
                        <?php
                            endwhile;
                        else:
                        ?>
                        <tr>
                            <td colspan="4" class="text-center">
                                Belum ada data kategori.
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php
$conn->close();
require_once '../../layouts/footer.php';
?>

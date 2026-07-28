<?php
/*
|--------------------------------------------------------------------------
| File        : index.php
| Folder      : administrator/alat
| Fungsi      : Menampilkan Data Master Alat
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

$title = 'Master Alat';

/*
|--------------------------------------------------------------------------
| Mengambil Data Alat
|--------------------------------------------------------------------------
*/

$query = "
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
ORDER BY a.nama_alat ASC
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
                <i class="bi bi-tools"></i>
                Master Alat
            </h3>

            <p class="text-muted mb-0">
                Kelola seluruh data alat yang tersedia untuk dipinjam.
            </p>

        </div>

        <a href="tambah.php" class="btn btn-primary">

            <i class="bi bi-plus-circle"></i>

            Tambah Alat

        </a>

    </div>

    <?php if(isset($_GET['pesan'])) : ?>

    <?php if($_GET['pesan']=="sukses") : ?>

    <div class="alert alert-success alert-dismissible fade show">

        Data alat berhasil ditambahkan.

        <button class="btn-close" data-bs-dismiss="alert"></button>

    </div>

    <?php elseif($_GET['pesan']=="update") : ?>

    <div class="alert alert-warning alert-dismissible fade show">

        Data alat berhasil diperbarui.

        <button class="btn-close" data-bs-dismiss="alert"></button>

    </div>

    <?php elseif($_GET['pesan']=="hapus") : ?>

    <div class="alert alert-danger alert-dismissible fade show">

        Data alat berhasil dihapus.

        <button class="btn-close" data-bs-dismiss="alert"></button>

    </div>

    <?php endif; ?>

    <?php endif; ?>

    <div class="card shadow-sm">

        <div class="card-header bg-primary text-white">

            Daftar Data Alat

        </div>

        <div class="card-body">

            <div class="table-responsive">

                <table class="table table-bordered table-hover table-striped align-middle">

                    <thead class="table-dark">

                        <tr>

                            <th width="50">No</th>

                            <th width="90">Foto</th>

                            <th>Nama Alat</th>

                            <th>Kategori</th>

                            <th width="80">Stok</th>

                            <th width="150">Kondisi</th>

                            <th>Lokasi</th>

                            <th width="170">Aksi</th>

                        </tr>

                    </thead>

                    <tbody>

                        <?php

                    $no = 1;

                    if($result->num_rows > 0):

                    while($row = $result->fetch_assoc()):

                    ?>

                        <tr>

                            <td class="text-center">

                                <?= $no++ ?>

                            </td>

                            <td class="text-center">

                                <?php

                            $foto = "../../assets/img/alat/" . $row['foto'];

                            if(!empty($row['foto']) && file_exists($foto)):

                            ?>

                                <img src="<?= $foto ?>" class="img-thumbnail" width="70">

                                <?php else: ?>

                                <span class="text-muted">Tidak Ada</span>

                                <?php endif; ?>

                            </td>

                            <td>

                                <?= htmlspecialchars($row['nama_alat']) ?>

                            </td>

                            <td>

                                <?= htmlspecialchars($row['nama_kategori']) ?>

                            </td>

                            <td class="text-center">

                                <?= htmlspecialchars($row['stok']) ?>

                            </td>

                            <td class="text-center">

                                <?php
                                
                                switch ($row['kondisi']) {
                                    case 'Baik':
                                        echo '<span class="badge bg-success">Baik</span>';
                                
                                        break;
                                
                                    case 'Rusak Ringan':
                                        echo '<span class="badge bg-warning text-dark">Rusak Ringan</span>';
                                
                                        break;
                                
                                    case 'Rusak Berat':
                                        echo '<span class="badge bg-danger">Rusak Berat</span>';
                                
                                        break;
                                }
                                
                                ?>

                            </td>

                            <td>

                                <?= htmlspecialchars($row['lokasi']) ?>

                            </td>

                            <td class="text-center">

                                <a href="detail.php?id=<?= $row['id_alat'] ?>" class="btn btn-info btn-sm">

                                    <i class="bi bi-eye"></i>

                                </a>

                                <a href="edit.php?id=<?= $row['id_alat'] ?>" class="btn btn-warning btn-sm">

                                    <i class="bi bi-pencil-square"></i>

                                </a>

                                <a href="hapus.php?id=<?= $row['id_alat'] ?>" class="btn btn-danger btn-sm"
                                    onclick="return confirm('Yakin ingin menghapus data alat ini?')">

                                    <i class="bi bi-trash"></i>

                                </a>

                            </td>

                        </tr>

                        <?php

                    endwhile;

                    else:

                    ?>

                        <tr>

                            <td colspan="8" class="text-center">

                                Belum ada data alat.

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

<?php
/*
|--------------------------------------------------------------------------
| File        : index.php
| Folder      : administrator/peminjaman
| Fungsi      : Menampilkan Daftar Transaksi Peminjaman
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

$title = "Transaksi Peminjaman";

/*
|--------------------------------------------------------------------------
| Mengambil Data Transaksi
|--------------------------------------------------------------------------
*/

$query = "
SELECT
    p.id_peminjaman,
    u.nama_lengkap,
    p.tanggal_pinjam,
    p.tanggal_kembali,
    p.status,
    COUNT(dp.id_detail) AS jumlah_item,
    COALESCE(SUM(dp.jumlah),0) AS total_unit
FROM peminjaman p
INNER JOIN users u
    ON p.id_user = u.id_user
LEFT JOIN detail_peminjaman dp
    ON p.id_peminjaman = dp.id_peminjaman
GROUP BY
    p.id_peminjaman,
    u.nama_lengkap,
    p.tanggal_pinjam,
    p.tanggal_kembali,
    p.status
ORDER BY
    p.id_peminjaman DESC
";

$result = $conn->query($query);

require_once "../../layouts/header.php";
require_once "../../layouts/navbar.php";
require_once "../../layouts/sidebar.php";

?>

<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h3>

                <i class="bi bi-arrow-left-right"></i>

                Transaksi Peminjaman

            </h3>

            <p class="text-muted mb-0">

                Kelola seluruh transaksi peminjaman alat.

            </p>

        </div>

        <a
            href="tambah.php"
            class="btn btn-primary">

            <i class="bi bi-plus-circle"></i>

            Tambah Peminjaman

        </a>

    </div>

    <?php if (isset($_GET['pesan'])) : ?>

        <?php if ($_GET['pesan'] == "sukses") : ?>

            <div class="alert alert-success alert-dismissible fade show">

                Transaksi berhasil disimpan.

                <button
                    class="btn-close"
                    data-bs-dismiss="alert"></button>

            </div>

        <?php elseif ($_GET['pesan'] == "update") : ?>

            <div class="alert alert-warning alert-dismissible fade show">

                Transaksi berhasil diperbarui.

                <button
                    class="btn-close"
                    data-bs-dismiss="alert"></button>

            </div>

        <?php elseif ($_GET['pesan'] == "hapus") : ?>

            <div class="alert alert-danger alert-dismissible fade show">

                Transaksi berhasil dihapus.

                <button
                    class="btn-close"
                    data-bs-dismiss="alert"></button>

            </div>

        <?php endif; ?>

    <?php endif; ?>

    <div class="card shadow-sm">

        <div class="card-header bg-primary text-white">

            Daftar Transaksi Peminjaman

        </div>

        <div class="card-body">

            <div class="table-responsive">

                <table class="table table-bordered table-hover table-striped align-middle">

                    <thead class="table-dark">

                        <tr>

                            <th width="60">No</th>

                            <th width="100">No Transaksi</th>

                            <th>Peminjam</th>

                            <th width="120">Tgl Pinjam</th>

                            <th width="120">Tgl Kembali</th>

                            <th width="90">Jenis Alat</th>

                            <th width="90">Total Unit</th>

                            <th width="120">Status</th>

                            <th width="170">Aksi</th>

                        </tr>

                    </thead>

                    <tbody>

                        <?php

                        $no = 1;

                        if ($result->num_rows > 0):

                            while ($row = $result->fetch_assoc()):

                        ?>

                                <tr>

                                    <td class="text-center">

                                        <?= $no++; ?>

                                    </td>

                                    <td class="text-center">

                                        <b>#<?= $row['id_peminjaman']; ?></b>

                                    </td>

                                    <td>

                                        <?= htmlspecialchars($row['nama_lengkap']); ?>

                                    </td>

                                    <td class="text-center">

                                        <?= date('d-m-Y', strtotime($row['tanggal_pinjam'])); ?>

                                    </td>

                                    <td class="text-center">

                                        <?= date('d-m-Y', strtotime($row['tanggal_kembali'])); ?>

                                    </td>

                                    <td class="text-center">

                                        <?= $row['jumlah_item']; ?>

                                    </td>

                                    <td class="text-center">

                                        <?= $row['total_unit']; ?>

                                    </td>

                                    <td class="text-center">

                                        <?php

                                        switch ($row['status']) {

                                            case "Menunggu":

                                                echo '<span class="badge bg-secondary">Menunggu</span>';

                                                break;

                                            case "Disetujui":

                                                echo '<span class="badge bg-primary">Disetujui</span>';

                                                break;

                                            case "Ditolak":

                                                echo '<span class="badge bg-danger">Ditolak</span>';

                                                break;

                                            case "Dipinjam":

                                                echo '<span class="badge bg-warning text-dark">Dipinjam</span>';

                                                break;

                                            case "Selesai":

                                                echo '<span class="badge bg-success">Selesai</span>';

                                                break;
                                        }

                                        ?>

                                    </td>

                                    <td class="text-center">

                                        <a
                                            href="detail.php?id=<?= $row['id_peminjaman']; ?>"
                                            class="btn btn-info btn-sm">

                                            <i class="bi bi-eye"></i>

                                        </a>

                                        <a
                                            href="edit.php?id=<?= $row['id_peminjaman']; ?>"
                                            class="btn btn-warning btn-sm">

                                            <i class="bi bi-pencil-square"></i>

                                        </a>

                                        <a
                                            href="hapus.php?id=<?= $row['id_peminjaman']; ?>"
                                            class="btn btn-danger btn-sm"
                                            onclick="return confirm('Yakin ingin menghapus transaksi ini?')">

                                            <i class="bi bi-trash"></i>

                                        </a>

                                    </td>

                                </tr>

                            <?php

                            endwhile;

                        else:

                            ?>

                            <tr>

                                <td colspan="9" class="text-center">

                                    Belum ada transaksi peminjaman.

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

require_once "../../layouts/footer.php";

?>
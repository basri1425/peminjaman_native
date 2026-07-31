<?php

require '../../config/session.php';
require '../../config/database.php';

if (!isset($_SESSION['id_user'])) {

    header('Location: ../../login.php');
    exit();
}

if ($_SESSION['level'] != 'Petugas') {

    header('Location: ../../unauthorized.php');
    exit();
}

/*
|--------------------------------------------------------------------------
| Validasi ID
|--------------------------------------------------------------------------
*/

if (!isset($_GET['id'])) {

    header('Location: index.php');
    exit();
}

$idPeminjaman = (int) $_GET['id'];

/*
|--------------------------------------------------------------------------
| Data Peminjaman
|--------------------------------------------------------------------------
*/

$sql = "

SELECT

    p.id_peminjaman,
    p.tanggal_pinjam,
    p.tanggal_kembali,
    p.updated_at,

    u.id_user,
    u.nama_lengkap

FROM peminjaman p

INNER JOIN users u
ON p.id_user=u.id_user

WHERE

    p.id_peminjaman=?

AND

    p.status='Dipinjam'

LIMIT 1

";

$stmt = $conn->prepare($sql);

$stmt->bind_param("i", $idPeminjaman);

$stmt->execute();

$peminjaman = $stmt->get_result()->fetch_assoc();

$stmt->close();

if (!$peminjaman) {

    $_SESSION['error'] = "Data tidak ditemukan.";

    header("Location:index.php");

    exit();
}

/*
|--------------------------------------------------------------------------
| Daftar Alat
|--------------------------------------------------------------------------
*/

$sql = "

SELECT

    dp.id_detail,
    dp.id_alat,
    dp.jumlah,

    a.nama_alat,
    a.lokasi,
    a.kondisi

FROM detail_peminjaman dp

INNER JOIN alat a
ON dp.id_alat=a.id_alat

WHERE

dp.id_peminjaman=?

ORDER BY

a.nama_alat ASC

";

$stmt = $conn->prepare($sql);

$stmt->bind_param("i", $idPeminjaman);

$stmt->execute();

$detail = $stmt->get_result();

$stmt->close();

?>

<?php include '../../layouts/header.php'; ?>
<?php include '../../layouts/navbar.php'; ?>
<?php include '../../layouts/sidebar.php'; ?>

<div class="content-wrapper">

    <section class="content-header">

        <div class="container-fluid">

            <div class="row mb-2">

                <div class="col-sm-6">

                    <h1>

                        <i class="fas fa-clipboard-check"></i>

                        Verifikasi Pengembalian

                    </h1>

                    <p class="text-muted">

                        Periksa kondisi alat sebelum pengembalian diselesaikan.

                    </p>

                </div>

                <div class="col-sm-6 text-end">

                    <a href="index.php" class="btn btn-secondary">

                        <i class="fas fa-arrow-left"></i>

                        Kembali

                    </a>

                </div>

            </div>

        </div>

    </section>

    <section class="content">

        <div class="container-fluid">

            <form action="proses.php" method="POST">

                <input
                    type="hidden"
                    name="id_peminjaman"
                    value="<?= $peminjaman['id_peminjaman']; ?>">

                <input
                    type="hidden"
                    name="tanggal_pengembalian"
                    value="<?= date('Y-m-d'); ?>">
                <!-- Informasi Peminjaman -->

                <div class="card card-primary">

                    <div class="card-header">

                        <h3 class="card-title">

                            <i class="fas fa-info-circle"></i>

                            Informasi Peminjaman

                        </h3>

                    </div>

                    <div class="card-body">

                        <div class="row">

                            <div class="col-md-6">

                                <table class="table table-borderless table-sm">

                                    <tr>

                                        <th width="40%">No. Peminjaman</th>

                                        <td width="5%">:</td>

                                        <td>

                                            PMJ<?= str_pad($peminjaman['id_peminjaman'], 5, '0', STR_PAD_LEFT); ?>

                                        </td>

                                    </tr>

                                    <tr>

                                        <th>Nama Peminjam</th>

                                        <td>:</td>

                                        <td>

                                            <?= htmlspecialchars($peminjaman['nama_lengkap']); ?>

                                        </td>

                                    </tr>

                                    <tr>

                                        <th>Tanggal Pinjam</th>

                                        <td>:</td>

                                        <td>

                                            <?= date('d F Y', strtotime($peminjaman['tanggal_pinjam'])); ?>

                                        </td>

                                    </tr>

                                </table>

                            </div>

                            <div class="col-md-6">

                                <table class="table table-borderless table-sm">

                                    <tr>

                                        <th width="40%">Rencana Kembali</th>

                                        <td width="5%">:</td>

                                        <td>

                                            <?= date('d F Y', strtotime($peminjaman['tanggal_kembali'])); ?>

                                        </td>

                                    </tr>

                                    <tr>

                                        <th>Tanggal Pengajuan</th>

                                        <td>:</td>

                                        <td>

                                            <?= date('d F Y H:i', strtotime($peminjaman['updated_at'])); ?>

                                        </td>

                                    </tr>

                                    <tr>

                                        <th>Status</th>

                                        <td>:</td>

                                        <td>

                                            <span class="badge bg-warning">

                                                Menunggu Pengembalian

                                            </span>

                                        </td>

                                    </tr>

                                </table>

                            </div>

                        </div>

                    </div>

                </div>
                <!-- Daftar Alat -->

                <div class="card card-success">

                    <div class="card-header">

                        <h3 class="card-title">

                            <i class="fas fa-toolbox"></i>

                            Pemeriksaan Alat

                        </h3>

                    </div>

                    <div class="card-body">

                        <div class="table-responsive">

                            <table class="table table-bordered table-hover">

                                <thead class="table-light">

                                    <tr>

                                        <th width="5%" class="text-center">No</th>

                                        <th>Nama Alat</th>

                                        <th width="10%" class="text-center">Jumlah</th>

                                        <th width="15%">Lokasi</th>

                                        <th width="15%">Kondisi Saat Dipinjam</th>

                                        <th width="20%">Kondisi Saat Kembali</th>

                                        <th>Keterangan</th>

                                    </tr>

                                </thead>

                                <tbody>

                                    <?php

                                    $no = 1;

                                    while ($row = $detail->fetch_assoc()) {

                                    ?>

                                        <tr>

                                            <td class="text-center">

                                                <?= $no++; ?>

                                            </td>

                                            <td>

                                                <?= htmlspecialchars($row['nama_alat']); ?>

                                                <input
                                                    type="hidden"
                                                    name="id_alat[]"
                                                    value="<?= $row['id_alat']; ?>">

                                            </td>

                                            <td class="text-center">

                                                <?= $row['jumlah']; ?>

                                                <input
                                                    type="hidden"
                                                    name="jumlah[]"
                                                    value="<?= $row['jumlah']; ?>">

                                            </td>

                                            <td>

                                                <?= htmlspecialchars($row['lokasi']); ?>

                                            </td>

                                            <td>

                                                <span class="badge bg-info">

                                                    <?= htmlspecialchars($row['kondisi']); ?>

                                                </span>

                                            </td>

                                            <td>

                                                <select
                                                    name="kondisi[]"
                                                    class="form-select"
                                                    required>

                                                    <option value="">-- Pilih Kondisi --</option>

                                                    <option value="Baik"
                                                        <?= ($row['kondisi'] == 'Baik') ? 'selected' : ''; ?>>
                                                        Baik
                                                    </option>

                                                    <option value="Rusak Ringan"
                                                        <?= ($row['kondisi'] == 'Rusak Ringan') ? 'selected' : ''; ?>>
                                                        Rusak Ringan
                                                    </option>

                                                    <option value="Rusak Berat"
                                                        <?= ($row['kondisi'] == 'Rusak Berat') ? 'selected' : ''; ?>>
                                                        Rusak Berat
                                                    </option>

                                                    <option value="Hilang">
                                                        Hilang
                                                    </option>

                                                </select>

                                            </td>

                                            <td>

                                                <input
                                                    type="text"
                                                    name="keterangan_alat[]"
                                                    class="form-control"
                                                    maxlength="255"
                                                    placeholder="Contoh: Lecet pada casing">

                                            </td>

                                        </tr>

                                    <?php } ?>

                                </tbody>

                            </table>

                        </div>

                    </div>

                </div>
                <!-- Catatan Verifikasi -->

                <div class="card card-warning">

                    <div class="card-header">

                        <h3 class="card-title">

                            <i class="fas fa-sticky-note"></i>

                            Catatan Verifikasi

                        </h3>

                    </div>

                    <div class="card-body">

                        <div class="form-group">

                            <label>

                                Catatan Petugas

                            </label>

                            <textarea

                                name="keterangan"

                                class="form-control"

                                rows="4"

                                placeholder="Masukkan catatan hasil pemeriksaan (opsional)..."></textarea>

                            <small class="text-muted">

                                Catatan ini akan disimpan sebagai keterangan umum pada transaksi pengembalian.

                            </small>

                        </div>

                    </div>

                </div>

                <!-- Tombol -->

                <div class="card">

                    <div class="card-body">

                        <div class="d-flex justify-content-between">

                            <a

                                href="index.php"

                                class="btn btn-secondary">

                                <i class="fas fa-arrow-left"></i>

                                Kembali

                            </a>

                            <button

                                type="submit"

                                class="btn btn-success"

                                onclick="return confirm('Apakah hasil verifikasi sudah benar?')">

                                <i class="fas fa-check-circle"></i>

                                Simpan Verifikasi

                            </button>

                        </div>

                    </div>

                </div>

            </form>

        </div>

    </section>

</div>

<?php include '../../layouts/footer.php'; ?>


<script>
    $(function() {

        $('.select2').select2();

    });
</script>
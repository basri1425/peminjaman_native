<?php

/*
|--------------------------------------------------------------------------
| Konfigurasi Aplikasi
|--------------------------------------------------------------------------
*/

require '../../config/session.php';
require '../../config/database.php';

/*
|--------------------------------------------------------------------------
| Validasi Login
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION['login'])) {
    header('Location: ../../login.php');
    exit();
}

/*
|--------------------------------------------------------------------------
| Validasi Hak Akses
|--------------------------------------------------------------------------
*/

if ($_SESSION['level'] != 'Peminjam') {
    header('Location: ../../unauthorized.php');
    exit();
}

/*
|--------------------------------------------------------------------------
| Judul Halaman
|--------------------------------------------------------------------------
*/

$title = 'Daftar Alat';

/*
|--------------------------------------------------------------------------
| Filter Pencarian
|--------------------------------------------------------------------------
*/

$keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';

$idKategori = isset($_GET['kategori']) ? (int) $_GET['kategori'] : 0;

/*
|--------------------------------------------------------------------------
| Data Kategori
|--------------------------------------------------------------------------
*/

$sqlKategori = "

    SELECT

        id_kategori,
        nama_kategori

    FROM kategori

    ORDER BY nama_kategori ASC

";

$resultKategori = $conn->query($sqlKategori);

/*
|--------------------------------------------------------------------------
| Data Alat
|--------------------------------------------------------------------------
*/

$sql = "

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

    WHERE 1 = 1

";

$parameter = [];

$tipe = '';

if ($keyword != '') {
    $sql .= "

        AND a.nama_alat LIKE ?

    ";

    $parameter[] = '%' . $keyword . '%';

    $tipe .= 's';
}

if ($idKategori > 0) {
    $sql .= "

        AND a.id_kategori = ?

    ";

    $parameter[] = $idKategori;

    $tipe .= 'i';
}

$sql .= "

    ORDER BY

        a.nama_alat ASC

";

$stmt = $conn->prepare($sql);

/*
|--------------------------------------------------------------------------
| Binding Parameter Dinamis
|--------------------------------------------------------------------------
*/

if (!empty($parameter)) {
    $stmt->bind_param($tipe, ...$parameter);
}

$stmt->execute();

$result = $stmt->get_result();

?>
<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= $title ?></title>

    <?php include '../../layouts/header.php'; ?>

</head>

<body>

    <?php include '../../layouts/navbar.php'; ?>

    <?php include '../../layouts/sidebar.php'; ?>

    <div class="content-wrapper">

        <!-- Header Halaman -->

        <section class="content-header">

            <div class="container-fluid">

                <div class="row mb-2">

                    <div class="col-sm-6">

                        <h1>

                            <i class="bi bi-tools text-primary"></i>

                            Daftar Alat

                        </h1>

                    </div>

                    <div class="col-sm-6">

                        <nav aria-label="breadcrumb">

                            <ol class="breadcrumb float-sm-end">

                                <li class="breadcrumb-item">

                                    Dashboard

                                </li>

                                <li class="breadcrumb-item active">

                                    Daftar Alat

                                </li>

                            </ol>

                        </nav>

                    </div>

                </div>

            </div>

        </section>

        <!-- Content -->

        <section class="content">

            <div class="container-fluid">

                <!-- Card Pencarian -->

                <div class="card shadow-sm mb-4">

                    <div class="card-header bg-primary text-white">

                        <h5 class="mb-0">

                            <i class="bi bi-search"></i>

                            Pencarian Alat

                        </h5>

                    </div>

                    <div class="card-body">

                        <form method="GET">

                            <div class="row">

                                <div class="col-md-5 mb-3">

                                    <label class="form-label">

                                        Nama Alat

                                    </label>

                                    <input type="text" name="keyword" class="form-control"
                                        placeholder="Masukkan nama alat..." value="<?= htmlspecialchars($keyword) ?>">

                                </div>

                                <div class="col-md-4 mb-3">

                                    <label class="form-label">

                                        Kategori

                                    </label>

                                    <select name="kategori" class="form-select">

                                        <option value="0">

                                            Semua Kategori

                                        </option>

                                        <?php while ($kategori = $resultKategori->fetch_assoc()) : ?>

                                        <option value="<?= $kategori['id_kategori'] ?>"
                                            <?= $idKategori == $kategori['id_kategori'] ? 'selected' : '' ?>>

                                            <?= htmlspecialchars($kategori['nama_kategori']) ?>

                                        </option>

                                        <?php endwhile; ?>

                                    </select>

                                </div>

                                <div class="col-md-3 mb-3 d-grid">

                                    <label class="form-label">

                                        &nbsp;

                                    </label>

                                    <button type="submit" class="btn btn-primary">

                                        <i class="bi bi-search"></i>

                                        Cari Data

                                    </button>

                                </div>

                            </div>

                        </form>

                    </div>

                </div>

                <!-- Card Daftar Alat -->

                <div class="card shadow-sm">

                    <div class="card-header bg-success text-white">

                        <h5 class="mb-0">

                            <i class="bi bi-list-ul"></i>

                            Daftar Alat

                        </h5>

                    </div>

                    <div class="card-body table-responsive">
                        <table class="table table-bordered table-hover align-middle">

                            <thead class="table-light">

                                <tr class="text-center">

                                    <th width="5%">No</th>

                                    <th width="10%">Foto</th>

                                    <th>Nama Alat</th>

                                    <th width="18%">Kategori</th>

                                    <th width="15%">Lokasi</th>

                                    <th width="12%">Kondisi</th>

                                    <th width="10%">Stok</th>

                                    <th width="12%">Aksi</th>

                                </tr>

                            </thead>

                            <tbody>

                                <?php

        if ($result->num_rows > 0) :

            $no = 1;

            while ($row = $result->fetch_assoc()) :

                /*
                |--------------------------------------------------------------------------
                | Badge Kondisi
                |--------------------------------------------------------------------------
                */

                switch ($row['kondisi']) {

                    case 'Baik':
                        $badgeKondisi = 'success';
                        break;

                    case 'Rusak Ringan':
                        $badgeKondisi = 'warning';
                        break;

                    case 'Rusak Berat':
                        $badgeKondisi = 'danger';
                        break;

                    default:
                        $badgeKondisi = 'secondary';
                        break;

                }

                /*
                |--------------------------------------------------------------------------
                | Badge Stok
                |--------------------------------------------------------------------------
                */

                if ($row['stok'] > 5) {

                    $badgeStok = 'success';

                } elseif ($row['stok'] > 0) {

                    $badgeStok = 'warning';

                } else {

                    $badgeStok = 'danger';

                }

        ?>

                                <tr>

                                    <td class="text-center">

                                        <?= $no++ ?>

                                    </td>

                                    <td class="text-center">

                                        <?php if (!empty($row['foto']) && file_exists("../../assets/img/alat/" . $row['foto'])) : ?>

                                        <img src="../../assets/img/alat/<?= htmlspecialchars($row['foto']) ?>"
                                            class="img-thumbnail" style="width:70px;height:70px;object-fit:cover;">

                                        <?php else : ?>

                                        <img src="../../assets/img/no-image.png" class="img-thumbnail"
                                            style="width:70px;height:70px;object-fit:cover;">

                                        <?php endif; ?>

                                    </td>

                                    <td>

                                        <strong>

                                            <?= htmlspecialchars($row['nama_alat']) ?>

                                        </strong>

                                    </td>

                                    <td>

                                        <?= htmlspecialchars($row['nama_kategori']) ?>

                                    </td>

                                    <td>

                                        <?= htmlspecialchars($row['lokasi']) ?>

                                    </td>

                                    <td class="text-center">

                                        <span class="badge bg-<?= $badgeKondisi ?>">

                                            <?= htmlspecialchars($row['kondisi']) ?>

                                        </span>

                                    </td>

                                    <td class="text-center">

                                        <span class="badge bg-<?= $badgeStok ?>">

                                            <?= $row['stok'] ?>

                                        </span>

                                    </td>

                                    <td class="text-center">

                                        <a href="detail.php?id=<?= $row['id_alat'] ?>"
                                            class="btn btn-sm btn-outline-primary">

                                            <i class="bi bi-eye"></i>

                                            Detail

                                        </a>

                                    </td>

                                </tr>

                                <?php

            endwhile;

        else :

        ?>

                                <tr>

                                    <td colspan="8" class="text-center text-muted py-5">

                                        <i class="bi bi-tools fs-1 d-block mb-3"></i>

                                        Belum ada data alat.

                                    </td>

                                </tr>

                                <?php endif; ?>

                            </tbody>

                        </table>

                    </div>

                </div>

            </div>

        </section>

    </div>
    <?php include '../../layouts/footer.php'; ?>

</body>

</html>

<?php

/*
|--------------------------------------------------------------------------
| Menutup Resource Query
|--------------------------------------------------------------------------
*/

if (isset($result) && $result instanceof mysqli_result) {
    $result->free();
}

if (isset($resultKategori) && $resultKategori instanceof mysqli_result) {
    $resultKategori->free();
}

/*
|--------------------------------------------------------------------------
| Menutup Prepared Statement
|--------------------------------------------------------------------------
*/

if (isset($stmt) && $stmt instanceof mysqli_stmt) {
    $stmt->close();
}

/*
|--------------------------------------------------------------------------
| Menutup Koneksi Database
|--------------------------------------------------------------------------
*/

$conn->close();

?>

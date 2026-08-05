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
| Validasi Parameter
|--------------------------------------------------------------------------
*/

if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "

    <script>

        alert('ID transaksi tidak ditemukan.');

        window.location='index.php';

    </script>

    ";

    exit();
}

$idPeminjaman = (int) $_GET['id'];

/*
|--------------------------------------------------------------------------
| Mengambil Header Transaksi
|--------------------------------------------------------------------------
*/

$sql = "
SELECT

    p.id_peminjaman,
    p.id_user,
    p.tanggal_pinjam,
    p.tanggal_kembali,
    p.status,

    u.nama_lengkap

FROM peminjaman p

INNER JOIN users u
ON p.id_user = u.id_user

WHERE p.id_peminjaman = ?

LIMIT 1
";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die('Prepare gagal : ' . $conn->error);
}

$stmt->bind_param('i', $idPeminjaman);

$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo "

    <script>

        alert('Data transaksi tidak ditemukan.');

        window.location='index.php';

    </script>

    ";

    exit();
}

$transaksi = $result->fetch_assoc();

$stmt->close();

/*
|--------------------------------------------------------------------------
| Mengambil Data User
|--------------------------------------------------------------------------
*/

$sqlUser = "
SELECT

    id_user,
    nama_lengkap

FROM users

WHERE
    status='Aktif'

ORDER BY
    nama_lengkap ASC
";

$resultUser = $conn->query($sqlUser);

/*
|--------------------------------------------------------------------------
| Mengambil Data Alat
|--------------------------------------------------------------------------
*/

$sqlAlat = "
SELECT

    a.id_alat,
    a.nama_alat,
    a.stok,
    a.kondisi,

    k.nama_kategori

FROM alat a

INNER JOIN kategori k
ON a.id_kategori = k.id_kategori

ORDER BY
    a.nama_alat ASC
";

$resultAlat = $conn->query($sqlAlat);

/*
|--------------------------------------------------------------------------
| Mengambil Detail Transaksi
|--------------------------------------------------------------------------
*/

$sqlDetail = "
SELECT

    dp.id_detail,
    dp.id_alat,
    dp.jumlah,

    a.nama_alat,
    a.stok,
    a.kondisi,
    a.lokasi,
    a.foto,

    k.nama_kategori

FROM detail_peminjaman dp

INNER JOIN alat a
ON dp.id_alat = a.id_alat

INNER JOIN kategori k
ON a.id_kategori = k.id_kategori

WHERE
    dp.id_peminjaman = ?

ORDER BY
    a.nama_alat ASC
";

$stmt = $conn->prepare($sqlDetail);

if (!$stmt) {
    die('Prepare gagal : ' . $conn->error);
}

$stmt->bind_param('i', $idPeminjaman);

$stmt->execute();

$resultDetail = $stmt->get_result();

/*
|--------------------------------------------------------------------------
| Menyimpan Detail ke Array
|--------------------------------------------------------------------------
*/

$detailPeminjaman = [];

while ($row = $resultDetail->fetch_assoc()) {
    $detailPeminjaman[] = [
        'id_alat' => $row['id_alat'],
        'nama_alat' => $row['nama_alat'],
        'kategori' => $row['nama_kategori'],
        'kondisi' => $row['kondisi'],
        'lokasi' => $row['lokasi'],
        'stok' => (int) $row['stok'],
        'jumlah' => (int) $row['jumlah'],
        'foto' => $row['foto'],
    ];
}

$stmt->close();

/*
|--------------------------------------------------------------------------
| Variabel Header
|--------------------------------------------------------------------------
*/

$idUser = $transaksi['id_user'];
$tanggalPinjam = $transaksi['tanggal_pinjam'];
$tanggalKembali = $transaksi['tanggal_kembali'];
$statusPeminjaman = $transaksi['status'];

?>

<?php include '../../layouts/header.php'; ?>

<div class="container-fluid">

    <!-- Judul Halaman -->
    <div class="row mb-4">

        <div class="col-md-12">

            <h3 class="fw-bold">

                Edit Transaksi Peminjaman

            </h3>

            <hr>

        </div>

    </div>

    <form action="update.php" method="POST" id="formPeminjaman">

        <!-- ID Transaksi -->
        <input type="hidden" name="id_peminjaman" value="<?= $idPeminjaman ?>">

        <!-- Header Transaksi -->
        <div class="card shadow-sm mb-4">

            <div class="card-header bg-primary text-white">

                <strong>Data Transaksi</strong>

            </div>

            <div class="card-body">

                <div class="row">

                    <div class="col-md-6 mb-3">

                        <label class="form-label">

                            Peminjam

                        </label>

                        <select name="id_user" class="form-select" required>

                            <option value="">-- Pilih Peminjam --</option>

                            <?php while($user = $resultUser->fetch_assoc()) : ?>

                            <option value="<?= $user['id_user'] ?>"
                                <?= $user['id_user'] == $idUser ? 'selected' : '' ?>>

                                <?= htmlspecialchars($user['nama_lengkap']) ?>

                            </option>

                            <?php endwhile; ?>

                        </select>

                    </div>

                    <div class="col-md-3 mb-3">

                        <label class="form-label">

                            Tanggal Pinjam

                        </label>

                        <input type="date" name="tanggal_pinjam" class="form-control" value="<?= $tanggalPinjam ?>"
                            required>

                    </div>

                    <div class="col-md-3 mb-3">

                        <label class="form-label">

                            Tanggal Kembali

                        </label>

                        <input type="date" name="tanggal_kembali" class="form-control" value="<?= $tanggalKembali ?>"
                            required>

                    </div>

                </div>

                <div class="row">

                    <div class="col-md-4">

                        <label class="form-label">

                            Status

                        </label>

                        <input type="text" class="form-control" value="<?= $statusPeminjaman ?>" readonly>

                    </div>

                </div>

            </div>

        </div>

        <!-- Tambah Alat -->
        <div class="card shadow-sm mb-4">

            <div class="card-header bg-success text-white">

                <strong>Tambah Alat</strong>

            </div>

            <div class="card-body">

                <div class="row align-items-end">

                    <div class="col-md-6">

                        <label class="form-label">

                            Pilih Alat

                        </label>

                        <select id="id_alat" class="form-select">

                            <option value="">-- Pilih Alat --</option>

                            <?php while($alat = $resultAlat->fetch_assoc()) : ?>

                            <option value="<?= $alat['id_alat'] ?>"
                                data-nama="<?= htmlspecialchars($alat['nama_alat']) ?>"
                                data-kategori="<?= htmlspecialchars($alat['nama_kategori']) ?>"
                                data-kondisi="<?= htmlspecialchars($alat['kondisi']) ?>"
                                data-stok="<?= $alat['stok'] ?>">

                                <?= htmlspecialchars($alat['nama_alat']) ?>

                                (Stok :

                                <?= $alat['stok'] ?>)

                            </option>

                            <?php endwhile; ?>

                        </select>

                    </div>

                    <div class="col-md-2">

                        <label class="form-label">

                            Jumlah

                        </label>

                        <input type="number" id="jumlah" class="form-control" min="1" value="1">

                    </div>

                    <div class="col-md-2 d-grid">

                        <button type="button" id="btnTambah" class="btn btn-success">

                            + Tambah

                        </button>

                    </div>

                </div>

            </div>

        </div>

        <!-- Daftar Alat -->
        <div class="card shadow-sm">

            <div class="card-header bg-secondary text-white">

                <strong>Daftar Alat Dipinjam</strong>

            </div>

            <div class="card-body">

                <div class="table-responsive">

                    <table class="table table-bordered table-hover">

                        <thead class="table-dark">

                            <tr>

                                <th width="50">No</th>

                                <th>Nama Alat</th>

                                <th>Kategori</th>

                                <th width="100">Stok</th>

                                <th width="100">Jumlah</th>

                                <th width="80">Aksi</th>

                            </tr>

                        </thead>

                        <tbody id="daftarAlat">

                            <!-- Diisi oleh JavaScript -->

                        </tbody>

                    </table>

                </div>

                <div id="hiddenInput"></div>

            </div>

        </div>

        <!-- Tombol -->
        <div class="mt-4 mb-5">

            <a href="index.php" class="btn btn-secondary">

                ← Kembali

            </a>

            <button type="reset" class="btn btn-warning" id="btnReset">

                Reset

            </button>

            <button type="submit" class="btn btn-primary">

                Simpan Perubahan

            </button>

        </div>

    </form>

</div>
<script>
    let daftarAlat = <?= json_encode($detailPeminjaman) ?>;

    /*
    |--------------------------------------------------------------------------
    | Render Tabel
    |--------------------------------------------------------------------------
    */

    function renderTabel() {

        const tbody = document.getElementById('daftarAlat');
        const hidden = document.getElementById('hiddenInput');

        tbody.innerHTML = '';
        hidden.innerHTML = '';

        if (daftarAlat.length === 0) {

            tbody.innerHTML = `

        <tr>

            <td colspan="6" class="text-center text-muted">

                Belum ada alat.

            </td>

        </tr>

        `;

            return;

        }

        daftarAlat.forEach(function(item, index) {

            tbody.innerHTML += `

        <tr>

            <td class="text-center">

                ${index+1}

            </td>

            <td>

                ${item.nama_alat}

            </td>

            <td>

                ${item.kategori}

            </td>

            <td class="text-center">

                ${item.stok}

            </td>

            <td width="120">

                <input
                    type="number"
                    min="1"
                    class="form-control"
                    value="${item.jumlah}"
                    onchange="ubahJumlah(${index},this.value)">

            </td>

            <td class="text-center">

                <button
                    type="button"
                    class="btn btn-danger btn-sm"
                    onclick="hapusAlat(${index})">

                    Hapus

                </button>

            </td>

        </tr>

        `;

            hidden.innerHTML += `

            <input
                type="hidden"
                name="id_alat[]"
                value="${item.id_alat}">

            <input
                type="hidden"
                name="jumlah[]"
                value="${item.jumlah}">

        `;

        });

    }

    /*
    |--------------------------------------------------------------------------
    | Tambah Alat
    |--------------------------------------------------------------------------
    */

    document.getElementById('btnTambah').addEventListener('click', function() {

        const select = document.getElementById('id_alat');

        if (select.value === '') {

            alert('Silakan pilih alat.');

            return;

        }

        const jumlah = parseInt(document.getElementById('jumlah').value);

        if (jumlah < 1) {

            alert('Jumlah tidak valid.');

            return;

        }

        const option = select.options[select.selectedIndex];

        const idAlat = parseInt(option.value);

        const nama = option.dataset.nama;

        const kategori = option.dataset.kategori;

        const kondisi = option.dataset.kondisi;

        const stok = parseInt(option.dataset.stok);

        /*
        |--------------------------------------------------------------------------
        | Cek Duplikasi
        |--------------------------------------------------------------------------
        */

        const sudahAda = daftarAlat.find(function(item) {

            return item.id_alat == idAlat;

        });

        if (sudahAda) {

            alert('Alat sudah ada di dalam transaksi.');

            return;

        }

        /*
        |--------------------------------------------------------------------------
        | Validasi Stok
        |--------------------------------------------------------------------------
        */

        if (jumlah > stok) {

            alert('Jumlah melebihi stok.');

            return;

        }

        daftarAlat.push({

            id_alat: idAlat,
            nama_alat: nama,
            kategori: kategori,
            kondisi: kondisi,
            lokasi: '',
            stok: stok,
            jumlah: jumlah,
            foto: ''

        });

        renderTabel();

        select.selectedIndex = 0;

        document.getElementById('jumlah').value = 1;

    });

    /*
    |--------------------------------------------------------------------------
    | Ubah Jumlah
    |--------------------------------------------------------------------------
    */

    function ubahJumlah(index, value) {

        let jumlah = parseInt(value);

        if (isNaN(jumlah) || jumlah < 1) {

            jumlah = 1;

        }

        if (jumlah > daftarAlat[index].stok) {

            alert('Jumlah melebihi stok.');

            jumlah = daftarAlat[index].stok;

        }

        daftarAlat[index].jumlah = jumlah;

        renderTabel();

    }

    /*
    |--------------------------------------------------------------------------
    | Hapus Alat
    |--------------------------------------------------------------------------
    */

    function hapusAlat(index) {

        if (!confirm('Hapus alat dari transaksi?')) {

            return;

        }

        daftarAlat.splice(index, 1);

        renderTabel();

    }

    /*
    |--------------------------------------------------------------------------
    | Validasi Submit
    |--------------------------------------------------------------------------
    */

    document.getElementById('formPeminjaman').addEventListener('submit', function(e) {

        if (daftarAlat.length === 0) {

            alert('Minimal terdapat satu alat.');

            e.preventDefault();

            return;

        }

    });

    /*
    |--------------------------------------------------------------------------
    | Reset Form
    |--------------------------------------------------------------------------
    */

    document.getElementById('btnReset').addEventListener('click', function() {

        setTimeout(function() {

            daftarAlat = <?= json_encode($detailPeminjaman) ?>;

            renderTabel();

        }, 100);

    });

    /*
    |--------------------------------------------------------------------------
    | Render Awal
    |--------------------------------------------------------------------------
    */

    renderTabel();
</script>

<?php include '../../layouts/footer.php'; ?>
<?php include '../../layouts/script.php'; ?>
<?php

/*
|--------------------------------------------------------------------------
| Menutup Koneksi Database
|--------------------------------------------------------------------------
*/

$conn->close();

?>

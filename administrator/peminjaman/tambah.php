<?php
/*
|--------------------------------------------------------------------------
| File        : create.php
| Folder      : administrator/peminjaman
| Fungsi      : Form Tambah Transaksi Peminjaman
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

$title = "Tambah Peminjaman";

/*
|--------------------------------------------------------------------------
| Mengambil Data Peminjam
|--------------------------------------------------------------------------
|
| Menampilkan seluruh user yang dapat melakukan peminjaman.
|
*/

$queryUser = "
SELECT
    id_user,
    nama_lengkap
FROM users
WHERE status='Aktif'
ORDER BY nama_lengkap ASC
";

$resultUser = $conn->query($queryUser);

/*
|--------------------------------------------------------------------------
| Mengambil Data Alat
|--------------------------------------------------------------------------
|
| Hanya alat yang stoknya masih tersedia.
|
*/

$queryAlat = "
SELECT
    a.id_alat,
    a.nama_alat,
    a.stok,
    k.nama_kategori
FROM alat a
INNER JOIN kategori k
ON a.id_kategori = k.id_kategori
WHERE a.stok > 0
ORDER BY
k.nama_kategori,
a.nama_alat
";

$resultAlat = $conn->query($queryAlat);

require_once "../../layouts/header.php";
require_once "../../layouts/navbar.php";
require_once "../../layouts/sidebar.php";

?>

<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h3>

                <i class="bi bi-arrow-left-right"></i>

                Tambah Transaksi Peminjaman

            </h3>

            <p class="text-muted mb-0">

                Membuat transaksi peminjaman alat baru.

            </p>

        </div>

        <a
            href="index.php"
            class="btn btn-secondary">

            <i class="bi bi-arrow-left"></i>

            Kembali

        </a>

    </div>

    <form
        action="simpan.php"
        method="POST"
        id="formPeminjaman">

        <!-- =======================================================
HEADER TRANSAKSI
======================================================= -->

        <div class="card shadow-sm mb-4">

            <div class="card-header bg-primary text-white">

                <i class="bi bi-file-earmark-text"></i>

                Informasi Transaksi

            </div>

            <div class="card-body">

                <div class="row">

                    <div class="col-md-4 mb-3">

                        <label class="form-label">

                            Tanggal Pinjam

                            <span class="text-danger">*</span>

                        </label>

                        <input
                            type="date"
                            name="tanggal_pinjam"
                            class="form-control"
                            value="<?= date('Y-m-d'); ?>"
                            required>

                    </div>

                    <div class="col-md-4 mb-3">

                        <label class="form-label">

                            Tanggal Kembali

                            <span class="text-danger">*</span>

                        </label>

                        <input
                            type="date"
                            name="tanggal_kembali"
                            class="form-control"
                            required>

                    </div>

                    <div class="col-md-4 mb-3">

                        <label class="form-label">

                            Peminjam

                            <span class="text-danger">*</span>

                        </label>

                        <select
                            name="id_user"
                            class="form-select"
                            required>

                            <option value="">

                                -- Pilih Peminjam --

                            </option>

                            <?php while ($user = $resultUser->fetch_assoc()) : ?>

                                <option
                                    value="<?= $user['id_user']; ?>">

                                    <?= htmlspecialchars($user['nama_lengkap']); ?>

                                </option>

                            <?php endwhile; ?>

                        </select>

                    </div>

                </div>

            </div>

        </div>

        <!-- =======================================================
FORM TAMBAH ALAT
======================================================= -->

        <div class="card shadow-sm">

            <div class="card-header bg-success text-white">

                <i class="bi bi-tools"></i>

                Tambah Alat

            </div>

            <div class="card-body">

                <div class="row align-items-end">

                    <div class="col-md-7">

                        <label class="form-label">

                            Pilih Alat

                        </label>

                        <select
                            id="alat"
                            class="form-select">

                            <option value="">

                                -- Pilih Alat --

                            </option>

                            <?php while ($alat = $resultAlat->fetch_assoc()) : ?>

                                <option
                                    value="<?= $alat['id_alat']; ?>"
                                    data-nama="<?= htmlspecialchars($alat['nama_alat']); ?>"
                                    data-kategori="<?= htmlspecialchars($alat['nama_kategori']); ?>"
                                    data-stok="<?= $alat['stok']; ?>">

                                    <?= htmlspecialchars($alat['nama_kategori']); ?>

                                    -

                                    <?= htmlspecialchars($alat['nama_alat']); ?>

                                    (Stok :

                                    <?= $alat['stok']; ?>)

                                </option>

                            <?php endwhile; ?>

                        </select>

                    </div>

                    <div class="col-md-2">

                        <label class="form-label">

                            Jumlah

                        </label>

                        <input
                            type="number"
                            id="jumlah"
                            class="form-control"
                            min="1"
                            value="1">

                    </div>

                    <div class="col-md-3">

                        <button
                            type="button"
                            id="btnTambah"
                            class="btn btn-success w-100">

                            <i class="bi bi-plus-circle"></i>

                            Tambah Alat

                        </button>

                    </div>

                </div>

            </div>

        </div>
        <!-- =======================================================
DAFTAR ALAT YANG AKAN DIPINJAM
======================================================= -->

        <div class="card shadow-sm mt-4">

            <div class="card-header bg-warning">

                <i class="bi bi-list-check"></i>

                Daftar Alat Yang Akan Dipinjam

            </div>

            <div class="card-body">

                <div class="table-responsive">

                    <table
                        class="table table-bordered table-hover align-middle"
                        id="tabelAlat">

                        <thead class="table-dark">

                            <tr>

                                <th width="50">

                                    No

                                </th>

                                <th>

                                    Kategori

                                </th>

                                <th>

                                    Nama Alat

                                </th>

                                <th width="90">

                                    Stok

                                </th>

                                <th width="90">

                                    Jumlah

                                </th>

                                <th width="90">

                                    Aksi

                                </th>

                            </tr>

                        </thead>

                        <tbody id="daftarAlat">

                            <tr id="barisKosong">

                                <td
                                    colspan="6"
                                    class="text-center text-muted">

                                    Belum ada alat yang dipilih.

                                </td>

                            </tr>

                        </tbody>

                        <tfoot>

                            <tr class="table-light">

                                <th colspan="3">

                                    Total Jenis Alat

                                </th>

                                <th
                                    colspan="3"
                                    id="totalJenis">

                                    0

                                </th>

                            </tr>

                            <tr class="table-light">

                                <th colspan="3">

                                    Total Unit Dipinjam

                                </th>

                                <th
                                    colspan="3"
                                    id="totalUnit">

                                    0

                                </th>

                            </tr>

                        </tfoot>

                    </table>

                </div>

            </div>

        </div>

        <!-- =======================================================
HIDDEN INPUT
======================================================= -->

        <div id="hiddenInputContainer"></div>

        <!-- =======================================================
TOMBOL SIMPAN
======================================================= -->

        <div class="card mt-4 shadow-sm">

            <div class="card-body">

                <div class="d-flex justify-content-end">

                    <button
                        type="reset"
                        class="btn btn-secondary me-2"
                        id="btnReset">

                        <i class="bi bi-arrow-counterclockwise"></i>

                        Reset

                    </button>

                    <button
                        type="submit"
                        class="btn btn-primary"
                        id="btnSimpan"
                        disabled>

                        <i class="bi bi-save"></i>

                        Simpan Transaksi

                    </button>

                </div>

            </div>

        </div>

    </form>

</div>

<?php

$conn->close();

require_once "../../layouts/footer.php";

?>

<script>
    let daftarPeminjaman = [];

    /*
    |--------------------------------------------------------------------------
    | Tombol Tambah Alat
    |--------------------------------------------------------------------------
    */

    document.getElementById("btnTambah").addEventListener("click", function() {

        const selectAlat = document.getElementById("alat");
        const jumlahInput = document.getElementById("jumlah");

        if (selectAlat.value == "") {
            alert("Silakan pilih alat.");
            selectAlat.focus();
            return;
        }

        const idAlat = selectAlat.value;

        const namaAlat =
            selectAlat.options[
                selectAlat.selectedIndex
            ].dataset.nama;

        const kategori =
            selectAlat.options[
                selectAlat.selectedIndex
            ].dataset.kategori;

        const stok =
            parseInt(
                selectAlat.options[
                    selectAlat.selectedIndex
                ].dataset.stok
            );

        const jumlah =
            parseInt(jumlahInput.value);

        if (isNaN(jumlah) || jumlah <= 0) {

            alert("Jumlah tidak valid.");

            jumlahInput.focus();

            return;

        }

        if (jumlah > stok) {

            alert("Jumlah melebihi stok.");

            jumlahInput.focus();

            return;

        }

        /*
        ----------------------------------------------------------
        Cek apakah alat sudah ada
        ----------------------------------------------------------
        */

        const sudahAda =
            daftarPeminjaman.find(function(item) {

                return item.id_alat == idAlat;

            });

        if (sudahAda) {

            alert("Alat sudah ada dalam daftar.");

            return;

        }

        /*
        ----------------------------------------------------------
        Tambahkan ke Array
        ----------------------------------------------------------
        */

        daftarPeminjaman.push({

            id_alat: idAlat,

            kategori: kategori,

            nama: namaAlat,

            stok: stok,

            jumlah: jumlah

        });

        renderTable();

        selectAlat.selectedIndex = 0;

        jumlahInput.value = 1;

    });



    /*
    |--------------------------------------------------------------------------
    | Render Tabel
    |--------------------------------------------------------------------------
    */

    function renderTable() {

        const tbody = document.getElementById("daftarAlat");

        const hiddenContainer =
            document.getElementById("hiddenInputContainer");

        tbody.innerHTML = "";

        hiddenContainer.innerHTML = "";

        let totalJenis = 0;

        let totalUnit = 0;



        if (daftarPeminjaman.length == 0) {

            tbody.innerHTML = `

        <tr id="barisKosong">

            <td colspan="6"

            class="text-center text-muted">

                Belum ada alat yang dipilih.

            </td>

        </tr>

        `;

            document.getElementById("btnSimpan").disabled = true;

            document.getElementById("totalJenis").innerHTML = 0;

            document.getElementById("totalUnit").innerHTML = 0;

            return;

        }

        document.getElementById("btnSimpan").disabled = false;

        daftarPeminjaman.forEach(function(item, index) {

            totalJenis++;

            totalUnit += item.jumlah;

            tbody.innerHTML += `

        <tr>

            <td class="text-center">

                ${index+1}

            </td>

            <td>

                ${item.kategori}

            </td>

            <td>

                ${item.nama}

            </td>

            <td class="text-center">

                ${item.stok}

            </td>

            <td class="text-center">

                ${item.jumlah}

            </td>

            <td class="text-center">

                <button

                type="button"

                class="btn btn-danger btn-sm"

                onclick="hapusItem(${index})">

                <i class="bi bi-trash"></i>

                </button>

            </td>

        </tr>

        `;

            /*
            ----------------------------------------------------------
            Hidden Input
            ----------------------------------------------------------
            */

            hiddenContainer.innerHTML += `

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

        document.getElementById("totalJenis").innerHTML = totalJenis;

        document.getElementById("totalUnit").innerHTML = totalUnit;

    }



    /*
    |--------------------------------------------------------------------------
    | Hapus Item
    |--------------------------------------------------------------------------
    */

    function hapusItem(index) {

        if (confirm("Hapus alat ini?")) {

            daftarPeminjaman.splice(index, 1);

            renderTable();

        }

    }



    /*
    |--------------------------------------------------------------------------
    | Reset Form
    |--------------------------------------------------------------------------
    */

    document.getElementById("btnReset")

        .addEventListener("click", function() {

            daftarPeminjaman = [];

            renderTable();

        });



    /*
    |--------------------------------------------------------------------------
    | Validasi Submit
    |--------------------------------------------------------------------------
    */

    document.getElementById("formPeminjaman")

        .addEventListener("submit", function(e) {

            if (daftarPeminjaman.length == 0) {

                e.preventDefault();

                alert("Tambahkan minimal satu alat.");

                return;

            }

            const tglPinjam = document.querySelector(
                "[name='tanggal_pinjam']"
            ).value;

            const tglKembali = document.querySelector(
                "[name='tanggal_kembali']"
            ).value;

            if (tglKembali < tglPinjam) {

                e.preventDefault();

                alert("Tanggal kembali tidak boleh lebih kecil dari tanggal pinjam.");

                return;

            }

        });



    /*
    |--------------------------------------------------------------------------
    | Inisialisasi
    |--------------------------------------------------------------------------
    */

    renderTable();
</script>
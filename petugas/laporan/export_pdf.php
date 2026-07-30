<?php

require '../../config/session.php';
require '../../config/database.php';
require '../../library/fpdf/fpdf.php';

/*
|--------------------------------------------------------------------------
| Validasi Login
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION['id_user'])) {
    header('Location: ../../login.php');

    exit();
}

/*
|--------------------------------------------------------------------------
| Validasi Hak Akses
|--------------------------------------------------------------------------
*/

if ($_SESSION['level'] != 'Petugas') {
    header('Location: ../../unauthorized.php');

    exit();
}

/*
|--------------------------------------------------------------------------
| Mengambil Parameter Filter
|--------------------------------------------------------------------------
*/

$tanggalAwal = trim($_GET['tanggal_awal'] ?? '');

$tanggalAkhir = trim($_GET['tanggal_akhir'] ?? '');

$status = trim($_GET['status'] ?? '');

/*
|--------------------------------------------------------------------------
| Query Dasar
|--------------------------------------------------------------------------
*/

$sql = "

    SELECT

        p.id_peminjaman,
        p.tanggal_pinjam,
        p.tanggal_kembali,
        p.status,

        u.nama_lengkap,

        COUNT(dp.id_detail) AS jumlah_alat

    FROM peminjaman p

    INNER JOIN users u

        ON p.id_user = u.id_user

    LEFT JOIN detail_peminjaman dp

        ON p.id_peminjaman = dp.id_peminjaman

";

/*
|--------------------------------------------------------------------------
| Menyusun WHERE Dinamis
|--------------------------------------------------------------------------
*/

$where = [];

$params = [];

$types = '';

/*
|--------------------------------------------------------------------------
| Filter Tanggal
|--------------------------------------------------------------------------
*/

if (!empty($tanggalAwal) && !empty($tanggalAkhir)) {
    $where[] = "

        DATE(p.tanggal_pinjam)

        BETWEEN ?

        AND ?

    ";

    $types .= 'ss';

    $params[] = $tanggalAwal;

    $params[] = $tanggalAkhir;
}

/*
|--------------------------------------------------------------------------
| Filter Status
|--------------------------------------------------------------------------
*/

if (!empty($status)) {
    $where[] = "

        p.status = ?

    ";

    $types .= 's';

    $params[] = $status;
}

/*
|--------------------------------------------------------------------------
| Menambahkan WHERE
|--------------------------------------------------------------------------
*/

if (!empty($where)) {
    $sql .=
        "

        WHERE

        " . implode(' AND ', $where);
}

/*
|--------------------------------------------------------------------------
| GROUP BY dan ORDER BY
|--------------------------------------------------------------------------
*/

$sql .= "

    GROUP BY

        p.id_peminjaman,
        p.tanggal_pinjam,
        p.tanggal_kembali,
        p.status,
        u.nama_lengkap

    ORDER BY

        p.tanggal_pinjam ASC

";

/*
|--------------------------------------------------------------------------
| Prepared Statement
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die('Query gagal dipersiapkan.');
}

if (!empty($params)) {
    $stmt->bind_param(
        $types,

        ...$params,
    );
}

$stmt->execute();

$result = $stmt->get_result();

$totalData = $result->num_rows;

/*
|--------------------------------------------------------------------------
| Menentukan Periode Laporan
|--------------------------------------------------------------------------
*/

if (!empty($tanggalAwal) && !empty($tanggalAkhir)) {
    $periode = date('d-m-Y', strtotime($tanggalAwal)) . ' s.d. ' . date('d-m-Y', strtotime($tanggalAkhir));
} else {
    $periode = 'Seluruh Periode';
}
/*
|--------------------------------------------------------------------------
| Membuat Dokumen PDF
|--------------------------------------------------------------------------
*/

$pdf = new FPDF('P', 'mm', 'A4');

$pdf->SetMargins(10, 10, 10);

$pdf->AddPage();

$pdf->SetAutoPageBreak(true, 15);

/*
|--------------------------------------------------------------------------
| Header Sekolah
|--------------------------------------------------------------------------
*/

$pdf->SetFont('Arial', 'B', 16);

$pdf->Cell(0, 8, 'SMK NEGERI TEKNOLOGI NUSANTARA', 0, 1, 'C');

$pdf->SetFont('Arial', 'B', 14);

$pdf->Cell(0, 8, 'LAPORAN PEMINJAMAN ALAT', 0, 1, 'C');

$pdf->SetFont('Arial', '', 10);

$pdf->Cell(0, 6, 'Jl. Pendidikan No.123 Kota Nusantara', 0, 1, 'C');

$pdf->Ln(2);

$pdf->Cell(190, 0, '', 'T', 1);

$pdf->Ln(6);

/*
|--------------------------------------------------------------------------
| Informasi Laporan
|--------------------------------------------------------------------------
*/

$pdf->SetFont('Arial', '', 10);

$pdf->Cell(35, 6, 'Periode', 0, 0);

$pdf->Cell(5, 6, ':', 0, 0);

$pdf->Cell(100, 6, $periode, 0, 1);

$pdf->Cell(35, 6, 'Status', 0, 0);

$pdf->Cell(5, 6, ':', 0, 0);

$pdf->Cell(100, 6, empty($status) ? 'Semua Status' : $status, 0, 1);

$pdf->Cell(35, 6, 'Tanggal Cetak', 0, 0);

$pdf->Cell(5, 6, ':', 0, 0);

$pdf->Cell(100, 6, date('d-m-Y H:i:s'), 0, 1);

$pdf->Ln(6);

/*
|--------------------------------------------------------------------------
| Header Tabel
|--------------------------------------------------------------------------
*/

$pdf->SetFont('Arial', 'B', 10);

$pdf->SetFillColor(220, 220, 220);

$pdf->Cell(10, 8, 'No', 1, 0, 'C', true);

$pdf->Cell(32, 8, 'Tgl Pinjam', 1, 0, 'C', true);

$pdf->Cell(32, 8, 'Tgl Kembali', 1, 0, 'C', true);

$pdf->Cell(60, 8, 'Peminjam', 1, 0, 'C', true);

$pdf->Cell(25, 8, 'Jml Alat', 1, 0, 'C', true);

$pdf->Cell(31, 8, 'Status', 1, 1, 'C', true);
/*
|--------------------------------------------------------------------------
| Menampilkan Data Laporan
|--------------------------------------------------------------------------
*/

$pdf->SetFont('Arial', '', 10);

$no = 1;

$totalAlat = 0;

while ($row = $result->fetch_assoc()) {
    $jumlahAlat = (int) $row['jumlah_alat'];

    $totalAlat += $jumlahAlat;

    /*
    |--------------------------------------------------------------------------
    | Cek Pergantian Halaman
    |--------------------------------------------------------------------------
    */

    if ($pdf->GetY() > 260) {
        $pdf->AddPage();

        $pdf->SetFont('Arial', 'B', 10);

        $pdf->SetFillColor(220, 220, 220);

        $pdf->Cell(10, 8, 'No', 1, 0, 'C', true);
        $pdf->Cell(32, 8, 'Tgl Pinjam', 1, 0, 'C', true);
        $pdf->Cell(32, 8, 'Tgl Kembali', 1, 0, 'C', true);
        $pdf->Cell(60, 8, 'Peminjam', 1, 0, 'C', true);
        $pdf->Cell(25, 8, 'Jml Alat', 1, 0, 'C', true);
        $pdf->Cell(31, 8, 'Status', 1, 1, 'C', true);

        $pdf->SetFont('Arial', '', 10);
    }

    $pdf->Cell(10, 8, $no++, 1, 0, 'C');

    $pdf->Cell(32, 8, date('d-m-Y', strtotime($row['tanggal_pinjam'])), 1, 0, 'C');

    $pdf->Cell(32, 8, date('d-m-Y', strtotime($row['tanggal_kembali'])), 1, 0, 'C');

    $pdf->Cell(60, 8, $row['nama_lengkap'], 1, 0);

    $pdf->Cell(25, 8, $jumlahAlat, 1, 0, 'C');

    $pdf->Cell(31, 8, $row['status'], 1, 1, 'C');
}

/*
|--------------------------------------------------------------------------
| Data Kosong
|--------------------------------------------------------------------------
*/

if ($totalData == 0) {
    $pdf->Cell(190, 10, 'Tidak ada data laporan.', 1, 1, 'C');
}

/*
|--------------------------------------------------------------------------
| Ringkasan Laporan
|--------------------------------------------------------------------------
*/

$pdf->Ln(8);

$pdf->SetFont('Arial', 'B', 10);

$pdf->Cell(
    45,
    7,
    'Total Transaksi',

    0,
    0,
);

$pdf->Cell(
    5,
    7,
    ':',

    0,
    0,
);

$pdf->Cell(
    30,
    7,
    $totalData,

    0,
    1,
);

$pdf->Cell(
    45,
    7,
    'Total Alat Dipinjam',

    0,
    0,
);

$pdf->Cell(
    5,
    7,
    ':',

    0,
    0,
);

$pdf->Cell(
    30,
    7,
    $totalAlat,

    0,
    1,
);

/*
|--------------------------------------------------------------------------
| Tanda Tangan
|--------------------------------------------------------------------------
*/

$pdf->Ln(15);

$pdf->Cell(
    120,
    6,
    '',

    0,
    0,
);

$pdf->Cell(
    60,
    6,
    date('d F Y'),

    0,
    1,
    'C',
);

$pdf->Cell(
    120,
    6,
    '',

    0,
    0,
);

$pdf->Cell(
    60,
    6,
    'Petugas',

    0,
    1,
    'C',
);

$pdf->Ln(22);

$pdf->Cell(
    120,
    6,
    '',

    0,
    0,
);

$pdf->Cell(
    60,
    6,
    $_SESSION['nama_lengkap'],

    0,
    1,
    'C',
);
/*
|--------------------------------------------------------------------------
| Menampilkan PDF
|--------------------------------------------------------------------------
*/

$pdf->Output(
    'I',

    'Laporan_Peminjaman_' . date('Ymd_His') . '.pdf',
);

/*
|--------------------------------------------------------------------------
| Membebaskan Result
|--------------------------------------------------------------------------
*/

if (isset($result) && $result instanceof mysqli_result) {
    $result->free();
}

/*
|--------------------------------------------------------------------------
| Menutup Statement
|--------------------------------------------------------------------------
*/

if (isset($stmt)) {
    $stmt->close();
}

/*
|--------------------------------------------------------------------------
| Menutup Koneksi Database
|--------------------------------------------------------------------------
*/

$conn->close();

?>

<?php
require 'koneksi.php';

$jenisBencana = [
    'banjir' => 'Banjir',
    'banjir_bandang' => 'Banjir Bandang',
    'gelombang_pasang' => 'Gelombang Pasang',
    'abrasi' => 'Abrasi',
    'tanah_longsor' => 'Tanah Longsor',
    'angin_puting_beliung' => 'Angin Puting Beliung',
    'gunung_meletus' => 'Gunung Meletus',
    'karhutla' => 'Karhutla',
    'kekeringan' => 'Kekeringan',
    'gempa_bumi' => 'Gempa Bumi',
    'tsunami' => 'Tsunami'
];

$pesan = '';
$error = '';

/* =========================
   SIMPAN DATA
========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['aksi'] ?? '') === 'simpan') {

    $kecamatan_id = (int)($_POST['kecamatan_id'] ?? 0);
    $tahun = (int)($_POST['tahun'] ?? 0);
    $jenis = $_POST['jenis_kolom'] ?? '';
    $jumlah = max(0, (int)($_POST['jumlah'] ?? 0));

    if (
        $kecamatan_id <= 0 ||
        $tahun <= 0 ||
        !array_key_exists($jenis, $jenisBencana)
    ) {
        $error = 'Data yang dimasukkan belum lengkap atau tidak valid.';
    } else {

        /*
         * Nama kolom berasal dari whitelist di atas,
         * sehingga aman digunakan sebagai identifier SQL.
         */
        $jenisSql = '`' . $jenis . '`';

        $cek = $conn->prepare(
            "SELECT id
             FROM kejadian_bencana
             WHERE kecamatan_id = ? AND tahun = ?
             LIMIT 1"
        );

        $cek->bind_param('ii', $kecamatan_id, $tahun);
        $cek->execute();
        $hasilCek = $cek->get_result();
        $rowAda = $hasilCek->fetch_assoc();
        $cek->close();

        if ($rowAda) {

            $id = (int)$rowAda['id'];

            $sqlUpdate =
                "UPDATE kejadian_bencana
                 SET $jenisSql = ?
                 WHERE id = ?";

            $stmt = $conn->prepare($sqlUpdate);
            $stmt->bind_param('ii', $jumlah, $id);

            if ($stmt->execute()) {
                $pesan = 'Data berhasil diperbarui.';
            } else {
                $error = 'Gagal memperbarui data: ' . $stmt->error;
            }

            $stmt->close();

        } else {

            /*
             * Satu kombinasi kecamatan + tahun = satu baris.
             * Kolom bencana lain otomatis bernilai 0.
             */
            $kolom = array_keys($jenisBencana);

            $namaKolom = '`kecamatan_id`, `tahun`, `' .
                implode('`, `', $kolom) . '`';

            $nilaiPlaceholder = '?, ?, ' .
                implode(', ', array_fill(0, count($kolom), '0'));

            $sqlInsert =
                "INSERT INTO kejadian_bencana
                 ($namaKolom)
                 VALUES ($nilaiPlaceholder)";

            $stmt = $conn->prepare($sqlInsert);
            $stmt->bind_param('ii', $kecamatan_id, $tahun);

            if ($stmt->execute()) {
                /*
                 * Karena semua jenis bencana awalnya 0,
                 * isi jenis yang dipilih setelah row dibuat.
                 */
                $idBaru = $stmt->insert_id;
                $stmt->close();

                $sqlUpdate =
                    "UPDATE kejadian_bencana
                     SET $jenisSql = ?
                     WHERE id = ?";

                $stmt2 = $conn->prepare($sqlUpdate);
                $stmt2->bind_param('ii', $jumlah, $idBaru);

                if ($stmt2->execute()) {
                    $pesan = 'Data berhasil ditambahkan.';
                } else {
                    $error = 'Data dasar berhasil dibuat, tetapi jumlah bencana gagal disimpan.';
                }

                $stmt2->close();

            } else {
                $error = 'Gagal menambahkan data: ' . $stmt->error;
                $stmt->close();
            }
        }
    }
}

/* =========================
   HAPUS DATA
========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['aksi'] ?? '') === 'hapus') {

    $idHapus = (int)($_POST['id'] ?? 0);

    if ($idHapus > 0) {

        $stmt = $conn->prepare(
            "DELETE FROM kejadian_bencana WHERE id = ?"
        );

        $stmt->bind_param('i', $idHapus);

        if ($stmt->execute()) {
            $pesan = 'Data berhasil dihapus.';
        } else {
            $error = 'Gagal menghapus data: ' . $stmt->error;
        }

        $stmt->close();
    }
}

/* =========================
   DATA KECAMATAN
========================= */
$kecamatanList = [];

$qKecamatan = $conn->query(
    "SELECT id, nama
     FROM kecamatan
     ORDER BY nama ASC"
);

if ($qKecamatan) {
    while ($row = $qKecamatan->fetch_assoc()) {
        $kecamatanList[] = $row;
    }
}

/* =========================
   DAFTAR TAHUN DINAMIS
========================= */
$tahunList = [];
$qTahun = $conn->query(
    "SELECT DISTINCT tahun FROM kejadian_bencana
     WHERE tahun IS NOT NULL
     ORDER BY tahun DESC"
);
if ($qTahun) {
    while ($row = $qTahun->fetch_assoc()) {
        $tahunList[] = (int)$row['tahun'];
    }
}

/* =========================
   FILTER TABEL
========================= */
$filterKecamatan = trim($_GET['kecamatan'] ?? '');
$filterTahun = trim($_GET['tahun'] ?? '');

$where = [];
$params = [];
$types = '';

if ($filterKecamatan !== '') {
    $where[] = 'k.nama = ?';
    $params[] = $filterKecamatan;
    $types .= 's';
}

if ($filterTahun !== '' && ctype_digit($filterTahun)) {
    $where[] = 'b.tahun = ?';
    $params[] = (int)$filterTahun;
    $types .= 'i';
}

$sqlData = "
    SELECT
        b.id,
        b.tahun,
        k.nama AS kecamatan,
        b.banjir,
        b.banjir_bandang,
        b.gelombang_pasang,
        b.abrasi,
        b.tanah_longsor,
        b.angin_puting_beliung,
        b.gunung_meletus,
        b.karhutla,
        b.kekeringan,
        b.gempa_bumi,
        b.tsunami
    FROM kejadian_bencana b
    INNER JOIN kecamatan k
        ON k.id = b.kecamatan_id
";

if ($where) {
    $sqlData .= ' WHERE ' . implode(' AND ', $where);
}

$sqlData .= "
    ORDER BY b.tahun DESC, k.nama ASC
";

$stmtData = $conn->prepare($sqlData);

if ($params) {
    $stmtData->bind_param($types, ...$params);
}

$stmtData->execute();
$resultData = $stmtData->get_result();

$rows = [];

while ($row = $resultData->fetch_assoc()) {
    $rows[] = $row;
}

$stmtData->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Manajemen Data Bencana - Kabupaten Jember</title>

<style>
* {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

html, body {
    width: 100%;
    height: 100%;
}

body {
    display: flex;
    min-height: 100vh;
    height: 100vh;
    background: #070926;
    color: #fff;
    overflow: hidden;
}

/* ================= SIDEBAR ================= */

.sidebar {
    width: 260px;
    min-height: 100vh;
    height: 100vh;
    position: sticky;
    top: 0;
    flex-shrink: 0;

    background: #030417;
    border-right: 1px solid #1a1e4a;

    padding: 20px;

    display: flex;
    flex-direction: column;
    gap: 18px;
    overflow-y: auto;
    overflow-x: hidden;
}

.user-profile {
    display: flex;
    align-items: center;
    gap: 12px;

    background: #131742;
    padding: 10px 14px;
    border-radius: 20px;
}

.user-avatar {
    width: 38px;
    height: 38px;
    background: #d9d9d9;
    border-radius: 50%;
    flex-shrink: 0;
}

.search-box input {
    width: 100%;
    padding: 9px 14px;

    border: none;
    outline: none;
    border-radius: 15px;

    background: #d9d9d9;
    color: #111;

    font-size: 13px;
    font-weight: 500;
}

.nav-group {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.nav-link {
    display: block;

    text-decoration: none;

    color: #8a99ad;

    padding: 8px 12px;
    border-radius: 8px;

    font-size: 15px;
    font-weight: 700;

    transition: 0.2s;
}

.nav-link:hover,
.nav-link:focus,
.nav-link.active {
    color: #fff;
    background: #131742;
    border-left: 3px solid #3498db;
}

.filter-block {
    border-top: 1px solid #1a1e4a;
    padding-top: 10px;
}

.filter-title {
    color: #fff;
    font-size: 14px;
    font-weight: 700;
    padding: 8px 10px;
    margin: 0;
    border-radius: 8px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: space-between;
    user-select: none;
    transition: 0.2s;
}

.filter-title:hover { background: #131742; }

.filter-title::after {
    content: '▾';
    font-size: 12px;
    color: #8a99ad;
    transition: transform 0.2s;
}

.filter-block.collapsed .filter-title::after { transform: rotate(-90deg); }
.filter-block.collapsed .filter-group { display: none; }

.filter-group {
    display: flex;
    flex-direction: column;
    gap: 6px;

    max-height: 180px;
    overflow-y: auto;

    padding-right: 6px;

    color: #b3b3b3;
    font-size: 13px;
}

.filter-group::-webkit-scrollbar {
    width: 5px;
}

.filter-group::-webkit-scrollbar-thumb {
    background: #1f2766;
    border-radius: 10px;
}

.filter-item {
    display: flex;
    align-items: center;
    gap: 8px;

    cursor: pointer;
    user-select: none;
}

.filter-item input {
    cursor: pointer;
    accent-color: #3498db;
}

/* ================= KONTEN ================= */

.main-content {
    flex: 1;
    min-width: 0;

    padding: 20px;

    display: flex;
    flex-direction: column;
    gap: 20px;
    height: 100vh;
    overflow-y: auto;
    overflow-x: hidden;
}

.page-header {
    background: #000133;
    border: 1px solid #1b205a;
    border-radius: 16px;

    padding: 20px;
}

.page-header h1 {
    font-size: 24px;
    margin-bottom: 5px;
}

.page-header p {
    color: #9ea6c7;
    font-size: 14px;
}

/* ================= ALERT ================= */

.alert {
    padding: 12px 16px;
    border-radius: 10px;
    font-weight: 600;
    font-size: 14px;
}

.alert.success {
    background: #123d2d;
    border: 1px solid #1d6d4b;
    color: #75e6b0;
}

.alert.error {
    background: #431c25;
    border: 1px solid #8e3345;
    color: #ff9cac;
}

/* ================= CARD ================= */

.card {
    background: #000133;
    border: 1px solid #1b205a;
    border-radius: 16px;
    padding: 20px;

    box-shadow: 0 4px 10px rgba(0,0,0,0.3);
}

.card h2 {
    font-size: 17px;
    margin-bottom: 16px;
}

/* ================= FORM ================= */

.form-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 14px;
    align-items: end;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 7px;
}

.form-group label {
    color: #bfc5df;
    font-size: 13px;
    font-weight: 600;
}

.form-group select,
.form-group input {
    width: 100%;

    padding: 10px 12px;

    border: 1px solid #293071;
    border-radius: 9px;

    background: #10143b;
    color: #fff;

    outline: none;
}

.form-group select:focus,
.form-group input:focus {
    border-color: #3498db;
}

.btn {
    border: none;
    border-radius: 9px;

    padding: 10px 15px;

    cursor: pointer;

    font-weight: 700;
    font-size: 13px;
}

.btn-primary {
    background: #3498db;
    color: #fff;
}

.btn-primary:hover {
    background: #2980b9;
}

.btn-danger {
    background: #c0392b;
    color: #fff;
}

.btn-danger:hover {
    background: #962d22;
}

/* ================= FILTER ================= */

.filter-bar {
    display: grid;
    grid-template-columns: 1fr 180px auto;
    gap: 10px;
    align-items: end;
}

.filter-bar label {
    display: block;
    color: #bfc5df;
    font-size: 13px;
    margin-bottom: 6px;
}

.filter-bar select {
    width: 100%;
    padding: 10px 12px;

    background: #10143b;
    color: #fff;

    border: 1px solid #293071;
    border-radius: 9px;
}

/* ================= TABLE ================= */

.table-wrapper {
    width: 100%;
    overflow-x: auto;
}

table {
    width: 100%;
    border-collapse: collapse;
    min-width: 900px;
}

thead th {
    background: #131742;
    color: #fff;

    padding: 11px 10px;

    font-size: 12px;
    text-align: center;

    white-space: nowrap;
}

tbody td {
    padding: 10px;

    border-bottom: 1px solid #1b205a;

    color: #d7dbed;

    font-size: 13px;
    text-align: center;
}

tbody tr:hover {
    background: #0c1034;
}

.badge {
    display: inline-block;

    padding: 4px 9px;

    border-radius: 10px;

    background: #131742;
    color: #9ecfff;

    font-weight: 700;
}

/* ================= SCROLLBAR ================= */
.sidebar::-webkit-scrollbar,
.main-content::-webkit-scrollbar { width: 8px; }
.sidebar::-webkit-scrollbar-track,
.main-content::-webkit-scrollbar-track { background: #05061b; }
.sidebar::-webkit-scrollbar-thumb,
.main-content::-webkit-scrollbar-thumb { background: #252b68; border-radius: 10px; }
.sidebar::-webkit-scrollbar-thumb:hover,
.main-content::-webkit-scrollbar-thumb:hover { background: #3498db; }

/* ================= RESPONSIVE ================= */

@media (max-width: 1100px) {

    .sidebar {
        width: 230px;
    }

    .form-grid {
        grid-template-columns: repeat(2, 1fr);
    }

}

@media (max-width: 800px) {

    body {
        display: block;
        height: auto;
        min-height: 100vh;
        overflow-y: auto;
        overflow-x: hidden;
    }

    .sidebar {
        position: relative;
        width: 100%;
        height: auto;
        min-height: auto;
        overflow: visible;
    }

    .main-content {
        width: 100%;
        height: auto;
        overflow: visible;
    }

    .filter-bar {
        grid-template-columns: 1fr;
    }

}

@media (max-width: 600px) {

    .form-grid {
        grid-template-columns: 1fr;
    }

    .main-content {
        padding: 12px;
    }

}
</style>
</head>

<body>

<!-- ================= SIDEBAR ================= -->

<div class="sidebar">

    <div class="user-profile">
        <div class="user-avatar"></div>
        <span style="font-weight:700;font-size:14px;">Polije</span>
    </div>

    <div class="search-box">
        <input
            type="text"
            id="searchInput"
            placeholder="Search Kecamatan..."
            onkeyup="filterKecamatanSearch()"
        >
    </div>

    <div class="nav-group">

        <a href="index.php" class="nav-link">
            Dashboard
        </a>

        <a href="index.php#section-statistik" class="nav-link">
            Statistik
        </a>

        <a href="manajemen.php" class="nav-link active">
            Manajemen
        </a>

        <a href="spk.php" class="nav-link">
            Pendukung Keputusan
        </a>

    </div>

    <div class="filter-block">
        <div class="filter-title" onclick="toggleFilterBlock(this)">
            Kecamatan
        </div>

        <div
            class="filter-group"
            id="kecamatanFilterList"
        >

            <label class="filter-item">
                <input
                    type="radio"
                    name="sidebar_kec"
                    value=""
                    checked
                    onclick="filterTabel()"
                >
                Semua
            </label>

            <?php foreach ($kecamatanList as $k): ?>

                <label class="filter-item">

                    <input
                        type="radio"
                        name="sidebar_kec"
                        value="<?= htmlspecialchars($k['nama']) ?>"
                        onclick="filterTabel()"
                    >

                    <?= htmlspecialchars($k['nama']) ?>

                </label>

            <?php endforeach; ?>

        </div>
    </div>

    <div class="filter-block">

        <div class="filter-title" onclick="toggleFilterBlock(this)">
            Tahun
        </div>

        <div class="filter-group">

            <label class="filter-item">

                <input
                    type="radio"
                    name="sidebar_tahun"
                    value=""
                    checked
                    onclick="filterTabel()"
                >

                Semua

            </label>

            <?php foreach ($tahunList as $tahun): ?>

                <label class="filter-item">

                    <input
                        type="radio"
                        name="sidebar_tahun"
                        value="<?= $tahun ?>"
                        onclick="filterTabel()"
                    >

                    <?= $tahun ?>

                </label>

            <?php endforeach; ?>

        </div>

    </div>

</div>


<!-- ================= KONTEN ================= -->

<div class="main-content">

    <div class="page-header">

        <h1>
            Manajemen Data Bencana
        </h1>

        <p>
            Kelola data kejadian bencana berdasarkan kecamatan dan tahun.
        </p>

    </div>


    <?php if ($pesan): ?>

        <div class="alert success">
            <?= htmlspecialchars($pesan) ?>
        </div>

    <?php endif; ?>


    <?php if ($error): ?>

        <div class="alert error">
            <?= htmlspecialchars($error) ?>
        </div>

    <?php endif; ?>


    <!-- ================= FORM ================= -->

    <div class="card">

        <h2>
            Tambah / Perbarui Data
        </h2>

        <form method="POST">

            <input
                type="hidden"
                name="aksi"
                value="simpan"
            >

            <div class="form-grid">

                <div class="form-group">

                    <label>
                        Kecamatan
                    </label>

                    <select
                        name="kecamatan_id"
                        required
                    >

                        <option value="">
                            -- Pilih Kecamatan --
                        </option>

                        <?php foreach ($kecamatanList as $k): ?>

                            <option
                                value="<?= (int)$k['id'] ?>"
                            >
                                <?= htmlspecialchars($k['nama']) ?>
                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>


                <div class="form-group">

                    <label>
                        Tahun
                    </label>

                    <input
                        type="number"
                        name="tahun"
                        min="1"
                        max="9999"
                        step="1"
                        placeholder="Contoh: 2025"
                        required
                    >


                </div>


                <div class="form-group">

                    <label>
                        Jenis Bencana
                    </label>

                    <select
                        name="jenis_kolom"
                        required
                    >

                        <option value="">
                            -- Pilih Jenis --
                        </option>

                        <?php foreach ($jenisBencana as $kolom => $label): ?>

                            <option
                                value="<?= htmlspecialchars($kolom) ?>"
                            >
                                <?= htmlspecialchars($label) ?>
                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>


                <div class="form-group">

                    <label>
                        Jumlah Kejadian
                    </label>

                    <input
                        type="number"
                        name="jumlah"
                        min="0"
                        value="0"
                        required
                    >

                </div>

            </div>

            <div style="margin-top:15px;">

                <button
                    type="submit"
                    class="btn btn-primary"
                >
                    Simpan Data
                </button>

            </div>

        </form>

    </div>


    <!-- ================= FILTER ================= -->

    <div class="card">

        <h2>
            Data Tersimpan
        </h2>

        <form
            method="GET"
            class="filter-bar"
        >

            <div>

                <label>
                    Kecamatan
                </label>

                <select name="kecamatan">

                    <option value="">
                        Semua Kecamatan
                    </option>

                    <?php foreach ($kecamatanList as $k): ?>

                        <option
                            value="<?= htmlspecialchars($k['nama']) ?>"
                            <?= $filterKecamatan === $k['nama'] ? 'selected' : '' ?>
                        >
                            <?= htmlspecialchars($k['nama']) ?>
                        </option>

                    <?php endforeach; ?>

                </select>

            </div>


            <div>

                <label>
                    Tahun
                </label>

                <select name="tahun">

                    <option value="">
                        Semua Tahun
                    </option>

                    <?php foreach ($tahunList as $tahun): ?>

                        <option
                            value="<?= $tahun ?>"
                            <?= $filterTahun === (string)$tahun ? 'selected' : '' ?>
                        >
                            <?= $tahun ?>
                        </option>

                    <?php endforeach; ?>

                </select>

            </div>


            <div>

                <button
                    type="submit"
                    class="btn btn-primary"
                >
                    Terapkan Filter
                </button>

            </div>

        </form>

    </div>


    <!-- ================= TABEL ================= -->

    <div class="card">

        <div class="table-wrapper">

            <table>

                <thead>

                    <tr>

                        <th>ID</th>
                        <th>Tahun</th>
                        <th>Kecamatan</th>
                        <th>Banjir</th>
                        <th>Longsor</th>
                        <th>Puting Beliung</th>
                        <th>Total</th>
                        <th>Aksi</th>

                    </tr>

                </thead>

                <tbody id="dataTableBody">

                <?php if (!$rows): ?>

                    <tr>

                        <td
                            colspan="8"
                            style="text-align:center;"
                        >
                            Tidak ada data.
                        </td>

                    </tr>

                <?php else: ?>

                    <?php foreach ($rows as $row): ?>

                        <?php
                        $total =
                            (int)$row['banjir'] +
                            (int)$row['banjir_bandang'] +
                            (int)$row['gelombang_pasang'] +
                            (int)$row['abrasi'] +
                            (int)$row['tanah_longsor'] +
                            (int)$row['angin_puting_beliung'] +
                            (int)$row['gunung_meletus'] +
                            (int)$row['karhutla'] +
                            (int)$row['kekeringan'] +
                            (int)$row['gempa_bumi'] +
                            (int)$row['tsunami'];
                        ?>

                        <tr
                            data-kecamatan="<?= htmlspecialchars(strtolower($row['kecamatan'])) ?>"
                            data-tahun="<?= (int)$row['tahun'] ?>"
                        >

                            <td>
                                <?= (int)$row['id'] ?>
                            </td>

                            <td>
                                <span class="badge">
                                    <?= (int)$row['tahun'] ?>
                                </span>
                            </td>

                            <td>
                                <b>
                                    <?= htmlspecialchars($row['kecamatan']) ?>
                                </b>
                            </td>

                            <td>
                                <?= (int)$row['banjir'] ?>
                            </td>

                            <td>
                                <?= (int)$row['tanah_longsor'] ?>
                            </td>

                            <td>
                                <?= (int)$row['angin_puting_beliung'] ?>
                            </td>

                            <td>
                                <b>
                                    <?= $total ?>
                                </b>
                            </td>

                            <td>

                                <form
                                    method="POST"
                                    onsubmit="return confirm('Yakin ingin menghapus data ini?');"
                                >

                                    <input
                                        type="hidden"
                                        name="aksi"
                                        value="hapus"
                                    >

                                    <input
                                        type="hidden"
                                        name="id"
                                        value="<?= (int)$row['id'] ?>"
                                    >

                                    <button
                                        type="submit"
                                        class="btn btn-danger"
                                    >
                                        Hapus
                                    </button>

                                </form>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                <?php endif; ?>

                </tbody>

            </table>

        </div>

    </div>

</div>


<script>
function toggleFilterBlock(title) {
    const block = title.closest('.filter-block');
    if (block) block.classList.toggle('collapsed');
}


function filterKecamatanSearch() {

    const input =
        document
            .getElementById('searchInput')
            .value
            .toLowerCase()
            .trim();

    document
        .querySelectorAll(
            '#kecamatanFilterList .filter-item'
        )
        .forEach(item => {

            const text =
                item.innerText.toLowerCase();

            item.style.display =
                text.includes(input)
                    ? 'flex'
                    : 'none';

        });
}


function filterTabel() {

    const kec =
        document
            .querySelector(
                'input[name="sidebar_kec"]:checked'
            )?.value || '';

    const tahun =
        document
            .querySelector(
                'input[name="sidebar_tahun"]:checked'
            )?.value || '';

    const rows =
        document.querySelectorAll(
            '#dataTableBody tr[data-kecamatan]'
        );

    rows.forEach(row => {

        const rowKec =
            row.dataset.kecamatan || '';

        const rowTahun =
            row.dataset.tahun || '';

        const cocokKec =
            !kec ||
            rowKec === kec.toLowerCase();

        const cocokTahun =
            !tahun ||
            rowTahun === tahun;

        row.style.display =
            cocokKec && cocokTahun
                ? ''
                : 'none';

    });
}
</script>

</body>
</html>

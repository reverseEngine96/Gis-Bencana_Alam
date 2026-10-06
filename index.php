<?php
require 'koneksi.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard Bencana Kabupaten Jember</title>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
* {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

html {
    scroll-behavior: smooth;
}

body {
    display: flex;
    background: #070926;
    color: #fff;
    min-height: 100vh;
    overflow-x: hidden;
}

/* ================= SIDEBAR ================= */

.sidebar {
    width: 260px;
    background: #030417;
    padding: 20px;
    display: flex;
    flex-direction: column;
    gap: 18px;
    border-right: 1px solid #1a1e4a;
    height: 100vh;
    position: sticky;
    top: 0;
    flex-shrink: 0;
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
}

.search-box input {
    width: 100%;
    padding: 9px 14px;
    border-radius: 15px;
    border: none;
    background: #d9d9d9;
    color: #111;
    outline: none;
    font-size: 13px;
    font-weight: 500;
}

.nav-group {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.nav-link {
    text-decoration: none;
    font-size: 15px;
    font-weight: 700;
    color: #8a99ad;
    padding: 8px 12px;
    cursor: pointer;
    border-radius: 8px;
    transition: 0.2s;
    display: block;
}

.nav-link:hover,
.nav-link:focus,
.nav-link.active {
    color: #fff;
    background: #131742;
    border-left: 3px solid #3498db;
}

/* ================= FILTER SIDEBAR ================= */

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

.filter-title:hover {
    background: #131742;
}

.filter-title::after {
    content: '▾';
    font-size: 12px;
    color: #8a99ad;
    transition: transform 0.2s;
}

.filter-block.collapsed .filter-title::after {
    transform: rotate(-90deg);
}

.filter-block.collapsed .filter-group {
    display: none;
}

.filter-group {
    font-size: 13px;
    color: #b3b3b3;
    display: flex;
    flex-direction: column;
    gap: 6px;
    max-height: 180px;
    overflow-y: auto;
    padding-right: 6px;
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
    display: flex;
    flex-direction: column;
    overflow-y: auto;
    padding: 20px;
    gap: 40px;
}

#section-dashboard {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

/* ================= CARD ATAS ================= */

.top-cards {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 16px;
}

.card-top {
    background: #000133;
    border-radius: 16px;
    padding: 20px;
    text-align: center;
    border: 1px solid #1b205a;
    box-shadow: 0 4px 10px rgba(0,0,0,0.3);
}

.card-top h3 {
    font-size: 14px;
    font-weight: 600;
    margin-bottom: 8px;
    color: #d0d3d4;
}

.card-top .val {
    font-size: 28px;
    font-weight: 800;
    color: #fff;
}

/* ================= MAP ================= */

#map,
#map-stat {
    width: 100%;
    min-width: 0;
    display: block;
    border-radius: 14px;
    border: 1px solid #1b205a;
    box-shadow: 0 4px 10px rgba(0,0,0,0.3);
}

#map {
    width: 100%;
    height: 380px;
    z-index: 1;
}

/* ================= MAP HOVER INFO ================= */
.map-info-control {
    min-width: 210px;
    max-width: 280px;
    background: rgba(3, 4, 23, 0.94);
    color: #fff;
    padding: 12px 14px;
    border: 1px solid #3498db;
    border-radius: 10px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.35);
    pointer-events: none;
}

.map-info-control .info-title {
    font-size: 15px;
    font-weight: 800;
    margin-bottom: 7px;
    color: #fff;
}

.map-info-control .info-total {
    font-size: 13px;
    color: #8fd3ff;
    font-weight: 700;
    margin-bottom: 7px;
}

.map-info-control .info-row {
    display: flex;
    justify-content: space-between;
    gap: 14px;
    font-size: 12px;
    color: #cbd2e8;
    padding: 2px 0;
}

.map-info-control .info-row span:last-child {
    color: #fff;
    font-weight: 700;
}

.geojson-hover {
    cursor: pointer;
}

#map-stat {
    width: 100%;
    border-radius: 14px;
    border: 1px solid #1b205a;
    box-shadow: 0 4px 10px rgba(0,0,0,0.3);
}

#map-stat {
    width: 100%;
    height: 380px;
    min-width: 0;
    border-color: #ddd;
}

/* ================= CARD BENCANA ================= */

.bencana-grid {
    display: grid;
    grid-template-columns: repeat(6, 1fr);
    gap: 12px;
}

.card-bencana {
    background: #000133;
    padding: 14px 8px;
    border-radius: 12px;
    text-align: center;
    border: 1px solid #1b205a;
    box-shadow: 0 2px 6px rgba(0,0,0,0.2);
}

.card-bencana .title {
    font-size: 11px;
    margin-bottom: 6px;
    color: #bdc3c7;
    text-transform: capitalize;
    font-weight: 600;
}

.card-bencana .count {
    font-size: 20px;
    font-weight: 800;
    color: #fff;
}

.card-bencana.highlight {
    background: #1a4369;
    border-color: #2980b9;
}

/* ================= STATISTIK ================= */

#section-statistik {
    background: #f4f6f9;
    color: #333;
    border-radius: 16px;
    padding: 24px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    margin-bottom: 20px;
}

.stat-header {
    font-size: 18px;
    font-weight: 800;
    color: #222;
    margin-bottom: 20px;
    text-transform: uppercase;
}

.stat-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}

.full-width {
    grid-column: 1 / -1;
}

.stat-card {
    background: #fff;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    border: 1px solid #eaeaea;
    display: flex;
    flex-direction: column;
}

.stat-card-title {
    font-size: 12px;
    font-weight: 800;
    color: #555;
    margin-bottom: 15px;
    text-transform: uppercase;
}

.kpi-box {
    background: #fff;
    border: 1px solid #eee;
    padding: 15px 20px;
    border-radius: 10px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 2px 6px rgba(0,0,0,0.03);
    margin-bottom: 15px;
}

.kpi-box .num {
    font-size: 32px;
    font-weight: 800;
    color: #2c3e50;
    display: flex;
    align-items: center;
    gap: 10px;
}

.kpi-box .badge {
    background: #e8f8f5;
    color: #2ecc71;
    font-size: 13px;
    padding: 4px 8px;
    border-radius: 12px;
    font-weight: bold;
}

.trend-box {
    background: #fff;
    border: 1px solid #eee;
    padding: 15px;
    border-radius: 10px;
    flex: 1;
    box-shadow: 0 2px 6px rgba(0,0,0,0.03);
}

.stat-table-wrapper {
    max-height: 320px;
    overflow-y: auto;
    border: 1px solid #eee;
    border-radius: 8px;
}

.stat-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 12px;
}

.stat-table th {
    background: #131742;
    color: #fff;
    padding: 12px;
    text-align: left;
    position: sticky;
    top: 0;
    z-index: 2;
}

.stat-table td {
    padding: 12px 10px;
    border-bottom: 1px solid #eee;
    color: #444;
    font-weight: 600;
}

.stat-table tr:nth-child(even) {
    background: #f9f9f9;
}

/* ================= RESPONSIVE ================= */

@media (max-width: 1000px) {
    .sidebar {
        width: 220px;
    }

    .bencana-grid {
        grid-template-columns: repeat(4, 1fr);
    }
}

@media (max-width: 750px) {
    body {
        flex-direction: column;
    }

    .sidebar {
        width: 100%;
        height: auto;
        position: relative;
    }

    .top-cards,
    .stat-grid {
        grid-template-columns: 1fr;
    }

    .bencana-grid {
        grid-template-columns: repeat(2, 1fr);
    }

    .full-width {
        grid-column: auto;
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
            placeholder="Search..."
            onkeyup="filterKecamatanSearch()"
        >
    </div>

    <div class="nav-group">
        <a href="#section-dashboard" class="nav-link active">Dashboard</a>
        <a href="#section-statistik" class="nav-link">Statistik</a>
        <a href="manajemen.php" class="nav-link">Manajemen</a>
        <a href="spk.php" class="nav-link">Pendukung Keputusan</a>
    </div>

    <div class="filter-block">
        <div class="filter-title" onclick="toggleFilterBlock(this)">
            Kecamatan
        </div>

        <div class="filter-group" id="kecamatanFilterList">

            <label class="filter-item">
                <input
                    type="radio"
                    name="kec"
                    value="semua"
                    checked
                    onchange="applyFilter()"
                >
                Semua
            </label>

            <?php
            $q_kec = $conn->query("SELECT nama FROM kecamatan ORDER BY nama ASC");

            if ($q_kec) {
                while ($k = $q_kec->fetch_assoc()) {
            ?>
                <label class="filter-item">
                    <input
                        type="radio"
                        name="kec"
                        value="<?= htmlspecialchars($k['nama']) ?>"
                        onchange="applyFilter()"
                    >
                    <?= htmlspecialchars($k['nama']) ?>
                </label>
            <?php
                }
            }
            ?>

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
                    name="thn"
                    value="semua"
                    checked
                    onchange="applyFilter()"
                >
                Semua
            </label>

            <label class="filter-item">
                <input
                    type="radio"
                    name="thn"
                    value="2022"
                    onchange="applyFilter()"
                >
                2022
            </label>

            <label class="filter-item">
                <input
                    type="radio"
                    name="thn"
                    value="2023"
                    onchange="applyFilter()"
                >
                2023
            </label>

            <label class="filter-item">
                <input
                    type="radio"
                    name="thn"
                    value="2024"
                    onchange="applyFilter()"
                >
                2024
            </label>

        </div>
    </div>
    </div>

</div>


<!-- ================= KONTEN UTAMA ================= -->

<div class="main-content">

    <!-- ================= DASHBOARD ================= -->

    <div id="section-dashboard">

        <div class="top-cards">

            <div class="card-top">
                <h3>Total Bencana Yang Terjadi</h3>
                <div class="val" id="valTotalBencana">0</div>
            </div>

            <div class="card-top">
                <h3>Total Kecamatan</h3>
                <div class="val" id="valTotalKecamatan">0</div>
            </div>

            <div class="card-top">
                <h3>Dampak Tertinggi</h3>
                <div class="val" id="valDampakTertinggi">-</div>
            </div>

        </div>

        <div id="map"></div>

        <div class="bencana-grid">

            <div class="card-bencana">
                <div class="title">Banjir</div>
                <div class="count" id="cnt-banjir">0</div>
            </div>

            <div class="card-bencana">
                <div class="title">Banjir Bandang</div>
                <div class="count" id="cnt-banjir_bandang">0</div>
            </div>

            <div class="card-bencana">
                <div class="title">Gelombang Pasang</div>
                <div class="count" id="cnt-gelombang_pasang">0</div>
            </div>

            <div class="card-bencana">
                <div class="title">Abrasi</div>
                <div class="count" id="cnt-abrasi">0</div>
            </div>

            <div class="card-bencana">
                <div class="title">Tanah Longsor</div>
                <div class="count" id="cnt-tanah_longsor">0</div>
            </div>

            <div class="card-bencana">
                <div class="title">Angin Puting Beliung</div>
                <div class="count" id="cnt-angin_puting_beliung">0</div>
            </div>

            <div class="card-bencana">
                <div class="title">Gunung Meletus</div>
                <div class="count" id="cnt-gunung_meletus">0</div>
            </div>

            <div class="card-bencana">
                <div class="title">Karhutla</div>
                <div class="count" id="cnt-karhutla">0</div>
            </div>

            <div class="card-bencana">
                <div class="title">Kekeringan</div>
                <div class="count" id="cnt-kekeringan">0</div>
            </div>

            <div class="card-bencana">
                <div class="title">Gempa Bumi</div>
                <div class="count" id="cnt-gempa_bumi">0</div>
            </div>

            <div class="card-bencana">
                <div class="title">Tsunami</div>
                <div class="count" id="cnt-tsunami">0</div>
            </div>

            <div class="card-bencana highlight">
                <div class="title">Jumlah</div>
                <div class="count" id="cnt-jumlah">0</div>
            </div>

        </div>

    </div>


    <!-- ================= STATISTIK ================= -->

    <div id="section-statistik">

        <div class="stat-header">
            GAMBARAN UMUM STATISTIK BENCANA (2022-2024)
        </div>

        <div class="stat-grid">

            <div class="stat-card full-width">

                <div class="stat-card-title">
                    SEBARAN BENCANA DI KABUPATEN JEMBER
                </div>

                <div id="map-stat"></div>

            </div>


            <div style="display:flex;flex-direction:column;">

                <div class="kpi-box">

                    <div
                        class="stat-card-title"
                        style="margin:0;"
                    >
                        TOTAL KEJADIAN:
                    </div>

                    <div class="num">
                        <span id="statTotalVal">0</span>
                        <span class="badge">↑</span>
                    </div>

                </div>

                <div class="trend-box">

                    <div class="stat-card-title">
                        TREN KEJADIAN BENCANA TAHUNAN
                    </div>

                    <div style="height:220px;">
                        <canvas id="chartTren"></canvas>
                    </div>

                </div>

            </div>


            <div class="stat-card">

                <div class="stat-card-title">
                    DISTRIBUSI JENIS BENCANA
                </div>

                <div
                    style="
                        height:250px;
                        display:flex;
                        justify-content:center;
                    "
                >
                    <canvas id="chartDistribusi"></canvas>
                </div>

            </div>


            <div class="stat-card full-width">

                <div class="stat-card-title">
                    RINGKASAN KEJADIAN TERPERINCI
                </div>

                <div class="stat-table-wrapper">

                    <table class="stat-table">

                        <thead>
                            <tr>
                                <th>Tahun</th>
                                <th>Jenis Bencana</th>
                                <th>Lokasi (Kecamatan)</th>
                                <th>Jumlah Kejadian</th>
                            </tr>
                        </thead>

                        <tbody id="statTableBody">

                            <tr>
                                <td
                                    colspan="4"
                                    style="text-align:center;"
                                >
                                    Memuat data...
                                </td>
                            </tr>

                        </tbody>

                    </table>

                </div>

            </div>

        </div>

    </div>

</div>


<script>

/* =========================================================
   MAP UTAMA
========================================================= */

const map = L.map('map').setView(
    [-8.2243, 113.7008],
    10
);

L.tileLayer(
    'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
    {
        attribution: '&copy; OpenStreetMap'
    }
).addTo(map);

let markersLayer = L.layerGroup().addTo(map);


/* =========================================================
   MAP STATISTIK
========================================================= */

const mapStat = L.map('map-stat', {
    zoomControl: false
}).setView(
    [-8.2243, 113.7008],
    10
);

L.tileLayer(
    'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
    {
        attribution: '&copy; OpenStreetMap'
    }
).addTo(mapStat);

let markersStatLayer = L.layerGroup().addTo(mapStat);

/* =========================================================
   INFO HOVER KECAMATAN
========================================================= */

const mapInfo = L.control({ position: 'topright' });

mapInfo.onAdd = function () {
    this._div = L.DomUtil.create('div', 'map-info-control');
    this.showDefault();
    return this._div;
};

mapInfo.showDefault = function () {
    this._div.innerHTML = `
        <div class="info-title">Informasi Kecamatan</div>
        <div style="font-size:12px;color:#9ea6c7;">
            Arahkan kursor ke area kecamatan pada peta.
        </div>
    `;
};

mapInfo.update = function (nama, data) {
    const total = Number(data?.total || 0);
    const jenis = [
        ['Banjir', data?.banjir],
        ['Banjir Bandang', data?.banjir_bandang],
        ['Gelombang Pasang', data?.gelombang_pasang],
        ['Abrasi', data?.abrasi],
        ['Tanah Longsor', data?.tanah_longsor],
        ['Puting Beliung', data?.angin_puting_beliung],
        ['Gunung Meletus', data?.gunung_meletus],
        ['Karhutla', data?.karhutla],
        ['Kekeringan', data?.kekeringan],
        ['Gempa Bumi', data?.gempa_bumi],
        ['Tsunami', data?.tsunami]
    ];
    const rows = jenis.filter(item => Number(item[1] || 0) > 0).map(item => `
        <div class="info-row">
            <span>${escapeHtml(item[0])}</span>
            <span>${Number(item[1] || 0)}</span>
        </div>
    `).join('');
    this._div.innerHTML = `
        <div class="info-title">${escapeHtml(nama)}</div>
        <div class="info-total">Total Bencana: ${total}</div>
        ${rows || '<div style="font-size:12px;color:#9ea6c7;">Belum ada kejadian bencana.</div>'}
    `;
};

mapInfo.addTo(map);



/* =========================================================
   VARIABLE GLOBAL
========================================================= */

let chartTren = null;
let chartDistribusi = null;
let globalKecamatanData = [];
let geojsonLayer = null;


/* =========================================================
   WARNA MAP
========================================================= */

function getColor(total) {

    total = Number(total) || 0;

    if (total > 30) return '#800026';
    if (total > 20) return '#BD0026';
    if (total > 10) return '#E31A1C';
    if (total > 5)  return '#FC4E2A';
    if (total > 0)  return '#FD8D3C';

    return '#2ecc71';
}


/* =========================================================
   AUTO ZOOM KECAMATAN
========================================================= */

const JEMBER_CENTER = [-8.2243, 113.7008];
const JEMBER_ZOOM = 10;
const KECAMATAN_ZOOM = 14;

function zoomToSelectedKecamatan(kec) {

    if (!kec || String(kec).toLowerCase() === 'semua') {

        map.flyTo(
            JEMBER_CENTER,
            JEMBER_ZOOM,
            { duration: 1.0 }
        );

        mapStat.flyTo(
            JEMBER_CENTER,
            JEMBER_ZOOM,
            { duration: 1.0 }
        );

        return;
    }

    const lokasi = globalKecamatanData.find(item => {

        return String(item.nama || '')
            .trim()
            .toLowerCase() ===
            String(kec)
                .trim()
                .toLowerCase();

    });

    if (!lokasi) {
        console.warn(
            'Lokasi kecamatan tidak ditemukan:',
            kec
        );
        return;
    }

    const lat = Number(lokasi.lat);
    const lng = Number(lokasi.lng);

    if (
        !Number.isFinite(lat) ||
        !Number.isFinite(lng)
    ) {
        console.warn(
            'Koordinat kecamatan tidak valid:',
            lokasi
        );
        return;
    }

    map.flyTo(
        [lat, lng],
        KECAMATAN_ZOOM,
        { duration: 1.2 }
    );

    mapStat.flyTo(
        [lat, lng],
        KECAMATAN_ZOOM,
        { duration: 1.2 }
    );

}


/* =========================================================
   FILTER DATA
========================================================= */


function toggleFilterBlock(title) {
    const block = title.closest('.filter-block');

    if (block) {
        block.classList.toggle('collapsed');
    }
}

function applyFilter() {

    const kecElement =
        document.querySelector('input[name="kec"]:checked');

    const tahunElement =
        document.querySelector('input[name="thn"]:checked');

    const kec =
        kecElement ? kecElement.value : 'semua';

    const thn =
        tahunElement ? tahunElement.value : 'semua';

    currentKecamatanFilter = kec;


    const url =
        `api_dashboard.php?kecamatan=${encodeURIComponent(kec)}&tahun=${encodeURIComponent(thn)}`;


    fetch(url)

        .then(response => {

            if (!response.ok) {
                throw new Error(
                    `HTTP Error ${response.status}`
                );
            }

            return response.json();
        })

        .then(data => {

            console.log('Data API:', data);


            /* =========================
               VALIDASI DATA API
            ========================= */

            if (!data.summary) {
                throw new Error(
                    'Data summary tidak ditemukan dari API.'
                );
            }


            const summary =
                data.summary || {};

            const jenis =
                data.jenis_bencana || {};


            /* =========================
               CARD ATAS
            ========================= */

            document.getElementById(
                'valTotalBencana'
            ).innerText =
                Number(summary.total_bencana || 0);


            document.getElementById(
                'valTotalKecamatan'
            ).innerText =
                Number(summary.total_kecamatan || 0);


            document.getElementById(
                'valDampakTertinggi'
            ).innerText =
                summary.dampak_tertinggi || '-';


            /* =========================
               CARD JENIS BENCANA
            ========================= */

            const jenisKolom = [
                'banjir',
                'banjir_bandang',
                'gelombang_pasang',
                'abrasi',
                'tanah_longsor',
                'angin_puting_beliung',
                'gunung_meletus',
                'karhutla',
                'kekeringan',
                'gempa_bumi',
                'tsunami'
            ];


            jenisKolom.forEach(key => {

                const element =
                    document.getElementById(`cnt-${key}`);

                if (element) {
                    element.innerText =
                        Number(jenis[key] || 0);
                }

            });


            /* =========================
               TOTAL JUMLAH
            ========================= */

            const totalJumlah =
                jenisKolom.reduce(
                    (total, key) =>
                        total + Number(jenis[key] || 0),
                    0
                );


            document.getElementById(
                'cnt-jumlah'
            ).innerText = totalJumlah;


            document.getElementById(
                'statTotalVal'
            ).innerText =
                Number(summary.total_bencana || totalJumlah);


            /* =========================
               DATA KECAMATAN
            ========================= */

            markersLayer.clearLayers();
            markersStatLayer.clearLayers();

            globalKecamatanData =
                Array.isArray(data.kecamatan_data)
                    ? data.kecamatan_data
                    : [];


            globalKecamatanData.forEach(item => {

                const lat =
                    Number(item.lat);

                const lng =
                    Number(item.lng);

                const total =
                    Number(item.total || 0);

                const isSemua =
                    String(currentKecamatanFilter).trim().toLowerCase() === 'semua';

                const isAllowed =
                    isSemua ||
                    String(item.nama || '').trim().toLowerCase() ===
                    String(currentKecamatanFilter).trim().toLowerCase();

                // Jika filter kecamatan tertentu dipilih, marker
                // kecamatan lain tidak dibuat sama sekali.
                if (!isAllowed) return;


                if (
                    !isNaN(lat) &&
                    !isNaN(lng) &&
                    total > 0
                ) {

                    const markerOpt = {

                        radius:
                            Math.max(
                                5,
                                Math.sqrt(total) * 2
                            ),

                        fillColor:
                            getColor(total),

                        color: '#fff',

                        weight: 1,

                        fillOpacity: 0.85

                    };


                    const popupText =
                        `<b>${escapeHtml(item.nama || 'Kecamatan')}</b>
                        <br>Total Bencana: ${total}`;


                    L.circleMarker(
                        [lat, lng],
                        markerOpt
                    )
                    .bindPopup(popupText)
                    .addTo(markersLayer);


                    L.circleMarker(
                        [lat, lng],
                        markerOpt
                    )
                    .bindPopup(popupText)
                    .addTo(markersStatLayer);

                }

            });


            /* =========================
               AUTO ZOOM KECAMATAN
            ========================= */

            zoomToSelectedKecamatan(kec);


            /* =========================
               GEOJSON
            ========================= */

            if (geojsonLayer) {
                geojsonLayer.setStyle(styleGeojson);
                updateGeojsonInteraction();
            }


            /* =========================
               CHART TREN
            ========================= */

            const tren =
                Array.isArray(data.tren_tahunan)
                    ? data.tren_tahunan
                    : [];


            const lblThn =
                tren.map(d => d.tahun);


            if (chartTren) {
                chartTren.destroy();
            }


            chartTren = new Chart(
                document.getElementById('chartTren'),
                {

                    type: 'bar',

                    data: {

                        labels: lblThn,

                        datasets: [

                            {
                                label: 'Banjir',

                                data:
                                    tren.map(
                                        d => Number(d.banjir || 0)
                                    ),

                                backgroundColor: '#3498db'
                            },

                            {
                                label: 'Tanah Longsor',

                                data:
                                    tren.map(
                                        d => Number(d.tanah_longsor || 0)
                                    ),

                                backgroundColor: '#e67e22'
                            },

                            {
                                label: 'Puting Beliung',

                                data:
                                    tren.map(
                                        d => Number(d.puting_beliung || 0)
                                    ),

                                backgroundColor: '#e74c3c'
                            },

                            {
                                label: 'Lainnya',

                                data:
                                    tren.map(
                                        d => Number(d.lainnya || 0)
                                    ),

                                backgroundColor: '#95a5a6'
                            }

                        ]

                    },

                    options: {

                        responsive: true,

                        maintainAspectRatio: false,

                        scales: {

                            x: {
                                stacked: true
                            },

                            y: {
                                stacked: true,
                                beginAtZero: true
                            }

                        },

                        plugins: {

                            legend: {
                                position: 'bottom',

                                labels: {
                                    boxWidth: 12
                                }
                            }

                        }

                    }

                }
            );


            /* =========================
               CHART DISTRIBUSI
            ========================= */

            if (chartDistribusi) {
                chartDistribusi.destroy();
            }


            const jumlahBanjir =
                Number(jenis.banjir || 0);

            const jumlahLongsor =
                Number(jenis.tanah_longsor || 0);

            const jumlahPuting =
                Number(jenis.angin_puting_beliung || 0);


            const jumlahLainnya =
                Number(jenis.lainnya || 0);


            chartDistribusi = new Chart(
                document.getElementById('chartDistribusi'),
                {

                    type: 'doughnut',

                    data: {

                        labels: [
                            'Banjir',
                            'Tanah Longsor',
                            'Puting Beliung',
                            'Lainnya'
                        ],

                        datasets: [

                            {

                                data: [
                                    jumlahBanjir,
                                    jumlahLongsor,
                                    jumlahPuting,
                                    jumlahLainnya
                                ],

                                backgroundColor: [
                                    '#3498db',
                                    '#e67e22',
                                    '#e74c3c',
                                    '#95a5a6'
                                ],

                                borderWidth: 0

                            }

                        ]

                    },

                    options: {

                        responsive: true,

                        maintainAspectRatio: false,

                        plugins: {

                            legend: {
                                position: 'right',

                                labels: {
                                    boxWidth: 12
                                }
                            }

                        },

                        cutout: '65%'

                    }

                }
            );


            /* =========================
               TABEL DETAIL
            ========================= */

            let tbody = '';

            const tabelDetail =
                Array.isArray(data.tabel_detail)
                    ? data.tabel_detail
                    : [];


            if (tabelDetail.length > 0) {

                tabelDetail.forEach(row => {

                    tbody += `
                        <tr>

                            <td>
                                ${escapeHtml(row.tahun ?? '-')}
                            </td>

                            <td>
                                <span
                                    style="
                                        background:#e8f8f5;
                                        color:#2c3e50;
                                        padding:4px 10px;
                                        border-radius:12px;
                                        font-weight:bold;
                                    "
                                >
                                    ${escapeHtml(row.jenis ?? '-')}
                                </span>
                            </td>

                            <td>
                                <b>
                                    ${escapeHtml(row.lokasi ?? '-')}
                                </b>
                            </td>

                            <td>
                                ${Number(row.jumlah || 0)}
                            </td>

                        </tr>
                    `;

                });

            } else {

                tbody = `
                    <tr>
                        <td
                            colspan="4"
                            style="text-align:center;"
                        >
                            Tidak ada data bencana untuk kombinasi filter ini.
                        </td>
                    </tr>
                `;

            }


            document.getElementById(
                'statTableBody'
            ).innerHTML = tbody;

        })

        .catch(error => {

            console.error(
                'ERROR API DASHBOARD:',
                error
            );


            document.getElementById(
                'statTableBody'
            ).innerHTML = `
                <tr>
                    <td
                        colspan="4"
                        style="
                            text-align:center;
                            color:#e74c3c;
                        "
                    >
                        Gagal mengambil data dashboard.
                        <br>
                        <small>${escapeHtml(error.message)}</small>
                    </td>
                </tr>
            `;

        });

}


/* =========================================================
   STYLE GEOJSON
========================================================= */

function styleGeojson(feature) {
    const kecName = feature.properties.nama || feature.properties.nm_kecamatan || '';
    const isSemua = String(currentKecamatanFilter).trim().toLowerCase() === 'semua';
    const isFilteredKecamatan = String(kecName).trim().toLowerCase() === String(currentKecamatanFilter).trim().toLowerCase();

    // Jika memilih kecamatan tertentu, hanya polygon kecamatan tersebut
    // yang boleh terlihat dan menerima interaksi/highlight.
    if (!isSemua && !isFilteredKecamatan) {
        return {
            fillColor: 'transparent',
            fillOpacity: 0,
            weight: 0,
            opacity: 0,
            color: 'transparent',
            interactive: false
        };
    }

    const found = globalKecamatanData.find(k => String(k.nama || '').trim().toLowerCase() === String(kecName).trim().toLowerCase());
    const total = found ? Number(found.total || 0) : 0;

    return {
        fillColor: getColor(total),
        weight: 1,
        opacity: 1,
        color: '#ffffff',
        fillOpacity: 0.55,
        interactive: true
    };
}


/* =========================================================
   BATASI INTERAKSI GEOJSON SESUAI FILTER
========================================================= */

function updateGeojsonInteraction() {

    if (!geojsonLayer) return;

    const isSemua =
        String(currentKecamatanFilter).trim().toLowerCase() === 'semua';

    geojsonLayer.eachLayer(layer => {

        const nama =
            layer.feature?.properties?.nama ||
            layer.feature?.properties?.nm_kecamatan ||
            '';

        const isAllowed =
            isSemua ||
            String(nama).trim().toLowerCase() ===
            String(currentKecamatanFilter).trim().toLowerCase();

        // interactive harus diubah langsung karena setStyle()
        // tidak mengubah target interaksi Leaflet yang sudah dibuat.
        layer.options.interactive = isAllowed;

        const element =
            typeof layer.getElement === 'function'
                ? layer.getElement()
                : null;

        if (element) {
            element.style.pointerEvents =
                isAllowed ? 'auto' : 'none';
        }

        if (!isAllowed) {
            if (typeof layer.closeTooltip === 'function') {
                layer.closeTooltip();
            }
            if (typeof layer.closePopup === 'function') {
                layer.closePopup();
            }
        }
    });
}


/* =========================================================
   LOAD GEOJSON
========================================================= */

fetch('jember_kecamatan.geojson')
    .then(response => {
        if (!response.ok) throw new Error('File jember_kecamatan.geojson tidak ditemukan.');
        return response.json();
    })
    .then(geojson => {
        geojsonLayer = L.geoJSON(geojson, {
            style: styleGeojson,
            onEachFeature: (feature, layer) => {
                const nama = feature.properties.nama || feature.properties.nm_kecamatan || 'Kecamatan';

                const getDataKecamatan = () => globalKecamatanData.find(item =>
                    String(item.nama || '').trim().toLowerCase() === String(nama).trim().toLowerCase()
                ) || { nama, total: 0 };

                layer.bindTooltip(`<b>${escapeHtml(nama)}</b>`, { sticky:true, direction:'top', opacity:0.95 });

                layer.bindPopup(`<b>Kecamatan ${escapeHtml(nama)}</b><br>
                    Total Bencana: <span class="geojson-popup-total">0</span>`);

                layer.on({
                    mouseover: function () {
                        const isSemua = String(currentKecamatanFilter).trim().toLowerCase() === 'semua';
                        const isAllowed = isSemua || String(nama).trim().toLowerCase() === String(currentKecamatanFilter).trim().toLowerCase();
                        if (!isAllowed) return;
                        layer.setStyle({ weight: 3, color: '#00e5ff', opacity: 1, fillOpacity: 0.78, interactive: true });
                        if (layer.bringToFront) layer.bringToFront();
                        mapInfo.update(nama, getDataKecamatan());
                    },
                    mouseout: function () {
                        const isSemua = String(currentKecamatanFilter).trim().toLowerCase() === 'semua';
                        const isAllowed = isSemua || String(nama).trim().toLowerCase() === String(currentKecamatanFilter).trim().toLowerCase();
                        if (!isAllowed) return;
                        geojsonLayer.resetStyle(layer);
                        mapInfo.showDefault();
                    },
                    click: function () {
                        const isSemua = String(currentKecamatanFilter).trim().toLowerCase() === 'semua';
                        const isAllowed = isSemua || String(nama).trim().toLowerCase() === String(currentKecamatanFilter).trim().toLowerCase();
                        if (!isAllowed) return;
                        const dataKecamatan = getDataKecamatan();
                        layer.setStyle({ weight:3, color:'#00e5ff', opacity:1, fillOpacity:0.78 });
                        layer.bindPopup(`<b>Kecamatan ${escapeHtml(nama)}</b><br>
                            Total Bencana: ${Number(dataKecamatan.total || 0)}`).openPopup();
                    }
                });
            }
        }).addTo(map);

        updateGeojsonInteraction();
    })
    .catch(error => console.error('Gagal memuat GeoJSON:', error));


/* =========================================================
   SEARCH KECAMATAN
========================================================= */

function filterKecamatanSearch() {

    const input =
        document
            .getElementById('searchInput')
            .value
            .toLowerCase();


    document
        .querySelectorAll(
            '#kecamatanFilterList .filter-item'
        )
        .forEach(item => {

            item.style.display =
                item.innerText
                    .toLowerCase()
                    .includes(input)
                    ? 'flex'
                    : 'none';

        });

}


/* =========================================================
   ESCAPE HTML
========================================================= */

function escapeHtml(value) {

    const div =
        document.createElement('div');

    div.textContent =
        value ?? '';

    return div.innerHTML;

}


/* =========================================================
   PERBAIKI UKURAN MAP
========================================================= */

setTimeout(() => {

    map.invalidateSize();
    mapStat.invalidateSize();

}, 300);


/* =========================================================
   JALANKAN DASHBOARD
========================================================= */

applyFilter();

</script>

</body>
</html>
```

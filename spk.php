<?php
require 'koneksi.php';

// Cek koneksi database
if ($conn->connect_error) {
    die("Koneksi database gagal: " . $conn->connect_error);
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Sistem Pendukung Keputusan - Jember</title>

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            display: flex;
            background: #070926;
            color: #fff;
            min-height: 100vh;
        }

        /* =========================
           SIDEBAR
        ========================= */

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

        /* =========================
           NAVIGATION
        ========================= */

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
            border-radius: 8px;
            transition: 0.2s;
            display: block;
        }

        .nav-link:hover,
        .nav-link.active {
            color: #ffffff;
            background: #131742;
            border-left: 3px solid #3498db;
        }

        /* =========================
           KONTEN UTAMA
        ========================= */

        .main-content {
            flex: 1;
            padding: 30px;
            background: #f4f6f9;
            color: #333;
            overflow-y: auto;
            min-width: 0;
        }

        .header-title {
            font-size: 22px;
            font-weight: 800;
            color: #111;
            margin-bottom: 20px;
        }

        /* =========================
           INFO BOX
        ========================= */

        .info-box {
            background: #e8f8f5;
            border-left: 4px solid #2ecc71;
            padding: 12px 18px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 13px;
            color: #27ae60;
            font-weight: 600;
        }

        /* =========================
           CARD
        ========================= */

        .card-container {
            background: #fff;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            border: 1px solid #eaeaea;
        }

        .card-title {
            font-weight: 700;
            font-size: 15px;
            margin-bottom: 15px;
            color: #222;
        }

        /* =========================
           TABLE
        ========================= */

        .table-wrapper {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
            text-align: left;
        }

        th {
            background: #131742;
            color: #fff;
            padding: 14px 16px;
            font-weight: 700;
        }

        td {
            padding: 12px 16px;
            border-bottom: 1px solid #eee;
            color: #444;
        }

        tr:nth-child(even) {
            background: #f9f9f9;
        }

        tr:hover {
            background: #f1f4f8;
        }

        /* =========================
           BADGE PERINGKAT
        ========================= */

        .badge-peringkat {
            padding: 4px 10px;
            border-radius: 12px;
            font-weight: bold;
            font-size: 11px;
            display: inline-block;
        }

        .peringkat-1 {
            background: #fadbd8;
            color: #c0392b;
        }

        .peringkat-2 {
            background: #fdebd0;
            color: #d35400;
        }

        .peringkat-3 {
            background: #fcf3cf;
            color: #b7950b;
        }

        .peringkat-lain {
            background: #e8f8f5;
            color: #16a085;
        }

        /* =========================
           RESPONSIVE
        ========================= */

        @media (max-width: 768px) {

            body {
                flex-direction: column;
            }

            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
            }

            .main-content {
                padding: 20px;
            }

            .header-title {
                font-size: 19px;
            }
        }
    </style>
</head>

<body>

    <!-- ==================================
         SIDEBAR
    ================================== -->

    <div class="sidebar">

        <div class="user-profile">
            <div class="user-avatar"></div>

            <span style="font-weight: 700; font-size: 14px;">
                polije
            </span>
        </div>

        <div class="search-box">
            <input type="text" placeholder="Search...">
        </div>

        <!-- NAVIGASI HANYA DI SINI -->
        <div class="nav-group">

            <a href="index.php" class="nav-link">
                Dashboard
            </a>

            <a href="index.php#section-statistik" class="nav-link">
                Statistik
            </a>

            <a href="manajemen.php" class="nav-link">
                Manajemen
            </a>

            <a href="spk.php" class="nav-link active">
                Pendukung Keputusan
            </a>

        </div>

    </div>


    <!-- ==================================
         KONTEN UTAMA
    ================================== -->

    <div class="main-content">

        <div class="header-title">
            Sistem Pendukung Keputusan (SPK) - Analisis Tingkat Kerawanan
        </div>


        <!-- INFORMASI -->

        <div class="info-box">
            💡 Halaman ini menyajikan pemeringkatan wilayah kecamatan di
            Kabupaten Jember berdasarkan akumulasi total kejadian bencana
            untuk menentukan prioritas penanganan mitigasi.
        </div>


        <!-- ==================================
             CARD PERANKINGAN
        ================================== -->

        <div class="card-container">

            <div class="card-title">
                Hasil Perankingan Wilayah Rawan Bencana
            </div>

            <div class="table-wrapper">

                <table>

                    <thead>

                        <tr>
                            <th>Peringkat</th>
                            <th>Kecamatan</th>
                            <th>Total Kejadian Bencana</th>
                            <th>Status Kerawanan</th>
                        </tr>

                    </thead>

                    <tbody>

                        <?php

                        /*
                         * ==========================================
                         * QUERY SPK
                         * ==========================================
                         *
                         * Menghitung seluruh kejadian bencana
                         * dari semua tahun yang tersedia.
                         */

                        $sql = "
                            SELECT
                                k.nama AS kecamatan,

                                COALESCE(
                                    SUM(
                                        COALESCE(b.banjir, 0) +
                                        COALESCE(b.banjir_bandang, 0) +
                                        COALESCE(b.gelombang_pasang, 0) +
                                        COALESCE(b.abrasi, 0) +
                                        COALESCE(b.tanah_longsor, 0) +
                                        COALESCE(b.angin_puting_beliung, 0) +
                                        COALESCE(b.gunung_meletus, 0) +
                                        COALESCE(b.karhutla, 0) +
                                        COALESCE(b.kekeringan, 0) +
                                        COALESCE(b.gempa_bumi, 0) +
                                        COALESCE(b.tsunami, 0)
                                    ),
                                    0
                                ) AS total

                            FROM kecamatan k

                            LEFT JOIN kejadian_bencana b
                                ON k.id = b.kecamatan_id

                            GROUP BY
                                k.id,
                                k.nama

                            ORDER BY
                                total DESC,
                                k.nama ASC
                        ";

                        $result = $conn->query($sql);


                        if ($result && $result->num_rows > 0) {

                            $rank = 1;

                            while ($row = $result->fetch_assoc()) {

                                $total = (int) $row['total'];


                                /*
                                 * ==================================
                                 * STATUS KERAWANAN
                                 * ==================================
                                 */

                                if ($total > 30) {

                                    $status = "
                                        <span style='color:#c0392b;font-weight:bold;'>
                                            Sangat Rawan (Prioritas 1)
                                        </span>
                                    ";

                                } elseif ($total > 10) {

                                    $status = "
                                        <span style='color:#d35400;font-weight:bold;'>
                                            Rawan (Prioritas 2)
                                        </span>
                                    ";

                                } elseif ($total > 0) {

                                    $status = "
                                        <span style='color:#f39c12;font-weight:bold;'>
                                            Cukup Rawan
                                        </span>
                                    ";

                                } else {

                                    $status = "
                                        <span style='color:#27ae60;font-weight:bold;'>
                                            Aman / Minim Bencana
                                        </span>
                                    ";
                                }


                                /*
                                 * ==================================
                                 * WARNA BADGE PERINGKAT
                                 * ==================================
                                 */

                                if ($rank == 1) {

                                    $badge_class = "peringkat-1";

                                } elseif ($rank == 2) {

                                    $badge_class = "peringkat-2";

                                } elseif ($rank == 3) {

                                    $badge_class = "peringkat-3";

                                } else {

                                    $badge_class = "peringkat-lain";
                                }


                                /*
                                 * ==================================
                                 * OUTPUT
                                 * ==================================
                                 */

                                $nama_kecamatan = htmlspecialchars(
                                    $row['kecamatan'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                );

                                echo "
                                    <tr>

                                        <td>
                                            <span class='badge-peringkat {$badge_class}'>
                                                Peringkat #{$rank}
                                            </span>
                                        </td>

                                        <td>
                                            <b>{$nama_kecamatan}</b>
                                        </td>

                                        <td>
                                            <b>{$total}</b> Kejadian
                                        </td>

                                        <td>
                                            {$status}
                                        </td>

                                    </tr>
                                ";

                                $rank++;
                            }

                        } else {

                            echo "
                                <tr>
                                    <td colspan='4' style='text-align:center;'>
                                        Belum ada data untuk dianalisis.
                                    </td>
                                </tr>
                            ";
                        }

                        ?>

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</body>

</html>

<?php
$conn->close();
?>
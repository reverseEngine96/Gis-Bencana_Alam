<?php
require 'koneksi.php';

// Ambil total data kejadian bencana
$total_data = $conn->query("SELECT COUNT(*) as total FROM kejadian_bencana")->fetch_assoc()['total'];

// Ambil total jenis bencana dari beberapa kolom utama
$tot_banjir = $conn->query("SELECT SUM(banjir) as jml FROM kejadian_bencana")->fetch_assoc()['jml'] ?? 0;
$tot_longsor = $conn->query("SELECT SUM(tanah_longsor) as jml FROM kejadian_bencana")->fetch_assoc()['jml'] ?? 0;
$tot_kekeringan = $conn->query("SELECT SUM(kekeringan) as jml FROM kejadian_bencana")->fetch_assoc()['jml'] ?? 0;
$tot_angin = $conn->query("SELECT SUM(angin_puting_beliung) as jml FROM kejadian_bencana")->fetch_assoc()['jml'] ?? 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Statistik Bencana - Jember</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: sans-serif; }
        body { display: flex; background: #f4f6f9; color: #333; min-height: 100vh; }
        
        /* Sidebar */
        .sidebar { width: 260px; background: #070926; padding: 20px; display: flex; flex-direction: column; gap: 15px; height: 100vh; position: sticky; top: 0; color: #fff; }
        .sidebar .profile { display: flex; align-items: center; gap: 10px; margin-bottom: 20px; }
        .sidebar .avatar { width: 40px; height: 40px; background: #fff; border-radius: 50%; }
        .nav-link { text-decoration: none; font-size: 14px; font-weight: bold; color: #8a99ad; padding: 10px 12px; border-radius: 6px; display: block; }
        .nav-link.active, .nav-link:hover { color: #fff; background: #131742; }

        /* Main Content */
        .main-content { flex: 1; padding: 30px; overflow-y: auto; }
        h2 { margin-bottom: 20px; color: #111; }
        
        .grid-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: #fff; border-radius: 8px; padding: 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); border-left: 5px solid #007bff; }
        .stat-card h3 { font-size: 14px; color: #666; margin-bottom: 8px; }
        .stat-card .number { font-size: 24px; font-weight: bold; color: #111; }
    </style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
    <div class="profile">
        <div class="avatar"></div>
        <span>Mr. Skibidi</span>
    </div>
    <a href="index.php" class="nav-link">Dashboard</a>
    <a href="statistik.php" class="nav-link active">Statistik</a>
    <a href="manajemen.php" class="nav-link">Manajemen</a>
    <a href="spk.php" class="nav-link">Pendukung Keputusan</a>
</div>

<!-- Main Area -->
<div class="main-content">
    <h2>Statistik Rekapitulasi Bencana</h2>

    <div class="grid-stats">
        <div class="stat-card" style="border-color: #007bff;">
            <h3>Total Baris Data</h3>
            <div class="number"><?= $total_data ?></div>
        </div>
        <div class="stat-card" style="border-color: #dc3545;">
            <h3>Total Kejadian Banjir</h3>
            <div class="number"><?= $tot_banjir ?></div>
        </div>
        <div class="stat-card" style="border-color: #ffc107;">
            <h3>Total Tanah Longsor</h3>
            <div class="number"><?= $tot_longsor ?></div>
        </div>
        <div class="stat-card" style="border-color: #28a745;">
            <h3>Total Kekeringan</h3>
            <div class="number"><?= $tot_kekeringan ?></div>
        </div>
    </div>
</div>

</body>
</html>
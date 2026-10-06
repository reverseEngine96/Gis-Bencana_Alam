<?php
require 'koneksi.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Test Bersih</title>
<style>
    body { background: #fff; color: #333; font-family: sans-serif; padding: 40px; }
    h1 { color: #28a745; }
</style>
</head>
<body>
    <h1>BERHASIL! Server PHP dan Database Terhubung Tanpa Teks Mentah.</h1>
    <p>Jumlah data di tabel kejadian_bencana: 
        <b>
        <?php 
            $res = $conn->query("SELECT COUNT(*) as total FROM kejadian_bencana");
            if ($res) {
                $row = $res->fetch_assoc();
                echo $row['total'];
            } else {
                echo "0 (Tabel belum ada / salah nama)";
            }
        ?>
        </b>
    </p>
</body>
</html>
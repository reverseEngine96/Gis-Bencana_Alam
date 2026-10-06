<?php
error_reporting(0);
header('Content-Type: application/json; charset=utf-8');
require 'koneksi.php';

if ($conn->connect_error) {
    echo json_encode(["error" => "Koneksi database gagal"]); exit;
}

$tahun     = isset($_GET['tahun']) && $_GET['tahun'] !== '' ? $_GET['tahun'] : 'semua';
$kecamatan = isset($_GET['kecamatan']) && $_GET['kecamatan'] !== '' ? $_GET['kecamatan'] : 'semua';

$where = [];
if ($tahun !== 'semua') $where[] = "b.tahun = " . (int)$tahun;
if ($kecamatan !== 'semua') $where[] = "k.nama = '" . $conn->real_escape_string($kecamatan) . "'";
$whereClause = count($where) > 0 ? "WHERE " . implode(" AND ", $where) : "";

// 1. Data Kecamatan Utama
$sql = "SELECT k.nama, k.latitude, k.longitude,
               COALESCE(SUM(b.banjir), 0) AS banjir, COALESCE(SUM(b.tanah_longsor), 0) AS tanah_longsor,
               COALESCE(SUM(b.angin_puting_beliung), 0) AS puting_beliung,
               COALESCE(SUM(b.banjir_bandang + b.gelombang_pasang + b.abrasi + b.gunung_meletus + b.karhutla + b.kekeringan + b.gempa_bumi + b.tsunami), 0) AS lainnya,
               COALESCE(SUM(b.banjir + b.banjir_bandang + b.gelombang_pasang + b.abrasi + b.tanah_longsor + b.angin_puting_beliung + b.gunung_meletus + b.karhutla + b.kekeringan + b.gempa_bumi + b.tsunami), 0) AS total
        FROM kecamatan k LEFT JOIN kejadian_bencana b ON k.id = b.kecamatan_id $whereClause
        GROUP BY k.id, k.nama, k.latitude, k.longitude ORDER BY total DESC";

$result = $conn->query($sql);
$dataKecamatan = [];
$totBanjir = $totLongsor = $totPuting = $totLainnya = $grandTotal = 0;

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $dataKecamatan[] = [
            'nama' => $row['nama'], 'lat' => (float)$row['latitude'], 'lng' => (float)$row['longitude'],
            'total' => (int)$row['total'],
            'detail' => ['banjir' => (int)$row['banjir'], 'tanah_longsor' => (int)$row['tanah_longsor'], 'puting_beliung' => (int)$row['puting_beliung']]
        ];
        $totBanjir += (int)$row['banjir']; $totLongsor += (int)$row['tanah_longsor'];
        $totPuting += (int)$row['puting_beliung']; $totLainnya += (int)$row['lainnya'];
        $grandTotal += (int)$row['total'];
    }
}

// 2. Data Tren Tahunan
$tren_tahunan = [];
$sql_tahun = "SELECT b.tahun, COALESCE(SUM(b.banjir),0) AS banjir, COALESCE(SUM(b.tanah_longsor),0) AS tanah_longsor,
              COALESCE(SUM(b.angin_puting_beliung),0) AS puting_beliung, COALESCE(SUM(b.karhutla + b.kekeringan + b.gelombang_pasang),0) AS lainnya
              FROM kejadian_bencana b JOIN kecamatan k ON b.kecamatan_id = k.id $whereClause GROUP BY b.tahun ORDER BY b.tahun ASC";
$res_tahun = $conn->query($sql_tahun);
if($res_tahun) {
    while($r = $res_tahun->fetch_assoc()){
        $tren_tahunan[] = ['tahun' => $r['tahun'], 'banjir' => (int)$r['banjir'], 'tanah_longsor' => (int)$r['tanah_longsor'], 'puting_beliung' => (int)$r['puting_beliung'], 'lainnya' => (int)$r['lainnya']];
    }
}

// 3. Data Detail Tabel
$tabel_detail = [];
$sql_detail = "SELECT b.tahun, k.nama, b.banjir, b.tanah_longsor, b.angin_puting_beliung, b.karhutla, b.kekeringan 
               FROM kejadian_bencana b JOIN kecamatan k ON b.kecamatan_id = k.id $whereClause ORDER BY b.tahun DESC";
$res_detail = $conn->query($sql_detail);
if($res_detail) {
    while($r = $res_detail->fetch_assoc()) {
        if($r['banjir'] > 0) $tabel_detail[] = ['tahun'=>$r['tahun'], 'jenis'=>'Banjir', 'lokasi'=>$r['nama'], 'jumlah'=>$r['banjir']];
        if($r['tanah_longsor'] > 0) $tabel_detail[] = ['tahun'=>$r['tahun'], 'jenis'=>'Tanah Longsor', 'lokasi'=>$r['nama'], 'jumlah'=>$r['tanah_longsor']];
        if($r['angin_puting_beliung'] > 0) $tabel_detail[] = ['tahun'=>$r['tahun'], 'jenis'=>'Angin Puting Beliung', 'lokasi'=>$r['nama'], 'jumlah'=>$r['angin_puting_beliung']];
        if($r['karhutla'] > 0) $tabel_detail[] = ['tahun'=>$r['tahun'], 'jenis'=>'Karhutla', 'lokasi'=>$r['nama'], 'jumlah'=>$r['karhutla']];
        if($r['kekeringan'] > 0) $tabel_detail[] = ['tahun'=>$r['tahun'], 'jenis'=>'Kekeringan', 'lokasi'=>$r['nama'], 'jumlah'=>$r['kekeringan']];
    }
}

echo json_encode([
    'summary' => [ 'total_bencana' => $grandTotal, 'total_kecamatan' => count($dataKecamatan), 'dampak_tertinggi' => count($dataKecamatan) > 0 ? $dataKecamatan[0]['nama'] : '-' ],
    'jenis_bencana' => [ 'banjir' => $totBanjir, 'tanah_longsor' => $totLongsor, 'angin_puting_beliung' => $totPuting, 'lainnya' => $totLainnya, 'jumlah' => $grandTotal ],
    'kecamatan_data' => $dataKecamatan,
    'tren_tahunan' => $tren_tahunan,
    'tabel_detail' => $tabel_detail
]);
$conn->close();
?>
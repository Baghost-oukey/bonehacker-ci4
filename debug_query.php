<?php
require 'vendor/autoload.php';
require 'app/Config/Constants.php';

// Mocking CodeIgniter environment for a simple script
$config = new \Config\Database();
$db = \Config\Database::connect();

$regionId = 'all';
$builder = $db->table('terapis t');
$subQueryTindakan = "(SELECT COUNT(h.id) FROM histories h WHERE FIND_IN_SET(t.id, h.terapis_id))";
$subQueryKasbon = "(SELECT COALESCE(SUM(nominal), 0) FROM kasbon_karyawan WHERE terapis_id = t.id AND status_potongan IN ('belum_lunas', 'belum_dipotong'))";
$subQueryTunjangan = "(SELECT COALESCE(SUM(tt.nominal), 0) FROM tunjangan_terapis tt WHERE tt.terapis_id = t.id AND tt.is_active = 1 AND tt.tipe = 'bulanan')";

$builder->select(
    't.id as terapis_id, 
t.nama, 
t.foto, 
r.name as wilayah,
j.nama_jabatan,
COALESCE(pg.tipe_gaji, "Belum Diset") as tipe_gaji,
COALESCE(pg.nominal_gaji, 0) as nominal_gaji,
COALESCE(pg.potong_absen, 0) as potong_absen,
' . $subQueryTindakan . ' as jml_tindakan, 
' . $subQueryKasbon . ' as total_kasbon,
' . $subQueryTunjangan . ' as total_tunjangan'
, false);

$builder->join('gaji_karyawan pg', 'pg.terapis_id = t.id', 'left');
$builder->join('regions r', 'r.id = t.region_id', 'left');
$builder->join('jabatan j', 'j.id = t.jabatan_id', 'left');
$builder->where('t.is_active', 1);

$result = $builder->get()->getResultArray();

file_put_contents('query_test_output.txt', "Count: " . count($result) . "\n" . print_r(array_slice($result, 0, 2), true));
echo "Done. Count: " . count($result);

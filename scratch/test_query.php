<?php
require 'public/index.php';
$db = \Config\Database::connect();
$builder = $db->table('patient_queues pq')
    ->select('pq.id as queue_id, pq.queue_number, pq.queue_date, p.id as patient_id, p.name as patient_name, p.age as patient_age, p.phone, p.address, pa.desa_nama, pa.kecamatan_nama, pa.kabupaten_nama, h.id as history_id, h.process_at, h.finish_at')
    ->join('patients p', 'p.id = pq.patient_id', 'left')
    ->join('patient_address pa', 'pa.patient_id = p.id', 'left')
    ->join('histories h', 'h.patient_queue_id = pq.id', 'left')
    ->where('DATE(pq.queue_date) >=', '2026-05-10')
    ->where('DATE(pq.queue_date) <=', '2026-05-10');

// Get total count
$total = $builder->countAllResults(false);

// Apply search
$searchValue = 'budi';
$builder->groupStart()
    ->like('p.name', $searchValue, 'both')
    ->orLike('p.phone', $searchValue, 'both')
    ->orLike('pa.kabupaten_nama', $searchValue, 'both')
    ->orLike('p.id', $searchValue, 'both')
    ->groupEnd();

// Get filtered count
$filtered = $builder->countAllResults(false);

// Get results
$results = $builder->get()->getResult();

echo "Total: $total\n";
echo "Filtered: $filtered\n";
echo "Results:\n";
print_r($results);

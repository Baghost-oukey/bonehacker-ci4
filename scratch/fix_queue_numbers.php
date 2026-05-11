<?php
// Script to fix queue numbers for existing records of today
$db = \Config\Database::connect();
$today = date('Y-m-d');
$regions = $db->table('patient_queues')
    ->select('region_id')
    ->where('queue_date', $today)
    ->groupBy('region_id')
    ->get()
    ->getResult();

foreach ($regions as $r) {
    $rows = $db->table('patient_queues')
        ->where('region_id', $r->region_id)
        ->where('queue_date', $today)
        ->orderBy('created_at', 'ASC')
        ->get()
        ->getResult();
    
    $num = 1;
    foreach ($rows as $row) {
        $db->table('patient_queues')
            ->where('id', $row->id)
            ->update(['queue_number' => $num]);
        $num++;
    }
}
echo "Queue numbers updated for today.\n";

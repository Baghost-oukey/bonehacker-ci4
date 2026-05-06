<?php

namespace App\Modules\api\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;

class Antrean extends BaseController
{
    use ResponseTrait;

    protected $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    /**
     * Get Today's Queues
     * GET /api/antrean?region_id=1
     */
    public function index()
    {
        $regionId = $this->request->getGet('region_id');
        $date = date('Y-m-d');

        $builder = $this->db->table('patient_queues pq')
            ->select('pq.id as queue_id, pq.queue_date, p.id as patient_id, p.name as patient_name, p.phone, p.age as patient_age, h.process_at, h.finish_at')
            ->join('patients p', 'p.id = pq.patient_id', 'left')
            ->join('histories h', 'h.patient_queue_id = pq.id', 'left')
            ->where('DATE(pq.queue_date)', $date);

        if (!empty($regionId) && $regionId !== 'all') {
            $builder->where('pq.region_id', $regionId);
        }

        // Urutkan: Yang belum selesai di atas, lalu berdasarkan waktu daftar
        $queues = $builder->orderBy('h.finish_at', 'ASC')
                         ->orderBy('pq.created_at', 'ASC')
                         ->get()->getResult();

        return $this->respond([
            'status' => 'success',
            'data' => $queues
        ], 200);
    }

    /**
     * Start Process (Terapi Dimulai)
     * POST /api/antrean/proses
     */
    public function proses()
    {
        $queueId = $this->request->getPost('queue_id');
        
        $queue = $this->db->table('patient_queues')->where('id', $queueId)->get()->getRow();
        if (!$queue) {
            return $this->failNotFound('Antrean tidak ditemukan');
        }

        // Cek apakah sudah diproses
        $existing = $this->db->table('histories')->where('patient_queue_id', $queueId)->get()->getRow();
        if ($existing) {
            return $this->failResourceExists('Pasien ini sudah dalam proses atau sudah selesai');
        }

        $this->db->table('histories')->insert([
            'patient_queue_id' => $queueId,
            'patient_id' => $queue->patient_id,
            'type' => 'draft',
            'process_at' => date('Y-m-d H:i:s'),
            'is_delete' => 0
        ]);

        return $this->respond([
            'status' => 'success',
            'message' => 'Terapi dimulai'
        ]);
    }

    /**
     * Finish Process (Terapi Selesai)
     * POST /api/antrean/selesai
     */
    public function selesai()
    {
        $queueId = $this->request->getPost('queue_id');

        $update = $this->db->table('histories')
            ->where('patient_queue_id', $queueId)
            ->update(['finish_at' => date('Y-m-d H:i:s')]);

        if ($update) {
            return $this->respond([
                'status' => 'success',
                'message' => 'Terapi selesai'
            ]);
        }

        return $this->fail('Gagal memperbarui status');
    }
}

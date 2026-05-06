<?php

namespace App\Modules\api\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;

class Statistics extends BaseController
{
    use ResponseTrait;

    protected $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    /**
     * Get Summary for Therapist Dashboard
     * GET /api/statistics/summary?terapis_id=...&region_id=...
     */
    public function summary()
    {
        $terapisId = $this->request->getGet('terapis_id');
        $regionIdInput = $this->request->getGet('region_id');

        if (!$terapisId) return $this->fail('Terapis ID required');

        // Check Role
        $user = $this->db->table('users')->where('id', $terapisId)->get()->getRow();
        if (!$user) return $this->fail('User not found');

        $isSuperAdmin = ($user->role === 'superadmin' || $user->role === 'owner');
        
        // If superadmin, we don't filter by region in the queries
        $regionId = $isSuperAdmin ? null : $regionIdInput;

        // 1. Summary Cards
        $todayTotalQueueBuilder = $this->db->table('patient_queues')->where('DATE(queue_date)', date('Y-m-d'));
        if (!$isSuperAdmin) $todayTotalQueueBuilder->where('region_id', $regionId);
        $todayTotalQueue = $todayTotalQueueBuilder->countAllResults();

        // Transaction Today (Match Web Logic: Sum EVERYTHING without filters)
        $todayTransactionsBuilder = $this->db->table('transaksi')
            ->selectSum('nominal')
            ->where('DATE(created_at)', date('Y-m-d'));
        if (!$isSuperAdmin) $todayTransactionsBuilder->where('region_id', $regionId);
        $todayTransactions = $todayTransactionsBuilder->get()->getRow()->nominal ?? 0;

        $todayKunjunganBuilder = $this->db->table('histories')->where('DATE(date)', date('Y-m-d'));
        if (!$isSuperAdmin) $todayKunjunganBuilder->where('history_region', $regionId);
        $todayKunjungan = $todayKunjunganBuilder->countAllResults();

        // 2. Weekly Stats (Last 7 Days)
        $weeklyStats = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $dayName = date('D', strtotime($date));
            
            $builder = $this->db->table('patient_queues')->where('DATE(queue_date)', $date);
            if (!$isSuperAdmin) $builder->where('region_id', $regionId);
            $count = $builder->countAllResults();
            
            $weeklyStats[] = [
                'day' => $dayName,
                'count' => (int)$count
            ];
        }

        // 3. Patient Comparison
        // Daily
        $yesterday = date('Y-m-d', strtotime("-1 day"));
        $dailyPrevBuilder = $this->db->table('patient_queues')->where('DATE(queue_date)', $yesterday);
        if (!$isSuperAdmin) $dailyPrevBuilder->where('region_id', $regionId);
        $countYesterday = $dailyPrevBuilder->countAllResults();
        
        // Monthly
        $thisMonth = date('Y-m');
        $lastMonth = date('Y-m', strtotime("-1 month"));
        
        $monthCurrBuilder = $this->db->table('patient_queues')->where("DATE_FORMAT(queue_date, '%Y-%m')", $thisMonth);
        if (!$isSuperAdmin) $monthCurrBuilder->where('region_id', $regionId);
        $countThisMonth = $monthCurrBuilder->countAllResults();

        $monthPrevBuilder = $this->db->table('patient_queues')->where("DATE_FORMAT(queue_date, '%Y-%m')", $lastMonth);
        if (!$isSuperAdmin) $monthPrevBuilder->where('region_id', $regionId);
        $countLastMonth = $monthPrevBuilder->countAllResults();

        // Yearly
        $thisYear = date('Y');
        $lastYear = date('Y', strtotime("-1 year"));
        
        $yearCurrBuilder = $this->db->table('patient_queues')->where("DATE_FORMAT(queue_date, '%Y')", $thisYear);
        if (!$isSuperAdmin) $yearCurrBuilder->where('region_id', $regionId);
        $countThisYear = $yearCurrBuilder->countAllResults();

        $yearPrevBuilder = $this->db->table('patient_queues')->where("DATE_FORMAT(queue_date, '%Y')", $lastYear);
        if (!$isSuperAdmin) $yearPrevBuilder->where('region_id', $regionId);
        $countLastYear = $yearPrevBuilder->countAllResults();

        return $this->respond([
            'status' => 'success',
            'data' => [
                'summary' => [
                    'today_queue' => (int)$todayTotalQueue,
                    'today_transactions' => (int)$todayTransactions,
                    'today_kunjungan' => (int)$todayKunjungan,
                    'region_name' => $isSuperAdmin ? 'Semua Wilayah' : $this->getRegionName($regionId)
                ],
                'weekly_stats' => $weeklyStats,
                'comparison' => [
                    'daily' => ['current' => (int)$todayTotalQueue, 'previous' => (int)$countYesterday],
                    'monthly' => ['current' => (int)$countThisMonth, 'previous' => (int)$countLastMonth],
                    'yearly' => ['current' => (int)$countThisYear, 'previous' => (int)$countLastYear]
                ],
                'recent_activities' => []
            ]
        ]);
    }

    private function getRegionName($id) {
        if (!$id) return 'Semua Wilayah';
        $row = $this->db->table('regions')->where('id', $id)->get()->getRow();
        return $row ? $row->name : 'Semua Wilayah';
    }
}

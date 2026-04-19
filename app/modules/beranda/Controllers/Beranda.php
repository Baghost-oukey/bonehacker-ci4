<?php

namespace App\modules\beranda\Controllers;

use App\Controllers\BaseController;
use App\modules\beranda\Models\MBeranda;
use App\modules\countries\Models\MCountries;
use App\modules\region\Models\MRegion;
use CodeIgniter\HTTP\ResponseInterface;
use DateTime;
use DatePeriod;
use DateInterval;

class Beranda extends BaseController
{

    protected $model_beranda;
    protected $model_region;
    protected $model_country;

    public function __construct()
    {
        $this->model_beranda = new MBeranda();
        $this->model_region = new MRegion();
        $this->model_country = new MCountries();
    }

    public function _get_random_greeting()
    {
        $file = FCPATH . 'greetings/greetings.json';
        if (file_exists($file)) {
            $greetings = json_decode(file_get_contents($file), true);
            return (!empty($greetings)) ? $greetings[array_rand($greetings)] : 'Welcome!';
        }
        return 'Welcome!';
    }

    public function _get_calender_data($role, $region_patients)
    {
        $firstDay = date('Y-m-d', strtotime('first day of this month'));
        $start    = (new DateTime($firstDay))->modify('last sunday')->format('Y-m-d');

        $lastDay  = date('Y-m-d', strtotime('last day of this month'));
        $end      = (new DateTime($lastDay))->modify('next saturday')->format('Y-m-d');

        // Query Database
        $db      = \Config\Database::connect();
        $builder = $db->table('patient_queues pq')
            ->select("DATE_FORMAT(pq.queue_date, '%Y-%m-%d') as q_date, COUNT(pq.id) as total")
            ->where('pq.queue_date >=', $start)
            ->where('pq.queue_date <=', $end);

        if (!empty($region_patients) && $region_patients !== 'all') {
            if (is_array($region_patients)) {
                $builder->whereIn('pq.region_id', $region_patients);
            } else {
                $builder->where('pq.region_id', $region_patients);
            }
        }

        $results = $builder->groupBy('pq.queue_date')->get()->getResultArray();
        $counts  = array_column($results, 'total', 'q_date');

        // Generate Period
        $period = new DatePeriod(new DateTime($start), new DateInterval('P1D'), (new DateTime($end))->modify('+1 day'));
        $days   = [];

        foreach ($period as $date) {
            $formatted = $date->format('Y-m-d');
            $days[] = [
                'formatted_date' => $date->format('d/m/Y'),
                'day_name'       => $date->format('l'),
                'daily_count'    => $counts[$formatted] ?? 0
            ];
        }

        return [
            'daily_counts' => array_chunk($days, 7),
            'startDate'    => $start,
            'endDate'      => $end
        ];
    }



    public function index()
    {
        //
        $session = session();
        // dd($session->get('region_patient'));
        $role = $session->get('role');
        $active_region = $session->get('active_region');
        $region_patients = $session->get('region_patient');

        if ($role === 'owner' || $role === 'superadmin') {
            $filter_region = ($active_region) ? $active_region : 'all';
        } else {
            $filter_region = $region_patients;
        }

        $data = [
            'realname'        => $session->get('realname'),
            'role'            => $role,
            'region_patient' => $filter_region,
            'base_url'        => base_url(),
            'current_segment' => $this->request->getUri()->getSegment(1),
            'title'           => 'Beranda',
            'msg'             => $session->getFlashdata('message'),
            'wilayah'         => $this->model_region->getData(),
            'negara'          => $this->model_country->getData(),
        ];

        $data['greeting'] = $this->_get_random_greeting();

        // Statistik Pasien
        $stats = ['today', 'yesterday', 'thismonth', 'lastmonth', 'thisyear', 'lastyear', 'all'];
        foreach ($stats as $type) {
            $data["pasien_$type"]    = $this->model_beranda->getPatientCount($type, $role, $filter_region);
            $data["kunjungan_$type"] = $this->model_beranda->getVisitCount($type, $role, $filter_region);
        }

        // Kalender Antrian
        $calendarData = $this->_get_calender_data($role, $filter_region);
        $data = array_merge($data, $calendarData);

        $db = \Config\Database::connect();
        $todayDate = date('Y-m-d');

        $queueTodayBuilder = $db->table('patient_queues')->where('DATE(queue_date)', $todayDate);
        if (!empty($filter_region) && $filter_region !== 'all') {
            if (is_array($filter_region)) {
                $queueTodayBuilder->whereIn('region_id', $filter_region);
            } else {
                $queueTodayBuilder->where('region_id', $filter_region);
            }
        }
        $data['queue_today'] = (int) $queueTodayBuilder->countAllResults();

        $transactionTodayBuilder = $db->table('transaksi')->selectSum('nominal')->where('DATE(created_at)', $todayDate);
        if (!empty($filter_region) && $filter_region !== 'all') {
            if (is_array($filter_region)) {
                $transactionTodayBuilder->whereIn('region_id', $filter_region);
            } else {
                $transactionTodayBuilder->where('region_id', $filter_region);
            }
        }
        $transactionTodayRow = $transactionTodayBuilder->get()->getRow();
        $data['transaction_today_total'] = (int) ($transactionTodayRow->nominal ?? 0);
        $data['active_region_name'] = $session->get('active_region_name') ?? 'Cabang Tidak Terdeteksi';

        return view('App\modules\beranda\Views\beranda_views', $data);
    }
}

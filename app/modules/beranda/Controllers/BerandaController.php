<?php

namespace App\Modules\Beranda\Controllers;

use App\Controllers\BaseController;
use App\Modules\Beranda\Models\MBeranda;
use App\Modules\Countries\Models\MCountries;
use App\Modules\Region\Models\MRegion;
use DateTime;
use DatePeriod;
use DateInterval;

class BerandaController extends BaseController
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

  private function getRandomGreeting()
  {
    $file = FCPATH . 'greetings/greetings.json';

    if (file_exists($file)) {
      $greetings = json_decode(file_get_contents($file), true);
      return !empty($greetings) ? $greetings[array_rand($greetings)] : 'Welcome!';
    }

    return 'Welcome!';
  }

  private function getCalendarData($role, $region_patients)
  {
    $firstDay = date('Y-m-d', strtotime('first day of this month'));
    $lastDay = date('Y-m-d', strtotime('last day of this month'));

    $startObj = new DateTime($firstDay);
    $startObj->modify('last sunday');
    $start = $startObj->format('Y-m-d');

    $endObj = new DateTime($lastDay);
    $endObj->modify('next saturday');
    $end = $endObj->format('Y-m-d');

    $db = \Config\Database::connect();

    $builder = $db->table('patient_queues pq')->select("DATE_FORMAT(pq.queue_date, '%Y-%m-%d') as q_date, COUNT(pq.id) as total")->where('pq.queue_date >=', $start)->where('pq.queue_date <=', $end);

    if (!empty($region_patients) && $region_patients !== 'all') {
      is_array($region_patients) ? $builder->whereIn('pq.region_id', $region_patients) : $builder->where('pq.region_id', $region_patients);
    }

    $results = $builder->groupBy('pq.queue_date')->get()->getResultArray();

    $counts = array_column($results, 'total', 'q_date');

    $periodEnd = new DateTime($end);
    $periodEnd->modify('+1 day');

    $period = new DatePeriod(new DateTime($start), new DateInterval('P1D'), $periodEnd);

    $days = [];

    foreach ($period as $date) {
      $formatted = $date->format('Y-m-d');

      $days[] = [
        'formatted_date' => $date->format('d/m/Y'),
        'day_name' => $date->format('l'),
        'daily_count' => $counts[$formatted] ?? 0,
      ];
    }

    return [
      'daily_counts' => array_chunk($days, 7),
      'startDate' => $start,
      'endDate' => $end,
    ];
  }

  public function index()
  {
    $session = session();

    $role = $session->get('role');
    $active_region = $session->get('active_region');
    $region_patients = $session->get('region_patient');

    if ($role === 'owner' || $role === 'superadmin') {
      $filter_region = $active_region ?: 'all';
    } else {
      $filter_region = $region_patients;
    }

    $data = [
      'realname' => $session->get('realname'),
      'role' => $role,
      'region_patient' => $filter_region,
      'base_url' => base_url(),
      'current_segment' => $this->request->getUri()->getSegment(1),
      'title' => 'Beranda',
      'msg' => $session->getFlashdata('message'),
      'wilayah' => $this->model_region->getData(),
      'negara' => $this->model_country->getData(),
      'greeting' => $this->getRandomGreeting(),
    ];

    $stats = ['today', 'yesterday', 'thismonth', 'lastmonth', 'thisyear', 'lastyear', 'all'];

    foreach ($stats as $type) {
      $data["pasien_$type"] = $this->model_beranda->getPatientCount($type, $role, $filter_region);
      $data["kunjungan_$type"] = $this->model_beranda->getVisitCount($type, $role, $filter_region);
    }

    $data = array_merge($data, $this->getCalendarData($role, $filter_region));

    $db = \Config\Database::connect();
    $todayDate = date('Y-m-d');

    // Queue Today
    $queueBuilder = $db->table('patient_queues')->where('DATE(queue_date)', $todayDate);

    if (!empty($filter_region) && $filter_region !== 'all') {
      is_array($filter_region) ? $queueBuilder->whereIn('region_id', $filter_region) : $queueBuilder->where('region_id', $filter_region);
    }

    $data['queue_today'] = (int) $queueBuilder->countAllResults();

    // Transaction Today
    $transactionBuilder = $db->table('transaksi')->selectSum('nominal')->where('DATE(created_at)', $todayDate);

    if (!empty($filter_region) && $filter_region !== 'all') {
      is_array($filter_region) ? $transactionBuilder->whereIn('region_id', $filter_region) : $transactionBuilder->where('region_id', $filter_region);
    }

    $row = $transactionBuilder->get()->getRow();

    $data['transaction_today_total'] = (int) ($row->nominal ?? 0);
    $data['active_region_name'] = $session->get('active_region_name') ?? 'Cabang Tidak Terdeteksi';

    return view('App\Modules\Beranda\Views\index', $data);
  }
}

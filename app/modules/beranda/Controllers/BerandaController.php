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

    // Desktop: Show statistics dashboard
    // Mobile/Tablet: Show menu grid (handled by view with responsive classes)
    
    // If user is a therapist (role user and has terapis_id), show therapist statistics
    if ($role === 'user' && !empty($session->get('terapis_id'))) {
      return $this->userStatistik();
    }

    // For other roles, show regular statistics dashboard
    $active_region = $session->get('active_region');
    $region_patients = $session->get('region_patient');

    if ($role === 'superadmin') {
      $filter_region = $active_region ?: 'all';
    } else if ($role === 'owner') {
      $filter_region = ($active_region && $active_region !== 'all') ? $active_region : $region_patients;
    } else {
      $filter_region = $region_patients;
    }

    $allowed_regions = ($role === 'superadmin') ? null : $region_patients;

    $data = [
      'realname' => $session->get('realname'),
      'role' => $role,
      'region_patient' => $filter_region,
      'base_url' => base_url(),
      'current_segment' => $this->request->getUri()->getSegment(1),
      'title' => 'Beranda',
      'msg' => $session->getFlashdata('message'),
      'wilayah' => $this->model_region->getData(null, $allowed_regions),
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
    $transactionBuilder = $db->table('transaksi')->selectSum('nominal')
      ->where('DATE(created_at)', $todayDate)
      ->where('status', 'active')
      ->where('type', 'income');

    if (!empty($filter_region) && $filter_region !== 'all') {
      is_array($filter_region) ? $transactionBuilder->whereIn('region_id', $filter_region) : $transactionBuilder->where('region_id', $filter_region);
    }

    $row = $transactionBuilder->get()->getRow();

    $data['transaction_today_total'] = (int) ($row->nominal ?? 0);
    $data['active_region_name'] = $session->get('active_region_name') ?? 'Cabang Tidak Terdeteksi';

    return view('App\Modules\Beranda\Views\index', $data);
  }

  /**
   * Statistik khusus untuk user/terapis (dipanggil dari beranda)
   */
  private function userStatistik()
  {
    $session = session();
    $terapisId = $session->get('terapis_id_int');
    $db = \Config\Database::connect();

    // Get terapis data
    $terapis = $db->table('terapis')
      ->where('id', $terapisId)
      ->get()
      ->getRow();

    if (!$terapis) {
      return redirect()->to('/menu')->with('error', 'Data terapis tidak ditemukan');
    }

    $regionId = $terapis->region_id;
    $bulanIni = date('Y-m');

    $data = [
      'title' => 'Beranda',
      'base_url' => base_url(),
      'current_segment' => 'beranda',
      'terapis' => $terapis,
      'realname' => $session->get('realname'),
      'role' => 'user',
      'bulan_display' => date('F Y'),
    ];

    // Get statistics
    $data['statistik_pasien'] = $this->getStatistikPasienPerHari($terapisId, $bulanIni);

    // Get attendance data if presensi is enabled
    if ($terapis->is_presensi == 1) {
      $data['rekap_kehadiran'] = $this->getRekapKehadiran($terapisId, $bulanIni);
    } else {
      $data['rekap_kehadiran'] = null;
    }

    // Get jaspel data
    $data['jaspel_harian'] = $this->getJaspelHarian($terapisId, $regionId, $bulanIni);

    return view('App\Modules\Beranda\Views\index', $data);
  }

  /**
   * Get daily patient statistics for the therapist
   */
  private function getStatistikPasienPerHari($terapisId, $bulan)
  {
    $startDate = date('Y-m-01', strtotime($bulan));
    $endDate = date('Y-m-t', strtotime($bulan));

    $db = \Config\Database::connect();

    $query = $db->table('histories h')
      ->select("DATE(h.date) as tanggal, COUNT(DISTINCT h.patient_id) as jumlah_pasien")
      ->where('h.terapis_id', $terapisId)
      ->where('DATE(h.date) >=', $startDate)
      ->where('DATE(h.date) <=', $endDate)
      ->where('h.is_delete', 0)
      ->where('h.type', 'posted')
      ->groupBy('DATE(h.date)')
      ->orderBy('tanggal', 'ASC')
      ->get()
      ->getResultArray();

    $stats = [];
    foreach ($query as $row) {
      $stats[$row['tanggal']] = (int) $row['jumlah_pasien'];
    }

    $totalPasien = array_sum($stats);
    $hariKerja = count($stats);
    $rataRata = $hariKerja > 0 ? round($totalPasien / $hariKerja, 1) : 0;

    return [
      'per_hari' => $stats,
      'total_pasien' => $totalPasien,
      'hari_kerja' => $hariKerja,
      'rata_rata' => $rataRata,
    ];
  }

  /**
   * Get attendance recap for the therapist
   */
  private function getRekapKehadiran($terapisId, $bulan)
  {
    $startDate = date('Y-m-01', strtotime($bulan));
    $endDate = date('Y-m-t', strtotime($bulan));

    $db = \Config\Database::connect();

    $query = $db->table('absensi_karyawan')
      ->select('tanggal, status, keterangan')
      ->where('terapis_id', $terapisId)
      ->where('tanggal >=', $startDate)
      ->where('tanggal <=', $endDate)
      ->orderBy('tanggal', 'ASC')
      ->get()
      ->getResultArray();

    $hadir = 0;
    $izin = 0;
    $sakit = 0;
    $alpha = 0;
    $cuti = 0;

    foreach ($query as $row) {
      $status = strtolower($row['status']);
      switch ($status) {
        case 'hadir':
          $hadir++;
          break;
        case 'izin':
          $izin++;
          break;
        case 'sakit':
          $sakit++;
          break;
        case 'alpha':
          $alpha++;
          break;
        case 'cuti':
          $cuti++;
          break;
      }
    }

    return [
      'records' => $query,
      'hadir' => $hadir,
      'izin' => $izin,
      'sakit' => $sakit,
      'alpha' => $alpha,
      'cuti' => $cuti,
      'total_hari' => count($query),
    ];
  }

  /**
   * Get daily jaspel for the therapist
   */
  private function getJaspelHarian($terapisId, $regionId, $bulan)
  {
    $startDate = date('Y-m-01', strtotime($bulan));
    $endDate = date('Y-m-t', strtotime($bulan));

    $db = \Config\Database::connect();

    $settingsReguler = $db->table('jaspel_settings')
      ->where('region_id', $regionId)
      ->where('tipe', 'reguler')
      ->get()
      ->getRow();

    $settingsKejantanan = $db->table('jaspel_settings')
      ->where('region_id', $regionId)
      ->where('tipe', 'kejantanan')
      ->get()
      ->getRow();

    if (!$settingsReguler && !$settingsKejantanan) {
      return [
        'per_hari' => [],
        'total_jaspel' => 0,
        'total_reguler' => 0,
        'total_kejantanan' => 0,
        'settings_exist' => false,
      ];
    }

    $nominalReguler = $settingsReguler ? (int) $settingsReguler->nominal_per_pasien : 0;
    $nominalKejantanan = $settingsKejantanan ? (int) $settingsKejantanan->nominal_per_pasien : 0;

    $terapisIdsReguler = $settingsReguler ? json_decode($settingsReguler->terapis_ids, true) ?? [] : [];
    $terapisIdsKejantanan = $settingsKejantanan ? json_decode($settingsKejantanan->terapis_ids, true) ?? [] : [];
    $allowedReguler = in_array($terapisId, $terapisIdsReguler);
    $allowedKejantanan = in_array($terapisId, $terapisIdsKejantanan);

    $jaspelPerHari = [];

    $kehadiranQuery = $db->table('absensi_karyawan')
      ->select('tanggal')
      ->where('terapis_id', $terapisId)
      ->where('status', 'Hadir')
      ->where('tanggal >=', $startDate)
      ->where('tanggal <=', $endDate)
      ->get()
      ->getResultArray();

    $tanggalHadir = array_column($kehadiranQuery, 'tanggal');

    foreach ($tanggalHadir as $tanggal) {
      $totalPasienReguler = $db->table('histories h')
        ->where('DATE(h.date)', $tanggal)
        ->where('h.history_region', $regionId)
        ->where('h.is_delete', 0)
        ->where('h.type', 'posted')
        ->groupStart()
        ->where('h.kejantanan IS NULL')
        ->orWhere('h.kejantanan !=', 'ya')
        ->groupEnd()
        ->countAllResults();

      $totalPasienKejantanan = $db->table('histories h')
        ->where('DATE(h.date)', $tanggal)
        ->where('h.history_region', $regionId)
        ->where('h.is_delete', 0)
        ->where('h.type', 'posted')
        ->where('h.kejantanan', 'ya')
        ->countAllResults();

      $jumlahTerapisHadir = $db->table('absensi_karyawan ak')
        ->join('terapis t', 't.id = ak.terapis_id', 'inner')
        ->where('ak.tanggal', $tanggal)
        ->where('ak.status', 'Hadir')
        ->where('t.region_id', $regionId)
        ->where('t.is_active', 1)
        ->where('t.is_presensi', 1)
        ->countAllResults();

      if ($jumlahTerapisHadir > 0) {
        $jaspelReguler = $allowedReguler ? ($totalPasienReguler * $nominalReguler) : 0;
        $jaspelKejantanan = $allowedKejantanan ? ($totalPasienKejantanan * $nominalKejantanan) : 0;
        $totalJaspel = $jaspelReguler + $jaspelKejantanan;

        $jaspelPerHari[$tanggal] = [
          'reguler' => (int) $jaspelReguler,
          'kejantanan' => (int) $jaspelKejantanan,
          'total' => (int) $totalJaspel,
          'pasien_reguler' => $totalPasienReguler,
          'pasien_kejantanan' => $totalPasienKejantanan,
          'terapis_hadir' => $jumlahTerapisHadir,
        ];
      }
    }

    $totalJaspelReguler = array_sum(array_column($jaspelPerHari, 'reguler'));
    $totalJaspelKejantanan = array_sum(array_column($jaspelPerHari, 'kejantanan'));
    $totalJaspel = $totalJaspelReguler + $totalJaspelKejantanan;

    return [
      'per_hari' => $jaspelPerHari,
      'total_jaspel' => $totalJaspel,
      'total_reguler' => $totalJaspelReguler,
      'total_kejantanan' => $totalJaspelKejantanan,
      'settings_exist' => true,
    ];
  }

  public function menu()
  {
    $session = session();
    $role = $session->get('role');
    
    $data = [
      'role' => $role,
      'title' => 'Menu Utama',
      'base_url' => base_url(),
      'current_segment' => 'menu',
    ];

    return view('App\Modules\Beranda\Views\menu', $data);
  }
}

<?php

namespace App\modules\statistik\Controllers;

use App\Controllers\BaseController;
use App\modules\region\Models\MRegion;
use App\modules\statistik\Models\MStatistik;
use CodeIgniter\HTTP\ResponseInterface;

class Statistik extends BaseController
{
    protected $model_statistik;
    protected $model_region;
    protected $session;

    public function __construct()
    {
        $this->model_statistik = new MStatistik();
        $this->model_region = new MRegion();
        $this->session = \Config\Services::session();
    }
    public function index()
    {
        $role = $this->session->get('role');

        // If user is a therapist (role user and has terapis_id), show therapist statistics
        if ($role === 'user' && !empty($this->session->get('terapis_id'))) {
            return $this->terapisStatistik();
        }

        // Regular statistics for other roles
        $region_patient = $this->session->get('region_patient');
        $allowed_regions = ($region_patient !== 'all') ? $region_patient : null;

        $data = [
            'realname'        => $this->session->get('realname'),
            'role'            => $role,
            'base_url'        => base_url(),
            'current_segment' => $this->request->getUri()->getSegment(1),
            'title'           => 'Statistik',
            'wilayah'         => $this->model_region->getData(null, $allowed_regions),
            'msg'             => $this->session->getFlashdata('message') ?: ['', '', '']
        ];

        return view('App\modules\statistik\Views\views_statistik', $data);
    }

    /**
     * Statistik khusus untuk terapis
     */
    private function terapisStatistik()
    {
        $terapisId = $this->session->get('terapis_id_int');
        $db = \Config\Database::connect();

        // Get terapis data
        $terapis = $db->table('terapis')
            ->where('id', $terapisId)
            ->get()
            ->getRow();

        if (!$terapis) {
            return redirect()->to('/beranda')->with('error', 'Data terapis tidak ditemukan');
        }

        $regionId = $terapis->region_id;
        $bulanIni = date('Y-m');

        $data = [
            'title' => 'Statistik Terapis',
            'base_url' => base_url(),
            'current_segment' => 'statistik',
            'terapis' => $terapis,
            'realname' => $this->session->get('realname'),
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

        return view('App\modules\statistik\Views\terapis_statistik', $data);
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

        $nominalReguler = $settingsReguler ? (int) round((float) $settingsReguler->nominal_per_pasien) : 0;
        $nominalKejantanan = $settingsKejantanan ? (int) round((float) $settingsKejantanan->nominal_per_pasien) : 0;

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

    // public function fetch_statistics()
    // {
    //     $startDate = $this->request->getGet('start_date');
    //     $endDate   = $this->request->getGet('end_date');
    //     $filter    = $this->request->getGet('filter') ?? 'daily';
    //     $regid     = $this->request->getGet('region_id');
    //     $data = $this->model_statistik->get_statistics($startDate, $endDate, $regid, $filter);

    //     return $this->response->setJSON($data);
    // }
    public function fetch_analysis()
    {
        $startDate = $this->request->getGet('start_date');
        $endDate   = $this->request->getGet('end_date');

        // Validation for secure date formats (Best Practice)
        if (!$startDate || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $startDate)) {
            $startDate = date('Y-m-d');
        }
        if (!$endDate || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $endDate)) {
            $endDate = date('Y-m-d');
        }

        // Use URL param if provided, otherwise fall back to session
        $regionId  = $this->request->getGet('region_id');
        if (!$regionId || $regionId === 'all') {
            $rp = $this->session->get('region_patient');
            $regionId = ($rp !== 'all' && !empty($rp)) ? (is_array($rp) ? $rp[0] : $rp) : null;
        }
        $result = $this->model_statistik->get_analisis($startDate, $endDate, $regionId);

        $summary = [
            'total' => 0,
            'baru'  => 0,
            'lama'  => 0,
            'avg_per_day' => 0
        ];

        foreach ($result as $row) {
            $summary['total'] += (int)$row->total_pasien;
            $summary['baru']  += (int)$row->pasien_baru;
            $summary['lama']  += (int)$row->pasien_lama;
        }

        $diff = (strtotime($endDate) - strtotime($startDate)) / (60 * 60 * 24) + 1;
        $summary['avg_per_day'] = $diff > 0 ? round($summary['total'] / $diff, 1) : 0;

        return $this->response->setJSON([
            'summary' => $summary,
            'details' => $result
        ]);
    }
}

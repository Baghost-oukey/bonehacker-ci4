<?php

namespace App\modules\transaksi_tunjangan\Controllers;

use App\Controllers\BaseController;
use App\Modules\Karyawan\Models\MKaryawan;
use App\modules\transaksi_tunjangan\Models\Mtransaksitunjangan;
use App\modules\tunjangan_karyawan\Models\Mtunjangankaryawan;
use App\modules\tunjangan_karyawan\Models\MtunjanganTerapis;

class Transaksitunjangan extends BaseController
{
    protected $model_karyawan;
    protected $model_transaksi_karyawan;
    protected $model_tunjangan_karyawan;
    protected $model_tunjangan_terapis;
    protected $db;

    public function __construct()
    {
        $this->model_karyawan              = new MKaryawan();
        $this->model_tunjangan_karyawan   = new Mtunjangankaryawan();
        $this->model_transaksi_karyawan   = new Mtransaksitunjangan();
        $this->model_tunjangan_terapis    = new MtunjanganTerapis();
        $this->db = \Config\Database::connect();
    }

    public function index()
    {
        $role           = session()->get('role');
        $region_patient = session()->get('region_patient');
        $regionFilter   = ($region_patient !== 'all' && !empty($region_patient)) ? $region_patient : null;

        $data = [
            'title'            => 'Kelola Tunjangan Terapis',
            'master_tunjangan' => $this->model_tunjangan_karyawan->orderBy('nama_tunjangan', 'ASC')->findAll(),
            'current_segment'  => 'transaksi-tunjangan',
            'role'             => $role,
        ];
        return view('App\modules\transaksi_tunjangan\Views\index', $data);
    }

    public function fetch()
    {
        $draw   = $this->request->getPost('draw');
        $start  = $this->request->getPost('start');
        $length = $this->request->getPost('length');
        $search = $this->request->getPost('search')['value'];

        $region_patient = session()->get('region_patient');
        $regionFilter   = ($region_patient !== 'all' && !empty($region_patient)) ? $region_patient : null;

        $dataRaw = $this->model_transaksi_karyawan->get_datatables_terapis($search, $start, $length, $regionFilter);
        $data    = [];

        foreach ($dataRaw as $row) {
            // Hitung total tunjangan dari setting (bukan transaksi lama)
            $settings       = $this->model_tunjangan_terapis->getByTerapis((int) $row['id']);
            $totalBulanan   = array_sum(array_column(array_filter($settings, fn($s) => $s['tipe'] === 'bulanan'), 'nominal'));
            $totalHarian    = array_sum(array_column(array_filter($settings, fn($s) => $s['tipe'] === 'harian'), 'nominal'));
            $jumlahSetting  = count($settings);

            $data[] = [
                'id'             => $row['id'],
                'nama'           => esc($row['nama']),
                'jabatan'        => esc($row['nama_jabatan'] ?? 'TERAPIS'),
                'gaji_pokok'     => 'Rp ' . number_format($row['nominal_gaji'] ?? 0, 0, ',', '.'),
                'tunjangan_info' => $jumlahSetting > 0
                    ? $jumlahSetting . ' jenis tunjangan aktif'
                    : 'Belum ada setting',
                'tunjangan_raw'  => $jumlahSetting,
            ];
        }

        return $this->response->setJSON([
            "draw"            => intval($draw),
            "recordsTotal"    => $this->model_transaksi_karyawan->count_all_terapis($regionFilter),
            "recordsFiltered" => $this->model_transaksi_karyawan->count_filtered_terapis($search, $regionFilter),
            "data"            => $data,
            "csrfHash"        => csrf_hash()
        ]);
    }

    public function detail($id)
    {
        $terapis = $this->model_transaksi_karyawan->getDetailTerapis($id);
        if (!$terapis) throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();

        $data = [
            'title'            => 'Setting Tunjangan - ' . $terapis['nama'],
            'terapis'          => $terapis,
            'master_tunjangan' => $this->model_tunjangan_karyawan->orderBy('nama_tunjangan', 'ASC')->findAll(),
            'settings'         => $this->model_tunjangan_terapis->getByTerapis((int) $id),
        ];

        return view('App\modules\transaksi_tunjangan\Views\detail_transaksi_tunjangan', $data);
    }

    /**
     * Setting tunjangan massal ke semua terapis aktif di cabang
     */
    public function saveSettingMassal()
    {
        $tunjanganId = (int) $this->request->getPost('tunjangan_karyawan_id');
        $nominal     = (float) preg_replace('/[^0-9]/', '', $this->request->getPost('nominal'));
        $tipe        = $this->request->getPost('tipe');
        $userId      = session()->get('userId');

        if (!$tunjanganId || $nominal <= 0) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Data tidak lengkap']);
        }

        $region_patient = session()->get('region_patient');
        $terapisQuery   = $this->model_karyawan->where('is_active', 1);

        if (!empty($region_patient) && $region_patient !== 'all') {
            if (is_array($region_patient)) {
                $terapisQuery->whereIn('region_id', $region_patient);
            } else {
                $terapisQuery->where('region_id', $region_patient);
            }
        }

        $terapisAktif = $terapisQuery->findAll();
        if (empty($terapisAktif)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Tidak ada terapis aktif']);
        }

        $count = 0;
        foreach ($terapisAktif as $t) {
            $id = is_object($t) ? $t->id : $t['id'];
            $this->model_tunjangan_terapis->saveSetting($id, $tunjanganId, $nominal, $tipe, $userId);
            $count++;
        }

        return $this->response->setJSON([
            'status'   => 'success',
            'message'  => "Setting tunjangan berhasil diterapkan ke $count terapis",
            'csrfHash' => csrf_hash()
        ]);
    }

    /**
     * Simpan setting tunjangan per terapis
     */
    public function saveSetting()
    {
        $terapisId  = (int) $this->request->getPost('terapis_id');
        $tunjanganId = (int) $this->request->getPost('tunjangan_karyawan_id');
        $nominal     = (float) preg_replace('/[^0-9]/', '', $this->request->getPost('nominal'));
        $tipe        = $this->request->getPost('tipe');
        $userId      = session()->get('userId');

        if (!$terapisId || !$tunjanganId || $nominal <= 0) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Data tidak lengkap']);
        }

        $result = $this->model_tunjangan_terapis->saveSetting($terapisId, $tunjanganId, $nominal, $tipe, $userId);

        return $this->response->setJSON([
            'status'   => $result ? 'success' : 'error',
            'message'  => $result ? 'Setting tunjangan berhasil disimpan' : 'Gagal menyimpan',
            'csrfHash' => csrf_hash()
        ]);
    }

    /**
     * Hapus setting tunjangan
     */
    public function deleteSetting($id)
    {
        $this->model_tunjangan_terapis->delete($id);
        return $this->response->setJSON(['status' => 'success', 'csrfHash' => csrf_hash()]);
    }

    /**
     * Store transaksi manual (bonus, dll) — tetap dipertahankan untuk kasus khusus
     */
    public function store()
    {
        $tipeInput = $this->request->getPost('tipe_input');
        $nominal   = (int) preg_replace('/[^0-9]/', '', $this->request->getPost('nominal'));
        $masterId  = $this->request->getPost('tunjangan_karyawan_id');
        $tanggal   = $this->request->getPost('tanggal') ?: date('Y-m-d');
        $ket       = $this->request->getPost('keterangan');

        if ($nominal <= 0 || empty($masterId)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Data input tidak lengkap.']);
        }

        if ($tipeInput === 'massal') {
            $region_patient = session()->get('region_patient');
            $terapisQuery   = $this->model_karyawan->where('is_active', 1);

            if (!empty($region_patient) && $region_patient !== 'all') {
                if (is_array($region_patient)) {
                    $terapisQuery->whereIn('region_id', $region_patient);
                } else {
                    $terapisQuery->where('region_id', $region_patient);
                }
            }

            $terapisAktif = $terapisQuery->findAll();
            if (empty($terapisAktif)) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Tidak ada terapis aktif.']);
            }

            $batchData = [];
            foreach ($terapisAktif as $t) {
                $idTerapis  = is_object($t) ? $t->id : $t['id'];
                $batchData[] = [
                    'terapis_id'            => $idTerapis,
                    'tunjangan_karyawan_id' => $masterId,
                    'tanggal'               => $tanggal,
                    'nominal'               => $nominal,
                    'keterangan'            => $ket,
                    'status_pembayaran'     => 'Belum Dibayar'
                ];
            }
            $this->model_transaksi_karyawan->insertBatch($batchData);
            $msg = "Bonus massal berhasil diberikan ke " . count($batchData) . " terapis.";
        } else {
            $terapisId = $this->request->getPost('terapis_id');
            $this->model_transaksi_karyawan->insert([
                'terapis_id'            => $terapisId,
                'tunjangan_karyawan_id' => $masterId,
                'tanggal'               => $tanggal,
                'nominal'               => $nominal,
                'keterangan'            => $ket,
                'status_pembayaran'     => 'Belum Dibayar'
            ]);
            $msg = "Bonus berhasil dicatat.";
        }

        return $this->response->setJSON(['status' => 'success', 'message' => $msg, 'csrfHash' => csrf_hash()]);
    }
}

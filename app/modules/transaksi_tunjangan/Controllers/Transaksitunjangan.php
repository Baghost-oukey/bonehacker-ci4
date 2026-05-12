<?php

namespace App\modules\transaksi_tunjangan\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\modules\terapis\Models\MTerapis;
use App\modules\transaksi_tunjangan\Models\Mtransaksitunjangan;
use App\modules\tunjangan_karyawan\Models\Mtunjangankaryawan;


class Transaksitunjangan extends BaseController
{

    protected $model_terapis;
    protected $model_transaksi_karyawan;
    protected $model_tunjangan_karyawan;
    protected $db;

    public function __construct()
    {
        $this->model_terapis = new MTerapis();
        $this->model_tunjangan_karyawan = new Mtunjangankaryawan();
        $this->model_transaksi_karyawan = new Mtransaksitunjangan();
        $this->db = \Config\Database::connect();
    }


    public function index()
    {
        $data = [
            'title'            => 'Kelola Tunjangan Terapis',
            'master_tunjangan' => $this->model_tunjangan_karyawan->orderBy('nama_tunjangan', 'ASC')->findAll(),
            'current_segment'  => 'transaksi-tunjangan'
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
        $regionFilter = ($region_patient !== 'all' && !empty($region_patient)) ? $region_patient : null;

        $dataRaw = $this->model_transaksi_karyawan->get_datatables_terapis($search, $start, $length, $regionFilter);
        $data = [];

        foreach ($dataRaw as $row) {
            $totalTunjangan = $this->model_transaksi_karyawan->getTotalBelumCair((int) $row['id']);

            $data[] = [
                'id'              => $row['id'],
                'nama'            => esc($row['nama']),
                'jabatan'         => esc($row['nama_jabatan'] ?? 'TERAPIS'),
                'gaji_pokok'      => 'Rp ' . number_format($row['nominal_gaji'] ?? 0, 0, ',', '.'),
                'tunjangan_aktif' => ($totalTunjangan > 0) ? 'Rp ' . number_format($totalTunjangan, 0, ',', '.') : 'Rp 0',
                'tunjangan_raw'   => $totalTunjangan
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
            'title'            => 'Detail Tunjangan - ' . $terapis['nama'],
            'terapis'          => $terapis,
            'master_tunjangan' => $this->model_tunjangan_karyawan->orderBy('nama_tunjangan', 'ASC')->findAll(),
            'total_tunjangan'  => $this->model_transaksi_karyawan->getTotalBelumCair($id),
            'riwayat'          => $this->model_transaksi_karyawan->getRiwayatTunjangan($id)
        ];

        return view('App\modules\transaksi_tunjangan\Views\detail_transaksi_tunjangan', $data);
    }


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

        $this->db->transStart();

        if ($tipeInput === 'massal') {
            $region_patient = session()->get('region_patient');
            $terapisQuery = $this->model_terapis->where('is_active', 1);

            if (!empty($region_patient) && $region_patient !== 'all') {
                if (is_array($region_patient)) {
                    $terapisQuery->whereIn('region_id', $region_patient);
                } else {
                    $terapisQuery->where('region_id', $region_patient);
                }
            }

            $terapisAktif = $terapisQuery->findAll();
            if (empty($terapisAktif)) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Tidak ada terapis aktif ditemukan.']);
            }

            $batchData = [];
            foreach ($terapisAktif as $t) {
                $idTerapis = is_object($t) ? $t->id : $t['id'];
                $batchData[] = [
                    'terapis_id'            => $idTerapis,
                    'tunjangan_karyawan_id' => $masterId,
                    'tanggal'               => $tanggal,
                    'nominal'               => $nominal,
                    'keterangan'            => $ket,
                    'status_pembayaran'      => 'Belum Dibayar'
                ];
            }
            // Perbaikan: Gunakan method model untuk insertBatch
            if (!empty($batchData)) {
                $this->model_transaksi_karyawan->insertBatch($batchData);
            }
            $msg = "Tunjangan massal berhasil diberikan ke " . count($batchData) . " terapis.";
        } else {
            $terapisId = $this->request->getPost('terapis_id');
            $this->model_transaksi_karyawan->insert([
                'terapis_id'            => $terapisId,
                'tunjangan_karyawan_id' => $masterId,
                'tanggal'               => $tanggal,
                'nominal'               => $nominal,
                'keterangan'            => $ket,
                'status_pembayaran'      => 'Belum Dibayar'
            ]);
            $msg = "Tunjangan berhasil dicatat.";
        }

        $this->db->transComplete();
        return ($this->db->transStatus() === false)
            ? $this->response->setJSON(['status' => 'error', 'message' => 'Gagal memproses data.'])
            : $this->response->setJSON(['status' => 'success', 'message' => $msg, 'csrfHash' => csrf_hash()]);
    }
}

<?php

namespace App\modules\gaji\Models;

use CodeIgniter\Model;

class Mgajikaryawan extends Model
{
    protected $table            = 'riwayat_gaji';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'terapis_id',
        'periode_bulan',
        'periode_tahun',
        'total_kehadiran',
        'gaji_pokok_total',
        'total_tunjangan',
        'total_potongan',
        'gaji_bersih',
        'tanggal_bayar',
        'status',
        'potong_absen'
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = '';
    // protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];



    public function getPayrollEstimates($regionId = 'all')
    {
        $builder = $this->db->table('terapis t');
        // SUB QUERY UNTUK JUMLAH TINDAKAN
        $subQueryTindakan = "(SELECT COUNT(h.id) FROM histories h WHERE FIND_IN_SET(t.id, h.terapis_id))";
        $subQueryKasbon = "(SELECT COALESCE(SUM(nominal), 0) FROM kasbon_karyawan WHERE terapis_id = t.id AND status_potongan IN ('belum_lunas', 'belum_dipotong'))";
        $subQueryTunjangan = "(SELECT COALESCE(SUM(tt.nominal), 0) FROM tunjangan_terapis tt WHERE tt.terapis_id = t.id AND tt.is_active = 1 AND tt.tipe = 'bulanan')";

        $builder->select(
            't.id as terapis_id, 
        t.nama, 
        t.foto, 
        r.name as wilayah,
        j.nama_jabatan,
        COALESCE(pg.tipe_gaji, "Belum Diset") as tipe_gaji,
        COALESCE(pg.nominal_gaji, 0) as nominal_gaji,
        COALESCE(pg.potong_absen, 0) as potong_absen,
        ' . $subQueryTindakan . ' as jml_tindakan, 
        ' . $subQueryKasbon . ' as total_kasbon,
        ' . $subQueryTunjangan . ' as total_tunjangan'
        , false);

        $builder->join('gaji_karyawan pg', 'pg.terapis_id = t.id', 'left');
        $builder->join('regions r', 'r.id = t.region_id', 'left');
        $builder->join('jabatan j', 'j.id = t.jabatan_id', 'left');
        $builder->where('t.is_active', 1);

        if ($regionId !== 'all') {
            $builder->where('t.region_id', $regionId);
        }
        return $builder->get()->getResultArray();
    }

    public function getPayrollHistory($bulan, $tahun, $regionId = 'all')
    {
        $builder = $this->db->table($this->table . ' p');
        $builder->select('
            p.*, 
            t.nama, 
            r.name as wilayah
        ');
        $builder->join('terapis t', 't.id = p.terapis_id');
        $builder->join('regions r', 'r.id = t.region_id', 'left');

        $builder->where('p.periode_bulan', $bulan);
        $builder->where('p.periode_tahun', $tahun);
        $builder->where('p.status', 'lunas');

        if ($regionId !== 'all') {
            $builder->where('t.region_id', $regionId);
        }

        $builder->orderBy('p.tanggal_bayar', 'DESC');

        return $builder->get()->getResultArray();
    }

    public function getDetailPerhitungan($id)
    {
        $bulan      = date('n');
        $tahun      = date('Y');
        $bulanBagus = str_pad((string)$bulan, 2, '0', STR_PAD_LEFT);
        $tanggalAwal  = "$tahun-$bulanBagus-01";
        $tanggalAkhir = date('Y-m-t', strtotime($tanggalAwal));

        $subQueryKehadiran = "(SELECT COUNT(*) FROM absensi_karyawan WHERE terapis_id = t.id AND status IN ('Hadir','Cuti') AND tanggal >= '$tanggalAwal' AND tanggal <= '$tanggalAkhir')";
        $subQueryKasbon    = "(SELECT COALESCE(SUM(sisa_hutang), 0) FROM kasbon_karyawan WHERE terapis_id = t.id AND status_potongan = 'belum_lunas')";

        $builder = $this->db->table('terapis t');
        $builder->select(
            't.id, t.nama, t.region_id,
            COALESCE(pg.tipe_gaji, "Belum Diset") as tipe_gaji,
            COALESCE(pg.nominal_gaji, 0) as nominal_gaji,
            COALESCE(pg.potong_absen, 0) as potong_absen,
            ' . $subQueryKehadiran . ' as current_kehadiran,
            ' . $subQueryKasbon . ' as total_kasbon'
        , false);
        $builder->join('gaji_karyawan pg', 'pg.terapis_id = t.id', 'left');
        $builder->where('t.id', $id);

        $terapis = $builder->get()->getRowArray();
        if (!$terapis) return ['terapis' => null, 'komponen' => []];

        $kehadiran   = (int) $terapis['current_kehadiran'];
        $nominalGaji = (float) $terapis['nominal_gaji'];
        $tipeGaji    = $terapis['tipe_gaji'];

        // ── GAJI POKOK ──────────────────────────────────────────────
        $gajiPokok = ($tipeGaji === 'harian') ? $nominalGaji * $kehadiran : $nominalGaji;

        // Potongan absen (gaji bulanan)
        $potonganAbsen = 0;
        $hariKerja     = 0;
        if ($tipeGaji === 'bulanan' && $terapis['potong_absen'] == 1) {
            $mKalender = new \App\modules\kalender\Models\MKalender();
            $hariKerja = $mKalender->getHariKerjaBulanan($bulan, $tahun, $terapis['region_id'] ?? null);
            if ($hariKerja > 0) {
                $absen = $hariKerja - $kehadiran;
                if ($absen > 0) $potonganAbsen = ($nominalGaji / $hariKerja) * $absen;
            }
        }
        $gajiPokok -= $potonganAbsen;

        // ── JASPEL (dari jaspel_settings) ───────────────────────────
        $mJaspel = new \App\modules\jasa_pelayanan\Models\MJaspelSettings();

        // Jaspel Reguler
        $jaspelReguler    = 0;
        $settingReguler   = $mJaspel->getByRegion($terapis['region_id'], 'reguler');
        if ($settingReguler) {
            $terapisAllowed = json_decode($settingReguler->terapis_ids, true) ?? [];
            if (in_array($id, $terapisAllowed)) {
                // Hitung total pasien reguler pada hari-hari ketika terapis ini hadir
                $sql = "SELECT COALESCE(SUM(daily_patients), 0) as total_patients FROM (
                            SELECT DATE(pq.queue_date) as tanggal, COUNT(DISTINCT pq.patient_id) as daily_patients
                            FROM patient_queues pq
                            JOIN histories h ON h.patient_queue_id = pq.id
                            JOIN absensi_karyawan ak ON ak.tanggal = DATE(pq.queue_date)
                            WHERE pq.region_id = ?
                              AND h.finish_at IS NOT NULL
                              AND (h.kejantanan != 'ya' OR h.kejantanan IS NULL)
                              AND MONTH(pq.queue_date) = ?
                              AND YEAR(pq.queue_date) = ?
                              AND ak.terapis_id = ?
                              AND ak.status = 'Hadir'
                            GROUP BY DATE(pq.queue_date)
                        ) tmp";
                $query = $this->db->query($sql, [$terapis['region_id'], $bulan, $tahun, $id])->getRow();
                $pasienReguler = (int)($query->total_patients ?? 0);
                $jaspelReguler = $pasienReguler * $settingReguler->nominal_per_pasien;
            }
        }

        // Jaspel Kejantanan
        $jaspelKejantanan  = 0;
        $settingKejantanan = $mJaspel->getByRegion($terapis['region_id'], 'kejantanan');
        if ($settingKejantanan) {
            $terapisAllowed = json_decode($settingKejantanan->terapis_ids, true) ?? [];
            if (in_array($id, $terapisAllowed)) {
                // Hitung total pasien kejantanan pada hari-hari ketika terapis ini hadir
                $sql = "SELECT COALESCE(SUM(daily_patients), 0) as total_patients FROM (
                            SELECT DATE(pq.queue_date) as tanggal, COUNT(DISTINCT pq.patient_id) as daily_patients
                            FROM patient_queues pq
                            JOIN histories h ON h.patient_queue_id = pq.id
                            JOIN absensi_karyawan ak ON ak.tanggal = DATE(pq.queue_date)
                            WHERE pq.region_id = ?
                              AND h.finish_at IS NOT NULL
                              AND h.kejantanan = 'ya'
                              AND MONTH(pq.queue_date) = ?
                              AND YEAR(pq.queue_date) = ?
                              AND ak.terapis_id = ?
                              AND ak.status = 'Hadir'
                            GROUP BY DATE(pq.queue_date)
                        ) tmp";
                $query = $this->db->query($sql, [$terapis['region_id'], $bulan, $tahun, $id])->getRow();
                $pasienKejantanan = (int)($query->total_patients ?? 0);
                $jaspelKejantanan = $pasienKejantanan * $settingKejantanan->nominal_per_pasien;
            }
        }

        // ── TUNJANGAN dari master gaji ───────────────────────────────
        $mMasterGaji = new \App\modules\tunjangan_karyawan\Models\Mtunjangankaryawan();
        $settingTunjangan = $mMasterGaji->getForTerapis($id, $terapis['region_id']);

        $benefitList    = [];
        $totalBenefit   = 0;

        foreach ($settingTunjangan as $t) {
            $nominal = ($t['tipe'] === 'harian')
                ? (float)$t['nominal'] * $kehadiran
                : (float)$t['nominal'];

            $item = ['nama' => $t['nama_tunjangan'], 'nominal' => (int)$nominal, 'kategori' => $t['kategori']];
            $benefitList[]  = $item;
            $totalBenefit  += $nominal;
        }

        // ── POTONGAN RUTIN dari master gaji (kategori=potongan) ──────
        // Sudah termasuk di atas karena getForTerapis ambil semua kategori
        // Pisahkan benefit dan potongan
        $benefitOnly  = array_values(array_filter($benefitList, fn($i) => $i['kategori'] === 'tunjangan'));
        $potonganMaster = array_values(array_filter($benefitList, fn($i) => $i['kategori'] === 'potongan'));
        $totalBenefitOnly = array_sum(array_column($benefitOnly, 'nominal'));
        $totalPotonganMaster = array_sum(array_column($potonganMaster, 'nominal'));

        // ── TOTAL KASBON ─────────────────────────────────────────────
        $totalKasbon = (int)$terapis['total_kasbon'];

        // ── KALKULASI AKHIR ──────────────────────────────────────────
        $totalA      = $gajiPokok + $jaspelReguler + $jaspelKejantanan;
        $totalB      = $totalBenefitOnly;
        $totalC      = $totalPotonganMaster + $totalKasbon;
        $gajiBersih  = ($totalA + $totalB) - $totalC;

        $terapis['total_tunjangan'] = (int)$totalBenefit;

        return [
            'terapis'    => $terapis,
            'komponen'   => [
                // Take Home
                'gaji_pokok'        => (int)$gajiPokok,
                'jaspel_reguler'    => (int)$jaspelReguler,
                'jaspel_kejantanan' => (int)$jaspelKejantanan,
                'total_A'           => (int)$totalA,
                // Benefit
                'benefit_list'      => $benefitOnly,
                'total_B'           => (int)$totalBenefitOnly,
                // Potongan
                'potongan_list'     => $potonganMaster,
                'total_potongan_rutin' => (int)$totalPotonganMaster,
                'total_kasbon'      => $totalKasbon,
                'total_C'           => (int)$totalC,
                // Hasil
                'gaji_bersih'       => (int)$gajiBersih,
                'kehadiran'         => $kehadiran,
                'hari_kerja'        => $hariKerja,
            ]
        ];
    }
    public function getHistoryByTerapis($terapisId)
    {
        return $this->db->table($this->table . ' p')
            ->select('p.*, t.nama, r.name as wilayah')
            ->join('terapis t', 't.id = p.terapis_id')
            ->join('regions r', 'r.id = t.region_id', 'left')
            ->where('p.terapis_id', $terapisId)
            ->orderBy('p.tanggal_bayar', 'DESC')
            ->get()
            ->getResultArray();
    }
}

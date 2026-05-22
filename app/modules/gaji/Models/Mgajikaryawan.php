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
        $currentMonth = date('n');
        $currentYear = date('Y');

        $builder = $this->db->table('terapis t');
        // SUB QUERY UNTUK JUMLAH TINDAKAN BULAN INI (DI-RESET TIAP BULAN)
        $subQueryTindakan = "(SELECT COUNT(h.id) FROM histories h WHERE FIND_IN_SET(t.id, h.terapis_id) AND h.is_delete = 0 AND MONTH(h.date) = {$currentMonth} AND YEAR(h.date) = {$currentYear})";
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
        COALESCE(pg.nominal_potong_absen, 0) as nominal_potong_absen,
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
            t.foto,
            j.nama_jabatan,
            r.name as wilayah
        ');
        $builder->join('terapis t', 't.id = p.terapis_id');
        $builder->join('regions r', 'r.id = t.region_id', 'left');
        $builder->join('jabatan j', 'j.id = t.jabatan_id', 'left');

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
        $subQueryAbsen     = "(SELECT COUNT(*) FROM absensi_karyawan WHERE terapis_id = t.id AND status = 'Tidak Hadir' AND tanggal >= '$tanggalAwal' AND tanggal <= '$tanggalAkhir')";
        $subQueryKasbon    = "(SELECT COALESCE(SUM(sisa_hutang), 0) FROM kasbon_karyawan WHERE terapis_id = t.id AND status_potongan = 'belum_lunas')";

        $builder = $this->db->table('terapis t');
        $builder->select(
            't.id, t.nama, t.region_id,
            COALESCE(pg.tipe_gaji, "Belum Diset") as tipe_gaji,
            COALESCE(pg.nominal_gaji, 0) as nominal_gaji,
            COALESCE(pg.potong_absen, 0) as potong_absen,
            COALESCE(pg.nominal_potong_absen, 0) as nominal_potong_absen,
            ' . $subQueryKehadiran . ' as current_kehadiran,
            ' . $subQueryAbsen . ' as current_absen,
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
        $absenCount    = (int)($terapis['current_absen'] ?? 0);
        if ($tipeGaji === 'bulanan' && $terapis['potong_absen'] == 1) {
            $mKalender = new \App\modules\kalender\Models\MKalender();
            $hariKerja = $mKalender->getHariKerjaBulanan($bulan, $tahun, $terapis['region_id'] ?? null);
            if ($absenCount > 0) {
                $potonganAbsen = (float)$terapis['nominal_potong_absen'] * $absenCount;
            }
        }

        // ── JASPEL (dari jaspel_harian – pool-splitting harian) ──────
        $mJaspel = new \App\modules\jasa_pelayanan\Models\MJaspelSettings();

        // Jaspel Reguler: baca dari jaspel_harian (data yang sudah dihitung saat tampilkan)
        $jaspelReguler = 0;
        $settingReguler = $mJaspel->getByRegion($terapis['region_id'], 'reguler');
        if ($settingReguler) {
            $terapisAllowed = json_decode($settingReguler->terapis_ids, true) ?? [];
            if (in_array($id, $terapisAllowed)) {
                // Ambil hari-hari terapis ini hadir bulan ini
                $presentDates = array_column(
                    $this->db->table('absensi_karyawan')
                        ->select('tanggal')
                        ->where('terapis_id', $id)
                        ->where('status', 'Hadir')
                        ->where('MONTH(tanggal)', $bulan)
                        ->where('YEAR(tanggal)', $tahun)
                        ->get()->getResultArray(),
                    'tanggal'
                );

                if (!empty($presentDates)) {
                    // Baca jaspel_harian untuk wilayah ini tipe reguler bulan ini
                    $jaspelHarianRows = $this->db->table('jaspel_harian')
                        ->where('region_id', $terapis['region_id'])
                        ->where('tipe', 'reguler')
                        ->where('is_processed', 0)
                        ->where('MONTH(tanggal)', $bulan)
                        ->where('YEAR(tanggal)', $tahun)
                        ->get()->getResultArray();

                    // Map tanggal → jaspel_per_terapis
                    $jaspelPerHari = [];
                    foreach ($jaspelHarianRows as $jr) {
                        $jaspelPerHari[$jr['tanggal']] = (float)$jr['jaspel_per_terapis'];
                    }

                    // Akumulasi hanya untuk hari-hari terapis ini hadir
                    foreach ($presentDates as $tgl) {
                        if (isset($jaspelPerHari[$tgl])) {
                            $jaspelReguler += $jaspelPerHari[$tgl];
                        } else {
                            // Fallback: hitung langsung jika belum ada di jaspel_harian
                            $patientRow = $this->db->table('patient_queues pq')
                                ->select('COUNT(DISTINCT pq.patient_id) as cnt')
                                ->join('histories h', 'h.patient_queue_id = pq.id')
                                ->where('pq.region_id', $terapis['region_id'])
                                ->where('DATE(pq.queue_date)', $tgl)
                                ->where('h.finish_at IS NOT NULL')
                                ->groupStart()
                                    ->where('h.kejantanan !=', 'ya')
                                    ->orWhere('h.kejantanan IS NULL')
                                ->groupEnd()
                                ->get()->getRow();

                            $totalPasien = (int)($patientRow->cnt ?? 0);
                            if ($totalPasien > 0) {
                                $hadirCount = (int)$this->db->table('absensi_karyawan')
                                    ->whereIn('terapis_id', $terapisAllowed)
                                    ->where('tanggal', $tgl)
                                    ->where('status', 'Hadir')
                                    ->countAllResults();
                                if ($hadirCount > 0) {
                                    $jaspelReguler += ($totalPasien * $settingReguler->nominal_per_pasien) / $hadirCount;
                                }
                            }
                        }
                    }
                }
            }
        }

        // Jaspel Kejantanan
        $jaspelKejantanan = 0;
        $settingKejantanan = $mJaspel->getByRegion($terapis['region_id'], 'kejantanan');
        if ($settingKejantanan) {
            $terapisAllowed = json_decode($settingKejantanan->terapis_ids, true) ?? [];
            if (in_array($id, $terapisAllowed)) {
                $presentDates = array_column(
                    $this->db->table('absensi_karyawan')
                        ->select('tanggal')
                        ->where('terapis_id', $id)
                        ->where('status', 'Hadir')
                        ->where('MONTH(tanggal)', $bulan)
                        ->where('YEAR(tanggal)', $tahun)
                        ->get()->getResultArray(),
                    'tanggal'
                );

                if (!empty($presentDates)) {
                    $jaspelHarianRows = $this->db->table('jaspel_harian')
                        ->where('region_id', $terapis['region_id'])
                        ->where('tipe', 'kejantanan')
                        ->where('is_processed', 0)
                        ->where('MONTH(tanggal)', $bulan)
                        ->where('YEAR(tanggal)', $tahun)
                        ->get()->getResultArray();

                    $jaspelPerHari = [];
                    foreach ($jaspelHarianRows as $jr) {
                        $jaspelPerHari[$jr['tanggal']] = (float)$jr['jaspel_per_terapis'];
                    }

                    foreach ($presentDates as $tgl) {
                        if (isset($jaspelPerHari[$tgl])) {
                            $jaspelKejantanan += $jaspelPerHari[$tgl];
                        } else {
                            $patientRow = $this->db->table('patient_queues pq')
                                ->select('COUNT(DISTINCT pq.patient_id) as cnt')
                                ->join('histories h', 'h.patient_queue_id = pq.id')
                                ->where('pq.region_id', $terapis['region_id'])
                                ->where('DATE(pq.queue_date)', $tgl)
                                ->where('h.finish_at IS NOT NULL')
                                ->where('h.kejantanan', 'ya')
                                ->get()->getRow();

                            $totalPasien = (int)($patientRow->cnt ?? 0);
                            if ($totalPasien > 0) {
                                $hadirCount = (int)$this->db->table('absensi_karyawan')
                                    ->whereIn('terapis_id', $terapisAllowed)
                                    ->where('tanggal', $tgl)
                                    ->where('status', 'Hadir')
                                    ->countAllResults();
                                if ($hadirCount > 0) {
                                    $jaspelKejantanan += ($totalPasien * $settingKejantanan->nominal_per_pasien) / $hadirCount;
                                }
                            }
                        }
                    }
                }
            }
        }

        // ── TUNJANGAN dari master gaji ───────────────────────────────
        $mMasterGaji = new \App\modules\tunjangan_karyawan\Models\Mtunjangankaryawan();
        $settingTunjangan = $mMasterGaji->getForTerapis($id, $terapis['region_id']);

        $benefitList  = [];
        $totalBenefit = 0;

        foreach ($settingTunjangan as $t) {
            $nominal = ($t['tipe'] === 'harian')
                ? (float)$t['nominal'] * $kehadiran
                : (float)$t['nominal'];

            $benefitList[]  = ['nama' => $t['nama_tunjangan'], 'nominal' => (int)$nominal, 'kategori' => $t['kategori']];
            $totalBenefit  += $nominal;
        }

        // Pisahkan benefit dan potongan
        $benefitOnly         = array_values(array_filter($benefitList, fn($i) => $i['kategori'] === 'tunjangan'));
        $potonganMaster      = array_values(array_filter($benefitList, fn($i) => $i['kategori'] === 'potongan'));
        $benefitNonCash      = array_values(array_filter($benefitList, fn($i) => $i['kategori'] === 'benefit'));

        // Ambil potongan rutin yang aktif
        $mPotonganRutin = new \App\modules\kasbon_karyawan\Models\MPotonganRutin();
        $potonganRutinList = $mPotonganRutin->getByTerapis((int)$id);
        foreach ($potonganRutinList as $pr) {
            $potonganMaster[] = [
                'nama'     => $pr['nama_potongan'],
                'nominal'  => (int)$pr['nominal'],
                'kategori' => 'potongan'
            ];
        }

        $totalBenefitOnly    = array_sum(array_column($benefitOnly, 'nominal'));
        $totalPotonganMaster = array_sum(array_column($potonganMaster, 'nominal'));
        $totalBenefitNonCash = array_sum(array_column($benefitNonCash, 'nominal'));

        // ── TOTAL KASBON ─────────────────────────────────────────────
        $totalKasbon = (int)$terapis['total_kasbon'];

        // ── KALKULASI AKHIR ──────────────────────────────────────────
        $totalA     = $gajiPokok + $jaspelReguler + $jaspelKejantanan;
        $totalB     = $totalBenefitOnly;
        $totalC     = $totalPotonganMaster + $totalKasbon;
        $gajiBersih = ($totalA + $totalB) - ($totalC + $potonganAbsen);

        // total_tunjangan = tunjangan tetap + jaspel (untuk tampilan ringkasan)
        $terapis['total_tunjangan'] = (int)($totalBenefitOnly + $jaspelReguler + $jaspelKejantanan);

        return [
            'terapis'  => $terapis,
            'komponen' => [
                // Take Home
                'gaji_pokok'           => (int)$gajiPokok,
                'jaspel_reguler'       => (int)$jaspelReguler,
                'jaspel_kejantanan'    => (int)$jaspelKejantanan,
                'total_A'              => (int)$totalA,
                // Benefit
                'benefit_list'         => $benefitOnly,
                'total_B'              => (int)$totalBenefitOnly,
                // Benefit Non-Cash
                'benefit_non_cash_list'  => $benefitNonCash,
                'total_benefit_non_cash' => (int)$totalBenefitNonCash,
                // Potongan
                'potongan_list'        => $potonganMaster,
                'total_potongan_rutin' => (int)$totalPotonganMaster,
                'total_kasbon'         => $totalKasbon,
                'total_C'              => (int)$totalC,
                // Hasil
                'gaji_bersih'          => (int)$gajiBersih,
                'kehadiran'            => $kehadiran,
                'hari_kerja'           => $hariKerja,
                'absen'                => $absenCount,
                'potongan_absen'       => (int)$potonganAbsen,
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

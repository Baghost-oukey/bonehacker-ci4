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
        'status'
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
        $subQueryTunjangan = "(SELECT COALESCE(SUM(nominal), 0) FROM transaksi_tunjangan WHERE terapis_id = t.id AND status_pembayaran = 'Belum Dibayar')";

        $builder->select(
            't.id as terapis_id, 
        t.nama, 
        t.foto, 
        r.name as wilayah,
        j.nama_jabatan,
        COALESCE(pg.tipe_gaji, "Belum Diset") as tipe_gaji,
        COALESCE(pg.nominal_gaji, 0) as nominal_gaji,
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
        $bulan = date('n');
        $tahun = date('Y');
        $bulanBagus = str_pad((string)$bulan, 2, '0', STR_PAD_LEFT);
        $tanggalAwal = "$tahun-$bulanBagus-01";
        $tanggalAkhir = date('Y-m-t', strtotime($tanggalAwal));

        $subQueryTindakan = "(SELECT COUNT(h.id) FROM histories h WHERE FIND_IN_SET(t.id, h.terapis_id))";
        $subQueryKasbon = "(SELECT COALESCE(SUM(nominal), 0) FROM kasbon_karyawan WHERE terapis_id = t.id AND status_potongan IN ('belum_lunas', 'belum_dipotong'))";
        $subQueryTunjangan = "(SELECT COALESCE(SUM(nominal), 0) FROM transaksi_tunjangan WHERE terapis_id = t.id AND status_pembayaran = 'Belum Dibayar')";
        $subQueryKehadiran = "(SELECT COUNT(*) FROM absensi_karyawan WHERE terapis_id = t.id AND status = 'Hadir' AND tanggal >= '$tanggalAwal' AND tanggal <= '$tanggalAkhir')";

        $builder = $this->db->table('terapis t');
        $builder->select(
            't.id, 
            t.nama, 
            COALESCE(pg.tipe_gaji, "Belum Diset") as tipe_gaji, 
            COALESCE(pg.nominal_gaji, 0) as nominal_gaji,
            ' . $subQueryTindakan . ' as jml_tindakan,
            ' . $subQueryKasbon . ' as total_kasbon,
            ' . $subQueryTunjangan . ' as total_tunjangan,
            ' . $subQueryKehadiran . ' as current_kehadiran'
        , false);
        $builder->join('gaji_karyawan pg', 'pg.terapis_id = t.id', 'left');

        // Ambil data spesifik 1 orang yang diklik
        $builder->where('t.id', $id);

        $terapis = $builder->get()->getRowArray();

        return [
            'terapis'   => $terapis
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

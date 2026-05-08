<?php

namespace App\modules\transaksi_tunjangan\Models;

use CodeIgniter\Model;

class Mtransaksitunjangan extends Model
{
    protected $table            = 'transaksi_tunjangan';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'terapis_id',
        'tunjangan_karyawan_id',
        'tanggal',
        'nominal',
        'keterangan',
        'status_pembayaran'
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

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

    public function get_datatables_terapis($search, $start, $length)
    {
        $builder = $this->db->table('terapis t');
        $builder->select('t.id, t.nama, j.nama_jabatan, g.nominal_gaji');
        $builder->join('jabatan j', 'j.id = t.jabatan_id', 'left');
        $builder->join('gaji_karyawan g', 'g.terapis_id = t.id', 'left');

        if ($search) {
            $builder->groupStart()
                ->like('t.nama', $search)
                ->orLike('j.nama_jabatan', $search)
                ->groupEnd();
        }

        $builder->limit($length, $start);
        return $builder->get()->getResultArray();
    }

    public function count_all_terapis()
    {
        return $this->db->table('terapis')->countAllResults();
    }

    public function count_filtered_terapis($search)
    {
        $builder = $this->db->table('terapis t');
        $builder->join('jabatan j', 'j.id = t.jabatan_id', 'left');

        if ($search) {
            $builder->groupStart()
                ->like('t.nama', $search)
                ->orLike('j.nama_jabatan', $search)
                ->groupEnd();
        }

        return $builder->countAllResults();
    }

    public function getTotalBelumCair(int $terapisId)
    {
        $result = $this->db->table($this->table)
            ->selectSum('nominal', 'total')
            ->where('terapis_id', $terapisId)
            ->where('status_pembayaran', 'Belum Dibayar')
            ->get()
            ->getRowArray();

        return (int)($result['total'] ?? 0);
    }

    public function getDetailTerapis($id)
    {
        return $this->db->table('terapis t')
            ->select('t.*, j.nama_jabatan, g.nominal_gaji')
            ->join('jabatan j', 'j.id = t.jabatan_id', 'left')
            ->join('gaji_karyawan g', 'g.terapis_id = t.id', 'left')
            ->where('t.id', $id)
            ->get()
            ->getRowArray();
    }

    public function getRiwayatTunjangan($terapisId)
    {
        return $this->db->table('transaksi_tunjangan tt')
            ->select('tt.*, tk.nama_tunjangan, tk.kategori')
            ->join('tunjangan_karyawan tk', 'tk.id = tt.tunjangan_karyawan_id')
            ->where('tt.terapis_id', $terapisId)
            ->orderBy('tt.tanggal', 'DESC')
            ->get()
            ->getResultArray();
    }

    public function markAsCair(int $terapisId)
    {
        return $this->db->table($this->table)
            ->where('terapis_id', $terapisId)
            ->where('status_pembayaran', 'Belum Dibayar')
            ->update(['status_pembayaran' => 'Sudah Cair']);
    }
}

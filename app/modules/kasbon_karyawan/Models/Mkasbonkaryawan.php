<?php

namespace App\modules\kasbon_karyawan\Models;

use CodeIgniter\Model;

class Mkasbonkaryawan extends Model
{
    protected $table            = 'kasbon_karyawan';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'terapis_id',
        'tanggal',
        'nominal',
        'sisa_hutang',
        'keterangan',
        'status_potongan',
        // 'created_at',
        // 'updated_at'
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


    public function getTotalHutangAktif($terapisId)
    {
        $result = $this->db->table('kasbon_karyawan')
            ->selectSum('sisa_hutang', 'total_sisa')
            ->where('terapis_id', $terapisId)
            ->where('status_potongan', 'belum_lunas')
            ->get()
            ->getRowArray();

        // Kembalikan angkanya, jika kosong maka 0
        return $result['total_sisa'] ?? 0;  
    }

    public function get_datatables($search = null, $start = 0, $length = 10)
    {
        $builder = $this->db->table('terapis t');
        $builder->select('t.id, t.nama, j.nama_jabatan, gk.nominal_gaji');
        $builder->join('jabatan j', 'j.id = t.jabatan_id', 'left');
        $builder->join('gaji_karyawan gk', 'gk.terapis_id = t.id', 'left');
        $builder->where('t.is_active', 1);

        if ($search) {
            $builder->groupStart()
                ->like('t.nama', $search)
                ->orLike('j.nama_jabatan', $search)
                ->groupEnd();
        }

        return $builder->limit($length, $start)->get()->getResultArray();
    }

    public function count_all_active()
    {
        return $this->db->table('terapis')->where('is_active', 1)->countAllResults();
    }

    public function count_filtered($search = null)
    {
        $builder = $this->db->table('terapis t');
        $builder->join('jabatan j', 'j.id = t.jabatan_id', 'left');
        $builder->where('t.is_active', 1);

        if ($search) {
            $builder->like('t.nama', $search)
                ->orLike('j.nama_jabatan', $search);
        }

        return $builder->countAllResults();
    }

    /**
     * Mengambil data profil lengkap satu karyawan untuk halaman detail
     */
    public function getDetailKaryawan($id)
    {
        $builder = $this->db->table('terapis t');
        $builder->select('
            t.id, 
            t.nama, 
            j.nama_jabatan, 
            gk.nominal_gaji as gaji_pokok
        ');
        $builder->join('jabatan j', 'j.id = t.jabatan_id', 'left');
        $builder->join('gaji_karyawan gk', 'gk.terapis_id = t.id', 'left');
        $builder->where('t.id', $id);

        $data = $builder->get()->getRowArray();

        if ($data) {
            $data['total_hutang_aktif'] = $this->getTotalHutangAktif($id);
        }

        return $data;
    }

    public function getHistoryByTerapis($terapisId)
    {
        return $this->db->table($this->table)
            ->where('terapis_id', $terapisId)
            ->orderBy('tanggal', 'DESC')
            ->orderBy('id', 'DESC') // Penanda urutan jika di hari yang sama ada 2 kasbon
            ->get()->getResultArray();
    }
}

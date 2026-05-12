<?php

namespace App\modules\terapis\Models;

use CodeIgniter\Model;

class MTerapis extends Model
{
    protected $table            = 'terapis';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'terapis_id',
        'nama',
        'alamat',
        'tempat_lahir',  
        'tanggal_lahir', 
        'rank',
        'is_active',
        'is_presensi',
        'region_id',
        'jabatan_id',
        'foto',
        'tgl_mulai_kerja',
        'jatah_cuti',
        'keterangan'
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = false;
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

    public function getTerapis($region = null, $allowed_regions = null)
    {
        $builder = $this->db->table($this->table . ' t');

        $subQuery = "(SELECT COUNT(h.id) FROM histories h WHERE FIND_IN_SET(t.id, h.terapis_id))";
        $builder->select("t.id, t.terapis_id, t.nama, t.alamat, t.is_active, r.name as region_name, $subQuery as jml_tindakan", false);
        $builder->join('regions r', 'r.id = t.region_id', 'left');

        if (!empty($region)) {
            $builder->where('t.region_id', $region);
        }

        if (!empty($allowed_regions)) {
            $builder->whereIn('t.region_id', $allowed_regions);
        }

        return $builder;
    }

    public function getById($id)
    {
        return $this->where('id', $id)->first();
    }

    public function detail($user_id)
    {
        return $this->where('terapis_id', $user_id)->first();
    }

    public function store($data)
    {
        $this->insert($data);
        return $this->db->insertID();
    }

    public function edit($data, $where)
    {
        return $this->update($where, $data);
    }

    public function destroy($id)
    {
        return $this->delete($id);
    }

    public function get_regions($allowed_regions = null)
    {
        $builder = $this->db->table('regions')->select('id, name')->where('is_active', 1);
        if (!empty($allowed_regions)) {
            $builder->whereIn('id', $allowed_regions);
        }
        return $builder->get()->getResult();
    }

    public function get_jabatan()
    {
        return $this->db->table('jabatan')->get()->getResult();
    }

    public function getJabatanById($id)
    {
        return $this->db->table($this->table . ' t')
            ->select('t.id, t.terapis_id, t.jabatan_id, j.nama_jabatan')
            ->join('jabatan j', 't.jabatan_id = j.id', 'left')
            ->where('t.terapis_id', $id)
            ->get()->getRow();
    }

    public function getRegionById($id)
    {
        return $this->db->table($this->table . ' t')
            ->select('t.id, t.terapis_id, t.region_id, r.name')
            ->join('regions r', 't.region_id = r.id', 'left')
            ->where('t.terapis_id', $id)
            ->get()->getRow();
    }

    public function get_all_terapis()
    {
        return $this->where('is_active', true)
            ->orderBy('nama', 'ASC')
            ->findAll();
    }

    public function isActive($where, $status)
    {
        return $this->update($where, ['is_active' => $status]);
    }

    public function hapusfoto($id, $data)
    {
        return $this->update($id, $data);
    }
}

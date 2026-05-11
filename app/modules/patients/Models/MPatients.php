<?php

namespace App\modules\patients\Models;

use CodeIgniter\Model;

class MPatients extends Model
{
    protected $table            = 'patients';
    protected $table2           = 'resources';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'name',
        'gender',
        'age',
        'country_id',
        'address',
        'phone',
        'region_id',
        'is_suspective',
        'domestic',
        'url',
        'created_at',
        'created_by',
        'updated_at',
        'updated_by',
        'patient_information',
        'ket_suspect',
        'is_delete'
    ];

    protected bool $allowEmptyInserts = true;
    // protected bool $allowEmptyInserts = false;
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
    // protected $beforeInsert   = ['fixPhonevalue'];
    protected $afterInsert    = [];
    // protected $beforeUpdate   = ['fixPhonevalue'];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    private function _buildQuery()
    {
        return $this->db->table($this->table . ' p')
            ->select("p.id, p.name, p.gender, p.age, p.address, p.phone, p.region_id, 
                      p.is_suspective, p.is_delete, r.name as region_name,
                      IF(p.gender = 'Man', 'Laki-laki', 'Perempuan') AS jnsKelamin,
                      MAX(h.date) as last_history_date")
            ->join('histories h', 'h.patient_id = p.id', 'left')
            ->join('regions r', 'r.id = p.region_id', 'left')
            ->where('p.is_delete', 0);
    }

    public function get_resources(array $colom = null)
    {
        $builder = $this->db->table($this->table2);

        if (isset($colom)) {
            $builder->select($colom);
        }

        return $builder->get()->getResult();
    }

    public function getListData(array $options)
    {
        $builder = $this->_buildQuery();

        if (!empty($options['search'])) {
            $builder->groupStart()
                ->like('p.name', $options['search'])
                ->orLike('p.phone', $options['search'])
                ->groupEnd();
        }

        return $builder->groupBy('p.id')
            ->orderBy($options['order'], $options['mode'])
            ->limit($options['limit'], $options['offset'])
            ->get()
            ->getResult();
    }

    public function getAllData($region = null, ?int $limit = null, int $offset = 0, $start_date = null, $end_date = null)
    {
        $builder = $this->builder();
        $builder->select('
      patients.id, 
        patients.name, 
        patients.gender, 
        patients.age, 
        patients.address, 
        patients.phone, 
        patients.is_suspective,
        r.name AS name_region,
        a.desa_nama, 
        a.kecamatan_nama, 
        a.kabupaten_nama,
        patients.is_delete,
        COUNT(h.id) AS total_history, 
        MAX(h.date) AS last_visit
    ');

        // Join tabel regions dan patient_address
        $builder->join('regions r', 'r.id = patients.region_id', 'left');
        $builder->join('patient_address a', 'a.patient_id = patients.id', 'left');
        $builder->join('histories h', 'h.patient_id = patients.id AND h.is_delete = 0', 'left');

        // Filter berdasarkan region jika ada
        if (!empty($region) && $region !== 'all') {
            if (is_array($region)) {
                $builder->whereIn('patients.region_id', $region);
            } else {
                $builder->where('patients.region_id', $region);
            }
        }
   if ($start_date && $end_date) {
        $builder->groupStart()
            ->where('DATE(h.date) >=', $start_date)
            ->where('DATE(h.date) <=', $end_date)
            ->groupEnd();
    }

        // $builder->groupBy('patients.id');
        $builder->orderBy('patients.created_at', 'DESC');
        $builder->groupBy(
            [
                'patients.id',
                'patients.name',
                'patients.gender',
                'patients.age',
                'patients.address',
                'patients.phone',
                'patients.is_suspective',
                'patients.is_delete',
                'r.name',
                'a.desa_nama',
                'a.kecamatan_nama',
                'a.kabupaten_nama'
            ]
        );

        // Limit Data
        if ($limit !== null && $limit !== -1) {
            return $builder->get($limit, $offset);
        }

        // return $builder->get($limit, $offset)->getResult();
        return $builder->get();
    }

    public function getTotalData(string $search = '')
    {
        $builder = $this->db->table($this->table);
        $builder->where('is_delete', 0);

        if ($search !== '') {
            $builder->like('name', $search);
        }

        return $builder->countAllResults();
    }

    public function show(int $id)
    {
        return $this->db->table($this->table . ' p')
            ->select('p.*, pa.id as id_address, pa.desa_nama, pa.kecamatan_nama, 
                      cr.realname as createdby, up.realname as updatedby')
            ->join('patient_address pa', 'pa.patient_id = p.id', 'left')
            ->join('users cr', 'cr.id = p.created_by', 'left')
            ->join('users up', 'up.id = p.updated_by', 'left')
            ->where('p.id', $id)
            ->get()
            ->getRowArray();
    }

    public function updateAddress(int $patientId, array $data)
    {
        return $this->db->table('patient_address')
            ->where('patient_id', $patientId)
            ->update($data);
    }

    public function destroy(int $id)
    {
        return $this->update($id, ['is_delete' => 1]);
    }

    public function get_by_phone($phone08, $phone628)
    {
        return $this->select('patients.*, m_address.desa_nama, m_address.kecamatan_nama, m_address.kabupaten_nama')
            ->join('m_address', 'm_address.patient_id = patients.id', 'left') // Sesuaikan nama tabel & PK/FK
            ->groupStart()
            ->where('phone', $phone08)
            ->orWhere('phone', $phone628)
            ->groupEnd()
            ->findAll();
    }

    public function getById($id)
    {
        return $this->where('id', $id)->first();
    }

    public function fixPhonevalue(array $data)
    {
        if (!isset($data['data'])) {

            if (!isset($data['data']['phone']) || empty($data['data']['phone'])) {
                $data['data']['phone'] = '-';
            }
        }
        return $data;
    }
}

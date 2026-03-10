<?php

namespace App\modules\address\Models;

use CodeIgniter\Model;

class MAddress extends Model
{
    protected $table            = 'patient_address';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'patient_id',
        'desa_id',
        'desa_nama',
        'kecamatan_id',
        'kecamatan_nama',
        'kabupaten_id',
        'kabupaten_nama',
        'provinsi_id',
        'provinsi_nama',
        'date_created',
        'date_updated'
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

    public function store($data)
    {
        return $this->insert($data);
    }

    public function getByPatientId($patient_id)
    {
        return $this->where('patient_id', $patient_id)->first();
    }

    public function updatePatientById($data, $patient_id)
    {
        return $this->where('patient_id', $patient_id)->set($data)->update();
    }

    public function destroyByPatientId($patient_id)
    {
        return $this->where('patient_id', $patient_id)->delete();
    }

    public function getAll()
    {
        return $this->select('patient_address.*, patients.name as patient_name')
            ->join('patients', 'patients.id = patient_address.patient_id', 'left')
            ->findAll();
    }

    public function getWilayahDataByDesaId($desa_id)
    {
        $builder = $this->db->table('desa');
        $builder->select('
            desa.nama as desa_nama, 
            kecamatan.id as kecamatan_id, 
            kecamatan.nama as kecamatan_nama, 
            kabupaten.id as kabupaten_id, 
            kabupaten.nama as kabupaten_nama, 
            provinsi.id as provinsi_id, 
            provinsi.nama as provinsi_nama
        ');
        $builder->join('kecamatan', 'desa.kecamatan_id = kecamatan.id');
        $builder->join('kabupaten', 'kecamatan.kabupaten_id = kabupaten.id');
        $builder->join('provinsi', 'kabupaten.provinsi_id = provinsi.id');
        $builder->where('desa.id', $desa_id);

        $result = $builder->get()->getRowArray();

        return $result ?: null;
    }
}

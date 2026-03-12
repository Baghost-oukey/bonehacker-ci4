<?php

namespace App\modules\journal\Models;

use CodeIgniter\Model;

class MJournal extends Model
{
    protected $table            = 'patients';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [];

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

    public function get_query_for_Journal($region = null, $start_date = null, $end_date = null, $export = null)
    {


        // $subQuery = $this->db->table('histories h2')
        //     ->select('h2.id as history_id, GROUP_CONCAT(DISTINCT rt.name SEPARATOR ", ") as result_names')
        //     ->join('result_tags rt', 'FIND_IN_SET(rt.id, h2.results)', 'left')
        //     ->groupBy('h2.id')
        //     ->getCompiledSelect();


        $builder = $this->db->table('patients p');
        $builder->select('
          p.id as patient_id, 
        p.name as nama, 
        p.address as alamat, 
        pa.desa_nama, 
        pa.kecamatan_nama, 
        pa.kabupaten_nama, 
        p.phone as nowa, 
        r.name as name_region, 
        h.date as tanggal, 
        h.results, 
        h.measure AS measures,
       '-' as result_names

        
        ');

        $builder->join('patient_address pa', 'pa.patient_id = p.id', 'left');
        $builder->join('regions r', 'r.id = p.region_id', 'left');
        $builder->join('histories h', 'h.patient_id = p.id', 'inner');
        // $builder->join("($subQuery) sq", 'sq.history_id = h.id', 'left');

        $builder->where('p.is_delete', false);
        $builder->where('h.is_delete', false);

        if (!empty($region)) {
            $builder->where('h.history_region', $region);
        }

        if (!empty($start_date) && !empty($end_date)) {
            $builder->where("h.date >=", $start_date . " 00:00:00")
                ->where("h.date <=", $end_date . " 23:59:59");
        }


        $builder->orderBy('h.date', 'DESC');


        if ($export) {
            // export semua data
            return $builder->get()->getResult();
        } else {
            return $builder;
        }
    }




    public function getPatients($patient_id)
    {
        $builder = $this->db->table('histories');
        $count = $builder->where('patient_id', $patient_id)->countAllResults();

        return ($count > 1) ? 'Pasien Lama' : 'Pasien Baru';
    }
}

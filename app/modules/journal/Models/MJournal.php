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
        $builder->select([
            'p.id as patient_id',
            'p.name as nama',
            'p.phone as nowa',
            'h.id as history_id',
            'h.date as tanggal',
            'h.measure AS measures',
            "'-' as result_names",
           
            // '(SELECT IF(COUNT(h2.id) > 1, "Pasien Lama", "Pasien Baru") FROM histories h2 WHERE h2.patient_id = p.id AND h2.is_delete = 0) as status',
         '(SELECT IF(COUNT(id) > 1, "Pasien Lama", "Pasien Baru") FROM histories WHERE patient_id = p.id AND is_delete = 0) as status',
            'CONCAT_WS(", ", p.address, pa.desa_nama, pa.kecamatan_nama, pa.kabupaten_nama) as alamat'
        ]);

        $builder->join('patient_address pa', 'pa.patient_id = p.id', 'left');
        $builder->join('regions r', 'r.id = p.region_id', 'left');
        $builder->join('histories h', 'h.patient_id = p.id', 'inner');
        // $builder->join("($subQuery) sq", 'sq.history_id = h.id', 'left');
        // $builder->join('histories h_all', 'h_all.patient_id = p.id AND h_all.is_delete = 0', 'left');

        // $builder->where('p.is_delete', false);
        // $builder->where('h.is_delete', false);
        $builder->where('p.is_delete', 0);
        $builder->where('h.is_delete', 0);

        if (!empty($region)) {
            $builder->where('h.history_region', $region);
        }

        if (!empty($start_date) && !empty($end_date)) {
            $builder->where("h.date >=", $start_date . " 00:00:00")
                ->where("h.date <=", $end_date . " 23:59:59");
        }

        // $builder->groupBy([
        //     'h.id',
        //     'p.id',
        //     'p.name',
        //     'p.phone',
        //     'h.date',
        //     'h.measure',
        //     'p.address',
        //     'pa.desa_nama',
        //     'pa.kecamatan_nama',
        //     'pa.kabupaten_nama'
        // ]);

       $builder->groupBy(['h.id', 'p.id', 'pa.id']);
        $builder->orderBy('h.date', 'DESC');


        // if ($export) {
        //     // export semua data
        //     return $builder->get()->getResult();
        // } else {
        //     return $builder;
        // }
        return $export ? $builder->get()->getResult() : $builder;
    }

    public function getTotal()
    {
        return $this->db->table('histories')->where('is_delete', 0)->countAllResults();
    }

    public function getTotalFiltered($region = null, $start_date = null, $end_date = null)
    {
        $builder = $this->db->table('histories h');
        $builder->join('patients p', 'p.id = h.patient_id');
        $builder->where('h.is_delete', 0);
        $builder->where('p.is_delete', 0);

        if (!empty($region)) {
            $builder->where('h.history_region', $region);
        }

        if (!empty($start_date) && !empty($end_date)) {
            $builder->where("h.date >=", $start_date . " 00:00:00")
                ->where("h.date <=", $end_date . " 23:59:59");
        }

        return $builder->countAllResults();
    }




    public function getPatients($patient_id)
    {
        $builder = $this->db->table('histories');
        $count = $builder->where('patient_id', $patient_id)->countAllResults();

        return ($count > 1) ? 'Pasien Lama' : 'Pasien Baru';
    }
}

<?php

namespace App\modules\statistikrecource\Models;

use CodeIgniter\Model;
use PhpOffice\PhpSpreadsheet\Calculation\FunctionArray;

class MstatistikRecource extends Model
{
    protected $table            = 'resources';
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

    public function get_sumber_marketing($startDate, $endDate, $regionID = null)
    {
        $db = \Config\Database::connect();
        $builder = $db->table('resources res');
        $builder->select('res.nama as saluran, COUNT(p.id) as total_pasien');
        $startrealDate = $db->escape($startDate . ' 00:00:00');
        $endrealDate   = $db->escape($endDate . ' 23:59:59');

        $joinCondition = "p.patient_information = res.id " .
            "AND p.created_at >= " . $startrealDate . " " .
            "AND p.created_at <= " . $endrealDate . " " .
            "AND p.is_delete = 0";

        if (!empty($regionID)) {
            $joinCondition .= " AND p.region_id = " . $db->escape($regionID);
        }

        $builder->join('patients p', $joinCondition, 'left');

        $builder->groupBy('res.id');
        $builder->orderBy('total_pasien', 'DESC');

        return $builder->get()->getResult();
    }
}

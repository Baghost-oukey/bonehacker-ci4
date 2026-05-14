<?php

namespace App\Modules\Karyawan\Models;

use CodeIgniter\Model;

class MKaryawan extends Model
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

    public function getListData($options = [])
    {
        $search = $options['search'] ?? '';
        $limit = $options['limit'] ?? 25;
        $offset = $options['offset'] ?? 0;
        $regionFilter = $options['region_filter'] ?? null;

        // Build region filter condition for terapis
        $regionCondition = '';
        if ($regionFilter) {
            if (is_array($regionFilter)) {
                $ids = implode(',', array_map('intval', $regionFilter));
                $regionCondition = " AND t.region_id IN ($ids)";
            } else {
                $regionCondition = " AND t.region_id = " . intval($regionFilter);
            }
        }

        $sql = "
        SELECT * FROM (
            SELECT 
                u.id as user_id, 
                u.realname as name, 
                u.username, 
                u.role, 
                u.regions_patient, 
                u.terapis_id, 
                t.id as id_terapis_table,
                t.region_id as terapis_region_id,
                'Management' as personnel_type,
                u.is_active
            FROM users u 
            LEFT JOIN terapis t ON u.terapis_id = t.terapis_id 
            WHERE (u.terapis_id IS NULL OR u.terapis_id = '')
            " . ($regionFilter ? " AND u.role != 'superadmin'" : "") . "
            
            UNION ALL
            
            SELECT 
                u.id as user_id, 
                t.nama as name, 
                u.username, 
                u.role, 
                u.regions_patient, 
                t.terapis_id, 
                t.id as id_terapis_table,
                t.region_id as terapis_region_id,
                'Therapist' as personnel_type,
                t.is_active
            FROM terapis t 
            LEFT JOIN users u ON t.terapis_id = u.terapis_id
            WHERE 1=1 $regionCondition
        ) AS personnel
        WHERE 1=1
        ";

        if (!empty($search)) {
            $sql .= " AND (name LIKE '%" . $this->db->escapeLikeString($search) . "%' OR username LIKE '%" . $this->db->escapeLikeString($search) . "%' OR terapis_id LIKE '%" . $this->db->escapeLikeString($search) . "%')";
        }

        $sql .= " ORDER BY is_active DESC, name ASC LIMIT $limit OFFSET $offset";

        return $this->db->query($sql)->getResult();
    }

    public function getTotalData($options)
    {
        $search = $options['search'] ?? '';
        $regionFilter = $options['region_filter'] ?? null;

        $regionCondition = '';
        if ($regionFilter) {
            if (is_array($regionFilter)) {
                $ids = implode(',', array_map('intval', $regionFilter));
                $regionCondition = " AND t.region_id IN ($ids)";
            } else {
                $regionCondition = " AND t.region_id = " . intval($regionFilter);
            }
        }

        $sql = "
        SELECT COUNT(*) as total FROM (
            SELECT u.id FROM users u WHERE (u.terapis_id IS NULL OR u.terapis_id = '')" . ($regionFilter ? " AND u.role != 'superadmin'" : "") . "
            UNION ALL
            SELECT t.id FROM terapis t WHERE 1=1 $regionCondition
        ) AS personnel
        ";

        if (!empty($search)) {
            return $this->getTotalFiltered($search, $regionFilter);
        }

        return $this->db->query($sql)->getRow()->total;
    }

    private function getTotalFiltered($search, $regionFilter = null)
    {
        $regionCondition = '';
        if ($regionFilter) {
            if (is_array($regionFilter)) {
                $ids = implode(',', array_map('intval', $regionFilter));
                $regionCondition = " AND t.region_id IN ($ids)";
            } else {
                $regionCondition = " AND t.region_id = " . intval($regionFilter);
            }
        }

        $sql = "
        SELECT COUNT(*) as total FROM (
            SELECT u.realname as name, u.username, u.terapis_id FROM users u WHERE (u.terapis_id IS NULL OR u.terapis_id = '')" . ($regionFilter ? " AND u.role != 'superadmin'" : "") . "
            UNION ALL
            SELECT t.nama as name, u.username, t.terapis_id FROM terapis t LEFT JOIN users u ON t.terapis_id = u.terapis_id WHERE 1=1 $regionCondition
        ) AS personnel
        WHERE (name LIKE '%" . $this->db->escapeLikeString($search) . "%' OR username LIKE '%" . $this->db->escapeLikeString($search) . "%' OR terapis_id LIKE '%" . $this->db->escapeLikeString($search) . "%')
        ";
        return $this->db->query($sql)->getRow()->total;
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

    public function get_region_names($region_ids)
    {
        if (empty($region_ids) || !is_array($region_ids)) {
            return [];
        }

        $builder = $this->db->table('regions');
        $builder->select('name');
        $builder->whereIn('id', $region_ids);
        $query = $builder->get()->getResultArray();
        return array_column($query, 'name');
    }

    public function get_patients_by_user_region($user_id, $export = false)
    {
        $user = $this->db->table('users')->where('id', $user_id)->get()->getRow();
        if (!$user) {
            return $export ? [] : $this->db->table('patients');
        }

        $region_ids = json_decode($user->regions_patient, true) ?: [];

        $builder = $this->db->table('patients p');
        $builder->select('p.id, p.name as nama, p.gender, p.age, p.address, r.name as wilayah');
        $builder->join('regions r', 'p.region_id = r.id', 'left');
        $builder->where('p.is_delete', 0);

        if ($user->role !== 'superadmin') {
            if (empty($region_ids)) {
                $builder->where('1=0');
            } else {
                $builder->whereIn('p.region_id', $region_ids);
            }
        }

        return $export ? $builder->get()->getResult() : $builder;
    }

    public function get_other_patients($user_id, $export = false)
    {
        $user = $this->db->table('users')->where('id', $user_id)->get()->getRow();
        if (!$user) {
            return $export ? [] : $this->db->table('patients');
        }

        $other_patients_ids = json_decode($user->other_patient, true) ?: [];

        $builder = $this->db->table('patients p');
        $builder->select('p.id, p.name as nama, p.gender, p.age, p.address, r.name as wilayah');
        $builder->join('regions r', 'p.region_id = r.id', 'left');
        $builder->where('p.is_delete', 0);

        if (empty($other_patients_ids)) {
            $builder->where('1=0');
        } else {
            $builder->whereIn('p.id', $other_patients_ids);
        }

        return $export ? $builder->get()->getResult() : $builder;
    }

    public function search_outside_patients($user_id, $search_term, $limit = 20)
    {
        $user = $this->db->table('users')->where('id', $user_id)->get()->getRow();
        $region_ids = json_decode($user->regions_patient, true) ?: [];
        $other_patient_ids = json_decode($user->other_patient, true) ?: [];

        $builder = $this->db->table('patients p');
        $builder->select('p.id, p.name as nama, p.gender, p.age, p.address, r.name as wilayah');
        $builder->join('regions r', 'p.region_id = r.id', 'left');
        $builder->where('p.is_delete', 0);

        if (!empty($region_ids)) {
            $builder->whereNotIn('p.region_id', $region_ids);
        }

        if (!empty($other_patient_ids)) {
            $builder->whereNotIn('p.id', $other_patient_ids);
        }

        if ($search_term) {
            $builder->groupStart()->like('p.name', $search_term)->orLike('p.address', $search_term)->orLike('r.name', $search_term)->groupEnd();
        }

        return $builder->limit($limit)->get()->getResult();
    }

    public function append_patient_to_user($user_id, $patient_id)
    {
        $sql = "UPDATE users SET other_patient = JSON_ARRAY_APPEND(IFNULL(other_patient, '[]'), '$', ?) WHERE id = ?";
        return $this->db->query($sql, [(int) $patient_id, (int) $user_id]);
    }

    public function username_exists_edit($username, $user_id)
    {
        return $this->db->table('users')->where('username', $username)->where('id !=', $user_id)->countAllResults() > 0;
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

    public function isActive($where, $status)
    {
        return $this->update($where, ['is_active' => $status]);
    }

    public function hapusfoto($id, $data)
    {
        return $this->update($id, $data);
    }
}

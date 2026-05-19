<?php

namespace App\modules\karyawan\Models;

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
        'tempat_lahir',
        'tanggal_lahir',
        'alamat',
        'region_id',
        'jabatan_id',
        'rank',
        'tgl_mulai_kerja',
        'foto',
        'keterangan',
        'is_active',
        'is_presensi'
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

    public function getById($id)
    {
        return $this->where('id', $id)->first();
    }

    public function detail($terapis_id)
    {
        return $this->where('terapis_id', $terapis_id)->first();
    }

    public function edit($data, $where)
    {
        return $this->update($where, $data);
    }

    public function destroy($id)
    {
        return $this->delete($id);
    }

    public function getListData($options = [])
    {
        $limit = $options['limit'] ?? 25;
        $offset = $options['offset'] ?? 0;
        $search = $options['search'] ?? '';
        $regionFilter = $options['region_filter'] ?? null;

        $regionIds = $this->normalizeRegionFilter($regionFilter);
        $jsonRegionCond = $this->buildJsonRegionCondition($regionIds);
        $terapisRegionIn = empty($regionIds) ? '' : ' AND t.region_id IN (' . implode(',', $regionIds) . ')';

        // Pre-aggregate tindakan counts per terapis — avoids expensive per-row correlated subquery
        $tindakanJoin = "LEFT JOIN (
            SELECT SUBSTRING_INDEX(SUBSTRING_INDEX(h.terapis_id, ',', numbers.n), ',', -1) AS tid,
                   COUNT(*) AS cnt
            FROM histories h
            INNER JOIN (
                SELECT 1 n UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5
            ) numbers ON CHAR_LENGTH(h.terapis_id) - CHAR_LENGTH(REPLACE(h.terapis_id, ',', '')) >= numbers.n - 1
            GROUP BY tid
        ) ht ON ht.tid = t.id";

        $sql = "
        SELECT * FROM (
            -- 1. Users (Management, Admin, atau Terapis yang sudah punya akun)
            SELECT 
                u.id as user_id, 
                COALESCE(t.nama, u.realname) as name, 
                u.username, 
                u.role, 
                u.regions_patient, 
                u.terapis_id, 

                t.id as id_terapis_table,
                t.region_id as terapis_region_id,
                rt.name as terapis_region_name,
                j.nama_jabatan as terapis_jabatan_name,
                CASE 
                    WHEN u.role = 'user' THEN 'Therapist' 
                    ELSE 'Management' 
                END as personnel_type,
                u.is_active,
                COALESCE(ht.cnt, 0) as jml_tindakan
            FROM users u 
            LEFT JOIN terapis t ON u.terapis_id = t.terapis_id 
            LEFT JOIN regions rt ON t.region_id = rt.id
            LEFT JOIN jabatan j ON t.jabatan_id = j.id
            $tindakanJoin
            WHERE 1=1 
            AND (
                u.role = 'superadmin'
                OR (u.role IN ('owner', 'admin') $jsonRegionCond)
                OR (u.role = 'user' $terapisRegionIn)
            )
            
            UNION ALL
            
            -- 2. Terapis yang BELUM punya akun user
            SELECT 
                NULL as user_id, 
                t.nama as name, 
                NULL as username, 
                'user' as role, 
                NULL as regions_patient, 
                t.terapis_id, 
                t.id as id_terapis_table,
                t.region_id as terapis_region_id,
                rt.name as terapis_region_name,
                j.nama_jabatan as terapis_jabatan_name,
                'Therapist' as personnel_type,
                t.is_active,
                COALESCE(ht.cnt, 0) as jml_tindakan
            FROM terapis t 
            LEFT JOIN regions rt ON t.region_id = rt.id
            LEFT JOIN jabatan j ON t.jabatan_id = j.id
            $tindakanJoin
            WHERE NOT EXISTS (SELECT 1 FROM users u WHERE u.terapis_id = t.terapis_id)
            " . (empty($regionIds) ? "" : " AND t.region_id IN (" . implode(',', $regionIds) . ")") . "
        ) AS personnel
        WHERE 1=1
        ";

        if (!empty($search)) {
            $search_esc = $this->db->escapeLikeString($search);
            $sql .= " AND (name LIKE '%$search_esc%' OR username LIKE '%$search_esc%' OR terapis_id LIKE '%$search_esc%')";
        }

        $sql .= " ORDER BY is_active DESC, name ASC LIMIT $limit OFFSET $offset";

        return $this->db->query($sql)->getResult();
    }

    public function getTotalData()
    {
        $search = $options['search'] ?? '';
        $regionFilter = $options['region_filter'] ?? null;

        if (!empty($search)) {
            return $this->getTotalFiltered($search, $regionFilter);
        }

        $regionIds = $this->normalizeRegionFilter($regionFilter);
        $jsonRegionCond = $this->buildJsonRegionCondition($regionIds);
        $terapisRegionIn = empty($regionIds) ? '' : ' AND t.region_id IN (' . implode(',', $regionIds) . ')';

        $sql = "
        SELECT COUNT(*) as total FROM (
            SELECT u.id 
            FROM users u 
            LEFT JOIN terapis t ON u.terapis_id = t.terapis_id
            WHERE 1=1 
            AND (
                u.role = 'superadmin'
                OR (u.role IN ('owner', 'admin') $jsonRegionCond)
                OR (u.role = 'user' $terapisRegionIn)
            )
            UNION ALL
            SELECT t.id 
            FROM terapis t 
            WHERE NOT EXISTS (SELECT 1 FROM users u WHERE u.terapis_id = t.terapis_id)
            " . (empty($regionIds) ? "" : " AND t.region_id IN (" . implode(',', $regionIds) . ")") . "
        ) AS personnel
        ";

        return $this->db->query($sql)->getRow()->total;
    }

    private function getTotalFiltered($search, $regionFilter = null)
    {
        $regionIds = $this->normalizeRegionFilter($regionFilter);
        $jsonRegionCond = $this->buildJsonRegionCondition($regionIds);
        $terapisRegionIn = empty($regionIds) ? '' : ' AND t.region_id IN (' . implode(',', $regionIds) . ')';

        $search_esc = $this->db->escapeLikeString($search);

        $sql = "
        SELECT COUNT(*) as total FROM (
            SELECT 
                COALESCE(t.nama, u.realname) as name, 
                u.username, 
                u.terapis_id
            FROM users u 
            LEFT JOIN terapis t ON u.terapis_id = t.terapis_id
            WHERE 1=1 
            AND (
                u.role = 'superadmin'
                OR (u.role IN ('owner', 'admin') $jsonRegionCond)
                OR (u.role = 'user' $terapisRegionIn)
            )
            
            UNION ALL
            
            SELECT 
                t.nama as name, 
                NULL as username, 
                t.terapis_id
            FROM terapis t 
            WHERE NOT EXISTS (SELECT 1 FROM users u WHERE u.terapis_id = t.terapis_id)
            " . (empty($regionIds) ? "" : " AND t.region_id IN (" . implode(',', $regionIds) . ")") . "
        ) AS personnel
        WHERE (name LIKE '%$search_esc%' OR username LIKE '%$search_esc%' OR terapis_id LIKE '%$search_esc%')
        ";

        $row = $this->db->query($sql)->getRow();
        return $row ? (int)$row->total : 0;
    }

    public function get_regions($allowed_regions = null)
    {
        $builder = $this->db->table('regions')->select('id, name')->where('is_active', 1);
        if (!empty($allowed_regions)) {
            if (is_string($allowed_regions)) {
                $decoded = json_decode($allowed_regions, true);
                if (is_array($decoded)) {
                    $allowed_regions = $decoded;
                } else if (strpos($allowed_regions, ',') !== false) {
                    $allowed_regions = explode(',', $allowed_regions);
                } else {
                    $allowed_regions = [$allowed_regions];
                }
            }
            if (is_array($allowed_regions) && !empty($allowed_regions)) {
                $builder->whereIn('id', $allowed_regions);
            }
        }
        return $builder->get()->getResult();
    }

    private function normalizeRegionFilter($regionFilter): array
    {
        if (empty($regionFilter) || $regionFilter === 'all') {
            return [];
        }

        if (!is_array($regionFilter)) {
            $regionFilter = [$regionFilter];
        }

        return array_values(array_unique(array_filter(array_map('intval', $regionFilter))));
    }

    private function buildJsonRegionCondition(array $regionIds): string
    {
        if (empty($regionIds)) {
            return '';
        }

        $jsonConditions = array_map(
            static fn ($id) => "JSON_CONTAINS(COALESCE(NULLIF(u.regions_patient, ''), '[]'), '$id', '$')",
            $regionIds
        );

        return " AND (" . implode(' OR ', $jsonConditions) . ")";
    }

    public function store($data)
    {
        $this->insert($data);
        return $this->db->insertID();
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
            return $export ? [] : $this->db->table('patients p')->where('1=0');
        }

        $region_ids = json_decode($user->regions_patient, true) ?: [];

        $builder = $this->db->table('patients p')
            ->select('p.id, p.name as nama, p.gender, p.age, p.address, r.name as wilayah')
            ->join('regions r', 'p.region_id = r.id', 'left')
            ->where('p.is_delete', 0);
        if ($user->role !== 'superadmin') {
            if (empty($region_ids)) {
                $builder->where('1=0');
            } else {
                $builder->whereIn('p.region_id', $region_ids);
            }
        }
        return $export ? $builder->get()->getResult() : $builder;
    }

    public function get_jabatan()
    {
        return $this->db->table('jabatan')->get()->getResult();
    }


    public function get_other_patients($user_id)
    {
        $user = $this->db->table('users')->where('id', $user_id)->get()->getRow();
        if (!$user) return $this->db->table('patients p')->where('1=0');

        $other_ids = json_decode($user->other_patient, true) ?: [];
        $builder = $this->db->table('patients p')
            ->select('p.id, p.name as nama, p.gender, p.age, p.address, r.name as wilayah')
            ->join('regions r', 'p.region_id = r.id', 'left');

        if (!empty($other_ids)) {
            $builder->whereIn('p.id', $other_ids);
        } else {
            $builder->where('1=0');
        }

        return $builder;
    }

    public function search_outside_patients($user_id, $term)
    {
        $user = $this->db->table('users')->where('id', $user_id)->get()->getRow();
        if (!$user) return [];

        $region_ids = json_decode($user->regions_patient, true) ?: [];
        $other_ids = json_decode($user->other_patient, true) ?: [];

        $builder = $this->db->table('patients p')
            ->select('p.id, p.name as text, r.name as wilayah')
            ->join('regions r', 'p.region_id = r.id', 'left');

        if (!empty($region_ids)) {
            $builder->whereNotIn('p.region_id', $region_ids);
        }
        if (!empty($other_ids)) {
            $builder->whereNotIn('p.id', $other_ids);
        }

        if ($term) {
            $builder->like('p.name', $term);
        }

        $results = $builder->limit(20)->get()->getResult();
        foreach ($results as $res) {
            $res->text = $res->text . ' (' . $res->wilayah . ')';
        }
        return $results;
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

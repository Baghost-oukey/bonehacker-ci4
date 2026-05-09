<?php

namespace App\modules\users\Models;

use CodeIgniter\Model;

class MUsers extends Model
{
  protected $table = 'users';
  protected $primaryKey = 'id';
  protected $useAutoIncrement = true;
  protected $returnType = 'object';
  protected $useSoftDeletes = false;
  protected $protectFields = true;
  protected $allowedFields = ['realname', 'username', 'password', 'role', 'regions_patient', 'other_patient'];

  protected bool $allowEmptyInserts = false;
  protected bool $updateOnlyChanged = true;

  protected array $casts = [];
  protected array $castHandlers = [];

  // Dates
  protected $useTimestamps = false;
  protected $dateFormat = 'datetime';
  protected $createdField = 'created_at';
  protected $updatedField = 'updated_at';
  protected $deletedField = 'deleted_at';

  // Validation
  protected $validationRules = [];
  protected $validationMessages = [];
  protected $skipValidation = false;
  protected $cleanValidationRules = true;

  // Callbacks
  protected $allowCallbacks = true;
  protected $beforeInsert = [];
  protected $afterInsert = [];
  protected $beforeUpdate = [];
  protected $afterUpdate = [];
  protected $beforeFind = [];
  protected $afterFind = [];
  protected $beforeDelete = [];
  protected $afterDelete = [];

  public function show($id)
  {
    return $this->where('id', $id)->first();
  }

  public function getListData($options = [])
  {
    $builder = $this->db->table($this->table . ' u');
    $builder->select('u.id, u.realname, u.username, u.role, u.regions_patient, r.name AS region_name');
    $builder->join('regions r', 'u.regions_patient = r.id', 'left');

    if (!empty($options['search'])) {
      $builder->groupStart()
        ->like('u.realname', $options['search'])
        ->orLike('u.username', $options['search'])
        ->groupEnd();
    }

    $order = $options['order'] ?? 'u.realname';
    $mode = $options['mode'] ?? 'asc';

    return $builder->orderBy($order, $mode)->limit($options['limit'], $options['offset'])->get()->getResult();
  }

  public function getTotalData($options)
  {
    $builder = $this->db->table($this->table . ' u');
    if (!empty($options['search'])) {
      $builder->groupStart()
        ->like('u.realname', $options['search'])
        ->orLike('u.username', $options['search'])
        ->groupEnd();
    }
    return $builder->countAllResults();
  }

  public function get_regions()
  {
    return $this->db->table('regions')->select('id, name')->get()->getResult();
  }

  public function get_patients_by_user_region($user_id, $export = false)
  {
    $user = $this->show($user_id);
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

  public function append_patient_to_user($user_id, $patient_id)
  {
    $sql = "UPDATE users SET other_patient = JSON_ARRAY_APPEND(IFNULL(other_patient, '[]'), '$', ?) WHERE id = ?";
    return $this->db->query($sql, [(int) $patient_id, (int) $user_id]);
  }

  public function username_exists_edit($username, $user_id)
  {
    return $this->where('username', $username)->where('id !=', $user_id)->countAllResults() > 0;
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

  public function get_other_patients($user_id, $export = false)
  {
    $user = $this->show($user_id);
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
    $user = $this->show($user_id);
    $region_ids = json_decode($user->regions_patient, true) ?: [];
    $other_patient_ids = json_decode($user->other_patient, true) ?: [];

    $builder = $this->db->table('patients p');
    $builder->select('p.id, p.name as nama, p.gender, p.age, p.address, r.name as wilayah');
    $builder->join('regions r', 'p.region_id = r.id', 'left');
    $builder->where('p.is_delete', 0);

    // Kecualikan wilayah yang sudah dimiliki
    if (!empty($region_ids)) {
      $builder->whereNotIn('p.region_id', $region_ids);
    }

    // Kecualikan pasien yang sudah ditambahkan manual
    if (!empty($other_patient_ids)) {
      $builder->whereNotIn('p.id', $other_patient_ids);
    }

    if ($search_term) {
      $builder->groupStart()->like('p.name', $search_term)->orLike('p.address', $search_term)->orLike('r.name', $search_term)->groupEnd();
    }

    return $builder->limit($limit)->get()->getResult();
  }
}

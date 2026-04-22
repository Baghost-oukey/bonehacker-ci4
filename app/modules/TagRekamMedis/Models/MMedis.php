<?php

namespace App\Modules\TagRekamMedis\Models;

use CodeIgniter\Model;

class MMedis extends Model
{
  protected $table = 'medhis_tags'; // pastikan ini sesuai database kamu
  protected $primaryKey = 'id';
  protected $returnType = 'object';
  protected $allowedFields = ['name', 'description', 'created_at', 'updated_at'];

  public function checkNameExists($name, $id = null)
  {
    $builder = $this->where('name', $name);

    if ($id) {
      $builder->where('id !=', $id);
    }

    return $builder->countAllResults() > 0;
  }

  // 🔥 FIX: nama method diperbaiki
  public function getMedisTags()
  {
    $subQuery = '(SELECT COUNT(*) FROM histories h WHERE FIND_IN_SET(medhis_tags.id, h.medhis))';

    return $this->builder()
      ->select('medhis_tags.id, medhis_tags.name as nama, medhis_tags.description as deskripsi')
      ->select("$subQuery as jumlah", false);
  }

  public function get_all_tags()
  {
    return $this->select('id, name')->findAll();
  }

  public function store($data)
  {
    $this->insert($data);
    return $this->db->insertID();
  }

  public function destroy($tagId)
  {
    $this->db->transStart();

    $this->delete($tagId);

    $this->db
      ->table('histories')
      ->set('medhis', "TRIM(BOTH ',' FROM REPLACE(CONCAT(',', medhis, ','), ',$tagId,', ','))", false)
      ->where("FIND_IN_SET('$tagId', medhis) >", 0)
      ->update();

    $this->db->transComplete();

    return $this->db->transStatus();
  }
}

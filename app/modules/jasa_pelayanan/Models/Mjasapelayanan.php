<?php

namespace App\modules\jasa_pelayanan\Models;

use CodeIgniter\Model;

class Mjasapelayanan extends Model
{
    protected $table            = 'jasa_pelayanan';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'history_id',
        'patient_id',
        'terapis_id',
        'kategori_layanan',
        'tanggal_layanan',
        'is_delete'
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = true;
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

   private function _buildQuery(string $kategori)
    {
        return $this->db->table($this->table . ' jp')
            ->select('
                jp.*, 
                p.name as nama_pasien, 
                p.phone as telepon_pasien,
                t.nama as nama_terapis
            ')
            ->join('histories h', 'h.id = jp.history_id', 'left')
            ->join('patients p', 'p.id = jp.patient_id', 'left')
            ->join('terapis t', 't.id = jp.terapis_id', 'left')
            ->where('jp.is_delete', 0)
            ->where('jp.kategori_layanan', $kategori);
    }

    public function getDatatablesBuilder(string $kategori)
    {
        return $this->_buildQuery($kategori);
    }

    public function destroy(int $id)
    {
        return $this->update($id, ['is_delete' => 1]);
    }

    // FUNGSI DETAIL (BAWAAN MAS BAGUS)

    public function showDetail(int $id)
    {
        // 1. Ambil header jasa pelayanan
        $header = $this->where('id', $id)->first();
        if (!$header) return null;
        $history_id = is_array($header) ? $header['history_id'] : $header->history_id;
        $kategori   = is_array($header) ? $header['kategori_layanan'] : $header->kategori_layanan;

        $builder = $this->db->table('histories h')
            ->select('h.*, p.name as nama_pasien, t.nama as nama_terapis')
            ->join('patients p', 'p.id = h.patient_id', 'left')
            ->join('terapis t', 't.id = h.terapis_id', 'left')
            ->where('h.id', $history_id);

        if ($kategori === 'Kejantanan') {
            $builder->select('kj.*')
                ->join('kejantanan kj', 'kj.history_id = h.id', 'left');
        }

        return $builder->get()->getRowObject();
    }
}

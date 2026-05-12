<?php

namespace App\modules\absensi_karyawan\Controllers;

use App\Controllers\BaseController;
use App\modules\absensi_karyawan\Models\Mcutikaryawan;
use App\modules\absensi_karyawan\Models\Mabsensikaryawan;
use App\modules\terapis\Models\MTerapis;

class Cutikaryawan extends BaseController
{
    protected $model_cuti;
    protected $model_absensi;
    protected $model_terapis;

    public function __construct()
    {
        $this->model_cuti = new Mcutikaryawan();
        $this->model_absensi = new Mabsensikaryawan();
        $this->model_terapis = new MTerapis();
    }

    public function index()
    {
        $region_patient = session()->get('region_patient');
        $allowed_regions = ($region_patient !== 'all') ? $region_patient : null;

        $terapis = $this->model_terapis->where('is_active', 1)->findAll();
        $terpakai = $this->model_cuti->getTotalCutiTerpakai();

        foreach ($terapis as $t) {
            $t->terpakai = $terpakai[$t->id] ?? 0;
            $t->sisa = (int)$t->jatah_cuti - $t->terpakai;
        }

        $data = [
            'title'   => 'Manajemen Cuti Karyawan',
            'cuti'    => $this->model_cuti->getCutiByRegion(null, $allowed_regions),
            'terapis' => $terapis
        ];

        return view('App\modules\absensi_karyawan\Views\cuti_index', $data);
    }

    public function simpan()
    {
        $terapis_id = $this->request->getPost('terapis_id');
        $tgl_mulai = $this->request->getPost('tanggal_mulai');
        $jumlah_hari = (int)$this->request->getPost('jumlah_hari');
        $keterangan = $this->request->getPost('keterangan');

        // Hitung tanggal selesai
        $start = new \DateTime($tgl_mulai);
        $end = clone $start;
        if ($jumlah_hari > 1) {
            $end->modify('+' . ($jumlah_hari - 1) . ' days');
        }
        $tgl_selesai = $end->format('Y-m-d');

        $data = [
            'terapis_id'      => $terapis_id,
            'tanggal_mulai'   => $tgl_mulai,
            'tanggal_selesai' => $tgl_selesai,
            'jumlah_hari'     => $jumlah_hari,
            'keterangan'      => $keterangan,
            'status'          => 'Disetujui'
        ];

        $this->model_cuti->insert($data);

        // Update Absensi secara otomatis
        $current = clone $start;
        $end_loop = new \DateTime($tgl_selesai);
        $end_loop->modify('+1 day');

        while ($current < $end_loop) {
            $tgl = $current->format('Y-m-d');
            
            // Cek apakah sudah ada absensi di tanggal tersebut
            $existing = $this->model_absensi->where('terapis_id', $terapis_id)
                                           ->where('tanggal', $tgl)
                                           ->first();
            
            $dataAbsen = [
                'terapis_id' => $terapis_id,
                'tanggal'    => $tgl,
                'status'     => 'Cuti',
                'keterangan' => 'Cuti: ' . $keterangan
            ];

            if ($existing) {
                $this->model_absensi->update($existing['id'], $dataAbsen);
            } else {
                $this->model_absensi->insert($dataAbsen);
            }

            $current->modify('+1 day');
        }

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Data cuti berhasil disimpan dan absensi telah diperbarui.',
            'csrfHash' => csrf_hash()
        ]);
    }

    public function hapus($id)
    {
        $cuti = $this->model_cuti->find($id);
        if ($cuti) {
            // Hapus juga absensi yang terkait dengan cuti ini
            $this->model_absensi->where('terapis_id', $cuti->terapis_id)
                               ->where('tanggal >=', $cuti->tanggal_mulai)
                               ->where('tanggal <=', $cuti->tanggal_selesai)
                               ->where('status', 'Cuti')
                               ->delete();
            
            $this->model_cuti->delete($id);
            return $this->response->setJSON(['status' => 'success', 'message' => 'Data cuti berhasil dihapus.']);
        }
        return $this->response->setJSON(['status' => 'error', 'message' => 'Data tidak ditemukan.']);
    }

    public function update_kuota()
    {
        $terapis_id = $this->request->getPost('terapis_id');
        $kuota = $this->request->getPost('jatah_cuti');

        $this->model_terapis->update($terapis_id, ['jatah_cuti' => $kuota]);

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Kuota cuti berhasil diperbarui.',
            'csrfHash' => csrf_hash()
        ]);
    }

    public function cek_sisa_cuti($id)
    {
        $terapis = $this->model_terapis->find($id);
        if (!$terapis) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Karyawan tidak ditemukan.']);
        }

        $tahun = date('Y');
        $total_terpakai = $this->model_cuti->where('terapis_id', $id)
            ->where('YEAR(tanggal_mulai)', $tahun)
            ->selectSum('jumlah_hari')
            ->first();

        $terpakai = (int)($total_terpakai->jumlah_hari ?? 0);
        $kuota = (int)$terapis->jatah_cuti;
        $sisa = $kuota - $terpakai;

        return $this->response->setJSON([
            'status'   => 'success',
            'kuota'    => $kuota,
            'terpakai' => $terpakai,
            'sisa'     => $sisa,
            'tahun'    => $tahun
        ]);
    }
}

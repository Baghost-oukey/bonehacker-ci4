<?php

namespace App\modules\terapis\Controllers;

use App\Controllers\BaseController;
use App\modules\jabatan\Models\Mjabatan;
use App\modules\region\Models\MRegion;
use App\modules\terapis\Models\MTerapis;
use CodeIgniter\HTTP\ResponseInterface;

class Terapis extends BaseController
{

    protected $model_terapis;
    protected $model_jabatan;
    protected $model_region;
    protected $session;

    public function __construct()
    {
        $this->model_terapis = new MTerapis();
        $this->model_jabatan = new Mjabatan();
        $this->model_region = new MRegion();
        $this->session = \Config\Services::session();

        $router = service('router');
        if ($router->methodName() !== 'public_info') {
            if ($this->session->get('role') !== 'superadmin') {
                $this->session->setFlashdata('message', ['danger', 'You do not have access to this page']);
                return redirect()->to(base_url())->send();
            }
        }
    }

    public function index()
    {
        //

        $data = [
            'realname'        => $this->session->get('realname'),
            'base_url'        => base_url(),
            'current_segment' => $this->request->getUri()->getSegment(1),
            'title'           => 'Terapis',
            'regions'         => $this->model_terapis->get_regions(),
            'jabatan'         => $this->model_jabatan->getData(),
            'msg'             => $this->session->getFlashdata('message'),
            'role'            => $this->session->get('role'),
        ];
        $data['wilayah'] = $this->model_region->getData();

        return view('App\modules\terapis\Views\views_terapis', $data);
    }

    public function detail_terapis($user_id)
    {
        $data = [
            'realname'        => $this->session->get('realname'),
            'base_url'        => base_url(),
            'current_segment' => $this->request->getUri()->getSegment(1),
            'title'           => 'Data Terapis',
            'msg'             => $this->session->getFlashdata('message'),
            'role'            => $this->session->get('role'),
            'terapis'         => $this->model_terapis->detail($user_id),
            'wilayah'         => $this->model_region->getData(),
            'jabatan'         => $this->model_terapis->get_jabatan()
        ];

        $qrContent = base_url('terapis/public_info/' . $user_id);
        $data['qr_code_base64'] = ""; 

        return view('App\modules\terapis\Views\views_detail', $data);
    }

    public function public_info($id)
    {
        $data = [
            'base_url'        => base_url(),
            'current_segment' => $this->request->getUri()->getSegment(1),
            'title'           => 'Data Terapis',
            'terapis'         => $this->model_terapis->detail($id),
            'jabatan'         => $this->model_terapis->getJabatanById($id),
            'wilayah'         => $this->model_terapis->getRegionById($id),
        ];

        return view('App\modules\terapis\Views\views_info_publik', $data);
    }

    public function store()
    {
        $file = $this->request->getFile('foto');
        $tgl_kerja = $this->request->getPost('tgl_kerja');
        
        $data = [
            'terapis_id'      => $this->request->getPost('terapis_id'),
            'nama'            => $this->request->getPost('nama'),
            'tempat_lahir'    => $this->request->getPost('tempat_lahir'),
            'tanggal_lahir'   => $this->request->getPost('tgl_lahir'),
            'alamat'          => $this->request->getPost('alamat') ?: null,
            'region_id'       => $this->request->getPost('region_id'),
            'jabatan_id'      => $this->request->getPost('jabatan_id'),
            'rank'            => $this->request->getPost('rank'),
            'tgl_mulai_kerja' => !empty($tgl_kerja) ? $tgl_kerja . ' ' . date('H:i:s') : null,
            'keterangan'      => $this->request->getPost('keterangan'),
        ];

        if ($file && $file->isValid() && !$file->hasMoved()) {
            $newName = $file->getRandomName();
            $file->move(FCPATH . 'foto_terapis/', $newName);
            $data['foto'] = $newName;
        }

        if ($this->model_terapis->store($data)) {
            $this->session->setFlashdata('message', ['success', 'Data berhasil disimpan']);
        } else {
            $this->session->setFlashdata('message', ['danger', 'Gagal menyimpan data']);
        }

        return redirect()->to('terapis');
    }

    public function update()
    {
        $id = $this->request->getPost('id');
        $terapis_id = $this->request->getPost('terapis_id');
        $status = $this->request->getPost('status') === 'on' ? 1 : 0;
        $file = $this->request->getFile('foto');

        $data = [
            'terapis_id'      => $terapis_id,
            'nama'            => $this->request->getPost('nama'),
            'tempat_lahir'    => $this->request->getPost('tempat_lahir'),
            'tanggal_lahir'   => $this->request->getPost('tgl_lahir'),
            'alamat'          => $this->request->getPost('alamat') ?: null,
            'region_id'       => $this->request->getPost('region_id'),
            'jabatan_id'      => $this->request->getPost('jabatan_id'),
            'rank'            => $this->request->getPost('rank'),
            'tgl_mulai_kerja' => $this->request->getPost('tgl_kerja'),
            'keterangan'      => $this->request->getPost('keterangan'),
            'is_active'       => $status,
        ];

        if ($file && $file->isValid() && !$file->hasMoved()) {
            // Hapus foto lama
            $oldData = $this->model_terapis->getById($id);
            if ($oldData && $oldData->foto && file_exists(FCPATH . 'foto_terapis/' . $oldData->foto)) {
                unlink(FCPATH . 'foto_terapis/' . $oldData->foto);
            }

            $newName = $file->getRandomName();
            $file->move(FCPATH . 'foto_terapis/', $newName);
            $data['foto'] = $newName;
        }

        if ($this->model_terapis->update($id, $data)) {
            $this->session->setFlashdata('message', ['success', 'Data berhasil diperbarui']);
        }

        return redirect()->to('terapis/detail_terapis/' . $terapis_id);
    }

    public function destroy($id)
    {
        $terapis = $this->model_terapis->getById($id);
        if ($terapis) {
            if ($terapis->foto && file_exists(FCPATH . 'foto_terapis/' . $terapis->foto)) {
                unlink(FCPATH . 'foto_terapis/' . $terapis->foto);
            }
            $this->model_terapis->delete($id);
            $this->session->setFlashdata('message', ['success', 'Data berhasil dihapus']);
        }
        return redirect()->to('terapis');
    }
}

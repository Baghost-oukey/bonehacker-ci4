<?php

namespace App\modules\terapis\Controllers;

use App\Controllers\BaseController;
use App\modules\jabatan\Models\Mjabatan;
use App\modules\region\Models\MRegion;
use App\modules\terapis\Models\MTerapis;
use CodeIgniter\HTTP\ResponseInterface;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

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

    public function fetch()
    {
        $region = $this->request->getPost('region');
        $queryBuilder = $this->model_terapis->getTerapis($region);

        $datatables = new \Ngekoding\CodeIgniterDataTables\DataTables($queryBuilder, '4');

        $start = $this->request->getPost('start') ?? 0;
        $datatables->addColumn('no', function ($row) use (&$start) {
            return ++$start;
        });

        $datatables->addColumn('jml_tindakan', function ($row) {
            $row = (object) $row;
            $jumlah = $row->jml_tindakan ?? 0;

            if ($jumlah == 0) {
                return '<span class="badge badge-danger">0 Tindakan</span>';
            } else {

                return '<span class="badge badge-info">' . $jumlah . ' Tindakan</span>';
            }
        });

        $datatables->addColumn('is_active', function ($row) {
            $row = (object) $row;
            if ($row->is_active == 1) {
                return '<span class="badge badge-success">Aktif</span>';
            } else {
                return '<span class="badge badge-danger">Tidak Aktif</span>';
            }
        });

        $datatables->addColumn('action', function ($row) {
            $row = (object) $row;
            $type = ($row->is_active == 1) ? 'delete' : 'active';
            $btn_status = ($row->is_active == 1)
                ? '<a href="javascript:void(0)" data-href="' . base_url("terapis/nonActive/" . $row->id) . '" data-type="delete" class="btn btn-danger btn_status"><i class="fas fa-window-close"></i></a>'
                : '<a href="javascript:void(0)" data-href="' . base_url("terapis/active/" . $row->id) . '" data-type="active" class="btn btn-primary btn_status mr-1"><i class="fas fa-check-square"></i></a>';

            return '
            <a href="' . base_url('terapis/detail_terapis/' . $row->terapis_id) . '" class="btn btn-primary"><i class="fas fa-eye"></i></a>' . $btn_status;
            // 'button type="button" data-href="' . base_url("terapis/destroy/" . $row->id) . '" class="btn btn-danger btn-sm btn_delete"><i class="fas fa-trash"></i></button>';
        });

        $datatables->asObject();
        $output = $datatables->generate(false);
        $output['csrfHash'] = csrf_hash();
        return $this->response->setJSON($output);
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

        $writer = new PngWriter();
        $qrCode = QrCode::create($qrContent)
            ->setEncoding(new Encoding('UTF-8'))
            ->setSize(300)
            ->setMargin(10)
            ->setForegroundColor(new Color(0, 0, 0)) // Warna QR (Hitam)
            ->setBackgroundColor(new Color(255, 255, 255)); // Warna Background (Putih)

        $result = $writer->write($qrCode);

        $data['qr_code_base64'] = $result->getDataUri();

        return view('App\modules\terapis\Views\views_detail', $data);
    }

    public function checkId()
    {
        $id = $this->request->getPost('terapis_id');
        $currentId = $this->request->getPost('currentId');
        $builder = $this->model_terapis; 
        if (!empty($currentId)) {
            $builder->where('terapis_id !=', $currentId);
        }
        $exists = $builder->where('terapis_id', $id)
            ->countAllResults() > 0;
        return $this->response->setJSON(['exists' => $exists]);
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

    public function active($id)
    {
        if ($this->request->isAJAX()) {
            if ($this->model_terapis->update($id, ['is_active' => 1])) {
                return $this->response->setJSON([
                    'status'   => 'success',
                    'message'  => 'Terapis berhasil diaktifkan',
                    'csrfHash' => csrf_hash() // Penting untuk update token di sisi JS
                ]);
            }

            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Gagal mengaktifkan terapis'
            ]);
        }

        // Fallback jika diakses manual tanpa AJAX (opsional)
        $this->model_terapis->update($id, ['is_active' => 1]);
        return redirect()->to('terapis');
    }

    public function nonActive($id)
    {
        if ($this->request->isAJAX()) {
            if ($this->model_terapis->update($id, ['is_active' => 0])) {
                return $this->response->setJSON([
                    'status'   => 'success',
                    'message'  => 'Terapis berhasil dinonaktifkan',
                    'csrfHash' => csrf_hash()
                ]);
            }

            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Gagal menonaktifkan terapis'
            ]);
        }

        $this->model_terapis->update($id, ['is_active' => 0]);
        return redirect()->to('terapis');
    }
}

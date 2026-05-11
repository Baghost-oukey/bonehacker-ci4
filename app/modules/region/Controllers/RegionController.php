<?php

namespace App\Modules\Region\Controllers;

use App\Controllers\BaseController;
use App\modules\region\Models\MRegion;
use CodeIgniter\HTTP\ResponseInterface;

class RegionController extends BaseController
{
    protected $model_regions;

    public function __construct()
    {
        $this->model_regions = new MRegion();
    }


    public function index()
    {
        if (session()->get('role') !== 'superadmin') {
            return redirect()->to(base_url('beranda'))->with('message', ['error', 'Akses ditolak.', 'Halaman Cabang hanya dapat diakses oleh Superadmin.']);
        }

        $data = [
            'realname' => session()->get('realname'),
            'role' => session()->get('role'),
            'base_url' => base_url(),
            'current_segment' => $this->request->getUri()->getSegment(1),
            'title' => 'Cabang',
            'msg' => session()->getFlashdata('message')
        ];

        return view('App\Modules\Region\Views\index', $data);
    }

    public function fetch()
    {
        if (session()->get('role') !== 'superadmin') {
            return $this->response->setStatusCode(403)->setJSON(['status' => 'error', 'message' => 'Akses ditolak.']);
        }
        $requestData = $this->request->getPost();
        $order = $this->request->getPost('order');
        $column = $this->request->getPost('columns');

        $options = [];
        $options['order'] = !empty($order) && !empty($column) ? $column[$order[0]['column']]['data'] : 'r.id';
        $options['mode'] = !empty($order) ? $order[0]['dir'] : 'ASC';

        $start = $this->request->getPost('start');
        $length = $this->request->getPost('length');
        $options['offset'] = empty($start) ? 0 : $start;
        $options['limit'] = empty($length) ? 10 : $length;
        if (!empty($requestData['search']['value'])) {
            $options['where_like'] = [
                "r.name LIKE '%" . $requestData['search']['value'] . "%'"
            ];
        }

        // Role-based filtering
        // Dihapus karena halaman Cabang hanya bisa diakses oleh superadmin (semua cabang ditampilkan)

        $dataOutput = $this->model_regions->getListData($options);
        $totalFiltered = $this->model_regions->getTotalData($options);
        $totalData = $this->model_regions->getTotal($options);
        $no = $options['offset'] + 1;

        $role = session()->get('role');
        if (!empty($dataOutput)) {
            foreach ($dataOutput as $value) {
                $value->no = $no;
                $statusBadge = ($value->is_active == 1) ? '' : ' <span class="ml-2 inline-flex items-center rounded-md bg-red-50 px-2 py-1 text-xs font-medium text-red-700 ring-1 ring-inset ring-red-600/10">Nonaktif</span>';
                $value->name_view = "<a href='" . base_url() . "?region=" . $value->id . "'>" . $value->name . $statusBadge . "<br>(" . $value->jumlah . " Pasien)</a>";


                $value->created_at = !empty($value->created_at) ? $value->created_at : '-';
                $value->updated_at = !empty($value->updated_at) ? $value->updated_at : '-';

                if ($role === 'superadmin') {
                    if ($value->is_active == 1) {
                        $value->action = '
                            <div class="flex items-center justify-center gap-2">
                                <button type="button" 
                                    data-name="' . htmlspecialchars($value->name, ENT_QUOTES, 'UTF-8') . '" 
                                    data-address="' . htmlspecialchars($value->address ?? '', ENT_QUOTES, 'UTF-8') . '" 
                                    data-phone="' . htmlspecialchars($value->phone ?? '', ENT_QUOTES, 'UTF-8') . '" 
                                    data-href="' . base_url('region/update/' . $value->id) . '" 
                                    title="Edit Data" 
                                    class="group flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-400 shadow-sm transition-all hover:border-teal-200 hover:bg-teal-50 hover:text-teal-600 btn_edit">
                                    <i class="fas fa-edit text-xs transition-transform group-hover:scale-110"></i>
                                </button>

                                <button type="button" 
                                    data-href="' . base_url('region/destroy/' . $value->id) . '" 
                                    title="Hapus Data" 
                                    class="group flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-400 shadow-sm transition-all hover:border-red-200 hover:bg-red-50 hover:text-red-600 btn_delete">
                                    <i class="fas fa-trash text-xs transition-transform group-hover:scale-110"></i>
                                </button>
                            </div>';
                    } else {
                        $value->action = '
                            <div class="flex items-center justify-center gap-2">
                                <button type="button" 
                                    data-name="' . htmlspecialchars($value->name, ENT_QUOTES, 'UTF-8') . '" 
                                    data-href="' . base_url('region/reactivate/' . $value->id) . '" 
                                    title="Aktifkan Cabang" 
                                    class="group flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-400 shadow-sm transition-all hover:border-teal-200 hover:bg-teal-50 hover:text-teal-600 btn_reactivate">
                                    <i class="fas fa-check text-xs transition-transform group-hover:scale-110"></i>
                                </button>
                            </div>';
                    }
                } else {
                    $value->action = '-';
                }

                $no++;
            }
        }

        $response = [
            "draw" => isset($requestData['draw']) ? intval($requestData['draw']) : 0,
            "recordsTotal" => intval($totalData),
            "recordsFiltered" => intval($totalFiltered),
            "data" => $dataOutput,
            "new_token" => csrf_hash()
        ];


        return $this->response->setJSON($response);
    }

    public function store()
    {
        if (session()->get('role') !== 'superadmin') {
            return $this->response->setStatusCode(403)->setJSON(['status' => 'error', 'message' => 'Akses ditolak.']);
        }
        $name = strtoupper(trim($this->request->getPost('name')));
        $isExist = $this->model_regions->where('name', $name)->where('is_active', 1)->first();
        $csrfToken = csrf_hash();
        if ($isExist) {
            return $this->response->setJSON([
                'status'    => 'error',
                'message'   => 'Gagal! Cabang "' . $name . '" sudah terdaftar.',
                'new_token' => $csrfToken
            ]);
        }

        $address = trim($this->request->getPost('address'));
        $phone = trim($this->request->getPost('phone'));

        $data = [
            'name' => $name,
            'address' => $address,
            'phone' => $phone
        ];

        if ($this->model_regions->insert($data)) {
            return $this->response->setJSON([
                'status'    => 'success',
                'message'   => 'Data cabang berhasil disimpan!',
                'new_token' => $csrfToken
            ]);
        } else {
            return $this->response->setJSON([
                'status'    => 'error',
                'message'   => 'Gagal menyimpan data ke database.',
                'new_token' => $csrfToken
            ]);
        }
    }

    public function update($id)
    {
        if (session()->get('role') !== 'superadmin') {
            return $this->response->setStatusCode(403)->setJSON(['status' => 'error', 'message' => 'Akses ditolak.']);
        }
        $name = strtoupper(trim($this->request->getPost('name')));
        $csrfToken = csrf_hash();
        $isExist = $this->model_regions->where('name', $name)->where('is_active', 1)->where('id !=', $id)->first();

        if ($isExist) {
            return $this->response->setJSON([
                'status'    => 'error',
                'message'   => 'Gagal! Cabang "' . $name . '" sudah terdaftar.',
                'new_token' => $csrfToken
            ]);
        }

        $address = trim($this->request->getPost('address'));
        $phone = trim($this->request->getPost('phone'));

        $data = [
            'name' => $name,
            'address' => $address,
            'phone' => $phone
        ];

        if ($this->model_regions->update($id, $data)) {
            return $this->response->setJSON([
                'status'    => 'success',
                'message'   => 'Data cabang berhasil diubah!',
                'new_token' => $csrfToken
            ]);
        } else {
            return $this->response->setJSON([
                'status'    => 'error',
                'message'   => 'Gagal mengubah data di database.',
                'new_token' => $csrfToken
            ]);
        }
    }

    public function destroy($id)
    {
        if (session()->get('role') !== 'superadmin') {
            return $this->response->setStatusCode(403)->setJSON(['status' => 'error', 'message' => 'Akses ditolak.']);
        }
        $csrfToken = csrf_hash();
        $db = \Config\Database::connect();
        $countPatient = $db->table('patients')->where("region_id", $id)->countAllResults();

        if ($countPatient > 0) {
            // Update branch to inactive
            $this->model_regions->update($id, ['is_active' => 0]);
            
            // Deactivate all therapists in this branch
            $db->table('terapis')->where('region_id', $id)->update(['is_active' => 0]);

            // Deactivate users (karyawan) in this branch
            // regions_patient is a string like "5" or JSON like ["5"]
            // The user might be associated with multiple branches, but since there's no bridge table, we search by string
            $users = $db->table('users')->get()->getResultArray();
            foreach ($users as $u) {
                $u_regions = $u['regions_patient'];
                $shouldDeactivate = false;
                if (!empty($u_regions) && $u_regions !== 'all') {
                    $decoded = json_decode($u_regions, true);
                    if (is_array($decoded) && in_array($id, $decoded)) {
                        $shouldDeactivate = true;
                    } else if ((string)$u_regions === (string)$id) {
                        $shouldDeactivate = true;
                    }
                }
                if ($shouldDeactivate) {
                    $db->table('users')->where('id', $u['id'])->update(['is_active' => 0]);
                }
            }

            return $this->response->setJSON([
                'status'    => 'success',
                'message'   => 'Cabang memiliki pasien. Status cabang beserta seluruh karyawan/terapis di dalamnya telah dinonaktifkan.',
                'new_token' => $csrfToken
            ]);
        }

        // Hard delete if 0 patients
        if ($this->model_regions->delete($id, true)) {
            return $this->response->setJSON([
                'status'    => 'success',
                'message'   => 'Data Cabang berhasil dihapus!',
                'new_token' => $csrfToken
            ]);
        } else {
            return $this->response->setJSON([
                'status'    => 'error',
                'message'   => 'Terjadi kesalahan, gagal menghapus data.',
                'new_token' => $csrfToken
            ]);
        }

        // return redirect()->to(base_url('region'));
    }
    public function reactivate($id)
    {
        if (session()->get('role') !== 'superadmin') {
            return $this->response->setStatusCode(403)->setJSON(['status' => 'error', 'message' => 'Akses ditolak.']);
        }
        $csrfToken = csrf_hash();
        $db = \Config\Database::connect();

        // Update branch to active
        if ($this->model_regions->update($id, ['is_active' => 1])) {
            // Reactivate all therapists in this branch
            $db->table('terapis')->where('region_id', $id)->update(['is_active' => 1]);

            // Reactivate users (karyawan) in this branch
            $users = $db->table('users')->get()->getResultArray();
            foreach ($users as $u) {
                $u_regions = $u['regions_patient'];
                $shouldReactivate = false;
                if (!empty($u_regions) && $u_regions !== 'all') {
                    $decoded = json_decode($u_regions, true);
                    if (is_array($decoded) && in_array($id, $decoded)) {
                        $shouldReactivate = true;
                    } else if ((string)$u_regions === (string)$id) {
                        $shouldReactivate = true;
                    }
                }
                if ($shouldReactivate) {
                    $db->table('users')->where('id', $u['id'])->update(['is_active' => 1]);
                }
            }

            return $this->response->setJSON([
                'status'    => 'success',
                'message'   => 'Cabang beserta seluruh karyawan/terapis di dalamnya telah diaktifkan kembali.',
                'new_token' => $csrfToken
            ]);
        } else {
            return $this->response->setJSON([
                'status'    => 'error',
                'message'   => 'Terjadi kesalahan, gagal mengaktifkan cabang.',
                'new_token' => $csrfToken
            ]);
        }
    }
}

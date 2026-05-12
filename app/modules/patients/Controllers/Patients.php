<?php

namespace App\modules\patients\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\modules\address\Models\MAddress;
use App\modules\patients\Models;
use App\modules\patients\Models\MPatients;
use TCPDF;

class Patients extends BaseController
{


    protected $jenisKelamin = ['Man' => 'Laki-laki', 'Woman' => 'Perempuan'];
    protected $bulan_indo = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
    ];
    protected $patientModel;
    protected $session;

    protected $db;

    public function __construct()
    {
        $this->patientModel = new MPatients();
        $this->session = \Config\Services::session();
        $this->db = \Config\Database::connect();
        helper(['url', 'form']);
    }

    public function store()
    {
        $files = $this->request->getFileMultiple('userfiles');
        $file_urls = [];

        // Handle Multiple Uploads
        if ($files) {
            foreach ($files as $file) {
                if ($file->isValid() && !$file->hasMoved()) {
                    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp', 'application/pdf'];
                    if (in_array($file->getMimeType(), $allowedTypes) && $file->getSizeByUnit('kb') <= 10240) {
                        $newName = $file->getRandomName();

                        if ($file->move(FCPATH . 'patient_file', $newName)) {
                            $file_urls[] = $newName;
                        }
                    }
                }
            }
        }

        $domestic = ($this->request->getPost('domestic') === 'dalam_negeri') ? 1 : 0;
        $userId = $this->session->get('userId') ?? $this->session->get('id');

        if (!$userId) {
            return redirect()->back()->with('message', ['error', 'Sesi login habis. Silakan login kembali.']);
        }

        // Data Pasien
        $patientData = [
            'name' => $this->request->getPost('name'),
            'gender' => $this->request->getPost('gender'),
            'age' => $this->request->getPost('age') ?: 0,
            'country_id' => $this->request->getPost('country_id'),
            'address' => $this->request->getPost('address') ?: "",
            // 'phone'            => $this->request->getPost('phone') ?: "",
            'phone' => (string) ($this->request->getPost('phone') ?? ""),
            'region_id' => $this->request->getPost('region_id'),
            'is_suspective' => $this->request->getPost('is_suspective') === 'on' ? 1 : 0,
            'domestic' => $domestic,
            'url' => json_encode($file_urls),
            'created_by' => $userId,
            'patient_information' => $this->request->getPost('patient_information') ?: "",
            'ket_suspect' => $this->request->getPost('ket_rentan') ?: "",
            'created_at' => date('Y-m-d H:i:s'),
        ];

        $visitDate = $this->request->getPost('visit_date');
        if ($visitDate) {
            $formattedDate = date('Y-m-d', strtotime($visitDate));
            $patientData['created_at'] = $formattedDate . ' ' . date('H:i:s');
        } else {
            $patientData['created_at'] = date('Y-m-d H:i:s');
        }

        try {

            if ($this->patientModel->insert($patientData)) {
                $patientId = $this->patientModel->getInsertID();
                $addressModel = new MAddress();
                $addressData = [
                    'patient_id' => $patientId,
                    'desa_id' => $this->request->getPost('desa_id'),
                    'desa_nama' => $this->request->getPost('desa_nama'),
                    'kecamatan_id' => $this->request->getPost('kecamatan_id'),
                    'kecamatan_nama' => $this->request->getPost('kecamatan_nama'),
                    'kabupaten_id' => $this->request->getPost('kabupaten_id'),
                    'kabupaten_nama' => $this->request->getPost('kabupaten_nama'),
                    'provinsi_id' => $this->request->getPost('provinsi_id'),
                    'provinsi_nama' => $this->request->getPost('provinsi_nama'),
                    'date_created' => date('Y-m-d H:i:s'),
                ];

                $addressModel->insert($addressData);

                return $this->response->setJSON([
                    'status' => 'success',
                    'message' => 'Data Pasien ' . $patientData['name'] . ' Berhasil Disimpan',
                    'new_token' => csrf_hash()
                ]);
                // session()->setFlashdata('message', ['success', 'Data Berhasil Disimpan']);
                // return redirect()->to(site_url('dashboard'));
            } else {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Gagal menyimpan data pasien. Pastikan data yang dimasukkan valid.',
                    'new_token' => csrf_hash()
                ]);
                // return redirect()->back()->with('message', ['error', 'Gagal: ' . $errors]);
            }
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Terjadi kesalahan pada sistem. Silakan coba lagi nanti.',
                'new_token' => csrf_hash()
            ]);
        }
    }

    public function fetch()
    {
        // Menggunakan Service Request CI4
        $request = \Config\Services::request();

        // Mapping parameter dari DataTables
        $draw = $request->getPost('draw');
        $start = $request->getPost('start') ?? 0;
        $length = $request->getPost('length') ?? 10;
        $search = $request->getPost('search')['value'] ?? '';
        $order = $request->getPost('order');
        $columns = $request->getPost('columns');

        // Menentukan kolom pengurutan
        $orderBy = 'name'; // Default
        $orderMode = 'asc';
        if (!empty($order) && isset($columns[$order[0]['column']]['data'])) {
            $orderBy = $columns[$order[0]['column']]['data'];
            $orderMode = $order[0]['dir'];
        }

        // Inisialisasi Query Builder melalui Model
        $builder = $this->patientModel->builder();

        // Logic Filter Wilayah (Region)
        $region_session = session()->get('region_patient');
        if ($region_session !== 'all' && !empty($region_session)) {
            if (is_array($region_session)) {
                $builder->whereIn('region_id', $region_session);
            } else {
                $builder->where('region_id', $region_session);
            }
        }

        // Logic Pencarian (Search)
        if (!empty($search)) {
            $builder->groupStart()
                ->like('name', $search)
                ->orLike('phone', $search)
                ->groupEnd();
        }

        // Hitung Total Terfilter (sebelum limit)
        $totalFiltered = $builder->countAllResults(false);

        // Ambil Data dengan Sorting dan Limit
        $dataOutput = $builder->orderBy($orderBy, $orderMode)
            ->limit($length, $start)
            ->get()
            ->getResult();

        // Hitung Total Seluruh Data (tanpa filter)
        $totalData = $this->patientModel->countAllResults();

        $no = $start + 1;
        
        foreach ($dataOutput as &$value) {
            $value->no = $no;

            if (!empty($value->date)) {
                $tgl = date('d', strtotime($value->date));
                $bln = $this->bulan_indo[(int)date('m', strtotime($value->date))];
                $thn = date('Y', strtotime($value->date));
                $value->date = "$tgl $bln $thn";
            } else {
                $value->date = '-';
            }

            $value->action = '
                <div class="flex items-center justify-center gap-2">
                    <button type="button" 
                        onclick="show(' . $value->id . ')" 
                        title="Detail Data" 
                        class="group flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-400 shadow-sm transition-all hover:border-teal-200 hover:bg-teal-50 hover:text-teal-600">
                        <i class="fas fa-eye text-xs transition-transform group-hover:scale-110"></i>
                    </button>
                    <button type="button" 
                        onclick="destroy(' . $value->id . ')" 
                        title="Hapus Data" 
                        class="group flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-400 shadow-sm transition-all hover:border-red-200 hover:bg-red-50 hover:text-red-600">
                        <i class="fas fa-trash text-xs transition-transform group-hover:scale-110"></i>
                    </button>
                </div>';

            $no++;
        }

        $response = [
            "draw" => intval($draw),
            "recordsTotal" => intval($totalData),
            "recordsFiltered" => intval($totalFiltered),
            "data" => $dataOutput
        ];

        return $this->response->setJSON($response);
    }

    public function fetch2()
    {
        $db = db_connect();
        $request = \Config\Services::request();

        $limit = $this->request->getPost('length') ?? 10;
        $start = $this->request->getPost('start') ?? 0;
        $search = $request->getPost('search')['value'] ?? '';
        $region = $this->request->getPost('region');

        $builder = $db->table('patients p')
            ->select('
            p.id, p.name, p.phone, p.address, p.is_delete, p.region_id,
            r.name as name_region, 
            pa.desa_nama, pa.kecamatan_nama, pa.kabupaten_nama, pa.provinsi_nama,
            COUNT(h.id) AS visit_count,
            MAX(h.date) AS last_visit_date
        ')
            ->join('regions r', 'r.id = p.region_id', 'left')
            ->join('patient_address pa', 'pa.patient_id = p.id', 'left')
            ->join('histories h', 'h.patient_id = p.id AND h.is_delete = 0', 'left');
        // ->where('p.is_delete', 0);
        // ->limit($limit, $start);

        $active_region = session()->get('active_region');
        $region_session = session()->get('region_patient');
        
        // Smart Logic: Jika pencarian kosong, gunakan filter wilayah. Jika sedang mencari, buka akses lintas wilayah.
        if (empty($search)) {
            $region = ($region && $region !== 'all') ? $region : ($active_region !== 'all' ? $active_region : $region_session);
        } else {
            $region = 'all'; // Cari di seluruh database jika ada keyword
        }

        if (!empty($region) && $region !== 'all') {
            if (is_array($region)) {
                $builder->whereIn('p.region_id', $region);
            } else {
                $builder->where('p.region_id', $region);
            }
        }

        if (!empty($search)) {
            $builder->groupStart()->like('p.name', $search)->orLike('p.phone', $search)
                ->orLike('p.address', $search)
                ->orLike('p.id', $search)
                ->groupEnd();
        }

        $builder->groupBy([
            'p.id',
            'p.name',
            'p.phone',
            'p.address',
            'p.is_delete',
            'p.region_id',
            'r.name',
            'pa.desa_nama',
            'pa.kecamatan_nama',
            'pa.kabupaten_nama',
            'pa.provinsi_nama'
        ]);
        // $totalFiltered = $builder->countAllResults(false);
        $tempBuilder = clone $builder;
        $totalFiltered = $db->table('(' . $tempBuilder->getCompiledSelect() . ') AS temp_table')->countAllResults();

        $data = $builder->orderBy('p.id', 'ASC') // Pastikan urut dari yang terbaru
            ->limit($limit, $start)
            ->get()
            ->getResult();
        $totalData = $db->table('patients')->countAllResults();

        // $data = $builder->get()->getResult();
        $output = [];


        foreach ($data as $row) {
            $addressParts = array_filter([
                $row->address,
                $row->desa_nama,
                $row->kecamatan_nama,
                $row->kabupaten_nama,
                $row->provinsi_nama
            ]);
            $fullAddress = implode(', ', $addressParts);

            // Format tanggal ke bahasa Indonesia
            $dateFormatted = '-';
            if (!empty($row->last_visit_date)) {
                $tgl = date('d', strtotime($row->last_visit_date));
                $bln = $this->bulan_indo[(int)date('m', strtotime($row->last_visit_date))];
                $thn = date('Y', strtotime($row->last_visit_date));
                $dateFormatted = "$tgl $bln $thn";
            }

            $output[] = [
                "id" => $row->id,
                "name" => $row->name . ' (' . $row->phone . ')',
                "name_region" => $row->name_region ?? '-',
                "address" => $fullAddress,
                "date" => $dateFormatted,
                "visit_count" => $row->visit_count ?? 0,
                "action" => '
                <a href="' . site_url('patient/show/' . $row->id) . '" class="btn btn-primary btn-sm mr-1"><i class="fas fa-eye"></i></a>
                <button type="button" class="btn btn-danger btn-sm" onclick="destroy(' . "'" . $row->id . "'" . ')"><i class="fas fa-trash"></i></button>
            ',
                "is_delete" => $row->is_delete,
                "phone" => $row->phone
            ];
        }

        return $this->response->setJSON([
            "draw" => intval($this->request->getPost('draw')),
            "recordsTotal" => $totalData,
            "recordsFiltered" => $totalFiltered,
            "data" => $output
        ]);
    }

    private function getUserName($user_id)
    {
        if (empty($user_id)) {
            return '-';
        }
        $builder = $this->db->table('users');
        $user = $builder->select('realname')
            ->where('id', $user_id)
            ->get()
            ->getRow(); // Sama dengan row() di CI3
        return $user ? $user->realname : '-';
    }

    public function show($id = null)
    {
        $queue_id = $this->request->getGet('queue_id');
        $patientData = $this->patientModel->find($id);

        if (!$patientData) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Pasien dengan ID $id tidak ditemukan.");
        }
        $addressData = $this->db->table('patient_address') // Pastikan nama tabel benar
            ->where('patient_id', $id)
            ->get()
            ->getRowArray() ?? [];

        if (!$addressData) {
            $addressData = [
                'desa_id' => '',
                'desa_nama' => '',
                'kecamatan_id' => '',
                'kecamatan_nama' => '',
                'kabupaten_id' => '',
                'kabupaten_nama' => '',
                'provinsi_id' => '',
                'provinsi_nama' => '',
            ];
        }

        $historyRow = $this->db->table('histories')
            ->select('id')
            ->where([
                'patient_queue_id' => $queue_id,
                'is_delete' => 0
            ])
            ->get()
            ->getRow();
        $historyId = $historyRow ? $historyRow->id : null;

        $mAddress = new \App\modules\address\Models\MAddress();
        $mCountries = new \App\modules\countries\Models\MCountries();
        $mRegion = new \App\modules\region\Models\MRegion();
        $mTerapis = new \App\modules\terapis\Models\MTerapis();

        $region_patient = session()->get('region_patient');
        $allowed_regions = ($region_patient !== 'all') ? $region_patient : null;

        $data = [
            'title' => 'Detil Pasien',
            'patient' => $patientData,
            'address' => (object) $addressData,
            'alamat_patient' => $mAddress->asObject()->findAll(),
            'wilayah' => $mRegion->getData(null, $allowed_regions) ?? [],
            'negara' => $mCountries->asObject()->findAll(),
            'terapis' => $mTerapis->asObject()->findAll(),
            'resources' => $this->patientModel->get_resources(),


            'patient_id' => $id,
            'queue_id' => $queue_id,
            'history_id' => $historyId,
            'file_urls' => json_decode($patientData->url ?? '[]', true),
            'current_date' => date("Y-m-d"),
            'created_at' => !empty($patientData->created_at) ? date("j F Y H:i", strtotime($patientData->created_at)) : '-',
            'updated_at' => !empty($patientData->updated_at) ? date("j F Y H:i", strtotime($patientData->updated_at)) : '-',
            'created_by_name' => $this->getUserName($patientData->created_by ?? null),
            'updated_by_name' => $this->getUserName($patientData->updated_by ?? null),
            'realname' => $this->session->get('realname'),
            'role' => $this->session->get('role'),
            'regions_patient' => $region_patient,
            'msg' => $this->session->getFlashdata('message') ?? ['', '', ''],
        ];

        $data['has_updated'] = ($data['updated_at'] !== '-');

        return view('App\modules\patients\Views\show', $data);
    }

    public function history($id = null)
    {
        $patientData = $this->patientModel->find($id);

        if (!$patientData) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Pasien dengan ID $id tidak ditemukan.");
        }
        $addressData = $this->db->table('patient_address')
            ->where('patient_id', $id)
            ->get()
            ->getRowArray() ?? [];

        $mRegion = new \App\modules\region\Models\MRegion();
        $mCountries = new \App\modules\countries\Models\MCountries();
        $mTerapis = new \App\modules\terapis\Models\MTerapis();

        $region_patient = session()->get('region_patient');
        $allowed_regions = ($region_patient !== 'all') ? $region_patient : null;

        $data = [
            'title' => 'Riwayat Kunjungan: ' . esc($patientData->name),
            'patient' => $patientData,
            'address' => (object) $addressData,
            'wilayah' => $mRegion->getData(null, $allowed_regions) ?? [],
            'negara' => $mCountries->asObject()->findAll(),
            'terapis' => $mTerapis->asObject()->findAll(),
            'resources' => $this->patientModel->get_resources(),
            'patient_id' => $id,
            'queue_id' => '',
            'hide_add_button' => true,
            'role' => $this->session->get('role'),
            'realname' => $this->session->get('realname'),
            'current_date' => date("Y-m-d"),
            'created_at' => !empty($patientData->created_at) ? date("j F Y H:i", strtotime($patientData->created_at)) : '-',
            'updated_at' => !empty($patientData->updated_at) ? date("j F Y H:i", strtotime($patientData->updated_at)) : '-',
            'created_by_name' => $this->getUserName($patientData->created_by ?? null),
            'updated_by_name' => $this->getUserName($patientData->updated_by ?? null),
        ];

        $data['has_updated'] = ($data['updated_at'] !== '-');

        return view('App\modules\patients\Views\history', $data);
    }

    public function update_files()
    {
        $id = $this->request->getPost('id');
        if (!$id) {
            return redirect()->back()->with('message', ['error', 'danger', 'ID Pasien tidak valid']);
        }

        $patient = $this->patientModel->asArray()->find($id);
        if (!$patient) {
            return redirect()->back()->with('message', ['error', 'danger', 'Pasien tidak ditemukan']);
        }

        $existingFiles = [];
        if (!empty($patient['url'])) {
            $existingFiles = json_decode($patient['url'], true) ?? [];
        }
        $delete_files = $this->request->getPost('delete_files');
        if (!empty($delete_files)) {
            foreach ($delete_files as $index) {
                if (isset($existingFiles[$index])) {
                    $file_to_delete = $existingFiles[$index];

                    // Ubah URL menjadi path sistem (FCPATH)
                    $file_path = str_replace(base_url(), '', $file_to_delete);
                    $full_path = FCPATH . ltrim($file_path, '/');

                    if (file_exists($full_path)) {
                        unlink($full_path);
                    }
                    unset($existingFiles[$index]);
                }
            }
            $existingFiles = array_values($existingFiles);
        }

        $new_file_urls = [];
        $files = $this->request->getFileMultiple('userfiles');

        if ($files) {
            foreach ($files as $file) {
                if ($file->isValid() && !$file->hasMoved()) {
                    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp', 'application/pdf'];
                    if (in_array($file->getMimeType(), $allowedTypes) && $file->getSizeByUnit('kb') <= 10240) {

                        $newName = $file->getRandomName();

                        if ($file->move(FCPATH . 'patient_file', $newName)) {
                            $new_file_urls[] = $newName;
                            // $new_file_urls[] = 'patient_file/' . $newName;
                        }
                    }
                }
            }
        }

        $final_file_urls = array_merge($existingFiles, $new_file_urls);
        $updateData = [
            'url' => json_encode($final_file_urls),
            'updated_at' => date('Y-m-d H:i:s'),
            'updated_by' => session()->get('userId'),
            'phone' => (!empty($patient['phone'])) ? $patient['phone'] : "-"
        ];
        // dd($updateData);

        $update = $this->patientModel->update($id, $updateData);

        // Logging (Opsional)
        log_message('info', "Update file pasien ID $id. Data POST: " . json_encode($this->request->getPost()));

        if ($update) {
            return redirect()->to('patient/show/' . $id)->with('success', 'File pasien berhasil diperbarui');
        } else {
            return redirect()->back()->with('error', 'File pasien gagal diperbarui');
        }
    }


    public function destroy($id)
    {
        $this->db->table('patients')
            ->where('id', $id)
            ->update(['is_delete' => 1]);

        return $this->response->setJSON([
            'status' => true,
            'new_token' => csrf_hash()
        ]);
    }

    public function check_phone()
    {
        $phone = $this->request->getPost('phone');
        if (!$phone) {
            return $this->response->setJSON(['exists' => false, 'patients' => []]);
        }

        $phone628 = preg_replace('/^08/', '628', $phone);
        $phone08 = preg_replace('/^628/', '08', $phone);


        $patients = $this->patientModel
            ->whereIn('phone', [$phone08, $phone628])
            ->findAll();

        return $this->response->setJSON([
            'exists' => !empty($patients),
            'patients' => $patients,
            'new_token' => csrf_hash(),
        ]);
    }

     public function update($id = null)
    {
        // 1. Ambil ID dari segment URL atau Post (Fallback)
        $id = $id ?? $this->request->getPost('id');
        $patientModel = new \App\modules\patients\Models\MPatients();

        // 2. Ambil data lama
        $patient = $patientModel->find($id);
        if (!$patient) {
            return redirect()->back()->with('message', ['error', 'Pasien tidak ditemukan']);
        }

        // 3. Logika Tanggal (Created At)
        $existingCreatedAt = $patient->created_at;
        $existingDate = date('Y-m-d', strtotime($existingCreatedAt));
        $submittedDate = $this->request->getPost('visit_date');

        // 4. Handle File Lama (JSON)
        $existingFiles = empty($patient->url) ? [] : json_decode($patient->url, true);

        // 5. Hapus File yang dipilih
        $deleteFiles = $this->request->getPost('delete_files');
        if (!empty($deleteFiles)) {
            foreach ($deleteFiles as $index) {
                if (isset($existingFiles[$index])) {
                    $fileToDelete = $existingFiles[$index];
                    // Hapus domain base_url untuk mendapatkan path lokal
                    $relativeInfo = parse_url($fileToDelete);
                    $filePath = FCPATH . ltrim($relativeInfo['path'], '/');

                    if (file_exists($filePath)) {
                        unlink($filePath);
                    }
                    unset($existingFiles[$index]);
                }
            }
            $existingFiles = array_values($existingFiles); // Reindex
        }

        // 6. Handle Upload File Baru (CI4 Multiple Upload)
        $newFileUrls = [];
        if ($imageFiles = $this->request->getFiles()) {
            if (isset($imageFiles['userfiles'])) {
                foreach ($imageFiles['userfiles'] as $img) {
                    if ($img->isValid() && !$img->hasMoved()) {
                        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp', 'application/pdf'];
                        if (in_array($img->getMimeType(), $allowedTypes) && $img->getSizeByUnit('kb') <= 10240) {
                            $newName = $img->getRandomName();
                            $img->move(FCPATH . 'patient_file', $newName);
                            $newFileUrls[] = base_url('patient_file/' . $newName);
                        }
                    }
                }
            }
        }

        // Gabungkan file lama dan baru
        $finalFileUrls = array_merge($existingFiles, $newFileUrls);

        // 7. Siapkan Data Pasien
        $userData = session()->get('userId'); // Pastikan session CI4 sudah aktif

        $data = [
            'name' => $this->request->getPost('name'),
            'gender' => $this->request->getPost('gender'),
            'age' => $this->request->getPost('age') ?: null,
            'country_id' => $this->request->getPost('country_id'),
            'address' => $this->request->getPost('address') ?: null,
            'phone' => $this->request->getPost('phone') ?: null,
            'region_id' => $this->request->getPost('region_id'),
            'is_suspective' => $this->request->getPost('is_suspective') === 'on' ? 1 : 0,
            'domestic' => $this->request->getPost('domestic') === 'on' ? 1 : 0,
            'url' => json_encode($finalFileUrls),
            'created_at' => ($submittedDate && $submittedDate != $existingDate) ? $submittedDate . ' ' . date('H:i:s') : $existingCreatedAt,
            'updated_at' => date('Y-m-d H:i:s'),
            'updated_by' => $userData,
            'patient_information' => $this->request->getPost('patient_information') ?: null,
            'ket_suspect' => ($this->request->getPost('is_suspective') === 'on') ? $this->request->getPost('ket_rentan') : null
        ];

        $update = $patientModel->update($id, $data);

        $addressModel = new \App\modules\address\Models\MAddress();
        $addressData = [
            'patient_id' => $id,
            'desa_id' => $this->request->getPost('desa_id'),
            'desa_nama' => $this->request->getPost('desa_nama'),
            'kecamatan_id' => $this->request->getPost('kecamatan_id'),
            'kecamatan_nama' => $this->request->getPost('kecamatan_nama'),
            'kabupaten_id' => $this->request->getPost('kabupaten_id'),
            'kabupaten_nama' => $this->request->getPost('kabupaten_nama'),
            'provinsi_id' => $this->request->getPost('provinsi_id'),
            'provinsi_nama' => $this->request->getPost('provinsi_nama'),
            'date_updated' => date('Y-m-d H:i:s')
        ];

        // CI4 menggunakan upsert atau cek manual
        $existingAddress = $addressModel->where('patient_id', $id)->first();
        if ($existingAddress) {
            $addressModel->update($existingAddress['id'], $addressData);
        } else {
            $addressModel->insert($addressData);
        }

        // 10. Flash Message & Redirect
        if ($update) {
            session()->setFlashdata('message', ['success', 'Data Berhasil disimpan']);
        } else {
            session()->setFlashdata('message', ['error', 'Gagal menyimpan data']);
        }

        return redirect()->to('patient/show/' . $id);
    }

    public function print_pdf($patients)
    {
        set_time_limit(0);
        ini_set('memory_limit', '512M');
        // 1. Ambil Data (Eager Loaded dari Model)
        // $region_id = $this->request->getGet('region_id');
        // $dateRange = $this->request->getGet('date_range');
        // $start_date = null;
        // $end_date = null;

        // if (!empty($dateRange) && strpos($dateRange, ' - ') !== false) {
        //     $dates = explode(' - ', $dateRange);
        //     $start_date = date('Y-m-d', strtotime(trim($dates[0])));
        //     $end_date = date('Y-m-d', strtotime(trim($dates[1])));
        // }

        // // $patients = $this->patientModel->getAllData($region_id, null, 0, $start_date, $end_date);
        // $jenisKelamin = $this->jenisKelamin;
        $pdf = new \TCPDF('L', 'mm', 'A4', true, 'UTF-8', false);

        // Setup Metadata
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetTitle('Data Pasien');
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetMargins(10, 10, 10);
        $pdf->SetAutoPageBreak(TRUE, 15);

        $pdf->AddPage();

        // 3. Header Judul
        $pdf->SetFont('times', 'B', 14);
        $pdf->Cell(0, 10, 'LAPORAN DATA PASIEN', 0, 1, 'C');
        $pdf->Ln(5);

        $this->drawHeader($pdf);
        $pdf->SetFont('times', '', 8);
        $no = 1;

        while ($row = $patients->getUnbufferedRow()) {

            if ($pdf->GetY() > 175) {
                $pdf->AddPage();
                $this->drawHeader($pdf);
                $pdf->SetFont('times', '', 8);
            }

            $jumlahRM = $row->total_history ?? 0;
            // $gender   = $jenisKelamin[$row->gender] ?? $row->gender;
            $gender = ($row->gender == 'Man') ? 'L' : 'P';
            $alamat = trim(($row->desa_nama ?? '') . ' ' . ($row->kecamatan_nama ?? '') . ' ' . ($row->kabupaten_nama ?? ''));
            if (empty($alamat))
                $alamat = $row->address ?? '-';
            $pdf->SetFont('times', '', 8);

            // Render Row
            $pdf->MultiCell(10, 7, $no++, 1, 'C', 0, 0);
            $pdf->MultiCell(15, 7, $row->id, 1, 'C', 0, 0);
            $pdf->MultiCell(45, 7, $row->name, 1, 'L', 0, 0); // Nama rata kiri (L)
            $pdf->MultiCell(15, 7, $gender, 1, 'C', 0, 0);
            $pdf->MultiCell(12, 7, $row->age, 1, 'C', 0, 0);
            $pdf->MultiCell(30, 7, $row->name_region ?? '-', 1, 'L', 0, 0);
            $pdf->MultiCell(30, 7, $row->phone, 1, 'L', 0, 0);
            $pdf->MultiCell(55, 7, $row->address, 1, 'L', 0, 0); // Alamat rata kiri
            $pdf->MultiCell(30, 7, $row->last_visit ?? '-', 1, 'C', 0, 0);
            $pdf->MultiCell(15, 7, $row->total_history ?? 0, 1, 'C', 0, 1);
        }

        $this->response->setHeader('Content-Type', 'application/pdf');
        $pdf->Output('Laporan_Pasien_' . date('Ymd') . '.pdf', 'I');
        exit();
    }


    private function drawHeader($pdf)
    {
        $pdf->SetFont('times', 'B', 8);
        $pdf->SetFillColor(230, 230, 230);
        $pdf->MultiCell(10, 8, 'NO', 1, 'C', 1, 0);
        $pdf->MultiCell(15, 8, 'ID', 1, 'C', 1, 0);
        $pdf->MultiCell(45, 8, 'Nama Pasien', 1, 'C', 1, 0);
        $pdf->MultiCell(15, 8, 'L/P', 1, 'C', 1, 0);
        $pdf->MultiCell(12, 8, 'Usia', 1, 'C', 1, 0);
        $pdf->MultiCell(30, 8, 'Wilayah', 1, 'C', 1, 0);
        $pdf->MultiCell(30, 8, 'No. Telp', 1, 'C', 1, 0);
        $pdf->MultiCell(55, 8, 'Alamat (Desa/Kec/Kab)', 1, 'C', 1, 0);
        $pdf->MultiCell(30, 8, 'Visit Terakhir', 1, 'C', 1, 0);
        $pdf->MultiCell(15, 8, 'RM', 1, 'C', 1, 1);
        $pdf->SetFont('times', '', 8);
    }

   

    public function export($data)
    {
        set_time_limit(0);
        ini_set('memory_limit', '512M');
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Data Pasien');

        // --- 1. JUDUL LAPORAN ---
        $sheet->setCellValue('A1', 'LAPORAN DATA PASIEN BONEHACKER');
        $sheet->mergeCells('A1:K1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $sheet->setCellValue('A2', 'Dicetak pada: ' . date('d/m/Y H:i:s'));
        $sheet->mergeCells('A2:K2');
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // --- 2. HEADER TABEL ---
        $headers = ['No', 'ID Pasien', 'Nama Pasien', 'L/P', 'Usia', 'Alamat', 'No. Telp', 'Rentan', 'Wilayah', 'Visit Terakhir', 'Total RM'];
        $sheet->fromArray($headers, NULL, 'A4');

        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 
                'startColor' => ['rgb' => '1E40AF'] // Blue-800
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
        ];
        $sheet->getStyle('A4:K4')->applyFromArray($headerStyle);
        $sheet->getRowDimension(4)->setRowHeight(25);

        // --- 3. ISI DATA ---
        $row = 5;
        $no = 1;
        while ($item = $data->getUnbufferedRow()) {
            $sheet->setCellValue('A' . $row, $no);
            $sheet->setCellValue('B' . $row, $item->id);
            $sheet->setCellValue('C' . $row, $item->name);
            $sheet->setCellValue('D' . $row, ($item->gender == 'Man' ? 'L' : 'P'));
            $sheet->setCellValue('E' . $row, $item->age);
            $sheet->setCellValue('F' . $row, $item->address);
            $sheet->setCellValue('G' . $row, $item->phone);
            $sheet->setCellValue('H' . $row, $item->is_suspective ? 'Ya' : 'Tidak');
            $sheet->setCellValue('I' . $row, $item->name_region);
            $sheet->setCellValue('J' . $row, $item->last_visit ? date('d/m/Y', strtotime($item->last_visit)) : '-');
            $sheet->setCellValue('K' . $row, $item->total_history);

            // Row styling
            $sheet->getStyle('A' . $row . ':K' . $row)->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        'color'       => ['rgb' => 'E2E8F0'],
                    ],
                ],
                'alignment' => [
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                ],
            ]);

            // Alignment spesifik
            $sheet->getStyle('A' . $row . ':B' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('D' . $row . ':E' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('H' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('J' . $row . ':K' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

            if (isset($item->is_delete) && $item->is_delete == 1) {
                $sheet->getStyle('A' . $row . ':K' . $row)->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFFFCCCC');
            }

            $row++;
            $no++;
        }

        // Auto size columns
        foreach (range('A', 'K') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // DOWNLOAD PROSES
        $filename = 'Data_Patient_' . date('Ymd') . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        exit();
    }

    public function export_data()
    {
        if (session()->get('role') !== 'superadmin') {
            return redirect()->to(base_url('beranda'))->with('message', ['error', 'Anda tidak memiliki hak akses untuk ekspor data.']);
        }
        $type = $this->request->getGet('type');
        $region_id = $this->request->getGet('region_id');
        $region_session = session()->get('region_patient');

        if (empty($region_id) || $region_id === 'all') {
            $region_id = $region_session;
        }
        $periode = $this->request->getGet('periode');
        $start_date = null;
        $end_date = null;

        switch ($periode) {
            case 'today':
                $start_date = date('Y-m-d');
                $end_date = date('Y-m-d');
                break;
            case 'last_7_days':
                $start_date = date('Y-m-d', strtotime('-7 days'));
                $end_date = date('Y-m-d');
                break;
            case 'this_month':
                $start_date = date('Y-m-01'); 
                $end_date = date('Y-m-t');    
                break;
            case 'custom':
                $start_date = $this->request->getGet('start_date');
                $end_date = $this->request->getGet('end_date');
                break;
            default:
                $start_date = null;
                $end_date = null;
                break;
        }

        if ($periode === 'custom' && (empty($start_date) || empty($end_date))) {
            return redirect()->back()->with('error', 'Harap lengkapi rentang tanggal kustom.');
        }

        $data = $this->patientModel->getAllData($region_id, null, 0, $start_date, $end_date);
        if ($type === 'pdf') {
            return $this->print_pdf($data);
        } else {
            return $this->export($data);
        }
    }
}

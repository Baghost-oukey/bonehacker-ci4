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
                    $newName = $file->getRandomName();
                    $file->move(ROOTPATH . 'public/patient_file/', $newName);
                    $file_urls[] = base_url('patient_file/' . $newName);
                }
            }
        }

        $domestic = ($this->request->getPost('domestic') === 'dalam_negeri') ? 1 : 0;
        $userId   = $this->session->get('userId');

        // Data Pasien
        $patientData = [
            'name'                => $this->request->getPost('name'),
            'gender'              => $this->request->getPost('gender'),
            'age'                 => $this->request->getPost('age') ?: 0,
            'country_id'          => $this->request->getPost('country_id'),
            'address'             => $this->request->getPost('address') ?: "",
            // 'phone'            => $this->request->getPost('phone') ?: "",
            'phone'               => (string) ($this->request->getPost('phone') ?? ""),
            'region_id'           => $this->request->getPost('region_id'),
            'is_suspective'       => $this->request->getPost('is_suspective') === 'on' ? 1 : 0,
            'domestic'            => $domestic,
            'url'                 => json_encode($file_urls),
            'created_by'          => $userId,
            'patient_information' => $this->request->getPost('patient_information') ?: "",
            'ket_suspect'         => $this->request->getPost('ket_rentan') ?: "",
        ];

        $visitDate = $this->request->getPost('visit_date');
        if ($visitDate) {
            $formattedDate = date('Y-m-d', strtotime($visitDate));
            $patientData['created_at'] = $formattedDate . ' ' . date('H:i:s');
        } else {
            $patientData['created_at'] = date('Y-m-d H:i:s');
        }

        if ($this->patientModel->insert($patientData)) {
            $patientId = $this->patientModel->getInsertID();
            $addressModel = new MAddress();
            $addressData = [
                'patient_id'     => $patientId,
                'desa_id'        => $this->request->getPost('desa_id'),
                'desa_nama'      => $this->request->getPost('desa_nama'),
                'kecamatan_id'   => $this->request->getPost('kecamatan_id'),
                'kecamatan_nama' => $this->request->getPost('kecamatan_nama'),
                'kabupaten_id'   => $this->request->getPost('kabupaten_id'),
                'kabupaten_nama' => $this->request->getPost('kabupaten_nama'),
                'provinsi_id'    => $this->request->getPost('provinsi_id'),
                'provinsi_nama'  => $this->request->getPost('provinsi_nama'),
            ];

            $addressModel->insert($addressData);

            session()->setFlashdata('message', ['success', 'Data Berhasil Disimpan']);
        } else {
            // Jika insert pasien gagal
            session()->setFlashdata('message', ['error', 'Gagal menyimpan data pasien']);
        }

        return redirect()->to(site_url('dashboard'));
    }

    public function fetch()
    {
        // Menggunakan Service Request CI4
        $request = \Config\Services::request();

        // Mapping parameter dari DataTables
        $draw   = $request->getPost('draw');
        $start  = $request->getPost('start') ?? 0;
        $length = $request->getPost('length') ?? 10;
        $search = $request->getPost('search')['value'] ?? '';
        $order  = $request->getPost('order');
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

            $value->date = !empty($value->date) ? date('d-m-Y', strtotime($value->date)) : '-';

            $value->action = '
            <a href="' . site_url('patient/show/' . $value->id) . '" class="btn btn-primary btn-sm mr-1">
                <i class="fas fa-eye"></i>
            </a>
            <button type="button" class="btn btn-danger btn-sm" onclick="destroy(\'' . $value->id . '\')">
                <i class="fas fa-trash"></i>
            </button>';

            $no++;
        }

        $response = [
            "draw"            => intval($draw),
            "recordsTotal"    => intval($totalData),
            "recordsFiltered" => intval($totalFiltered),
            "data"            => $dataOutput
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
            p.*, 
            r.name as name_region, 
            pa.desa_nama, pa.kecamatan_nama, pa.kabupaten_nama, pa.provinsi_nama,
            (
                SELECT MAX(date) 
                FROM histories h 
                WHERE h.patient_id = p.id AND h.is_delete = 0
            ) AS date,
            (
                SELECT COUNT(h.id)
                FROM histories h 
                WHERE h.patient_id = p.id AND h.is_delete = 0
            ) AS visit_count
        ')
            ->join('regions r', 'r.id = p.region_id', 'left')
            ->join('patient_address pa', 'pa.patient_id = p.id', 'left');
        // ->limit($limit, $start);

        if (!empty($region)) {
            $builder->where('p.region_id', $region);
        }

        if (!empty($search)) {
            $builder->groupStart()->like('p.name', $search)->orLike('p.phone', $search)
                ->orLike('p.address', $search)
                ->orLike('p.id', $search)
                ->groupEnd();
        }
        $totalFiltered = $builder->countAllResults(false);
        $data = $builder->limit($limit, $start)->get()->getResult();
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

            $output[] = [
                "id"          => $row->id,
                "name"        => $row->name . ' (' . $row->phone . ')',
                "name_region" => $row->name_region ?? '-',
                "address"     => $fullAddress,
                "date"        => !empty($row->date) ? date('d-m-Y', strtotime($row->date)) : '-', // format_tanggal manual
                "visit_count" => $row->visit_count ?? 0,
                "action"      => '
                <a href="' . site_url('patient/show/' . $row->id) . '" class="btn btn-primary btn-sm mr-1"><i class="fas fa-eye"></i></a>
                <button type="button" class="btn btn-danger btn-sm" onclick="destroy(' . "'" . $row->id . "'" . ')"><i class="fas fa-trash"></i></button>
            ',
                "is_delete"   => $row->is_delete,
                "phone"       => $row->phone
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

        // Jika data alamat tidak ditemukan, inisialisasi dengan string kosong agar view tidak error
        if (!$addressData) {
            $addressData = [
                'desa_id'        => '',
                'desa_nama'      => '',
                'kecamatan_id'   => '',
                'kecamatan_nama' => '',
                'kabupaten_id'   => '',
                'kabupaten_nama' => '',
                'provinsi_id'    => '',
                'provinsi_nama'  => '',
            ];
        }

        $historyRow = $this->db->table('histories')
            ->select('id')
            ->where([
                'patient_queue_id' => $queue_id,
                'is_delete'        => 0
            ])
            ->get()
            ->getRow();
        $historyId = $historyRow ? $historyRow->id : null;

        $mAddress   = new \App\modules\address\Models\MAddress();
        $mCountries = new \App\modules\countries\Models\MCountries();
        $mRegion    = new \App\modules\region\Models\MRegion();
        $mTerapis   = new \App\modules\terapis\Models\MTerapis();

        $regions_patient = json_decode($this->session->get('regions_patient') ?? '[]', true);

        $data = [
            'title'           => 'Detil Pasien',
            'patient'         => $patientData,
            'address'         => (object)$addressData,
            'alamat_patient'  => $mAddress->asObject()->findAll(),
            'wilayah'         => $mRegion->asObject()->findAll(),
            'negara'          => $mCountries->asObject()->findAll(),
            'terapis'         => $mTerapis->asObject()->findAll(),
            'resources'       => $this->patientModel->get_resources(),


            'patient_id'      => $id,
            'queue_id'        => $queue_id,
            'history_id'      => $historyId,
            'file_urls'       => json_decode($patientData->url ?? '[]', true),
            'current_date'    => date("Y-m-d"),
            'created_at'      => !empty($patientData->created_at) ? date("j F Y H:i", strtotime($patientData->created_at)) : '-',
            'updated_at'      => !empty($patientData->updated_at) ? date("j F Y H:i", strtotime($patientData->updated_at)) : '-',
            'created_by_name' => $this->getUserName($patientData->created_by ?? null),
            'updated_by_name' => $this->getUserName($patientData->updated_by ?? null),
            'realname'        => $this->session->get('realname'),
            'role'            => $this->session->get('role'),
            'regions_patient' => [$regions_patient],
            'msg'             => $this->session->getFlashdata('message') ?? ['', '', ''],
        ];

        $data['has_updated'] = ($data['updated_at'] !== '-');

        return view('App\modules\patients\Views\show', $data);
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
                    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'application/pdf'];
                    if (in_array($file->getMimeType(), $allowedTypes) && $file->getSizeByUnit('kb') <= 2048) {

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
            'url'        => json_encode($final_file_urls),
            'updated_at' => date('Y-m-d H:i:s'),
            'updated_by' => session()->get('userId'),
            'phone'      => (!empty($patient['phone'])) ? $patient['phone'] : "-"
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
        if ($this->patientModel->destroy($id)) {
            $this->session->setFlashdata('message', ['success', 'Data Berhasil dihapus']);
    
            return $this->response->setJSON(['status' => true]);
        }

        return $this->response->setJSON(['status' => false], 500);
    }

    public function check_phone()
    {
        $phone = $this->request->getPost('phone');
        if (!$phone) {
            return $this->response->setJSON(['exists' => false, 'patients' => []]);
        }

        $phone628 = preg_replace('/^08/', '628', $phone);
        $phone08  = preg_replace('/^628/', '08', $phone);


        $patients = $this->patientModel
            ->whereIn('phone', [$phone08, $phone628])
            ->findAll();

        return $this->response->setJSON([
            'exists'   => !empty($patients),
            'patients' => $patients
        ]);
    }

    public function print_pdf()
    {
        // 1. Ambil Data (Eager Loaded dari Model)
        $region_id = $this->request->getGet('region_id');
        $patients = $this->patientModel->getAllData($region_id);
        $jenisKelamin = $this->jenisKelamin;

        // 2. Inisialisasi TCPDF (Ubah ke Landscape 'L' agar muat banyak kolom)
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

        foreach ($patients as $patient) {
            $row = (object) $patient;
            if ($pdf->GetY() > 180) {
                $pdf->AddPage();
                $this->drawHeader($pdf);
            }

            // Ambil data yang sudah dihitung di model (total_history)
            $jumlahRM = $row->total_history ?? 0;
            $gender   = $jenisKelamin[$row->gender] ?? $row->gender;

            // Render Row
            $pdf->MultiCell(10, 7, $no++, 1, 'C', 0, 0);
            $pdf->MultiCell(12, 7, $row->id, 1, 'C', 0, 0);
            $pdf->MultiCell(35, 7, $row->name, 1, 'L', 0, 0);
            $pdf->MultiCell(20, 7, $gender, 1, 'C', 0, 0);
            $pdf->MultiCell(10, 7, $row->age, 1, 'C', 0, 0);
            $pdf->MultiCell(25, 7, $row->name_region ?? '-', 1, 'L', 0, 0);
            $pdf->MultiCell(25, 7, $row->phone, 1, 'L', 0, 0);
            $pdf->MultiCell(30, 7, $row->desa_nama ?? '-', 1, 'L', 0, 0);
            $pdf->MultiCell(30, 7, $row->kecamatan_nama ?? '-', 1, 'L', 0, 0);
            $pdf->MultiCell(30, 7, $row->kabupaten_nama ?? '-', 1, 'L', 0, 0);
            $pdf->MultiCell(15, 7, ($row->is_suspective ? 'Ya' : 'Tdk'), 1, 'C', 0, 0);
            $pdf->MultiCell(15, 7, $jumlahRM, 1, 'C', 0, 1);
        }

        // 6. Output
        $this->response->setHeader('Content-Type', 'application/pdf');
        $pdf->Output('Laporan_Pasien_' . date('Ymd') . '.pdf', 'I');
        exit();
    }


    private function drawHeader($pdf)
    {
        $pdf->SetFont('times', 'B', 8);
        $pdf->SetFillColor(230, 230, 230);
        $pdf->MultiCell(10, 8, 'NO', 1, 'C', 1, 0);
        $pdf->MultiCell(12, 8, 'ID', 1, 'C', 1, 0);
        $pdf->MultiCell(35, 8, 'Nama', 1, 'C', 1, 0);
        $pdf->MultiCell(20, 8, 'Gender', 1, 'C', 1, 0);
        $pdf->MultiCell(10, 8, 'Usia', 1, 'C', 1, 0);
        $pdf->MultiCell(25, 8, 'Wilayah', 1, 'C', 1, 0);
        $pdf->MultiCell(25, 8, 'No. Telp', 1, 'C', 1, 0);
        $pdf->MultiCell(30, 8, 'Desa', 1, 'C', 1, 0);
        $pdf->MultiCell(30, 8, 'Kecamatan', 1, 'C', 1, 0);
        $pdf->MultiCell(30, 8, 'Kabupaten', 1, 'C', 1, 0);
        $pdf->MultiCell(15, 8, 'Rentan', 1, 'C', 1, 0);
        $pdf->MultiCell(15, 8, 'RM', 1, 'C', 1, 1);

        $pdf->SetFont('times', '', 8);
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
                        $newName = $img->getRandomName();
                        $img->move(FCPATH . 'patient_file', $newName);
                        $newFileUrls[] = base_url('patient_file/' . $newName);
                    }
                }
            }
        }

        // Gabungkan file lama dan baru
        $finalFileUrls = array_merge($existingFiles, $newFileUrls);

        // 7. Siapkan Data Pasien
        $userData = session()->get('userId'); // Pastikan session CI4 sudah aktif

        $data = [
            'name'                => $this->request->getPost('name'),
            'gender'              => $this->request->getPost('gender'),
            'age'                 => $this->request->getPost('age') ?: null,
            'country_id'          => $this->request->getPost('country_id'),
            'address'             => $this->request->getPost('address') ?: null,
            'phone'               => $this->request->getPost('phone') ?: null,
            'region_id'           => $this->request->getPost('region_id'),
            'is_suspective'       => $this->request->getPost('is_suspective') === 'on' ? 1 : 0,
            'domestic'            => $this->request->getPost('domestic') === 'on' ? 1 : 0,
            'url'                 => json_encode($finalFileUrls),
            'created_at'          => ($submittedDate && $submittedDate != $existingDate) ? $submittedDate . ' ' . date('H:i:s') : $existingCreatedAt,
            'updated_at'          => date('Y-m-d H:i:s'),
            'updated_by'          => $userData,
            'patient_information' => $this->request->getPost('patient_information') ?: null,
            'ket_suspect'         => ($this->request->getPost('is_suspective') === 'on') ? $this->request->getPost('ket_rentan') : null
        ];

        $update = $patientModel->update($id, $data);

        $addressModel = new \App\modules\address\Models\MAddress();
        $addressData = [
            'patient_id'     => $id,
            'desa_id'        => $this->request->getPost('desa_id'),
            'desa_nama'      => $this->request->getPost('desa_nama'),
            'kecamatan_id'   => $this->request->getPost('kecamatan_id'),
            'kecamatan_nama' => $this->request->getPost('kecamatan_nama'),
            'kabupaten_id'   => $this->request->getPost('kabupaten_id'),
            'kabupaten_nama' => $this->request->getPost('kabupaten_nama'),
            'provinsi_id'    => $this->request->getPost('provinsi_id'),
            'provinsi_nama'  => $this->request->getPost('provinsi_nama'),
            'date_updated'   => date('Y-m-d H:i:s')
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

    public function export()
    {
        set_time_limit(0);
        ini_set('memory_limit', '512M');

        $region_id = $this->request->getGet('region_id');
        $data = $this->patientModel->getAllData($region_id);

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $headers = ['No', 'ID Pasien', 'Name', 'Gender', 'Age', 'Address', 'Phone', 'Rentan', 'Region', 'Desa', 'Kecamatan', 'Kabupaten', 'Date', 'Total RM'];
        $sheet->fromArray($headers, NULL, 'A1');

        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '2E7D32']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]
        ];
        $sheet->getStyle('A1:N1')->applyFromArray($headerStyle);

        $row = 2;
        $no = 1;
        foreach ($data as $item) {
            $sheet->setCellValue('A' . $row, $no);
            $sheet->setCellValue('B' . $row, $item->id);
            $sheet->setCellValue('C' . $row, $item->name);
            $sheet->setCellValue('D' . $row, $item->gender);
            $sheet->setCellValue('E' . $row, $item->age);
            $sheet->setCellValue('F' . $row, $item->address);
            $sheet->setCellValue('G' . $row, $item->phone);
            $sheet->setCellValue('H' . $row, $item->is_suspective ? 'Ya' : 'Tidak');
            $sheet->setCellValue('I' . $row, $item->name_region);
            $sheet->setCellValue('J' . $row, $item->desa_nama);
            $sheet->setCellValue('K' . $row, $item->kecamatan_nama);
            $sheet->setCellValue('L' . $row, $item->kabupaten_nama);
            $sheet->setCellValue('M' . $row, $item->last_visit);
            $sheet->setCellValue('N' . $row, $item->total_history);

            if (isset($item->is_delete) && $item->is_delete == 1) {
                $sheet->getStyle('A' . $row . ':N' . $row)->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFFFCCCC'); // Merah sangat muda agar tetap terbaca
            }

            $row++;
            $no++;
        }

        $widths = ['A' => 5, 'B' => 10, 'C' => 25, 'D' => 10, 'E' => 8, 'F' => 30, 'G' => 15, 'H' => 10, 'I' => 15, 'J' => 15, 'K' => 15, 'L' => 15, 'M' => 18, 'N' => 10];
        foreach ($widths as $col => $w) {
            $sheet->getColumnDimension($col)->setWidth($w);
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
}

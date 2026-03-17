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
            // 'phone'               => $this->request->getPost('phone') ?: "",
            'phone' => (string) ($this->request->getPost('phone') ?? ""),
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

            session()->setFlashdata('msg', ['success', 'Data Berhasil Disimpan']);
        } else {
            // Jika insert pasien gagal
            session()->setFlashdata('msg', ['error', 'Gagal menyimpan data pasien']);
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
        if ($patient) {
            return redirect()->back()->with('message', ['error', 'danger', 'Pasien tidak ditemukan']);
        }

        $existingFiles = [];
        if (!empty($patient['url'])) {
            $existingFiles = json_decode($patient['url'], true) ?? [];
        }
        $delete_files = $this->request->getPost('delete_files');
        if (!empty($delete_files)) {
            foreach ($delete_files as $index) {
                if (isset($existing_files[$index])) {
                    $file_to_delete = $existing_files[$index];

                    // Ubah URL menjadi path sistem (FCPATH)
                    $file_path = str_replace(base_url(), '', $file_to_delete);
                    $full_path = FCPATH . ltrim($file_path, '/');

                    if (file_exists($full_path)) {
                        unlink($full_path);
                    }
                    unset($existing_files[$index]);
                }
            }
            $existing_files = array_values($existing_files);
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
                            $new_file_urls[] = base_url('patient_file/' . $newName);
                        }
                    }
                }
            }
        }

        $final_file_urls = array_merge($existing_files, $new_file_urls);
        $updateData = [
            'url'        => json_encode($final_file_urls),
            'updated_at' => date('Y-m-d H:i:s'),
            'updated_by' => session()->get('userId')
        ];

        $update = $this->patientModel->update($id, $updateData);

        // Logging (Opsional)
        log_message('info', "Update file pasien ID $id. Data POST: " . json_encode($this->request->getPost()));

        if ($update) {
            return redirect()->to('patient/show/' . $id)->with('message', [1, 'success', 'File pasien berhasil diperbarui']);
        } else {
            return redirect()->back()->with('message', [0, 'danger', 'File pasien gagal diperbarui']);
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
        // 1. Ambil Parameter & Data
        $region_id = $this->request->getGet('region_id');
        $patients = $this->patientModel->getAllData($region_id);

        // Inisialisasi Model History (untuk menghitung rekam medis)
        $historyModel = new \App\modules\history\Models\MHistory();

        // Gunakan property jenisKelamin yang sudah didefinisikan di controller
        $jenisKelamin = $this->jenisKelamin;

        // 2. Inisialisasi TCPDF
        // TCPDF akan otomatis terload jika diinstal via composer
        $pdf = new \TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);

        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Medical Application');
        $pdf->SetTitle('Data Pasien');

        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetMargins(8, 10, 8, true);
        $pdf->SetAutoPageBreak(TRUE, 10);

        $pdf->AddPage();

        // 3. Header Tabel
        $pdf->SetFont('times', 'B', 12);
        $pdf->Cell(0, 10, 'Data Pasien', 0, 1, 'C');
        $pdf->Ln(4);

        $pdf->SetFont('times', 'B', 8);
        $pdf->SetFillColor(240, 240, 240);

        // MultiCell(width, height, text, border, align, fill, next_line)
        $pdf->MultiCell(10, 8, 'NO', 1, 'C', 1, 0);
        $pdf->MultiCell(10, 8, 'ID', 1, 'C', 1, 0);
        $pdf->MultiCell(20, 8, 'Nama', 1, 'C', 1, 0);
        $pdf->MultiCell(15, 8, 'Jenis Kelamin', 1, 'C', 1, 0);
        $pdf->MultiCell(8, 8, 'Usia', 1, 'C', 1, 0);
        $pdf->MultiCell(16, 8, 'Wilayah', 1, 'C', 1, 0);
        $pdf->MultiCell(15, 8, 'No. Telp', 1, 'C', 1, 0);
        $pdf->MultiCell(24, 8, 'Alamat', 1, 'C', 1, 0);
        $pdf->MultiCell(16, 8, 'Desa', 1, 'C', 1, 0);
        $pdf->MultiCell(16, 8, 'Kecamatan', 1, 'C', 1, 0);
        $pdf->MultiCell(18, 8, 'Kabupaten', 1, 'C', 1, 0);
        $pdf->MultiCell(15, 8, 'Rentan', 1, 'C', 1, 0);
        $pdf->MultiCell(11, 8, 'RM', 1, 'C', 1, 1);

        // 4. Baris Data
        $pdf->SetFont('times', '', 8);
        $no = 1;

        foreach ($patients as $patient) {
            // Konversi ke object jika perlu
            if (is_array($patient)) $patient = (object) $patient;

            $jumlahRekamMedis = $historyModel->count_histories_by_patient_id($patient->id);

            // Cek apakah butuh halaman baru
            if ($pdf->GetY() + 8 > $pdf->getPageHeight() - 10) {
                $pdf->AddPage();
                // Opsional: Cetak header tabel lagi di halaman baru
            }

            // Ambil label jenis kelamin dengan aman
            $genderLabel = $jenisKelamin[$patient->gender] ?? $patient->gender;

            $pdf->MultiCell(10, 8, $no++, 1, 'C', 0, 0);
            $pdf->MultiCell(10, 8, $patient->id, 1, 'C', 0, 0);
            $pdf->MultiCell(20, 8, $patient->name, 1, 'L', 0, 0);
            $pdf->MultiCell(15, 8, $genderLabel, 1, 'C', 0, 0);
            $pdf->MultiCell(8, 8, $patient->age, 1, 'C', 0, 0);
            $pdf->MultiCell(16, 8, $patient->name_region ?? '-', 1, 'L', 0, 0);
            $pdf->MultiCell(15, 8, $patient->phone, 1, 'L', 0, 0);
            $pdf->MultiCell(24, 8, $patient->address ?? '-', 1, 'L', 0, 0);
            $pdf->MultiCell(16, 8, $patient->desa_nama ?? '-', 1, 'L', 0, 0);
            $pdf->MultiCell(16, 8, $patient->kecamatan_nama ?? '-', 1, 'L', 0, 0);
            $pdf->MultiCell(18, 8, $patient->kabupaten_nama ?? '-', 1, 'L', 0, 0);
            $pdf->MultiCell(15, 8, ($patient->is_suspective ? 'Ya' : 'Tidak'), 1, 'C', 0, 0);
            $pdf->MultiCell(11, 8, $jumlahRekamMedis, 1, 'C', 0, 1);
        }

        // 5. Output
        $this->response->setHeader('Content-Type', 'application/pdf');
        $pdf->Output('all_patient_data.pdf', 'I');
        exit();
    }

    public function export()
    {
        // 1. Ambil Parameter (Input GET)
        $region_id = $this->request->getGet('region_id');

        // 2. Inisialisasi Model History (Karena PatientModel sudah ada di construct)
        $historyModel = new \App\modules\history\Models\MHistory();

        // 3. Ambil data dari model
        $data = $this->patientModel->getAllData($region_id);

        // 4. Inisialisasi Spreadsheet
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // 5. Header Tabel
        $headers = [
            'A1' => 'No',
            'B1' => 'ID Pasien',
            'C1' => 'Name',
            'D1' => 'Gender',
            'E1' => 'Age',
            'F1' => 'Address',
            'G1' => 'Phone',
            'H1' => 'Pasien Rentan',
            'I1' => 'Region Name',
            'J1' => 'Desa',
            'K1' => 'Kecamatan',
            'L1' => 'Kabupaten',
            'M1' => 'Date',
            'N1' => 'Jumlah Rekam Medis'
        ];

        foreach ($headers as $cell => $value) {
            $sheet->setCellValue($cell, $value);
        }

        // 6. Styling Header
        $headerStyle = [
            'font' => ['bold' => true, 'size' => 12],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
            'fill' => [
                'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'B6D7A8'],
            ],
            'borders' => [
                'allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN],
            ],
        ];
        $sheet->getStyle('A1:N1')->applyFromArray($headerStyle);

        // 7. Loop Data
        $row = 2;
        $no = 1;
        foreach ($data as $item) {
            // Pastikan item diperlakukan sebagai object
            if (is_array($item)) $item = (object) $item;

            // Highlight Merah jika is_delete = 1
            if (isset($item->is_delete) && $item->is_delete == 1) {
                $sheet->getStyle('A' . $row . ':N' . $row)
                    ->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setARGB('FFFF0000'); // Merah (Alpha ditambahkan di awal)
            }

            // Hitung jumlah rekam medis dari model histories
            $jumlahRekamMedis = $historyModel->count_histories_by_patient_id($item->id);

            $sheet->setCellValue('A' . $row, $no);
            $sheet->setCellValue('B' . $row, $item->id);
            $sheet->setCellValue('C' . $row, $item->name);
            $sheet->setCellValue('D' . $row, $item->gender);
            $sheet->setCellValue('E' . $row, $item->age);
            $sheet->setCellValue('F' . $row, $item->address);
            $sheet->setCellValue('G' . $row, $item->phone);
            $sheet->setCellValue('H' . $row, $item->is_suspective ? 'Ya' : 'Tidak');
            $sheet->setCellValue('I' . $row, $item->name_region ?? '-');
            $sheet->setCellValue('J' . $row, $item->desa_nama ?? '-');
            $sheet->setCellValue('K' . $row, $item->kecamatan_nama ?? '-');
            $sheet->setCellValue('L' . $row, $item->kabupaten_nama ?? '-');
            $sheet->setCellValue('M' . $row, $item->date ?? '-');
            $sheet->setCellValue('N' . $row, $jumlahRekamMedis);

            $row++;
            $no++;
        }

        // 8. Auto-size kolom agar rapi
        foreach (range('A', 'N') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        // 9. Proses Download
        $filename = 'Data_Patient_' . date('Y-m-d') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save('php://output');
        exit();
    }
}

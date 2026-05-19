<?php

namespace App\Modules\Transaksi\Controllers;

use App\Controllers\BaseController;
use App\modules\transaksi\Models\MTransaksi;
use CodeIgniter\HTTP\ResponseInterface;
use Dompdf\Dompdf;
use Dompdf\Options;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class TransaksiController extends BaseController
{
    protected $model_transaksi;

    public function __construct()
    {
        $this->model_transaksi = new MTransaksi();
    }
    public function index()
    {
        $db = \Config\Database::connect();
        $role = session()->get('role');
        $is_admin = ($role === 'admin');

        $region_patient = session()->get('region_patient');
        $filter_region = ($region_patient !== 'all' && !empty($region_patient)) ? $region_patient : null;
        $list_regions = $db->table('regions')->select('id, name')->where('is_active', 1);
        if ($filter_region) {
            if (is_array($filter_region)) {
                $list_regions->whereIn('id', $filter_region);
            } else {
                $list_regions->where('id', $filter_region);
            }
        }
        $list_regions = $list_regions->get()->getResultArray();

        $monthInput = $this->request->getGet('month') ?: date('Y-m');
        if ($is_admin) {
            $startDate = date('Y-m-d');
            $endDate = date('Y-m-d');
        } else {
            $startDate = $monthInput . '-01';
            $endDate = date('Y-m-t', strtotime($startDate));
        }
        
        // Pemasukan Periode
        $incomeBuilder = $db->table('transaksi')->selectSum('nominal')
            ->where('DATE(created_at) >=', $startDate)
            ->where('DATE(created_at) <=', $endDate)
            ->where('status', 'active')
            ->where('type', 'income');
        if (is_array($filter_region)) { $incomeBuilder->whereIn('region_id', $filter_region); }
        elseif ($filter_region) { $incomeBuilder->where('region_id', $filter_region); }
        $total_income = $incomeBuilder->get()->getRow()->nominal ?? 0;

        // Pengeluaran Periode
        $expenseBuilder = $db->table('transaksi')->selectSum('nominal')
            ->where('DATE(created_at) >=', $startDate)
            ->where('DATE(created_at) <=', $endDate)
            ->where('status', 'active')
            ->where('type', 'expense');
        if (is_array($filter_region)) { $expenseBuilder->whereIn('region_id', $filter_region); }
        elseif ($filter_region) { $expenseBuilder->where('region_id', $filter_region); }
        $total_expense = $expenseBuilder->get()->getRow()->nominal ?? 0;

        // Saldo Kas Kecil dan Kas Besar
        $saldo_kas_kecil = $this->model_transaksi->get_saldo_kas('kas_kecil', $filter_region);
        $saldo_kas_besar = $this->model_transaksi->get_saldo_kas('kas_besar', $filter_region);

        $list_terapis = $db->table('terapis')->select('id, nama, region_id')->where('is_active', 1)->get()->getResultArray();

        $recent_keterangan = $db->table('transaksi')
            ->select('keterangan, MAX(id_transaksi) as max_id')
            ->where('status', 'active')
            ->where('keterangan !=', '')
            ->whereNotIn('type', ['mutasi_in', 'mutasi_out'])
            ->groupBy('keterangan')
            ->orderBy('max_id', 'DESC')
            ->limit(30)
            ->get()
            ->getResultArray();

        $list_kategori = $db->table('finance_categories')
            ->select('id, name, type')
            ->orderBy('type', 'DESC') // 'income' then 'expense'
            ->orderBy('name', 'ASC')
            ->get()
            ->getResultArray();

        $bulan_indo = [
            '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
            '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
            '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
        ];
        $monthPart = date('m', strtotime($startDate));
        $yearPart = date('Y', strtotime($startDate));
        $monthName = $bulan_indo[$monthPart] ?? date('F', strtotime($startDate));
        $period_label = $is_admin ? 'Hari Ini' : 'Bulan ' . $monthName . ' ' . $yearPart;

        $data = [
            'title'             => 'Dashboard Transaksi Keuangan',
            'realname'          => session()->get('realname'),
            'role'              => $role,
            'today_balance'     => $total_income - $total_expense,
            'total_income'      => $total_income,
            'total_expense'     => $total_expense,
            'saldo_kas_kecil'   => $saldo_kas_kecil,
            'saldo_kas_besar'   => $saldo_kas_besar,
            'active_region'     => session()->get('active_region'),
            'list_regions'      => $list_regions,
            'list_terapis'      => $list_terapis,
            'recent_keterangan' => $recent_keterangan,
            'list_kategori'     => $list_kategori,
            'period_label'      => $period_label,
            'current_month'     => $monthInput
        ];
        return view('\App\Modules\Transaksi\Views\index', $data);
    }

    public function fetch()
    {
        $draw = $this->request->getPost('draw');
        $start = $this->request->getPost('start');
        $length = $this->request->getPost('length');
        $order = $this->request->getPost('order');
        $columns = $this->request->getPost('columns');
        $date_start = $this->request->getPost('date_start');
        $date_end = $this->request->getPost('date_end');
        $month = $this->request->getPost('month');
        $metode = $this->request->getPost('metode');

        $options = [
            'order' => (!empty($order) && !empty($columns)) ? ($columns[$order[0]['column']]['name'] ?: $columns[$order[0]['column']]['data']) : 'created_at',
            'mode' => (!empty($order)) ? $order[0]['dir'] : 'desc',
            'offset' => $start ?? 0,
            'limit'  => $length ?? 10,
            'where'  => []
        ];

        // Filter by date range
        if (!empty($date_start) && !empty($date_end)) {
            $options['where']['DATE(t.created_at) >='] = $date_start;
            $options['where']['DATE(t.created_at) <='] = $date_end;
        } elseif (!empty($date_start)) {
            $options['where']['DATE(t.created_at)'] = $date_start;
        } else {
            // No date start/end provided, use month filter if not admin
            if (session()->get('role') !== 'admin' && !empty($month)) {
                $options['where']['DATE(t.created_at) >='] = $month . '-01';
                $options['where']['DATE(t.created_at) <='] = date('Y-m-t', strtotime($month . '-01'));
            }
        }

        if (session()->get('role') === 'admin') {
            $sevenDaysAgo = date('Y-m-d', strtotime('-7 days'));
            if (empty($options['where']['DATE(t.created_at) >=']) || $options['where']['DATE(t.created_at) >='] < $sevenDaysAgo) {
                $options['where']['DATE(t.created_at) >='] = $sevenDaysAgo;
            }
        }

        if (!empty($metode) && $metode !== 'all') {
            $options['where']['t.metode_pembayaran'] = $metode;
        }

        $dataOutput = $this->model_transaksi->get_list_data($options);
        $totalData  = $this->model_transaksi->get_total_data($options);
        $no = ($start ?? 0) + 1;
        foreach ($dataOutput as $value) {
            $value->no = $no++;
            $value->nominal_format = "Rp " . number_format($value->nominal, 0, ',', '.');
            $value->tanggal = date('d/m/Y H:i', strtotime($value->created_at));
            $value->dihapus_oleh = $value->cancelled_by_name ?? '-';

            $formatted_nominal = "Rp " . number_format($value->nominal, 0, ',', '.');

            if ($value->status === 'canceled') {
                $value->nominal_format = '<span class="text-danger" style="text-decoration: line-through;">' . $formatted_nominal . '</span>';
            } else if ($value->type === 'mutasi_out') {
                $value->nominal_format = '<span class="text-info font-weight-bold">- ' . $formatted_nominal . ' <br><small>(Mutasi Keluar)</small></span>';
            } else if ($value->type === 'mutasi_in') {
                $value->nominal_format = '<span class="text-info font-weight-bold">+ ' . $formatted_nominal . ' <br><small>(Mutasi Masuk)</small></span>';
            } else if ($value->type === 'expense') {
                $value->nominal_format = '<span class="text-danger font-weight-bold">- ' . $formatted_nominal . '</span>';
            } else {
                $value->nominal_format = '<span class="text-emerald-600 font-weight-bold">+ ' . $formatted_nominal . '</span>';
            }
            
            // Clean up the prefix from keterangan
            $clean_keterangan = str_replace(['[KAS_BESAR] ', '[KAS_KECIL] ', '[KAS_BESAR]', '[KAS_KECIL]'], '', $value->keterangan);
            
            // Add Badge for Kas
            $kas_badge = ($value->kas_type ?? 'kas_kecil') === 'kas_besar' ? '<span class="bg-blue-100 text-blue-800 text-xs px-2 py-0.5 rounded ml-2">Besar</span>' : '<span class="bg-gray-100 text-gray-800 text-xs px-2 py-0.5 rounded ml-2">Kecil</span>';
            $value->keterangan = $clean_keterangan . $kas_badge;

            if ($value->status === 'canceled') {
                $value->aksi = '<span class="badge badge-secondary"></span>';
            } else {
                $value->aksi = '
            <button class="btn btn-danger btn-sm btn-delete" data-id="' . $value->id_transaksi . '">
                <i class="fas fa-ban"></i>
            </button>';
            }
        }

        return $this->response->setJSON([
            "draw" => intval($draw),
            "recordsTotal" => intval($totalData),
            "recordsFiltered" => intval($totalData),
            "data" => $dataOutput,
            "csrfHash" => csrf_hash()
        ]);
    }

    public function store()
    {
        $role = session()->get('role');
        $akitf_region = session()->get("active_region");
        $type = $this->request->getPost('type');

        if ($role === 'superadmin' || $role === 'owner') {
            $region_id = $this->request->getPost('region_id');
            if (empty($region_id) && $akitf_region !== 'all') {
                $region_id = $akitf_region;
            }
        } else {
            $region_id = session()->get('region_id');
        }

        if (empty($region_id) || $region_id === 'all') {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Gagal: Cabang tidak terdeteksi atau belum dipilih!'
            ]);
        }

        $typeInput = $this->request->getPost('type') ?? 'income';
        $kategoriAuto = $this->request->getPost('kategori_pilihan') ?: (($typeInput === 'income') ? 'pemasukan' : 'pengeluaran');
        
        $kas_type = $this->request->getPost('kas_type') ?? 'kas_kecil';
        $terapis_id = $this->request->getPost('terapis_id');

        // Get tanggal from input, default to now if not provided
        $tanggal = $this->request->getPost('tanggal');
        if (empty($tanggal)) {
            $tanggal = date('Y-m-d H:i:s');
        } else {
            // Combine date with current time
            $tanggal = date('Y-m-d H:i:s', strtotime($tanggal . ' ' . date('H:i:s')));
        }
        
        $nominal = preg_replace('/[^0-9]/', '', $this->request->getPost('nominal'));

        $keteranganInput = $this->request->getPost('keterangan');
        $keteranganDb = ($kas_type === 'kas_besar') ? '[KAS_BESAR] ' . $keteranganInput : '[KAS_KECIL] ' . $keteranganInput;

        $data = [
            'region_id'         => $region_id,
            'nominal'           => $nominal,
            'type'              => $typeInput,
            'kategori'          => $kategoriAuto,
            'keterangan'        => $keteranganDb,
            'created_at'        => $tanggal,
            'created_by'        => session()->get('userId')
        ];

        try {
            $db = \Config\Database::connect();
            $db->transStart();
            
            $this->model_transaksi->insert($data);
            
            // Integrasi Kasbon
            if ($typeInput === 'expense' && strpos($kategoriAuto, 'Kasbon') !== false && !empty($terapis_id)) {
                $db->table('kasbon_karyawan')->insert([
                    'terapis_id'      => $terapis_id,
                    'tanggal'         => date('Y-m-d', strtotime($tanggal)),
                    'nominal'         => $nominal,
                    'sisa_hutang'     => $nominal,
                    'keterangan'      => $this->request->getPost('keterangan'),
                    'status_potongan' => 'belum_lunas'
                ]);
            }
            
            // Integrasi Gaji
            if ($typeInput === 'expense' && strpos($kategoriAuto, 'Gaji') !== false && !empty($terapis_id)) {
                $db->table('riwayat_gaji')->insert([
                    'terapis_id'       => $terapis_id,
                    'periode_bulan'    => date('n', strtotime($tanggal)),
                    'periode_tahun'    => date('Y', strtotime($tanggal)),
                    'gaji_bersih'      => $nominal,
                    'gaji_pokok_total' => $nominal, // as a baseline
                    'tanggal_bayar'    => $tanggal,
                    'status'           => 'lunas'
                ]);
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal menyimpan transaksi (Transaction rollback)']);
            }
            
            return $this->response->setJSON(['status' => 'success', 'message' => 'Transaksi berhasil']);
        } catch (\Exception $e) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
        }

        return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal menyimpan data']);
    }

    public function store_mutasi()
    {
        $role = session()->get('role');
        $akitf_region = session()->get("active_region");

        if ($role === 'superadmin' || $role === 'owner') {
            $region_id = $this->request->getPost('region_id');
            if (empty($region_id) && $akitf_region !== 'all') {
                $region_id = $akitf_region;
            }
        } else {
            $region_id = session()->get('region_id');
        }

        if (empty($region_id) || $region_id === 'all') {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Gagal: Cabang tidak terdeteksi atau belum dipilih!'
            ]);
        }

        $nominal = preg_replace('/[^0-9]/', '', $this->request->getPost('nominal'));
        $dari_kas = $this->request->getPost('dari_kas');
        $ke_kas = $this->request->getPost('ke_kas');
        $keterangan = $this->request->getPost('keterangan');

        if ($dari_kas === $ke_kas) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Kas asal dan tujuan tidak boleh sama.']);
        }

        $tanggal = date('Y-m-d H:i:s');
        $userId = session()->get('userId');

        try {
            $db = \Config\Database::connect();
            $db->transStart();

            // Mutasi Keluar
            $dari_prefix = ($dari_kas === 'kas_besar') ? '[KAS_BESAR] ' : '[KAS_KECIL] ';
            $this->model_transaksi->insert([
                'region_id'  => $region_id,
                'type'       => 'mutasi_out',
                'nominal'    => $nominal,
                'kategori'   => 'mutasi',
                'keterangan' => $dari_prefix . 'Pindah Buku Keluar: ' . $keterangan,
                'created_at' => $tanggal,
                'created_by' => $userId
            ]);

            // Mutasi Masuk
            $ke_prefix = ($ke_kas === 'kas_besar') ? '[KAS_BESAR] ' : '[KAS_KECIL] ';
            $this->model_transaksi->insert([
                'region_id'  => $region_id,
                'type'       => 'mutasi_in',
                'nominal'    => $nominal,
                'kategori'   => 'mutasi',
                'keterangan' => $ke_prefix . 'Pindah Buku Masuk: ' . $keterangan,
                'created_at' => $tanggal,
                'created_by' => $userId
            ]);

            $db->transComplete();

            if ($db->transStatus() === false) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal melakukan mutasi.']);
            }

            return $this->response->setJSON(['status' => 'success', 'message' => 'Pindah Buku berhasil.']);
        } catch (\Exception $e) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
        }
    }

    public function delete()
    {
        $id = $this->request->getPost('id_transaksi');
        $reason = $this->request->getPost('reason');
        $userId = session()->get('userId');

        if (!$id) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'ID Transaksi tidak ditemukan']);
        }

        $data = [
            'status'        => 'canceled',
            'cancel_reason' => $reason,
            'cancelled_by'  => $userId
        ];

        if ($this->model_transaksi->update($id, $data)) {
            return $this->response->setJSON([
                'status'  => 'success',
                'message' => 'Transaksi Berhasil Dibatalkan'
            ]);
        }

        return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal memproses pembatalan']);
    }

    public function export_data()
    {
        $date_start = $this->request->getGet('date_start');
        $date_end = $this->request->getGet('date_end');
        $month = $this->request->getGet('month');
        $metode = $this->request->getGet('metode');
        $role = session()->get('role');

        $active_region = session()->get('active_region');
        $region_session = session()->get('region_id');
        $builder = $this->model_transaksi->where('status', 'active');

        // 2. Filter Tanggal (Jika ada)
        if (!empty($date_start) && !empty($date_end)) {
            $builder->where('DATE(created_at) >=', $date_start);
            $builder->where('DATE(created_at) <=', $date_end);
        } elseif (!empty($date_start)) {
            $builder->where('DATE(created_at)', $date_start);
        } else {
            // No date filters, check month filter if not admin
            if ($role !== 'admin' && !empty($month)) {
                $builder->where('DATE(created_at) >=', $month . '-01');
                $builder->where('DATE(created_at) <=', date('Y-m-t', strtotime($month . '-01')));
            }
        }

        if ($role === 'admin') {
            $sevenDaysAgo = date('Y-m-d', strtotime('-7 days'));
            $builder->where('DATE(created_at) >=', $sevenDaysAgo);
        }

        // 3. Filter Metode (Jika ada dan bukan 'all')
        if (!empty($metode) && $metode !== 'all') {
            $builder->where('metode_pembayaran', $metode);
        }

        if ($role === 'superadmin' || $role === 'owner') {
            $filter_region = ($active_region !== 'all') ? $active_region : null;
        } else {
            $filter_region = $region_session;
        }

        if ($filter_region) {
            $builder->where('region_id', $filter_region);
        }

        return $builder->orderBy('created_at', 'DESC')->findAll();
    }


    public function export_excell()
    {
        $data = $this->export_data();
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Laporan Transaksi');

        // --- 1. JUDUL LAPORAN ---
        $sheet->setCellValue('A1', 'LAPORAN RIWAYAT TRANSAKSI KEUANGAN');
        $sheet->mergeCells('A1:D1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $sheet->setCellValue('A2', 'Dicetak pada: ' . date('d/m/Y H:i:s'));
        $sheet->mergeCells('A2:D2');
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // --- 2. HEADER TABEL ---
        $sheet->setCellValue('A4', 'No')
            ->setCellValue('B4', 'Tanggal')
            ->setCellValue('C4', 'Keterangan')
            ->setCellValue('D4', 'Nominal');

        // Header Styling
        $styleHeader = [
            'font' => [
                'bold'  => true,
                'color' => ['rgb' => 'FFFFFF']
            ],
            'fill' => [
                'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1E40AF'] // Dark Blue
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
        $sheet->getStyle('A4:D4')->applyFromArray($styleHeader);
        $sheet->getRowDimension(4)->setRowHeight(25);

        // --- 3. ISI DATA ---
        $row = 5;
        foreach ($data as $index => $t) {
            $clean_keterangan = str_replace(['[KAS_BESAR] ', '[KAS_KECIL] ', '[KAS_BESAR]', '[KAS_KECIL]'], '', $t['keterangan']);
            $kas_label = (strpos($t['keterangan'], '[KAS_BESAR]') !== false) ? ' (Kas Besar)' : ' (Kas Kecil)';

            $sheet->setCellValue('A' . $row, $index + 1)
                ->setCellValue('B' . $row, date('d/m/Y H:i', strtotime($t['created_at'])))
                ->setCellValue('C' . $row, $clean_keterangan . $kas_label)
                ->setCellValue('D' . $row, $t['nominal']);

            // Styling baris data
            $sheet->getStyle('A' . $row . ':D' . $row)->applyFromArray([
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
            
            // Format Rupiah di kolom D (sebelumnya F)
            $sheet->getStyle('D' . $row)->getNumberFormat()->setFormatCode('#,##0');
            $row++;
        }

        // Auto size kolom
        foreach (range('A', 'D') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        $filename = 'Laporan_Transaksi_' . date('YmdHis') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    public function export_pdf()
    {
        $raw_transaksi = $this->export_data();
        $transaksi = [];
        foreach ($raw_transaksi as $t) {
            $t['kas_type'] = (strpos($t['keterangan'], '[KAS_BESAR]') !== false) ? 'kas_besar' : 'kas_kecil';
            $t['keterangan'] = str_replace(['[KAS_BESAR] ', '[KAS_KECIL] ', '[KAS_BESAR]', '[KAS_KECIL]'], '', $t['keterangan']);
            $transaksi[] = $t;
        }
        $data['transaksi'] = $transaksi;
        $data['title'] = "Laporan Transaksi - " . date('d M Y');

        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);

        $dompdf = new Dompdf($options);

        $html = view('App\modules\transaksi\Views\export_transaksi_pdf_template', $data);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $dompdf->stream("Laporan_Transaksi_" . date('Ymd') . ".pdf", ["Attachment" => 1]);
    }
    public function chart_data()
    {
        $date = $this->request->getGet('date');
        $region_patient = session()->get('region_patient');
        $filter_region = ($region_patient !== 'all' && !empty($region_patient)) ? $region_patient : null;

        // Build 7-day labels & data around the selected date
        $referenceDate = !empty($date) ? $date : date('Y-m-d');
        $startDate = date('Y-m-d', strtotime('-6 days', strtotime($referenceDate)));

        $db = \Config\Database::connect();
        $builder = $db->table('transaksi')
            ->select("DATE(created_at) as tanggal")
            ->selectSum("CASE WHEN type = 'income' THEN nominal ELSE 0 END", 'pemasukan', false)
            ->selectSum("CASE WHEN type = 'expense' THEN nominal ELSE 0 END", 'pengeluaran', false)
            ->where("DATE(created_at) >=", $startDate)
            ->where("DATE(created_at) <=", $referenceDate)
            ->where('status', 'active');

        if ($filter_region) {
            if (is_array($filter_region)) {
                $builder->whereIn('region_id', $filter_region);
            } else {
                $builder->where('region_id', $filter_region);
            }
        }

        $rows = $builder->groupBy("DATE(created_at)")->orderBy("tanggal", "ASC")->get()->getResultArray();

        $indexed = [];
        foreach ($rows as $r) {
            $indexed[$r['tanggal']] = $r;
        }

        $labels = [];
        $income = [];
        $expense = [];

        for ($i = 6; $i >= 0; $i--) {
            $day = date('Y-m-d', strtotime("-$i days", strtotime($referenceDate)));
            $labels[] = date('d/m', strtotime($day));
            $income[]  = (int)($indexed[$day]['pemasukan'] ?? 0);
            $expense[] = (int)($indexed[$day]['pengeluaran'] ?? 0);
        }

        return $this->response->setJSON([
            'labels'  => $labels,
            'income'  => $income,
            'expense' => $expense,
        ]);
    }
}


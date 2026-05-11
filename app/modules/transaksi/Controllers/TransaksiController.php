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
        $region_patient = session()->get('region_patient');
        $filter_region = ($region_patient !== 'all' && !empty($region_patient)) ? $region_patient : null;
        $list_regions = $db->table('regions')->select('id, name');
        if ($filter_region) {
            if (is_array($filter_region)) {
                $list_regions->whereIn('id', $filter_region);
            } else {
                $list_regions->where('id', $filter_region);
            }
        }
        $list_regions = $list_regions->get()->getResultArray();

        // Normalize filter to scalar for single-region queries
        $scalar_filter = is_array($filter_region) && count($filter_region) === 1 ? $filter_region[0] : $filter_region;


        // Hitung Uang Masuk Hari Ini
        $todayIncomeBuilder = $db->table('transaksi')->selectSum('nominal')
            ->where('DATE(created_at)', date('Y-m-d'))
            ->where('status', 'active')
            ->where('type', 'income');
        if (is_array($filter_region)) { $todayIncomeBuilder->whereIn('region_id', $filter_region); }
        elseif ($filter_region) { $todayIncomeBuilder->where('region_id', $filter_region); }
        $in_today = $todayIncomeBuilder->get()->getRow()->nominal ?? 0;

        // Hitung Uang Keluar Hari Ini
        $todayExpenseBuilder = $db->table('transaksi')->selectSum('nominal')
            ->where('DATE(created_at)', date('Y-m-d'))
            ->where('status', 'active')
            ->where('type', 'expense');
        if (is_array($filter_region)) { $todayExpenseBuilder->whereIn('region_id', $filter_region); }
        elseif ($filter_region) { $todayExpenseBuilder->where('region_id', $filter_region); }
        $out_today = $todayExpenseBuilder->get()->getRow()->nominal ?? 0;

        $today_balance = $in_today - $out_today;

        // --- 2. Total Income (Akumulasi Selamanya) ---
        $incomeBuilder = $db->table('transaksi')->selectSum('nominal')->where('type', 'income')->where('status', 'active');
        if (is_array($filter_region)) { $incomeBuilder->whereIn('region_id', $filter_region); }
        elseif ($filter_region) { $incomeBuilder->where('region_id', $filter_region); }
        $total_in = $incomeBuilder->get()->getRow()->nominal ?? 0;

        // --- 3. Total Expense (Akumulasi Selamanya) ---
        $expenseBuilder = $db->table('transaksi')->selectSum('nominal')->where('type', 'expense')->where('status', 'active');
        if (is_array($filter_region)) { $expenseBuilder->whereIn('region_id', $filter_region); }
        elseif ($filter_region) { $expenseBuilder->where('region_id', $filter_region); }
        $total_out = $expenseBuilder->get()->getRow()->nominal ?? 0;

        $total_income = $total_in - $total_out;
        $total_expense = $total_out;

        $data = [
            'title'          => 'Dashboard Keuangan',
            'realname'       => session()->get('realname'),
            'role'           => session()->get('role'),
            'today_balance'  => $today_balance,
            'total_income'   => $total_income,
            'total_expense'  => $total_expense,
            'active_region'  => session()->get('active_region'),
            'list_regions'   => $list_regions
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
        $date = $this->request->getPost('date');
        $metode = $this->request->getPost('metode');

        $options = [
            'order' => (!empty($order) && !empty($columns)) ? ($columns[$order[0]['column']]['name'] ?: $columns[$order[0]['column']]['data']) : 'created_at',
            'mode' => (!empty($order)) ? $order[0]['dir'] : 'desc',
            'offset' => $start ?? 0,
            'limit'  => $length ?? 10,
            'where'  => []
        ];

        if (!empty($date)) {
            $options['where']['DATE(t.created_at)'] = $date;
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
            } else if ($value->type === 'expense') {
                $value->nominal_format = '<span class="text-danger font-weight-bold">- ' . $formatted_nominal . '</span>';
            } else {
                $value->nominal_format = '<span class="text-dark font-weight-bold">' . $formatted_nominal . '</span>';
            }

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
        $kategoriAuto = ($typeInput === 'income') ? 'pemasukan' : 'pengeluaran';

        $data = [
            'region_id'         => $region_id,
            'nominal'           => $this->request->getPost('nominal'),
            'type'              => $typeInput,
            'kategori'          => $kategoriAuto,
            'keterangan'        => $this->request->getPost('keterangan'),
            'metode_pembayaran' => $this->request->getPost('metode_pembayaran'),
            'rentang_usia'      => $this->request->getPost('rentang_usia'),
            'created_at'        => date('Y-m-d H:i:s'),
            'created_by'        => session()->get('userId')
        ];

        try {
            if ($this->model_transaksi->insert($data)) {
                return $this->response->setJSON(['status' => 'success', 'message' => 'Transaksi berhasil']);
            }
        } catch (\Exception $e) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
        }

        return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal menyimpan data']);
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
        $date = $this->request->getGet('date');
        $metode = $this->request->getGet('metode');
        $role = session()->get('role');

        $active_region = session()->get('active_region');
        $region_session = session()->get('region_id');
        $builder = $this->model_transaksi->where('status', 'active');

        // 2. Filter Tanggal (Jika ada)
        if (!empty($date)) {
            $builder->where('DATE(created_at)', $date);
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

        // Header Styling
        $styleHeader = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '4E73DF']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]
        ];

        // Header Tabel
        $sheet->setCellValue('A1', 'No')
            ->setCellValue('B1', 'Tanggal')
            ->setCellValue('C1', 'Metode')
            ->setCellValue('D1', 'Usia')
            ->setCellValue('E1', 'Keterangan')
            ->setCellValue('F1', 'Nominal');

        $sheet->getStyle('A1:F1')->applyFromArray($styleHeader);

        // Isi Data
        $row = 2;
        foreach ($data as $index => $t) {
            $sheet->setCellValue('A' . $row, $index + 1)
                ->setCellValue('B' . $row, date('d/m/Y H:i', strtotime($t['created_at'])))
                ->setCellValue('C' . $row, strtoupper($t['metode_pembayaran']))
                ->setCellValue('D' . $row, $t['rentang_usia'])
                ->setCellValue('E' . $row, $t['keterangan'])
                ->setCellValue('F' . $row, $t['nominal']);

            // Format Rupiah di kolom F
            $sheet->getStyle('F' . $row)->getNumberFormat()->setFormatCode('#,##0');
            $row++;
        }

        // Auto size kolom
        foreach (range('A', 'F') as $columnID) {
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
        $data['transaksi'] = $this->export_data();
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


<?php

namespace App\modules\transaksi\Controllers;

use App\Controllers\BaseController;
use App\modules\transaksi\Models\MTransaksi;
use CodeIgniter\HTTP\ResponseInterface;
use Dompdf\Dompdf;
use Dompdf\Options;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class Transaksi extends BaseController
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
        $active_region = session()->get('active_region');
        $region_session = session()->get('region_id');
        $list_regions = $db->table('regions')->select('id, name')->get()->getResultArray();


        if ($role === 'superadmin' || $role === 'owner') {
            $filter_region = ($active_region !== 'all') ? $active_region : null;
        } else {
            $filter_region = $region_session;
        }


        // --- 1. Saldo Hari Ini (Reset Tiap Hari) ---
        $todayBuilder = $db->table('transaksi')->selectSum('nominal')->where(['DATE(created_at)' => date('Y-m-d'), 'status' => 'active', 'type' => 'income']);
        if ($filter_region) $todayBuilder->where('region_id', $filter_region);
        $inToday = $todayBuilder->get()->getRow()->nominal ?? 0;

        $outTodayBuilder = $db->table('transaksi')->selectSum('nominal')->where(['DATE(created_at)' => date('Y-m-d'), 'status' => 'active', 'type' => 'expense']);
        if ($filter_region) $outTodayBuilder->where('region_id', $filter_region);
        $outToday = $outTodayBuilder->get()->getRow()->nominal ?? 0;

        $todayIncome = $inToday - $outToday;


        // $todayBuilder->where('DATE(created_at)', date('Y-m-d'));
        // $todayBuilder->where('status', 'active');
        // if ($filter_region) $todayBuilder->where('region_id', $filter_region);
        // $today_balance = $todayBuilder->get()->getRow()->nominal ?? 0;

        $incomeBuilder = $db->table('transaksi')->selectSum('nominal')->where(['status' => 'active', 'type' => 'income']);
        if ($filter_region) $incomeBuilder->where('region_id', $filter_region);
        $total_in = $incomeBuilder->get()->getRow()->nominal ?? 0;

        $expenseBuilder = $db->table('transaksi')->selectSum('nominal')->where(['status' => 'active', 'type' => 'expense']);
        if ($filter_region) $expenseBuilder->where('region_id', $filter_region);
        $total_out = $expenseBuilder->get()->getRow()->nominal ?? 0;

        $total_income = $total_in - $total_out;

        $total_expense = $total_out;
        if ($role === 'superadmin' || $role === 'owner') {
            // Contoh: Ambil dari tabel pengeluaran jika ada, atau filter type='expense'
            $total_expense = $db->table('transaksi')->selectSum('nominal')->where('type', 'expense')->where('status', 'active')->get()->getRow()->nominal ?? 0;
        }

        $data = [
            'title'          => 'Dashboard Keuangan',
            'realname'       => session()->get('realname'),
            'role'           => $role,
            'today_balance'  => $todayIncome,
            'total_income'   => $total_income,
            'total_expense'  => $total_expense,
            'active_region'  => $active_region,
            'list_regions'   => $list_regions
        ];
        return view('\App\modules\transaksi\Views\views_transaksi', $data);
    }

    public function fetch()
    {
        $draw = $this->request->getPost('draw');
        $start = $this->request->getPost('start');
        $length = $this->request->getPost('length');
        // Ganti 'other' menjadi 'order'
        $order = $this->request->getPost('order');
        $columns = $this->request->getPost('columns');
        $date = $this->request->getPost('date');
        $metode = $this->request->getPost('metode');

        $options = [
            // Pastikan variabel $order digunakan di sini
            'order'  => (!empty($order) && !empty($columns)) ? ($columns[$order[0]['column']]['name'] ?: $columns[$order[0]['column']]['data']) : 'created_at',
            'mode'   => (!empty($order)) ? $order[0]['dir'] : 'desc',
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
        // dd($dataOutput);
        $totalData  = $this->model_transaksi->get_total_data($options);

        $no = ($start ?? 0) + 1;
        foreach ($dataOutput as $value) {
            $value->no = $no++;
            // Gunakan nominal_format sesuai yang dipanggil di View (data: 'nominal_format')
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
            "draw"            => intval($draw),
            "recordsTotal"    => intval($totalData),
            "recordsFiltered" => intval($totalData),
            "data"            => $dataOutput,
            "csrfHash"        => csrf_hash()
        ]);
    }

    public function store()
    {
        $role = session()->get('role');
        $akitf_region = session()->get("active_region");
        $type = $this->request->getPost('type');


        // Proteksi Region
        if ($role === 'superadmin' || $role === 'owner') {
            $region_id = $this->request->getPost('region_id');

            if (empty($region_id) && $akitf_region !== 'all') {
                $region_id = $akitf_region;
            }
        } else {
            // Admin Cabang terkunci ke wilayah aktif mereka
            $region_id = session()->get('region_id');
        }

        // Cek jika masih dalam mode 'all'
        if (empty($region_id) || $region_id === 'all') {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Gagal: Cabang tidak terdeteksi atau belum dipilih!'
            ]);
        }

  

        $data = [
            'region_id'         => $region_id,
            'nominal'           => $this->request->getPost('nominal'),
            'type'              => $type,
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

        // $this->model_transaksi->update($id, ['deleted_by' => $userId]);

        $data = [
            'status'        => 'canceled',
            'cancel_reason' => $reason,
            'cancelled_by'   => $userId
        ];

        // Opsi: Tambahkan pengecekan role di sini jika hanya Superadmin yang boleh hapus
        if ($this->model_transaksi->update($id, $data)) {
            return $this->response->setJSON([
                'status'  => 'success',
                'message' => 'Transaksi Berhasil Dihapus'
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


        return $this->model_transaksi->where('status', 'active')->where($filter_region ? ['region_id' => $filter_region] : [])->orderBy('created_at', 'DESC')->findAll();
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
}

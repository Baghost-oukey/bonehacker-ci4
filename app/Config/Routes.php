<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
// $routes->get('/', 'Home::index');
$routes->get('/', '\App\Modules\auth\Controllers\Auth::index');

$routes->group('auth', ['namespace' => 'App\Modules\auth\Controllers'], function ($routes) {
  $routes->get('', 'Auth::index');
  $routes->post('validate', 'Auth::authValidate');
  $routes->post('switch_region', 'Auth::switch_region');
  $routes->get('destroy', 'Auth::destroy');
  $routes->get('get_csrf', 'Auth::get_csrf');
});

// Beranda
$routes->get('beranda', 'BerandaController::index', ['namespace' => 'App\Modules\Beranda\Controllers']);

// Antrean
$routes->group('antrean', ['namespace' => 'App\Modules\Antrean\Controllers'], function ($routes) {
  $routes->get('', 'AntreanController::index');
  $routes->get('fetchJson', 'AntreanController::fetchJson');
  $routes->post('fetchDataTable', 'AntreanController::fetchDataTable');
  $routes->post('fetchPatientDataTables', 'AntreanController::fetchPatientDataTables');
  $routes->get('addToQueue/(:num)', 'AntreanController::addToQueue/$1');
  $routes->post('destroy/(:num)', 'AntreanController::destroy/$1');
  $routes->get('daftar-antrean', 'AntreanController::daftarAntrean');
  $routes->get('procesToQueue/(:num)', 'AntreanController::procesToQueue/$1');
  $routes->get('finishQueue/(:num)', 'AntreanController::finishQueue/$1');
  $routes->get('export_excell_antrean', 'AntreanController::export_excell_antrean');
  $routes->get('print_pdf_antrean', 'AntreanController::print_pdf_antrean');
});

// Rekam Medis
$routes->get('rekam-medis', '\App\Modules\RekamMedis\Controllers\RekamMedisController::index');
$routes->post('patients/fetch2', '\App\Modules\patients\Controllers\Patients::fetch2');

// Save Data - Rekam Medis
$routes->post('patient/store', '\App\Modules\patients\Controllers\Patients::store');
$routes->post('patient/check_phone', '\App\Modules\patients\Controllers\Patients::check_phone');
$routes->get('patient/export_data', '\App\Modules\patients\Controllers\Patients::export_data');

// Wilayah
$routes->group('region', ['namespace' => 'App\Modules\Region\Controllers'], function ($routes) {
  $routes->get('', 'RegionController::index');
  $routes->post('fetch', 'RegionController::fetch');
  $routes->post('store', 'RegionController::store');
  $routes->post('update/(:num)', 'RegionController::update/$1');
  $routes->post('destroy/(:num)', 'RegionController::destroy/$1');
});
// app/Config/Routes.php

// Journal
$routes->group('journal', ['namespace' => 'App\Modules\Journal\Controllers'], function ($routes) {
  $routes->get('', 'JournalController::index');
  $routes->post('fetch', 'JournalController::fetch');
  $routes->get('export_excell', 'JournalController::export_excell');
  $routes->get('export_pdf', 'JournalController::export_pdf');
  $routes->get('export_file_journal', 'JournalController::export_file_journal');
});

// Transaksi
$routes->group('transaksi', ['namespace' => 'App\Modules\Transaksi\Controllers', 'filter' => 'auth'], function ($routes) {
  $routes->get('', 'TransaksiController::index');
  $routes->post('fetch', 'TransaksiController::fetch');
  $routes->post('store', 'TransaksiController::store');
  $routes->post('delete', 'TransaksiController::delete');
});

// Statistik => Ada Typo
$routes->group('statistik', ['namespace' => 'App\Modules\statisktik\Controllers'], function ($routes) {
  $routes->get('', 'Statistik::index');
  $routes->get('fetch_statistics', 'Statistik::fetch_statistics');
  $routes->get('fetch_analysis', 'Statistik::fetch_analysis');
});

// Statistik - Tag
$routes->group('statistiktag', ['namespace' => 'App\Modules\statistiktag\Controllers'], function ($routes) {
  $routes->get('', 'StatistikTag::index');
  $routes->get('fetch_statistics', 'StatistikTag::fetch_statistics');
});

// Statistik - Hasil Pemeriksaan
$routes->group('statistikresult', ['namespace' => 'App\Modules\statistikresult\Controllers'], function ($routes) {
  $routes->get('', 'StatistikResult::index');
  $routes->get('fetch_statistics', 'StatistikResult::fetch_statistics');
});

// Statistik - Gender
$routes->group('statistikgender', ['namespace' => 'App\Modules\statistikgender\Controllers', 'filter' => 'auth'], function ($routes) {
  $routes->get('', 'Statistikgender::index');
  $routes->get('fetch_statistics', 'Statistikgender::fetch_statistics');
});

// Statistik - Daerah
$routes->group('statistikdaerah', ['namespace' => 'App\Modules\statistikdaerah\Controllers', 'filter' => 'auth'], function ($routes) {
  $routes->get('', 'Statistikdaerah::index');
  $routes->get('fetch_statistics', 'Statistikdaerah::fetch_statistics');
  $routes->get('fetch_kabupaten', 'Statistikdaerah::fetch_kabupaten');
  $routes->get('fetch_kecamatan', 'Statistikdaerah::fetch_kecamatan');
  $routes->get('fetch_desa', 'Statistikdaerah::fetch_desa');
});

// Statistik Sosial Media
$routes->group('statistikresource', ['namespace' => 'App\Modules\statistikrecource\Controllers'], function ($routes) {
  $routes->get('', 'statistikrecource::index');
  $routes->get('get_marketing_data', 'statistikrecource::get_marketing_data');
});

// Jabatan
$routes->group('jabatan', ['namespace' => 'App\Modules\jabatan\Controllers'], function ($routes) {
  $routes->get('', 'Jabatan::index');
  $routes->post('fetch', 'Jabatan::fetch');
  $routes->post('store', 'Jabatan::store');
  $routes->post('update/(:num)', 'Jabatan::update/$1');
  $routes->post('destroy/(:num)', 'Jabatan::destroy/$1');
  $routes->post('check_name_exists', 'Jabatan::check_name_exists');
});

// Tag - Complaint(Keluhan)
$routes->group('tag-keluhan', ['namespace' => 'App\Modules\TagComplaint\Controllers'], function ($routes) {
  $routes->get('', 'TagComplaintController::index');
  $routes->post('fetch', 'TagComplaintController::fetch');
  $routes->post('store', 'TagComplaintController::store');
  $routes->post('update/(:num)', 'TagComplaintController::update/$1');
  $routes->post('destroy/(:num)', 'TagComplaintController::destroy/$1');
  $routes->post('check_name_exists', 'TagComplaintController::check_name_exists');
  $routes->get('get_tags', 'TagComplaintController::get_tags');
});

// Tag - Medis(Rekam Medis)
$routes->group('tag-rekam-medis', ['namespace' => 'App\Modules\TagRekamMedis\Controllers'], function ($routes) {
  $routes->get('/', 'MedisController::index');
  $routes->match(['get', 'post'], 'fetch', 'MedisController::fetch');
  $routes->get('tags', 'MedisController::get_tags');
  $routes->post('store', 'MedisController::store');
  $routes->post('update/(:num)', 'MedisController::update/$1');
  $routes->post('destroy/(:num)', 'MedisController::destroy/$1');
  $routes->post('check-name', 'MedisController::check_name_exists');
});

// Tag - Result
$routes->group('tag-pemeriksaan', ['namespace' => 'App\Modules\TagPemeriksaan\Controllers'], function ($routes) {
  $routes->get('', 'TagPemeriksaanController::index');
  $routes->post('fetch', 'TagPemeriksaanController::fetch');
  $routes->post('store', 'TagPemeriksaanController::store');
  $routes->post('update/(:num)', 'TagPemeriksaanController::update/$1');
  $routes->post('destroy/(:num)', 'TagPemeriksaanController::destroy/$1');
  $routes->post('check_name_exists', 'TagPemeriksaanController::check_name_exists');
  $routes->get('get_tags', 'TagPemeriksaanController::get_tags');
});

// Logs
$routes->group('logs', ['namespace' => 'App\Modules\logs\Controllers'], function ($routes) {
  $routes->get('', 'Log::index');
});

// Whatsapp
$routes->group('whatsapp', ['namespace' => 'App\Modules\whatsapp\Controllers'], function ($routes) {
  $routes->get('', 'Whatsapp::index');
  $routes->post('store', 'Whatsapp::store');
  $routes->post('edit/(:num)', 'Whatsapp::edit/$1');
  $routes->post('delete/(:num)', 'Whatsapp::delete/$1');
  $routes->post('send_notif_patients/(:num)', 'Whatsapp::send_notif_patients/$1');
});

// History
$routes->group('history', ['namespace' => 'App\Modules\history\Controllers'], function ($routes) {
  $routes->post('fetch/(:num)', 'History::fetch/$1');
  $routes->post('store', 'History::store');
  $routes->post('update', 'History::update');
  $routes->post('copy', 'History::copy');
  $routes->post('destroy/(:num)', 'History::destroy/$1');
  $routes->get('show/(:num)', 'History::show/$1');
});

// Log - WhatsApp
$routes->group('log_whatsapp', ['namespace' => 'App\Modules\log_whatsapp\Controllers'], function ($routes) {
  $routes->get('', 'Logwhatsapp::index');
  $routes->post('resend', 'Logwhatsapp::resend');
});

// Greeting
$routes->group('greeting', ['namespace' => 'App\Modules\greeting\Controllers'], function ($routes) {
  $routes->get('', 'Greeting::index');
  $routes->post('save', 'Greeting::save');
  $routes->get('delete/(:num)', 'Greeting::delete/$1');
});

// Terapis
$routes->group('terapis', ['namespace' => 'App\Modules\terapis\Controllers'], function ($routes) {
  $routes->get('', 'TerapisController::index');
  $routes->get('detail-terapis/(:any)', 'TerapisController::detail_terapis/$1');
  $routes->post('fetch', 'TerapisController::fetch'); // Untuk narik data tabel
  $routes->post('active/(:num)', 'TerapisController::active/$1');
  $routes->post('nonActive/(:num)', 'TerapisController::nonActive/$1');
  $routes->post('checkId', 'TerapisController::checkId');
  $routes->post('store', 'TerapisController::store');
  $routes->post('update', 'TerapisController::update');
  $routes->get('destroy/(:num)', 'TerapisController::destroy/$1');
  $routes->get('public_info/(:any)', 'TerapisController::public_info/$1');
});

// Untuk Akses info publik
// $routes->get('p/(:any)', '\App\Controllers\Terapis::public_info/$1');

// Users
$routes->group('users', ['namespace' => 'App\Modules\Users\Controllers'], function ($routes) {
  $routes->get('', 'UsersController::index');
  $routes->post('fetch', 'UsersController::fetch');
  $routes->post('store', 'UsersController::store');
  $routes->post('update/(:num)', 'UsersController::update/$1');
  $routes->get('destroy/(:num)', 'UsersController::destroy/$1');

  // Pasien Luar
  $routes->get('view_patient/(:num)', 'Users::view_patient/$1');
  $routes->post('fetch_patients', 'Users::fetch_patients');
  $routes->post('add_outside_patient', 'Users::add_outside_patient');
  $routes->post('fetch_patients_luar', 'Users::fetch_patients_luar');
  $routes->post('get_outside_patients_select', 'Users::get_outside_patients_select');
  $routes->post('delete_outside_patient', 'Users::delete_outside_patient');
  $routes->post('send_notif_patients/(:num)', 'Users::send_notif_patients/$1');
  $routes->post('check_username_exists', 'Users::check_username_exists');
});

// Patients - Routes
$routes->group('patient', ['namespace' => 'App\Modules\patients\Controllers'], function ($routes) {
  $routes->get('', 'Patients::index');
  $routes->post('fetch', 'Patients::fetch');
  $routes->post('fetch2', 'Patients::fetch2');
  $routes->get('show/(:any)', 'Patients::show/$1');
  $routes->get('export', 'Patients::export');
  $routes->get('print_pdf', 'Patients::print_pdf');
  $routes->post('store', 'Patients::store');
  $routes->post('update', 'Patients::update');
  $routes->post('update/(:any)', 'Patients::update/$1');
  $routes->post('update_files', 'Patients::update_files');

  // Delete/Hapus
  $routes->post('destroy/(:any)', 'Patients::destroy/$1');
});

// Routes - Kas
$routes->group('kas', ['namespace' => 'App\modules\kas\Controllers'], function ($routes) {
  $routes->get('/', 'Kas::index');
  $routes->post('get_data_pemasukan', 'Kas::get_data_pemasukan');
  $routes->post('get_data_pengeluaran', 'Kas::get_data_pengeluaran');
  $routes->post('get_data_pengeluaran_harian', 'Kas::get_data_pengeluaran_harian');
  $routes->get('get_master_harian', 'Kas::get_master_harian');
  $routes->post('simpan_transaksi', 'Kas::simpan_transaksi');
  $routes->post('bayar_pengeluaran_harian', 'Kas::bayar_pengeluaran_harian');
  $routes->post('set_filter_region', 'Kas::set_filter_region');

  // Grouping untuk CRUD Master Pengeluaran Harian
  $routes->group('master', function ($routes) {
    $routes->post('simpan', 'Kas::simpan_master_harian');
    $routes->post('hapus', 'Kas::hapus_master_harian');
  });
});

// Routes - Statsitik Keuangan
$routes->group('statistikkeuangan', ['namespace' => 'App\Modules\StatistikKeuangan\Controllers'], function ($routes) {
  $routes->get('/', 'StatistikKeuangan::index'); // Gunakan '/' agar terbaca sebagai root grup
  $routes->get('get_chart_data', 'StatistikKeuangan::get_chart_data');
});


// Routes - Gaji
$routes->group('gaji', ['namespace' => 'App\modules\gaji\Controllers'], function ($routes) {
  $routes->get('/', 'Gajikaryawan::index');
  $routes->post('setting/save', 'Gajikaryawan::saveSetting');
  $routes->get('detail/(:num)', 'Gajikaryawan::detailEstimasi/$1');
  $routes->post('proses_bayar', 'Gajikaryawan::prosesBayar');
});

// Rutes - Kas bon
$routes->group('kasbon', ['namespace' => 'App\modules\kasbon_karyawan\Controllers'], function ($routes) {
  $routes->get('/', 'Kasbonkaryawan::index');
  $routes->post('fetch', 'Kasbonkaryawan::fetchKaryawan');
  $routes->get('detail/(:num)', 'Kasbonkaryawan::detail/$1');
  $routes->post('store', 'Kasbonkaryawan::store');
  $routes->post('bayar', 'Kasbonkaryawan::bayar');
});


// Rotes - tunjangan karyawan
$routes->group('tunjangan-karyawan', ['namespace' => 'App\modules\tunjangan_karyawan\Controllers'], function ($routes) {
  $routes->get('/', 'Tunjangankaryawan::index');
  $routes->post('fetch', 'Tunjangankaryawan::fetch');
  $routes->post('store', 'Tunjangankaryawan::store');
  $routes->delete('delete/(:num)', 'Tunjangankaryawan::delete/$1');
});

// Rutes - transaksi tunjangan
$routes->group('transaksi-tunjangan', ['namespace' => 'App\modules\transaksi_tunjangan\Controllers'], function ($routes) {
  $routes->get('/', 'Transaksitunjangan::index');
  $routes->post('fetch', 'Transaksitunjangan::fetch');
  $routes->get('detail/(:num)', 'Transaksitunjangan::detail/$1');
  $routes->post('store', 'Transaksitunjangan::store');
});

// ROUTES MODUL PENGGAJIAN (PAYROLL)
// $routes->group('detail-gaji', ['namespace' => 'App\modules\detail_gaji\Controllers'], function($routes) {
//     $routes->get('/', 'Detailgaji::index');
//     $routes->get('review/(:num)', 'Detailgaji::review/$1');
//     $routes->post('proses_simpan', 'Detailgaji::proses_simpan');

// });

// Routes - Kehadruan 
$routes->group('kehadiran', ['namespace' => 'App\modules\absensi_karyawan\Controllers'], function ($routes) {
  $routes->get('/', 'Absensikaryawan::index');
  $routes->get('store/(:any)', 'Absensikaryawan::store/$1');
  $routes->get('store', 'Absensikaryawan::store'); // Route untuk halaman form Card
  $routes->get('detail/(:any)', 'Absensikaryawan::detail/$1');
  $routes->post('simpan_massal', 'Absensikaryawan::simpan_massal'); // Route untuk proses AJAX
});


$routes->group('jasa-pelayanan', ['namespace' => 'App\modules\jasa_pelayanan\Controllers'], function($routes) {
    $routes->get('reguler', 'Jasapelayanan::reguler');
    $routes->get('kejantanan', 'Jasapelayanan::kejantanan');
    $routes->post('fetch', 'Jasapelayanan::fetch');
    $routes->get('show/(:num)', 'Jasapelayanan::show/$1');
    $routes->post('destroy/(:num)', 'Jasapelayanan::destroy/$1'); 
    
});
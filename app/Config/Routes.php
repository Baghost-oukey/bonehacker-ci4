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

// API Routes Consolidated at the end of file

// Beranda & Hub
$routes->get('beranda', 'BerandaController::index', ['namespace' => 'App\Modules\Beranda\Controllers', 'filter' => 'auth']);
$routes->get('menu', 'BerandaController::menu', ['namespace' => 'App\Modules\Beranda\Controllers', 'filter' => 'auth']);

// Antrean Public
$routes->get('antrean/daftar-antrean', '\App\Modules\Antrean\Controllers\AntreanController::daftarAntrean');

// Antrean
$routes->group('antrean', ['namespace' => 'App\Modules\Antrean\Controllers', 'filter' => 'auth'], function ($routes) {
  $routes->get('', 'AntreanController::index');
  $routes->get('fetchJson', 'AntreanController::fetchJson');
  $routes->post('fetchDataTable', 'AntreanController::fetchDataTable');
  $routes->post('fetchPatientDataTables', 'AntreanController::fetchPatientDataTables');
  $routes->get('getPatientData/(:num)', 'AntreanController::getPatientData/$1');
  $routes->get('addToQueue/(:num)', 'AntreanController::addToQueue/$1');
  $routes->post('destroy/(:num)', 'AntreanController::destroy/$1');
  $routes->get('procesToQueue/(:num)', 'AntreanController::procesToQueue/$1');
  $routes->get('finishQueue/(:num)', 'AntreanController::finishQueue/$1');
  $routes->post('set-break', 'AntreanController::setBreak');
  $routes->get('export_excell_antrean', 'AntreanController::export_excell_antrean');
  $routes->get('print_pdf_antrean', 'AntreanController::print_pdf_antrean');
});

// Rekam Medis
$routes->get('rekam-medis', '\App\Modules\RekamMedis\Controllers\RekamMedisController::index', ['filter' => 'auth']);
$routes->post('patients/fetch2', '\App\Modules\patients\Controllers\Patients::fetch2');

// Save Data - Rekam Medis
$routes->post('patient/store', '\App\Modules\patients\Controllers\Patients::store');
$routes->post('patient/check_phone', '\App\Modules\patients\Controllers\Patients::check_phone');
$routes->get('patient/export_data', '\App\Modules\patients\Controllers\Patients::export_data');

// Wilayah
$routes->group('region', ['namespace' => 'App\Modules\Region\Controllers', 'filter' => 'auth'], function ($routes) {
  $routes->get('', 'RegionController::index');
  $routes->post('fetch', 'RegionController::fetch');
  $routes->post('store', 'RegionController::store');
  $routes->post('update/(:num)', 'RegionController::update/$1');
  $routes->post('destroy/(:num)', 'RegionController::destroy/$1');
  $routes->post('reactivate/(:num)', 'RegionController::reactivate/$1');
});
// app/Config/Routes.php

// Journal
$routes->group('journal', ['namespace' => 'App\Modules\Journal\Controllers', 'filter' => 'auth'], function ($routes) {
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
  $routes->get('export_excell', 'TransaksiController::export_excell');
  $routes->get('export_pdf', 'TransaksiController::export_pdf');
  $routes->get('chart_data', 'TransaksiController::chart_data');
});

// Statistik
$routes->group('statistik', ['namespace' => 'App\Modules\statistik\Controllers', 'filter' => 'auth'], function ($routes) {
  $routes->get('', 'Statistik::index');
  $routes->get('fetch_statistics', 'Statistik::fetch_statistics');
  $routes->get('fetch_analysis', 'Statistik::fetch_analysis');
});

// Statistik - Tag
$routes->group('statistiktag', ['namespace' => 'App\Modules\statistiktag\Controllers', 'filter' => 'auth'], function ($routes) {
  $routes->get('', 'StatistikTag::index');
  $routes->get('fetch_statistics', 'StatistikTag::fetch_statistics');
});

// Statistik - Hasil Pemeriksaan
$routes->group('statistikresult', ['namespace' => 'App\Modules\statistikresult\Controllers', 'filter' => 'auth'], function ($routes) {
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

// Statistik-Sumber Daya
$routes->group('statistikresource', ['namespace' => 'App\Modules\statistikresource\Controllers', 'filter' => 'auth'], function ($routes) {
  $routes->get('', 'StatistikResource::index');
  $routes->get('get_marketing_data', 'StatistikResource::get_marketing_data');
});

// Jabatan
$routes->group('jabatan', ['namespace' => 'App\Modules\jabatan\Controllers', 'filter' => 'auth'], function ($routes) {
  $routes->get('', 'Jabatan::index');
  $routes->post('fetch', 'Jabatan::fetch');
  $routes->post('store', 'Jabatan::store');
  $routes->post('update/(:num)', 'Jabatan::update/$1');
  $routes->post('destroy/(:num)', 'Jabatan::destroy/$1');
  $routes->post('check_name_exists', 'Jabatan::check_name_exists');
});


// Tag - Complaint(Keluhan)
$routes->group('tag-keluhan', ['namespace' => 'App\Modules\TagComplaint\Controllers', 'filter' => 'auth'], function ($routes) {
  $routes->get('', 'TagComplaintController::index');
  $routes->post('fetch', 'TagComplaintController::fetch');
  $routes->post('store', 'TagComplaintController::store');
  $routes->post('update/(:num)', 'TagComplaintController::update/$1');
  $routes->post('destroy/(:num)', 'TagComplaintController::destroy/$1');
  $routes->post('check_name_exists', 'TagComplaintController::check_name_exists');
  $routes->get('get_tags', 'TagComplaintController::get_tags');
});

// Tag - Medis(Rekam Medis)
$routes->group('tag-rekam-medis', ['namespace' => 'App\Modules\TagRekamMedis\Controllers', 'filter' => 'auth'], function ($routes) {
  $routes->get('/', 'MedisController::index');
  $routes->match(['get', 'post'], 'fetch', 'MedisController::fetch');
  $routes->get('tags', 'MedisController::get_tags');
  $routes->post('store', 'MedisController::store');
  $routes->post('update/(:num)', 'MedisController::update/$1');
  $routes->post('destroy/(:num)', 'MedisController::destroy/$1');
  $routes->post('check-name', 'MedisController::check_name_exists');
});

// Tag - Result
$routes->group('tag-pemeriksaan', ['namespace' => 'App\Modules\TagPemeriksaan\Controllers', 'filter' => 'auth'], function ($routes) {
  $routes->get('', 'TagPemeriksaanController::index');
  $routes->post('fetch', 'TagPemeriksaanController::fetch');
  $routes->post('store', 'TagPemeriksaanController::store');
  $routes->post('update/(:num)', 'TagPemeriksaanController::update/$1');
  $routes->post('destroy/(:num)', 'TagPemeriksaanController::destroy/$1');
  $routes->post('check_name_exists', 'TagPemeriksaanController::check_name_exists');
  $routes->get('get_tags', 'TagPemeriksaanController::get_tags');
});

// Logs
$routes->group('logs', ['namespace' => 'App\Modules\logs\Controllers', 'filter' => 'auth'], function ($routes) {
  $routes->get('', 'Log::index');
});

// Whatsapp
$routes->group('whatsapp', ['namespace' => 'App\Modules\whatsapp\Controllers', 'filter' => 'auth'], function ($routes) {
  $routes->get('', 'Whatsapp::index');
  $routes->post('store', 'Whatsapp::store');
  $routes->post('edit/(:num)', 'Whatsapp::edit/$1');
  $routes->post('delete/(:num)', 'Whatsapp::delete/$1');
  $routes->post('send_notif_patients/(:num)', 'Whatsapp::send_notif_patients/$1');
});

// History
$routes->group('history', ['namespace' => 'App\Modules\history\Controllers', 'filter' => 'auth'], function ($routes) {
  $routes->post('fetch/(:num)', 'History::fetch/$1');
  $routes->post('store', 'History::store');
  $routes->post('update', 'History::update');
  $routes->post('copy', 'History::copy');
  $routes->post('destroy/(:num)', 'History::destroy/$1');
  $routes->get('show/(:num)', 'History::show/$1');
  $routes->get('terapis-by-region', 'History::getTerapisByRegion');
});

// Log - WhatsApp
$routes->group('log_whatsapp', ['namespace' => 'App\Modules\log_whatsapp\Controllers', 'filter' => 'auth'], function ($routes) {
  $routes->get('', 'Logwhatsapp::index');
  $routes->post('resend', 'Logwhatsapp::resend');
});

// Greeting
$routes->group('greeting', ['namespace' => 'App\Modules\greeting\Controllers', 'filter' => 'auth'], function ($routes) {
  $routes->get('', 'Greeting::index');
  $routes->post('save', 'Greeting::save');
  $routes->get('delete/(:num)', 'Greeting::delete/$1');
});

// Karyawan (Unified Personnel Management)
$routes->group('karyawan', ['namespace' => 'App\modules\karyawan\Controllers', 'filter' => 'auth'], function ($routes) {
  $routes->get('', 'KaryawanController::index');
  $routes->post('fetch', 'KaryawanController::fetch');
  $routes->post('store', 'KaryawanController::store');
  $routes->get('show/(:any)', 'KaryawanController::show/$1');
  
  // Account operations
  $routes->post('update_account/(:num)', 'KaryawanController::update_account/$1');
  $routes->post('delete_account/(:num)', 'KaryawanController::delete_account/$1');
  $routes->post('check_username_exists', 'KaryawanController::checkUsername');
  
  // Profile operations
  $routes->post('update_profile', 'KaryawanController::update_profile');
  $routes->post('active/(:num)', 'KaryawanController::active/$1');
  $routes->post('nonActive/(:num)', 'KaryawanController::nonActive/$1');
  $routes->get('destroy/(:num)', 'KaryawanController::destroy/$1');
  
  // Patient Access
  $routes->get('view_patient/(:num)', 'KaryawanController::view_patient/$1');
  $routes->post('fetch_patients', 'KaryawanController::fetch_patients');
  $routes->post('add_outside_patient', 'KaryawanController::add_outside_patient');
  $routes->post('fetch_patients_luar', 'KaryawanController::fetch_patients_luar');
  $routes->post('get_outside_patients_select', 'KaryawanController::get_outside_patients_select');
  $routes->post('delete_outside_patient', 'KaryawanController::delete_outside_patient');
  
  // Extra
  $routes->get('public_info/(:any)', 'KaryawanController::public_info/$1');
  $routes->get('profil_saya', 'KaryawanController::profil_saya');
});

// Patients - Routes
$routes->group('patient', ['namespace' => 'App\Modules\patients\Controllers', 'filter' => 'auth'], function ($routes) {
  $routes->get('', 'Patients::index');
  $routes->post('fetch', 'Patients::fetch');
  $routes->post('fetch2', 'Patients::fetch2');
  $routes->get('show/(:any)', 'Patients::show/$1');
  $routes->get('history/(:any)', 'Patients::history/$1');
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
$routes->group('kas', ['namespace' => 'App\modules\kas\Controllers', 'filter' => 'auth'], function ($routes) {
  $routes->get('/', 'Kas::index');
  $routes->post('get_data_pemasukan', 'Kas::get_data_pemasukan');
  $routes->post('get_data_pengeluaran', 'Kas::get_data_pengeluaran');
  $routes->post('get_data_pengeluaran_harian', 'Kas::get_data_pengeluaran_harian');
  $routes->get('get_master_harian', 'Kas::get_master_harian');
  $routes->post('simpan_transaksi', 'Kas::simpan_transaksi');
  $routes->post('bayar_pengeluaran_harian', 'Kas::bayar_pengeluaran_harian');
  $routes->post('set_filter_region', 'Kas::set_filter_region');

  // Kategori Keuangan
  $routes->get('categories', 'FinanceCategoryController::index', ['namespace' => 'App\Modules\transaksi\Controllers']);
  $routes->post('categories/store', 'FinanceCategoryController::store', ['namespace' => 'App\Modules\transaksi\Controllers']);
  $routes->get('categories/delete/(:num)', 'FinanceCategoryController::delete/$1', ['namespace' => 'App\Modules\transaksi\Controllers']);

  // Grouping untuk CRUD Master Pengeluaran Harian
  $routes->group('master', function ($routes) {
    $routes->post('simpan', 'Kas::simpan_master_harian');
    $routes->post('hapus', 'Kas::hapus_master_harian');
  });
});

// --- API ROUTES FOR MOBILE (Consolidated) ---
$routes->group('api', ['namespace' => 'App\Modules\api\Controllers'], function ($routes) {
  $routes->post('login', 'Auth::login');
  $routes->get('statistics/summary', 'Statistics::summary');

  // Antrean API
  $routes->get('antrean', 'Antrean::index');
  $routes->post('antrean/proses', 'Antrean::proses');
  $routes->post('antrean/selesai', 'Antrean::selesai');

  // Patients API
  $routes->get('patients', 'Patients::index');
  $routes->get('patients/show/(:num)', 'Patients::show/$1');
  $routes->post('patients/add-to-queue', 'Patients::addToQueue');
  $routes->post('patients/register', 'Patients::register');
  $routes->post('patients/upload-file', 'Patients::uploadFile');
  $routes->post('patients/delete-file', 'Patients::deleteFile');

  // Region (Cabang) API
  $routes->get('regions', 'Region::index');
  $routes->post('regions/store', 'Region::store');
  $routes->post('regions/update/(:num)', 'Region::update/$1');
  $routes->post('regions/delete/(:num)', 'Region::delete/$1');

  // History & Medical Records API
  $routes->get('medical-records/patient/(:num)', 'History::index/$1');
  $routes->get('medical-records/tags', 'History::tags');
  $routes->post('medical-records/store', 'History::save');
});

// Routes - Statsitik Keuangan
$routes->group('statistikkeuangan', ['namespace' => 'App\Modules\StatistikKeuangan\Controllers', 'filter' => 'auth'], function ($routes) {
  $routes->get('/', 'StatistikKeuangan::index');
  $routes->get('get_chart_data', 'StatistikKeuangan::get_chart_data');
  $routes->get('export_excel', 'StatistikKeuangan::export_excel');
  $routes->get('export_pdf', 'StatistikKeuangan::export_pdf');
});


// Routes - Gaji
$routes->group('gaji', ['namespace' => 'App\modules\gaji\Controllers', 'filter' => 'auth'], function ($routes) {
  $routes->get('/', 'Gajikaryawan::index');
  $routes->post('setting/save', 'Gajikaryawan::saveSetting');
  $routes->get('detail/(:num)', 'Gajikaryawan::detailEstimasi/$1');
  $routes->post('proses_bayar', 'Gajikaryawan::prosesBayar');
  $routes->get('fetch_estimasi', 'Gajikaryawan::fetchEstimasi');
  $routes->get('export', 'Gajikaryawan::export');
  $routes->get('monitor', 'Gajikaryawan::monitor');
});

// Rutes - Kas bon
$routes->group('kasbon', ['namespace' => 'App\modules\kasbon_karyawan\Controllers', 'filter' => 'auth'], function ($routes) {
  $routes->get('/', 'Kasbonkaryawan::index');
  $routes->post('fetch', 'Kasbonkaryawan::fetchKaryawan');
  $routes->get('detail/(:num)', 'Kasbonkaryawan::detail/$1');
  $routes->post('store', 'Kasbonkaryawan::store');
  $routes->post('bayar', 'Kasbonkaryawan::bayar');
  // Potongan Rutin
  $routes->post('potongan/store', 'Kasbonkaryawan::storePotongan');
  $routes->post('potongan/delete/(:num)', 'Kasbonkaryawan::deletePotongan/$1');
});


// Rotes - master gaji
$routes->group('master-gaji', ['namespace' => 'App\modules\tunjangan_karyawan\Controllers', 'filter' => 'auth'], function ($routes) {
  $routes->get('/', 'Tunjangankaryawan::index');
  $routes->post('fetch', 'Tunjangankaryawan::fetch');
  $routes->post('store', 'Tunjangankaryawan::store');
  $routes->get('detail/(:num)', 'Tunjangankaryawan::detail/$1');
  $routes->delete('delete/(:num)', 'Tunjangankaryawan::delete/$1');
  $routes->post('delete/(:num)', 'Tunjangankaryawan::delete/$1');
});

// Rutes - transaksi tunjangan
$routes->group('transaksi-tunjangan', ['namespace' => 'App\modules\transaksi_tunjangan\Controllers', 'filter' => 'auth'], function ($routes) {
  $routes->get('/', 'Transaksitunjangan::index');
  $routes->post('fetch', 'Transaksitunjangan::fetch');
  $routes->get('detail/(:num)', 'Transaksitunjangan::detail/$1');
  $routes->post('store', 'Transaksitunjangan::store');
  $routes->post('save-setting', 'Transaksitunjangan::saveSetting');
  $routes->post('save-setting-massal', 'Transaksitunjangan::saveSettingMassal');
  $routes->post('delete-setting/(:num)', 'Transaksitunjangan::deleteSetting/$1');
});

// ROUTES MODUL PENGGAJIAN (PAYROLL)
$routes->group('detail-gaji', ['namespace' => 'App\modules\detail_gaji\Controllers', 'filter' => 'auth'], function ($routes) {
  $routes->get('/', 'Detailgaji::index');
  $routes->get('review/(:num)', 'Detailgaji::review/$1');
  $routes->post('proses_simpan', 'Detailgaji::proses_simpan');
});

// Routes - Kehadiran 
$routes->group('kehadiran', ['namespace' => 'App\modules\absensi_karyawan\Controllers', 'filter' => 'auth'], function ($routes) {
  $routes->get('/', 'Absensikaryawan::index');
  $routes->get('tambah', 'Absensikaryawan::tambah');
  $routes->post('simpan_presensi_baru', 'Absensikaryawan::simpan_presensi_baru');
  $routes->get('presensi/(:any)', 'Absensikaryawan::presensi/$1');
  $routes->get('detail/(:any)', 'Absensikaryawan::detail/$1');
  $routes->get('export', 'Absensikaryawan::export');
  $routes->post('simpan_massal', 'Absensikaryawan::simpan_massal');

  // Cuti Karyawan
  $routes->group('cuti', function ($routes) {
    $routes->get('/', 'Cutikaryawan::index');
    $routes->post('simpan', 'Cutikaryawan::simpan');
    $routes->delete('hapus/(:num)', 'Cutikaryawan::hapus/$1');
    $routes->post('update_kuota', 'Cutikaryawan::update_kuota');
    $routes->get('cek_sisa_cuti/(:num)', 'Cutikaryawan::cek_sisa_cuti/$1');
  });
});

// Routes - Kalender
$routes->group('kalender', ['namespace' => 'App\modules\kalender\Controllers', 'filter' => 'auth'], function ($routes) {
  $routes->get('/', 'Kalender::index');
  $routes->post('store', 'Kalender::store');
  $routes->post('store-rutin', 'Kalender::storeRutin');
  $routes->match(['post', 'delete'], 'destroy/(:num)', 'Kalender::destroy/$1');
  $routes->post('copy-global', 'Kalender::copyGlobal');
  $routes->get('get-data', 'Kalender::getData');
});


$routes->group('jasa-pelayanan', ['namespace' => 'App\modules\jasa_pelayanan\Controllers', 'filter' => 'auth'], function ($routes) {
  $routes->get('reguler', 'Jasapelayanan::reguler');
  $routes->get('kejantanan', 'Jasapelayanan::kejantanan');
  $routes->get('settings', 'Jasapelayanan::settings');
  $routes->post('saveSettings', 'Jasapelayanan::saveSettings');
  $routes->post('getJaspelPerHari', 'Jasapelayanan::getJaspelPerHari');
  $routes->post('getJaspelKejantananPerHari', 'Jasapelayanan::getJaspelKejantananPerHari');
  $routes->post('fetch', 'Jasapelayanan::fetch');
  $routes->post('fetchPatients', 'Jasapelayanan::fetchPatients');
  $routes->get('show/(:num)', 'Jasapelayanan::show/$1');
  $routes->get('detail-reguler/(:num)', 'Jasapelayanan::showReguler/$1');
  $routes->get('detail-kejantanan/(:num)', 'Jasapelayanan::showKejantanan/$1');
  $routes->post('destroy/(:num)', 'Jasapelayanan::destroy/$1');
});

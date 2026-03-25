<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
// $routes->get('/', 'Home::index');
$routes->get('/', '\App\modules\auth\Controllers\Auth::index');

$routes->group('auth', ['namespace' => 'App\modules\auth\Controllers'], function ($routes) {
    $routes->get('/', 'Auth::index');
    $routes->post('validate', 'Auth::authValidate');
    $routes->get('destroy', 'Auth::destroy');
});

// Beranda Patients
$routes->get('beranda_views', '\App\modules\beranda\Controllers\Beranda::index');

// Rekam Medis
$routes->get('dashboard', '\App\modules\dashboard\Controllers\Dashboard::index');
$routes->post('patients/fetch2', '\App\modules\patients\Controllers\Patients::fetch2');

// Save Data - Rekam Medis
$routes->post('patient/store', '\App\modules\patients\Controllers\Patients::store');
$routes->post('patient/check_phone', '\App\modules\patients\Controllers\Patients::check_phone');

// Antrean
$routes->group('antrean', ['namespace' => 'App\modules\antrean\Controllers'], function ($routes) {
    $routes->get('/', 'Antrean::index');
    // Ambil Data
    $routes->get('fetchJson', 'Antrean::fetchJson');
    $routes->post('fetchDataTable', 'Antrean::fetchDataTable');
    // Ambil Data Untuk Pasien
    $routes->post('fetchPatientDataTables', 'Antrean::fetchPatientDataTables');
    // Menambahkan ke Antrian
    $routes->get('addToQueue/(:num)', 'Antrean::addToQueue/$1');
    $routes->post('destroy/(:num)', 'Antrean::destroy/$1');
    $routes->get('daftarAntrean', 'Antrean::daftarAntrean');
    $routes->get('procesToQueue/(:num)', 'Antrean::procesToQueue/$1');
    $routes->get('finishQueue/(:num)', 'Antrean::finishQueue/$1');
});

// Wilayah
$routes->group('region', ['namespace' => 'App\modules\region\Controllers'], function ($routes) {
    $routes->get('/', 'Region::index');
    $routes->post('fetch', 'Region::fetch');
    $routes->post('store', 'Region::store');
    $routes->post('update/(:num)', 'Region::update/$1');
    $routes->get('destroy/(:num)', 'Region::destroy/$1');
});
// app/Config/Routes.php

// Journal
$routes->group('journal', function ($routes) {
    $routes->get('/', '\App\Modules\Journal\Controllers\Journal::index');
    $routes->post('fetch', '\App\Modules\Journal\Controllers\Journal::fetch');
    $routes->get('export_excell', '\App\Modules\Journal\Controllers\Journal::export_excell');
    $routes->get('export_pdf', '\App\Modules\Journal\Controllers\Journal::export_pdf');
});

// Statistik
$routes->group('statistik',  ['namespace' => 'App\modules\statisktik\Controllers'], function ($routes) {
    $routes->get('/', 'Statistik::index');
    $routes->get('statistik/fetch_statistics', 'Statistik::fetch_statistics');
});

// Statistik - Tag
$routes->group('statistiktag',  ['namespace' => 'App\modules\statistiktag\Controllers'], function ($routes) {
    $routes->get('/', 'StatistikTag::index');
    $routes->get('fetch_statistics', 'StatistikTag::fetch_statistics');
});

// Statistik - Hasil Pemeriksaan
$routes->group('statistikresult', ['namespace' => 'App\modules\statistikresult\Controllers'], function ($routes) {
    $routes->get('/', 'Statistikresult::index');
    $routes->get('fetch_statistics', 'Statistikresult::fetch_statistics');
});

// Statistik - Gender
$routes->group('statistikgender', ['namespace' => 'App\modules\statistikgender\Controllers', 'filter' => 'auth'], function ($routes) {
    $routes->get('/', 'Statistikgender::index');
    $routes->get('fetch_statistics', 'Statistikgender::fetch_statistics');
});

// Statistik - Gender
$routes->group('statistikdaerah', ['namespace' => 'App\modules\statistikdaerah\Controllers', 'filter' => 'auth'], function ($routes) {
    $routes->get('/', 'Statistikdaerah::index');
    $routes->get('fetch_statistics', 'Statistikdaerah::fetch_statistics');
});

// Jabatan
$routes->group('jabatan', ['namespace' => 'App\modules\jabatan\Controllers'], function ($routes) {
    $routes->get('/', 'Jabatan::index');
    $routes->post('fetch', 'Jabatan::fetch');
    $routes->post('store', 'Jabatan::store');
    $routes->post('update/(:num)', 'Jabatan::update/$1');
    $routes->post('destroy/(:num)', 'Jabatan::destroy/$1');
    $routes->post('check_name_exists', 'Jabatan::check_name_exists');
});

// Tag - Keluhan
$routes->group('complaint', ['namespace' => 'App\modules\complaint\Controllers'], function ($routes) {
    $routes->get('/', 'Complaint::index');
    $routes->post('fetch', 'Complaint::fetch');
    $routes->post('store', 'Complaint::store');
    $routes->post('update/(:num)', 'Complaint::update/$1');
    $routes->post('destroy/(:num)', 'Complaint::destroy/$1');
    $routes->post('check_name_exists', 'Complaint::check_name_exists');
});

// Tag - Rekam Medis
$routes->group('medis', ['namespace' => 'App\modules\medis\Controllers'], function ($routes) {
    $routes->get('/', 'Medis::index');
    $routes->post('fetch', 'Medis::fetch');
    $routes->get('get_tags', 'Medis::get_tags');
    $routes->post('store', 'Medis::store');
    $routes->post('update/(:num)', 'Medis::update/$1');
    $routes->post('destroy/(:num)', 'Medis::destroy/$1');
    $routes->post('check_name_exists', 'Medis::check_name_exists');
});

// Tag - Result
$routes->group('result', ['namespace' => 'App\modules\result\Controllers'], function ($routes) {
    $routes->get('/', 'Result::index');
    $routes->post('fetch', 'Result::fetch');
    $routes->post('store', 'Result::store');
    $routes->post('update/(:num)', 'Result::update/$1');
    $routes->post('destroy/(:num)', 'Result::destroy/$1');
    $routes->post('check_name_exists', 'Result::check_name_exists');
});

// Logs
$routes->group('logs', ['namespace' => 'App\modules\logs\Controllers'], function ($routes) {
    $routes->get('/', 'Log::index');
});

// Whatsapp
$routes->group('whatsapp', ['namespace' => 'App\modules\whatsapp\Controllers'], function ($routes) {
    $routes->get('/', 'Whatsapp::index');
    $routes->post('store', 'Whatsapp::store');
    $routes->post('edit/(:num)', 'Whatsapp::edit/$1');
    $routes->post('delete/(:num)', 'Whatsapp::delete/$1');
    $routes->get('send_notif_patients/(:num)', 'Whatsapp::send_notif_patients/$1');
});

// History
$routes->group('history', ['namespace' => 'App\modules\history\Controllers'], function ($routes) {
    $routes->post('fetch/(:num)', 'History::fetch/$1');
    $routes->post('store', 'History::store');
    $routes->post('update', 'History::update');
    $routes->post('copy', 'History::copy');
    $routes->post('destroy/(:num)', 'History::destroy/$1');
    $routes->get('show/(:num)', 'History::show/$1');
});

// Log - WhatsApp
$routes->group('log_whatsapp', ['namespace' => 'App\modules\log_whatsapp\Controllers'], function ($routes) {
    $routes->get('/', 'Logwhatsapp::index');
    $routes->post('resend', 'Logwhatsapp::resend');
});

// Greeting
$routes->group('greeting', ['namespace' => 'App\modules\greeting\Controllers'], function ($routes) {
    $routes->get('/', 'Greeting::index');
    $routes->post('save', 'Greeting::save');
    $routes->get('delete/(:num)', 'Greeting::delete/$1');
});

// Terapis
$routes->group('terapis', ['namespace' => 'App\modules\terapis\Controllers'], function ($routes) {
    $routes->get('/', 'Terapis::index');
    $routes->get('detail_terapis/(:any)', 'Terapis::detail_terapis/$1');
    $routes->post('store', 'Terapis::store');
    $routes->post('update', 'Terapis::update');
    $routes->get('destroy/(:num)', 'Terapis::destroy/$1');
});

// Untuk Akses info publik
$routes->get('p/(:any)', '\App\Controllers\Terapis::public_info/$1');

// Users
$routes->group('users', ['namespace' => 'App\Modules\users\Controllers'], function ($routes) {
    $routes->get('/', 'Users::index');
    $routes->post('fetch', 'Users::fetch');
    $routes->post('store', 'Users::store');
    $routes->post('update/(:num)', 'Users::update/$1');
    $routes->get('destroy/(:num)', 'Users::destroy/$1');

    // Pasien Luar 
    $routes->get('view_patient/(:num)', 'Users::view_patient/$1');
    $routes->post('fetch_patients', 'Users::fetch_patients');
    $routes->post('add_outside_patient', 'Users::add_outside_patient');
});

// Patients - Routes
$routes->group('patient', ['namespace' => 'App\Modules\patients\Controllers'], function ($routes) {
    $routes->get('/', 'Patients::index');
    $routes->post('fetch', 'Patients::fetch');
    $routes->post('fetch2', 'Patients::fetch2');
    $routes->get('show/(:any)', 'Patients::show/$1');
    $routes->get('export', 'Patients::export');
    $routes->get('print_pdf', 'Patients::print_pdf');
    $routes->post('store', 'Patients::store');
    $routes->post('update', 'Patients::update');
    $routes->post('update/(:any)', 'Patients::update/$1');

    // Delete/Hapus
    $routes->post('destroy/(:any)', 'Patients::destroy/$1');
});

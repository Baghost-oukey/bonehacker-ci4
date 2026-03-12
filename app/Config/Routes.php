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
    $routes->get('fetchJson', 'Antrean::fetchJson');
    $routes->post('fetchDataTable', 'Antrean::fetchDataTable');
    $routes->post('fetchPatientsDataTable', 'Antrean::fetchPatientsDataTable');
    $routes->post('destroy/(:num)', 'Antrean::destroy/$1');
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
